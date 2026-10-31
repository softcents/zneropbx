<?php

namespace App\Services\ContactCenter;

use App\Models\ContactCenterCampaign;
use App\Models\ContactCenterCampaignLead;
use App\Models\CallCenterQueues;
use App\Models\CallCenterAgents;
use App\Services\FreeswitchEslService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampaignDialerEngineService
{
    protected FreeswitchEslService $esl;

    public function __construct(FreeswitchEslService $esl)
    {
        $this->esl = $esl;
    }

    /**
     * Compute current pacing telemetry and status for an outbound campaign.
     */
    public function getCampaignTelemetry(ContactCenterCampaign $campaign): array
    {
        $domainUuid = $campaign->domain_uuid;

        // Query queue agents status
        $queueAgents = DB::table('v_call_center_tiers')
            ->join('v_call_center_agents', 'v_call_center_tiers.call_center_agent_uuid', '=', 'v_call_center_agents.call_center_agent_uuid')
            ->where('v_call_center_tiers.call_center_queue_uuid', $campaign->queue_uuid)
            ->where('v_call_center_agents.domain_uuid', $domainUuid)
            ->select([
                'v_call_center_agents.call_center_agent_uuid',
                'v_call_center_agents.agent_name',
                'v_call_center_agents.agent_status',
                'v_call_center_agents.agent_state',
            ])
            ->get();

        $availableAgents = $queueAgents->filter(fn ($a) => 
            in_array(strtolower($a->agent_status ?? ''), ['available', 'on demand', 'logged in']) &&
            in_array(strtolower($a->agent_state ?? ''), ['waiting', 'idle'])
        )->count();

        $busyAgents = $queueAgents->filter(fn ($a) => 
            in_array(strtolower($a->agent_state ?? ''), ['in a queue call', 'busy', 'ringing'])
        )->count();

        $wrapUpAgents = $queueAgents->filter(fn ($a) => 
            in_array(strtolower($a->agent_state ?? ''), ['wrap-up', 'after-call'])
        )->count();

        // Calculate abandon rate
        $dialed = max(1, $campaign->dialed_leads);
        $abandonRate = round(($campaign->abandoned_leads / $dialed) * 100, 2);

        // Calculate recommended lines to dial based on mode
        $linesToDial = 0;
        $isSafeMode = false;

        if ($campaign->status === 'running') {
            if ($campaign->dialer_mode === 'preview') {
                $linesToDial = $availableAgents;
            } elseif ($campaign->dialer_mode === 'progressive') {
                $linesToDial = $availableAgents;
            } elseif ($campaign->dialer_mode === 'predictive') {
                // If abandon rate breaches threshold, auto fallback to progressive safe mode
                if ($abandonRate >= $campaign->max_abandon_rate) {
                    $linesToDial = $availableAgents;
                    $isSafeMode = true;
                } else {
                    $effectiveAgents = $availableAgents + ($wrapUpAgents * 0.4);
                    $linesToDial = (int) ceil($effectiveAgents * $campaign->pacing_ratio);
                }
            }
        }

        $progressPercentage = $campaign->total_leads > 0 
            ? round(($campaign->dialed_leads / $campaign->total_leads) * 100, 1) 
            : 0;

        return [
            'campaign_uuid' => $campaign->campaign_uuid,
            'name' => $campaign->name,
            'status' => $campaign->status,
            'dialer_mode' => $campaign->dialer_mode,
            'pacing_ratio' => $campaign->pacing_ratio,
            'max_abandon_rate' => $campaign->max_abandon_rate,
            'current_abandon_rate' => $abandonRate,
            'is_safe_mode' => $isSafeMode,
            'total_leads' => $campaign->total_leads,
            'dialed_leads' => $campaign->dialed_leads,
            'connected_leads' => $campaign->connected_leads,
            'abandoned_leads' => $campaign->abandoned_leads,
            'progress_percentage' => $progressPercentage,
            'agents' => [
                'total' => $queueAgents->count(),
                'available' => $availableAgents,
                'busy' => $busyAgents,
                'wrap_up' => $wrapUpAgents,
            ],
            'recommended_lines_to_dial' => $linesToDial,
        ];
    }

    /**
     * Dispatch an outbound dial batch for a campaign.
     */
    public function processOutboundBatch(ContactCenterCampaign $campaign, int $batchSize = 5): array
    {
        if ($campaign->status !== 'running') {
            return ['status' => 'skipped', 'reason' => 'Campaign is not running'];
        }

        $telemetry = $this->getCampaignTelemetry($campaign);
        $linesAllowed = min($batchSize, max(1, $telemetry['recommended_lines_to_dial']));

        if ($telemetry['agents']['available'] === 0 && $campaign->dialer_mode !== 'predictive') {
            return ['status' => 'idle', 'message' => 'No agents available'];
        }

        $leads = ContactCenterCampaignLead::where('campaign_uuid', $campaign->campaign_uuid)
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->limit($linesAllowed)
            ->get();

        $dialsTriggered = 0;

        foreach ($leads as $lead) {
            $lead->status = 'dialing';
            $lead->attempts += 1;
            $lead->last_dialed_at = now();
            $lead->call_uuid = (string) Str::uuid();
            $lead->save();

            $campaign->increment('dialed_leads');
            $dialsTriggered++;

            // If FreeSWITCH ESL is connected, trigger originate command
            try {
                if ($this->esl->connect()) {
                    $cid = $campaign->caller_id_number ?: '0000000000';
                    $queueName = $campaign->queue?->queue_name ?? 'outbound_campaign';
                    $destNumber = preg_replace('/[^0-9]/', '', $lead->phone_number);

                    // FreeSWITCH originate command into queue
                    $originateCmd = "originate {origination_caller_id_number={$cid},campaign_uuid={$campaign->campaign_uuid},lead_uuid={$lead->lead_uuid}}loopback/{$destNumber} &callcenter({$queueName})";
                    $this->esl->bgapi($originateCmd);
                }
            } catch (\Throwable $e) {
                logger("Campaign dialer originate error: " . $e->getMessage());
            }
        }

        return [
            'status' => 'success',
            'dials_triggered' => $dialsTriggered,
            'remaining_pending' => ContactCenterCampaignLead::where('campaign_uuid', $campaign->campaign_uuid)->where('status', 'pending')->count(),
        ];
    }
}
