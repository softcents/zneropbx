<?php

namespace App\Services;

use App\Models\MessagingProviderConnector;
use App\Models\MessagingRoutingPolicy;
use App\Models\MessagingSuppressionDnd;
use App\Models\MessagingQueueItem;
use App\Models\MessagingVoiceSmsCampaign;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MessagingGatewayService
{
    /**
     * Ensure default messaging initial dataset is populated.
     */
    public function ensureInitialDataset(string $domainUuid): void
    {
        // 1. Provider Connectors
        if (MessagingProviderConnector::where('domain_uuid', $domainUuid)->count() === 0) {
            $connectors = [
                [
                    'provider_name' => 'Telnyx Direct High-Throughput',
                    'protocol' => 'HTTP_REST',
                    'smpp_system_id' => null,
                    'smpp_bind_mode' => 'transceiver',
                    'host' => 'api.telnyx.com',
                    'port' => 443,
                    'tps_limit' => 100,
                    'enquire_link_interval_sec' => 30,
                    'bind_status' => 'bound',
                    'success_rate_pct' => 99.20,
                    'cost_per_segment' => 0.00320,
                    'is_active' => true,
                    'health_last_checked_at' => now()->subSeconds(10),
                ],
                [
                    'provider_name' => 'Infobip Global SMPP v3.4 Gateway',
                    'protocol' => 'SMPP_V34',
                    'smpp_system_id' => 'zneropbx_smpp_core_01',
                    'smpp_bind_mode' => 'transceiver',
                    'host' => 'smpp.infobip.com',
                    'port' => 2775,
                    'tps_limit' => 50,
                    'enquire_link_interval_sec' => 30,
                    'bind_status' => 'bound',
                    'success_rate_pct' => 98.80,
                    'cost_per_segment' => 0.00350,
                    'is_active' => true,
                    'health_last_checked_at' => now()->subSeconds(15),
                ],
                [
                    'provider_name' => 'Twilio Messaging Service',
                    'protocol' => 'HTTP_REST',
                    'smpp_system_id' => null,
                    'smpp_bind_mode' => 'transceiver',
                    'host' => 'api.twilio.com',
                    'port' => 443,
                    'tps_limit' => 80,
                    'enquire_link_interval_sec' => 30,
                    'bind_status' => 'bound',
                    'success_rate_pct' => 99.10,
                    'cost_per_segment' => 0.00410,
                    'is_active' => true,
                    'health_last_checked_at' => now()->subSeconds(25),
                ],
                [
                    'provider_name' => 'Meta WhatsApp Cloud Business API',
                    'protocol' => 'WHATSAPP_BUSINESS_API',
                    'smpp_system_id' => null,
                    'smpp_bind_mode' => 'transceiver',
                    'host' => 'graph.facebook.com',
                    'port' => 443,
                    'tps_limit' => 50,
                    'enquire_link_interval_sec' => 60,
                    'bind_status' => 'bound',
                    'success_rate_pct' => 97.90,
                    'cost_per_segment' => 0.00500,
                    'is_active' => true,
                    'health_last_checked_at' => now()->subSeconds(40),
                ],
            ];

            foreach ($connectors as $c) {
                MessagingProviderConnector::create(array_merge($c, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        $telnyx = MessagingProviderConnector::where('domain_uuid', $domainUuid)->where('provider_name', 'like', '%Telnyx%')->first();
        $infobip = MessagingProviderConnector::where('domain_uuid', $domainUuid)->where('provider_name', 'like', '%Infobip%')->first();
        $twilio = MessagingProviderConnector::where('domain_uuid', $domainUuid)->where('provider_name', 'like', '%Twilio%')->first();
        $meta = MessagingProviderConnector::where('domain_uuid', $domainUuid)->where('provider_name', 'like', '%Meta%')->first();

        // 2. Routing Policies
        if (MessagingRoutingPolicy::where('domain_uuid', $domainUuid)->count() === 0) {
            $policies = [
                [
                    'policy_name' => 'Critical Transactional OTP & Security Alerts',
                    'traffic_type' => 'transactional_otp',
                    'queue_priority' => 10, // Maximum tier priority
                    'primary_connector_uuid' => $telnyx ? $telnyx->uuid : null,
                    'failover_connector_uuid' => $twilio ? $twilio->uuid : null,
                    'prefix_match' => '*',
                    'sender_id_type' => 'short_code',
                    'max_retries' => 3,
                    'is_active' => true,
                ],
                [
                    'policy_name' => 'High-Volume Marketing Bulk LCR Campaign',
                    'traffic_type' => 'marketing_bulk',
                    'queue_priority' => 2, // Low tier fair scheduling
                    'primary_connector_uuid' => $infobip ? $infobip->uuid : null,
                    'failover_connector_uuid' => $telnyx ? $telnyx->uuid : null,
                    'prefix_match' => '+1*',
                    'sender_id_type' => 'long_code_10dlc',
                    'max_retries' => 2,
                    'is_active' => true,
                ],
                [
                    'policy_name' => 'Voice SMS High-Priority Notification Dial',
                    'traffic_type' => 'voice_sms',
                    'queue_priority' => 9,
                    'primary_connector_uuid' => $telnyx ? $telnyx->uuid : null,
                    'failover_connector_uuid' => $twilio ? $twilio->uuid : null,
                    'prefix_match' => '*',
                    'sender_id_type' => 'toll_free',
                    'max_retries' => 3,
                    'is_active' => true,
                ],
                [
                    'policy_name' => 'WhatsApp Interactive Business Notifications',
                    'traffic_type' => 'whatsapp',
                    'queue_priority' => 7,
                    'primary_connector_uuid' => $meta ? $meta->uuid : null,
                    'failover_connector_uuid' => $telnyx ? $telnyx->uuid : null,
                    'prefix_match' => '*',
                    'sender_id_type' => 'alphanumeric',
                    'max_retries' => 2,
                    'is_active' => true,
                ],
            ];

            foreach ($policies as $p) {
                MessagingRoutingPolicy::create(array_merge($p, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 3. Suppression List & DND
        if (MessagingSuppressionDnd::where('domain_uuid', $domainUuid)->count() === 0) {
            $dnd = [
                [
                    'phone_number' => '+15125550999',
                    'reason' => 'tcpa_opt_out',
                    'opt_out_timestamp' => now()->subDays(5),
                    'source_channel' => 'SMS_INBOUND',
                    'is_active' => true,
                ],
                [
                    'phone_number' => '+15125550888',
                    'reason' => 'stop_keyword',
                    'opt_out_timestamp' => now()->subDays(12),
                    'source_channel' => 'SMS_INBOUND',
                    'is_active' => true,
                ],
                [
                    'phone_number' => '+12125550111',
                    'reason' => 'hard_bounce',
                    'opt_out_timestamp' => now()->subDays(20),
                    'source_channel' => 'CARRIER_DLR',
                    'is_active' => true,
                ],
                [
                    'phone_number' => '+14155550222',
                    'reason' => 'manual_dnd',
                    'opt_out_timestamp' => now()->subDays(30),
                    'source_channel' => 'COMPLIANCE_DESK',
                    'is_active' => true,
                ],
            ];

            foreach ($dnd as $item) {
                MessagingSuppressionDnd::create(array_merge($item, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 4. Queue Items & DLR
        if (MessagingQueueItem::where('domain_uuid', $domainUuid)->count() === 0) {
            $queue = [
                [
                    'traffic_type' => 'transactional_otp',
                    'priority_tier' => 10,
                    'sender_id' => 'ZNERO_AUTH',
                    'recipient_phone' => '+15125550101',
                    'message_body' => 'Your Znero PBX one-time passcode is 849201. Valid for 5 minutes. Do not share.',
                    'segment_count' => 1,
                    'voice_tts_enabled' => false,
                    'status' => 'delivered',
                    'provider_connector_uuid' => $telnyx ? $telnyx->uuid : null,
                    'external_message_id' => 'msg_tlnx_994827104921',
                    'dlr_status' => 'DELIVRD',
                    'dlr_timestamp' => now()->subMinutes(8),
                    'cost_amount' => 0.00320,
                    'retail_billed_amount' => 0.01000,
                    'retry_count' => 0,
                ],
                [
                    'traffic_type' => 'transactional_otp',
                    'priority_tier' => 10,
                    'sender_id' => 'ZNERO_NOC',
                    'recipient_phone' => '+15125550102',
                    'message_body' => 'CRITICAL NOC ALERT: FreeSWITCH node fs-core-austin-01 CPU reached 84%. BGP Anycast standby active.',
                    'segment_count' => 1,
                    'voice_tts_enabled' => false,
                    'status' => 'delivered',
                    'provider_connector_uuid' => $infobip ? $infobip->uuid : null,
                    'external_message_id' => 'smpp_pdu_submit_sm_8849102',
                    'dlr_status' => 'DELIVRD',
                    'dlr_timestamp' => now()->subMinutes(25),
                    'cost_amount' => 0.00350,
                    'retail_billed_amount' => 0.01000,
                    'retry_count' => 0,
                ],
                [
                    'traffic_type' => 'voice_sms',
                    'priority_tier' => 9,
                    'sender_id' => '+18005550199',
                    'recipient_phone' => '+15125550103',
                    'message_body' => 'Urgent security notice: please press 1 to confirm your PBX portal password change request.',
                    'segment_count' => 1,
                    'voice_tts_enabled' => true,
                    'status' => 'delivered',
                    'provider_connector_uuid' => $telnyx ? $telnyx->uuid : null,
                    'external_message_id' => 'voice_call_tlnx_440192',
                    'dlr_status' => 'DELIVRD',
                    'dlr_timestamp' => now()->subMinutes(42),
                    'cost_amount' => 0.01200,
                    'retail_billed_amount' => 0.03500,
                    'retry_count' => 0,
                ],
                [
                    'traffic_type' => 'marketing_bulk',
                    'priority_tier' => 2,
                    'sender_id' => '+15125550188',
                    'recipient_phone' => '+15125550104',
                    'message_body' => 'Unlock enterprise VoIP savings: get 20% off all DID trunks this quarter. Reply STOP to opt out.',
                    'segment_count' => 1,
                    'voice_tts_enabled' => false,
                    'status' => 'delivered',
                    'provider_connector_uuid' => $infobip ? $infobip->uuid : null,
                    'external_message_id' => 'smpp_pdu_submit_sm_339104',
                    'dlr_status' => 'DELIVRD',
                    'dlr_timestamp' => now()->subHours(2),
                    'cost_amount' => 0.00350,
                    'retail_billed_amount' => 0.00800,
                    'retry_count' => 0,
                ],
            ];

            foreach ($queue as $q) {
                MessagingQueueItem::create(array_merge($q, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 5. Voice SMS Campaigns
        if (MessagingVoiceSmsCampaign::where('domain_uuid', $domainUuid)->count() === 0) {
            $campaigns = [
                [
                    'campaign_title' => 'Emergency Network Maintenance Broadcast',
                    'tts_voice' => 'Google.en-US-Neural2-F',
                    'tts_script' => 'Attention: Emergency maintenance will commence on core network switches at 02:00 UTC. Press 1 to acknowledge receipt.',
                    'dtmf_confirm_key' => '1 to acknowledge',
                    'recipients_count' => 250,
                    'completed_count' => 248,
                    'acknowledged_count' => 219,
                    'status' => 'completed',
                ],
                [
                    'campaign_title' => 'Executive Priority Appointment Confirmation Dial',
                    'tts_voice' => 'Polly.Joanna-Neural',
                    'tts_script' => 'This is an automated confirmation from Znero Telecom. Your quarterly platform review is scheduled for tomorrow at 10 AM. Press 1 to confirm, or 2 to reschedule.',
                    'dtmf_confirm_key' => '1 to confirm, 2 to reschedule',
                    'recipients_count' => 140,
                    'completed_count' => 138,
                    'acknowledged_count' => 128,
                    'status' => 'running',
                ],
            ];

            foreach ($campaigns as $camp) {
                MessagingVoiceSmsCampaign::create(array_merge($camp, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }
    }

    /**
     * Compute message segment count and encoding.
     */
    public function calculateMessageSegments(string $text): array
    {
        $len = mb_strlen($text, 'UTF-8');
        // Check if string contains characters outside GSM 7-bit default alphabet
        $isUnicode = preg_match('/[^\x20-\x7E\r\n\t€£$¥èéùìòÇØøÅåΔ_ΦΓΛΩΠΨΣΘΞ^{}\[~\]|]/u', $text);

        if ($isUnicode) {
            $encoding = 'UCS-2 (16-bit Unicode)';
            $segmentLimit = 70;
            $concatLimit = 67;
        } else {
            $encoding = 'GSM-7 (7-bit Default)';
            $segmentLimit = 160;
            $concatLimit = 153;
        }

        if ($len <= $segmentLimit) {
            $segments = 1;
        } else {
            $segments = (int)ceil($len / $concatLimit);
        }

        return [
            'character_count' => $len,
            'encoding' => $encoding,
            'segments' => $segments,
            'is_unicode' => (bool)$isUnicode,
        ];
    }

    /**
     * Simulate sending an SMS or Voice SMS through the routing engine.
     */
    public function simulateSend(array $data, string $domainUuid): array
    {
        $recipient = trim($data['recipient_phone'] ?? '');
        $senderId = trim($data['sender_id'] ?? 'ZNERO');
        $message = trim($data['message_body'] ?? '');
        $trafficType = $data['traffic_type'] ?? 'transactional_otp';
        $voiceTts = !empty($data['voice_tts_enabled']);

        // 1. DND & TCPA Suppression Check
        $isSuppressed = MessagingSuppressionDnd::where('domain_uuid', $domainUuid)
            ->where('is_active', true)
            ->where('phone_number', $recipient)
            ->first();

        if ($isSuppressed) {
            $item = MessagingQueueItem::create([
                'uuid' => Str::uuid()->toString(),
                'domain_uuid' => $domainUuid,
                'traffic_type' => $trafficType,
                'priority_tier' => 1,
                'sender_id' => $senderId,
                'recipient_phone' => $recipient,
                'message_body' => $message,
                'segment_count' => 1,
                'voice_tts_enabled' => $voiceTts,
                'status' => 'suppressed_dnd',
                'dlr_status' => 'REJECTD',
                'cost_amount' => 0.00000,
                'retail_billed_amount' => 0.00000,
            ]);

            return [
                'success' => false,
                'code' => 'TCPA_DND_BLOCKED',
                'reason' => "Recipient {$recipient} is on the Active DND Suppression List ({$isSuppressed->reason}). Message dropped to protect regulatory compliance.",
                'queue_item' => $item,
            ];
        }

        // 2. Segment calculation
        $segmentInfo = $this->calculateMessageSegments($message);
        $segments = $segmentInfo['segments'];

        // 3. Match Routing Policy
        $policy = MessagingRoutingPolicy::where('domain_uuid', $domainUuid)
            ->where('traffic_type', $trafficType)
            ->where('is_active', true)
            ->orderBy('queue_priority', 'desc')
            ->first();

        $connector = null;
        if ($policy && $policy->primary_connector_uuid) {
            $connector = MessagingProviderConnector::where('uuid', $policy->primary_connector_uuid)
                ->where('bind_status', 'bound')
                ->first();
        }

        if (!$connector) {
            $connector = MessagingProviderConnector::where('domain_uuid', $domainUuid)
                ->where('bind_status', 'bound')
                ->first();
        }

        $ratePerSeg = $connector ? $connector->cost_per_segment : 0.00350;
        $cost = round($ratePerSeg * $segments, 5);
        $billed = round(0.01000 * $segments, 5);
        if ($voiceTts) {
            $cost += 0.00800;
            $billed += 0.02500;
        }

        // 4. Create Queue Item with DLR lifecycle
        $item = MessagingQueueItem::create([
            'uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'traffic_type' => $trafficType,
            'priority_tier' => $policy ? $policy->queue_priority : 10,
            'sender_id' => $senderId,
            'recipient_phone' => $recipient,
            'message_body' => $message,
            'segment_count' => $segments,
            'voice_tts_enabled' => $voiceTts,
            'status' => 'delivered',
            'provider_connector_uuid' => $connector ? $connector->uuid : null,
            'external_message_id' => 'msg_' . Str::random(18),
            'dlr_status' => 'DELIVRD',
            'dlr_timestamp' => now(),
            'cost_amount' => $cost,
            'retail_billed_amount' => $billed,
            'retry_count' => 0,
        ]);

        return [
            'success' => true,
            'queue_item' => $item,
            'segment_info' => $segmentInfo,
            'routed_via' => $connector ? $connector->provider_name : 'Default REST Edge',
            'protocol' => $connector ? $connector->protocol : 'HTTP_REST',
            'priority_tier' => $item->priority_tier,
            'dlr_status' => 'DELIVRD',
            'cost_incurred' => $cost,
            'retail_billed' => $billed,
            'profit_margin' => round($billed - $cost, 5),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Toggle Provider Connector Bind (SMPP bind / REST health).
     */
    public function toggleConnectorBind(string $connectorUuid, string $domainUuid): array
    {
        $connector = MessagingProviderConnector::where('uuid', $connectorUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $newStatus = $connector->bind_status === 'bound' ? 'unbound' : 'bound';
        $connector->update([
            'bind_status' => $newStatus,
            'health_last_checked_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'uuid' => $connector->uuid,
            'provider_name' => $connector->provider_name,
            'protocol' => $connector->protocol,
            'bind_status' => $connector->bind_status,
            'message' => $newStatus === 'bound'
                ? "SMPP/REST bind established with {$connector->host}:{$connector->port}. Enquire_link heartbeat active."
                : "Connector unbind sent. Channel quarantined from routing pool.",
        ];
    }

    /**
     * Add number to DND suppression list.
     */
    public function addSuppression(string $phone, string $reason, string $domainUuid): MessagingSuppressionDnd
    {
        return MessagingSuppressionDnd::create([
            'uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'phone_number' => $phone,
            'reason' => $reason,
            'opt_out_timestamp' => now(),
            'source_channel' => 'MANUAL_ENTRY',
            'is_active' => true,
        ]);
    }

    /**
     * Remove number from DND suppression list.
     */
    public function removeSuppression(string $suppressionUuid, string $domainUuid): bool
    {
        return (bool)MessagingSuppressionDnd::where('uuid', $suppressionUuid)
            ->where('domain_uuid', $domainUuid)
            ->delete();
    }
}
