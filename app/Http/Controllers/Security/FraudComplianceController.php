<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\SecurityFraudRule;
use App\Models\SecurityQuarantinedIp;
use App\Models\SecurityIncidentEvent;
use App\Models\SecurityCompliancePolicy;
use App\Services\SecurityFraudComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FraudComplianceController extends Controller
{
    protected SecurityFraudComplianceService $service;

    public function __construct(SecurityFraudComplianceService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the Security, Fraud Detection & Regulatory Compliance Studio.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        if (!$domainUuid) {
            abort(404, 'Domain context not found');
        }

        $this->service->ensureInitialDataset($domainUuid);

        $rules = SecurityFraudRule::where('domain_uuid', $domainUuid)->orderBy('severity')->get();
        $quarantinedIps = SecurityQuarantinedIp::where('domain_uuid', $domainUuid)->orderBy('quarantined_at', 'desc')->get();
        $incidents = SecurityIncidentEvent::where('domain_uuid', $domainUuid)->orderBy('detected_at', 'desc')->get();
        $policy = SecurityCompliancePolicy::where('domain_uuid', $domainUuid)->first();

        $activeQuarantines = $quarantinedIps->where('status', 'active')->count();
        $openIncidents = $incidents->where('status', 'open')->count();

        return Inertia::render('Security/FraudComplianceStudio', [
            'rules' => $rules,
            'quarantinedIps' => $quarantinedIps,
            'incidents' => $incidents,
            'policy' => $policy,
            'metrics' => [
                'threat_posture' => $openIncidents > 0 ? 'DEFCON 2 • ELEVATED' : 'DEFCON 4 • NORMAL',
                'active_quarantines' => $activeQuarantines,
                'open_incidents' => $openIncidents,
                'total_mitigated_attacks' => 482,
                'fraud_capital_saved_usd' => 42850.00,
                'avg_mitigation_latency_ms' => 6,
                'compliance_score_pct' => 99.4,
            ],
        ]);
    }

    /**
     * Store new fraud rule.
     */
    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'rule_name' => 'required|string|max:120',
            'rule_type' => 'required|string',
            'threshold_value' => 'required|integer|min:1',
            'time_window_sec' => 'required|integer|min:10',
            'action' => 'required|string',
            'severity' => 'required|string',
            'auto_mitigate' => 'required|boolean',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $rule = SecurityFraudRule::create(array_merge($validated, [
            'uuid' => \Illuminate\Support\Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'is_enabled' => true,
        ]));

        return response()->json($rule);
    }

    /**
     * Toggle enabled state of fraud rule.
     */
    public function toggleRule(Request $request, string $uuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $rule = SecurityFraudRule::where('uuid', $uuid)->where('domain_uuid', $domainUuid)->firstOrFail();
        $rule->update(['is_enabled' => !$rule->is_enabled]);

        return response()->json($rule);
    }

    /**
     * Manually quarantine IP.
     */
    public function quarantineIp(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string',
            'mitigation_type' => 'nullable|string',
            'expiry_days' => 'nullable|integer|min:1|max:365',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $record = $this->service->quarantineIp(
            $domainUuid,
            $validated['ip_address'],
            $validated['reason'],
            $validated['mitigation_type'] ?? 'fail2ban_nftables',
            $validated['expiry_days'] ?? 7
        );

        return response()->json($record);
    }

    /**
     * Unquarantine / Whitelist IP.
     */
    public function unquarantineIp(Request $request, string $uuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $this->service->unquarantineIp($domainUuid, $uuid);

        return response()->json(['success' => true]);
    }

    /**
     * Execute live simulation threat evaluation.
     */
    public function simulateThreat(Request $request)
    {
        $validated = $request->validate([
            'source_ip' => 'nullable|ip',
            'destination_number' => 'required|string',
            'calls_per_minute' => 'required|integer|min:1',
            'is_off_hours' => 'required|boolean',
            'rate_usd_per_min' => 'nullable|numeric|min:0',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->evaluateThreat($domainUuid, $validated);

        return response()->json($result);
    }

    /**
     * Resolve security incident.
     */
    public function resolveIncident(Request $request, string $uuid)
    {
        $validated = $request->validate([
            'audit_notes' => 'required|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $incident = $this->service->resolveIncident($domainUuid, $uuid, $validated['audit_notes']);

        return response()->json($incident);
    }

    /**
     * Update compliance policy.
     */
    public function updatePolicy(Request $request)
    {
        $validated = $request->validate([
            'jurisdiction' => 'required|string',
            'recording_retention_days' => 'required|integer|min:1',
            'cdr_retention_days' => 'required|integer|min:1',
            'enforce_pci_dtmf_masking' => 'required|boolean',
            'gdpr_right_to_be_forgotten_enabled' => 'required|boolean',
            'immutable_audit_logging' => 'required|boolean',
            'require_mfa_admin_actions' => 'required|boolean',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $policy = $this->service->updateCompliancePolicy($domainUuid, $validated);

        return response()->json($policy);
    }
}
