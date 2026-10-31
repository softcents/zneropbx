<?php

namespace App\Services\ContactCenter;

use App\Models\CallCenterQueues;
use App\Models\CallCenterAgents;
use App\Models\ContactCenterQueueSetting;
use App\Models\ContactCenterSkill;
use App\Models\ContactCenterAgentSkill;
use App\Models\ContactCenterQueueSkill;
use App\Models\ContactCenterCallback;
use App\Services\FreeswitchEslService;
use Illuminate\Support\Facades\DB;

class AcdRoutingIntelligenceService
{
    protected FreeswitchEslService $esl;

    public function __construct(FreeswitchEslService $esl)
    {
        $this->esl = $esl;
    }

    /**
     * Get queue routing configuration, required skills, and SLA settings.
     */
    public function getQueueRoutingPolicy(CallCenterQueues $queue): array
    {
        $setting = ContactCenterQueueSetting::where('queue_uuid', $queue->call_center_queue_uuid)->first();
        
        $requiredSkills = ContactCenterQueueSkill::where('queue_uuid', $queue->call_center_queue_uuid)
            ->with('skill')
            ->get();

        return [
            'queue_uuid' => $queue->call_center_queue_uuid,
            'queue_name' => $queue->queue_name,
            'queue_extension' => $queue->queue_extension,
            'strategy' => $queue->queue_strategy,
            'sla_seconds' => $setting?->sla_seconds ?? 20,
            'sla_target_percentage' => $setting?->sla_target_percentage ?? 80.0,
            'max_wait_time_seconds' => $setting?->max_wait_time_seconds ?? 180,
            'abandon_alert_threshold' => $setting?->abandon_alert_threshold ?? 15.0,
            'smart_callback_enabled' => (bool) ($setting?->smart_callback_enabled ?? false),
            'smart_callback_threshold_seconds' => $setting?->smart_callback_threshold_seconds ?? 120,
            'vip_boost_score' => $setting?->vip_boost_score ?? 10,
            'overflow_queue_uuid' => $setting?->overflow_queue_uuid,
            'overflow_callers_count' => $setting?->overflow_callers_count ?? 10,
            'required_skills' => $requiredSkills->map(fn ($qs) => [
                'queue_skill_uuid' => $qs->queue_skill_uuid,
                'skill_uuid' => $qs->skill_uuid,
                'name' => $qs->skill?->name ?? 'Skill',
                'category' => $qs->skill?->category ?? 'General',
                'min_proficiency' => $qs->min_proficiency,
                'is_mandatory' => (bool) $qs->is_mandatory,
            ]),
        ];
    }

    /**
     * Save or update queue routing policy, SLA, overflow, and required skills.
     */
    public function saveQueueRoutingPolicy(CallCenterQueues $queue, array $data): ContactCenterQueueSetting
    {
        $setting = ContactCenterQueueSetting::updateOrCreate(
            ['queue_uuid' => $queue->call_center_queue_uuid],
            [
                'domain_uuid' => $queue->domain_uuid,
                'sla_seconds' => $data['sla_seconds'] ?? 20,
                'sla_target_percentage' => $data['sla_target_percentage'] ?? 80.0,
                'max_wait_time_seconds' => $data['max_wait_time_seconds'] ?? 180,
                'abandon_alert_threshold' => $data['abandon_alert_threshold'] ?? 15.0,
                'smart_callback_enabled' => (bool) ($data['smart_callback_enabled'] ?? false),
                'smart_callback_threshold_seconds' => $data['smart_callback_threshold_seconds'] ?? 120,
                'vip_boost_score' => $data['vip_boost_score'] ?? 10,
                'overflow_queue_uuid' => !empty($data['overflow_queue_uuid']) ? $data['overflow_queue_uuid'] : null,
                'overflow_callers_count' => $data['overflow_callers_count'] ?? 10,
            ]
        );

        // Sync required skills
        if (isset($data['required_skills']) && is_array($data['required_skills'])) {
            $existingSkillUuids = [];
            foreach ($data['required_skills'] as $skillReq) {
                if (empty($skillReq['skill_uuid'])) continue;
                $existingSkillUuids[] = $skillReq['skill_uuid'];

                ContactCenterQueueSkill::updateOrCreate(
                    [
                        'queue_uuid' => $queue->call_center_queue_uuid,
                        'skill_uuid' => $skillReq['skill_uuid'],
                    ],
                    [
                        'domain_uuid' => $queue->domain_uuid,
                        'min_proficiency' => $skillReq['min_proficiency'] ?? 1,
                        'is_mandatory' => (bool) ($skillReq['is_mandatory'] ?? true),
                    ]
                );
            }

            // Remove any removed skills
            ContactCenterQueueSkill::where('queue_uuid', $queue->call_center_queue_uuid)
                ->whereNotIn('skill_uuid', $existingSkillUuids)
                ->delete();
        }

        // Apply skill tiers to FreeSWITCH queue tiers
        $this->syncQueueAgentTiersBySkills($queue);

        return $setting;
    }

