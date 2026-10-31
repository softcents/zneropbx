<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CallCenterAgents;
use App\Models\CallCenterQueues;
use App\Models\Extensions;
use App\Models\SupervisorAuditLog;
use App\Services\ContactCenter\AgentStateManager;
use App\Services\ContactCenter\QueueIntelligenceService;
use App\Services\ContactCenter\SupervisorLiveControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SupervisorCommandCenterController extends Controller
{
    protected QueueIntelligenceService $queueIntelligence;
    protected SupervisorLiveControlService $liveControl;
    protected AgentStateManager $agentStateManager;

    public function __construct(
        QueueIntelligenceService $queueIntelligence,
        SupervisorLiveControlService $liveControl,
        AgentStateManager $agentStateManager
    ) {
        $this->queueIntelligence = $queueIntelligence;
        $this->liveControl = $liveControl;
        $this->agentStateManager = $agentStateManager;
    }

    /**
     * Display the live Supervisor Command Center wallboard.
     */
    public function index(Request $request): Response
    {
        $domainUuid = session('domain_uuid');
        $domainName = session('domain_name');

        $wallboardData = $this->queueIntelligence->getLiveWallboard($domainUuid, $domainName);
        $breakReasons = $this->agentStateManager->getBreakReasons($domainUuid);

        // Fetch extensions in domain so supervisor can pick their listening device
        $extensions = Extensions::where('domain_uuid', $domainUuid)
            ->where('enabled', 'true')
            ->orderBy('extension')
            ->get(['extension_uuid', 'extension', 'effective_caller_id_name', 'description']);

        return Inertia::render('ContactCenter/SupervisorCommandCenter', [
            'initialData' => $wallboardData,
            'breakReasons' => $breakReasons,
            'extensions' => $extensions,
            'routes' => [
                'live_data' => route('contact_center.live_wallboard'),
                'action' => route('contact_center.supervisor.action'),
                'set_status' => route('contact_center.agent.set_status'),
                'timeline' => url('/api/contact-center/agent'),
                'audit_logs' => route('contact_center.supervisor.audit_logs'),
            ],
            'permissions' => [
                'can_listen' => true,
                'can_whisper' => true,
                'can_barge' => true,
                'can_takeover' => true,
                'can_force_logout' => true,
            ]
        ]);
    }

    /**
     * API: Get live wallboard metrics snapshot for real-time polling.
     */
    public function liveWallboard(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $domainName = session('domain_name');

        $data = $this->queueIntelligence->getLiveWallboard($domainUuid, $domainName);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * API: Execute supervisor live intervention (Listen, Whisper, Barge, Takeover, Force Drop).
     */
    public function executeAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:listen,whisper,barge,takeover,force_drop,force_logout',
            'channel_uuid' => 'nullable|string',
            'supervisor_extension' => 'nullable|string',
            'destination_extension' => 'nullable|string',
            'agent_uuid' => 'nullable|string',
            'agent_name' => 'nullable|string',
            'queue_uuid' => 'nullable|string',
            'reason_code' => 'nullable|string',
            'reason_description' => 'nullable|string',
        ]);

        $userUuid = session('user_uuid');
        $action = $validated['action'];

        // Authorization check
        if (!$this->liveControl->authorize($userUuid, $validated['queue_uuid'] ?? null, $action)) {
            return response()->json([
                'success' => false,
                'error' => 'You do not have permission to execute this supervisor action.',
            ], 403);
        }

        $result = match ($action) {
            'listen' => $this->liveControl->listen(
                $validated['supervisor_extension'],
                $validated['channel_uuid'],
                $userUuid,
                $validated['agent_uuid'] ?? null,
                $validated['agent_name'] ?? null,
                $validated['queue_uuid'] ?? null
            ),
            'whisper' => $this->liveControl->whisper(
                $validated['supervisor_extension'],
                $validated['channel_uuid'],
                $userUuid,
                $validated['agent_uuid'] ?? null,
                $validated['agent_name'] ?? null,
                $validated['queue_uuid'] ?? null
            ),
            'barge' => $this->liveControl->barge(
                $validated['supervisor_extension'],
                $validated['channel_uuid'],
                $userUuid,
                $validated['agent_uuid'] ?? null,
                $validated['agent_name'] ?? null,
                $validated['queue_uuid'] ?? null
            ),
            'takeover' => $this->liveControl->takeover(
                $validated['channel_uuid'],
                $validated['destination_extension'] ?: $validated['supervisor_extension'],
                $userUuid,
                $validated['agent_uuid'] ?? null,
                $validated['agent_name'] ?? null,
                $validated['queue_uuid'] ?? null
            ),
            'force_drop' => $this->liveControl->forceDrop(
                $validated['channel_uuid'],
                $userUuid,
                $validated['reason_description'] ?? 'Supervisor termination',
                $validated['agent_uuid'] ?? null,
                $validated['agent_name'] ?? null,
                $validated['queue_uuid'] ?? null
            ),
            'force_logout' => $this->liveControl->forceLogoutAgent(
                $validated['agent_uuid'],
                $userUuid,
                $validated['reason_code'] ?? 'SUPERVISOR_OVERRIDE',
                $validated['reason_description'] ?? 'Forced by supervisor'
            ),
        };

        return response()->json($result);
    }

    /**
     * API: Set agent status (Available, Logged Out, On Break).
     */
    public function setAgentStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_uuid' => 'required|string',
            'status' => 'required|in:Available,Logged Out,On Break,Wrap-Up',
            'break_reason_code' => 'nullable|string',
        ]);

        try {
            $result = $this->agentStateManager->setStatus(
                $validated['agent_uuid'],
                $validated['status'],
                $validated['break_reason_code'] ?? null
            );

            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API: Get agent timeline for today.
     */
    public function getAgentTimeline(Request $request, string $agentUuid): JsonResponse
    {
        $timeline = $this->agentStateManager->getAgentTimeline($agentUuid, $request->query('date'));

        return response()->json([
            'success' => true,
            'timeline' => $timeline,
        ]);
    }

    /**
     * API: Get supervisor audit logs.
     */
    public function auditLogs(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $logs = SupervisorAuditLog::where('domain_uuid', $domainUuid)
            ->with('supervisor:user_uuid,username')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }
}
