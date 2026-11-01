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
        // 1. Global DID Number Warehouse Inventory
        if (!Schema::hasTable('superadmin_did_warehouse')) {
            Schema::create('superadmin_did_warehouse', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('phone_number', 32)->unique();
                $table->string('country_code', 8)->default('US');
                $table->string('state_province', 32)->nullable();
                $table->string('rate_center', 64)->nullable();
                $table->string('carrier_provider', 64)->default('Bandwidth Wholesale');
                $table->decimal('wholesale_monthly_cost', 8, 2)->default(0.50);
                $table->decimal('retail_monthly_price', 8, 2)->default(2.50);
                $table->string('status', 32)->default('available')->index(); // available, assigned, reserved, porting
                $table->uuid('assigned_domain_uuid')->nullable()->index();
                $table->timestamps();
            });
        }

        // 2. SaaS Package Tier & Feature Entitlements per Domain
        if (!Schema::hasTable('superadmin_feature_entitlements')) {
            Schema::create('superadmin_feature_entitlements', function (Blueprint $table) {
                $table->uuid('domain_uuid')->primary();
                $table->string('package_tier', 32)->default('enterprise'); // starter, business, enterprise, carrier
                $table->boolean('feature_ai_agents')->default(true);
                $table->boolean('feature_contact_center')->default(true);
                $table->boolean('feature_stir_shaken')->default(true);
                $table->boolean('feature_omnichannel')->default(true);
                $table->boolean('feature_developer_api')->default(true);
                $table->boolean('feature_video_conference')->default(true);
                $table->boolean('feature_call_recording_ai')->default(true);
                $table->timestamps();
            });
        }

        // 3. Platform Cross-Tenant Audit Logs
        if (!Schema::hasTable('superadmin_audit_logs')) {
            Schema::create('superadmin_audit_logs', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->uuid('actor_user_uuid')->nullable();
                $table->string('actor_name', 120)->nullable();
                $table->string('action', 64)->index();
                $table->string('target_type', 64)->nullable();
                $table->string('target_name', 190)->nullable();
                $table->text('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->timestamp('created_at')->useCurrent();
            });
        }

        // 4. Global System Broadcast Notices
        if (!Schema::hasTable('superadmin_system_notices')) {
            Schema::create('superadmin_system_notices', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('title', 190);
                $table->text('message');
                $table->string('severity', 16)->default('info'); // info, warning, emergency
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('superadmin_system_notices');
        Schema::dropIfExists('superadmin_audit_logs');
        Schema::dropIfExists('superadmin_feature_entitlements');
        Schema::dropIfExists('superadmin_did_warehouse');
    }
};
