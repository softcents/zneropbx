<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CallCenterQueues;
use App\Models\CallCenterAgents;
use App\Models\ContactCenterSkill;
use App\Models\ContactCenterAgentSkill;
use App\Models\ContactCenterCallback;
use App\Services\ContactCenter\AcdRoutingIntelligenceService;
use App\Services\ContactCenter\CallbackEngineService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AcdRoutingController extends Controller
{
    protected AcdRoutingIntelligenceService $acdService;
    protected CallbackEngineService $callbackService;

    public function __construct(
        AcdRoutingIntelligenceService $acdService,
        CallbackEngineService $callbackService
    ) {
        $this->acdService = $acdService;
        $this->callbackService = $callbackService;
    }

    /**
     * Display the ACD & Skills Routing Manager.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid');

        $queues = CallCenterQueues::where('domain_uuid', $domainUuid)
            ->get()
            ->map(function ($q) {
                return $this->acdService->getQueueRoutingPolicy($q);
            });

        $skills = ContactCenterSkill::where('domain_uuid', $domainUuid)
            ->withCount('agentSkills')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $agents = CallCenterAgents::where('domain_uuid', $domainUuid)
            ->with(['agentSkills.skill'])
            ->get()
            ->map(fn ($a) => [
                'agent_uuid' => $a->call_center_agent_uuid,
                'name' => $a->agent_name,
                'agent_id' => $a->agent_id,
                'status' => $a->agent_status,
                'skills' => $a->agentSkills->map(fn ($as) => [
                    'agent_skill_uuid' => $as->agent_skill_uuid,
                    'skill_uuid' => $as->skill_uuid,
                    'name' => $as->skill?->name ?? 'Skill',
                    'category' => $as->skill?->category ?? 'General',
                    'proficiency_level' => $as->proficiency_level,
                ]),
            ]);

        $callbacks = ContactCenterCallback::where('domain_uuid', $domainUuid)
            ->with(['queue:call_center_queue_uuid,queue_name,queue_extension'])
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return Inertia::render('ContactCenter/AcdRouting', [
            'queues' => $queues,
            'skills' => $skills,
            'agents' => $agents,
            'callbacks' => $callbacks,
        ]);
    }

    /**
     * Store or update a skill definition.
     */
    public function saveSkill(Request $request)
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'skill_uuid' => 'nullable|uuid',
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:50',
            'category' => 'required|string|in:Language,Technical,Department,Tier,General',
            'description' => 'nullable|string|max:255',
        ]);

        $skill = ContactCenterSkill::updateOrCreate(
            [
                'skill_uuid' => $validated['skill_uuid'] ?? null,
                'domain_uuid' => $domainUuid,
            ],
            array_merge($validated, ['domain_uuid' => $domainUuid])
        );

        return response()->json([
            'success' => true,
            'message' => __('Skill saved successfully.'),
            'skill' => $skill,
        ]);
    }

    /**
     * Delete a skill definition.
     */
    public function deleteSkill(ContactCenterSkill $skill)
    {
        if ($skill->domain_uuid !== session('domain_uuid')) {
            abort(403);
        }

        $skill->agentSkills()->delete();
        $skill->queueSkills()->delete();
        $skill->delete();

        return response()->json([
            'success' => true,
            'message' => __('Skill removed successfully.'),
        ]);
    }

    /**
     * Update skills and proficiencies for an agent.
     */
    public function updateAgentSkills(Request $request)
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'agent_uuid' => 'required|uuid',
            'skills' => 'required|array',
            'skills.*.skill_uuid' => 'required|uuid',
            'skills.*.proficiency_level' => 'required|integer|min:1|max:10',
        ]);

        $agent = CallCenterAgents::where('domain_uuid', $domainUuid)
            ->where('call_center_agent_uuid', $validated['agent_uuid'])
            ->firstOrFail();

        $activeSkillUuids = [];
        foreach ($validated['skills'] as $s) {
            $activeSkillUuids[] = $s['skill_uuid'];
            ContactCenterAgentSkill::updateOrCreate(
                [
                    'domain_uuid' => $domainUuid,
                    'agent_uuid' => $agent->call_center_agent_uuid,
                    'skill_uuid' => $s['skill_uuid'],
                ],
                [
                    'proficiency_level' => $s['proficiency_level'],
                ]
            );
        }

        // Delete unselected
        ContactCenterAgentSkill::where('domain_uuid', $domainUuid)
            ->where('agent_uuid', $agent->call_center_agent_uuid)
            ->whereNotIn('skill_uuid', $activeSkillUuids)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => __('Agent skills updated successfully.'),
        ]);
    }

    /**
     * Save queue ACD policy, SLA, overflow, and required skills.
     */
    public function saveQueuePolicy(Request $request)
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'queue_uuid' => 'required|uuid',
            'sla_seconds' => 'required|integer|min:5|max:300',
            'sla_target_percentage' => 'required|numeric|min:10|max:100',
            'max_wait_time_seconds' => 'required|integer|min:10|max:1800',
            'abandon_alert_threshold' => 'required|numeric|min:1|max:100',
            'smart_callback_enabled' => 'required|boolean',
            'smart_callback_threshold_seconds' => 'required|integer|min:10|max:600',
            'vip_boost_score' => 'required|integer|min:0|max:100',
            'overflow_queue_uuid' => 'nullable|uuid',
            'overflow_callers_count' => 'required|integer|min:1|max:100',
            'required_skills' => 'nullable|array',
            'required_skills.*.skill_uuid' => 'required|uuid',
            'required_skills.*.min_proficiency' => 'required|integer|min:1|max:10',
            'required_skills.*.is_mandatory' => 'required|boolean',
        ]);

        $queue = CallCenterQueues::where('domain_uuid', $domainUuid)
            ->where('call_center_queue_uuid', $validated['queue_uuid'])
            ->firstOrFail();

        $this->acdService->saveQueueRoutingPolicy($queue, $validated);

        return response()->json([
            'success' => true,
            'message' => __('Queue routing policy and skills updated.'),
            'policy' => $this->acdService->getQueueRoutingPolicy($queue),
        ]);
    }

    /**
     * Perform action on a pending callback (Trigger Now, Cancel).
     */
    public function callbackAction(Request $request, ContactCenterCallback $callback)
    {
        if ($callback->domain_uuid !== session('domain_uuid')) {
            abort(403);
        }

        $action = $request->input('action');

        if ($action === 'trigger') {
            $callback->next_attempt_at = now();
            $callback->save();
            $result = $this->callbackService->processPendingCallbacks();
            return response()->json([
                'success' => true,
                'message' => __('Callback call triggered.'),
                'result' => $result,
            ]);
        } elseif ($action === 'cancel') {
            $callback->status = 'cancelled';
            $callback->last_error = 'Cancelled by supervisor';
            $callback->save();
            return response()->json([
                'success' => true,
                'message' => __('Callback cancelled.'),
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid action.'], 400);
    }
}
