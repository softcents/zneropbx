<?php

namespace App\Services\ContactCenter;

use App\Models\CallCenterQueues;
use App\Models\ContactCenterCallback;
use App\Services\FreeswitchEslService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CallbackEngineService
{
    protected ?FreeswitchEslService $eslService;

    public function __construct(?FreeswitchEslService $eslService = null)
    {
        $this->eslService = $eslService ?? new FreeswitchEslService();
    }

    /**
     * Register a new callback request for an overflowed or waiting caller.
     */
    public function scheduleCallback(
        string $domainUuid,
        string $queueUuid,
        string $callerNumber,
        string $originalCallUuid,
        int $delaySeconds = 60
    ): ContactCenterCallback {
        return ContactCenterCallback::create([
            'contact_center_callback_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'queue_uuid' => $queueUuid,
            'original_call_uuid' => $originalCallUuid ?: (string) Str::uuid(),
            'source_node' => gethostname() ?: 'node-1',
            'number' => $callerNumber,
            'name_path' => 'callback_' . $callerNumber,
            'name_sha256' => hash('sha256', $callerNumber),
            'name_bytes' => strlen($callerNumber),
            'joined_epoch' => time(),
            'base_score' => 100, // Priority boost for callback callers
            'status' => 'waiting',
            'attempts' => 0,
            'next_attempt_at' => Carbon::now()->addSeconds($delaySeconds),
            'expires_at' => Carbon::now()->addHours(4),
        ]);
    }

    /**
     * Get active and pending callbacks for a tenant domain.
     */
    public function getCallbacks(string $domainUuid, int $limit = 50)
    {
        return ContactCenterCallback::where('domain_uuid', $domainUuid)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($cb) {
                $queue = CallCenterQueues::where('call_center_queue_uuid', $cb->queue_uuid)->first();
                return [
                    'callback_uuid' => $cb->contact_center_callback_uuid,
                    'queue_uuid' => $cb->queue_uuid,
                    'queue_name' => $queue ? $queue->queue_name : 'Default Queue',
                    'number' => $cb->number,
                    'status' => $cb->status,
                    'attempts' => $cb->attempts,
                    'next_attempt_at' => $cb->next_attempt_at ? $cb->next_attempt_at->toIso8601String() : null,
                    'expires_at' => $cb->expires_at ? $cb->expires_at->toIso8601String() : null,
                    'created_at' => $cb->created_at ? $cb->created_at->toIso8601String() : null,
                    'last_error' => $cb->last_error,
                ];
            });
    }

    /**
     * Execute eligible pending callbacks.
     */
    public function processPendingCallbacks(int $batchSize = 10): int
    {
        $pending = ContactCenterCallback::where('status', 'waiting')
            ->where('next_attempt_at', '<=', Carbon::now())
            ->where('expires_at', '>', Carbon::now())
            ->limit($batchSize)
            ->get();

        $processedCount = 0;

        foreach ($pending as $callback) {
            $this->originateCallback($callback);
            $processedCount++;
        }

        // Mark expired callbacks
        ContactCenterCallback::where('status', 'waiting')
            ->where('expires_at', '<=', Carbon::now())
            ->update([
                'status' => 'expired',
                'last_error' => 'Callback expired before an agent became available.'
            ]);

        return $processedCount;
    }

    /**
     * Originate an outbound callback call connecting the customer back to the queue or an agent.
     */
    public function originateCallback(ContactCenterCallback $callback): bool
    {
        $queue = CallCenterQueues::where('call_center_queue_uuid', $callback->queue_uuid)->first();
        if (!$queue) {
            $callback->update([
                'status' => 'failed',
                'last_error' => 'Associated queue not found.'
            ]);
            return false;
        }

        $callback->update([
            'status' => 'calling',
            'attempts' => $callback->attempts + 1,
        ]);

        $attemptUuid = (string) Str::uuid();
        $waiterUuid = (string) Str::uuid();
        $customerUuid = (string) Str::uuid();

        // Log attempt to contact_center_callback_attempts
        try {
            DB::table('contact_center_callback_attempts')->insert([
                'contact_center_callback_attempt_uuid' => $attemptUuid,
                'callback_uuid' => $callback->contact_center_callback_uuid,
                'execution_uuid' => (string) Str::uuid(),
                'node_id' => gethostname() ?: 'node-1',
                'generation' => 1,
                'status' => 'originating',
                'waiter_uuid' => $waiterUuid,
                'customer_uuid' => $customerUuid,
                'token_hash' => hash('sha256', $attemptUuid),
                'core_uuid' => (string) Str::uuid(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning("Failed to insert callback attempt log: " . $e->getMessage());
        }

        if (!$this->eslService->isConnected()) {
            $callback->update([
                'status' => 'waiting',
                'next_attempt_at' => Carbon::now()->addSeconds(120),
                'last_error' => 'Telephony engine ESL not connected. Retrying in 2 minutes.'
            ]);
            return false;
        }

        // Bridge to queue extension or loopback
        $queueExtension = $queue->queue_extension ?: 'callcenter';
        $dialString = "loopback/{$callback->number}/default";

        // FreeSWITCH originate command
        $cmd = "originate {origination_caller_id_name='Queue Callback',origination_caller_id_number='{$queueExtension}',callback_uuid='{$callback->contact_center_callback_uuid}'}{$dialString} {$queueExtension} XML default";
        
        try {
            $response = $this->eslService->executeCommand($cmd);
            if (str_starts_with(trim($response), '+OK')) {
                $callback->update([
                    'status' => 'connected',
                    'last_error' => null
                ]);
                return true;
            } else {
                $retryDelay = min(300, 60 * pow(2, $callback->attempts)); // Exponential backoff up to 5 min
                $callback->update([
                    'status' => $callback->attempts >= 3 ? 'failed' : 'waiting',
                    'next_attempt_at' => Carbon::now()->addSeconds($retryDelay),
                    'last_error' => "Call failed: {$response}. Retrying in {$retryDelay}s."
                ]);
                return false;
            }
        } catch (\Throwable $e) {
            $callback->update([
                'status' => 'failed',
                'last_error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Cancel a pending callback.
     */
    public function cancelCallback(string $callbackUuid, string $reason = 'Cancelled by supervisor'): bool
    {
        $callback = ContactCenterCallback::where('contact_center_callback_uuid', $callbackUuid)->first();
        if (!$callback) {
            return false;
        }

        $callback->update([
            'status' => 'cancelled',
            'last_error' => $reason
        ]);

        return true;
    }
}
