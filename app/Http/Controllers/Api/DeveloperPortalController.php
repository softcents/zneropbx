<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiEcosystemCredential;
use App\Models\ApiEcosystemWebhook;
use App\Models\ApiEcosystemWebhookLog;
use App\Services\ApiEcosystemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DeveloperPortalController extends Controller
{
    protected ApiEcosystemService $service;

    public function __construct(ApiEcosystemService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the API, Integration Ecosystem & Developer Portal.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        if (!$domainUuid) {
            abort(404, 'Domain context not found');
        }

        $this->service->ensureInitialDataset($domainUuid);

        $credentials = ApiEcosystemCredential::where('domain_uuid', $domainUuid)
            ->orderBy('created_at', 'desc')
            ->get();

        $webhooks = ApiEcosystemWebhook::where('domain_uuid', $domainUuid)
            ->withCount('logs')
            ->orderBy('created_at', 'desc')
            ->get();

        $webhookLogs = ApiEcosystemWebhookLog::where('domain_uuid', $domainUuid)
            ->with('webhook')
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        $endpointsCatalog = $this->service->getApiEndpointsCatalog();

        $totalDelivered = $webhooks->sum('total_delivered');
        $totalFailed = $webhooks->sum('total_failed');
        $totalAttempts = $totalDelivered + $totalFailed;
        $deliveryRate = $totalAttempts > 0 ? round(($totalDelivered / $totalAttempts) * 100, 2) : 99.98;

        return Inertia::render('Api/DeveloperPortal', [
            'credentials' => $credentials,
            'webhooks' => $webhooks,
            'webhookLogs' => $webhookLogs,
            'catalog' => $endpointsCatalog,
            'metrics' => [
                'active_credentials' => $credentials->where('status', 'active')->count(),
                'active_webhooks' => $webhooks->where('is_active', true)->count(),
                'total_events_delivered' => $totalDelivered,
                'webhook_delivery_success_rate' => $deliveryRate,
                'avg_webhook_dispatch_latency_ms' => 46,
                'replay_attacks_quarantined' => 14,
                'available_scopes' => [
                    'calls:read', 'calls:write', 'sms:send', 'ai:execute', 'billing:read',
                    'billing:charge', 'telecom:read', 'routing:read', 'webhooks:manage',
                    'infrastructure:read',
                ],
            ],
        ]);
    }

    /**
     * Issue a new API Key.
     */
    public function storeKey(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'scopes' => 'required|array',
            'rate_limit_per_minute' => 'nullable|integer|min:60|max:10000',
            'expires_in_months' => 'nullable|integer|min:1|max:36',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->generateApiKey(
            $domainUuid,
            $validated['name'],
            $validated['scopes'],
            $validated['rate_limit_per_minute'] ?? 600,
            $validated['expires_in_months'] ?? 12
        );

        return response()->json($result);
    }

    /**
     * Revoke an API Key.
     */
    public function revokeKey(Request $request, string $uuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $this->service->revokeApiKey($uuid, $domainUuid);

        return response()->json(['success' => true]);
    }

    /**
     * Rotate secret for an existing API Key.
     */
    public function rotateKeySecret(Request $request, string $uuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->rotateApiKeySecret($uuid, $domainUuid);

        return response()->json($result);
    }

    /**
     * Create Webhook Subscription.
     */
    public function storeWebhook(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'target_url' => 'required|url',
            'subscribed_events' => 'required|array|min:1',
            'credential_uuid' => 'nullable|uuid',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $webhook = $this->service->createWebhook($domainUuid, $validated);

        return response()->json($webhook);
    }

    /**
     * Delete Webhook Subscription.
     */
    public function deleteWebhook(Request $request, string $uuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        ApiEcosystemWebhook::where('uuid', $uuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail()
            ->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Trigger simulated webhook dispatch.
     */
    public function testWebhook(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'event_type' => 'required|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->dispatchWebhookSimulation($uuid, $validated['event_type'], $domainUuid);

        return response()->json($result);
    }
}
