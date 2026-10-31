<?php

namespace App\Http\Controllers\Messaging;

use App\Http\Controllers\Controller;
use App\Models\MessagingProviderConnector;
use App\Models\MessagingRoutingPolicy;
use App\Models\MessagingSuppressionDnd;
use App\Models\MessagingQueueItem;
use App\Models\MessagingVoiceSmsCampaign;
use App\Services\MessagingGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SmsVoiceStudioController extends Controller
{
    protected MessagingGatewayService $service;

    public function __construct(MessagingGatewayService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the SMS, Voice SMS & SMPP Gateway Studio.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        if (!$domainUuid) {
            abort(404, 'Domain context not found');
        }

        $this->service->ensureInitialDataset($domainUuid);

        $connectors = MessagingProviderConnector::where('domain_uuid', $domainUuid)->get();
        $policies = MessagingRoutingPolicy::where('domain_uuid', $domainUuid)->with(['primaryConnector', 'failoverConnector'])->orderBy('queue_priority', 'desc')->get();
        $suppressions = MessagingSuppressionDnd::where('domain_uuid', $domainUuid)->orderBy('opt_out_timestamp', 'desc')->get();
        $queueItems = MessagingQueueItem::where('domain_uuid', $domainUuid)->with('connector')->orderBy('created_at', 'desc')->limit(25)->get();
        $voiceCampaigns = MessagingVoiceSmsCampaign::where('domain_uuid', $domainUuid)->orderBy('created_at', 'desc')->get();

        $totalSent = $queueItems->count();
        $deliveredCount = $queueItems->where('dlr_status', 'DELIVRD')->count();
        $deliveryRate = $totalSent > 0 ? round(($deliveredCount / $totalSent) * 100, 1) : 99.2;
        $totalTps = $connectors->sum('tps_limit');
        $dndCount = $suppressions->count();
        $totalMargin = round($queueItems->sum('retail_billed_amount') - $queueItems->sum('cost_amount'), 4);

        return Inertia::render('Messaging/SmsVoiceStudio', [
            'connectors' => $connectors,
            'policies' => $policies,
            'suppressions' => $suppressions,
            'queueItems' => $queueItems,
            'voiceCampaigns' => $voiceCampaigns,
            'metrics' => [
                'total_sent' => $totalSent,
                'delivery_rate_pct' => $deliveryRate,
                'aggregate_tps_capacity' => $totalTps,
                'dnd_suppressed_count' => $dndCount,
                'active_voice_campaigns' => $voiceCampaigns->where('status', 'running')->count(),
                'total_margin_usd' => $totalMargin,
            ],
        ]);
    }

    /**
     * Dispatch SMS / Voice SMS message through routing engine.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_phone' => 'required|string',
            'sender_id' => 'required|string',
            'message_body' => 'required|string',
            'traffic_type' => 'required|string',
            'voice_tts_enabled' => 'nullable|boolean',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->simulateSend($validated, $domainUuid);

        return response()->json($result);
    }

    /**
     * Calculate SMS segments and character encoding.
     */
    public function calculateSegments(Request $request)
    {
        $validated = $request->validate([
            'message_body' => 'required|string',
        ]);

        $result = $this->service->calculateMessageSegments($validated['message_body']);

        return response()->json($result);
    }

    /**
     * Toggle Connector SMPP bind or REST state.
     */
    public function toggleBind(Request $request, string $connectorUuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->toggleConnectorBind($connectorUuid, $domainUuid);

        return response()->json($result);
    }

    /**
     * Add phone to DND suppression list.
     */
    public function addDnd(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string',
            'reason' => 'required|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $item = $this->service->addSuppression($validated['phone_number'], $validated['reason'], $domainUuid);

        return response()->json($item);
    }

    /**
     * Remove phone from DND suppression list.
     */
    public function removeDnd(Request $request, string $suppressionUuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $success = $this->service->removeSuppression($suppressionUuid, $domainUuid);

        return response()->json(['success' => $success]);
    }
}
