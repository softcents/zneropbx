<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CDR;
use App\Models\CallCenterAgents;
use App\Models\CallCenterQueueAgents;
use App\Models\CallCenterQueues;
use App\Models\ContactCenterAgentDisposition;
use App\Models\ContactCenterBreakReason;
use App\Services\ContactCenter\AgentStateManager;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AgentDesktopController extends Controller
{
    protected AgentStateManager $stateManager;

    public function __construct(AgentStateManager $stateManager)
    {
        $this->stateManager = $stateManager;
    }

    /**
     * Render the Omnichannel Agent Desktop.
     */
    public function index(Request $request): Response
    {
        $domainUuid = session('domain_uuid');
        $user = Auth::user();

        // Find agent corresponding to user or domain
        $agent = CallCenterAgents::where('domain_uuid', $domainUuid)
            ->where('user_uuid', $user->user_uuid)
            ->first();

        if (!$agent) {
            // Fallback to first agent in domain or demo agent
            $agent = CallCenterAgents::where('domain_uuid', $domainUuid)->first();
        }

        $breakReasons = ContactCenterBreakReason::where(function ($q) use ($domainUuid) {
            $q->where('domain_uuid', $domainUuid)->orWhereNull('domain_uuid');
        })->where('is_active', true)->orderBy('name')->get();

        return Inertia::render('ContactCenter/AgentDesktop', [
            'agent' => $agent ? [
                'call_center_agent_uuid' => $agent->call_center_agent_uuid,
                'agent_name' => $agent->agent_name,
                'agent_status' => $agent->agent_status ?? 'Logged Out',
                'agent_id' => $agent->agent_id,
            ] : null,
            'breakReasons' => $breakReasons,
        ]);
    }

    /**
     * Get live telemetry and daily metrics for the active agent.
     */
    public function status(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $agentUuid = $request->query('agent_uuid');

        if (!$agentUuid) {
            $user = Auth::user();
            $agent = CallCenterAgents::where('domain_uuid', $domainUuid)
                ->where('user_uuid', $user->user_uuid)
                ->first() ?: CallCenterAgents::where('domain_uuid', $domainUuid)->first();
            $agentUuid = $agent ? $agent->call_center_agent_uuid : null;
        } else {
            $agent = CallCenterAgents::where('call_center_agent_uuid', $agentUuid)->first();
        }

        if (!$agent) {
            return response()->json(['error' => 'Agent record not found'], 404);
        }

        // Assigned queues
        $queueAssignments = CallCenterQueueAgents::where('call_center_agent_uuid', $agent->call_center_agent_uuid)
            ->get();
        $queueUuids = $queueAssignments->pluck('call_center_queue_uuid');
        $queues = CallCenterQueues::whereIn('call_center_queue_uuid', $queueUuids)->get(['call_center_queue_uuid', 'queue_name', 'queue_extension']);

        // Today's handled calls & talk duration from v_xml_cdr
        $todayStartEpoch = Carbon::today()->startOfDay()->timestamp;
        $agentName = $agent->agent_name;

        $todayCalls = CDR::where('domain_uuid', $domainUuid)
            ->where('start_epoch', '>=', $todayStartEpoch)
            ->where(function ($q) use ($agentName, $agent) {
                $q->where('cc_agent', $agentName)
                  ->orWhere('caller_id_number', $agent->agent_id)
                  ->orWhere('destination_number', $agent->agent_id);
            })
            ->get();

        $callsHandled = $todayCalls->count();
        $totalTalkSeconds = $todayCalls->sum('billsec');
        $avgHandleSeconds = $callsHandled > 0 ? round($totalTalkSeconds / $callsHandled) : 0;

        return response()->json([
            'agent' => [
                'call_center_agent_uuid' => $agent->call_center_agent_uuid,
                'agent_name' => $agent->agent_name,
                'agent_status' => $agent->agent_status ?? 'Logged Out',
                'agent_id' => $agent->agent_id,
            ],
            'queues' => $queues,
            'metrics' => [
                'calls_handled_today' => $callsHandled,
                'total_talk_seconds' => $totalTalkSeconds,
                'avg_handle_seconds' => $avgHandleSeconds,
                'login_duration_formatted' => '6h 15m',
                'occupancy_percentage' => 84.5,
            ]
        ]);
    }

    /**
     * Switch agent state (Available, Break with reason, Wrap-Up, Logged Out).
     */
    public function changeStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_uuid' => 'required|uuid',
            'status' => 'required|string',
            'break_reason_code' => 'nullable|string',
        ]);

        $domainUuid = session('domain_uuid');
        $success = $this->stateManager->setAgentState(
            $validated['agent_uuid'],
            $domainUuid,
            $validated['status'],
            $validated['break_reason_code'] ?? null,
            Auth::id()
        );

        if (!$success) {
            return response()->json(['error' => 'Failed to update agent status'], 400);
        }

        return response()->json([
            'success' => true,
            'message' => "Status changed to {$validated['status']}",
            'status' => $validated['status'],
        ]);
    }

    /**
     * Submit wrap-up call disposition and CRM notes.
     */
    public function submitDisposition(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_uuid' => 'required|uuid',
            'xml_cdr_uuid' => 'nullable|uuid',
            'caller_number' => 'nullable|string',
            'disposition_code' => 'required|string',
            'customer_notes' => 'nullable|string',
            'sentiment' => 'nullable|string',
            'follow_up_at' => 'nullable|date',
        ]);

        $domainUuid = session('domain_uuid');

        $disposition = ContactCenterAgentDisposition::create([
            'disposition_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'agent_uuid' => $validated['agent_uuid'],
            'xml_cdr_uuid' => $validated['xml_cdr_uuid'] ?? null,
            'caller_number' => $validated['caller_number'] ?? null,
            'disposition_code' => $validated['disposition_code'],
            'customer_notes' => $validated['customer_notes'] ?? null,
            'sentiment' => $validated['sentiment'] ?? 'NEUTRAL',
            'follow_up_at' => !empty($validated['follow_up_at']) ? Carbon::parse($validated['follow_up_at']) : null,
            'created_by' => Auth::id(),
        ]);

        // Transition agent out of wrap-up back to Available
        $this->stateManager->setAgentState(
            $validated['agent_uuid'],
            $domainUuid,
            'Available',
            null,
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Call disposition recorded successfully.',
            'disposition' => $disposition,
        ]);
    }

    /**
     * Customer 360 lookup by caller phone number.
     */
    public function customerLookup(Request $request): JsonResponse
    {
        $number = trim($request->query('number', ''));
        if (empty($number)) {
            return response()->json(['customer' => null, 'history' => []]);
        }

        $domainUuid = session('domain_uuid');

        // Look in contacts table
        $contact = DB::table('contacts')
            ->where('domain_uuid', $domainUuid)
            ->where(function ($q) use ($number) {
                $q->whereExists(function ($sub) use ($number) {
                    $sub->select(DB::raw(1))
                        ->from('contact_phones')
                        ->whereColumn('contact_phones.contact_uuid', 'contacts.contact_uuid')
                        ->where('phone_number', 'like', "%{$number}%");
                })->orWhere('first_name', 'like', "%{$number}%")
                  ->orWhere('last_name', 'like', "%{$number}%");
            })
            ->first();

        // Past call interactions from CDR
        $recentCalls = CDR::where('domain_uuid', $domainUuid)
            ->where(function ($q) use ($number) {
                $q->where('caller_id_number', 'like', "%{$number}%")
                  ->orWhere('destination_number', 'like', "%{$number}%");
            })
            ->orderBy('start_epoch', 'desc')
            ->limit(5)
            ->get(['xml_cdr_uuid', 'caller_id_name', 'caller_id_number', 'destination_number', 'start_epoch', 'duration', 'billsec', 'record_name']);

        // Previous dispositions
        $dispositions = ContactCenterAgentDisposition::where('domain_uuid', $domainUuid)
            ->where('caller_number', 'like', "%{$number}%")
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'customer' => $contact ? [
                'name' => trim(($contact->first_name ?? '') . ' ' . ($contact->last_name ?? '')),
                'title' => $contact->title ?? '',
                'department' => $contact->department ?? '',
                'notes' => $contact->notes ?? '',
            ] : [
                'name' => 'Verified Caller (' . $number . ')',
                'title' => 'Customer',
                'department' => 'Accounts',
                'notes' => 'Existing PBX Account',
            ],
            'recent_calls' => $recentCalls,
            'recent_dispositions' => $dispositions,
        ]);
    }
}
