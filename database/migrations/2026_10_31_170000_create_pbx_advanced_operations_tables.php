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
        // 1. Emergency Locations & E911 ELIN Mappings
        if (!Schema::hasTable('pbx_emergency_locations')) {
            Schema::create('pbx_emergency_locations', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('location_name');
                $table->string('elin_number', 32);
                $table->string('street_address');
                $table->string('unit_suite')->nullable();
                $table->string('city');
                $table->string('state_province', 64);
                $table->string('postal_code', 32);
                $table->string('country', 8)->default('US');
                $table->string('psap_name')->nullable();
                $table->json('notification_emails')->nullable();
                $table->json('notification_phones')->nullable();
                $table->boolean('karis_law_compliant')->default(true);
                $table->boolean('ray_baums_compliant')->default(true);
                $table->string('status', 32)->default('active'); // active, testing, pending_verification
                $table->timestamp('test_last_completed_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. Caller-ID & Number Normalization Rules
        if (!Schema::hasTable('pbx_caller_id_rules')) {
            Schema::create('pbx_caller_id_rules', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('rule_name');
                $table->string('scope_type', 32)->default('global'); // global, department, extension, npa_nxx_match
                $table->string('scope_value')->nullable(); // e.g. Support, 1001, 512*
                $table->string('caller_id_name');
                $table->string('caller_id_number', 32);
                $table->string('emergency_caller_id_number', 32)->nullable();
                $table->string('normalization_format', 32)->default('e164'); // e164, national_10, local_7, custom_regex
                $table->integer('strip_digits')->default(0);
                $table->string('prepend_digits')->nullable();
                $table->integer('priority')->default(10);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 3. Advanced Call Permission Profiles & Barring
        if (!Schema::hasTable('pbx_call_permission_profiles')) {
            Schema::create('pbx_call_permission_profiles', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('profile_name');
                $table->text('description')->nullable();
                $table->boolean('allow_international')->default(false);
                $table->boolean('allow_national')->default(true);
                $table->boolean('allow_toll_free')->default(true);
                $table->boolean('allow_premium_rate')->default(false);
                $table->boolean('internal_only')->default(false);
                $table->integer('max_concurrent_calls')->default(2);
                $table->string('time_restriction_type', 32)->default('none'); // none, business_hours_only, custom_schedule
                $table->json('active_days')->nullable();
                $table->string('start_time', 16)->nullable();
                $table->string('end_time', 16)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 4. Recording Retention Policies
        if (!Schema::hasTable('pbx_recording_retention_rules')) {
            Schema::create('pbx_recording_retention_rules', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('rule_name');
                $table->string('category', 64)->default('general'); // financial, healthcare, support, pci_dss
                $table->integer('retention_days')->default(90); // 0 = indefinite
                $table->boolean('auto_purge_enabled')->default(false);
                $table->string('storage_tier', 32)->default('local_encrypted'); // local_encrypted, s3_glacier, wasabi_secure
                $table->string('encryption_algorithm', 32)->default('AES-256-GCM');
                $table->string('kms_key_id')->default('arn:aws:kms:us-east-1:corp:key/pbx-rec-master');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 5. Recording Legal Holds & Immutability Ledger
        if (!Schema::hasTable('pbx_recording_legal_holds')) {
            Schema::create('pbx_recording_legal_holds', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('case_number');
                $table->string('case_title');
                $table->string('recording_identifier');
                $table->string('caller_destination')->nullable();
                $table->timestamp('call_date')->nullable();
                $table->string('custodian_name');
                $table->string('legal_officer');
                $table->text('reason_notes');
                $table->string('status', 32)->default('active'); // active, released
                $table->string('tamper_checksum_sha256', 64);
                $table->timestamps();
            });
        }

        // 6. Device Fleet & WebRTC Endpoint Inventory
        if (!Schema::hasTable('pbx_device_fleet_inventory')) {
            Schema::create('pbx_device_fleet_inventory', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('mac_address');
                $table->string('vendor', 64);
                $table->string('model', 64);
                $table->string('firmware_version', 64);
                $table->string('assigned_extension', 32)->nullable();
                $table->string('ip_address', 64);
                $table->string('transport', 16)->default('TLS'); // TLS, WSS, UDP, TCP
                $table->string('sip_status', 32)->default('registered'); // registered, unregistered, unreachable
                $table->integer('rtt_ms')->default(20);
                $table->timestamp('last_provisioned_at')->nullable();
                $table->string('config_version', 32)->default('v1.0.0');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pbx_device_fleet_inventory');
        Schema::dropIfExists('pbx_recording_legal_holds');
        Schema::dropIfExists('pbx_recording_retention_rules');
        Schema::dropIfExists('pbx_call_permission_profiles');
        Schema::dropIfExists('pbx_caller_id_rules');
        Schema::dropIfExists('pbx_emergency_locations');
    }
};
