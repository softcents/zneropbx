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
        // 1. Omnichannel Conversations (Threads across SMS, WhatsApp, Web Chat, Email)
        if (!Schema::hasTable('contact_center_conversations')) {
            Schema::create('contact_center_conversations', function (Blueprint $table) {
                $table->uuid('conversation_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->uuid('customer_uuid')->nullable()->index();
                $table->uuid('assigned_agent_uuid')->nullable()->index();
                $table->string('channel', 30)->default('sms'); // sms, whatsapp, web_chat, email
                $table->string('customer_identifier')->index(); // phone, email, session token
                $table->string('customer_name')->nullable();
                $table->string('subject')->nullable();
                $table->string('status', 30)->default('queued'); // queued, assigned, open, waiting_customer, resolved, closed
                $table->string('priority', 30)->default('medium'); // low, medium, high, urgent
                $table->integer('unread_count')->default(0);
                $table->timestamp('last_message_at')->nullable();
                $table->text('last_message_preview')->nullable();
                $table->timestamps();
            });
        }

        // 2. Conversation Messages
        if (!Schema::hasTable('contact_center_conversation_messages')) {
            Schema::create('contact_center_conversation_messages', function (Blueprint $table) {
                $table->uuid('conversation_message_uuid')->primary();
                $table->uuid('conversation_uuid')->index();
                $table->uuid('domain_uuid')->index();
                $table->string('sender_type', 30)->default('customer'); // customer, agent, bot, system
                $table->string('sender_id')->nullable();
                $table->string('sender_name')->nullable();
                $table->text('body');
                $table->json('attachments')->nullable();
                $table->string('status', 30)->default('sent'); // sent, delivered, read, failed
                $table->string('delivery_provider', 50)->nullable();
                $table->string('external_message_id')->nullable()->index();
                $table->timestamps();
            });
        }

        // 3. Canned Responses / Macros for Quick Agent Replies
        if (!Schema::hasTable('contact_center_canned_responses')) {
            Schema::create('contact_center_canned_responses', function (Blueprint $table) {
                $table->uuid('canned_response_uuid')->primary();
                $table->uuid('domain_uuid')->index();
                $table->string('title');
                $table->string('shortcut', 50)->index(); // /greeting, /hours, /ticket
                $table->text('content');
                $table->string('channel', 30)->default('all');
                $table->string('category', 50)->default('General');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_center_canned_responses');
        Schema::dropIfExists('contact_center_conversation_messages');
        Schema::dropIfExists('contact_center_conversations');
    }
};
