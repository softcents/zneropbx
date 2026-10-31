<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SecurityFraudRule;
use App\Models\SecurityQuarantinedIp;
use App\Models\SecurityIncidentEvent;
use App\Models\SecurityCompliancePolicy;
use App\Services\SecurityFraudComplianceService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityFraudComplianceTest extends TestCase
{
    protected string $domainUuid;
    protected User $user;
    protected SecurityFraudComplianceService $service;

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
        $this->service = app(SecurityFraudComplianceService::class);
    }

    public function test_security_fraud_studio_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('security.fraud_compliance_studio'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Security/FraudComplianceStudio')
            ->has('rules')
            ->has('quarantinedIps')
            ->has('incidents')
            ->has('policy')
            ->has('metrics')
        );
    }

    public function test_create_fraud_rule(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('security.fraud_rules.store'), [
            'rule_name' => 'High Risk Seychelles Destination Ceiling (+248)',
            'rule_type' => 'velocity_destination',
            'threshold_value' => 20,
            'time_window_sec' => 60,
            'action' => 'drop_call',
            'severity' => 'critical',
            'auto_mitigate' => true,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('uuid', $data);

        $this->assertDatabaseHas('security_fraud_rules', [
            'domain_uuid' => $this->domainUuid,
            'rule_name' => 'High Risk Seychelles Destination Ceiling (+248)',
            'action' => 'drop_call',
        ]);
    }

    public function test_toggle_fraud_rule(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $rule = SecurityFraudRule::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($rule);

        $originalState = $rule->is_enabled;

        $response = $this->actingAs($this->user)->postJson(route('security.fraud_rules.toggle', ['uuid' => $rule->uuid]));

        $response->assertStatus(200);
        $this->assertEquals(!$originalState, $response->json('is_enabled'));
    }

    public function test_manual_ip_quarantine_and_unquarantine(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $targetIp = '198.51.100.99';
        $response = $this->actingAs($this->user)->postJson(route('security.ip_quarantine.store'), [
            'ip_address' => $targetIp,
            'reason' => 'Observed abusive SIP INVITE flood on trunk gateway',
            'mitigation_type' => 'fail2ban_nftables',
            'expiry_days' => 5,
        ]);

        $response->assertStatus(200);
        $quarantineUuid = $response->json('uuid');
        $this->assertNotNull($quarantineUuid);

        $this->assertDatabaseHas('security_quarantined_ips', [
            'uuid' => $quarantineUuid,
            'ip_address' => $targetIp,
            'status' => 'active',
        ]);

        // Unquarantine / Whitelist
        $unqResponse = $this->actingAs($this->user)->postJson(route('security.ip_quarantine.unquarantine', ['uuid' => $quarantineUuid]));
        $unqResponse->assertStatus(200);

        $this->assertDatabaseHas('security_quarantined_ips', [
            'uuid' => $quarantineUuid,
            'status' => 'whitelisted',
        ]);
    }

    public function test_simulate_threat_evaluation_detects_toll_fraud(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('security.simulate_threat'), [
            'source_ip' => '198.51.100.44',
            'destination_number' => '+23276999888',
            'calls_per_minute' => 55,
            'is_off_hours' => true,
            'rate_usd_per_min' => 1.75,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertGreaterThanOrEqual(80.0, $data['composite_risk_score']);
        $this->assertEquals('CRITICAL', $data['threat_level']);
        $this->assertEquals('quarantine_ip', $data['recommended_action']);
        $this->assertTrue($data['auto_quarantined']);
        $this->assertNotEmpty($data['triggered_rules']);
    }

    public function test_resolve_security_incident(): void
    {
        $this->service->ensureInitialDataset($this->domainUuid);
        $incident = SecurityIncidentEvent::where('domain_uuid', $this->domainUuid)->where('status', 'open')->first();
        if (!$incident) {
            $incident = SecurityIncidentEvent::where('domain_uuid', $this->domainUuid)->first();
        }
        $this->assertNotNull($incident);

        $response = $this->actingAs($this->user)->postJson(route('security.incidents.resolve', ['uuid' => $incident->uuid]), [
            'audit_notes' => 'Verified source IP blocked and carrier ACL verified by NOC supervisor.',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('resolved', $response->json('status'));

        $this->assertDatabaseHas('security_incident_events', [
            'uuid' => $incident->uuid,
            'status' => 'resolved',
        ]);
    }
}
