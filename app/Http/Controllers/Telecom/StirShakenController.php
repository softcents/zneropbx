<?php

namespace App\Http\Controllers\Telecom;

use App\Http\Controllers\Controller;
use App\Services\Telecom\StirShakenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StirShakenController extends Controller
{
    protected StirShakenService $service;

    public function __construct(StirShakenService $service)
    {
        $this->service = $service;
    }

    /**
     * Display the STIR/SHAKEN Caller Identity & Attestation Studio.
     */
    public function index(Request $request)
    {
        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        if (!$domainUuid) {
            abort(404, 'Domain context not found');
        }

        $this->service->ensureInitialDataset($domainUuid);
        $telemetry = $this->service->getAuditTelemetry($domainUuid);

        return Inertia::render('Telecom/StirShakenStudio', [
            'certificates' => $telemetry['certificates'],
            'recentLogs' => $telemetry['recent_logs'],
            'metrics' => $telemetry['metrics'],
        ]);
    }

    /**
     * Generate a new EC P-256 X.509 Certificate in the vault.
     */
    public function generateCertificate(Request $request)
    {
        $validated = $request->validate([
            'cert_name' => 'required|string|max:128',
            'x5u_url' => 'required|url|max:255',
            'spc_code' => 'required|string|max:16',
            'tn_ranges' => 'nullable|array',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $cert = $this->service->generateCertificate(
            $domainUuid,
            $validated['cert_name'],
            $validated['x5u_url'],
            $validated['spc_code'],
            $validated['tn_ranges'] ?? []
        );

        return response()->json([
            'success' => true,
            'certificate' => $cert,
            'message' => 'New STIR/SHAKEN EC P-256 certificate generated successfully.',
        ]);
    }

    /**
     * Simulate PASSporT signing and SIP Identity header generation.
     */
    public function simulateSigning(Request $request)
    {
        $validated = $request->validate([
            'caller_id' => 'required|string|max:32',
            'destination_number' => 'required|string|max:32',
            'attestation' => 'nullable|string|in:A,B,C',
        ]);

        $domainUuid = session('domain_uuid') ?? DB::table('v_domains')->value('domain_uuid');
        $res = $this->service->generatePassport(
            $validated['caller_id'],
            $validated['destination_number'],
            $validated['attestation'] ?? null,
            $domainUuid
        );

        return response()->json($res);
    }

    /**
     * Verify a SIP Identity header or PASSporT JWT.
     */
    public function verifyPassport(Request $request)
    {
        $validated = $request->validate([
            'identity_header' => 'required|string',
        ]);

        $res = $this->service->verifyIdentityHeader($validated['identity_header']);

        return response()->json($res);
    }
}
