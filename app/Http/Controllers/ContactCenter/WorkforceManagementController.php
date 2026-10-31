<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CallCenterAgents;
use App\Models\ContactCenterShift;
use App\Services\ContactCenter\WorkforceManagementService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class WorkforceManagementController extends Controller
{
    protected WorkforceManagementService $wfmService;

    public function __construct(WorkforceManagementService $wfmService)
    {
        $this->wfmService = $wfmService;
    }

    /**
     * Render the Workforce Management & Real-Time Adherence Dashboard.
     */
    public function index(Request $request): Response
    {
        $domainUuid = session('domain_uuid');
        $shifts = $this->wfmService->getOrCreateDefaultShifts($domainUuid);

        $startDate = $request->query('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $rosterData = $this->wfmService->getWeeklyRoster($domainUuid, $startDate);
        $adherenceData = $this->wfmService->getRealTimeAdherence($domainUuid);

        return Inertia::render('ContactCenter/WorkforceManagement', [
            'shifts' => $shifts,
            'roster' => $rosterData,
            'adherence' => $adherenceData,
            'currentWeekStart' => $startDate,
        ]);
    }

    /**
     * Get weekly roster JSON for a date.
     */
    public function getRoster(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $startDate = $request->query('start_date', Carbon::now()->startOfWeek()->format('Y-m-d'));
        $roster = $this->wfmService->getWeeklyRoster($domainUuid, $startDate);

        return response()->json($roster);
    }

    /**
     * Assign shift to agent for a date.
     */
    public function saveSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agent_uuid' => 'required|uuid',
            'date' => 'required|date',
            'shift_uuid' => 'nullable|uuid',
            'activity' => 'nullable|string',
        ]);

        $domainUuid = session('domain_uuid');
        $schedule = $this->wfmService->assignShift(
            $domainUuid,
            $validated['agent_uuid'],
            $validated['date'],
            $validated['shift_uuid'] ?? null,
            $validated['activity'] ?? 'ON_QUEUE',
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Shift schedule saved successfully.',
            'schedule' => $schedule,
        ]);
    }

    /**
     * Calculate Erlang-C Contact Center Staffing.
     */
    public function calculateStaffing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'calls_per_hour' => 'required|numeric|min:1',
            'aht_seconds' => 'required|integer|min:10',
            'target_sla_percent' => 'nullable|numeric|min:10|max:100',
            'target_wait_seconds' => 'nullable|integer|min:1|max:600',
            'shrinkage_percent' => 'nullable|numeric|min:0|max:80',
        ]);

        $callsPerHour = (float) $validated['calls_per_hour'];
        $aht = (int) $validated['aht_seconds'];
        $sla = isset($validated['target_sla_percent']) ? (float) $validated['target_sla_percent'] : 80.0;
        $wait = isset($validated['target_wait_seconds']) ? (int) $validated['target_wait_seconds'] : 20;
        $shrinkage = isset($validated['shrinkage_percent']) ? ((float) $validated['shrinkage_percent'] / 100.0) : 0.25;

        $results = $this->wfmService->calculateErlangC(
            $callsPerHour,
            $aht,
            $sla,
            $wait,
            $shrinkage
        );

        return response()->json($results);
    }

    /**
     * Live Real-Time Adherence (RTA) telemetry.
     */
    public function liveAdherence(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $data = $this->wfmService->getRealTimeAdherence($domainUuid);

        return response()->json($data);
    }
}
