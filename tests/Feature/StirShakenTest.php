<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StirShakenCertificate;
use App\Services\Telecom\StirShakenService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StirShakenTest extends TestCase
{
    protected string $domainUuid;
    protected User $user;
    protected StirShakenService $service;

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
        $this->service = app(StirShakenService::class);
    }

    public function test_stir_shaken_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('telecom.stir_shaken'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Telecom/StirShakenStudio')
            ->has('certificates')
            ->has('recentLogs')
            ->has('metrics')
        );
    }

    public function test_generate_certificate_api(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('telecom.stir_shaken.generate_cert'), [
            'cert_name' => 'Test Carrier Identity P-256',
            'x5u_url' => 'https://certs.znero.com/test.pem',
            'spc_code' => 'ZN99',
            'tn_ranges' => ['+12125550999'],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('certificate', $data);

        $this->assertDatabaseHas('stir_shaken_certificates', [
            'domain_uuid' => $this->domainUuid,
            'spc_code' => 'ZN99',
        ]);
    }

    public function test_simulate_signing_api(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('telecom.stir_shaken.simulate_signing'), [
            'caller_id' => '+12125550100',
            'destination_number' => '+14155550199',
            'attestation' => 'A',
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertEquals('A', $data['attestation']);
        $this->assertStringContainsString('Identity:', 'Identity: ' . $data['identity_header']);
        $this->assertStringContainsString('ppt=shaken', $data['identity_header']);
        $this->assertStringContainsString('alg=ES256', $data['identity_header']);
    }

    public function test_verify_passport_api(): void
    {
        $passport = $this->service->generatePassport('+12125550100', '+14155550199', 'A', $this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('telecom.stir_shaken.verify_passport'), [
            'identity_header' => $passport['identity_header'],
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['valid']);
        $this->assertEquals('A', $data['attestation']);
    }

    public function test_artisan_stir_shaken_test_command(): void
    {
        $this->artisan('enterprise:stir-shaken-test')
            ->assertExitCode(0);
    }
}
