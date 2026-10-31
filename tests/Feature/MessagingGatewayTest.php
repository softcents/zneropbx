<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MessagingProviderConnector;
use App\Models\MessagingRoutingPolicy;
use App\Models\MessagingSuppressionDnd;
use App\Models\MessagingQueueItem;
use App\Services\MessagingGatewayService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessagingGatewayTest extends TestCase
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

    public function test_messaging_studio_dashboard_loads(): void
    {
        $response = $this->actingAs($this->user)->get(route('messaging.sms_voice_studio'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Messaging/SmsVoiceStudio')
            ->has('connectors')
            ->has('policies')
            ->has('suppressions')
            ->has('queueItems')
            ->has('voiceCampaigns')
            ->has('metrics')
        );
    }

    public function test_calculate_message_segments_gsm7_and_unicode(): void
    {
        // 1. GSM-7 test
        $resGsm = $this->actingAs($this->user)->postJson(route('messaging.sms_voice_studio.calculate_segments'), [
            'message_body' => 'Standard ASCII English OTP message 123456.',
        ]);
        $resGsm->assertStatus(200);
        $this->assertStringContainsString('GSM-7', $resGsm->json('encoding'));
        $this->assertEquals(1, $resGsm->json('segments'));

        // 2. Unicode test
        $resUni = $this->actingAs($this->user)->postJson(route('messaging.sms_voice_studio.calculate_segments'), [
            'message_body' => 'আপনার ওয়ান-টাইম পাসওয়ার্ড হল ৮৪৯২০১। 🚀',
        ]);
        $resUni->assertStatus(200);
        $this->assertStringContainsString('UCS-2', $resUni->json('encoding'));
    }

    public function test_dispatch_transactional_otp_sms(): void
    {
        $service = app(MessagingGatewayService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $response = $this->actingAs($this->user)->postJson(route('messaging.sms_voice_studio.send'), [
            'recipient_phone' => '+15125550105',
            'sender_id' => 'ZNERO_AUTH',
            'traffic_type' => 'transactional_otp',
            'message_body' => 'Your authentication passcode is 994821.',
            'voice_tts_enabled' => false,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertEquals(10, $response->json('priority_tier'));
        $this->assertEquals('DELIVRD', $response->json('dlr_status'));
        $this->assertGreaterThan(0, (float)$response->json('retail_billed'));
    }

    public function test_dnd_tcpa_suppression_blocks_dispatch(): void
    {
        $service = app(MessagingGatewayService::class);
        $service->ensureInitialDataset($this->domainUuid);

        // +15125550999 is in suppression list
        $response = $this->actingAs($this->user)->postJson(route('messaging.sms_voice_studio.send'), [
            'recipient_phone' => '+15125550999',
            'sender_id' => 'ZNERO_PROMO',
            'traffic_type' => 'marketing_bulk',
            'message_body' => 'Special promotion on SIP lines!',
            'voice_tts_enabled' => false,
        ]);

        $response->assertStatus(200);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('TCPA_DND_BLOCKED', $response->json('code'));
    }

    public function test_toggle_connector_bind(): void
    {
        $service = app(MessagingGatewayService::class);
        $service->ensureInitialDataset($this->domainUuid);

        $conn = MessagingProviderConnector::where('domain_uuid', $this->domainUuid)->first();
        $this->assertNotNull($conn);

        $response = $this->actingAs($this->user)->postJson(route('messaging.sms_voice_studio.toggle_bind', [
            'connectorUuid' => $conn->uuid,
        ]));

        $response->assertStatus(200);
        $this->assertNotEmpty($response->json('bind_status'));
    }

    public function test_add_and_remove_dnd_number(): void
    {
        $phone = '+15125559876';
        $addRes = $this->actingAs($this->user)->postJson(route('messaging.sms_voice_studio.add_dnd'), [
            'phone_number' => $phone,
            'reason' => 'manual_dnd',
        ]);

        $addRes->assertStatus(200);
        $uuid = $addRes->json('uuid');
        $this->assertNotEmpty($uuid);

        $delRes = $this->actingAs($this->user)->deleteJson(route('messaging.sms_voice_studio.remove_dnd', [
            'suppressionUuid' => $uuid,
        ]));

        $delRes->assertStatus(200);
        $this->assertTrue($delRes->json('success'));
    }
}
