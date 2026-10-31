<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemProductionDeployment;
use App\Models\SystemBackupSnapshot;
use App\Services\ReleaseReadinessService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReleaseReadinessTest extends TestCase
{
    protected string $domainUuid;
    protected User $user;
    protected ReleaseReadinessService $service;

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
        $this->service = app(ReleaseReadinessService::class);
    }

    public function test_release_readiness_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('system.release_readiness'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('System/ReleaseReadiness')
            ->has('audit')
            ->has('deployments')
            ->has('backupSnapshots')
            ->has('rollbackRunbook')
            ->has('metrics')
        );
    }

    public function test_audit_check_api_endpoint(): void
    {
        $response = $this->actingAs($this->user)->getJson(route('system.release_readiness.audit_check'));

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('overall_score_pct', $data);
        $this->assertArrayHasKey('subsystems', $data);
        $this->assertGreaterThanOrEqual(1, count($data['subsystems']));
    }

    public function test_trigger_backup_snapshot_api(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('system.release_readiness.trigger_backup'), [
            'snapshot_type' => 'dialplan_xml',
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('snapshot', $data);
        $this->assertEquals('dialplan_xml', $data['snapshot']['snapshot_type']);

        $this->assertDatabaseHas('system_backup_snapshots', [
            'domain_uuid' => $this->domainUuid,
            'snapshot_type' => 'dialplan_xml',
        ]);
    }

    public function test_flush_cache_warmup_api(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('system.release_readiness.flush_cache_warmup'));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
    }

    public function test_artisan_verify_readiness_command(): void
    {
        $this->artisan('enterprise:verify-readiness')
            ->assertExitCode(0);
    }
}
