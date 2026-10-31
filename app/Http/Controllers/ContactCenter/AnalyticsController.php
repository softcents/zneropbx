<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CallCenterQueues;
use App\Services\ContactCenter\ContactCenterAnalyticsService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    protected ContactCenterAnalyticsService $analyticsService;

    public function __construct(ContactCenterAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the Contact Center Analytics & SLA Intelligence Dashboard.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid');
        $period = $request->query('period', 'today');
        $queueUuid = $request->query('queue_uuid');

        $kpis = $this->analyticsService->getExecutiveKpis($domainUuid, $period, $queueUuid);
        $intervals = $this->analyticsService->getIntervalMetrics($domainUuid, $period, $queueUuid);
        $inboundFunnel = $this->analyticsService->getInboundFunnel($domainUuid, $period);
        $outboundFunnel = $this->analyticsService->getOutboundCampaignFunnel($domainUuid, $period);
        $queuePerformance = $this->analyticsService->getQueuePerformance($domainUuid, $period);
        $agentLeaderboard = $this->analyticsService->getAgentLeaderboard($domainUuid, $period);

        $queues = CallCenterQueues::where('domain_uuid', $domainUuid)
            ->select('call_center_queue_uuid', 'queue_name', 'queue_extension')
            ->get();

        return Inertia::render('ContactCenter/Analytics', [
            'kpis' => $kpis,
            'intervals' => $intervals,
            'inboundFunnel' => $inboundFunnel,
            'outboundFunnel' => $outboundFunnel,
            'queuePerformance' => $queuePerformance,
            'agentLeaderboard' => $agentLeaderboard,
            'queues' => $queues,
            'filters' => [
                'period' => $period,
                'queue_uuid' => $queueUuid,
            ],
        ]);
    }

    /**
     * Export interval metrics to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $domainUuid = session('domain_uuid');
        $period = $request->query('period', 'today');
        $queueUuid = $request->query('queue_uuid');

        $intervals = $this->analyticsService->getIntervalMetrics($domainUuid, $period, $queueUuid);

        $filename = "contact_center_analytics_{$period}_" . date('Y-m-d_His') . ".csv";

        return response()->streamDownload(function () use ($intervals) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Time Interval',
                'Offered Calls',
                'Answered Calls',
                'Abandoned Calls',
                'Service Level (%)',
                'Avg Speed of Answer (sec)',
                'Avg Handle Time (sec)',
            ]);

            foreach ($intervals as $row) {
                fputcsv($handle, [
                    $row['label'],
                    $row['offered'],
                    $row['answered'],
                    $row['abandoned'],
                    $row['sla_percent'] . '%',
                    $row['avg_wait_sec'],
                    $row['avg_handle_sec'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
