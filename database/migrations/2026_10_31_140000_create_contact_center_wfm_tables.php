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
        // 1. Shift Templates
        if (!Schema::hasTable('contact_center_shifts')) {
            Schema::create('contact_center_shifts', function (Blueprint $table) {
                $table->uuid('shift_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('name', 128);
                $table->time('start_time');
                $table->time('end_time');
                $table->integer('break_duration_minutes')->default(60);
                $table->string('color', 32)->default('#4F46E5'); // Hex color for UI calendar
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Agent Schedules / Roster
        if (!Schema::hasTable('contact_center_schedules')) {
            Schema::create('contact_center_schedules', function (Blueprint $table) {
                $table->uuid('schedule_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('agent_uuid')->index();
                $table->uuid('shift_uuid')->nullable()->index();
                $table->date('schedule_date')->index();
                $table->time('start_time');
                $table->time('end_time');
                $table->string('expected_activity', 64)->default('ON_QUEUE'); // ON_QUEUE, BREAK, TRAINING, MEETING, OFF
                $table->boolean('published')->default(true);
                $table->uuid('created_by')->nullable();
                $table->timestamps();

                $table->foreign('shift_uuid')
                    ->references('shift_uuid')
                    ->on('contact_center_shifts')
                    ->onDelete('set null');
            });
        }

        // 3. Real-Time Adherence (RTA) Logs
        if (!Schema::hasTable('contact_center_adherence_logs')) {
            Schema::create('contact_center_adherence_logs', function (Blueprint $table) {
                $table->uuid('adherence_log_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('agent_uuid')->index();
                $table->string('expected_state', 64);
                $table->string('actual_state', 64);
                $table->boolean('is_adherent')->default(true);
                $table->decimal('adherence_percentage', 5, 2)->default(100.00);
                $table->timestamp('checked_at')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_adherence_logs');
        Schema::dropIfExists('contact_center_schedules');
        Schema::dropIfExists('contact_center_shifts');
    }
};
