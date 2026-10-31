<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AiVoiceAgent;
use App\Models\AiVoiceKnowledgeDocument;
use App\Models\AiVoiceCallSession;
use App\Services\AiVoiceAutomationService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AiVoiceAutomationTest extends TestCase
{
    protected string $domainUuid;
    protected User $user;
    protected AiVoiceAutomationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $domain = DB::table('v_domains')->first();
        if (!$domain) {
            $this->domainUuid = '663e7c32-a867-4d21-ac35-a6de0b259aa4';
            DB::table('v_domains')->insert([
                'domain_uuid' => $this->domainUuid,
                'domain_name' => 'admin.localhost',
                'domain_enabled' => 'true',
            ]);
        } else {
            $this->domainUuid = $domain->domain_uuid;
        }

        $this->user = User::first() ?? User::factory()->create([
            'domain_uuid' => $this->domainUuid,
            'user_email' => 'admin@localhost.local',
        ]);

        session(['domain_uuid' => $this->domainUuid]);
        $this->service = app(AiVoiceAutomationService::class);
    }

    public function test_ai_voice_platform_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('ai.voice_automation'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Ai/VoiceAutomation')
            ->has('agents')
            ->has('knowledgeDocs')
            ->has('providers')
            ->has('sessions')
            ->has('quota')
            ->has('metrics')
        );
    }

    public function test_knowledge_base_search(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('api.ai.voice_automation.search_knowledge'), [
            'query' => 'payment credit card refund',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('similarity_score', $data[0]);
    }

    public function test_simulate_voice_agent_turn(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $agent = AiVoiceAgent::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($agent);

        $response = $this->actingAs($this->user)->postJson(route('api.ai.voice_automation.simulate_turn'), [
            'agent_uuid' => $agent->agent_uuid,
            'caller_utterance' => 'I need to check outbound rates',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('spoken_response', $data);
        $this->assertArrayHasKey('latency_breakdown', $data);
        $this->assertArrayHasKey('total_latency_ms', $data['latency_breakdown']);
        $this->assertArrayHasKey('sentiment_score', $data);
        $this->assertArrayHasKey('rag_source_document', $data);
        $this->assertLessThanOrEqual(500, $data['latency_breakdown']['total_latency_ms']);
    }

    public function test_warm_transfer_escalation_detection(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $agent = AiVoiceAgent::where('domain_uuid', $this->domainUuid)->first();

        $response = $this->actingAs($this->user)->postJson(route('api.ai.voice_automation.simulate_turn'), [
            'agent_uuid' => $agent->agent_uuid,
            'caller_utterance' => 'This is unacceptable! Let me speak to a supervisor person immediately!',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['hand_off_triggered']);
        $this->assertNotNull($data['handoff_reason']);
    }

    public function test_update_agent_prompt_versioning(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $agent = AiVoiceAgent::where('domain_uuid', $this->domainUuid)->first();

        $newPrompt = 'You are an elite AI concierge specialized in high-priority enterprise VIP inquiries.';
        $newVersion = 'v2.4.0';

        $response = $this->actingAs($this->user)->postJson(route('api.ai.voice_automation.update_prompt', ['agentUuid' => $agent->agent_uuid]), [
            'system_prompt' => $newPrompt,
            'prompt_version' => $newVersion,
        ]);

        $response->assertStatus(200);
        $this->assertEquals($newPrompt, $response->json('system_prompt'));
        $this->assertEquals($newVersion, $response->json('prompt_version'));

        $this->assertDatabaseHas('ai_voice_agents', [
            'agent_uuid' => $agent->agent_uuid,
            'prompt_version' => $newVersion,
        ]);
    }
}
