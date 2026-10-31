<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsBiSavedView;
use App\Models\AnalyticsBiScheduledExport;
use App\Models\AnalyticsBiCarrierQosBenchmark;
use App\Services\OperationsIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OperationsIntelligenceController extends Controller
{
    protected OperationsIntelligenceService $service;

    public function __construct(OperationsIntelligenceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the Cross-Domain Operations Intelligence & BI Studio.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        if (!$domainUuid) {
            abort(404, 'Domain context not found');
        }

        $this->service->ensureInitialDataset($domainUuid);

        $crossDomainMetrics = $this->service->getCrossDomainOperationalMetrics($domainUuid);
        $carrierBenchmarks = AnalyticsBiCarrierQosBenchmark::where('domain_uuid', $domainUuid)->orderBy('margin_percent', 'desc')->get();
        $savedViews = AnalyticsBiSavedView::where('domain_uuid', $domainUuid)->orderBy('is_pinned', 'desc')->get();
        $scheduledExports = AnalyticsBiScheduledExport::where('domain_uuid', $domainUuid)->orderBy('created_at', 'desc')->get();
        $initialForecast = $this->service->forecastCapacity($domainUuid, 'concurrent_channels', 14);

        return Inertia::render('Analytics/OperationsIntelligence', [
            'crossDomainMetrics' => $crossDomainMetrics,
            'carrierBenchmarks' => $carrierBenchmarks,
            'savedViews' => $savedViews,
            'scheduledExports' => $scheduledExports,
            'initialForecast' => $initialForecast,
            'metrics' => [
                'composite_operational_health_score' => 99.2,
                'monthly_billed_revenue_usd' => $crossDomainMetrics['financial']['gross_revenue_usd'],
                'gross_margin_percent' => $crossDomainMetrics['financial']['gross_margin_pct'],
                'active_cluster_nodes' => $crossDomainMetrics['infrastructure']['cluster_nodes_online'],
                'avg_carrier_asr' => $crossDomainMetrics['telecom']['asr_percent'],
                'contact_center_sl_pct' => $crossDomainMetrics['contact_center']['service_level_pct'],
            ],
        ]);
    }

    /**
     * Store a new scheduled BI report export.
     */
    public function storeExportJob(Request $request)
    {
        $validated = $request->validate([
            'report_title' => 'required|string|max:120',
            'format' => 'required|string|in:csv,json,xlsx,pdf',
            'frequency' => 'required|string|in:daily,weekly,monthly,ad_hoc',
            'recipient_email' => 'required|email',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $export = $this->service->createScheduledExport($domainUuid, $validated);

        return response()->json($export);
    }

    /**
     * Save a custom analytical view.
     */
    public function storeSavedView(Request $request)
    {
        $validated = $request->validate([
            'view_name' => 'required|string|max:120',
            'category' => 'required|string',
            'filter_criteria_json' => 'nullable|array',
            'is_pinned' => 'nullable|boolean',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $view = $this->service->saveCustomView($domainUuid, $validated);

        return response()->json($view);
    }

    /**
     * Execute capacity forecasting projection.
     */
    public function runCapacityForecast(Request $request)
    {
        $validated = $request->validate([
            'metric' => 'required|string|in:concurrent_channels,agent_headcount,sms_volume,ai_tokens',
            'days_ahead' => 'required|integer|min:7|max:90',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->forecastCapacity($domainUuid, $validated['metric'], $validated['days_ahead']);

        return response()->json($result);
    }
}
