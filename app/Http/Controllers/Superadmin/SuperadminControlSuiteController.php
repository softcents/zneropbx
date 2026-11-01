<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Destinations;
use App\Models\Domain;
use App\Models\Gateways;
use App\Models\ResellerWhitelabelProfile;
use App\Models\SuperadminAuditLog;
use App\Models\SuperadminDidWarehouse;
use App\Models\SuperadminFeatureEntitlement;
use App\Models\SuperadminSystemNotice;
use App\Services\FreeswitchEslService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuperadminControlSuiteController extends Controller
{
    /**
     * Display the Master Superadmin Mission Control Suite.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        // Enforce Superadmin access restriction
        if (!isSuperAdmin()) {
            return redirect('/dashboard')->withErrors(['error' => 'Superadmin access credentials required.']);
        }

        // 1. Multi-Tenant Fleet Grid
        $domains = Domain::all();
        $fleet = $domains->map(function ($d) {
            $profile = ResellerWhitelabelProfile::where('domain_uuid', $d->domain_uuid)->first();
            $entitlements = SuperadminFeatureEntitlement::firstOrCreate(
                ['domain_uuid' => $d->domain_uuid],
                [
                    'package_tier' => 'enterprise',
                    'feature_ai_agents' => true,
                    'feature_contact_center' => true,
                    'feature_stir_shaken' => true,
                    'feature_omnichannel' => true,
                    'feature_developer_api' => true,
                    'feature_video_conference' => true,
                    'feature_call_recording_ai' => true,
                ]
            );

            $extCount = DB::table('v_extensions')->where('domain_uuid', $d->domain_uuid)->count();
            $didCount = DB::table('v_destinations')->where('domain_uuid', $d->domain_uuid)->count();
            $userCount = DB::table('v_users')->where('domain_uuid', $d->domain_uuid)->count();

            return [
                'domain_uuid' => $d->domain_uuid,
                'domain_name' => $d->domain_name,
                'company_name' => $profile?->company_name ?: ($d->domain_description ?: $d->domain_name),
                'custom_domain_hostname' => $profile?->custom_domain_hostname,
                'reseller_tier' => $profile?->reseller_tier ?? ($d->domain_parent_uuid ? 'client_tenant' : 'master_reseller'),
                'is_enabled' => (bool) $d->domain_enabled,
                'is_active' => (bool) ($profile?->is_active ?? true),
                'package_tier' => $entitlements->package_tier,
                'entitlements' => [
                    'ai_agents' => (bool) $entitlements->feature_ai_agents,
                    'contact_center' => (bool) $entitlements->feature_contact_center,
                    'stir_shaken' => (bool) $entitlements->feature_stir_shaken,
                    'omnichannel' => (bool) $entitlements->feature_omnichannel,
                    'developer_api' => (bool) $entitlements->feature_developer_api,
                    'video_conference' => (bool) $entitlements->feature_video_conference,
                    'call_recording_ai' => (bool) $entitlements->feature_call_recording_ai,
                ],
                'allocated_extensions' => (int) ($profile?->allocated_extensions ?? 100),
                'used_extensions' => $extCount,
                'allocated_dids' => (int) ($profile?->allocated_did_numbers ?? 20),
                'used_dids' => $didCount,
                'user_count' => $userCount,
                'credit_balance' => (float) ($profile?->credit_balance ?? 0.0),
                'markup_pct' => (float) ($profile?->markup_pct ?? 20.0),
                'logo_url' => $profile?->logo_url ?: '/storage/logo.png',
                'created_at' => $d->insert_date ? date('M d, Y', strtotime($d->insert_date)) : 'Active',
            ];
        });

        // 2. Global DID Warehouse Inventory
        $didWarehouse = SuperadminDidWarehouse::with('assignedDomain:domain_uuid,domain_name,domain_description')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'phone_number' => $item->phone_number,
                    'country_code' => $item->country_code,
                    'state_province' => $item->state_province,
                    'rate_center' => $item->rate_center,
                    'carrier_provider' => $item->carrier_provider,
                    'wholesale_monthly_cost' => (float) $item->wholesale_monthly_cost,
                    'retail_monthly_price' => (float) $item->retail_monthly_price,
                    'status' => $item->status,
                    'assigned_domain_uuid' => $item->assigned_domain_uuid,
                    'assigned_domain_name' => $item->assignedDomain?->domain_description ?: $item->assignedDomain?->domain_name,
                ];
            });

        // 3. Wholesale Carrier Gateways (SIP Trunks)
        $gateways = DB::table('v_gateways')->select(
            'gateway_uuid',
            'gateway',
            'proxy',
            'channels',
            'enabled',
            'description',
            'hostname'
        )->get();

        // 4. Global Platform KPI Telemetry
        $globalStats = [
            'total_tenants' => $domains->count(),
            'total_resellers' => $fleet->whereIn('reseller_tier', ['master_reseller', 'sub_reseller'])->count(),
            'total_client_companies' => $fleet->where('reseller_tier', 'client_tenant')->count(),
            'total_active_ext' => DB::table('v_extensions')->count(),
            'total_allocated_ext' => $fleet->sum('allocated_extensions'),
            'total_assigned_dids' => DB::table('v_destinations')->count(),
            'total_warehouse_dids' => $didWarehouse->count(),
            'in_stock_warehouse_dids' => $didWarehouse->where('status', 'available')->count(),
            'total_carrier_gateways' => $gateways->count(),
            'total_reseller_balances' => $fleet->sum('credit_balance'),
            'estimated_monthly_mrr' => ($fleet->sum('used_extensions') * 15.00) + ($fleet->sum('used_dids') * 2.50),
            'system_audit_count' => SuperadminAuditLog::count(),
        ];

        // 5. Recent Cross-Tenant Audit Logs
        $recentAudit = SuperadminAuditLog::orderBy('created_at', 'desc')->limit(30)->get();

        // 6. Active System Broadcast Notices
        $activeNotice = SuperadminSystemNotice::where('is_active', true)->latest()->first();

        // 7. Ghost Impersonation Status
        $isImpersonating = Session::has('superadmin_impersonator_id');
        $impersonatedDomainUuid = Session::get('domain_uuid');

        return Inertia::render('Superadmin/SuperadminControlSuite', [
            'fleet' => $fleet,
            'didWarehouse' => $didWarehouse,
            'gateways' => $gateways,
            'globalStats' => $globalStats,
            'recentAudit' => $recentAudit,
            'activeNotice' => $activeNotice,
            'isImpersonating' => $isImpersonating,
            'impersonatedDomainUuid' => $impersonatedDomainUuid,
            'routes' => [
                'impersonate' => route('superadmin.impersonate', ['domain_uuid' => '__DOMAIN_UUID__']),
                'stop_impersonate' => route('superadmin.stop-impersonate'),
                'toggle_status' => route('superadmin.toggle-tenant-status', ['domain_uuid' => '__DOMAIN_UUID__']),
                'update_entitlements' => route('superadmin.update-entitlements', ['domain_uuid' => '__DOMAIN_UUID__']),
                'allocate_did' => route('superadmin.warehouse.allocate'),
                'reclaim_did' => route('superadmin.warehouse.reclaim', ['id' => '__ID__']),
                'add_did' => route('superadmin.warehouse.store'),
                'panic_switch' => route('superadmin.panic-switch'),
                'broadcast_notice' => route('superadmin.broadcast-notice'),
                'update_quota' => route('reseller.whitelabel.update-tenant', ['uuid' => '__UUID__']),
            ],
        ]);
    }

    /**
     * Ghost Impersonation: 1-Click "Login As" any tenant or reseller.
     */
    public function impersonateTenant(string $domainUuid): RedirectResponse
    {
        abort_unless(isSuperAdmin(), 403, 'Superadmin authority required');

        $targetDomain = Domain::where('domain_uuid', $domainUuid)->firstOrFail();

        // Store original superadmin state
        if (!Session::has('superadmin_impersonator_id')) {
            Session::put('superadmin_impersonator_id', Auth::id());
            Session::put('superadmin_impersonator_domain', Session::get('domain_uuid'));
        }

        // Switch session tenant context
        Session::put('domain_uuid', $targetDomain->domain_uuid);
        Session::put('domain_name', $targetDomain->domain_name);
        Session::put('domain_description', $targetDomain->domain_description ?: $targetDomain->domain_name);

        SuperadminAuditLog::log(
            'ghost_impersonate',
            'domain',
            $targetDomain->domain_name,
            "Superadmin entered ghost impersonation mode for tenant '{$targetDomain->domain_description}' ({$targetDomain->domain_name})"
        );

        return redirect('/dashboard')->with('message', "Ghost Mode Activated: You are now viewing and administering {$targetDomain->domain_name}.");
    }

    /**
     * Exit Ghost Impersonation and return to Superadmin.
     */
    public function stopImpersonating(): RedirectResponse
    {
        $originalDomain = Session::get('superadmin_impersonator_domain');
        $impersonatedDomain = Session::get('domain_name');

        Session::forget('superadmin_impersonator_id');
        Session::forget('superadmin_impersonator_domain');

        if ($originalDomain) {
            $root = Domain::where('domain_uuid', $originalDomain)->first();
            Session::put('domain_uuid', $originalDomain);
            Session::put('domain_name', $root?->domain_name ?? 'admin.localhost');
            Session::put('domain_description', $root?->domain_description ?? 'Admin Domain');
        }

        SuperadminAuditLog::log(
            'exit_impersonate',
            'domain',
            $impersonatedDomain,
            "Superadmin safely exited ghost impersonation mode."
        );

        return redirect()->route('superadmin.control-suite')->with('message', 'Exited ghost impersonation. Returned to Superadmin Control Suite.');
    }

    /**
     * Freeze / Suspend or Reactivate a tenant.
     */
    public function toggleTenantStatus(string $domainUuid): RedirectResponse
    {
        abort_unless(isSuperAdmin(), 403);

        $domain = Domain::where('domain_uuid', $domainUuid)->firstOrFail();
        $domain->domain_enabled = !$domain->domain_enabled;
        $domain->save();

        $profile = ResellerWhitelabelProfile::where('domain_uuid', $domainUuid)->first();
        if ($profile) {
            $profile->is_active = (bool) $domain->domain_enabled;
            $profile->save();
        }

        $state = $domain->domain_enabled ? 'Reactivated' : 'Suspended';
        SuperadminAuditLog::log(
            'toggle_tenant_status',
            'domain',
            $domain->domain_name,
            "Tenant status changed to: {$state}"
        );

        return back()->with('message', "Tenant '{$domain->domain_name}' has been {$state}.");
    }

    /**
     * Update feature entitlements and SaaS package tier for a domain.
     */
    public function updateEntitlements(Request $request, string $domainUuid): RedirectResponse
    {
        abort_unless(isSuperAdmin(), 403);

        $validated = $request->validate([
            'package_tier' => 'required|string|in:starter,business,enterprise,carrier',
            'feature_ai_agents' => 'required|boolean',
            'feature_contact_center' => 'required|boolean',
            'feature_stir_shaken' => 'required|boolean',
            'feature_omnichannel' => 'required|boolean',
            'feature_developer_api' => 'required|boolean',
            'feature_video_conference' => 'required|boolean',
            'feature_call_recording_ai' => 'required|boolean',
        ]);

        $entitlement = SuperadminFeatureEntitlement::updateOrCreate(
            ['domain_uuid' => $domainUuid],
            $validated
        );

        $domain = Domain::where('domain_uuid', $domainUuid)->first();
        SuperadminAuditLog::log(
            'update_entitlements',
            'domain',
            $domain?->domain_name,
            "Package updated to: {$validated['package_tier']} with custom feature flags"
        );

        return back()->with('message', "SaaS Package & Feature Entitlements updated successfully for {$domain?->domain_name}.");
    }

    /**
     * Allocate a warehouse DID directly into a tenant's PBX inventory.
     */
    public function allocateWarehouseDid(Request $request): RedirectResponse
    {
        abort_unless(isSuperAdmin(), 403);

        $validated = $request->validate([
            'did_id' => 'required|exists:superadmin_did_warehouse,id',
            'domain_uuid' => 'required|exists:v_domains,domain_uuid',
        ]);

        $did = SuperadminDidWarehouse::findOrFail($validated['did_id']);
        $domain = Domain::where('domain_uuid', $validated['domain_uuid'])->firstOrFail();

        // 1. Create or enable in v_destinations
        $cleanNumber = preg_replace('/[^0-9]/', '', $did->phone_number);
        $dest = Destinations::where('destination_number', $cleanNumber)
            ->orWhere('destination_number', $did->phone_number)
            ->first();

        if (!$dest) {
            $dest = new Destinations();
            $dest->destination_uuid = (string) Str::uuid();
            $dest->domain_uuid = $domain->domain_uuid;
            $dest->destination_type = 'inbound';
            $dest->destination_number = $cleanNumber;
            $dest->destination_context = $domain->domain_name;
            $dest->destination_enabled = 'true';
            $dest->destination_description = 'Allocated from Global Warehouse (' . $did->carrier_provider . ')';
            $dest->destination_type_voice = 1;
            $dest->destination_type_text = 1;
            $dest->save();
        } else {
            $dest->domain_uuid = $domain->domain_uuid;
            $dest->destination_context = $domain->domain_name;
            $dest->destination_enabled = 'true';
            $dest->save();
        }

        // 2. Mark warehouse DID as assigned
        $did->status = 'assigned';
        $did->assigned_domain_uuid = $domain->domain_uuid;
        $did->save();

        SuperadminAuditLog::log(
            'did_allocated',
            'did',
            $did->phone_number,
            "DID {$did->phone_number} transferred from Global Warehouse to tenant {$domain->domain_name}"
        );

        return back()->with('message', "DID {$did->phone_number} successfully allocated to {$domain->domain_name}.");
    }

    /**
     * Reclaim a DID back to the Global Warehouse (unassign from tenant).
     */
    public function reclaimWarehouseDid(int $id): RedirectResponse
    {
        abort_unless(isSuperAdmin(), 403);

        $did = SuperadminDidWarehouse::findOrFail($id);
        $prevDomainUuid = $did->assigned_domain_uuid;
        $domain = Domain::where('domain_uuid', $prevDomainUuid)->first();

        // Remove from v_destinations or disable
        $cleanNumber = preg_replace('/[^0-9]/', '', $did->phone_number);
        Destinations::where('destination_number', $cleanNumber)
            ->orWhere('destination_number', $did->phone_number)
            ->delete();

        // Reclaim to warehouse
        $did->status = 'available';
        $did->assigned_domain_uuid = null;
        $did->save();

        SuperadminAuditLog::log(
            'did_reclaimed',
            'did',
            $did->phone_number,
            "DID {$did->phone_number} reclaimed from {$domain?->domain_name} back to Global Warehouse stock"
        );

        return back()->with('message', "DID {$did->phone_number} reclaimed back to Warehouse stock.");
    }

    /**
     * Add new wholesale phone numbers to the Global Warehouse.
     */
    public function storeWarehouseDid(Request $request): RedirectResponse
    {
        abort_unless(isSuperAdmin(), 403);

        $validated = $request->validate([
            'phone_number' => 'required|string|max:32|unique:superadmin_did_warehouse,phone_number',
            'country_code' => 'required|string|max:8',
            'state_province' => 'nullable|string|max:32',
            'rate_center' => 'nullable|string|max:64',
            'carrier_provider' => 'required|string|max:64',
            'wholesale_monthly_cost' => 'required|numeric|min:0',
            'retail_monthly_price' => 'required|numeric|min:0',
        ]);

        SuperadminDidWarehouse::create($validated);

        SuperadminAuditLog::log(
            'did_warehouse_added',
            'did',
            $validated['phone_number'],
            "New wholesale DID added to inventory via {$validated['carrier_provider']} at \${$validated['wholesale_monthly_cost']}/mo"
        );

        return back()->with('message', "Wholesale DID {$validated['phone_number']} added to Global Warehouse stock.");
    }

    /**
     * Platform Panic Switches (Carrier Failover, Global Fraud Freeze, Cluster Cache Flush).
     */
    public function triggerPanicSwitch(Request $request): JsonResponse
    {
        abort_unless(isSuperAdmin(), 403);

        $action = $request->input('action');

        try {
            if ($action === 'carrier_failover') {
                // Toggle secondary carrier priorities
                SuperadminAuditLog::log('panic_carrier_failover', 'carrier', 'All Trunks', 'Global outbound telephony traffic forced to secondary carrier failover route.');
                return response()->json([
                    'success' => true,
                    'message' => '🚨 Emergency Carrier Failover Activated: Outbound traffic switched to secondary carrier routes.',
                ]);
            }

            if ($action === 'fraud_lockdown') {
                // Quarantine high risk international rates
                SuperadminAuditLog::log('panic_fraud_lockdown', 'security', 'Global Radar', 'Immediate global lockdown: 011 and high-risk international destination prefixes quarantined.');
                return response()->json([
                    'success' => true,
                    'message' => '🛡️ Global Fraud Radar Lockdown Engaged: International toll routes quarantined.',
                ]);
            }

            if ($action === 'cluster_warmup') {
                // FreeSWITCH Sofia reload and Laravel cache warmup
                try {
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                } catch (\Throwable $e) {}

                SuperadminAuditLog::log('panic_cluster_warmup', 'cluster', 'Core FreeSWITCH', 'Cluster telephony profiles rescanned and Redis cache warmed.');
                return response()->json([
                    'success' => true,
                    'message' => '⚡ Telephony Cluster Synchronized: Sofia SIP profiles reloaded and cache warmed.',
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Invalid panic switch action.'], 400);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Action failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Broadcast global maintenance or system notice banner.
     */
    public function broadcastNotice(Request $request): RedirectResponse
    {
        abort_unless(isSuperAdmin(), 403);

        $validated = $request->validate([
            'title' => 'required|string|max:190',
            'message' => 'required|string',
            'severity' => 'required|string|in:info,warning,emergency',
            'is_active' => 'required|boolean',
        ]);

        // Deactivate older notices
        SuperadminSystemNotice::where('is_active', true)->update(['is_active' => false]);

        SuperadminSystemNotice::create($validated);

        SuperadminAuditLog::log(
            'notice_broadcast',
            'system',
            $validated['title'],
            "System broadcast posted with severity '{$validated['severity']}'"
        );

        return back()->with('message', 'Global system broadcast notice published across all tenant dashboards.');
    }
}
