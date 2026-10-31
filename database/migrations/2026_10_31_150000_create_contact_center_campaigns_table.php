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
        if (!Schema::hasTable('contact_center_campaigns')) {
            Schema::create('contact_center_campaigns', function (Blueprint $table) {
                $table->uuid('campaign_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('name');
                $table->string('dialer_mode', 30)->default('progressive'); // preview, progressive, predictive
                $table->string('status', 30)->default('draft')->index(); // draft, running, paused, completed
                $table->uuid('queue_uuid')->nullable()->index();
                $table->uuid('contact_list_uuid')->nullable()->index();
                $table->string('caller_id_name')->nullable();
                $table->string('caller_id_number')->nullable();
                $table->decimal('pacing_ratio', 4, 2)->default(1.20); // lines per available agent in predictive
                $table->decimal('max_abandon_rate', 5, 2)->default(3.00); // compliance threshold (e.g. 3%)
                $table->integer('ring_timeout_seconds')->default(25);
                $table->integer('total_leads')->default(0);
                $table->integer('dialed_leads')->default(0);
                $table->integer('connected_leads')->default(0);
                $table->integer('abandoned_leads')->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('contact_center_campaign_leads')) {
            Schema::create('contact_center_campaign_leads', function (Blueprint $table) {
                $table->uuid('lead_uuid')->primary();
                $table->uuid('campaign_uuid')->index();
                $table->uuid('domain_uuid')->index();
                $table->string('phone_number');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('company')->nullable();
                $table->string('status', 30)->default('pending')->index(); // pending, dialing, connected, abandoned, failed, dnc
                $table->integer('attempts')->default(0);
                $table->timestamp('last_dialed_at')->nullable();
                $table->string('call_uuid')->nullable()->index();
                $table->string('disposition')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_campaign_leads');
        Schema::dropIfExists('contact_center_campaigns');
    }
};
