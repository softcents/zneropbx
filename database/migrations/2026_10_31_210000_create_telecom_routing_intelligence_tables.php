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
        if (!Schema::hasTable('telecom_carriers')) {
            Schema::create('telecom_carriers', function (Blueprint $table) {
                $table->uuid('carrier_uuid')->primary();
                $table->uuid('domain_uuid')->nullable()->index();
                $table->string('name');
                $table->string('carrier_code')->unique();
                $table->string('sip_proxy');
                $table->string('tech_prefix')->nullable();
                $table->integer('max_channels')->default(100);
                $table->integer('current_channels')->default(0);
                $table->integer('max_tps')->default(20);
                $table->string('status')->default('ONLINE'); // ONLINE, DEGRADED, QUARANTINED, MAINTENANCE
                $table->decimal('asr_percentage', 5, 2)->default(85.00);
                $table->integer('acd_seconds')->default(180);
                $table->integer('pdd_ms')->default(450);
                $table->integer('consecutive_failures')->default(0);
                $table->boolean('circuit_breaker_tripped')->default(false);
                $table->timestamp('quarantined_until')->nullable();
                $table->integer('priority')->default(10);
                $table->integer('weight')->default(50);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('telecom_destinations')) {
            Schema::create('telecom_destinations', function (Blueprint $table) {
                $table->uuid('destination_uuid')->primary();
                $table->uuid('carrier_uuid')->index();
                $table->string('destination_name');
                $table->string('prefix')->index();
                $table->decimal('cost_per_minute', 8, 4);
                $table->decimal('connect_fee', 8, 4)->default(0.0000);
                $table->integer('rate_increment')->default(6);
                $table->timestamp('effective_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('telecom_routing_policies')) {
            Schema::create('telecom_routing_policies', function (Blueprint $table) {
                $table->uuid('policy_uuid')->primary();
                $table->uuid('domain_uuid')->nullable()->index();
                $table->string('name');
                $table->string('strategy')->default('LCR'); // LCR, QUALITY, WEIGHTED, TIER_BASED
                $table->decimal('min_asr', 5, 2)->default(50.00);
                $table->integer('max_pdd_ms')->default(1200);
                $table->integer('failover_limit')->default(3);
                $table->boolean('is_default')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('telecom_route_simulation_logs')) {
            Schema::create('telecom_route_simulation_logs', function (Blueprint $table) {
                $table->uuid('log_uuid')->primary();
                $table->uuid('domain_uuid')->nullable()->index();
                $table->string('destination_number');
                $table->string('normalized_prefix');
                $table->string('policy_applied');
                $table->json('routes_evaluated');
                $table->uuid('primary_carrier_uuid');
                $table->json('failover_carrier_uuids')->nullable();
                $table->text('freeswitch_dialstring');
                $table->json('decision_trace');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telecom_route_simulation_logs');
        Schema::dropIfExists('telecom_routing_policies');
        Schema::dropIfExists('telecom_destinations');
        Schema::dropIfExists('telecom_carriers');
    }
};
