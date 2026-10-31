<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\PbxEmergencyLocation;
use App\Models\PbxCallerIdRule;
use App\Models\PbxCallPermissionProfile;
use App\Models\PbxRecordingLegalHold;
use App\Models\PbxDeviceFleet;
use App\Services\PbxAdvancedOperationsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PbxAdvancedOperationsTest extends TestCase
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

    public function test_pbx_advanced_operations_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('pbx.advanced_operations'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Pbx/AdvancedOperations')
            ->has('emergencyLocations')
            ->has('callerIdRules')
            ->has('permissionProfiles')
            ->has('retentionRules')
            ->has('legalHolds')
            ->has('deviceFleet')
            ->has('metrics')
        );
    }

    public function test_emergency_dispatch_simulation(): void
    {
        $service = app(PbxAdvancedOperationsService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $loc = PbxEmergencyLocation::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($loc);

        $response = $this->actingAs($this->user)->postJson(route('pbx.advanced_operations.emergency_dispatch'), [
            'location_uuid' => $loc->uuid,
            'caller_extension' => '1001',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'location_uuid',
            'location_name',
            'caller_extension',
            'elin',
            'karis_law',
            'ray_baums',
            'notifications_dispatched',
        ]);
        $this->assertEquals('PASS', $response->json('karis_law.status'));
    }

    public function test_caller_id_normalization_e164(): void
    {
        $service = app(PbxAdvancedOperationsService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('pbx.advanced_operations.caller_id_simulate'), [
            'dialed_number' => '5125551234',
            'extension' => '1001',
            'department' => 'Executive',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('+15125551234', $response->json('normalized_destination'));
        $this->assertNotEmpty($response->json('effective_caller_id_number'));
        $this->assertNotEmpty($response->json('effective_caller_id_name'));
    }

    public function test_call_permission_evaluation_unconditional_emergency(): void
    {
        $service = app(PbxAdvancedOperationsService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('pbx.advanced_operations.permission_evaluate'), [
            'extension' => '1002',
            'destination' => '911',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('allowed'));
        $this->assertEquals('emergency', $response->json('destination_type'));
    }

    public function test_call_permission_evaluation_international_barring(): void
    {
        $service = app(PbxAdvancedOperationsService::class);
        $service->ensureInitialDataset($this->domainUuid);

        // Standard corporate staff (extension 1002) has international barred
        $response = $this->actingAs($this->user)->postJson(route('pbx.advanced_operations.permission_evaluate'), [
            'extension' => '1002',
            'destination' => '+442079460111',
        ]);

        $response->assertStatus(200);
        $this->assertFalse($response->json('allowed'));
        $this->assertEquals('international', $response->json('destination_type'));
    }

    public function test_toggle_recording_legal_hold(): void
    {
        $service = app(PbxAdvancedOperationsService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $hold = PbxRecordingLegalHold::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($hold);
        $originalStatus = $hold->status;

        $response = $this->actingAs($this->user)->postJson(route('pbx.advanced_operations.legal_hold_toggle', [
            'holdUuid' => $hold->uuid,
        ]));

        $response->assertStatus(200);
        $this->assertNotEquals($originalStatus, $response->json('status'));
        $this->assertNotEmpty($response->json('tamper_checksum_sha256'));
    }

    public function test_remote_reboot_device(): void
    {
        $service = app(PbxAdvancedOperationsService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $dev = PbxDeviceFleet::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($dev);

        $response = $this->actingAs($this->user)->postJson(route('pbx.advanced_operations.device_reboot', [
            'deviceUuid' => $dev->uuid,
        ]));

        $response->assertStatus(200);
        $this->assertEquals('SUCCESS', $response->json('status'));
        $this->assertStringContainsString('check-sync;reboot=true', $response->json('sip_event'));
    }
}
