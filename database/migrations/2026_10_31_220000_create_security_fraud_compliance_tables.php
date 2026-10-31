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
        // 1. Telecom Fraud Prevention & Velocity Rules
        if (!Schema::hasTable('security_fraud_rules')) {
            Schema::create('security_fraud_rules', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('rule_name');
                $table->string('rule_type', 64); // velocity_destination, sip_bruteforce, spend_cap, off_hours_spike, ip_reputation
                $table->integer('threshold_value')->default(50);
                $table->integer('time_window_sec')->default(60);
                $table->string('action', 64)->default('quarantine_ip'); // quarantine_ip, drop_call, alert_noc, force_reauth
                $table->boolean('is_enabled')->default(true);
                $table->string('severity', 32)->default('high'); // critical, high, medium, low
                $table->boolean('auto_mitigate')->default(true);
                $table->timestamps();
            });
        }

        // 2. IP Quarantine & Network Firewall Posture
        if (!Schema::hasTable('security_quarantined_ips')) {
            Schema::create('security_quarantined_ips', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('ip_address', 45);
                $table->text('reason');
                $table->uuid('trigger_rule_uuid')->nullable()->index();
                $table->decimal('threat_score', 5, 2)->default(90.00);
                $table->timestamp('quarantined_at');
                $table->timestamp('expires_at')->nullable();
                $table->string('status', 32)->default('active'); // active, whitelisted, expired
                $table->string('mitigation_type', 64)->default('fail2ban_nftables'); // fail2ban_nftables, sip_challenge_drop, api_block
                $table->timestamps();
            });
        }

        // 3. Security Incident Events & Audit Trail
        if (!Schema::hasTable('security_incident_events')) {
            Schema::create('security_incident_events', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('incident_code', 32);
                $table->string('title');
                $table->string('severity', 32)->default('high'); // critical, high, medium, low
                $table->string('source_ip', 45)->nullable();
                $table->string('affected_extension_or_trunk', 128)->nullable();
                $table->decimal('risk_score', 5, 2)->default(85.00);
                $table->string('detected_vector', 128);
                $table->string('status', 32)->default('open'); // open, investigating, mitigated, resolved
                $table->text('mitigation_action_taken')->nullable();
                $table->text('audit_notes')->nullable();
                $table->timestamp('detected_at');
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Regulatory Compliance & Data Governance Policies
        if (!Schema::hasTable('security_compliance_policies')) {
            Schema::create('security_compliance_policies', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('jurisdiction', 64)->default('US_FCC_RAY_BAUM');
                $table->integer('recording_retention_days')->default(90);
                $table->integer('cdr_retention_days')->default(365);
                $table->boolean('enforce_pci_dtmf_masking')->default(true);
                $table->boolean('gdpr_right_to_be_forgotten_enabled')->default(true);
                $table->boolean('immutable_audit_logging')->default(true);
                $table->boolean('require_mfa_admin_actions')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_compliance_policies');
        Schema::dropIfExists('security_incident_events');
        Schema::dropIfExists('security_quarantined_ips');
        Schema::dropIfExists('security_fraud_rules');
    }
};
