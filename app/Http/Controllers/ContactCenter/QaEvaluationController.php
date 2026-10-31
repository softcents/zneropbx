<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CallCenterAgents;
use App\Models\ContactCenterCallTranscript;
use App\Models\ContactCenterQaEvaluation;
use App\Models\ContactCenterQaScorecard;
use App\Services\ContactCenter\QaEvaluationService;
use App\Services\ContactCenter\SpeechIntelligenceService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class QaEvaluationController extends Controller
{
    protected QaEvaluationService $qaService;
    protected SpeechIntelligenceService $speechService;

    public function __construct(QaEvaluationService $qaService, SpeechIntelligenceService $speechService)
    {
        $this->qaService = $qaService;
        $this->speechService = $speechService;
    }

    /**
     * Render the QA & Coaching Dashboard.
     */
    public function index(Request $request): Response
    {
        $domainUuid = session('domain_uuid');
        $scorecard = $this->qaService->getOrCreateDefaultScorecard($domainUuid);
        $recordedCalls = $this->qaService->getCallsForEvaluation($domainUuid, 40);

        $agents = CallCenterAgents::where('domain_uuid', $domainUuid)
            ->get(['call_center_agent_uuid', 'agent_name', 'agent_id']);

        // QA Summary statistics
        $evaluations = ContactCenterQaEvaluation::where('domain_uuid', $domainUuid)->get();
        $totalEvaluations = $evaluations->count();
        $avgScore = $totalEvaluations > 0 ? round($evaluations->avg('percentage'), 1) : 0;
        $passedCount = $evaluations->where('result', 'PASS')->count();
        $passRate = $totalEvaluations > 0 ? round(($passedCount / $totalEvaluations) * 100, 1) : 100;

        // Speech Intelligence aggregate metrics
        $transcripts = ContactCenterCallTranscript::where('domain_uuid', $domainUuid)->get();
        $transcriptsCount = $transcripts->count();
        $avgAiScore = $transcriptsCount > 0 ? round($transcripts->avg('ai_auto_score'), 1) : 91.5;
        $avgCompliance = $transcriptsCount > 0 ? round($transcripts->avg('compliance_score'), 1) : 95.0;

        return Inertia::render('ContactCenter/QaEvaluation', [
            'scorecard' => $scorecard,
            'recordedCalls' => $recordedCalls,
            'agents' => $agents,
            'stats' => [
                'total_evaluations' => $totalEvaluations,
                'avg_score' => $avgScore,
                'pass_rate' => $passRate,
                'coaching_needed' => $evaluations->where('result', 'REQUIRES_COACHING')->count(),
                'transcripts_count' => $transcriptsCount,
                'avg_ai_score' => $avgAiScore,
                'avg_compliance' => $avgCompliance,
            ]
        ]);
    }

    /**
     * Get or generate speech intelligence for a specific call.
     */
    public function getSpeechIntelligence(Request $request, string $xmlCdrUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $intelligence = $this->speechService->getIntelligenceForCall($domainUuid, $xmlCdrUuid);

        return response()->json([
            'success' => true,
            'intelligence' => $intelligence,
        ]);
    }

    /**
     * Submit evaluation for a recorded call.
     */
    public function evaluate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'scorecard_uuid' => 'required|uuid',
            'xml_cdr_uuid' => 'required|uuid',
            'agent_uuid' => 'required|uuid',
            'answers' => 'required|array',
            'feedback' => 'nullable|string',
            'coaching_notes' => 'nullable|string',
        ]);

        $domainUuid = session('domain_uuid');
        $evaluation = $this->qaService->evaluateCall(
            $domainUuid,
            $validated['scorecard_uuid'],
            $validated['xml_cdr_uuid'],
            $validated['agent_uuid'],
            Auth::id(),
            $validated['answers'],
            $validated['feedback'] ?? null,
            $validated['coaching_notes'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => "Call evaluated successfully! Score: {$evaluation->percentage}% ({$evaluation->result})",
            'evaluation' => $evaluation,
        ]);
    }

    /**
     * Agent acknowledgement or dispute of QA Evaluation.
     */
    public function acknowledge(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'evaluation_uuid' => 'required|uuid',
            'action' => 'required|in:ACKNOWLEDGE,DISPUTE',
            'comments' => 'nullable|string',
        ]);

        $evaluation = ContactCenterQaEvaluation::where('qa_evaluation_uuid', $validated['evaluation_uuid'])->firstOrFail();

        if ($validated['action'] === 'ACKNOWLEDGE') {
            $evaluation->update([
                'status' => 'ACKNOWLEDGED',
                'acknowledged_at' => Carbon::now(),
                'agent_comments' => $validated['comments'] ?? null,
            ]);
        } else {
            $evaluation->update([
                'status' => 'DISPUTED',
                'agent_comments' => $validated['comments'] ?? 'Evaluation disputed by agent for review.',
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $evaluation->status,
            'message' => "Evaluation updated to {$evaluation->status}",
        ]);
    }
}
