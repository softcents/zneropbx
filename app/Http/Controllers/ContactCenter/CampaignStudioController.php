<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\ContactCenterCampaign;
use App\Models\ContactCenterCampaignLead;
use App\Models\CallCenterQueues;
use App\Models\BasicDialerContactList;
use App\Services\ContactCenter\CampaignDialerEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CampaignStudioController extends Controller
{
    protected CampaignDialerEngineService $dialerService;

    public function __construct(CampaignDialerEngineService $dialerService)
    {
        $this->dialerService = $dialerService;
    }

    /**
     * Display the Campaign Studio.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid');

        $campaigns = ContactCenterCampaign::where('domain_uuid', $domainUuid)
            ->with(['queue:call_center_queue_uuid,queue_name,queue_extension'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($camp) {
                return $this->dialerService->getCampaignTelemetry($camp);
            });

        $queues = CallCenterQueues::where('domain_uuid', $domainUuid)
            ->get(['call_center_queue_uuid', 'queue_name', 'queue_extension']);

        // Optional basic dialer contact lists if available
        $contactLists = [];
        if (class_exists(BasicDialerContactList::class)) {
            try {
                $contactLists = BasicDialerContactList::where('domain_uuid', $domainUuid)
                    ->get(['basic_dialer_contact_list_uuid', 'name']);
            } catch (\Throwable $e) {}
        }

        return Inertia::render('ContactCenter/CampaignStudio', [
            'campaigns' => $campaigns,
            'queues' => $queues,
            'contactLists' => $contactLists,
        ]);
    }

    /**
     * Store a new campaign.
     */
    public function store(Request $request)
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'dialer_mode' => 'required|in:preview,progressive,predictive',
            'queue_uuid' => 'nullable|uuid',
            'contact_list_uuid' => 'nullable|uuid',
            'caller_id_name' => 'nullable|string|max:50',
            'caller_id_number' => 'nullable|string|max:30',
            'pacing_ratio' => 'nullable|numeric|min:1.0|max:5.0',
            'max_abandon_rate' => 'nullable|numeric|min:0.5|max:10.0',
            'ring_timeout_seconds' => 'nullable|integer|min:10|max:60',
            'description' => 'nullable|string',
        ]);

        $campaign = ContactCenterCampaign::create(array_merge($validated, [
            'domain_uuid' => $domainUuid,
            'status' => 'draft',
            'pacing_ratio' => $validated['pacing_ratio'] ?? 1.20,
            'max_abandon_rate' => $validated['max_abandon_rate'] ?? 3.00,
            'ring_timeout_seconds' => $validated['ring_timeout_seconds'] ?? 25,
        ]));

        return response()->json([
            'success' => true,
            'message' => __('Campaign created successfully.'),
            'campaign' => $campaign,
        ]);
    }

    /**
     * Start a campaign.
     */
    public function start(ContactCenterCampaign $campaign)
    {
        if ($campaign->domain_uuid !== session('domain_uuid')) {
            abort(403);
        }

        $campaign->status = 'running';
        $campaign->save();

        // Trigger immediate outbound batch
        $batchResult = $this->dialerService->processOutboundBatch($campaign);

        return response()->json([
            'success' => true,
            'message' => __('Campaign started.'),
            'telemetry' => $this->dialerService->getCampaignTelemetry($campaign),
            'batch' => $batchResult,
        ]);
    }

    /**
     * Pause a campaign.
     */
    public function pause(ContactCenterCampaign $campaign)
    {
        if ($campaign->domain_uuid !== session('domain_uuid')) {
            abort(403);
        }

        $campaign->status = 'paused';
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => __('Campaign paused.'),
            'telemetry' => $this->dialerService->getCampaignTelemetry($campaign),
        ]);
    }

    /**
     * Stop a campaign.
     */
    public function stop(ContactCenterCampaign $campaign)
    {
        if ($campaign->domain_uuid !== session('domain_uuid')) {
            abort(403);
        }

        $campaign->status = 'completed';
        $campaign->save();

        return response()->json([
            'success' => true,
            'message' => __('Campaign completed/stopped.'),
            'telemetry' => $this->dialerService->getCampaignTelemetry($campaign),
        ]);
    }

    /**
     * Import leads manually or in batch.
     */
    public function addLeads(Request $request, ContactCenterCampaign $campaign)
    {
        if ($campaign->domain_uuid !== session('domain_uuid')) {
            abort(403);
        }

        $request->validate([
            'leads' => 'required|array|min:1',
            'leads.*.phone_number' => 'required|string',
            'leads.*.first_name' => 'nullable|string',
            'leads.*.last_name' => 'nullable|string',
            'leads.*.company' => 'nullable|string',
        ]);

        $domainUuid = session('domain_uuid');
        $added = 0;

        foreach ($request->input('leads') as $leadData) {
            ContactCenterCampaignLead::create([
                'campaign_uuid' => $campaign->campaign_uuid,
                'domain_uuid' => $domainUuid,
                'phone_number' => $leadData['phone_number'],
                'first_name' => $leadData['first_name'] ?? null,
                'last_name' => $leadData['last_name'] ?? null,
                'company' => $leadData['company'] ?? null,
                'status' => 'pending',
            ]);
            $added++;
        }

        $campaign->increment('total_leads', $added);

        return response()->json([
            'success' => true,
            'message' => __(':count leads added successfully.', ['count' => $added]),
            'total_leads' => $campaign->total_leads,
        ]);
    }

    /**
     * Live telemetry for active campaign.
     */
    public function telemetry(ContactCenterCampaign $campaign)
    {
        if ($campaign->domain_uuid !== session('domain_uuid')) {
            abort(403);
        }

        return response()->json([
            'success' => true,
            'telemetry' => $this->dialerService->getCampaignTelemetry($campaign),
        ]);
    }
}
