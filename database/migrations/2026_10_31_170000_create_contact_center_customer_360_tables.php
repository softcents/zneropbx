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
        // 1. Customer Profiles & Accounts
        if (!Schema::hasTable('contact_center_customers')) {
            Schema::create('contact_center_customers', function (Blueprint $table) {
                $table->uuid('customer_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('first_name');
                $table->string('last_name')->nullable();
                $table->string('company')->nullable();
                $table->string('phone')->index();
                $table->string('email')->nullable()->index();
                $table->string('tier', 30)->default('standard'); // standard, silver, gold, vip
                $table->string('account_number', 50)->nullable()->index();
                $table->string('status', 30)->default('active'); // active, inactive, blocked
                $table->string('sentiment', 30)->default('neutral'); // positive, neutral, negative
                $table->text('notes')->nullable();
                $table->integer('total_calls_count')->default(0);
                $table->decimal('total_spend', 12, 2)->default(0.00);
                $table->timestamp('last_interaction_at')->nullable();
                $table->json('custom_attributes')->nullable();
                $table->timestamps();
            });
        }

        // 2. Cases & Support Tickets
        if (!Schema::hasTable('contact_center_tickets')) {
            Schema::create('contact_center_tickets', function (Blueprint $table) {
                $table->uuid('ticket_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('customer_uuid')->nullable()->index();
                $table->uuid('assigned_agent_uuid')->nullable()->index();
                $table->string('ticket_number', 50)->index();
                $table->string('subject');
                $table->text('description')->nullable();
                $table->string('category', 60)->default('Technical Support'); // Billing, Technical, Account, Complaint, Inquiry
                $table->string('priority', 30)->default('medium'); // low, medium, high, urgent
                $table->string('status', 30)->default('open'); // open, in_progress, pending_customer, resolved, closed
                $table->string('source', 30)->default('phone'); // phone, chat, email, sms, portal
                $table->timestamp('sla_due_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->timestamps();
            });
        }

        // 3. Customer Interaction History (Timeline)
        if (!Schema::hasTable('contact_center_customer_interactions')) {
            Schema::create('contact_center_customer_interactions', function (Blueprint $table) {
                $table->uuid('interaction_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('customer_uuid')->index();
                $table->uuid('agent_uuid')->nullable()->index();
                $table->uuid('ticket_uuid')->nullable()->index();
                $table->string('channel', 30)->default('voice'); // voice, sms, whatsapp, email, chat, note
                $table->string('direction', 30)->default('inbound'); // inbound, outbound, internal
                $table->string('summary');
                $table->text('details')->nullable();
                $table->string('sentiment', 30)->nullable();
                $table->integer('duration_seconds')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_customer_interactions');
        Schema::dropIfExists('contact_center_tickets');
        Schema::dropIfExists('contact_center_customers');
    }
};
