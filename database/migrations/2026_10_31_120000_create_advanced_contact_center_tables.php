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
        // 1. Break Reasons Table
        if (!Schema::hasTable('contact_center_break_reasons')) {
            Schema::create('contact_center_break_reasons', function (Blueprint $table) {
                $table->uuid('break_reason_uuid')->primary();
                $table->uuid('domain_uuid')->nullable()->index();
                $table->string('name');
                $table->string('code')->index();
                $table->integer('max_minutes')->default(15);
                $table->boolean('is_paid')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Agent Status Timeline Logs
        if (!Schema::hasTable('contact_center_agent_status_logs')) {
            Schema::create('contact_center_agent_status_logs', function (Blueprint $table) {
                $table->uuid('log_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('agent_uuid')->index();
                $table->string('status', 50)->index();
                $table->uuid('break_reason_uuid')->nullable()->index();
                $table->timestamp('started_at')->index();
                $table->timestamp('ended_at')->nullable()->index();
                $table->integer('duration_seconds')->nullable();
                $table->string('call_uuid')->nullable()->index();
                $table->timestamps();
            });
        }

        // 3. Queue Advanced Settings (SLA, Thresholds & Intelligence)
        if (!Schema::hasTable('contact_center_queue_settings')) {
            Schema::create('contact_center_queue_settings', function (Blueprint $table) {
                $table->uuid('queue_setting_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('queue_uuid')->unique();
                $table->integer('sla_seconds')->default(20);
                $table->decimal('sla_target_percentage', 5, 2)->default(80.00);
                $table->integer('max_wait_time_seconds')->default(180);
                $table->decimal('abandon_alert_threshold', 5, 2)->default(15.00);
                $table->boolean('smart_callback_enabled')->default(false);
                $table->integer('smart_callback_threshold_seconds')->default(120);
                $table->integer('vip_boost_score')->default(10);
                $table->uuid('overflow_queue_uuid')->nullable();
                $table->integer('overflow_callers_count')->default(10);
                $table->timestamps();
            });
        }

        // 4. Smart Callbacks Queue
        if (!Schema::hasTable('contact_center_callbacks')) {
            Schema::create('contact_center_callbacks', function (Blueprint $table) {
                $table->uuid('callback_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('queue_uuid')->index();
                $table->string('caller_number');
                $table->string('caller_name')->nullable();
                $table->string('status', 30)->default('pending')->index();
                $table->integer('attempts')->default(0);
                $table->timestamp('scheduled_at')->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Supervisor Scopes and RBAC
        if (!Schema::hasTable('supervisor_scopes')) {
            Schema::create('supervisor_scopes', function (Blueprint $table) {
                $table->uuid('scope_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('user_uuid')->index();
                $table->string('role_name', 50)->default('Supervisor');
                $table->boolean('all_queues')->default(true);
                $table->json('assigned_queues')->nullable();
                $table->boolean('can_listen')->default(true);
                $table->boolean('can_whisper')->default(true);
                $table->boolean('can_barge')->default(true);
                $table->boolean('can_takeover')->default(true);
                $table->boolean('can_force_logout')->default(true);
                $table->timestamps();
            });
        }

        // 6. Supervisor Audit Logs (Immutable)
        if (!Schema::hasTable('supervisor_audit_logs')) {
            Schema::create('supervisor_audit_logs', function (Blueprint $table) {
                $table->uuid('audit_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('supervisor_user_uuid')->index();
                $table->string('supervisor_extension', 50)->nullable();
                $table->string('action_type', 50)->index();
                $table->uuid('target_agent_uuid')->nullable()->index();
                $table->string('target_agent_name')->nullable();
                $table->string('target_channel_uuid')->nullable()->index();
                $table->uuid('target_queue_uuid')->nullable()->index();
                $table->string('reason_code', 50)->nullable();
                $table->text('reason_description')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('status', 20)->default('SUCCESS');
                $table->text('details')->nullable();
                $table->timestamp('created_at')->useCurrent()->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervisor_audit_logs');
        Schema::dropIfExists('supervisor_scopes');
        Schema::dropIfExists('contact_center_callbacks');
        Schema::dropIfExists('contact_center_queue_settings');
        Schema::dropIfExists('contact_center_agent_status_logs');
        Schema::dropIfExists('contact_center_break_reasons');
    }
};
