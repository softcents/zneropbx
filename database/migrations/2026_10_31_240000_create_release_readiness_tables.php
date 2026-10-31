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
        // 1. Production Deployments & Build Provenance
        if (!Schema::hasTable('system_production_deployments')) {
            Schema::create('system_production_deployments', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('release_version', 64);
                $table->string('git_commit_hash', 64);
                $table->string('deployed_by');
                $table->string('status', 32)->default('verified'); // deployed, verified, rolled_back
                $table->decimal('health_audit_score_pct', 5, 2)->default(100.00);
                $table->string('artifact_provenance_sha256', 64);
                $table->string('rollback_snapshot_id', 64)->nullable();
                $table->timestamp('deployed_at');
                $table->timestamps();
            });
        }

        // 2. Automated Configuration & DB Recovery Snapshots
        if (!Schema::hasTable('system_backup_snapshots')) {
            Schema::create('system_backup_snapshots', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->uuid('domain_uuid')->index();
                $table->string('snapshot_name');
                $table->string('snapshot_type', 64)->default('full_config_db'); // full_config_db, dialplan_xml, carrier_lcr_ledger
                $table->string('file_path');
                $table->bigInteger('file_size_bytes')->default(0);
                $table->string('sha256_checksum', 64);
                $table->string('status', 32)->default('completed'); // completed, verifying, failed
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_backup_snapshots');
        Schema::dropIfExists('system_production_deployments');
    }
};
