<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ApiEcosystemCredential;
use App\Models\ApiEcosystemWebhook;
use App\Models\ApiEcosystemWebhookLog;
use App\Services\ApiEcosystemService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ApiEcosystemDeveloperPortalTest extends TestCase
{
    protected string $domainUuid;
    protected User $user;
    protected ApiEcosystemService $service;

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
        $this->service = app(ApiEcosystemService::class);
    }

    public function test_developer_portal_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('api_ecosystem.developer_portal'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Api/DeveloperPortal')
            ->has('credentials')
            ->has('webhooks')
            ->has('webhookLogs')
            ->has('catalog')
            ->has('metrics')
        );
    }

    public function test_generate_api_key_with_scopes(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('api_ecosystem.credentials.store'), [
            'name' => 'Automated QA Pipeline Key',
            'scopes' => ['calls:read', 'sms:send'],
            'rate_limit_per_minute' => 900,
            'expires_in_months' => 6,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('raw_secret', $data);
        $this->assertArrayHasKey('raw_key_id', $data);
        $this->assertStringStartsWith('zn_live_', $data['raw_key_id']);
        $this->assertStringStartsWith('zn_sec_', $data['raw_secret']);

        $this->assertDatabaseHas('api_ecosystem_credentials', [
            'domain_uuid' => $this->domainUuid,
            'name' => 'Automated QA Pipeline Key',
            'rate_limit_per_minute' => 900,
        ]);
    }

    public function test_rotate_api_key_secret(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $credential = ApiEcosystemCredential::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($credential);

        $response = $this->actingAs($this->user)->postJson(route('api_ecosystem.credentials.rotate', ['uuid' => $credential->uuid]));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('raw_secret', $data);
        $this->assertStringStartsWith('zn_sec_', $data['raw_secret']);
    }

    public function test_revoke_api_key(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $credential = ApiEcosystemCredential::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($credential);

        $response = $this->actingAs($this->user)->postJson(route('api_ecosystem.credentials.revoke', ['uuid' => $credential->uuid]));

        $response->assertStatus(200);
        $this->assertDatabaseHas('api_ecosystem_credentials', [
            'uuid' => $credential->uuid,
            'status' => 'revoked',
        ]);
    }

    public function test_webhook_simulation_dispatch_generates_hmac_signature(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $webhook = ApiEcosystemWebhook::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($webhook);

        $response = $this->actingAs($this->user)->postJson(route('api_ecosystem.webhooks.test', ['uuid' => $webhook->uuid]), [
            'event_type' => 'call.completed',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals(200, $data['http_status']);
        $this->assertArrayHasKey('signature_header', $data);
        $this->assertStringContainsString('t=', $data['signature_header']);
        $this->assertStringContainsString('v1=', $data['signature_header']);
        $this->assertArrayHasKey('idempotency_key', $data);

        $this->assertDatabaseHas('api_ecosystem_webhook_logs', [
            'webhook_uuid' => $webhook->uuid,
            'event_type' => 'call.completed',
            'idempotency_key' => $data['idempotency_key'],
        ]);
    }
}
