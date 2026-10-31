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
        // 1. Autonomous AI Voice Agents
        if (!Schema::hasTable('ai_voice_agents')) {
            Schema::create('ai_voice_agents', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('agent_name');
                $table->string('assigned_extension', 32)->nullable();
                $table->string('persona_role');
                $table->string('primary_model', 64)->default('gpt-4o-realtime');
                $table->string('tts_voice', 64)->default('ElevenLabs.Rachel-Neural');
                $table->decimal('speaking_rate', 3, 2)->default(1.00);
                $table->boolean('barge_in_enabled')->default(true);
                $table->text('system_prompt');
                $table->string('prompt_version', 32)->default('v1.2');
                $table->boolean('rag_knowledge_base_enabled')->default(true);
                $table->decimal('escalation_sentiment_threshold', 4, 2)->default(-0.60);
                $table->string('human_handoff_queue_uuid', 64)->default('1002');
                $table->integer('max_turn_duration_sec')->default(300);
                $table->string('status', 32)->default('active'); // active, training, maintenance
                $table->integer('total_calls_handled')->default(0);
                $table->integer('avg_latency_ms')->default(280);
                $table->decimal('csat_score', 3, 2)->default(4.80);
                $table->timestamps();
            });
        }

        // 2. RAG Knowledge Base Documents
        if (!Schema::hasTable('ai_voice_knowledge_documents')) {
            Schema::create('ai_voice_knowledge_documents', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('document_title');
                $table->string('category', 64)->default('general'); // billing, technical, compliance, product
                $table->text('document_content');
                $table->integer('chunk_count')->default(4);
                $table->timestamp('freshness_date')->nullable();
                $table->string('vector_index_status', 32)->default('indexed'); // indexed, syncing, failed
                $table->string('access_tier', 32)->default('public_support');
                $table->timestamps();
            });
        }

        // 3. AI Voice Call Sessions & Dialogue Logs
        if (!Schema::hasTable('ai_voice_call_sessions')) {
            Schema::create('ai_voice_call_sessions', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->uuid('agent_uuid')->index();
                $table->string('caller_phone', 32);
                $table->string('caller_name')->nullable();
                $table->integer('session_duration_sec')->default(45);
                $table->decimal('sentiment_score', 4, 2)->default(0.75);
                $table->boolean('hand_off_triggered')->default(false);
                $table->string('handoff_reason')->nullable();
                $table->integer('stt_turnaround_ms')->default(65);
                $table->integer('llm_inference_ms')->default(130);
                $table->integer('tts_synthesis_ms')->default(80);
                $table->integer('total_latency_ms')->default(275);
                $table->integer('tokens_used')->default(340);
                $table->text('call_summary')->nullable();
                $table->json('full_transcript_json')->nullable();
                $table->decimal('cost_usd', 8, 5)->default(0.01250);
                $table->timestamps();
            });
        }

        // 4. Provider Adapters & BYOK Vault
        if (!Schema::hasTable('ai_voice_provider_adapters')) {
            Schema::create('ai_voice_provider_adapters', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('provider_name');
                $table->string('api_endpoint');
                $table->string('api_key_masked', 64);
                $table->boolean('supports_realtime_audio')->default(true);
                $table->boolean('is_healthy')->default(true);
                $table->integer('priority_order')->default(1);
                $table->decimal('cost_per_minute_usd', 8, 5)->default(0.02400);
                $table->timestamps();
            });
        }

        // 5. Usage Quotas & Spend Metering
        if (!Schema::hasTable('ai_voice_usage_quotas')) {
            Schema::create('ai_voice_usage_quotas', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->bigInteger('monthly_token_quota')->default(50000000);
                $table->integer('monthly_minutes_quota')->default(10000);
                $table->bigInteger('tokens_consumed_month')->default(4120900);
                $table->integer('minutes_consumed_month')->default(1420);
                $table->decimal('monthly_spend_usd', 10, 4)->default(84.5000);
                $table->decimal('spend_budget_ceiling_usd', 10, 4)->default(500.0000);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_voice_usage_quotas');
        Schema::dropIfExists('ai_voice_provider_adapters');
        Schema::dropIfExists('ai_voice_call_sessions');
        Schema::dropIfExists('ai_voice_knowledge_documents');
        Schema::dropIfExists('ai_voice_agents');
    }
};
