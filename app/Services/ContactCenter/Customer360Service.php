<?php

namespace App\Services\ContactCenter;

use App\Models\CDR;
use App\Models\CallCenterAgents;
use App\Models\ContactCenterCustomer;
use App\Models\ContactCenterCustomerInteraction;
use App\Models\ContactCenterTicket;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Customer360Service
{
    /**
     * Get paginated/filtered list of customers.
     */
    public function getCustomers(
        string $domainUuid,
        ?string $search = null,
        ?string $tier = null,
        ?string $status = null,
        int $limit = 50
    ) {
        $this->ensureSeedData($domainUuid);

        $query = ContactCenterCustomer::where('domain_uuid', $domainUuid)
            ->withCount(['tickets' => function ($q) {
                $q->whereIn('status', ['open', 'in_progress', 'pending_customer']);
            }])
            ->orderBy('tier', 'desc')
            ->orderBy('last_interaction_at', 'desc');

        if ($tier) {
            $query->where('tier', $tier);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $term = '%' . $search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'ilike', $term)
                    ->orWhere('last_name', 'ilike', $term)
                    ->orWhere('company', 'ilike', $term)
                    ->orWhere('phone', 'ilike', $term)
                    ->orWhere('email', 'ilike', $term)
                    ->orWhere('account_number', 'ilike', $term);
            });
        }

        return $query->limit($limit)->get();
    }

    /**
     * Get detailed Customer 360 profile with tickets, interactions, and CDR call history.
     */
    public function getCustomerDetails(string $domainUuid, string $customerUuid): ?array
    {
        $customer = ContactCenterCustomer::where('domain_uuid', $domainUuid)
            ->where('customer_uuid', $customerUuid)
            ->with([
                'tickets.assignedAgent',
                'interactions.agent',
            ])
            ->first();

        if (! $customer) {
            return null;
        }

        // Fetch telecom call history from CDR table
        $cleanPhone = preg_replace('/[^0-9]/', '', $customer->phone);
        $recentCalls = CDR::where('domain_uuid', $domainUuid)
            ->where(function ($q) use ($customer, $cleanPhone) {
                $q->where('caller_id_number', 'like', "%{$cleanPhone}%")
                    ->orWhere('destination_number', 'like', "%{$cleanPhone}%");
            })
            ->orderBy('start_epoch', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($c) => [
                'call_uuid' => $c->xml_cdr_uuid,
                'direction' => $c->direction,
                'caller_id_number' => $c->caller_id_number,
                'destination_number' => $c->destination_number,
                'start_stamp' => $c->start_date . ' ' . $c->start_time,
                'duration_formatted' => $c->duration_formatted,
                'billsec_formatted' => $c->billsec_formatted,
                'disposition' => $c->call_disposition ?? 'Normal Clearing',
                'cc_result' => $c->cc_result,
            ]);

        return [
            'customer' => $customer,
            'tickets' => $customer->tickets,
            'interactions' => $customer->interactions,
            'recent_calls' => $recentCalls,
        ];
    }

    /**
     * Create customer.
     */
    public function createCustomer(string $domainUuid, array $data): ContactCenterCustomer
    {
        return ContactCenterCustomer::create([
            'customer_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'tier' => $data['tier'] ?? 'standard',
            'account_number' => $data['account_number'] ?? ('ACC-' . rand(100000, 999999)),
            'status' => $data['status'] ?? 'active',
            'sentiment' => $data['sentiment'] ?? 'neutral',
            'notes' => $data['notes'] ?? null,
            'total_calls_count' => 0,
            'total_spend' => $data['total_spend'] ?? 0.00,
            'last_interaction_at' => Carbon::now(),
        ]);
    }

    /**
     * Update customer.
     */
    public function updateCustomer(string $domainUuid, string $customerUuid, array $data): ContactCenterCustomer
    {
        $customer = ContactCenterCustomer::where('domain_uuid', $domainUuid)
            ->where('customer_uuid', $customerUuid)
            ->firstOrFail();

        $customer->update($data);

        return $customer;
    }

    /**
     * Create support ticket / case.
     */
    public function createTicket(string $domainUuid, array $data): ContactCenterTicket
    {
        $priority = $data['priority'] ?? 'medium';
        $slaHours = match ($priority) {
            'urgent' => 4,
            'high' => 8,
            'medium' => 24,
            default => 48,
        };

        $ticket = ContactCenterTicket::create([
            'ticket_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'customer_uuid' => $data['customer_uuid'] ?? null,
            'assigned_agent_uuid' => $data['assigned_agent_uuid'] ?? null,
            'ticket_number' => 'TKT-' . rand(10000, 99999),
            'subject' => $data['subject'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'Technical Support',
            'priority' => $priority,
            'status' => 'open',
            'source' => $data['source'] ?? 'phone',
            'sla_due_at' => Carbon::now()->addHours($slaHours),
        ]);

        if (! empty($data['customer_uuid'])) {
            $this->logInteraction($domainUuid, [
                'customer_uuid' => $data['customer_uuid'],
                'ticket_uuid' => $ticket->ticket_uuid,
                'agent_uuid' => $data['assigned_agent_uuid'] ?? null,
                'channel' => 'note',
                'direction' => 'internal',
                'summary' => "Support ticket created: {$ticket->ticket_number} - {$ticket->subject}",
            ]);
        }

        return $ticket;
    }

    /**
     * Update support ticket.
     */
    public function updateTicket(string $domainUuid, string $ticketUuid, array $data): ContactCenterTicket
    {
        $ticket = ContactCenterTicket::where('domain_uuid', $domainUuid)
            ->where('ticket_uuid', $ticketUuid)
            ->firstOrFail();

        if (isset($data['status']) && $data['status'] === 'resolved' && $ticket->status !== 'resolved') {
            $data['resolved_at'] = Carbon::now();
        }

        $ticket->update($data);

        return $ticket;
    }

    /**
     * Log an omnichannel customer interaction (voice call, sms, chat, note).
     */
    public function logInteraction(string $domainUuid, array $data): ContactCenterCustomerInteraction
    {
        $interaction = ContactCenterCustomerInteraction::create([
            'interaction_uuid' => (string) Str::uuid(),
            'domain_uuid' => $domainUuid,
            'customer_uuid' => $data['customer_uuid'],
            'agent_uuid' => $data['agent_uuid'] ?? null,
            'ticket_uuid' => $data['ticket_uuid'] ?? null,
            'channel' => $data['channel'] ?? 'voice',
            'direction' => $data['direction'] ?? 'inbound',
            'summary' => $data['summary'],
            'details' => $data['details'] ?? null,
            'sentiment' => $data['sentiment'] ?? 'neutral',
            'duration_seconds' => $data['duration_seconds'] ?? 0,
        ]);

        $customer = ContactCenterCustomer::where('domain_uuid', $domainUuid)
            ->where('customer_uuid', $data['customer_uuid'])
            ->first();

        if ($customer) {
            $customer->last_interaction_at = Carbon::now();
            if (($data['channel'] ?? '') === 'voice') {
                $customer->increment('total_calls_count');
            }
            if (isset($data['sentiment'])) {
                $customer->sentiment = $data['sentiment'];
            }
            $customer->save();
        }

        return $interaction;
    }

    /**
     * Fast phone lookup for softphone Screen-Pop.
     */
    public function lookupByPhone(string $domainUuid, string $phone): ?array
    {
        $clean = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($clean) < 4) {
            return null;
        }

        $searchDigits = strlen($clean) > 7 ? substr($clean, -7) : $clean;

        $customer = ContactCenterCustomer::where('domain_uuid', $domainUuid)
            ->where(function ($q) use ($phone, $clean, $searchDigits) {
                $q->where('phone', 'like', "%{$phone}%")
                    ->orWhereRaw("regexp_replace(phone, '[^0-9]', '', 'g') LIKE ?", ["%{$clean}%"])
                    ->orWhereRaw("regexp_replace(phone, '[^0-9]', '', 'g') LIKE ?", ["%{$searchDigits}%"]);
            })
            ->withCount(['tickets' => fn ($q) => $q->whereIn('status', ['open', 'in_progress'])])
            ->first();

        if (! $customer) {
            return null;
        }

        return [
            'found' => true,
            'customer_uuid' => $customer->customer_uuid,
            'full_name' => $customer->full_name,
            'company' => $customer->company,
            'phone' => $customer->phone,
            'email' => $customer->email,
            'tier' => $customer->tier,
            'status' => $customer->status,
            'sentiment' => $customer->sentiment,
            'open_tickets_count' => $customer->tickets_count,
            'account_number' => $customer->account_number,
        ];
    }

    /**
     * Seed initial demo customers if table is completely empty for this domain.
     */
    protected function ensureSeedData(string $domainUuid): void
    {
        if (ContactCenterCustomer::where('domain_uuid', $domainUuid)->exists()) {
            return;
        }

        $samples = [
            [
                'first_name' => 'Alexander',
                'last_name' => 'Wright',
                'company' => 'Apex Global Logistics',
                'phone' => '+1 (555) 234-5678',
                'email' => 'a.wright@apexlogistics.com',
                'tier' => 'vip',
                'account_number' => 'ACC-992144',
                'status' => 'active',
                'sentiment' => 'positive',
                'notes' => 'Enterprise client with 24/7 SLA. VIP Routing Priority enabled.',
                'total_calls_count' => 14,
                'total_spend' => 18450.00,
                'last_interaction_at' => Carbon::now()->subHours(2),
            ],
            [
                'first_name' => 'Sophia',
                'last_name' => 'Martinez',
                'company' => 'Horizon Media Partners',
                'phone' => '+1 (555) 876-5432',
                'email' => 'smartinez@horizonmedia.com',
                'tier' => 'gold',
                'account_number' => 'ACC-448219',
                'status' => 'active',
                'sentiment' => 'neutral',
                'notes' => 'Requested monthly usage report integration and callback scheduling.',
                'total_calls_count' => 9,
                'total_spend' => 9200.00,
                'last_interaction_at' => Carbon::now()->subDays(1),
            ],
            [
                'first_name' => 'Liam',
                'last_name' => 'O\'Connor',
                'company' => 'Beacon Financial Services',
                'phone' => '+1 (555) 345-6789',
                'email' => 'liam.oc@beaconfin.org',
                'tier' => 'silver',
                'account_number' => 'ACC-312984',
                'status' => 'active',
                'sentiment' => 'positive',
                'notes' => 'Inquiring about multi-queue overflow and IVR routing rules.',
                'total_calls_count' => 5,
                'total_spend' => 3800.00,
                'last_interaction_at' => Carbon::now()->subDays(3),
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Chen',
                'company' => 'NextGen Cloud Systems',
                'phone' => '+1 (555) 987-6543',
                'email' => 'emily.chen@nextgencloud.io',
                'tier' => 'standard',
                'account_number' => 'ACC-110294',
                'status' => 'active',
                'sentiment' => 'negative',
                'notes' => 'Reported SIP trunk jitter during peak traffic. Ticket escalated.',
                'total_calls_count' => 8,
                'total_spend' => 1250.00,
                'last_interaction_at' => Carbon::now()->subHours(5),
            ],
        ];

        foreach ($samples as $sample) {
            $cust = $this->createCustomer($domainUuid, $sample);

            // Add sample ticket
            $ticket = $this->createTicket($domainUuid, [
                'customer_uuid' => $cust->customer_uuid,
                'subject' => $cust->tier === 'vip' ? 'Priority Trunk Latency Review' : 'Billing Invoice Verification',
                'category' => $cust->tier === 'vip' ? 'Technical Support' : 'Billing & Invoices',
                'priority' => $cust->tier === 'vip' ? 'urgent' : 'medium',
                'description' => 'Customer inquired regarding connection quality and invoice breakdown.',
                'source' => 'phone',
            ]);

            // Add sample call interaction
            $this->logInteraction($domainUuid, [
                'customer_uuid' => $cust->customer_uuid,
                'ticket_uuid' => $ticket->ticket_uuid,
                'channel' => 'voice',
                'direction' => 'inbound',
                'summary' => 'Inbound support call handled by Tier 1 Queue',
                'details' => 'Discussed account configuration and confirmed routing settings.',
                'sentiment' => $cust->sentiment,
                'duration_seconds' => rand(140, 260),
            ]);
        }
    }
}
