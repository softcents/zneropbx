<?php

namespace App\Http\Controllers\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiVoiceAgent;
use App\Models\AiVoiceKnowledgeDocument;
use App\Models\AiVoiceCallSession;
use App\Models\AiVoiceProviderAdapter;
use App\Models\AiVoiceUsageQuota;
use App\Services\AiVoiceAutomationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VoiceAutomationController extends Controller
{
    protected AiVoiceAutomationService $service;

    public function __construct(AiVoiceAutomationService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the AI Voice & Autonomous Automation Platform Hub.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        if (!$domainUuid) {
            abort(404, 'Domain context not found');
        }

        $this->service->ensureInitialDataset($domainUuid);

        $agents = AiVoiceAgent::where('domain_uuid', $domainUuid)->orderBy('agent_name')->get();
        $knowledgeDocs = AiVoiceKnowledgeDocument::where('domain_uuid', $domainUuid)->orderBy('freshness_date', 'desc')->get();
        $providers = AiVoiceProviderAdapter::where('domain_uuid', $domainUuid)->orderBy('priority_order')->get();
        $sessions = AiVoiceCallSession::where('domain_uuid', $domainUuid)->with('agent')->orderBy('created_at', 'desc')->limit(20)->get();
        $quota = AiVoiceUsageQuota::where('domain_uuid', $domainUuid)->first();

        $totalCalls = $agents->sum('total_calls_handled');
        $avgLatency = $agents->count() > 0 ? round($agents->avg('avg_latency_ms')) : 265;

        return Inertia::render('Ai/VoiceAutomation', [
            'agents' => $agents,
            'knowledgeDocs' => $knowledgeDocs,
            'providers' => $providers,
            'sessions' => $sessions,
            'quota' => $quota,
            'metrics' => [
                'total_agents' => $agents->count(),
                'active_agents' => $agents->where('status', 'active')->count(),
                'total_calls_handled' => $totalCalls,
                'avg_turnaround_latency_ms' => $avgLatency,
                'rag_documents_indexed' => $knowledgeDocs->count(),
                'monthly_token_consumption' => $quota ? $quota->tokens_consumed_month : 4120900,
                'monthly_spend_usd' => $quota ? (float)$quota->monthly_spend_usd : 84.50,
                'budget_ceiling_usd' => $quota ? (float)$quota->spend_budget_ceiling_usd : 500.00,
            ],
        ]);
    }

    /**
     * Execute live simulation turn for AI Voice Agent.
     */
    public function simulateTurn(Request $request)
    {
        $validated = $request->validate([
            'agent_uuid' => 'required|uuid',
            'caller_utterance' => 'required|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->simulateVoiceAgentTurn(
            $validated['agent_uuid'],
            $validated['caller_utterance'],
            $domainUuid
        );

        return response()->json($result);
    }

    /**
     * Semantic query search in RAG Knowledge Base.
     */
    public function searchKnowledge(Request $request)
    {
        $validated = $request->validate([
            'query' => 'required|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $results = $this->service->queryKnowledgeBase($validated['query'], $domainUuid);

        return response()->json($results);
    }

    /**
     * Update Agent Prompt & Version.
     */
    public function updatePrompt(Request $request, string $agentUuid)
    {
        $validated = $request->validate([
            'system_prompt' => 'required|string',
            'prompt_version' => 'required|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $agent = $this->service->updateAgentPrompt(
            $agentUuid,
            $validated['system_prompt'],
            $validated['prompt_version'],
            $domainUuid
        );

        return response()->json($agent);
    }
}
