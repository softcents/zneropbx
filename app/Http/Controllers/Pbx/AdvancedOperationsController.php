<?php

namespace App\Http\Controllers\Pbx;

use App\Http\Controllers\Controller;
use App\Models\PbxEmergencyLocation;
use App\Models\PbxCallerIdRule;
use App\Models\PbxCallPermissionProfile;
use App\Models\PbxRecordingRetentionRule;
use App\Models\PbxRecordingLegalHold;
use App\Models\PbxDeviceFleet;
use App\Services\PbxAdvancedOperationsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdvancedOperationsController extends Controller
{
    protected PbxAdvancedOperationsService $service;

    public function __construct(PbxAdvancedOperationsService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the PBX Advanced Operations & Fleet Governance Dashboard.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        if (!$domainUuid) {
            abort(404, 'Domain context not found');
        }

        $this->service->ensureInitialDataset($domainUuid);

        $emergencyLocations = PbxEmergencyLocation::where('domain_uuid', $domainUuid)->get();
        $callerIdRules = PbxCallerIdRule::where('domain_uuid', $domainUuid)->orderBy('priority', 'asc')->get();
        $permissionProfiles = PbxCallPermissionProfile::where('domain_uuid', $domainUuid)->get();
        $retentionRules = PbxRecordingRetentionRule::where('domain_uuid', $domainUuid)->get();
        $legalHolds = PbxRecordingLegalHold::where('domain_uuid', $domainUuid)->orderBy('created_at', 'desc')->get();
        $deviceFleet = PbxDeviceFleet::where('domain_uuid', $domainUuid)->orderBy('vendor')->get();

        $extensions = DB::table('v_extensions')
            ->where('domain_uuid', $domainUuid)
            ->select('extension', 'description', 'effective_caller_id_name', 'outbound_caller_id_number', 'emergency_caller_id_number')
            ->orderBy('extension')
            ->get();

        // Fleet telemetry metrics
        $totalDevices = $deviceFleet->count();
        $onlineDevices = $deviceFleet->where('sip_status', 'registered')->count();
        $offlineDevices = $totalDevices - $onlineDevices;
        $avgRtt = $onlineDevices > 0 ? round($deviceFleet->where('sip_status', 'registered')->avg('rtt_ms')) : 0;
        $webrtcCount = $deviceFleet->where('transport', 'WSS')->count();

        // Legal & Compliance Metrics
        $activeHoldsCount = $legalHolds->where('status', 'active')->count();
        $allKarisCompliant = $emergencyLocations->every(fn($loc) => $loc->karis_law_compliant);
        $allRayBaumsCompliant = $emergencyLocations->every(fn($loc) => $loc->ray_baums_compliant);

        return Inertia::render('Pbx/AdvancedOperations', [
            'emergencyLocations' => $emergencyLocations,
            'callerIdRules' => $callerIdRules,
            'permissionProfiles' => $permissionProfiles,
            'retentionRules' => $retentionRules,
            'legalHolds' => $legalHolds,
            'deviceFleet' => $deviceFleet,
            'extensions' => $extensions,
            'metrics' => [
                'total_devices' => $totalDevices,
                'online_devices' => $onlineDevices,
                'offline_devices' => $offlineDevices,
                'avg_rtt_ms' => $avgRtt,
                'webrtc_count' => $webrtcCount,
                'active_legal_holds' => $activeHoldsCount,
                'karis_law_compliant' => $allKarisCompliant,
                'ray_baums_compliant' => $allRayBaumsCompliant,
            ],
        ]);
    }

    /**
     * Simulate E911 Emergency Dispatch.
     */
    public function testEmergencyDispatch(Request $request)
    {
        $validated = $request->validate([
            'location_uuid' => 'required|uuid',
            'caller_extension' => 'required|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->simulateEmergencyDispatch(
            $validated['location_uuid'],
            $validated['caller_extension'],
            $domainUuid
        );

        return response()->json($result);
    }

    /**
     * Simulate Caller-ID policy matching and normalization regex.
     */
    public function simulateCallerId(Request $request)
    {
        $validated = $request->validate([
            'dialed_number' => 'required|string',
            'extension' => 'nullable|string',
            'department' => 'nullable|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->simulateCallerIdNormalization(
            $validated['dialed_number'],
            $validated['extension'] ?? null,
            $validated['department'] ?? null,
            $domainUuid
        );

        return response()->json($result);
    }

    /**
     * Evaluate call permission / Class of Service barring rules.
     */
    public function evaluateCallPermission(Request $request)
    {
        $validated = $request->validate([
            'extension' => 'required|string',
            'destination' => 'required|string',
            'time_string' => 'nullable|string',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->evaluateCallPermission(
            $validated['extension'],
            $validated['destination'],
            $domainUuid,
            $validated['time_string'] ?? null
        );

        return response()->json($result);
    }

    /**
     * Toggle recording legal hold status.
     */
    public function toggleLegalHold(Request $request, string $holdUuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->toggleLegalHold($holdUuid, $domainUuid);

        return response()->json($result);
    }

    /**
     * Remote reboot fleet endpoint via SIP NOTIFY check-sync.
     */
    public function rebootDevice(Request $request, string $deviceUuid)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $result = $this->service->rebootDevice($deviceUuid, $domainUuid);

        return response()->json($result);
    }
}
