<?php

namespace App\Services\ContactCenter;

use App\Models\CDR;
use App\Models\CallCenterAgents;
use App\Models\ContactCenterCallTranscript;
use App\Models\ContactCenterQaEvaluation;
use App\Models\ContactCenterQaScorecard;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QaEvaluationService
{
    /**
     * Get or create a default standard enterprise QA Scorecard for a tenant domain.
     */
    public function getOrCreateDefaultScorecard(string $domainUuid): ContactCenterQaScorecard
    {
        $scorecard = ContactCenterQaScorecard::where('domain_uuid', $domainUuid)
            ->where('is_active', true)
            ->first();

        if ($scorecard) {
            return $scorecard;
        }

        return ContactCenterQaScorecard::create([
            'qa_scorecard_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'name' => 'Standard Contact Center QA Rubric',
            'description' => 'Comprehensive enterprise evaluation covering soft skills, compliance, technical resolution, and customer empathy.',
            'passing_score' => 85.00,
            'is_active' => true,
            'sections' => [
                [
                    'id' => 'sec_greeting',
                    'title' => 'Greeting & Identity Verification',
                    'weight' => 20,
                    'questions' => [
                        [
                            'id' => 'q_intro',
                            'text' => 'Agent identified company and stated their name clearly and professionally.',
                            'max_score' => 10,
                        ],
                        [
                            'id' => 'q_verify',
                            'text' => 'Agent performed security/identity verification in compliance with policy.',
                            'max_score' => 10,
                        ],
                    ]
                ],
                [
                    'id' => 'sec_communication',
                    'title' => 'Communication & Active Listening',
                    'weight' => 25,
                    'questions' => [
                        [
                            'id' => 'q_active_listen',
                            'text' => 'Agent listened actively without interrupting the customer.',
                            'max_score' => 10,
                        ],
                        [
                            'id' => 'q_empathy',
                            'text' => 'Agent demonstrated empathy, polite tone, and positive phrasing.',
                            'max_score' => 15,
                        ],
                    ]
                ],
                [
                    'id' => 'sec_technical',
                    'title' => 'Product Knowledge & Problem Resolution',
                    'weight' => 35,
                    'questions' => [
                        [
                            'id' => 'q_accuracy',
                            'text' => 'Agent provided accurate and complete information/troubleshooting.',
                            'max_score' => 20,
                        ],
                        [
                            'id' => 'q_efficiency',
                            'text' => 'Agent navigated PBX tools effectively without unnecessary hold times.',
                            'max_score' => 15,
                        ],
                    ]
                ],
                [
                    'id' => 'sec_closing',
                    'title' => 'Closing, Wrap-Up & Documentation',
                    'weight' => 20,
                    'questions' => [
                        [
                            'id' => 'q_recap',
                            'text' => 'Agent summarized resolution and verified customer satisfaction before ending call.',
                            'max_score' => 10,
                        ],
                        [
                            'id' => 'q_wrapup',
                            'text' => 'Agent logged accurate disposition code and relevant customer CRM notes.',
                            'max_score' => 10,
                        ],
                    ]
                ]
            ]
        ]);
    }

    /**
     * Submit or update an evaluation for a recorded call.
     */
    public function evaluateCall(
        string $domainUuid,
        string $scorecardUuid,
        string $xmlCdrUuid,
        string $agentUuid,
        ?string $supervisorUserUuid,
        array $answers,
        ?string $feedback = null,
        ?string $coachingNotes = null
    ): ContactCenterQaEvaluation {
        $scorecard = ContactCenterQaScorecard::findOrFail($scorecardUuid);

        $totalEarned = 0.0;
        $maxPossible = 0.0;

        foreach ($scorecard->sections as $section) {
            foreach ($section['questions'] as $q) {
                $qId = $q['id'];
                $qMax = (float) $q['max_score'];
                $maxPossible += $qMax;

                if (isset($answers[$qId])) {
                    $earned = (float) $answers[$qId]['score'];
                    $totalEarned += min($earned, $qMax);
                }
            }
        }

        $percentage = $maxPossible > 0 ? round(($totalEarned / $maxPossible) * 100, 2) : 0;

        $result = 'PASS';
        if ($percentage < $scorecard->passing_score) {
            $result = $percentage < 65.0 ? 'FAIL' : 'REQUIRES_COACHING';
        }

        return ContactCenterQaEvaluation::updateOrCreate(
            [
                'domain_uuid' => $domainUuid,
                'xml_cdr_uuid' => $xmlCdrUuid,
            ],
            [
                'qa_evaluation_uuid' => (string) Str::uuid(),
                'scorecard_uuid' => $scorecardUuid,
                'agent_uuid' => $agentUuid,
                'supervisor_user_uuid' => $supervisorUserUuid,
                'total_score' => $totalEarned,
                'max_score' => $maxPossible,
                'percentage' => $percentage,
                'result' => $result,
                'status' => 'COMPLETED',
                'answers' => $answers,
                'feedback' => $feedback,
                'coaching_notes' => $coachingNotes ?: $this->generateCoachingRecommendation($percentage, $answers),
            ]
        );
    }

    /**
     * Generate automatic coaching recommendations based on scoring weaknesses.
     */
    protected function generateCoachingRecommendation(float $percentage, array $answers): string
    {
        $recommendations = [];

        if ($percentage < 85.0) {
            $recommendations[] = "Score is below target ({$percentage}% vs 85.0%). Review call recording in 1-on-1 coaching session.";
        }

        foreach ($answers as $qId => $data) {
            $score = (float) ($data['score'] ?? 0);
            if ($score <= 5) {
                if ($qId === 'q_verify') {
                    $recommendations[] = "Strict adherence to customer identity verification protocol is required.";
                } elseif ($qId === 'q_empathy') {
                    $recommendations[] = "Work on maintaining an empathetic tone and active listening during customer frustration.";
                } elseif ($qId === 'q_accuracy') {
                    $recommendations[] = "Review product knowledge base articles to ensure technical solutions are accurate.";
                } elseif ($qId === 'q_wrapup') {
                    $recommendations[] = "Ensure wrap-up notes are detailed and disposition codes match the actual resolution.";
                }
            }
        }

        return !empty($recommendations)
            ? implode(" | ", $recommendations)
            : "Performance meets quality standard. Maintain high customer satisfaction.";
    }

    /**
     * Get calls with recordings available for evaluation.
     */
    public function getCallsForEvaluation(string $domainUuid, int $limit = 30)
    {
        // Ensure sample enterprise calls with audio intelligence exist
        app(SpeechIntelligenceService::class)->ensureSampleCallsWithRecordings($domainUuid);

        $cdrs = CDR::where('domain_uuid', $domainUuid)
            ->whereNotNull('record_name')
            ->where('record_name', '!=', '')
            ->where('billsec', '>', 5)
            ->orderBy('start_epoch', 'desc')
            ->limit($limit)
            ->get();

        return $cdrs->map(function ($cdr) use ($domainUuid) {
            $evaluation = ContactCenterQaEvaluation::where('xml_cdr_uuid', $cdr->xml_cdr_uuid)->first();
            $transcript = ContactCenterCallTranscript::where('xml_cdr_uuid', $cdr->xml_cdr_uuid)->first();

            return [
                'xml_cdr_uuid' => $cdr->xml_cdr_uuid,
                'caller_id_name' => $cdr->caller_id_name,
                'caller_id_number' => $cdr->caller_id_number,
                'destination_number' => $cdr->destination_number,
                'start_epoch' => $cdr->start_epoch,
                'start_date' => $cdr->start_date ?? date('Y-m-d H:i', (int)$cdr->start_epoch),
                'duration' => (int) $cdr->duration,
                'billsec' => (int) $cdr->billsec,
                'record_name' => $cdr->record_name,
                'record_path' => $cdr->record_path,
                'audio_url' => "/recordings/{$cdr->xml_cdr_uuid}",
                'evaluation' => $evaluation ? [
                    'qa_evaluation_uuid' => $evaluation->qa_evaluation_uuid,
                    'total_score' => $evaluation->total_score,
                    'max_score' => $evaluation->max_score,
                    'percentage' => $evaluation->percentage,
                    'result' => $evaluation->result,
                    'status' => $evaluation->status,
                    'coaching_notes' => $evaluation->coaching_notes,
                ] : null,
                'transcript' => $transcript ? [
                    'transcript_uuid' => $transcript->transcript_uuid,
                    'overall_sentiment' => $transcript->overall_sentiment,
                    'customer_sentiment_score' => $transcript->customer_sentiment_score,
                    'ai_auto_score' => $transcript->ai_auto_score,
                    'compliance_score' => $transcript->compliance_score,
                    'summary' => $transcript->summary,
                ] : null,
            ];
        });
    }
}
