<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\ContactCenterIvrFlow;
use App\Services\ContactCenter\IvrJourneyStudioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class IvrStudioController extends Controller
{
    protected IvrJourneyStudioService $ivrService;

    public function __construct(IvrJourneyStudioService $ivrService)
    {
        $this->ivrService = $ivrService;
    }

    /**
     * Display the Inbound IVR Journey Studio & Visual Flow Builder.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid');
        $flows = $this->ivrService->getFlows($domainUuid);

        $selectedUuid = $request->query('flow_uuid') ?: ($flows->first()?->ivr_flow_uuid ?? null);
        $activeFlowDetails = $selectedUuid ? $this->ivrService->getFlow($domainUuid, $selectedUuid) : null;

        return Inertia::render('ContactCenter/IvrStudio', [
            'flows' => $flows,
            'activeFlow' => $activeFlowDetails,
            'filters' => [
                'flow_uuid' => $selectedUuid,
            ],
        ]);
    }

    /**
     * Create new IVR journey flow.
     */
    public function store(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'extension' => 'required|string|max:30',
            'description' => 'nullable|string|max:1000',
            'language_default' => 'nullable|string|max:20',
            'vip_boost_enabled' => 'nullable|boolean',
            'containment_rate_target' => 'nullable|numeric|min:0|max:100',
        ]);

        $flow = $this->ivrService->saveFlow($domainUuid, $validated);

        return response()->json([
            'success' => true,
            'message' => 'IVR flow created successfully.',
            'flow' => $flow,
        ]);
    }

    /**
     * Update IVR flow configuration.
     */
    public function update(Request $request, string $flowUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'extension' => 'required|string|max:30',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|string|in:active,draft,archived',
            'language_default' => 'nullable|string|max:20',
            'vip_boost_enabled' => 'nullable|boolean',
            'containment_rate_target' => 'nullable|numeric|min:0|max:100',
        ]);

        $flow = $this->ivrService->saveFlow($domainUuid, $validated, $flowUuid);

        return response()->json([
            'success' => true,
            'message' => 'IVR flow updated.',
            'flow' => $flow,
        ]);
    }

    /**
     * Interactive Dry-Run Call Journey Simulator.
     */
    public function simulate(Request $request, string $flowUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'caller_number' => 'required|string|max:50',
            'dtmf_sequence' => 'nullable|array',
            'language' => 'nullable|string|max:20',
        ]);

        $result = $this->ivrService->simulateCallJourney(
            $domainUuid,
            $flowUuid,
            $validated['caller_number'],
            $validated['dtmf_sequence'] ?? ['1'],
            $validated['language'] ?? 'en-US'
        );

        return response()->json($result);
    }

    /**
     * Export FreeSWITCH Dialplan XML for the flow.
     */
    public function exportXml(Request $request, string $flowUuid): Response
    {
        $domainUuid = session('domain_uuid');
        $flow = ContactCenterIvrFlow::where('domain_uuid', $domainUuid)
            ->where('ivr_flow_uuid', $flowUuid)
            ->firstOrFail();

        $xml = $this->ivrService->generateFreeSwitchDialplanXml($flow);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => "attachment; filename=\"freeswitch_ivr_{$flow->extension}.xml\"",
        ]);
    }
}
