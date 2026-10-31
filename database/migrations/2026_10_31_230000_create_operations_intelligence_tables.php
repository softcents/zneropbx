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
        // 1. Saved Views & Custom Analytical Dashboards
        if (!Schema::hasTable('analytics_bi_saved_views')) {
            Schema::create('analytics_bi_saved_views', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('view_name');
                $table->string('category', 64)->default('financial'); // financial, carrier_qos, workforce, messaging_ai, infrastructure
                $table->json('filter_criteria_json');
                $table->boolean('is_pinned')->default(false);
                $table->uuid('created_by_user_uuid')->nullable();
                $table->timestamps();
            });
        }

        // 2. Scheduled BI Report Exports & Delivery Pipelines
        if (!Schema::hasTable('analytics_bi_scheduled_exports')) {
            Schema::create('analytics_bi_scheduled_exports', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('report_title');
                $table->string('format', 32)->default('csv'); // csv, json, xlsx, pdf
                $table->string('frequency', 32)->default('weekly'); // daily, weekly, monthly, ad_hoc
                $table->string('recipient_email');
                $table->timestamp('last_exported_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->string('status', 32)->default('active'); // active, paused, completed
                $table->integer('total_runs')->default(0);
                $table->timestamps();
            });
        }

        // 3. Multi-Carrier QoS & Wholesale Margin Historical Benchmarks
        if (!Schema::hasTable('analytics_bi_carrier_qos_benchmarks')) {
            Schema::create('analytics_bi_carrier_qos_benchmarks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('carrier_name');
                $table->decimal('asr_percent', 5, 2)->default(98.50);
                $table->integer('acd_seconds')->default(240);
                $table->integer('pdd_ms')->default(1200);
                $table->decimal('jitter_ms', 4, 2)->default(2.40);
                $table->decimal('mos_score', 3, 2)->default(4.41);
                $table->decimal('cost_per_minute_usd', 8, 5)->default(0.00750);
                $table->decimal('margin_percent', 5, 2)->default(40.00);
                $table->bigInteger('total_minutes_billed')->default(120000);
                $table->decimal('total_revenue_usd', 12, 2)->default(18500.00);
                $table->date('sample_date');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_bi_carrier_qos_benchmarks');
        Schema::dropIfExists('analytics_bi_scheduled_exports');
        Schema::dropIfExists('analytics_bi_saved_views');
    }
};
