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
        if (!Schema::hasTable('stir_shaken_certificates')) {
            Schema::create('stir_shaken_certificates', function (Blueprint $table) {
                $table->uuid('certificate_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('cert_name');
                $table->string('x5u_url');
                $table->text('private_key'); // EC P-256 private key PEM
                $table->text('public_cert'); // X.509 certificate PEM
                $table->string('spc_code')->default('ZN01'); // Service Provider Code
                $table->jsonb('tn_ranges')->nullable(); // Verified authorized telephone numbers
                $table->boolean('is_active')->default(true);
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stir_shaken_signing_logs')) {
            Schema::create('stir_shaken_signing_logs', function (Blueprint $table) {
                $table->uuid('log_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('orig_tn', 32)->index(); // Caller ID
                $table->string('dest_tn', 32)->index(); // Destination Number
                $table->string('attestation', 2)->default('A'); // 'A', 'B', 'C'
                $table->uuid('origid_uuid')->index();
                $table->text('passport_jwt'); // Encoded PASSporT JWT
                $table->text('identity_header'); // Full SIP Identity header string
                $table->string('verification_status', 32)->default('signed'); // 'signed', 'verified', 'invalid'
                $table->timestamp('signed_at')->useCurrent();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stir_shaken_signing_logs');
        Schema::dropIfExists('stir_shaken_certificates');
    }
};
