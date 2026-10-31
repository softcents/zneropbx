<?php

namespace App\Services;

use App\Models\ApiEcosystemCredential;
use App\Models\ApiEcosystemWebhook;
use App\Models\ApiEcosystemWebhookLog;
use App\Models\ApiEcosystemIdempotencyKey;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiEcosystemService
{
    /**
     * Ensure baseline API credentials, webhook endpoints, and delivery logs exist.
     */
    public function ensureInitialDataset(string $domainUuid): void
    {
        // 1. Scoped API Credentials
        if (ApiEcosystemCredential::where('domain_uuid', $domainUuid)->count() === 0) {
            $credentials = [
                [
                    'name' => 'Production Webhook & Billing Stream',
                    'key_id' => 'zn_live_' . Str::random(24),
                    'secret_preview' => 'zn_sec_***' . strtolower(Str::random(4)),
                    'scopes' => ['billing:read', 'billing:charge', 'calls:read', 'webhooks:manage'],
                    'rate_limit_per_minute' => 1200,
                    'status' => 'active',
                    'last_used_at' => now()->subMinutes(3),
                    'expires_at' => now()->addMonths(12),
                ],
                [
                    'name' => 'Salesforce CTI & Realtime Agent Sync',
                    'key_id' => 'zn_live_' . Str::random(24),
                    'secret_preview' => 'zn_sec_***' . strtolower(Str::random(4)),
                    'scopes' => ['calls:read', 'calls:write', 'agents:read', 'routing:read'],
                    'rate_limit_per_minute' => 600,
                    'status' => 'active',
                    'last_used_at' => now()->subMinutes(12),
                    'expires_at' => now()->addMonths(6),
                ],
                [
                    'name' => 'Carrier NOC & Prometheus Exporter',
                    'key_id' => 'zn_live_' . Str::random(24),
                    'secret_preview' => 'zn_sec_***' . strtolower(Str::random(4)),
                    'scopes' => ['telecom:read', 'infrastructure:read', 'metrics:export'],
                    'rate_limit_per_minute' => 3000,
                    'status' => 'active',
                    'last_used_at' => now()->subSeconds(45),
                    'expires_at' => now()->addMonths(24),
                ],
            ];

            foreach ($credentials as $cred) {
                ApiEcosystemCredential::create([
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                    'name' => $cred['name'],
                    'key_id' => $cred['key_id'],
                    'secret_hash' => Hash::make(Str::random(40)),
                    'secret_preview' => $cred['secret_preview'],
                    'scopes' => $cred['scopes'],
                    'rate_limit_per_minute' => $cred['rate_limit_per_minute'],
                    'status' => $cred['status'],
                    'last_used_at' => $cred['last_used_at'],
                    'expires_at' => $cred['expires_at'],
                ]);
            }
        }

        // 2. Webhook Subscriptions
        if (ApiEcosystemWebhook::where('domain_uuid', $domainUuid)->count() === 0) {
            $webhooks = [
                [
                    'name' => 'HubSpot CRM Deal & Call Journey Stream',
                    'target_url' => 'https://api.hubspot.com/crm/v3/telecom-events/znero-stream',
                    'signing_secret' => 'whsec_' . Str::random(32),
                    'subscribed_events' => ['call.completed', 'call.recorded', 'sms.delivered'],
                    'is_active' => true,
                    'consecutive_failures' => 0,
                    'status' => 'healthy',
                    'total_delivered' => 14280,
                    'total_failed' => 2,
                ],
                [
                    'name' => 'Zendesk Automated Ticket & Sentiment Escalation',
                    'target_url' => 'https://enterprise-helpdesk.zendesk.com/api/v2/channels/voice/tickets',
                    'signing_secret' => 'whsec_' . Str::random(32),
                    'subscribed_events' => ['ai.sentiment_escalated', 'call.missed', 'agent.handoff_requested'],
                    'is_active' => true,
                    'consecutive_failures' => 0,
                    'status' => 'healthy',
                    'total_delivered' => 890,
                    'total_failed' => 0,
                ],
                [
                    'name' => 'Stripe Revenue Assurance & Metered CDR Stream',
                    'target_url' => 'https://billing.corp.znero.com/api/v1/metered-cdr-ingest',
                    'signing_secret' => 'whsec_' . Str::random(32),
                    'subscribed_events' => ['invoice.rated', 'wallet.auto_topped', 'cdr.billed'],
                    'is_active' => true,
                    'consecutive_failures' => 0,
                    'status' => 'healthy',
                    'total_delivered' => 31200,
                    'total_failed' => 5,
                ],
            ];

            foreach ($webhooks as $wh) {
                ApiEcosystemWebhook::create([
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                    'credential_uuid' => null,
                    'name' => $wh['name'],
                    'target_url' => $wh['target_url'],
                    'signing_secret' => $wh['signing_secret'],
                    'subscribed_events' => $wh['subscribed_events'],
                    'is_active' => $wh['is_active'],
                    'consecutive_failures' => $wh['consecutive_failures'],
                    'status' => $wh['status'],
                    'total_delivered' => $wh['total_delivered'],
                    'total_failed' => $wh['total_failed'],
                ]);
            }
        }

        // 3. Webhook Delivery Logs
        if (ApiEcosystemWebhookLog::where('domain_uuid', $domainUuid)->count() === 0) {
            $sampleWebhook = ApiEcosystemWebhook::where('domain_uuid', $domainUuid)->first();
            if ($sampleWebhook) {
                $events = [
                    [
                        'event_type' => 'call.completed',
                        'payload' => [
                            'call_uuid' => Str::uuid()->toString(),
                            'caller_id' => '+15125550101',
                            'destination' => '+15125550199',
                            'direction' => 'inbound',
                            'duration_sec' => 142,
                            'disposition' => 'ANSWERED',
                            'sip_hangup_cause' => 'NORMAL_CLEARING',
                        ],
                        'http_status' => 200,
                        'latency_ms' => 48,
                    ],
                    [
                        'event_type' => 'sms.delivered',
                        'payload' => [
                            'message_uuid' => Str::uuid()->toString(),
                            'sender_id' => 'ZNERO-AUTH',
                            'recipient' => '+14155552671',
                            'segments' => 1,
                            'status' => 'DELIVERED',
                            'mno_carrier' => 'T-Mobile US',
                        ],
                        'http_status' => 200,
                        'latency_ms' => 54,
                    ],
                    [
                        'event_type' => 'ai.sentiment_escalated',
                        'payload' => [
                            'session_uuid' => Str::uuid()->toString(),
                            'agent_name' => 'Maya - Tier 1 Telecom Concierge',
                            'sentiment_score' => -0.75,
                            'trigger_reason' => 'Caller requested supervisor handoff',
                            'handoff_queue' => '1002',
                        ],
                        'http_status' => 201,
                        'latency_ms' => 62,
                    ],
                    [
                        'event_type' => 'invoice.rated',
                        'payload' => [
                            'invoice_number' => 'INV-2026-10-8812',
                            'total_minutes' => 450,
                            'rated_amount_usd' => 3.375,
                            'currency' => 'USD',
                            'customer_tier' => 'Enterprise Standard',
                        ],
                        'http_status' => 200,
                        'latency_ms' => 39,
                    ],
                ];

                foreach ($events as $idx => $ev) {
                    $jsonStr = json_encode($ev['payload']);
                    $sig = 'sha256=' . hash_hmac('sha256', $jsonStr, $sampleWebhook->signing_secret);

                    ApiEcosystemWebhookLog::create([
                        'uuid' => Str::uuid()->toString(),
                        'webhook_uuid' => $sampleWebhook->uuid,
                        'domain_uuid' => $domainUuid,
                        'event_type' => $ev['event_type'],
                        'idempotency_key' => 'idem_' . Str::random(24),
                        'http_status' => $ev['http_status'],
                        'payload_json' => $ev['payload'],
                        'request_signature' => $sig,
                        'latency_ms' => $ev['latency_ms'],
                        'attempt_count' => 1,
                        'delivery_status' => 'delivered',
                        'error_message' => null,
                        'created_at' => now()->subMinutes(($idx + 1) * 8),
                    ]);
                }
            }
        }
    }

    /**
     * Issue a new scoped API Key.
     */
    public function generateApiKey(string $domainUuid, string $name, array $scopes, int $rateLimit = 600, ?int $expiresInMonths = 12): array
    {
        $rawKeyId = 'zn_live_' . Str::random(24);
        $rawSecret = 'zn_sec_' . Str::random(40);
        $secretPreview = 'zn_sec_***' . substr($rawSecret, -4);

        $credential = ApiEcosystemCredential::create([
            'uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'name' => $name,
            'key_id' => $rawKeyId,
            'secret_hash' => Hash::make($rawSecret),
            'secret_preview' => $secretPreview,
            'scopes' => !empty($scopes) ? $scopes : ['calls:read', 'sms:send'],
            'rate_limit_per_minute' => $rateLimit,
            'status' => 'active',
            'last_used_at' => null,
            'expires_at' => $expiresInMonths ? now()->addMonths($expiresInMonths) : null,
        ]);

        return [
            'credential' => $credential,
            'raw_key_id' => $rawKeyId,
            'raw_secret' => $rawSecret, // ONLY returned upon creation!
        ];
    }

    /**
     * Revoke API Key.
     */
    public function revokeApiKey(string $credentialUuid, string $domainUuid): bool
    {
        $credential = ApiEcosystemCredential::where('uuid', $credentialUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $credential->update(['status' => 'revoked']);
        return true;
    }

    /**
     * Rotate secret for an existing API Key.
     */
    public function rotateApiKeySecret(string $credentialUuid, string $domainUuid): array
    {
        $credential = ApiEcosystemCredential::where('uuid', $credentialUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $rawSecret = 'zn_sec_' . Str::random(40);
        $secretPreview = 'zn_sec_***' . substr($rawSecret, -4);

        $credential->update([
            'secret_hash' => Hash::make($rawSecret),
            'secret_preview' => $secretPreview,
        ]);

        return [
            'credential' => $credential,
            'raw_secret' => $rawSecret,
        ];
    }

    /**
     * Create Webhook Subscription.
     */
    public function createWebhook(string $domainUuid, array $data): ApiEcosystemWebhook
    {
        $signingSecret = 'whsec_' . Str::random(32);

        return ApiEcosystemWebhook::create([
            'uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'credential_uuid' => $data['credential_uuid'] ?? null,
            'name' => $data['name'],
            'target_url' => $data['target_url'],
            'signing_secret' => $signingSecret,
            'subscribed_events' => $data['subscribed_events'] ?? ['call.completed'],
            'is_active' => true,
            'consecutive_failures' => 0,
            'status' => 'healthy',
            'total_delivered' => 0,
            'total_failed' => 0,
        ]);
    }

    /**
     * Simulate live Webhook dispatch with HMAC-SHA256 signature and replay prevention.
     */
    public function dispatchWebhookSimulation(string $webhookUuid, string $eventType, string $domainUuid): array
    {
        $webhook = ApiEcosystemWebhook::where('uuid', $webhookUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $timestamp = time();
        $idempotencyKey = 'idem_' . Str::random(24);

        $payload = [
            'id' => 'evt_' . Str::random(20),
            'event' => $eventType,
            'created_at' => $timestamp,
            'domain_uuid' => $domainUuid,
            'data' => [
                'call_uuid' => Str::uuid()->toString(),
                'direction' => 'outbound',
                'from' => '+15125550100',
                'to' => '+15125550199',
                'duration_seconds' => rand(30, 240),
                'hangup_disposition' => 'NORMAL_CLEARING',
                'qos' => [
                    'jitter_ms' => rand(2, 6),
                    'packet_loss_pct' => 0.00,
                    'mos_score' => 4.41,
                ],
                'cost_usd' => 0.0150,
            ],
        ];

        $payloadJson = json_encode($payload);
        $signedData = "t={$timestamp}.{$payloadJson}";
        $hmacSignature = hash_hmac('sha256', $signedData, $webhook->signing_secret);
        $headerSignature = "t={$timestamp},v1={$hmacSignature}";

        $latencyMs = rand(38, 75);
        $httpStatus = 200;

        // Record log
        $log = ApiEcosystemWebhookLog::create([
            'uuid' => Str::uuid()->toString(),
            'webhook_uuid' => $webhook->uuid,
            'domain_uuid' => $domainUuid,
            'event_type' => $eventType,
            'idempotency_key' => $idempotencyKey,
            'http_status' => $httpStatus,
            'payload_json' => $payload,
            'request_signature' => $headerSignature,
            'latency_ms' => $latencyMs,
            'attempt_count' => 1,
            'delivery_status' => 'delivered',
            'error_message' => null,
        ]);

        $webhook->increment('total_delivered');

        return [
            'success' => true,
            'http_status' => $httpStatus,
            'latency_ms' => $latencyMs,
            'idempotency_key' => $idempotencyKey,
            'signature_header' => $headerSignature,
            'payload' => $payload,
            'log' => $log,
        ];
    }

    /**
     * Return OpenAPI / Swagger interactive documentation catalog.
     */
    public function getApiEndpointsCatalog(): array
    {
        return [
            [
                'method' => 'GET',
                'endpoint' => '/api/v2/calls',
                'summary' => 'Search and Stream CDR Interactivity Records',
                'required_scope' => 'calls:read',
                'description' => 'Retrieve paginated call detail records with carrier route attribution, MOS score, and billing duration.',
                'parameters' => [
                    ['name' => 'start_date', 'type' => 'string (ISO8601)', 'required' => false, 'example' => '2026-10-01T00:00:00Z'],
                    ['name' => 'end_date', 'type' => 'string (ISO8601)', 'required' => false, 'example' => '2026-10-31T23:59:59Z'],
                    ['name' => 'direction', 'type' => 'string', 'required' => false, 'example' => 'inbound | outbound'],
                    ['name' => 'limit', 'type' => 'integer', 'required' => false, 'example' => '50'],
                ],
                'curl_sample' => "curl -X GET \"https://pbx.corp.znero.com/api/v2/calls?direction=inbound&limit=10\" \\\n  -H \"Authorization: Bearer zn_live_9a71b2d...\" \\\n  -H \"Accept: application/json\"",
                'response_sample' => [
                    'status' => 'success',
                    'data' => [
                        [
                            'uuid' => 'f1b982c4-001a-4f51-b883-91823901bce4',
                            'caller_id' => '+15125550101',
                            'destination' => '+15125550199',
                            'duration' => 142,
                            'mos_score' => 4.42,
                            'carrier' => 'Tata Communications Global',
                        ],
                    ],
                ],
            ],
            [
                'method' => 'POST',
                'endpoint' => '/api/v2/calls/originate',
                'summary' => 'Programmatic Click-to-Call & Outbound Origination',
                'required_scope' => 'calls:write',
                'description' => 'Bridge an outbound SIP call leg to an agent extension or contact center queue with caller-ID masking.',
                'parameters' => [
                    ['name' => 'from_number', 'type' => 'string (E.164)', 'required' => true, 'example' => '+15125550100'],
                    ['name' => 'to_number', 'type' => 'string (E.164)', 'required' => true, 'example' => '+14155550123'],
                    ['name' => 'destination_extension', 'type' => 'string', 'required' => true, 'example' => '1001'],
                    ['name' => 'caller_id_name', 'type' => 'string', 'required' => false, 'example' => 'Znero Support'],
                ],
                'curl_sample' => "curl -X POST \"https://pbx.corp.znero.com/api/v2/calls/originate\" \\\n  -H \"Authorization: Bearer zn_live_9a71b2d...\" \\\n  -H \"Idempotency-Key: idem_718294a...\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"from_number\":\"+15125550100\",\"to_number\":\"+14155550123\",\"destination_extension\":\"1001\"}'",
                'response_sample' => [
                    'status' => 'initiated',
                    'call_uuid' => '8ca12804-b91c-43fe-a901-08182937cd91',
                    'originated_at' => '2026-10-31T21:05:00Z',
                ],
            ],
            [
                'method' => 'POST',
                'endpoint' => '/api/v2/sms/send',
                'summary' => 'Dual-Tier High Priority SMS Dispatch',
                'required_scope' => 'sms:send',
                'description' => 'Dispatch transactional OTP or bulk marketing SMS with GSM-7/UCS-2 auto-segmentation and DND filtering.',
                'parameters' => [
                    ['name' => 'to', 'type' => 'string (E.164)', 'required' => true, 'example' => '+14155552671'],
                    ['name' => 'message', 'type' => 'string', 'required' => true, 'example' => 'Your security code is 912834. Expires in 5 minutes.'],
                    ['name' => 'priority_tier', 'type' => 'string', 'required' => false, 'example' => 'tier_1_otp | tier_2_bulk'],
                    ['name' => 'sender_id', 'type' => 'string', 'required' => false, 'example' => 'ZNERO'],
                ],
                'curl_sample' => "curl -X POST \"https://pbx.corp.znero.com/api/v2/sms/send\" \\\n  -H \"Authorization: Bearer zn_live_9a71b2d...\" \\\n  -H \"Idempotency-Key: idem_sms_91823...\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"to\":\"+14155552671\",\"message\":\"Your OTP is 912834\",\"priority_tier\":\"tier_1_otp\"}'",
                'response_sample' => [
                    'status' => 'queued',
                    'message_id' => 'msg_7182930aef',
                    'segments' => 1,
                    'encoding' => 'GSM-7',
                    'cost_usd' => 0.0045,
                ],
            ],
            [
                'method' => 'POST',
                'endpoint' => '/api/v2/ai/voice-agents/simulate',
                'summary' => 'Execute Streaming Turn with Autonomous Voice Agent',
                'required_scope' => 'ai:execute',
                'description' => 'Test sub-300ms neural STT, RAG knowledge query, and warm-transfer escalation triggers.',
                'parameters' => [
                    ['name' => 'agent_uuid', 'type' => 'string (UUID)', 'required' => true, 'example' => 'cc1f59a6-a565-47af-bbfc-aeed63c8da14'],
                    ['name' => 'caller_utterance', 'type' => 'string', 'required' => true, 'example' => 'What is the rate for calling London?'],
                ],
                'curl_sample' => "curl -X POST \"https://pbx.corp.znero.com/api/v2/ai/voice-agents/simulate\" \\\n  -H \"Authorization: Bearer zn_live_9a71b2d...\" \\\n  -H \"Content-Type: application/json\" \\\n  -d '{\"agent_uuid\":\"cc1f59a6...\",\"caller_utterance\":\"What is the rate for calling London?\"}'",
                'response_sample' => [
                    'spoken_response' => 'Domestic US outbound is 0.75 cents/min, while UK Tier-1 destinations start at 1.2 cents/min.',
                    'latency_ms' => 268,
                    'sentiment' => 0.85,
                    'hand_off_triggered' => false,
                ],
            ],
            [
                'method' => 'GET',
                'endpoint' => '/api/v2/billing/ledger/balance',
                'summary' => 'Query Immutable Double-Entry Ledger Wallet',
                'required_scope' => 'billing:read',
                'description' => 'Inspect realtime available balance, reserved channel limits, credit exposure, and currency code.',
                'parameters' => [],
                'curl_sample' => "curl -X GET \"https://pbx.corp.znero.com/api/v2/billing/ledger/balance\" \\\n  -H \"Authorization: Bearer zn_live_9a71b2d...\" \\\n  -H \"Accept: application/json\"",
                'response_sample' => [
                    'wallet_type' => 'prepaid',
                    'currency' => 'USD',
                    'available_balance' => 2450.75,
                    'reserved_exposure' => 50.00,
                    'credit_limit' => 500.00,
                    'status' => 'healthy',
                ],
            ],
        ];
    }
}
