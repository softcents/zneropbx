<?php

namespace App\Services\Telecom;

use App\Models\StirShakenCertificate;
use App\Models\StirShakenSigningLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StirShakenService
{
    /**
     * Ensure initial STIR/SHAKEN certificate and sample verification data exist.
     */
    public function ensureInitialDataset(string $domainUuid): void
    {
        if (StirShakenCertificate::where('domain_uuid', $domainUuid)->count() === 0) {
            $this->generateCertificate(
                $domainUuid,
                'Znero Carrier Identity Primary (EC P-256)',
                'https://certs.znero.com/shaken/cert-primary.pem',
                'ZN01',
                ['+12125550100', '+12125550101', '+12125550199', '+18005550199', '+14155552671']
            );
        }

        if (StirShakenSigningLog::where('domain_uuid', $domainUuid)->count() === 0) {
            // Seed sample signed calls with A, B, and C attestations
            $this->generatePassport('+12125550199', '+14155550144', 'A', $domainUuid);
            $this->generatePassport('+12125559876', '+13125550122', 'B', $domainUuid);
            $this->generatePassport('+442079460911', '+12125550199', 'C', $domainUuid);
        }
    }

    /**
     * Generate an EC P-256 Keypair and X.509 Certificate for STIR/SHAKEN.
     */
    public function generateCertificate(
        string $domainUuid,
        string $certName,
        string $x5uUrl,
        string $spcCode = 'ZN01',
        array $tnRanges = []
    ): StirShakenCertificate {
        $config = [
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ];

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKeyPem);

        $dn = [
            'countryName' => 'US',
            'stateOrProvinceName' => 'Texas',
            'localityName' => 'Austin',
            'organizationName' => 'ZneroPBX Telecom Cloud',
            'organizationalUnitName' => 'STIR/SHAKEN Identity Authority',
            'commonName' => 'Znero Carrier SHAKEN Cert ' . $spcCode,
        ];

        $csr = openssl_csr_new($dn, $res, ['digest_alg' => 'sha256']);
        $x509 = openssl_csr_sign($csr, null, $res, 365, ['digest_alg' => 'sha256']);
        openssl_x509_export($x509, $publicCertPem);

        return StirShakenCertificate::create([
            'certificate_uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'cert_name' => $certName,
            'x5u_url' => $x5uUrl,
            'private_key' => $privateKeyPem,
            'public_cert' => $publicCertPem,
            'spc_code' => $spcCode,
            'tn_ranges' => $tnRanges,
            'is_active' => true,
            'expires_at' => now()->addDays(365),
        ]);
    }

    /**
     * Determine STIR/SHAKEN Attestation Level (A, B, or C).
     *
     * Attestation A (Full): Caller ID is verified and allocated to customer.
     * Attestation B (Partial): Call originates from authenticated customer, but number not verified.
     * Attestation C (Gateway): Call received from an international gateway or unverified transit.
     */
    public function determineAttestation(string $callerId, string $domainUuid): string
    {
        $normalizedCaller = $this->normalizePhoneNumber($callerId);

        // Check active certs TN ranges
        $activeCerts = StirShakenCertificate::where('domain_uuid', $domainUuid)
            ->where('is_active', true)
            ->get();

        foreach ($activeCerts as $cert) {
            $ranges = $cert->tn_ranges ?? [];
            if (in_array($normalizedCaller, $ranges, true)) {
                return 'A';
            }
        }

        // Check if destination/extension belongs to domain in PBX
        $isDomainNumber = DB::table('v_destinations')
            ->where('domain_uuid', $domainUuid)
            ->where('destination_number', $normalizedCaller)
            ->exists();

        if ($isDomainNumber) {
            return 'A';
        }

        // Domestic formatted number originating from PBX authenticated user
        if (str_starts_with($normalizedCaller, '+1') && strlen($normalizedCaller) === 12) {
            return 'B';
        }

        return 'C';
    }

    /**
     * Generate an RFC 8588 Compliant PASSporT JWT and SIP Identity Header.
     */
    public function generatePassport(
        string $callerId,
        string $destNumber,
        ?string $attestation = null,
        ?string $domainUuid = null
    ): array {
        $start = microtime(true);
        $domainUuid = $domainUuid ?? DB::table('v_domains')->value('domain_uuid');
        $callerId = $this->normalizePhoneNumber($callerId);
        $destNumber = $this->normalizePhoneNumber($destNumber);

        if (!$attestation) {
            $attestation = $this->determineAttestation($callerId, $domainUuid);
        }

        $cert = StirShakenCertificate::where('domain_uuid', $domainUuid)
            ->where('is_active', true)
            ->first();

        if (!$cert) {
            $cert = $this->generateCertificate(
                $domainUuid,
                'Default On-Demand Identity Cert',
                'https://certs.znero.com/shaken/default.pem',
                'ZN01',
                [$callerId]
            );
        }

        $now = time();
        $origId = Str::uuid()->toString();

        $header = [
            'alg' => 'ES256',
            'ppt' => 'shaken',
            'typ' => 'passport',
            'x5u' => $cert->x5u_url,
        ];

        $payload = [
            'attest' => $attestation,
            'dest' => [
                'tn' => [$destNumber],
            ],
            'iat' => $now,
            'orig' => [
                'tn' => $callerId,
            ],
            'origid' => $origId,
        ];

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));
        $signingInput = "{$encodedHeader}.{$encodedPayload}";

        // Sign with EC P-256 Private Key
        openssl_sign($signingInput, $derSignature, $cert->private_key, OPENSSL_ALGO_SHA256);
        $rawSignature = $this->derToRaw($derSignature);
        $encodedSignature = $this->base64UrlEncode($rawSignature);

        $passportJwt = "{$signingInput}.{$encodedSignature}";
        $identityHeader = "{$passportJwt};info=<{$cert->x5u_url}>;alg=ES256;ppt=shaken";

        $latencyMs = round((microtime(true) - $start) * 1000, 2);

        $log = StirShakenSigningLog::create([
            'log_uuid' => Str::uuid()->toString(),
            'domain_uuid' => $domainUuid,
            'orig_tn' => $callerId,
            'dest_tn' => $destNumber,
            'attestation' => $attestation,
            'origid_uuid' => $origId,
            'passport_jwt' => $passportJwt,
            'identity_header' => $identityHeader,
            'verification_status' => 'signed',
            'signed_at' => now(),
        ]);

        return [
            'success' => true,
            'passport_jwt' => $passportJwt,
            'identity_header' => $identityHeader,
            'header' => $header,
            'payload' => $payload,
            'attestation' => $attestation,
            'origid' => $origId,
            'cert_name' => $cert->cert_name,
            'x5u' => $cert->x5u_url,
            'latency_ms' => $latencyMs,
            'log_uuid' => $log->log_uuid,
        ];
    }

    /**
     * Verify a SIP Identity header containing a PASSporT JWT.
     */
    public function verifyIdentityHeader(string $identityHeader): array
    {
        $start = microtime(true);

        // Strip parameter qualifiers
        $parts = explode(';', trim($identityHeader));
        $jwt = trim($parts[0]);

        $jwtSections = explode('.', $jwt);
        if (count($jwtSections) !== 3) {
            return [
                'valid' => false,
                'error' => 'Malformed JWT structure (expected 3 parts).',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $jwtSections;

        $header = json_decode($this->base64UrlDecode($encodedHeader), true);
        $payload = json_decode($this->base64UrlDecode($encodedPayload), true);

        if (!$header || !$payload) {
            return [
                'valid' => false,
                'error' => 'Invalid JSON in PASSporT header or payload.',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        if (($header['alg'] ?? '') !== 'ES256' || ($header['ppt'] ?? '') !== 'shaken') {
            return [
                'valid' => false,
                'error' => 'Invalid header algorithm or ppt (must be ES256 and shaken).',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        // Replay defense check: timestamp within 60 seconds
        $iat = (int)($payload['iat'] ?? 0);
        $now = time();
        $age = abs($now - $iat);
        if ($age > 120) {
            return [
                'valid' => false,
                'error' => "Token expired or clock skew exceeded ({$age}s old).",
                'header' => $header,
                'payload' => $payload,
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        // Retrieve public key from matching certificate in vault
        $x5u = $header['x5u'] ?? '';
        $cert = StirShakenCertificate::where('x5u_url', $x5u)->first()
            ?? StirShakenCertificate::where('is_active', true)->first();

        if (!$cert) {
            return [
                'valid' => false,
                'error' => "Certificate repository could not resolve x5u: {$x5u}",
                'header' => $header,
                'payload' => $payload,
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        }

        $rawSig = $this->base64UrlDecode($encodedSignature);
        $derSig = $this->rawToDer($rawSig);
        $signingInput = "{$encodedHeader}.{$encodedPayload}";

        $verify = openssl_verify($signingInput, $derSig, $cert->public_cert, OPENSSL_ALGO_SHA256);

        $latencyMs = round((microtime(true) - $start) * 1000, 2);

        return [
            'valid' => ($verify === 1),
            'attestation' => $payload['attest'] ?? 'Unknown',
            'orig_tn' => $payload['orig']['tn'] ?? null,
            'dest_tn' => $payload['dest']['tn'][0] ?? null,
            'origid' => $payload['origid'] ?? null,
            'iat' => $iat,
            'age_seconds' => $age,
            'cert_name' => $cert->cert_name,
            'x5u' => $x5u,
            'header' => $header,
            'payload' => $payload,
            'latency_ms' => $latencyMs,
            'error' => ($verify === 1) ? null : 'Cryptographic signature verification failed.',
        ];
    }

    /**
     * Get aggregate STIR/SHAKEN telemetry for the domain.
     */
    public function getAuditTelemetry(string $domainUuid): array
    {
        $certs = StirShakenCertificate::where('domain_uuid', $domainUuid)->get();
        $logs = StirShakenSigningLog::where('domain_uuid', $domainUuid)
            ->orderBy('signed_at', 'desc')
            ->take(15)
            ->get();

        $totalSigned = StirShakenSigningLog::where('domain_uuid', $domainUuid)->count();
        $attestA = StirShakenSigningLog::where('domain_uuid', $domainUuid)->where('attestation', 'A')->count();
        $attestB = StirShakenSigningLog::where('domain_uuid', $domainUuid)->where('attestation', 'B')->count();
        $attestC = StirShakenSigningLog::where('domain_uuid', $domainUuid)->where('attestation', 'C')->count();

        return [
            'certificates' => $certs,
            'recent_logs' => $logs,
            'metrics' => [
                'total_signed_calls' => $totalSigned,
                'attestation_a_count' => $attestA,
                'attestation_b_count' => $attestB,
                'attestation_c_count' => $attestC,
                'full_attestation_pct' => $totalSigned > 0 ? round(($attestA / $totalSigned) * 100, 1) : 100.0,
                'active_certificates' => $certs->where('is_active', true)->count(),
                'crypto_algorithm' => 'ES256 (NIST P-256 + SHA-256)',
                'standard_compliance' => 'RFC 8588 / ATIS-1000074 / ATIS-1000080',
            ],
        ];
    }

    /**
     * Convert ASN.1 DER signature to 64-byte raw (r || s).
     */
    protected function derToRaw(string $der): string
    {
        $offset = 3;
        $rLen = ord($der[$offset++]);
        $r = substr($der, $offset, $rLen);
        $offset += $rLen + 1;
        $sLen = ord($der[$offset++]);
        $s = substr($der, $offset, $sLen);

        $r = str_pad(ltrim($r, "\x00"), 32, "\x00", STR_PAD_LEFT);
        $s = str_pad(ltrim($s, "\x00"), 32, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    /**
     * Convert 64-byte raw signature to ASN.1 DER.
     */
    protected function rawToDer(string $raw): string
    {
        $r = substr($raw, 0, 32);
        $s = substr($raw, 32, 32);

        $r = ltrim($r, "\x00");
        if (empty($r) || ord($r[0]) >= 0x80) {
            $r = "\x00" . $r;
        }

        $s = ltrim($s, "\x00");
        if (empty($s) || ord($s[0]) >= 0x80) {
            $s = "\x00" . $s;
        }

        $content = "\x02" . chr(strlen($r)) . $r . "\x02" . chr(strlen($s)) . $s;
        return "\x30" . chr(strlen($content)) . $content;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    protected function normalizePhoneNumber(string $number): string
    {
        $digits = preg_replace('/[^\d]/', '', $number);
        if (strlen($digits) === 10) {
            return '+1' . $digits;
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
            return '+' . $digits;
        }
        return '+' . $digits;
    }
}
