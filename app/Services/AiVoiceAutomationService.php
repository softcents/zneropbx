<?php

namespace App\Services;

use App\Models\AiVoiceAgent;
use App\Models\AiVoiceKnowledgeDocument;
use App\Models\AiVoiceCallSession;
use App\Models\AiVoiceProviderAdapter;
use App\Models\AiVoiceUsageQuota;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AiVoiceAutomationService
{
    /**
     * Ensure AI Voice platform initial dataset exists for domain.
     */
    public function ensureInitialDataset(string $domainUuid): void
    {
        // 1. AI Voice Agents
        if (AiVoiceAgent::where('domain_uuid', $domainUuid)->count() === 0) {
            $agents = [
                [
                    'agent_name' => 'Maya - Tier 1 Telecom Concierge',
                    'assigned_extension' => '5001',
                    'persona_role' => 'Customer Care Concierge',
                    'primary_model' => 'gpt-4o-realtime',
                    'tts_voice' => 'ElevenLabs.Rachel-Neural',
                    'speaking_rate' => 1.00,
                    'barge_in_enabled' => true,
                    'system_prompt' => 'You are Maya, an autonomous AI voice concierge for Znero Telecom. Assist customers with account queries, DID rates, and call forwarding. Maintain a warm, executive tone. If the caller requests a human or expresses significant dissatisfaction, initiate a warm transfer immediately.',
                    'prompt_version' => 'v1.4',
                    'rag_knowledge_base_enabled' => true,
                    'escalation_sentiment_threshold' => -0.60,
                    'human_handoff_queue_uuid' => '1002',
                    'max_turn_duration_sec' => 300,
                    'status' => 'active',
                    'total_calls_handled' => 1420,
                    'avg_latency_ms' => 265,
                    'csat_score' => 4.88,
                ],
                [
                    'agent_name' => 'Atlas - Technical Network Triage',
                    'assigned_extension' => '5002',
                    'persona_role' => 'Tier 2 Technical Triage',
                    'primary_model' => 'gemini-2.0-flash-exp',
                    'tts_voice' => 'OpenAI.Echo',
                    'speaking_rate' => 1.05,
                    'barge_in_enabled' => true,
                    'system_prompt' => 'You are Atlas, a VoIP network diagnostic engineer. Assist with SIP 403/488 error debugging, codec negotiation (Opus, G.711u), and device auto-provisioning. Be concise, technical, and precise.',
                    'prompt_version' => 'v2.1',
                    'rag_knowledge_base_enabled' => true,
                    'escalation_sentiment_threshold' => -0.50,
                    'human_handoff_queue_uuid' => '1002',
                    'max_turn_duration_sec' => 360,
                    'status' => 'active',
                    'total_calls_handled' => 890,
                    'avg_latency_ms' => 240,
                    'csat_score' => 4.79,
                ],
                [
                    'agent_name' => 'Eva - After-Hours Emergency Scribe',
                    'assigned_extension' => '5003',
                    'persona_role' => 'Emergency Incident Scribe',
                    'primary_model' => 'claude-3-5-sonnet',
                    'tts_voice' => 'Google.en-US-Journey-F',
                    'speaking_rate' => 0.95,
                    'barge_in_enabled' => false,
                    'system_prompt' => 'You are Eva, handling after-hours mission-critical facility and network outages. Capture caller contact, company name, severity level, and trigger an automated SMS/voice alert to the on-call NOC engineer.',
                    'prompt_version' => 'v1.1',
                    'rag_knowledge_base_enabled' => true,
                    'escalation_sentiment_threshold' => -0.70,
                    'human_handoff_queue_uuid' => '1001',
                    'max_turn_duration_sec' => 180,
                    'status' => 'active',
                    'total_calls_handled' => 340,
                    'avg_latency_ms' => 295,
                    'csat_score' => 4.92,
                ],
            ];

            foreach ($agents as $a) {
                AiVoiceAgent::create(array_merge($a, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 2. RAG Knowledge Documents
        if (AiVoiceKnowledgeDocument::where('domain_uuid', $domainUuid)->count() === 0) {
            $docs = [
                [
                    'document_title' => 'Znero PBX Wholesale Carrier DID & LCR Rate Sheet',
                    'category' => 'billing',
                    'document_content' => 'Domestic US outbound termination rate is $0.00750 per minute with standard 60/6 interval rating. Inbound toll-free numbers are billed at $0.01200 per minute. Volume tier discount applies automatically when monthly usage exceeds 250,000 minutes. International calls to Tier-1 destinations (UK, Canada, Germany) start at $0.0120/min.',
                    'chunk_count' => 6,
                    'freshness_date' => now()->subDays(3),
                    'vector_index_status' => 'indexed',
                    'access_tier' => 'public_support',
                ],
                [
                    'document_title' => 'Emergency E911 Address & Dispatchable Location SOP',
                    'category' => 'compliance',
                    'document_content' => 'In accordance with Kari\'s Law and Ray Baum\'s Act Section 506, all SIP endpoints must have an assigned ELIN with precise dispatchable location data including street address, floor, and suite/room number. Direct 911 dialing is active 24/7 without prefix. Security desk notification is triggered simultaneously upon dial.',
                    'chunk_count' => 4,
                    'freshness_date' => now()->subDays(1),
                    'vector_index_status' => 'indexed',
                    'access_tier' => 'compliance_legal',
                ],
                [
                    'document_title' => 'Yealink & Polycom SIP Phone Provisioning Guide',
                    'category' => 'technical',
                    'document_content' => 'Hardware IP phones auto-provision via DHCP Option 66 pointing to https://pbx.corp.znero.com/app/provision. Mutual TLS certificate authentication and AES-256-GCM SRTP encryption are enforced. Device configuration templates reside in /etc/fusionpbx/templates.',
                    'chunk_count' => 5,
                    'freshness_date' => now()->subDays(7),
                    'vector_index_status' => 'indexed',
                    'access_tier' => 'technical_support',
                ],
                [
                    'document_title' => 'Billing Dispute Resolution & Ledger Credit Guidelines',
                    'category' => 'billing',
                    'document_content' => 'Billing disputes must be filed within 30 days of invoice generation. System CDR records are audited against carrier gateway interconnect logs. Once verified by accounting, automated credit ledger entries are posted to the client pre-paid wallet balance.',
                    'chunk_count' => 3,
                    'freshness_date' => now()->subDays(14),
                    'vector_index_status' => 'indexed',
                    'access_tier' => 'finance_billing',
                ],
            ];

            foreach ($docs as $d) {
                AiVoiceKnowledgeDocument::create(array_merge($d, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 3. Provider Adapters
        if (AiVoiceProviderAdapter::where('domain_uuid', $domainUuid)->count() === 0) {
            $providers = [
                [
                    'provider_name' => 'OpenAI Realtime Voice API (GPT-4o Audio)',
                    'api_endpoint' => 'wss://api.openai.com/v1/realtime',
                    'api_key_masked' => 'sk-proj-9921****8812',
                    'supports_realtime_audio' => true,
                    'is_healthy' => true,
                    'priority_order' => 1,
                    'cost_per_minute_usd' => 0.06000,
                ],
                [
                    'provider_name' => 'Google Cloud Vertex AI Live (Gemini 2.0 Flash)',
                    'api_endpoint' => 'wss://generativelanguage.googleapis.com/v1alpha/live',
                    'api_key_masked' => 'AIzaSy78****9420',
                    'supports_realtime_audio' => true,
                    'is_healthy' => true,
                    'priority_order' => 2,
                    'cost_per_minute_usd' => 0.04500,
                ],
                [
                    'provider_name' => 'ElevenLabs Conversational AI Streaming',
                    'api_endpoint' => 'wss://api.elevenlabs.io/v1/convai/conversation',
                    'api_key_masked' => 'xi-api-3312****1190',
                    'supports_realtime_audio' => true,
                    'is_healthy' => true,
                    'priority_order' => 1,
                    'cost_per_minute_usd' => 0.02200,
                ],
                [
                    'provider_name' => 'Anthropic Claude 3.5 Sonnet (Fallback Logic)',
                    'api_endpoint' => 'https://api.anthropic.com/v1/messages',
                    'api_key_masked' => 'sk-ant-4411****9910',
                    'supports_realtime_audio' => false,
                    'is_healthy' => true,
                    'priority_order' => 3,
                    'cost_per_minute_usd' => 0.03500,
                ],
            ];

            foreach ($providers as $pr) {
                AiVoiceProviderAdapter::create(array_merge($pr, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 4. Usage Quota
        if (AiVoiceUsageQuota::where('domain_uuid', $domainUuid)->count() === 0) {
            AiVoiceUsageQuota::create([
                'uuid' => Str::uuid()->toString(),
                'domain_uuid' => $domainUuid,
                'monthly_token_quota' => 50000000,
                'monthly_minutes_quota' => 10000,
                'tokens_consumed_month' => 4120900,
                'minutes_consumed_month' => 1420,
                'monthly_spend_usd' => 84.5000,
                'spend_budget_ceiling_usd' => 500.0000,
            ]);
        }

        // 5. Sample Sessions
        if (AiVoiceCallSession::where('domain_uuid', $domainUuid)->count() === 0) {
            $maya = AiVoiceAgent::where('domain_uuid', $domainUuid)->first();
            $sessions = [
                [
                    'agent_uuid' => $maya ? $maya->uuid : Str::uuid()->toString(),
                    'caller_phone' => '+15125550101',
                    'caller_name' => 'John Sterling',
                    'session_duration_sec' => 48,
                    'sentiment_score' => 0.85,
                    'hand_off_triggered' => false,
                    'handoff_reason' => null,
                    'stt_turnaround_ms' => 62,
                    'llm_inference_ms' => 125,
                    'tts_synthesis_ms' => 78,
                    'total_latency_ms' => 265,
                    'tokens_used' => 345,
                    'call_summary' => 'Caller enquired regarding international call termination rates to London. Maya explained the $0.0120/min Tier-1 rate.',
                    'full_transcript_json' => [
                        ['speaker' => 'AI', 'text' => 'Thank you for calling Znero Telecom! My name is Maya. How may I assist you today?'],
                        ['speaker' => 'User', 'text' => 'Hi Maya, what is our per-minute rate for calling our UK branch in London?'],
                        ['speaker' => 'AI', 'text' => 'Outbound calls to the United Kingdom are billed at our Tier-1 rate of 1.2 cents per minute with standard 60/6 second increments.'],
                    ],
                    'cost_usd' => 0.01450,
                ],
                [
                    'agent_uuid' => $maya ? $maya->uuid : Str::uuid()->toString(),
                    'caller_phone' => '+15125550102',
                    'caller_name' => 'Sarah Connor',
                    'session_duration_sec' => 32,
                    'sentiment_score' => -0.72,
                    'hand_off_triggered' => true,
                    'handoff_reason' => 'Caller requested live human agent due to urgent billing dispute.',
                    'stt_turnaround_ms' => 58,
                    'llm_inference_ms' => 110,
                    'tts_synthesis_ms' => 72,
                    'total_latency_ms' => 240,
                    'tokens_used' => 280,
                    'call_summary' => 'Caller expressed frustration regarding unrecognized carrier surcharge. Agent executed warm handoff to Tier 2 queue 1002.',
                    'full_transcript_json' => [
                        ['speaker' => 'User', 'text' => 'I have a huge charge on my invoice that is completely incorrect! Transfer me to a person right now.'],
                        ['speaker' => 'AI', 'text' => 'I completely understand your frustration. I am transferring you directly to a senior billing specialist right now.'],
                    ],
                    'cost_usd' => 0.00980,
                ],
            ];

            foreach ($sessions as $s) {
                AiVoiceCallSession::create(array_merge($s, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }
    }

    /**
     * Simulate real-time streaming AI Voice turn with STT, RAG knowledge retrieval, LLM reasoning, and neural TTS synthesis.
     */
    public function simulateVoiceAgentTurn(string $agentUuid, string $callerUtterance, string $domainUuid): array
    {
        $agent = AiVoiceAgent::where('uuid', $agentUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $cleanText = strtolower(trim($callerUtterance));

        // 1. Semantic Sentiment & Human Handoff Evaluation
        $isHumanRequest = str_contains($cleanText, 'human') || str_contains($cleanText, 'person') || str_contains($cleanText, 'representative') || str_contains($cleanText, 'transfer me');
        $isFrustrated = str_contains($cleanText, 'angry') || str_contains($cleanText, 'terrible') || str_contains($cleanText, 'incorrect') || str_contains($cleanText, 'dispute') || str_contains($cleanText, 'charge');

        $sentiment = 0.80;
        if ($isFrustrated) $sentiment = -0.75;
        if ($isHumanRequest) $sentiment = -0.40;

        $shouldHandoff = $isHumanRequest || ($sentiment <= $agent->escalation_sentiment_threshold);

        // 2. RAG Knowledge Retrieval
        $matchedDoc = null;
        $similarityScore = 0.94;
        if (str_contains($cleanText, 'rate') || str_contains($cleanText, 'price') || str_contains($cleanText, 'did') || str_contains($cleanText, 'cost')) {
            $matchedDoc = AiVoiceKnowledgeDocument::where('domain_uuid', $domainUuid)->where('category', 'billing')->first();
        } elseif (str_contains($cleanText, '911') || str_contains($cleanText, 'emergency') || str_contains($cleanText, 'elin') || str_contains($cleanText, 'address')) {
            $matchedDoc = AiVoiceKnowledgeDocument::where('domain_uuid', $domainUuid)->where('category', 'compliance')->first();
        } elseif (str_contains($cleanText, 'yealink') || str_contains($cleanText, 'polycom') || str_contains($cleanText, 'phone') || str_contains($cleanText, 'sip') || str_contains($cleanText, 'provision')) {
            $matchedDoc = AiVoiceKnowledgeDocument::where('domain_uuid', $domainUuid)->where('category', 'technical')->first();
        }

        if (!$matchedDoc && $agent->rag_knowledge_base_enabled) {
            $matchedDoc = AiVoiceKnowledgeDocument::where('domain_uuid', $domainUuid)->first();
            $similarityScore = 0.88;
        }

        // 3. Formulate Spoken AI Response
        if ($shouldHandoff) {
            $responseText = "I completely understand and I am here to help. I am performing an immediate warm transfer to our Tier 2 Support Queue (Extension {$agent->human_handoff_queue_uuid}). Please hold while I connect you with our specialist.";
            $handoffReason = $isHumanRequest ? 'Caller explicitly requested human representative.' : 'Caller sentiment threshold exceeded (-0.75 <= -0.60).';
        } elseif ($matchedDoc && str_contains($cleanText, 'rate')) {
            $responseText = "Based on our current enterprise tariff matrix, domestic US outbound termination is 0.75 cents per minute with 60/6 rounding, and Tier-1 international destinations to the UK and Canada start at 1.2 cents per minute.";
            $handoffReason = null;
        } elseif ($matchedDoc && (str_contains($cleanText, '911') || str_contains($cleanText, 'emergency'))) {
            $responseText = "Under our Kari's Law compliance framework, direct 911 dialing is active from all extensions without any prefix. Ray Baum's Act Section 506 automatically passes your exact floor and suite coordinates to the PSAP.";
            $handoffReason = null;
        } elseif ($matchedDoc && str_contains($cleanText, 'phone')) {
            $responseText = "Your hardware VoIP phones provision automatically using DHCP Option 66 pointing to our secure HTTPS provisioning gateway with mutual TLS and SRTP audio encryption.";
            $handoffReason = null;
        } else {
            $responseText = "Hello! I am {$agent->agent_name}. I can assist you with your SIP trunk lines, DID inventory, billing records, or connect you with technical support. What can I do for you today?";
            $handoffReason = null;
        }

        // 4. Latency Breakdown Telemetry
        $sttMs = rand(55, 75);
        $llmMs = rand(110, 140);
        $ttsMs = rand(65, 85);
        $totalMs = $sttMs + $llmMs + $ttsMs;
        $tokens = rand(240, 420);
        $cost = round(($tokens / 1000) * 0.03, 5);

        // 5. Record Session
        $session = AiVoiceCallSession::create([
            'uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'agent_uuid' => $agent->uuid,
            'caller_phone' => '+15125550199',
            'caller_name' => 'Live Simulator Caller',
            'session_duration_sec' => rand(25, 60),
            'sentiment_score' => $sentiment,
            'hand_off_triggered' => $shouldHandoff,
            'handoff_reason' => $handoffReason,
            'stt_turnaround_ms' => $sttMs,
            'llm_inference_ms' => $llmMs,
            'tts_synthesis_ms' => $ttsMs,
            'total_latency_ms' => $totalMs,
            'tokens_used' => $tokens,
            'call_summary' => "Caller asked: '{$callerUtterance}'. Agent responded with RAG attribution.",
            'full_transcript_json' => [
                ['speaker' => 'User', 'text' => $callerUtterance],
                ['speaker' => 'AI', 'text' => $responseText],
            ],
            'cost_usd' => $cost,
        ]);

        // Update Agent Telemetry
        $agent->increment('total_calls_handled');
        $agent->update(['avg_latency_ms' => $totalMs]);

        return [
            'session_uuid' => $session->uuid,
            'agent_name' => $agent->agent_name,
            'spoken_response' => $responseText,
            'sentiment_score' => $sentiment,
            'hand_off_triggered' => $shouldHandoff,
            'handoff_reason' => $handoffReason,
            'handoff_destination' => $shouldHandoff ? "Queue Ext {$agent->human_handoff_queue_uuid}" : null,
            'latency_breakdown' => [
                'stt_ms' => $sttMs,
                'llm_inference_ms' => $llmMs,
                'tts_synthesis_ms' => $ttsMs,
                'total_latency_ms' => $totalMs,
            ],
            'tokens_used' => $tokens,
            'cost_usd' => $cost,
            'rag_source_document' => $matchedDoc ? [
                'title' => $matchedDoc->document_title,
                'category' => $matchedDoc->category,
                'similarity_score' => $similarityScore,
            ] : null,
        ];
    }

    /**
     * Semantic Search in RAG Knowledge Base.
     */
    public function queryKnowledgeBase(string $query, string $domainUuid): array
    {
        $clean = strtolower(trim($query));
        $docs = AiVoiceKnowledgeDocument::where('domain_uuid', $domainUuid)->get();

        $results = [];
        foreach ($docs as $doc) {
            $score = 0.70;
            if (str_contains(strtolower($doc->document_title), $clean) || str_contains(strtolower($doc->document_content), $clean)) {
                $score = 0.95;
            } elseif (str_contains(strtolower($doc->category), $clean)) {
                $score = 0.88;
            }

            $results[] = [
                'uuid' => $doc->uuid,
                'title' => $doc->document_title,
                'category' => $doc->category,
                'excerpt' => mb_substr($doc->document_content, 0, 160) . '...',
                'similarity_score' => $score,
            ];
        }

        usort($results, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);

        return array_slice($results, 0, 3);
    }

    /**
     * Update Agent Prompt and Version.
     */
    public function updateAgentPrompt(string $agentUuid, string $prompt, string $version, string $domainUuid): AiVoiceAgent
    {
        $agent = AiVoiceAgent::where('uuid', $agentUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $agent->update([
            'system_prompt' => $prompt,
            'prompt_version' => $version,
            'updated_at' => now(),
        ]);

        return $agent;
    }
}
