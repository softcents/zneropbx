<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AnalyticsBiSavedView;
use App\Models\AnalyticsBiScheduledExport;
use App\Models\AnalyticsBiCarrierQosBenchmark;
use App\Services\OperationsIntelligenceService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OperationsIntelligenceTest extends TestCase
{
    protected string $domainUuid;
    protected User $user;
    protected OperationsIntelligenceService $service;

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
        $this->service = app(OperationsIntelligenceService::class);
    }

    public function test_operations_intelligence_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('analytics.operations_intelligence'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Analytics/OperationsIntelligence')
            ->has('crossDomainMetrics')
            ->has('carrierBenchmarks')
            ->has('savedViews')
            ->has('scheduledExports')
            ->has('initialForecast')
            ->has('metrics')
        );
    }

    public function test_create_scheduled_export_job(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('analytics.operations_intelligence.export'), [
            'report_title' => 'Weekly Executive Carrier Settlement & ASR Quality',
            'format' => 'csv',
            'frequency' => 'weekly',
            'recipient_email' => 'telecom-bi@corp.znero.com',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('uuid', $data);

        $this->assertDatabaseHas('analytics_bi_scheduled_exports', [
            'domain_uuid' => $this->domainUuid,
            'report_title' => 'Weekly Executive Carrier Settlement & ASR Quality',
            'format' => 'csv',
            'recipient_email' => 'telecom-bi@corp.znero.com',
        ]);
    }

    public function test_save_custom_view(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('analytics.operations_intelligence.save_view'), [
            'view_name' => 'Wholesale High Margin Carrier Radar',
            'category' => 'carrier_qos',
            'is_pinned' => true,
            'filter_criteria_json' => [
                'min_margin_pct' => 40.0,
                'min_asr' => 98.0,
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('analytics_bi_saved_views', [
            'domain_uuid' => $this->domainUuid,
            'view_name' => 'Wholesale High Margin Carrier Radar',
            'category' => 'carrier_qos',
            'is_pinned' => true,
        ]);
    }

    public function test_run_capacity_forecast(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('analytics.operations_intelligence.forecast'), [
            'metric' => 'concurrent_channels',
            'days_ahead' => 14,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('concurrent_channels', $data['metric']);
        $this->assertEquals(14, $data['days_ahead']);
        $this->assertArrayHasKey('current_baseline', $data);
        $this->assertArrayHasKey('projected_peak', $data);
        $this->assertArrayHasKey('recommended_provisioning_ceiling', $data);
        $this->assertArrayHasKey('trend_points', $data);
        $this->assertCount(14, $data['trend_points']);
        $this->assertGreaterThan($data['current_baseline'], $data['projected_peak']);
    }
}
