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
        // 1. Skills Registry
        if (!Schema::hasTable('contact_center_skills')) {
            Schema::create('contact_center_skills', function (Blueprint $table) {
                $table->uuid('skill_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('name');
                $table->string('code')->index();
                $table->string('category', 50)->default('General'); // Language, Technical, Department, Tier
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 2. Agent Skills Matrix (proficiency 1 to 10)
        if (!Schema::hasTable('contact_center_agent_skills')) {
            Schema::create('contact_center_agent_skills', function (Blueprint $table) {
                $table->uuid('agent_skill_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('agent_uuid')->index();
                $table->uuid('skill_uuid')->index();
                $table->integer('proficiency_level')->default(5); // 1-10
                $table->timestamps();

                $table->unique(['agent_uuid', 'skill_uuid']);
            });
        }

        // 3. Queue Skill Requirements
        if (!Schema::hasTable('contact_center_queue_skills')) {
            Schema::create('contact_center_queue_skills', function (Blueprint $table) {
                $table->uuid('queue_skill_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('queue_uuid')->index();
                $table->uuid('skill_uuid')->index();
                $table->integer('min_proficiency')->default(1); // 1-10
                $table->boolean('is_mandatory')->default(true);
                $table->timestamps();

                $table->unique(['queue_uuid', 'skill_uuid']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_queue_skills');
        Schema::dropIfExists('contact_center_agent_skills');
        Schema::dropIfExists('contact_center_skills');
    }
};
