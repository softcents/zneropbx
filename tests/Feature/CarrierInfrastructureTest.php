<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\CarrierFreeswitchNode;
use App\Models\CarrierSipProxy;
use App\Models\CarrierSecurityAcl;
use App\Models\CarrierFailoverDrill;
use App\Models\CarrierBackupSnapshot;
use App\Services\CarrierInfrastructureService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CarrierInfrastructureTest extends TestCase
{
    protected string $domainUuid;
    protected User $user;

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
    }

    public function test_carrier_infrastructure_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('carrier.infrastructure'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Carrier/Infrastructure')
            ->has('nodes')
            ->has('proxies')
            ->has('acls')
            ->has('drills')
            ->has('snapshots')
            ->has('metrics')
        );
    }

    public function test_toggle_node_drain_mode(): void
    {
        $service = app(CarrierInfrastructureService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $node = CarrierFreeswitchNode::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($node);

        $response = $this->actingAs($this->user)->postJson(route('carrier.infrastructure.node_drain', [
            'nodeUuid' => $node->uuid,
        ]));

        $response->assertStatus(200);
        $this->assertTrue($response->json('drain_mode'));
        $this->assertEquals('draining', $response->json('status'));
    }

    public function test_ping_node_sip_options(): void
    {
        $service = app(CarrierInfrastructureService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $node = CarrierFreeswitchNode::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($node);

        $response = $this->actingAs($this->user)->postJson(route('carrier.infrastructure.node_ping', [
            'nodeUuid' => $node->uuid,
        ]));

        $response->assertStatus(200);
        $this->assertEquals('SIP OPTIONS', $response->json('probe_method'));
        $this->assertEquals('200 OK', $response->json('response_code'));
        $this->assertGreaterThanOrEqual(1, $response->json('rtt_ms'));
    }

    public function test_simulate_failover_drill_rto_rpo(): void
    {
        $service = app(CarrierInfrastructureService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $nodes = CarrierFreeswitchNode::where('domain_uuid', $this->domainUuid)->get();
        $source = $nodes[0];
        $target = $nodes[3] ?? $nodes[1];

        $response = $this->actingAs($this->user)->postJson(route('carrier.infrastructure.simulate_failover'), [
            'source_node_uuid' => $source->uuid,
            'target_node_uuid' => $target->uuid,
        ]);

        $response->assertStatus(200);
        $this->assertEquals('SUCCESS', $response->json('result_status'));
        $this->assertLessThan(180, (float)$response->json('rto_sec')); // Target < 180s
        $this->assertEquals(0.00, (float)$response->json('rpo_sec')); // Target < 60s
    }

    public function test_create_config_snapshot(): void
    {
        $service = app(CarrierInfrastructureService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('carrier.infrastructure.create_snapshot'), [
            'snapshot_type' => 'freeswitch_xml',
            'storage_target' => 's3_encrypted',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('is_verified'));
        $this->assertNotEmpty($response->json('sha256_checksum'));
        $this->assertNotEmpty($response->json('snapshot_name'));
    }
}