    /**
     * Compute skills-based ranking for agents assigned to this queue
     * and update FreeSWITCH tier level/position accordingly.
     */
    public function syncQueueAgentTiersBySkills(CallCenterQueues $queue): void
    {
        $queueSkills = ContactCenterQueueSkill::where('queue_uuid', $queue->call_center_queue_uuid)->get();
        if ($queueSkills->isEmpty()) {
            return;
        }

        $domainUuid = $queue->domain_uuid;
        $agents = CallCenterAgents::where('domain_uuid', $domainUuid)->get();

        foreach ($agents as $agent) {
            $agentSkills = ContactCenterAgentSkill::where('agent_uuid', $agent->call_center_agent_uuid)->get()->keyBy('skill_uuid');

            $qualifies = true;
            $totalScore = 0;

            foreach ($queueSkills as $qs) {
                $hasSkill = $agentSkills->get($qs->skill_uuid);
                $prof = $hasSkill ? $hasSkill->proficiency_level : 0;

                if ($qs->is_mandatory && $prof < $qs->min_proficiency) {
                    $qualifies = false;
                    break;
                }
                $totalScore += $prof * 10;
            }

            // Tier level in mod_callcenter (1 = highest priority, 9 = lowest priority)
            // Higher score translates to lower tier number (Tier 1 vs Tier 5)
            $tierLevel = $qualifies ? max(1, 10 - (int) floor($totalScore / 20)) : 9;
            $tierPosition = max(1, 100 - $totalScore);

            // Update database tier
            DB::table('v_call_center_tiers')
                ->where('call_center_queue_uuid', $queue->call_center_queue_uuid)
                ->where('call_center_agent_uuid', $agent->call_center_agent_uuid)
                ->update([
                    'tier_level' => $tierLevel,
                    'tier_position' => $tierPosition,
                ]);

            // Sync live to FreeSWITCH ESL if connected
            try {
                if ($this->esl->connect()) {
                    $cmd = "callcenter_config tier set level {$queue->queue_name} {$agent->agent_name} {$tierLevel}";
                    $this->esl->api($cmd);
                    $cmdPos = "callcenter_config tier set position {$queue->queue_name} {$agent->agent_name} {$tierPosition}";
                    $this->esl->api($cmdPos);
                }
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Calculate VIP boost score for incoming caller.
     */
    public function calculateCallerScore(string $callerNumber, CallCenterQueues $queue, int $waitTimeSeconds): array
    {
        $setting = ContactCenterQueueSetting::where('queue_uuid', $queue->call_center_queue_uuid)->first();
        $vipBoost = $setting?->vip_boost_score ?? 10;

        // Check if number is in phonebook or VIP list
        $isVip = DB::table('v_contacts')
            ->join('v_contact_phones', 'v_contacts.contact_uuid', '=', 'v_contact_phones.contact_uuid')
            ->where('v_contacts.domain_uuid', $queue->domain_uuid)
            ->where(function ($q) use ($callerNumber) {
                $q->where('phone_number', 'like', "%{$callerNumber}%")
                  ->orWhere('contact_category', 'VIP')
                  ->orWhere('contact_type', 'VIP');
            })
            ->exists();

        $calculatedScore = $waitTimeSeconds + ($isVip ? ($vipBoost * 60) : 0);

        return [
            'caller_number' => $callerNumber,
            'is_vip' => $isVip,
            'vip_boost_applied' => $isVip ? ($vipBoost * 60) : 0,
            'base_wait_seconds' => $waitTimeSeconds,
            'calculated_queue_score' => $calculatedScore,
        ];
    }
}
