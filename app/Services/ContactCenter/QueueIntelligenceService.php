<?php

namespace App\Services\ContactCenter;

use App\Models\CallCenterAgents;
use App\Models\CallCenterQueues;
use App\Models\ContactCenterQueueSetting;
use App\Services\FreeswitchEslService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class QueueIntelligenceService
{
    protected FreeswitchEslService $esl;

    public function __construct(FreeswitchEslService $esl)
    {
        $this->esl = $esl;
    }

    /**
     * Get live overview of all queues in the domain with SLA and intelligence indicators.
     */
    public function getLiveWallboard(string $domainUuid, string $domainName): array
    {
        $queues = CallCenterQueues::where('domain_uuid', $domainUuid)
            ->with('queueSettings')
            ->get();

        $allAgents = CallCenterAgents::where('domain_uuid', $domainUuid)->get();
        $agentMap = $allAgents->keyBy('call_center_agent_uuid');

        $queueCards = [];
        $totalWaiting = 0;
        $totalActive = 0;
        $totalAnsweredToday = 0;
        $longestWaitOverall = 0;

        foreach ($queues as $queue) {
            $metrics = $this->getQueueRuntimeMetrics($queue, $domainName, $agentMap);
            $queueCards[] = $metrics;

            $totalWaiting += $metrics['waiting_count'];
            $totalActive += $metrics['active_calls_count'];
            if ($metrics['longest_wait_seconds'] > $longestWaitOverall) {
                $longestWaitOverall = $metrics['longest_wait_seconds'];
            }
        }

        // Aggregate agent states
        $agentSummary = [
            'total' => $allAgents->count(),
            'available' => 0,
            'in_call' => 0,
            'on_break' => 0,
            'logged_out' => 0,
        ];

        // Query FreeSWITCH agent list to get real-time status
        $rawAgents = $this->executeListCommand('callcenter_config agent list');
        $rawAgentStatus = [];
        foreach ($rawAgents as $r) {
            if (!empty($r['name'])) {
                $rawAgentStatus[$r['name']] = [
                    'status' => $r['status'] ?? 'Logged Out',
                    'state' => $r['state'] ?? 'Waiting',
                    'contact' => $r['contact'] ?? '',
                ];
            }
        }

        $liveAgentList = [];
        foreach ($allAgents as $agent) {
            $uuid = $agent->call_center_agent_uuid;
            $fsData = $rawAgentStatus[$uuid] ?? [
                'status' => $agent->agent_status ?: 'Logged Out',
                'state' => 'Waiting',
                'contact' => $agent->agent_contact ?: '',
            ];

            $status = $fsData['status'];
            $state = $fsData['state'];

            if ($status === 'Available' && $state === 'In a queue call') {
                $effectiveState = 'In Call';
                $agentSummary['in_call']++;
            } elseif ($status === 'Available') {
                $effectiveState = 'Available';
                $agentSummary['available']++;
            } elseif ($status === 'On Break') {
                $effectiveState = 'On Break';
                $agentSummary['on_break']++;
            } else {
                $effectiveState = 'Logged Out';
                $agentSummary['logged_out']++;
            }

            $liveAgentList[] = [
                'agent_uuid' => $uuid,
                'name' => $agent->agent_name,
                'extension' => $agent->agent_id ?: '',
                'status' => $status,
                'state' => $state,
                'effective_state' => $effectiveState,
                'contact' => $fsData['contact'],
            ];
        }

        return [
            'summary' => [
                'total_queues' => $queues->count(),
                'total_waiting' => $totalWaiting,
                'total_active_calls' => $totalActive,
                'longest_wait_seconds' => $longestWaitOverall,
                'longest_wait_formatted' => $this->formatSeconds($longestWaitOverall),
                'agents' => $agentSummary,
                'refreshed_at' => Carbon::now()->toIso8601String(),
            ],
            'queues' => $queueCards,
            'agents' => $liveAgentList,
        ];
    }

    /**
     * Compute metrics and SLA prediction for a specific queue.
     */
    public function getQueueRuntimeMetrics(CallCenterQueues $queue, string $domainName, Collection $agentMap): array
    {
        $settings = ContactCenterQueueSetting::where('queue_uuid', $queue->call_center_queue_uuid)->first();
        $slaTargetSeconds = $settings?->sla_seconds ?? 20;
        $maxWaitSeconds = $settings?->max_wait_time_seconds ?? 180;
        $abandonThreshold = $settings?->abandon_alert_threshold ?? 15.0;

        // Query FreeSWITCH queue members
        $queueIdentifier = sprintf('%s@%s', $queue->queue_extension, $domainName);
        $members = $this->executeListCommand("callcenter_config queue list members {$queueIdentifier}");

        $waitingCount = 0;
        $tryingCount = 0;
        $answeredCount = 0;
        $longestWait = 0;
        $totalWait = 0;
        $now = time();

        $activeCalls = [];

        foreach ($members as $m) {
            $state = $m['state'] ?? 'Waiting';
            $joinedEpoch = (int) ($m['joined_epoch'] ?? 0);
            $waitSec = $joinedEpoch > 0 ? max(0, $now - $joinedEpoch) : 0;
            $servingAgentUuid = $m['serving_agent'] ?? null;
            $servingAgent = $servingAgentUuid ? ($agentMap->get($servingAgentUuid)?->agent_name ?: $servingAgentUuid) : null;

            if ($state === 'Waiting') {
                $waitingCount++;
                $totalWait += $waitSec;
                if ($waitSec > $longestWait) {
                    $longestWait = $waitSec;
                }
            } elseif ($state === 'Trying') {
                $tryingCount++;
            } elseif ($state === 'Answered') {
                $answeredCount++;
            }

            $activeCalls[] = [
                'channel_uuid' => $m['session_uuid'] ?? $m['uuid'] ?? null,
                'caller_number' => $m['cid_number'] ?? 'Unknown',
                'caller_name' => $m['cid_name'] ?? 'Caller',
                'state' => $state,
                'wait_seconds' => $waitSec,
                'wait_formatted' => $this->formatSeconds($waitSec),
                'serving_agent_uuid' => $servingAgentUuid,
                'serving_agent_name' => $servingAgent,
                'sla_breached' => ($state === 'Waiting' && $waitSec > $slaTargetSeconds),
            ];
        }

        $avgWait = $waitingCount > 0 ? (int) round($totalWait / $waitingCount) : 0;

        // Abandon Risk calculation
        $abandonRisk = 'LOW';
        if ($longestWait > ($slaTargetSeconds * 1.5)) {
            $abandonRisk = 'MEDIUM';
        }
        if ($longestWait > ($maxWaitSeconds * 0.75)) {
            $abandonRisk = 'HIGH';
        }
        if ($longestWait >= $maxWaitSeconds && $maxWaitSeconds > 0) {
            $abandonRisk = 'CRITICAL';
        }

        // SLA Prediction: Is queue at risk of missing SLA?
        $slaAtRisk = ($waitingCount > 2 && $longestWait >= ($slaTargetSeconds * 0.8));

        return [
            'queue_uuid' => $queue->call_center_queue_uuid,
            'queue_name' => $queue->queue_name,
            'queue_extension' => $queue->queue_extension,
            'queue_strategy' => $queue->queue_strategy,
            'waiting_count' => $waitingCount,
            'trying_count' => $tryingCount,
            'active_calls_count' => $answeredCount,
            'total_in_queue' => $waitingCount + $tryingCount + $answeredCount,
            'longest_wait_seconds' => $longestWait,
            'longest_wait_formatted' => $this->formatSeconds($longestWait),
            'avg_wait_seconds' => $avgWait,
            'avg_wait_formatted' => $this->formatSeconds($avgWait),
            'sla_target_seconds' => $slaTargetSeconds,
            'sla_at_risk' => $slaAtRisk,
            'abandon_risk' => $abandonRisk,
            'calls' => $activeCalls,
        ];
    }

    /**
     * Parse FreeSWITCH pipe-delimited output.
     */
    protected function executeListCommand(string $command): array
    {
        try {
            $output = $this->esl->executeCommand("api {$command}", false);
            if (empty($output) || !is_string($output)) {
                return [];
            }

            $lines = explode("\n", trim($output));
            if (count($lines) < 2) {
                return [];
            }

            $headers = explode('|', trim(array_shift($lines)));
            $rows = [];

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '+OK')) {
                    continue;
                }

                $values = explode('|', $trimmed);
                if (count($values) === count($headers)) {
                    $rows[] = array_combine($headers, $values);
                }
            }

            return $rows;
        } catch (\Throwable $e) {
            Log::warning("QueueIntelligenceService ESL execute error: " . $e->getMessage());
            return [];
        }
    }

    protected function formatSeconds(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $rem = $seconds % 60;
        return sprintf('%02d:%02d', $minutes, $rem);
    }
}
