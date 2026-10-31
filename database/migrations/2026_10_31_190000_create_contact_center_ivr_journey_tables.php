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
        // 1. Inbound IVR Visual Journey Flows
        if (!Schema::hasTable('contact_center_ivr_flows')) {
            Schema::create('contact_center_ivr_flows', function (Blueprint $table) {
                $table->uuid('ivr_flow_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('name');
                $table->string('extension', 30)->index();
                $table->text('description')->nullable();
                $table->string('status', 30)->default('active'); // active, draft, archived
                $table->integer('version')->default(1);
                $table->string('language_default', 20)->default('en-US');
                $table->json('languages_supported')->nullable();
                $table->boolean('vip_boost_enabled')->default(true);
                $table->uuid('business_hours_schedule_uuid')->nullable();
                $table->json('nodes_definition')->nullable();
                $table->float('containment_rate_target')->default(35.0);
                $table->integer('total_calls_handled')->default(0);
                $table->timestamps();
            });
        }

        // 2. Caller Journey Path Traces (Analytics)
        if (!Schema::hasTable('contact_center_ivr_journeys')) {
            Schema::create('contact_center_ivr_journeys', function (Blueprint $table) {
                $table->uuid('journey_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('ivr_flow_uuid')->index();
                $table->string('caller_id_number')->index();
                $table->string('caller_id_name')->nullable();
                $table->uuid('xml_cdr_uuid')->nullable()->index();
                $table->json('path_taken')->nullable();
                $table->string('drop_off_node')->nullable();
                $table->boolean('contained')->default(false);
                $table->integer('duration_seconds')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_ivr_journeys');
        Schema::dropIfExists('contact_center_ivr_flows');
    }
};
