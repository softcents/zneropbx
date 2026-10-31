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
        // 1. Messaging Provider Connectors & SMPP Bind Registry
        if (!Schema::hasTable('messaging_provider_connectors')) {
            Schema::create('messaging_provider_connectors', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('provider_name');
                $table->string('protocol', 32)->default('HTTP_REST'); // HTTP_REST, SMPP_V34, WHATSAPP_BUSINESS_API
                $table->string('smpp_system_id')->nullable();
                $table->string('smpp_bind_mode', 32)->default('transceiver'); // transceiver, transmitter, receiver
                $table->string('host', 128)->nullable();
                $table->integer('port')->default(443);
                $table->integer('tps_limit')->default(50); // Transactions per second ceiling
                $table->integer('enquire_link_interval_sec')->default(30);
                $table->string('bind_status', 32)->default('bound'); // bound, unbound, reconnecting, quarantined
                $table->decimal('success_rate_pct', 5, 2)->default(98.50);
                $table->decimal('cost_per_segment', 8, 5)->default(0.00350);
                $table->boolean('is_active')->default(true);
                $table->timestamp('health_last_checked_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Messaging Routing Policies & Pacing
        if (!Schema::hasTable('messaging_routing_policies')) {
            Schema::create('messaging_routing_policies', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('policy_name');
                $table->string('traffic_type', 32)->default('transactional_otp'); // transactional_otp, marketing_bulk, voice_sms, whatsapp
                $table->integer('queue_priority')->default(10); // 1-10
                $table->uuid('primary_connector_uuid')->nullable();
                $table->uuid('failover_connector_uuid')->nullable();
                $table->string('prefix_match', 32)->default('*');
                $table->string('sender_id_type', 32)->default('long_code_10dlc'); // short_code, long_code_10dlc, toll_free, alphanumeric
                $table->integer('max_retries')->default(3);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Suppression List & DND Compliance Registry
        if (!Schema::hasTable('messaging_suppression_dnd')) {
            Schema::create('messaging_suppression_dnd', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('phone_number', 32);
                $table->string('reason', 32)->default('stop_keyword'); // tcpa_opt_out, stop_keyword, hard_bounce, manual_dnd
                $table->timestamp('opt_out_timestamp')->nullable();
                $table->string('source_channel', 32)->default('SMS_INBOUND');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Messaging Queue Items & Delivery Receipts (DLR)
        if (!Schema::hasTable('messaging_queue_items')) {
            Schema::create('messaging_queue_items', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('traffic_type', 32)->default('transactional_otp');
                $table->integer('priority_tier')->default(10);
                $table->string('sender_id', 32);
                $table->string('recipient_phone', 32);
                $table->text('message_body');
                $table->integer('segment_count')->default(1);
                $table->boolean('voice_tts_enabled')->default(false);
                $table->string('status', 32)->default('queued'); // queued, dispatched, delivered, undelivered, suppressed_dnd, failed
                $table->uuid('provider_connector_uuid')->nullable();
                $table->string('external_message_id')->nullable();
                $table->string('dlr_status', 32)->default('PENDING'); // DELIVRD, UNDELIV, EXPIRED, REJECTD, PENDING
                $table->timestamp('dlr_timestamp')->nullable();
                $table->decimal('cost_amount', 8, 5)->default(0.00350);
                $table->decimal('retail_billed_amount', 8, 5)->default(0.01000);
                $table->integer('retry_count')->default(0);
                $table->timestamps();
            });
        }

        // 5. Voice SMS Broadcast Campaigns
        if (!Schema::hasTable('messaging_voice_sms_campaigns')) {
            Schema::create('messaging_voice_sms_campaigns', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('campaign_title');
                $table->string('tts_voice', 64)->default('Google.en-US-Neural2-F');
                $table->text('tts_script');
                $table->string('dtmf_confirm_key', 32)->default('1 to acknowledge');
                $table->integer('recipients_count')->default(0);
                $table->integer('completed_count')->default(0);
                $table->integer('acknowledged_count')->default(0);
                $table->string('status', 32)->default('running'); // draft, running, completed, paused
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messaging_voice_sms_campaigns');
        Schema::dropIfExists('messaging_queue_items');
        Schema::dropIfExists('messaging_suppression_dnd');
        Schema::dropIfExists('messaging_routing_policies');
        Schema::dropIfExists('messaging_provider_connectors');
    }
};
