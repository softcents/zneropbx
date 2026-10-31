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
        if (!Schema::hasTable('reseller_whitelabel_profiles')) {
            Schema::create('reseller_whitelabel_profiles', function (Blueprint $table) {
                $table->uuid('uuid')->primary();
                $table->uuid('domain_uuid')->nullable()->index();
                $table->uuid('parent_reseller_uuid')->nullable()->index();
                $table->string('reseller_tier', 32)->default('master_reseller')->index(); // master_reseller, sub_reseller, client_tenant
                $table->string('company_name');
                $table->string('custom_domain_hostname')->nullable()->unique();
                $table->string('logo_url')->nullable();
                $table->string('favicon_url')->nullable();
                $table->string('login_heading')->nullable();
                $table->string('login_subheading')->nullable();
                $table->string('login_accent_color', 16)->default('#4f46e5');
                $table->string('login_bg_image_url')->nullable();
                $table->string('support_email')->nullable();
                $table->string('support_phone', 64)->nullable();
                $table->string('copyright_text')->nullable();
                $table->string('terms_url')->nullable();
                $table->string('privacy_url')->nullable();

                // Custom Email / SMTP
                $table->boolean('smtp_enabled')->default(false);
                $table->string('smtp_host')->nullable();
                $table->integer('smtp_port')->nullable();
                $table->string('smtp_username')->nullable();
                $table->text('smtp_password')->nullable();
                $table->string('smtp_encryption', 16)->nullable(); // tls, ssl
                $table->string('smtp_from_address')->nullable();
                $table->string('smtp_from_name')->nullable();

                // Custom SMS
                $table->string('sms_sender_id', 32)->nullable();
                $table->string('sms_footer_text')->nullable();

                // Commercial Allocations & Markups
                $table->integer('allocated_extensions')->default(0);
                $table->integer('allocated_did_numbers')->default(0);
                $table->decimal('credit_balance', 12, 4)->default(0.0000);
                $table->decimal('markup_pct', 5, 2)->default(20.00);

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
        Schema::dropIfExists('reseller_whitelabel_profiles');
    }
};
