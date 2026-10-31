<?php

namespace App\Services\ContactCenter;

use App\Models\CallCenterAgents;
use App\Models\ContactCenterCannedResponse;
use App\Models\ContactCenterConversation;
use App\Models\ContactCenterConversationMessage;
use App\Models\ContactCenterCustomer;
use App\Models\ContactCenterCustomerInteraction;
use App\Models\Messages;
use Carbon\Carbon;
use Illuminate\Support\Str;

class OmnichannelMessagingService
{
    /**
     * Get filtered conversations list.
     */
    public function getConversations(
        string $domainUuid,
        ?string $channel = null,
        ?string $status = null,
        ?string $agentUuid = null
    ) {
        $this->ensureSeedData($domainUuid);

        $query = ContactCenterConversation::where('domain_uuid', $domainUuid)
            ->with(['customer', 'assignedAgent'])
            ->orderBy('last_message_at', 'desc');

        if ($channel && $channel !== 'all') {
            $query->where('channel', $channel);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($agentUuid) {
            $query->where('assigned_agent_uuid', $agentUuid);
        }

        return $query->get();
    }

    /**
     * Get full conversation thread messages.
     */
    public function getConversationThread(string $domainUuid, string $conversationUuid): ?array
    {
        $conversation = ContactCenterConversation::where('domain_uuid', $domainUuid)
            ->where('conversation_uuid', $conversationUuid)
            ->with(['customer', 'assignedAgent', 'messages'])
            ->first();

        if (! $conversation) {
            return null;
        }

        // Mark as read
        if ($conversation->unread_count > 0) {
            $conversation->unread_count = 0;
            $conversation->save();
        }

        return [
            'conversation' => $conversation,
            'messages' => $conversation->messages,
            'customer' => $conversation->customer,
        ];
    }

    /**
     * Send agent message in conversation thread.
     */
    public function sendAgentMessage(
        string $domainUuid,
        string $conversationUuid,
        ?string $agentUuid,
        string $body,
        ?array $attachments = null
    ): ContactCenterConversationMessage {
        $conversation = ContactCenterConversation::where('domain_uuid', $domainUuid)
            ->where('conversation_uuid', $conversationUuid)
            ->firstOrFail();

        $agent = $agentUuid ? CallCenterAgents::where('call_center_agent_uuid', $agentUuid)->first() : null;

        $msg = ContactCenterConversationMessage::create([
            'conversation_message_uuid' => (string) Str::uuid(),
            'conversation_uuid' => $conversation->conversation_uuid,
            'domain_uuid' => $domainUuid,
            'sender_type' => 'agent',
            'sender_id' => $agentUuid,
            'sender_name' => $agent?->agent_name ?? 'Support Agent',
            'body' => $body,
            'attachments' => $attachments,
            'status' => 'delivered',
            'delivery_provider' => $conversation->channel === 'whatsapp' ? 'meta_whatsapp' : 'telnyx',
        ]);

        // Update conversation metadata
        $conversation->status = 'waiting_customer';
        $conversation->last_message_at = Carbon::now();
        $conversation->last_message_preview = $body;
        $conversation->save();

        // Also record to Customer 360 interaction timeline
        if ($conversation->customer_uuid) {
            ContactCenterCustomerInteraction::create([
                'interaction_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
                'customer_uuid' => $conversation->customer_uuid,
                'agent_uuid' => $agentUuid,
                'channel' => $conversation->channel,
                'direction' => 'outbound',
                'summary' => "Agent reply via {$conversation->channel}: " . Str::limit($body, 60),
                'details' => $body,
                'duration_seconds' => 0,
            ]);
        }

        return $msg;
    }

    /**
     * Simulate an incoming customer message across SMS, WhatsApp, Web Chat.
     */
    public function simulateInboundMessage(
        string $domainUuid,
        string $channel,
        string $fromIdentifier,
        string $customerName,
        string $body
    ): ContactCenterConversation {
        // Find or associate customer
        $customer = ContactCenterCustomer::where('domain_uuid', $domainUuid)
            ->where(function ($q) use ($fromIdentifier) {
                $q->where('phone', 'like', "%{$fromIdentifier}%")
                    ->orWhere('email', 'like', "%{$fromIdentifier}%");
            })
            ->first();

        // Find active conversation or create new
        $conversation = ContactCenterConversation::where('domain_uuid', $domainUuid)
            ->where('channel', $channel)
            ->where('customer_identifier', $fromIdentifier)
            ->whereNotIn('status', ['closed'])
            ->first();

        if (! $conversation) {
            $conversation = ContactCenterConversation::create([
                'conversation_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
                'customer_uuid' => $customer?->customer_uuid,
                'channel' => $channel,
                'customer_identifier' => $fromIdentifier,
                'customer_name' => $customer?->full_name ?? $customerName,
                'subject' => "Inbound {$channel} message",
                'status' => 'queued',
                'priority' => $customer?->tier === 'vip' ? 'urgent' : 'medium',
                'unread_count' => 1,
                'last_message_at' => Carbon::now(),
                'last_message_preview' => $body,
            ]);
        } else {
            $conversation->status = 'open';
            $conversation->increment('unread_count');
            $conversation->last_message_at = Carbon::now();
            $conversation->last_message_preview = $body;
            $conversation->save();
        }

        // Add message
        ContactCenterConversationMessage::create([
            'conversation_message_uuid' => (string) Str::uuid(),
            'conversation_uuid' => $conversation->conversation_uuid,
            'domain_uuid' => $domainUuid,
            'sender_type' => 'customer',
            'sender_id' => $fromIdentifier,
            'sender_name' => $conversation->customer_name,
            'body' => $body,
            'status' => 'delivered',
            'delivery_provider' => $channel === 'whatsapp' ? 'meta_whatsapp' : 'telnyx',
        ]);

        return $conversation;
    }

    /**
     * Assign thread to agent.
     */
    public function assignConversation(string $domainUuid, string $conversationUuid, ?string $agentUuid): ContactCenterConversation
    {
        $conversation = ContactCenterConversation::where('domain_uuid', $domainUuid)
            ->where('conversation_uuid', $conversationUuid)
            ->firstOrFail();

        $conversation->assigned_agent_uuid = $agentUuid;
        $conversation->status = 'assigned';
        $conversation->save();

        return $conversation;
    }

    /**
     * Update thread status.
     */
    public function updateStatus(string $domainUuid, string $conversationUuid, string $status): ContactCenterConversation
    {
        $conversation = ContactCenterConversation::where('domain_uuid', $domainUuid)
            ->where('conversation_uuid', $conversationUuid)
            ->firstOrFail();

        $conversation->status = $status;
        $conversation->save();

        return $conversation;
    }

    /**
     * Get list of canned responses.
     */
    public function getCannedResponses(string $domainUuid, ?string $channel = null)
    {
        $this->ensureSeedData($domainUuid);

        $query = ContactCenterCannedResponse::where('domain_uuid', $domainUuid)
            ->orderBy('category')
            ->orderBy('title');

        if ($channel && $channel !== 'all') {
            $query->where(function ($q) use ($channel) {
                $q->where('channel', $channel)->orWhere('channel', 'all');
            });
        }

        return $query->get();
    }

    /**
     * Ensure sample omnichannel threads and canned responses for fresh installations.
     */
    protected function ensureSeedData(string $domainUuid): void
    {
        if (ContactCenterCannedResponse::where('domain_uuid', $domainUuid)->doesntExist()) {
            $templates = [
                ['title' => 'Greeting', 'shortcut' => '/greeting', 'content' => 'Hello! Thank you for contacting Znero Contact Center support. How can I assist you today?', 'channel' => 'all', 'category' => 'General'],
                ['title' => 'Business Hours', 'shortcut' => '/hours', 'content' => 'Our support team is available 24/7 for VIP clients, and 8:00 AM - 8:00 PM EST for general inquiries.', 'channel' => 'all', 'category' => 'Information'],
                ['title' => 'Ticket Created', 'shortcut' => '/ticket', 'content' => 'I have opened support ticket for your inquiry. A specialist will update you shortly.', 'channel' => 'all', 'category' => 'Support'],
                ['title' => 'Callback Offer', 'shortcut' => '/callback', 'content' => 'Would you prefer a live callback from our Tier 2 voice queue instead? Please reply YES to confirm.', 'channel' => 'sms', 'category' => 'Telephony'],
                ['title' => 'Closing Resolution', 'shortcut' => '/resolve', 'content' => 'Your request has been resolved. Please let us know if there is anything else we can do. Have a great day!', 'channel' => 'all', 'category' => 'Closing'],
            ];

            foreach ($templates as $t) {
                ContactCenterCannedResponse::create([
                    'canned_response_uuid' => (string) Str::uuid(),
                    'domain_uuid' => $domainUuid,
                    'title' => $t['title'],
                    'shortcut' => $t['shortcut'],
                    'content' => $t['content'],
                    'channel' => $t['channel'],
                    'category' => $t['category'],
                ]);
            }
        }

        if (ContactCenterConversation::where('domain_uuid', $domainUuid)->exists()) {
            return;
        }

        // Demo threads
        $demoThreads = [
            [
                'channel' => 'whatsapp',
                'customer_name' => 'Alexander Wright',
                'identifier' => '+15552345678',
                'preview' => 'Can you confirm if our SIP trunk backup route is active?',
                'status' => 'open',
                'priority' => 'urgent',
                'messages' => [
                    ['sender' => 'customer', 'name' => 'Alexander Wright', 'text' => 'Hi team, checking on our primary SIP trunk redundancy status for Apex Logistics.'],
                    ['sender' => 'agent', 'name' => 'Sarah Connor', 'text' => 'Hello Mr. Wright! Your secondary trunk node is healthy and active in standby mode.'],
                    ['sender' => 'customer', 'name' => 'Alexander Wright', 'text' => 'Can you confirm if our SIP trunk backup route is active?'],
                ],
            ],
            [
                'channel' => 'sms',
                'customer_name' => 'Sophia Martinez',
                'identifier' => '+15558765432',
                'preview' => 'Received the invoice, thank you for clarifying line items.',
                'status' => 'waiting_customer',
                'priority' => 'medium',
                'messages' => [
                    ['sender' => 'customer', 'name' => 'Sophia Martinez', 'text' => 'Hello, I had a question regarding the international toll charge on account ACC-448219.'],
                    ['sender' => 'agent', 'name' => 'Michael Chang', 'text' => 'Hi Sophia, I reviewed the invoice and applied the volume discount credit to your balance.'],
                    ['sender' => 'customer', 'name' => 'Sophia Martinez', 'text' => 'Received the invoice, thank you for clarifying line items.'],
                ],
            ],
            [
                'channel' => 'web_chat',
                'customer_name' => 'David Miller',
                'identifier' => 'chat_sess_88219',
                'preview' => 'Need help setting up WebRTC softphone on mobile.',
                'status' => 'queued',
                'priority' => 'high',
                'messages' => [
                    ['sender' => 'customer', 'name' => 'David Miller', 'text' => 'Need help setting up WebRTC softphone on mobile.'],
                ],
            ],
        ];

        foreach ($demoThreads as $d) {
            $customer = ContactCenterCustomer::where('domain_uuid', $domainUuid)
                ->where('first_name', explode(' ', $d['customer_name'])[0])
                ->first();

            $conv = ContactCenterConversation::create([
                'conversation_uuid' => (string) Str::uuid(),
                'domain_uuid' => $domainUuid,
                'customer_uuid' => $customer?->customer_uuid,
                'channel' => $d['channel'],
                'customer_identifier' => $d['identifier'],
                'customer_name' => $d['customer_name'],
                'subject' => "Customer inquiry via {$d['channel']}",
                'status' => $d['status'],
                'priority' => $d['priority'],
                'unread_count' => $d['status'] === 'queued' ? 1 : 0,
                'last_message_at' => Carbon::now()->subMinutes(rand(5, 45)),
                'last_message_preview' => $d['preview'],
            ]);

            foreach ($d['messages'] as $m) {
                ContactCenterConversationMessage::create([
                    'conversation_message_uuid' => (string) Str::uuid(),
                    'conversation_uuid' => $conv->conversation_uuid,
                    'domain_uuid' => $domainUuid,
                    'sender_type' => $m['sender'],
                    'sender_name' => $m['name'],
                    'body' => $m['text'],
                    'status' => 'delivered',
                    'delivery_provider' => $d['channel'] === 'whatsapp' ? 'meta_whatsapp' : 'telnyx',
                    'created_at' => Carbon::now()->subMinutes(rand(10, 60)),
                ]);
            }
        }
    }
}
