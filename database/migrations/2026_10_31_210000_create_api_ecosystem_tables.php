<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Scoped API Credentials & Tokens
        if (!Schema::hasTable('api_ecosystem_credentials')) {
            Schema::create('api_ecosystem_credentials', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('name');
                $table->string('key_id', 64)->unique();
                $table->string('secret_hash');
                $table->string('secret_preview', 32);
                $table->json('scopes'); // e.g. ["calls:read", "sms:send", "billing:read"]
                $table->integer('rate_limit_per_minute')->default(600);
                $table->string('status', 32)->default('active'); // active, revoked, expired
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Webhook Subscriptions
        if (!Schema::hasTable('api_ecosystem_webhooks')) {
            Schema::create('api_ecosystem_webhooks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->uuid('credential_uuid')->nullable()->index();
                $table->string('name');
                $table->text('target_url');
                $table->string('signing_secret', 64);
                $table->json('subscribed_events'); // e.g. ["call.completed", "sms.delivered"]
                $table->boolean('is_active')->default(true);
                $table->integer('consecutive_failures')->default(0);
                $table->string('status', 32)->default('healthy'); // healthy, degraded, quarantined
                $table->bigInteger('total_delivered')->default(0);
                $table->bigInteger('total_failed')->default(0);
                $table->timestamps();
            });
        }

        // 3. Webhook Delivery & Dead-Letter Audit Logs
        if (!Schema::hasTable('api_ecosystem_webhook_logs')) {
            Schema::create('api_ecosystem_webhook_logs', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('webhook_uuid')->index();
                $table->uuid('domain_uuid')->index();
                $table->string('event_type', 64);
                $table->string('idempotency_key', 64)->nullable()->index();
                $table->integer('http_status')->default(200);
                $table->json('payload_json');
                $table->string('request_signature', 128);
                $table->integer('latency_ms')->default(45);
                $table->integer('attempt_count')->default(1);
                $table->string('delivery_status', 32)->default('delivered'); // delivered, retrying, dead_letter
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }

        // 4. API Request Idempotency Registry
        if (!Schema::hasTable('api_ecosystem_idempotency_keys')) {
            Schema::create('api_ecosystem_idempotency_keys', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('idempotency_key', 64)->index();
                $table->string('endpoint');
                $table->string('request_hash', 64);
                $table->integer('http_response_code')->default(200);
                $table->json('response_payload');
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->unique(['domain_uuid', 'idempotency_key']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_ecosystem_idempotency_keys');
        Schema::dropIfExists('api_ecosystem_webhook_logs');
        Schema::dropIfExists('api_ecosystem_webhooks');
        Schema::dropIfExists('api_ecosystem_credentials');
    }
};
