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
        if (!Schema::hasTable('contact_center_call_transcripts')) {
            Schema::create('contact_center_call_transcripts', function (Blueprint $table) {
                $table->uuid('transcript_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('xml_cdr_uuid')->unique()->index();
                $table->string('caller_id_number')->nullable();
                $table->string('caller_id_name')->nullable();
                $table->uuid('agent_uuid')->nullable()->index();
                $table->string('agent_name')->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->string('language')->default('en-US');
                $table->text('full_transcript')->nullable();
                $table->text('redacted_transcript')->nullable();
                $table->text('summary')->nullable();
                $table->json('key_points')->nullable();
                $table->json('action_items')->nullable();
                $table->json('utterances')->nullable();
                $table->integer('talk_time_agent_sec')->default(0);
                $table->integer('talk_time_customer_sec')->default(0);
                $table->integer('silence_sec')->default(0);
                $table->integer('overtalk_sec')->default(0);
                $table->decimal('talk_ratio_agent', 5, 2)->default(50.00);
                $table->string('overall_sentiment')->default('NEUTRAL');
                $table->decimal('customer_sentiment_score', 4, 2)->default(0.00);
                $table->decimal('agent_empathy_score', 5, 2)->default(0.00);
                $table->decimal('compliance_score', 5, 2)->default(0.00);
                $table->json('compliance_flags')->nullable();
                $table->json('keywords_spotted')->nullable();
                $table->decimal('ai_auto_score', 5, 2)->default(0.00);
                $table->json('ai_scorecard_suggestions')->nullable();
                $table->string('status')->default('COMPLETED');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_call_transcripts');
    }
};
