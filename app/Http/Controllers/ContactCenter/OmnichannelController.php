<?php

namespace App\Http\Controllers\ContactCenter;

use App\Http\Controllers\Controller;
use App\Models\CallCenterAgents;
use App\Services\ContactCenter\OmnichannelMessagingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OmnichannelController extends Controller
{
    protected OmnichannelMessagingService $messagingService;

    public function __construct(OmnichannelMessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    /**
     * Display the Omnichannel Messaging & Conversation Hub.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid');
        $channel = $request->query('channel', 'all');
        $status = $request->query('status', 'all');
        $agentUuid = $request->query('agent_uuid');

        $conversations = $this->messagingService->getConversations($domainUuid, $channel, $status, $agentUuid);

        $selectedUuid = $request->query('conversation_uuid') ?: ($conversations->first()?->conversation_uuid ?? null);
        $activeThread = $selectedUuid ? $this->messagingService->getConversationThread($domainUuid, $selectedUuid) : null;

        $cannedResponses = $this->messagingService->getCannedResponses($domainUuid);

        $agents = CallCenterAgents::where('domain_uuid', $domainUuid)
            ->select('call_center_agent_uuid', 'agent_name', 'agent_id')
            ->get();

        return Inertia::render('ContactCenter/Omnichannel', [
            'conversations' => $conversations,
            'activeThread' => $activeThread,
            'cannedResponses' => $cannedResponses,
            'agents' => $agents,
            'filters' => [
                'channel' => $channel,
                'status' => $status,
                'agent_uuid' => $agentUuid,
                'conversation_uuid' => $selectedUuid,
            ],
        ]);
    }

    /**
     * Fetch thread messages via JSON for fast client-side switching.
     */
    public function show(Request $request, string $conversationUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');
        $thread = $this->messagingService->getConversationThread($domainUuid, $conversationUuid);

        if (! $thread) {
            return response()->json(['error' => 'Thread not found'], 404);
        }

        return response()->json($thread);
    }

    /**
     * Send agent message reply.
     */
    public function sendMessage(Request $request, string $conversationUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'body' => 'required|string|max:4000',
            'agent_uuid' => 'nullable|string|uuid',
            'attachments' => 'nullable|array',
        ]);

        $message = $this->messagingService->sendAgentMessage(
            $domainUuid,
            $conversationUuid,
            $validated['agent_uuid'] ?? null,
            $validated['body'],
            $validated['attachments'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Assign conversation to agent.
     */
    public function assign(Request $request, string $conversationUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'agent_uuid' => 'nullable|string|uuid',
        ]);

        $conversation = $this->messagingService->assignConversation(
            $domainUuid,
            $conversationUuid,
            $validated['agent_uuid'] ?? null
        );

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Update conversation status.
     */
    public function updateStatus(Request $request, string $conversationUuid): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'status' => 'required|string|in:queued,assigned,open,waiting_customer,resolved,closed',
        ]);

        $conversation = $this->messagingService->updateStatus(
            $domainUuid,
            $conversationUuid,
            $validated['status']
        );

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
        ]);
    }

    /**
     * Simulate an incoming customer message across SMS, WhatsApp, Web Chat.
     */
    public function simulateInbound(Request $request): JsonResponse
    {
        $domainUuid = session('domain_uuid');

        $validated = $request->validate([
            'channel' => 'required|string|in:sms,whatsapp,web_chat,email',
            'from' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'body' => 'required|string|max:2000',
        ]);

        $conversation = $this->messagingService->simulateInboundMessage(
            $domainUuid,
            $validated['channel'],
            $validated['from'],
            $validated['name'],
            $validated['body']
        );

        return response()->json([
            'success' => true,
            'conversation' => $conversation,
            'message' => 'Simulated inbound message delivered to queue.',
        ]);
    }
}
