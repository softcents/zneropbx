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
        // 1. FreeSWITCH Cluster Nodes Inventory & Telemetry
        if (!Schema::hasTable('carrier_freeswitch_nodes')) {
            Schema::create('carrier_freeswitch_nodes', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('node_name');
                $table->string('node_role', 32)->default('media_edge'); // primary_core, media_edge, transcoding_farm, standby_replica
                $table->string('region', 32)->default('us-central');
                $table->string('private_ip', 64);
                $table->string('public_ip', 64);
                $table->integer('sip_port')->default(5060);
                $table->integer('esl_port')->default(8021);
                $table->string('status', 32)->default('active'); // active, draining, standby, unreachable
                $table->integer('max_channels_capacity')->default(1000);
                $table->integer('active_channels')->default(0);
                $table->integer('cps_capacity')->default(50);
                $table->decimal('current_cps', 8, 2)->default(0.00);
                $table->integer('cpu_usage_pct')->default(15);
                $table->integer('memory_usage_pct')->default(30);
                $table->integer('rtt_ms')->default(10);
                $table->bigInteger('uptime_seconds')->default(864000);
                $table->boolean('esl_connected')->default(true);
                $table->boolean('supports_transcoding')->default(true);
                $table->boolean('supports_webrtc')->default(true);
                $table->boolean('supports_conferencing')->default(true);
                $table->boolean('drain_mode')->default(false);
                $table->timestamp('health_last_probed_at')->nullable();
                $table->timestamps();
            });
        }

        // 2. SIP Edge Proxies & Load Balancers (Kamailio / OpenSIPS Dispatcher)
        if (!Schema::hasTable('carrier_sip_proxies')) {
            Schema::create('carrier_sip_proxies', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('proxy_name');
                $table->string('vip_address', 64);
                $table->string('listen_interface', 64)->default('0.0.0.0:5060');
                $table->string('balancing_algorithm', 32)->default('least_loaded_channels'); // round_robin, least_loaded_channels, hash_callid, priority_failover
                $table->integer('options_ping_interval_sec')->default(5);
                $table->integer('options_ping_timeout_ms')->default(1500);
                $table->integer('failure_threshold_count')->default(3);
                $table->string('status', 32)->default('healthy'); // healthy, degraded, offline
                $table->integer('active_nodes_count')->default(3);
                $table->integer('quarantined_nodes_count')->default(0);
                $table->timestamps();
            });
        }

        // 3. Carrier Security ACL & Firewall Rules
        if (!Schema::hasTable('carrier_security_acls')) {
            Schema::create('carrier_security_acls', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('acl_name');
                $table->string('cidr_network', 64);
                $table->string('rule_action', 32)->default('allow'); // allow, deny, rate_limit
                $table->string('port_range')->default('5060-5061, 10000-20000 RTP');
                $table->boolean('is_active')->default(true);
                $table->bigInteger('hits_count')->default(0);
                $table->timestamps();
            });
        }

        // 4. Failover & Disaster Recovery Drills (RTO/RPO Metrics)
        if (!Schema::hasTable('carrier_failover_drills')) {
            Schema::create('carrier_failover_drills', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('drill_name');
                $table->string('trigger_type', 32)->default('manual_drill'); // manual_drill, automated_watchdog, scheduled_test
                $table->string('source_node');
                $table->string('target_node');
                $table->integer('failover_duration_ms')->default(350);
                $table->integer('channels_reallocated')->default(120);
                $table->decimal('measured_rto_sec', 8, 2)->default(1.20); // target < 180s
                $table->decimal('measured_rpo_sec', 8, 2)->default(0.00); // target < 60s
                $table->string('result_status', 32)->default('SUCCESS'); // SUCCESS, PARTIAL, FAILED
                $table->timestamp('executed_at')->nullable();
                $table->text('logs')->nullable();
                $table->timestamps();
            });
        }

        // 5. Configuration Snapshots & Offsite Disaster Recovery Backups
        if (!Schema::hasTable('carrier_backup_snapshots')) {
            Schema::create('carrier_backup_snapshots', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('snapshot_name');
                $table->string('snapshot_type', 32)->default('freeswitch_xml'); // freeswitch_xml, database_full, dialplan_routes, media_prompts
                $table->bigInteger('file_size_bytes')->default(1450000);
                $table->string('storage_target', 32)->default('s3_encrypted'); // s3_encrypted, wasabi_secure, local_redundant
                $table->string('sha256_checksum', 64);
                $table->boolean('is_verified')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_backup_snapshots');
        Schema::dropIfExists('carrier_failover_drills');
        Schema::dropIfExists('carrier_security_acls');
        Schema::dropIfExists('carrier_sip_proxies');
        Schema::dropIfExists('carrier_freeswitch_nodes');
    }
};
