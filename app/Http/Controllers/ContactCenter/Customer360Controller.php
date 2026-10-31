<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CallCenterAgents;
use App\Services\ContactCenter\Customer360Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Customer360Controller extends Controller
{
    protected Customer360Service $customerService;

    public function __construct(Customer360Service $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display the Customer 360 & Ticket Workspace.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid');
        $search = $request->query('search');
        $tier = $request->query('tier');
        $status = $request->query('status');

        $customers = $this->customerService->getCustomers($domainUuid, $search, $tier, $status);

        $selectedUuid = $request->query('customer_uuid') ?: ($customers->first()?->customer_uuid ?? null);
        $activeCustomerDetails = $selectedUuid ? $this->customerService->getCustomerDetails($domainUuid, $selectedUuid) : null;

        $agents = CallCenterAgents::where('domain_uuid', $domainUuid)
            ->select('call_center_agent_uuid', 'agent_name', 'agent_id')
            ->get();

        return Inertia::render('ContactCenter/Customer360', [
            'customers' => $customers,
            'activeCustomer' => $activeCustomerDetails,
            'agents' => $agents,
            'filters' => [
                'search' => $search,
                'tier' => $tier,
                'status' => $status,
                'customer_uuid' => $selectedUuid,
            ],
        ]);
    }

    /**
     * Fetch customer profile details via JSON (for fast client-side tab switching).
     */
    public function show(Request $request, string $customerUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $details = $this->customerService->getCustomerDetails($domainUuid, $customerUuid);

        if (! $details) {
            return response()->json(['error' => 'Customer not found'], 404);
        }

        return response()->json($details);
    }

    /**
     * Create new customer profile.
     */
    public function store(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:150',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'tier' => 'nullable|string|in:standard,silver,gold,vip',
            'account_number' => 'nullable|string|max:50',
            'status' => 'nullable|string|in:active,inactive,blocked',
            'sentiment' => 'nullable|string|in:positive,neutral,negative',
            'notes' => 'nullable|string|max:2000',
            'total_spend' => 'nullable|numeric|min:0',
        ]);

        $customer = $this->customerService->createCustomer($domainUuid, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer profile created successfully.',
            'customer' => $customer,
        ]);
    }

    /**
     * Update customer profile.
     */
    public function update(Request $request, string $customerUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'company' => 'nullable|string|max:150',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:150',
            'tier' => 'required|string|in:standard,silver,gold,vip',
            'account_number' => 'nullable|string|max:50',
            'status' => 'required|string|in:active,inactive,blocked',
            'sentiment' => 'required|string|in:positive,neutral,negative',
            'notes' => 'nullable|string|max:2000',
            'total_spend' => 'nullable|numeric|min:0',
        ]);

        $customer = $this->customerService->updateCustomer($domainUuid, $customerUuid, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Customer profile updated.',
            'customer' => $customer,
        ]);
    }

    /**
     * Create support ticket for a customer.
     */
    public function storeTicket(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'customer_uuid' => 'required|string|uuid',
            'subject' => 'required|string|max:200',
            'description' => 'nullable|string|max:5000',
            'category' => 'required|string',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'assigned_agent_uuid' => 'nullable|string|uuid',
            'source' => 'nullable|string',
        ]);

        $ticket = $this->customerService->createTicket($domainUuid, $validated);

        return response()->json([
            'success' => true,
            'message' => "Ticket {$ticket->ticket_number} created successfully.",
            'ticket' => $ticket,
        ]);
    }

    /**
     * Update ticket status / details.
     */
    public function updateTicket(Request $request, string $ticketUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'status' => 'nullable|string|in:open,in_progress,pending_customer,resolved,closed',
            'priority' => 'nullable|string|in:low,medium,high,urgent',
            'assigned_agent_uuid' => 'nullable|string|uuid',
            'resolution_notes' => 'nullable|string|max:3000',
        ]);

        $ticket = $this->customerService->updateTicket($domainUuid, $ticketUuid, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Ticket updated successfully.',
            'ticket' => $ticket,
        ]);
    }

    /**
     * Log interaction note or call event.
     */
    public function logInteraction(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'customer_uuid' => 'required|string|uuid',
            'ticket_uuid' => 'nullable|string|uuid',
            'channel' => 'required|string',
            'direction' => 'required|string',
            'summary' => 'required|string|max:255',
            'details' => 'nullable|string|max:3000',
            'sentiment' => 'nullable|string',
            'duration_seconds' => 'nullable|integer',
        ]);

        $interaction = $this->customerService->logInteraction($domainUuid, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Interaction logged to customer timeline.',
            'interaction' => $interaction,
        ]);
    }

    /**
     * Fast Screen-Pop API lookup by phone number for softphone/agent desktop.
     */
    public function screenPop(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $phone = $request->query('phone', '');

        $result = $this->customerService->lookupByPhone($domainUuid, $phone);

        if (! $result) {
            return response()->json(['found' => false], 404);
        }

        return response()->json($result);
    }
}
