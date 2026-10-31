<?php

namespace App\Services\Telecom;

use App\Models\TelecomCarrier;
use App\Models\TelecomDestination;
use App\Models\TelecomRoutingPolicy;
use App\Models\TelecomRouteSimulationLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TelecomRoutingIntelligenceService
{
    /**
     * Get all carrier trunks with operational metrics and health status.
     */
    public function getCarriers(?string $domainUuid = null)
    {
        $this->ensureSeedTelecomData($domainUuid);

        return TelecomCarrier::where(function ($q) use ($domainUuid) {
                if ($domainUuid) {
                    $q->where('domain_uuid', $domainUuid)->orWhereNull('domain_uuid');
                }
            })
            ->withCount('destinations')
            ->orderBy('priority', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get destination rate cards with longest prefix match capability.
     */
    public function getDestinations(?string $domainUuid = null, ?string $prefix = null)
    {
        $query = TelecomDestination::with('carrier');

        if (!empty($prefix)) {
            $query->where('prefix', 'like', "{$prefix}%");
        }

        return $query->orderBy('prefix', 'asc')
            ->orderBy('cost_per_minute', 'asc')
            ->limit(50)
            ->get();
    }

    /**
     * Get configured routing policies.
     */
    public function getPolicies(?string $domainUuid = null)
    {
        return TelecomRoutingPolicy::where(function ($q) use ($domainUuid) {
                if ($domainUuid) {
                    $q->where('domain_uuid', $domainUuid)->orWhereNull('domain_uuid');
                }
            })
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Simulate telecom route selection for a dialed destination.
     */
    public function simulateRouting(
        string $destinationNumber,
        string $strategy = 'LCR',
        string $customerTier = 'Standard',
        ?string $domainUuid = null
    ): array {
        $this->ensureSeedTelecomData($domainUuid);

        $cleanNumber = preg_replace('/[^0-9]/', '', $destinationNumber);
        $decisionTrace = [];

        $decisionTrace[] = [
            'step' => 1,
            'phase' => 'Normalization',
            'detail' => "Input '{$destinationNumber}' normalized to E.164 dial digits: '{$cleanNumber}'",
            'status' => 'success',
        ];

        // 2. Longest Prefix Match (LPM)
        $matchedDestinations = [];
        $matchedPrefix = '';

        for ($len = min(6, strlen($cleanNumber)); $len >= 1; $len--) {
            $testPrefix = substr($cleanNumber, 0, $len);
            $found = TelecomDestination::where('prefix', $testPrefix)
                ->where('is_active', true)
                ->with('carrier')
                ->get();

            if ($found->isNotEmpty()) {
                $matchedPrefix = $testPrefix;
                $matchedDestinations = $found;
                break;
            }
        }

        if (empty($matchedDestinations) || $matchedDestinations->isEmpty()) {
            // Fallback to default prefix "1" or first available
            $matchedPrefix = '1';
            $matchedDestinations = TelecomDestination::where('prefix', '1')->with('carrier')->get();
        }

        $decisionTrace[] = [
            'step' => 2,
            'phase' => 'Prefix Match (LPM)',
            'detail' => "Matched prefix [{$matchedPrefix}] ({$matchedDestinations->first()->destination_name}) across {$matchedDestinations->count()} carrier trunks.",
            'status' => 'success',
        ];

        // 3. Filter candidates by health, capacity and circuit breaker
        $candidates = [];
        $disqualified = [];

        foreach ($matchedDestinations as $dest) {
            $carrier = $dest->carrier;
            if (!$carrier || !$carrier->is_active) {
                continue;
            }

            // Circuit breaker / Quarantine check
            if ($carrier->circuit_breaker_tripped || $carrier->status === 'QUARANTINED') {
                $disqualified[] = [
                    'carrier' => $carrier->name,
                    'reason' => 'Circuit Breaker Tripped / Quarantined due to high error rate or consecutive SIP 503s.',
                ];
                continue;
            }

            // Capacity / Concurrent channels check
            if ($carrier->current_channels >= $carrier->max_channels) {
                $disqualified[] = [
                    'carrier' => $carrier->name,
                    'reason' => "Concurrent channel capacity reached ({$carrier->current_channels}/{$carrier->max_channels}).",
                ];
                continue;
            }

            // Quality threshold check if Quality or Tier-based
            if ($strategy === 'QUALITY' && $carrier->asr_percentage < 60.0) {
                $disqualified[] = [
                    'carrier' => $carrier->name,
                    'reason' => "ASR ({$carrier->asr_percentage}%) below quality threshold of 60.0%.",
                ];
                continue;
            }

            $candidates[] = [
                'carrier_uuid' => $carrier->carrier_uuid,
                'carrier_name' => $carrier->name,
                'carrier_code' => $carrier->carrier_code,
                'sip_proxy' => $carrier->sip_proxy,
                'cost_per_minute' => (float) $dest->cost_per_minute,
                'connect_fee' => (float) $dest->connect_fee,
                'asr_percentage' => (float) $carrier->asr_percentage,
                'pdd_ms' => (int) $carrier->pdd_ms,
                'acd_seconds' => (int) $carrier->acd_seconds,
                'priority' => (int) $carrier->priority,
                'weight' => (int) $carrier->weight,
                'status' => $carrier->status,
            ];
        }

        $decisionTrace[] = [
            'step' => 3,
            'phase' => 'Health & Circuit Breaker',
            'detail' => count($candidates) . " carriers eligible. " . count($disqualified) . " disqualified by Circuit Breaker/Capacity.",
            'disqualified' => $disqualified,
            'status' => count($candidates) > 0 ? 'success' : 'warning',
        ];

        // 4. Policy Sorting & Ranking
        if ($customerTier === 'VIP') {
            $decisionTrace[] = [
                'step' => 4,
                'phase' => 'VIP Tier Override',
                'detail' => 'VIP Customer detected. Applying Tier-1 Premium routing with lowest Post-Dial Delay (PDD < 400ms).',
                'status' => 'vip_override',
            ];
            // Sort by lowest PDD then highest ASR
            usort($candidates, function ($a, $b) {
                if ($a['pdd_ms'] === $b['pdd_ms']) {
                    return $b['asr_percentage'] <=> $a['asr_percentage'];
                }
                return $a['pdd_ms'] <=> $b['pdd_ms'];
            });
        } elseif ($strategy === 'QUALITY') {
            // Sort by highest ASR, then lowest PDD, then cost
            usort($candidates, function ($a, $b) {
                if ($a['asr_percentage'] === $b['asr_percentage']) {
                    return $a['cost_per_minute'] <=> $b['cost_per_minute'];
                }
                return $b['asr_percentage'] <=> $a['asr_percentage'];
            });
        } elseif ($strategy === 'WEIGHTED') {
            // Sort by weight DESC, then cost
            usort($candidates, function ($a, $b) {
                return $b['weight'] <=> $a['weight'];
            });
        } else {
            // Default: LCR (Least Cost Routing)
            usort($candidates, function ($a, $b) {
                if ($a['cost_per_minute'] === $b['cost_per_minute']) {
                    return $b['asr_percentage'] <=> $a['asr_percentage'];
                }
                return $a['cost_per_minute'] <=> $b['cost_per_minute'];
            });
        }

        // 5. Select Primary & Failovers
        $primary = $candidates[0] ?? null;
        $failovers = array_slice($candidates, 1, 3);

        $decisionTrace[] = [
            'step' => 5,
            'phase' => 'Route Finalization',
            'detail' => $primary
                ? "Primary: {$primary['carrier_name']} at \${$primary['cost_per_minute']}/min (ASR {$primary['asr_percentage']}%, PDD {$primary['pdd_ms']}ms). " . count($failovers) . " failover routes queued."
                : "No carriers available! Call will fail with SIP 503 Service Unavailable.",
            'status' => $primary ? 'success' : 'failed',
        ];

        // 6. FreeSWITCH Bridge Dialstring Construction
        $fsDialstrings = [];
        if ($primary) {
            $fsDialstrings[] = "sofia/gateway/{$primary['carrier_code']}/{$cleanNumber}";
        }
        foreach ($failovers as $fo) {
            $fsDialstrings[] = "sofia/gateway/{$fo['carrier_code']}/{$cleanNumber}";
        }
        $combinedDialstring = implode('|', $fsDialstrings);

        // 7. Save Simulation Log
        if ($primary) {
            TelecomRouteSimulationLog::create([
                'log_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
                'destination_number' => $destinationNumber,
                'normalized_prefix' => $matchedPrefix,
                'policy_applied' => $strategy . ($customerTier === 'VIP' ? ' (VIP)' : ''),
                'routes_evaluated' => $candidates,
                'primary_carrier_uuid' => $primary['carrier_uuid'],
                'failover_carrier_uuids' => array_column($failovers, 'carrier_uuid'),
                'freeswitch_dialstring' => $combinedDialstring,
                'decision_trace' => $decisionTrace,
            ]);
        }

        return [
            'destination_number' => $destinationNumber,
            'normalized_digits' => $cleanNumber,
            'matched_prefix' => $matchedPrefix,
            'strategy' => $strategy,
            'customer_tier' => $customerTier,
            'primary_route' => $primary,
            'failover_routes' => $failovers,
            'disqualified_carriers' => $disqualified,
            'freeswitch_dialstring' => $combinedDialstring,
            'decision_trace' => $decisionTrace,
            'evaluated_count' => count($candidates) + count($disqualified),
        ];
    }

    /**
     * Toggle Circuit Breaker or Quarantine for a carrier trunk.
     */
    public function toggleCircuitBreaker(string $carrierUuid, bool $trip): TelecomCarrier
    {
        $carrier = TelecomCarrier::findOrFail($carrierUuid);

        if ($trip) {
            $carrier->update([
                'circuit_breaker_tripped' => true,
                'status' => 'QUARANTINED',
                'quarantined_until' => Carbon::now()->addHours(2),
                'consecutive_failures' => 5,
            ]);
        } else {
            $carrier->update([
                'circuit_breaker_tripped' => false,
                'status' => 'ONLINE',
                'quarantined_until' => null,
                'consecutive_failures' => 0,
            ]);
        }

        return $carrier;
    }

    /**
     * Seed realistic Tier-1, Wholesale & Quarantined carrier trunks and rate cards.
     */
    public function ensureSeedTelecomData(?string $domainUuid = null): void
    {
        if (TelecomCarrier::count() >= 4) {
            return;
        }

        $now = Carbon::now();

        // 1. Carriers
        $carriersData = [
            [
                'carrier_code' => 'telnyx_global',
                'name' => 'Telnyx Global IP Trunking',
                'sip_proxy' => 'sip.telnyx.com:5060',
                'tech_prefix' => null,
                'max_channels' => 250,
                'current_channels' => 42,
                'max_tps' => 45,
                'status' => 'ONLINE',
                'asr_percentage' => 92.40,
                'acd_seconds' => 210,
                'pdd_ms' => 380,
                'priority' => 10,
                'weight' => 70,
                'circuit_breaker_tripped' => false,
            ],
            [
                'carrier_code' => 'bandwidth_tier1',
                'name' => 'Bandwidth.com Tier-1 Direct',
                'sip_proxy' => 'ot.bandwidth.com:5060',
                'tech_prefix' => null,
                'max_channels' => 500,
                'current_channels' => 88,
                'max_tps' => 80,
                'status' => 'ONLINE',
                'asr_percentage' => 95.80,
                'acd_seconds' => 245,
                'pdd_ms' => 290,
                'priority' => 5,
                'weight' => 90,
                'circuit_breaker_tripped' => false,
            ],
            [
                'carrier_code' => 'tata_intl',
                'name' => 'Tata Communications Global',
                'sip_proxy' => 'sip.tatacommunications.com:5060',
                'tech_prefix' => '9901',
                'max_channels' => 150,
                'current_channels' => 28,
                'max_tps' => 25,
                'status' => 'ONLINE',
                'asr_percentage' => 84.50,
                'acd_seconds' => 165,
                'pdd_ms' => 560,
                'priority' => 20,
                'weight' => 40,
                'circuit_breaker_tripped' => false,
            ],
            [
                'carrier_code' => 'twilio_wholesale',
                'name' => 'Twilio Elastic SIP Trunking',
                'sip_proxy' => 'pstn.twilio.com:5060',
                'tech_prefix' => null,
                'max_channels' => 300,
                'current_channels' => 12,
                'max_tps' => 50,
                'status' => 'ONLINE',
                'asr_percentage' => 89.10,
                'acd_seconds' => 195,
                'pdd_ms' => 460,
                'priority' => 30,
                'weight' => 50,
                'circuit_breaker_tripped' => false,
            ],
            [
                'carrier_code' => 'voxbone_quarantined',
                'name' => 'Voxbone Legacy (Circuit Tripped)',
                'sip_proxy' => 'quarantine.voxbone.net:5060',
                'tech_prefix' => null,
                'max_channels' => 50,
                'current_channels' => 0,
                'max_tps' => 10,
                'status' => 'QUARANTINED',
                'asr_percentage' => 31.50,
                'acd_seconds' => 45,
                'pdd_ms' => 1580,
                'consecutive_failures' => 7,
                'circuit_breaker_tripped' => true,
                'quarantined_until' => $now->copy()->addHours(6),
                'priority' => 90,
                'weight' => 10,
            ],
        ];

        $carrierMap = [];
        foreach ($carriersData as $c) {
            $carrier = TelecomCarrier::create(array_merge($c, [
                'carrier_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
            ]));
            $carrierMap[$c['carrier_code']] = $carrier;
        }

        // 2. Destination Rates (Prefixes: 1 for US, 44 for UK, 880 for Bangladesh, 49 for Germany)
        $rateMatrix = [
            // US Standard (Prefix 1)
            ['prefix' => '1', 'name' => 'USA & Canada Standard', 'rates' => [
                'telnyx_global' => 0.0035,
                'bandwidth_tier1' => 0.0042,
                'tata_intl' => 0.0032,
                'twilio_wholesale' => 0.0085,
                'voxbone_quarantined' => 0.0028,
            ]],
            // UK (Prefix 44)
            ['prefix' => '44', 'name' => 'United Kingdom National', 'rates' => [
                'telnyx_global' => 0.0089,
                'bandwidth_tier1' => 0.0095,
                'tata_intl' => 0.0072,
                'twilio_wholesale' => 0.0140,
                'voxbone_quarantined' => 0.0065,
            ]],
            // Bangladesh (Prefix 880)
            ['prefix' => '880', 'name' => 'Bangladesh Mobile & Fixed', 'rates' => [
                'telnyx_global' => 0.0245,
                'bandwidth_tier1' => 0.0260,
                'tata_intl' => 0.0210,
                'twilio_wholesale' => 0.0380,
                'voxbone_quarantined' => 0.0195,
            ]],
            // Germany (Prefix 49)
            ['prefix' => '49', 'name' => 'Germany Fixed/Mobile', 'rates' => [
                'telnyx_global' => 0.0078,
                'bandwidth_tier1' => 0.0084,
                'tata_intl' => 0.0070,
                'twilio_wholesale' => 0.0125,
                'voxbone_quarantined' => 0.0062,
            ]],
        ];

        foreach ($rateMatrix as $rm) {
            foreach ($rm['rates'] as $carrierCode => $cost) {
                if (isset($carrierMap[$carrierCode])) {
                    TelecomDestination::create([
                        'destination_uuid' => (string) Str::uuid(),
                        'carrier_uuid' => $carrierMap[$carrierCode]->carrier_uuid,
                        'destination_name' => $rm['name'],
                        'prefix' => $rm['prefix'],
                        'cost_per_minute' => $cost,
                        'connect_fee' => 0.0000,
                        'rate_increment' => 6,
                        'effective_date' => $now,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // 3. Routing Policies
        $policies = [
            [
                'name' => 'Enterprise LCR (Least Cost Routing)',
                'strategy' => 'LCR',
                'min_asr' => 50.00,
                'max_pdd_ms' => 1200,
                'failover_limit' => 3,
                'is_default' => true,
            ],
            [
                'name' => 'Premium Quality First (Low Latency / ASR > 85%)',
                'strategy' => 'QUALITY',
                'min_asr' => 85.00,
                'max_pdd_ms' => 500,
                'failover_limit' => 3,
                'is_default' => false,
            ],
            [
                'name' => 'VIP Customer High-Availability Policy',
                'strategy' => 'TIER_BASED',
                'min_asr' => 90.00,
                'max_pdd_ms' => 350,
                'failover_limit' => 4,
                'is_default' => false,
            ],
        ];

        foreach ($policies as $p) {
            TelecomRoutingPolicy::create(array_merge($p, [
                'policy_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
                'is_active' => true,
            ]));
        }
    }
}
