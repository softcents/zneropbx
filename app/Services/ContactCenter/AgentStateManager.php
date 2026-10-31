<?php

namespace App\Services\ContactCenter;

use App\Models\CallCenterAgents;
use App\Models\ContactCenterAgentStatusLog;
use App\Models\ContactCenterBreakReason;
use App\Services\FreeswitchEslService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AgentStateManager
{
    public const STATUS_AVAILABLE = 'Available';
    public const STATUS_LOGGED_OUT = 'Logged Out';
    public const STATUS_ON_BREAK = 'On Break';
    public const STATUS_IN_CALL = 'In Call';
    public const STATUS_HOLD = 'Hold';
    public const STATUS_WRAP_UP = 'Wrap-Up';

    protected FreeswitchEslService $esl;

    public function __construct(FreeswitchEslService $esl)
    {
        $this->esl = $esl;
    }

    /**
     * Change agent status across FreeSWITCH and Database audit log.
     */
    public function setStatus(
        string $agentUuid,
        string $status,
        ?string $breakReasonCode = null,
        ?string $callUuid = null
    ): array {
        $agent = CallCenterAgents::where('call_center_agent_uuid', $agentUuid)->first();
        if (!$agent) {
            throw new \InvalidArgumentException("Agent not found: {$agentUuid}");
        }

        $domainUuid = $agent->domain_uuid ?: session('domain_uuid');
        $breakReason = null;

        // If going On Break, validate break reason
        if ($status === self::STATUS_ON_BREAK && $breakReasonCode) {
            $breakReason = ContactCenterBreakReason::where('code', $breakReasonCode)
                ->where(function ($q) use ($domainUuid) {
                    $q->whereNull('domain_uuid')->orWhere('domain_uuid', $domainUuid);
                })
                ->where('is_active', true)
                ->first();
        }

        // Map status to FreeSWITCH mod_callcenter status
        $fsStatus = match ($status) {
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_LOGGED_OUT => 'Logged Out',
            self::STATUS_ON_BREAK => 'On Break',
            self::STATUS_WRAP_UP => 'On Break', // In mod_callcenter, ACW/Wrap-up sets agent status to pause distribution
            default => 'Available'
        };

        // 1. Update FreeSWITCH runtime status via ESL
        try {
            $cmd = sprintf("callcenter_config agent set status %s '%s'", $agentUuid, $fsStatus);
            $this->esl->executeCommand($cmd, false);

            if ($status === self::STATUS_AVAILABLE) {
                // Ensure state is Waiting
                $this->esl->executeCommand(sprintf("callcenter_config agent set state %s 'Waiting'", $agentUuid), false);
            }
        } catch (\Throwable $e) {
            Log::warning("AgentStateManager: ESL status update failed: " . $e->getMessage());
        }

        // 2. Update persistent DB table v_call_center_agents
        $agent->agent_status = $fsStatus;
        $agent->save();

        // 3. Close open status log and calculate duration
        $now = Carbon::now();
        $openLog = ContactCenterAgentStatusLog::where('agent_uuid', $agentUuid)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if ($openLog) {
            $duration = (int) $openLog->started_at->diffInSeconds($now);
            $openLog->update([
                'ended_at' => $now,
                'duration_seconds' => $duration,
            ]);
        }

        // 4. Record new status log
        $newLog = ContactCenterAgentStatusLog::create([
            'domain_uuid' => $domainUuid,
            'agent_uuid' => $agentUuid,
            'status' => $status,
            'break_reason_uuid' => $breakReason?->break_reason_uuid,
            'started_at' => $now,
            'call_uuid' => $callUuid,
        ]);

        return [
            'success' => true,
            'agent_uuid' => $agentUuid,
            'status' => $status,
            'fs_status' => $fsStatus,
            'break_reason' => $breakReason?->name,
            'started_at' => $now->toIso8601String(),
        ];
    }

    /**
     * Get active agent timeline for today.
     */
    public function getAgentTimeline(string $agentUuid, ?string $date = null): Collection
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();

        return ContactCenterAgentStatusLog::with('breakReason')
            ->where('agent_uuid', $agentUuid)
            ->whereDate('started_at', $date)
            ->orderBy('started_at', 'asc')
            ->get();
    }

    /**
     * List all available break reasons for current tenant.
     */
    public function getBreakReasons(?string $domainUuid = null): Collection
    {
        $domainUuid = $domainUuid ?: session('domain_uuid');

        return ContactCenterBreakReason::where(function ($q) use ($domainUuid) {
            $q->whereNull('domain_uuid');
            if ($domainUuid) {
                $q->orWhere('domain_uuid', $domainUuid);
            }
        })
        ->where('is_active', true)
        ->orderBy('name')
        ->get();
    }
}
