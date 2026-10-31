<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\ResellerWhitelabelProfile;
use App\Services\WhiteLabelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ResellerWhitelabelController extends Controller
{
    public function __construct(
        protected WhiteLabelService $whiteLabelService
    ) {}

    /**
     * Display the White-Label and Reseller Studio workspace.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $domainUuid = $request->query('domain_uuid') ?? Session::get('domain_uuid') ?? Auth::user()?->domain_uuid;
        
        // Find or create profile for active domain
        $currentDomain = Domain::where('domain_uuid', $domainUuid)->first();
        if (!$currentDomain) {
            $currentDomain = Domain::first();
            $domainUuid = $currentDomain?->domain_uuid;
        }

        $profile = ResellerWhitelabelProfile::firstOrCreate(
            ['domain_uuid' => $domainUuid],
            [
                'uuid' => (string) Str::uuid(),
                'reseller_tier' => 'master_reseller',
                'company_name' => $currentDomain?->domain_description ?: ($currentDomain?->domain_name ?: 'Cloud PBX Platform'),
                'custom_domain_hostname' => $currentDomain?->domain_name,
                'logo_url' => '/storage/logo.png',
                'favicon_url' => '/storage/favicon-32x32.png',
                'login_heading' => 'Sign in to your account',
                'login_subheading' => 'Enterprise Cloud Telephony & Contact Center',
                'login_accent_color' => '#4f46e5',
                'support_email' => 'support@' . ($currentDomain?->domain_name ?: 'zneropbx.com'),
                'support_phone' => '+1 (800) 555-0199',
                'copyright_text' => '© ' . date('Y') . ' ' . ($currentDomain?->domain_description ?: 'Cloud PBX') . '. All rights reserved.',
                'sms_sender_id' => 'PBX_AUTH',
                'allocated_extensions' => 100,
                'allocated_did_numbers' => 20,
                'credit_balance' => 500.00,
                'markup_pct' => 25.00,
                'is_active' => true,
            ]
        );

        // Sub-resellers and client tenants under this profile
        $subProfiles = ResellerWhitelabelProfile::where('parent_reseller_uuid', $profile->uuid)
            ->orWhere(function ($query) use ($profile) {
                // If superadmin root, show all other profiles
                if ($profile->reseller_tier === 'master_reseller') {
                    $query->where('uuid', '!=', $profile->uuid);
                }
            })
            ->get();

        $subResellers = $subProfiles->map(function ($p) {
            $d = Domain::where('domain_uuid', $p->domain_uuid)->first();
            $extCount = DB::table('v_extensions')->where('domain_uuid', $p->domain_uuid)->count();
            $didCount = DB::table('v_destinations')->where('domain_uuid', $p->domain_uuid)->count();
            $userCount = DB::table('v_users')->where('domain_uuid', $p->domain_uuid)->count();

            return [
                'uuid' => $p->uuid,
                'domain_uuid' => $p->domain_uuid,
                'domain_name' => $d?->domain_name ?? 'N/A',
                'company_name' => $p->company_name,
                'custom_domain_hostname' => $p->custom_domain_hostname,
                'reseller_tier' => $p->reseller_tier,
                'allocated_extensions' => (int) $p->allocated_extensions,
                'used_extensions' => (int) $extCount,
                'allocated_did_numbers' => (int) $p->allocated_did_numbers,
                'used_dids' => (int) $didCount,
                'used_users' => (int) $userCount,
                'credit_balance' => (float) $p->credit_balance,
                'markup_pct' => (float) $p->markup_pct,
                'is_active' => (bool) $p->is_active,
                'smtp_enabled' => (bool) $p->smtp_enabled,
                'logo_url' => $p->logo_url,
                'created_at' => $p->created_at?->format('M d, Y'),
            ];
        });

        // Overview statistics
        $stats = [
            'total_tenants' => $subProfiles->count(),
            'sub_resellers' => $subProfiles->where('reseller_tier', 'sub_reseller')->count(),
            'client_companies' => $subProfiles->where('reseller_tier', 'client_tenant')->count(),
            'total_allocated_ext' => $subProfiles->sum('allocated_extensions') + (int) $profile->allocated_extensions,
            'total_used_ext' => DB::table('v_extensions')->count(),
            'total_allocated_dids' => $subProfiles->sum('allocated_did_numbers') + (int) $profile->allocated_did_numbers,
            'total_used_dids' => DB::table('v_destinations')->count(),
            'total_reseller_balance' => $subProfiles->sum('credit_balance'),
        ];

        // All domains for switcher
        $domains = Domain::select('domain_uuid', 'domain_name', 'domain_description')->get();

        return Inertia::render('Reseller/WhiteLabelStudio', [
            'profile' => $profile,
            'subResellers' => $subResellers,
            'stats' => $stats,
            'domains' => $domains,
            'currentDomainUuid' => $domainUuid,
            'routes' => [
                'update_profile' => route('reseller.whitelabel.update-profile'),
                'upload_asset' => route('reseller.whitelabel.upload-asset'),
                'update_smtp' => route('reseller.whitelabel.update-smtp'),
                'test_smtp' => route('reseller.whitelabel.test-smtp'),
                'create_tenant' => route('reseller.whitelabel.create-tenant'),
                'update_tenant' => route('reseller.whitelabel.update-tenant', ['uuid' => '__UUID__']),
                'delete_tenant' => route('reseller.whitelabel.delete-tenant', ['uuid' => '__UUID__']),
            ],
        ]);
    }

    /**
     * Update brand identity, login design, and SMS settings.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid',
            'company_name' => 'required|string|max:120',
            'custom_domain_hostname' => 'nullable|string|max:255',
            'logo_url' => 'nullable|string',
            'favicon_url' => 'nullable|string',
            'login_heading' => 'nullable|string|max:120',
            'login_subheading' => 'nullable|string|max:255',
            'login_accent_color' => 'nullable|string|max:16',
            'login_bg_image_url' => 'nullable|string',
            'support_email' => 'nullable|email|max:120',
            'support_phone' => 'nullable|string|max:64',
            'copyright_text' => 'nullable|string|max:255',
            'terms_url' => 'nullable|string|max:255',
            'privacy_url' => 'nullable|string|max:255',
            'sms_sender_id' => 'nullable|string|max:32',
            'sms_footer_text' => 'nullable|string|max:160',
            'markup_pct' => 'nullable|numeric|min:0|max:1000',
        ]);

        $profile = ResellerWhitelabelProfile::findOrFail($validated['uuid']);
        $profile->update($validated);

        return back()->with('message', 'White-Label brand identity and login design successfully updated.');
    }

    /**
     * Upload brand image asset (logo, favicon, login background).
     */
    public function uploadAsset(Request $request): JsonResponse
    {
        $request->validate([
            'asset' => 'required|image|mimes:jpeg,png,jpg,gif,svg,ico,webp|max:5120',
            'type' => 'required|string|in:logo,favicon,login_bg',
            'profile_uuid' => 'nullable|uuid',
        ]);

        try {
            $file = $request->file('asset');
            $extension = $file->getClientOriginalExtension();
            $type = $request->input('type');
            $filename = $type . '_' . Str::random(12) . '.' . $extension;

            // Ensure destination directory exists
            $targetDir = public_path('storage/whitelabel');
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0755, true);
            }

            $file->move($targetDir, $filename);
            $url = '/storage/whitelabel/' . $filename;

            // Update profile if uuid provided
            if ($request->filled('profile_uuid')) {
                $profile = ResellerWhitelabelProfile::find($request->input('profile_uuid'));
                if ($profile) {
                    if ($type === 'logo') $profile->logo_url = $url;
                    elseif ($type === 'favicon') $profile->favicon_url = $url;
                    elseif ($type === 'login_bg') $profile->login_bg_image_url = $url;
                    $profile->save();
                }
            }

            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => ucfirst($type) . ' uploaded successfully.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update dedicated white-label SMTP settings.
     */
    public function updateSmtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'uuid' => 'required|uuid',
            'smtp_enabled' => 'required|boolean',
            'smtp_host' => 'nullable|string|max:190',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:190',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string|in:tls,ssl,none',
            'smtp_from_address' => 'nullable|email|max:190',
            'smtp_from_name' => 'nullable|string|max:190',
        ]);

        $profile = ResellerWhitelabelProfile::findOrFail($validated['uuid']);

        // Don't overwrite existing password if left blank in request
        if (empty($validated['smtp_password'])) {
            unset($validated['smtp_password']);
        }

        $profile->update($validated);

        return back()->with('message', 'White-Label SMTP mail server settings saved successfully.');
    }

    /**
     * Run live diagnostic test for SMTP server.
     */
    public function testSmtp(Request $request): JsonResponse
    {
        $request->validate([
            'test_email' => 'required|email',
            'uuid' => 'nullable|uuid',
            'smtp_host' => 'nullable|string',
            'smtp_port' => 'nullable|integer',
            'smtp_username' => 'nullable|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'nullable|string',
            'smtp_from_address' => 'nullable|email',
            'smtp_from_name' => 'nullable|string',
        ]);

        $profile = null;
        if ($request->filled('uuid')) {
            $profile = ResellerWhitelabelProfile::find($request->input('uuid'));
        }

        $config = [
            'host' => $request->input('smtp_host', $profile?->smtp_host),
            'port' => (int) $request->input('smtp_port', $profile?->smtp_port ?: 587),
            'username' => $request->input('smtp_username', $profile?->smtp_username),
            'password' => $request->input('smtp_password', $profile?->smtp_password),
            'encryption' => $request->input('smtp_encryption', $profile?->smtp_encryption ?: 'tls'),
            'from_address' => $request->input('smtp_from_address', $profile?->smtp_from_address),
            'from_name' => $request->input('smtp_from_name', $profile?->smtp_from_name ?: $profile?->company_name),
        ];

        if (empty($config['host']) || empty($config['from_address'])) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide an SMTP Host and From Email Address.',
            ], 422);
        }

        $result = $this->whiteLabelService->testSmtpConnection($config, $request->input('test_email'));

        return response()->json($result);
    }

    /**
     * Create a new Sub-Reseller or Client Company Tenant.
     */
    public function createTenant(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'parent_uuid' => 'required|uuid',
            'company_name' => 'required|string|max:120',
            'domain_name' => 'required|string|max:120|unique:v_domains,domain_name',
            'custom_domain_hostname' => 'nullable|string|max:120|unique:reseller_whitelabel_profiles,custom_domain_hostname',
            'reseller_tier' => 'required|string|in:sub_reseller,client_tenant',
            'allocated_extensions' => 'required|integer|min:1',
            'allocated_did_numbers' => 'required|integer|min:0',
            'credit_balance' => 'required|numeric|min:0',
            'markup_pct' => 'required|numeric|min:0|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $parentProfile = ResellerWhitelabelProfile::findOrFail($validated['parent_uuid']);

            // 1. Create domain in v_domains
            $domain = new Domain();
            $domain->domain_uuid = (string) Str::uuid();
            $domain->domain_parent_uuid = $parentProfile->domain_uuid;
            $domain->domain_name = strtolower(trim($validated['domain_name']));
            $domain->domain_description = $validated['company_name'];
            $domain->domain_enabled = true;
            $domain->save();

            // 2. Create reseller whitelabel profile
            $childProfile = new ResellerWhitelabelProfile();
            $childProfile->uuid = (string) Str::uuid();
            $childProfile->domain_uuid = $domain->domain_uuid;
            $childProfile->parent_reseller_uuid = $parentProfile->uuid;
            $childProfile->reseller_tier = $validated['reseller_tier'];
            $childProfile->company_name = $validated['company_name'];
            $childProfile->custom_domain_hostname = $validated['custom_domain_hostname'] ?: $domain->domain_name;
            // Inherit parent assets by default
            $childProfile->logo_url = $parentProfile->logo_url;
            $childProfile->favicon_url = $parentProfile->favicon_url;
            $childProfile->login_heading = 'Sign in to ' . $validated['company_name'];
            $childProfile->login_subheading = 'Enterprise Cloud Telephony';
            $childProfile->login_accent_color = $parentProfile->login_accent_color;
            $childProfile->support_email = 'support@' . $domain->domain_name;
            $childProfile->support_phone = $parentProfile->support_phone;
            $childProfile->copyright_text = '© ' . date('Y') . ' ' . $validated['company_name'] . '. All rights reserved.';
            $childProfile->sms_sender_id = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $validated['company_name']), 0, 11));
            $childProfile->allocated_extensions = (int) $validated['allocated_extensions'];
            $childProfile->allocated_did_numbers = (int) $validated['allocated_did_numbers'];
            $childProfile->credit_balance = (float) $validated['credit_balance'];
            $childProfile->markup_pct = (float) $validated['markup_pct'];
            $childProfile->is_active = true;
            $childProfile->save();

            DB::commit();

            return back()->with('message', 'New ' . ($validated['reseller_tier'] === 'sub_reseller' ? 'Sub-Reseller' : 'Company Tenant') . ' successfully created.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create tenant: ' . $e->getMessage()]);
        }
    }

    /**
     * Update tenant quota, credit balance, markup % or status.
     */
    public function updateTenant(Request $request, string $uuid): RedirectResponse
    {
        $validated = $request->validate([
            'allocated_extensions' => 'required|integer|min:0',
            'allocated_did_numbers' => 'required|integer|min:0',
            'credit_balance' => 'required|numeric|min:0',
            'markup_pct' => 'required|numeric|min:0|max:1000',
            'is_active' => 'required|boolean',
        ]);

        $profile = ResellerWhitelabelProfile::findOrFail($uuid);
        $profile->update($validated);

        return back()->with('message', 'Tenant quota and commercial terms successfully updated.');
    }

    /**
     * Deactivate or delete tenant.
     */
    public function deleteTenant(string $uuid): RedirectResponse
    {
        $profile = ResellerWhitelabelProfile::findOrFail($uuid);
        $profile->is_active = false;
        $profile->save();

        if ($profile->domain) {
            $profile->domain->domain_enabled = false;
            $profile->domain->save();
        }

        return back()->with('message', 'Tenant account deactivated successfully.');
    }
}
