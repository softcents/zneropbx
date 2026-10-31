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
        // 1. QA Scorecard Templates (Rubrics)
        if (!Schema::hasTable('contact_center_qa_scorecards')) {
            Schema::create('contact_center_qa_scorecards', function (Blueprint $table) {
                $table->uuid('qa_scorecard_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('name', 255);
                $table->text('description')->nullable();
                $table->decimal('passing_score', 5, 2)->default(80.00);
                $table->jsonb('sections')->nullable(); // Sections, questions, criteria, weights
                $table->boolean('is_active')->default(true);
                $table->uuid('created_by')->nullable();
                $table->timestamps();
            });
        }

        // 2. QA Call Evaluations
        if (!Schema::hasTable('contact_center_qa_evaluations')) {
            Schema::create('contact_center_qa_evaluations', function (Blueprint $table) {
                $table->uuid('qa_evaluation_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('scorecard_uuid')->index();
                $table->uuid('xml_cdr_uuid')->index();
                $table->uuid('agent_uuid')->index();
                $table->uuid('supervisor_user_uuid')->nullable()->index();
                $table->decimal('total_score', 6, 2)->default(0.00);
                $table->decimal('max_score', 6, 2)->default(100.00);
                $table->decimal('percentage', 5, 2)->default(0.00);
                $table->string('result', 32)->default('PENDING'); // PASS, FAIL, REQUIRES_COACHING
                $table->string('status', 32)->default('COMPLETED'); // DRAFT, COMPLETED, ACKNOWLEDGED, DISPUTED
                $table->jsonb('answers')->nullable(); // Answers and scores per criterion
                $table->text('feedback')->nullable(); // Supervisor general evaluation notes
                $table->text('coaching_notes')->nullable(); // Actionable advice
                $table->text('agent_comments')->nullable(); // Agent response or dispute reason
                $table->timestamp('acknowledged_at')->nullable();
                $table->timestamps();

                $table->foreign('scorecard_uuid')
                    ->references('qa_scorecard_uuid')
                    ->on('contact_center_qa_scorecards')
                    ->onDelete('cascade');
            });
        }

        // 3. Contact Center Agent Dispositions (Wrap-Up Codes & Customer Interaction History)
        if (!Schema::hasTable('contact_center_agent_dispositions')) {
            Schema::create('contact_center_agent_dispositions', function (Blueprint $table) {
                $table->uuid('disposition_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('agent_uuid')->index();
                $table->uuid('xml_cdr_uuid')->nullable()->index();
                $table->string('caller_number', 64)->nullable()->index();
                $table->string('disposition_code', 64); // RESOLVED, FOLLOW_UP, ESCALATED, CALLBACK_REQUESTED, NOT_INTERESTED, SALE_COMPLETED
                $table->text('customer_notes')->nullable();
                $table->timestamp('follow_up_at')->nullable();
                $table->string('sentiment', 32)->nullable(); // POSITIVE, NEUTRAL, NEGATIVE
                $table->uuid('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_agent_dispositions');
        Schema::dropIfExists('contact_center_qa_evaluations');
        Schema::dropIfExists('contact_center_qa_scorecards');
    }
};
