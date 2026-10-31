<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Models\TelecomCarrier;
use App\Services\Telecom\TelecomRoutingIntelligenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoutingIntelligenceController extends Controller
{
    protected TelecomRoutingIntelligenceService $routingService;

    public function __construct(TelecomRoutingIntelligenceService $routingService)
    {
        $this->routingService = $routingService;
    }

    /**
     * Render the Telecom Routing Intelligence Studio.
     */
    public function index(Request $request): Response
    {
        $domainUuid = session('domain_uuid');

        $carriers = $this->routingService->getCarriers($domainUuid);
        $destinations = $this->routingService->getDestinations($domainUuid);
        $policies = $this->routingService->getPolicies($domainUuid);

        $totalCarriers = $carriers->count();
        $onlineCarriers = $carriers->where('status', 'ONLINE')->count();
        $quarantinedCarriers = $carriers->where('circuit_breaker_tripped', true)->count();
        $avgAsr = $totalCarriers > 0 ? round($carriers->avg('asr_percentage'), 1) : 0;
        $avgPdd = $totalCarriers > 0 ? round($carriers->avg('pdd_ms')) : 0;
        $activeChannels = $carriers->sum('current_channels');
        $maxChannels = $carriers->sum('max_channels');

        return Inertia::render('Telecom/RoutingIntelligence', [
            'carriers' => $carriers,
            'destinations' => $destinations,
            'policies' => $policies,
            'stats' => [
                'total_carriers' => $totalCarriers,
                'online_carriers' => $onlineCarriers,
                'quarantined_carriers' => $quarantinedCarriers,
                'avg_asr' => $avgAsr,
                'avg_pdd' => $avgPdd,
                'active_channels' => $activeChannels,
                'max_channels' => $maxChannels,
                'utilization_pct' => $maxChannels > 0 ? round(($activeChannels / $maxChannels) * 100, 1) : 0,
            ],
        ]);
    }

    /**
     * Run interactive route simulation and dry-run explanation.
     */
    public function simulate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'destination_number' => 'required|string',
            'strategy' => 'nullable|string|in:LCR,QUALITY,WEIGHTED,TIER_BASED',
            'customer_tier' => 'nullable|string|in:Standard,VIP,Wholesale',
        ]);

        $domainUuid = session('domain_uuid');
        $result = $this->routingService->simulateRouting(
            $validated['destination_number'],
            $validated['strategy'] ?? 'LCR',
            $validated['customer_tier'] ?? 'Standard',
            $domainUuid
        );

        return response()->json([
            'success' => true,
            'simulation' => $result,
        ]);
    }

    /**
     * Toggle carrier circuit breaker or manual quarantine.
     */
    public function toggleCircuitBreaker(Request $request, string $carrierUuid): JsonResponse
    {
        $validated = $request->validate([
            'trip' => 'required|boolean',
        ]);

        $carrier = $this->routingService->toggleCircuitBreaker($carrierUuid, $validated['trip']);

        return response()->json([
            'success' => true,
            'message' => $carrier->circuit_breaker_tripped
                ? "Circuit Breaker TRIPPED for {$carrier->name}. Carrier quarantined from outbound routing."
                : "Circuit Breaker RESET for {$carrier->name}. Trunk restored to ONLINE state.",
            'carrier' => $carrier,
        ]);
    }
}
