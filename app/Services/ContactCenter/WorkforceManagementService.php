<?php

namespace App\Services\ContactCenter;

use App\Models\CallCenterAgents;
use App\Models\ContactCenterAdherenceLog;
use App\Models\ContactCenterSchedule;
use App\Models\ContactCenterShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WorkforceManagementService
{
    /**
     * Get or initialize default shifts for a domain.
     */
    public function getOrCreateDefaultShifts(string $domainUuid)
    {
        $shifts = ContactCenterShift::where('domain_uuid', $domainUuid)->get();
        if ($shifts->isNotEmpty()) {
            return $shifts;
        }

        $defaults = [
            [
                'name' => 'Morning Shift',
                'start_time' => '08:00:00',
                'end_time' => '16:00:00',
                'break_duration_minutes' => 60,
                'color' => '#10B981', // Emerald
            ],
            [
                'name' => 'Day / Peak Shift',
                'start_time' => '10:00:00',
                'end_time' => '18:00:00',
                'break_duration_minutes' => 60,
                'color' => '#4F46E5', // Indigo
            ],
            [
                'name' => 'Evening Shift',
                'start_time' => '16:00:00',
                'end_time' => '00:00:00',
                'break_duration_minutes' => 60,
                'color' => '#F59E0B', // Amber
            ],
            [
                'name' => 'Night Shift',
                'start_time' => '00:00:00',
                'end_time' => '08:00:00',
                'break_duration_minutes' => 60,
                'color' => '#6366F1', // Violet
            ],
        ];

        foreach ($defaults as $item) {
            ContactCenterShift::create([
                'shift_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
                'name' => $item['name'],
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time'],
                'break_duration_minutes' => $item['break_duration_minutes'],
                'color' => $item['color'],
                'is_active' => true,
            ]);
        }

        return ContactCenterShift::where('domain_uuid', $domainUuid)->get();
    }

    /**
     * Get weekly roster matrix for a starting Monday.
     */
    public function getWeeklyRoster(string $domainUuid, string $startDate)
    {
        $start = Carbon::parse($startDate)->startOfWeek(); // Monday
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $start->copy()->addDays($i)->format('Y-m-d');
        }

        $agents = CallCenterAgents::where('domain_uuid', $domainUuid)->get();
        $schedules = ContactCenterSchedule::with('shift')
            ->where('domain_uuid', $domainUuid)
            ->whereBetween('schedule_date', [$dates[0], $dates[6]])
            ->get();

        $roster = [];
        foreach ($agents as $agent) {
            $agentDays = [];
            foreach ($dates as $date) {
                $sched = $schedules->first(function ($s) use ($agent, $date) {
                    return $s->agent_uuid === $agent->call_center_agent_uuid && $s->schedule_date->format('Y-m-d') === $date;
                });

                $agentDays[$date] = $sched ? [
                    'schedule_uuid' => $sched->schedule_uuid,
                    'shift_uuid' => $sched->shift_uuid,
                    'shift_name' => $sched->shift ? $sched->shift->name : 'Scheduled',
                    'shift_color' => $sched->shift ? $sched->shift->color : '#4F46E5',
                    'start_time' => $sched->start_time,
                    'end_time' => $sched->end_time,
                    'expected_activity' => $sched->expected_activity,
                ] : null;
            }

            $roster[] = [
                'agent_uuid' => $agent->call_center_agent_uuid,
                'agent_name' => $agent->agent_name,
                'agent_id' => $agent->agent_id,
                'days' => $agentDays,
            ];
        }

        return [
            'dates' => $dates,
            'roster' => $roster,
        ];
    }

    /**
     * Assign or update shift for an agent on a specific date.
     */
    public function assignShift(string $domainUuid, string $agentUuid, string $date, ?string $shiftUuid, string $activity = 'ON_QUEUE', ?string $userId = null)
    {
        $shift = $shiftUuid ? ContactCenterShift::where('shift_uuid', $shiftUuid)->first() : null;

        return ContactCenterSchedule::updateOrCreate(
            [
                'domain_uuid' => $domainUuid,
                'agent_uuid' => $agentUuid,
                'schedule_date' => $date,
            ],
            [
                'schedule_uuid' => (string) Str::uuid(),
                'shift_uuid' => $shiftUuid,
                'start_time' => $shift ? $shift->start_time : '09:00:00',
                'end_time' => $shift ? $shift->end_time : '17:00:00',
                'expected_activity' => $activity,
                'published' => true,
                'created_by' => $userId,
            ]
        );
    }

    /**
     * Erlang-C Contact Center Staffing Calculator
     *
     * @param float $callsPerHour Incoming call volume per hour (lambda)
     * @param int $ahtSeconds Average Handling Time in seconds (AHT)
     * @param float $targetSlaPercent Target Service Level (e.g., 80 for 80%)
     * @param int $targetWaitSeconds Target wait time in seconds (e.g., 20s)
     * @param float $shrinkagePercent Break/training/absence allowance (default 25%)
     */
    public function calculateErlangC(
        float $callsPerHour,
        int $ahtSeconds,
        float $targetSlaPercent = 80.0,
        int $targetWaitSeconds = 20,
        float $shrinkagePercent = 0.25
    ): array {
        // Traffic intensity A (in Erlangs)
        $arrivalRate = $callsPerHour / 3600; // calls per second
        $trafficIntensity = $arrivalRate * $ahtSeconds; // Erlangs A

        // Starting agents must be strictly greater than traffic intensity
        $minAgents = (int) ceil($trafficIntensity) + 1;
        $slaTargetRatio = $targetSlaPercent / 100.0;

        $chosenAgents = $minAgents;
        $achievedSla = 0.0;
        $probWait = 1.0;
        $asa = 0.0;

        // Iterate agents N until SLA target is achieved (up to max +100 agents)
        for ($n = $minAgents; $n <= $minAgents + 100; $n++) {
            $pw = $this->erlangCProbabilityOfWaiting($trafficIntensity, $n);
            // Probability call answered within targetWaitSeconds: SL = 1 - (Pw * exp(-(N - A) * t / AHT))
            $exponent = -($n - $trafficIntensity) * ($targetWaitSeconds / $ahtSeconds);
            $sl = 1.0 - ($pw * exp($exponent));

            if ($sl >= $slaTargetRatio || $n === $minAgents + 100) {
                $chosenAgents = $n;
                $achievedSla = round($sl * 100, 2);
                $probWait = round($pw * 100, 2);
                // Average Speed of Answer (ASA) = (Pw * AHT) / (N - A)
                $asa = ($n > $trafficIntensity) ? round(($pw * $ahtSeconds) / ($n - $trafficIntensity), 1) : 0;
                break;
            }
        }

        $occupancy = $chosenAgents > 0 ? round(($trafficIntensity / $chosenAgents) * 100, 1) : 0;
        $shrinkageFactor = max(0.01, 1.0 - $shrinkagePercent);
        $totalStaffWithShrinkage = (int) ceil($chosenAgents / $shrinkageFactor);

        return [
            'traffic_intensity_erlangs' => round($trafficIntensity, 2),
            'raw_agents_required' => $chosenAgents,
            'total_staff_with_shrinkage' => $totalStaffWithShrinkage,
            'achieved_service_level' => $achievedSla,
            'probability_of_waiting' => $probWait,
            'average_speed_of_answer_sec' => $asa,
            'agent_occupancy_percent' => $occupancy,
            'assumptions' => [
                'calls_per_hour' => $callsPerHour,
                'aht_seconds' => $ahtSeconds,
                'target_wait_seconds' => $targetWaitSeconds,
                'shrinkage_percent' => round($shrinkagePercent * 100, 1),
            ],
        ];
    }

    /**
     * Compute Erlang-C waiting probability P(W > 0).
     */
    protected function erlangCProbabilityOfWaiting(float $a, int $n): float
    {
        if ($n <= $a) {
            return 1.0;
        }

        // Sum for k = 0 to N-1 of (A^k / k!)
        $sum = 0.0;
        $term = 1.0;
        for ($k = 0; $k < $n; $k++) {
            if ($k > 0) {
                $term = $term * ($a / $k);
            }
            $sum += $term;
        }

        // (A^N / N!) * (N / (N - A))
        $termN = $term * ($a / $n);
        $lastTerm = $termN * ($n / ($n - $a));

        $denominator = $sum + $lastTerm;
        return $denominator > 0 ? ($lastTerm / $denominator) : 1.0;
    }

    /**
     * Evaluate Real-Time Adherence (RTA) for all active agents.
     */
    public function getRealTimeAdherence(string $domainUuid): array
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');

        $agents = CallCenterAgents::where('domain_uuid', $domainUuid)->get();
        $schedules = ContactCenterSchedule::where('domain_uuid', $domainUuid)
            ->where('schedule_date', $today)
            ->get();

        $adherentCount = 0;
        $totalEvaluated = 0;
        $agentAdherenceList = [];

        foreach ($agents as $agent) {
            $sched = $schedules->firstWhere('agent_uuid', $agent->call_center_agent_uuid);
            $actualStatus = $agent->agent_status ?: 'Logged Out';

            // Determine expected activity based on scheduled shift time
            $expectedActivity = 'OFF';
            if ($sched) {
                if ($currentTime >= $sched->start_time && $currentTime <= $sched->end_time) {
                    $expectedActivity = $sched->expected_activity ?: 'ON_QUEUE';
                }
            }

            // Check adherence
            $isAdherent = true;
            $driftReason = null;

            if ($expectedActivity === 'ON_QUEUE') {
                if (!in_array($actualStatus, ['Available', 'In Call', 'Wrap-Up'])) {
                    $isAdherent = false;
                    $driftReason = "Scheduled for Queue, but currently {$actualStatus}";
                }
            } elseif ($expectedActivity === 'BREAK') {
                if ($actualStatus !== 'On Break') {
                    $isAdherent = false;
                    $driftReason = "Scheduled for Break, but currently {$actualStatus}";
                }
            } elseif ($expectedActivity === 'OFF') {
                if ($actualStatus !== 'Logged Out') {
                    $isAdherent = false;
                    $driftReason = "Scheduled OFF, but logged in as {$actualStatus}";
                }
            }

            if ($isAdherent) {
                $adherentCount++;
            }
            $totalEvaluated++;

            $agentAdherenceList[] = [
                'agent_uuid' => $agent->call_center_agent_uuid,
                'agent_name' => $agent->agent_name,
                'agent_id' => $agent->agent_id,
                'expected_activity' => $expectedActivity,
                'actual_status' => $actualStatus,
                'is_adherent' => $isAdherent,
                'drift_reason' => $driftReason,
                'shift_time' => $sched ? "{$sched->start_time} - {$sched->end_time}" : "Not Scheduled",
            ];
        }

        $adherenceRate = $totalEvaluated > 0 ? round(($adherentCount / $totalEvaluated) * 100, 1) : 100.0;

        return [
            'adherence_rate' => $adherenceRate,
            'adherent_count' => $adherentCount,
            'out_of_adherence_count' => $totalEvaluated - $adherentCount,
            'total_agents' => $totalEvaluated,
            'agents' => $agentAdherenceList,
            'checked_at' => $now->toIso8601String(),
        ];
    }
}
