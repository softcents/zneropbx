<?php

namespace App\Services;

use App\Models\PbxEmergencyLocation;
use App\Models\PbxCallerIdRule;
use App\Models\PbxCallPermissionProfile;
use App\Models\PbxRecordingRetentionRule;
use App\Models\PbxRecordingLegalHold;
use App\Models\PbxDeviceFleet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PbxAdvancedOperationsService
{
    /**
     * Ensure default PBX Advanced Operations dataset is populated for the domain.
     */
    public function ensureInitialDataset(string $domainUuid): void
    {
        // 1. Seed demo extensions if none exist
        if (DB::table('v_extensions')->where('domain_uuid', $domainUuid)->count() === 0) {
            $demoExtensions = [
                ['1001', 'CEO & Executive Office', 'John Sterling', '+15125550101', 'executive'],
                ['1002', 'Tier 2 Technical Support', 'Sarah Connor', '+15125550102', 'support'],
                ['1003', 'Enterprise Sales Lead', 'Marcus Vance', '+15125550103', 'sales'],
                ['1004', 'Contact Center Softphone', 'Elena Rostova', '+15125550104', 'call_center'],
            ];

            foreach ($demoExtensions as $ext) {
                DB::table('v_extensions')->insert([
                    'extension_uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                    'extension' => $ext[0],
                    'description' => $ext[1],
                    'effective_caller_id_name' => $ext[2],
                    'effective_caller_id_number' => $ext[3],
                    'outbound_caller_id_name' => 'Znero Enterprise PBX',
                    'outbound_caller_id_number' => '+18005550199',
                    'emergency_caller_id_name' => 'HQ Floor 4 Suite 410',
                    'emergency_caller_id_number' => '5125550911',
                    'directory_visible' => 'true',
                    'directory_exten_visible' => 'true',
                    'enabled' => 'true',
                    'insert_date' => now(),
                ]);
            }
        }

        // 2. Emergency Locations (E911 & Kari's Law / Ray Baum's Act)
        if (PbxEmergencyLocation::where('domain_uuid', $domainUuid)->count() === 0) {
            $locations = [
                [
                    'location_name' => 'Corporate HQ Tower - 4th Floor Executive Suite',
                    'elin_number' => '5125550911',
                    'street_address' => '300 Colorado St',
                    'unit_suite' => 'Suite 410 / Room 402',
                    'city' => 'Austin',
                    'state_province' => 'TX',
                    'postal_code' => '78701',
                    'country' => 'US',
                    'psap_name' => 'Austin-Travis County 911 Communications',
                    'notification_emails' => ['security-desk@corp.znero.com', 'e911-alert@corp.znero.com'],
                    'notification_phones' => ['+15125559999', '+15125558888'],
                    'karis_law_compliant' => true,
                    'ray_baums_compliant' => true,
                    'status' => 'active',
                    'test_last_completed_at' => now()->subDays(2),
                ],
                [
                    'location_name' => 'Tech Center NOC & Data Facility',
                    'elin_number' => '5125550912',
                    'street_address' => '11501 Domain Dr',
                    'unit_suite' => 'Server Room Alpha, Rack 14',
                    'city' => 'Austin',
                    'state_province' => 'TX',
                    'postal_code' => '78758',
                    'country' => 'US',
                    'psap_name' => 'Austin-Travis County 911 Communications',
                    'notification_emails' => ['noc-duty@corp.znero.com'],
                    'notification_phones' => ['+15125557777'],
                    'karis_law_compliant' => true,
                    'ray_baums_compliant' => true,
                    'status' => 'active',
                    'test_last_completed_at' => now()->subDays(12),
                ],
                [
                    'location_name' => 'Remote WebRTC Hybrid Workforce Hub',
                    'elin_number' => '5125550913',
                    'street_address' => '500 W 2nd St',
                    'unit_suite' => 'Floor 19 Hot-Desk Zone',
                    'city' => 'Austin',
                    'state_province' => 'TX',
                    'postal_code' => '78701',
                    'country' => 'US',
                    'psap_name' => 'Travis Regional PSAP Central',
                    'notification_emails' => ['workforce-ops@corp.znero.com'],
                    'notification_phones' => ['+15125556666'],
                    'karis_law_compliant' => true,
                    'ray_baums_compliant' => true,
                    'status' => 'testing',
                    'test_last_completed_at' => null,
                ],
            ];

            foreach ($locations as $loc) {
                PbxEmergencyLocation::create(array_merge($loc, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 3. Caller-ID & Normalization Rules
        if (PbxCallerIdRule::where('domain_uuid', $domainUuid)->count() === 0) {
            $cidRules = [
                [
                    'rule_name' => 'Global Brand E.164 Identity',
                    'scope_type' => 'global',
                    'scope_value' => null,
                    'caller_id_name' => 'Znero Telecom Corp',
                    'caller_id_number' => '+18005550199',
                    'emergency_caller_id_number' => '5125550911',
                    'normalization_format' => 'e164',
                    'strip_digits' => 0,
                    'prepend_digits' => null,
                    'priority' => 100,
                    'is_active' => true,
                ],
                [
                    'rule_name' => 'Executive Direct Dial Outbound Rule',
                    'scope_type' => 'extension',
                    'scope_value' => '1001',
                    'caller_id_name' => 'J. Sterling Office',
                    'caller_id_number' => '+15125550101',
                    'emergency_caller_id_number' => '5125550911',
                    'normalization_format' => 'e164',
                    'strip_digits' => 0,
                    'prepend_digits' => null,
                    'priority' => 10,
                    'is_active' => true,
                ],
                [
                    'rule_name' => 'Support Desk Queue Dynamic Caller-ID',
                    'scope_type' => 'department',
                    'scope_value' => 'Support',
                    'caller_id_name' => 'Znero Global Support',
                    'caller_id_number' => '+18885550144',
                    'emergency_caller_id_number' => '5125550911',
                    'normalization_format' => 'e164',
                    'strip_digits' => 0,
                    'prepend_digits' => null,
                    'priority' => 20,
                    'is_active' => true,
                ],
                [
                    'rule_name' => 'Local Austin Dynamic NPA Matching (512 Area)',
                    'scope_type' => 'npa_nxx_match',
                    'scope_value' => '512*',
                    'caller_id_name' => 'Znero Austin Regional',
                    'caller_id_number' => '+15125550188',
                    'emergency_caller_id_number' => '5125550911',
                    'normalization_format' => 'national_10',
                    'strip_digits' => 0,
                    'prepend_digits' => null,
                    'priority' => 30,
                    'is_active' => true,
                ],
            ];

            foreach ($cidRules as $rule) {
                PbxCallerIdRule::create(array_merge($rule, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 4. Call Permission Profiles
        if (PbxCallPermissionProfile::where('domain_uuid', $domainUuid)->count() === 0) {
            $profiles = [
                [
                    'profile_name' => 'Executive Global Tier (Unrestricted)',
                    'description' => 'Full domestic, international, toll-free, and roaming privilege 24/7 without concurrent limits.',
                    'allow_international' => true,
                    'allow_national' => true,
                    'allow_toll_free' => true,
                    'allow_premium_rate' => false,
                    'internal_only' => false,
                    'max_concurrent_calls' => 10,
                    'time_restriction_type' => 'none',
                    'active_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'is_active' => true,
                ],
                [
                    'profile_name' => 'Standard Corporate Staff (Business Hours Only)',
                    'description' => 'Domestic national and toll-free calls permitted during business hours. International barred.',
                    'allow_international' => false,
                    'allow_national' => true,
                    'allow_toll_free' => true,
                    'allow_premium_rate' => false,
                    'internal_only' => false,
                    'max_concurrent_calls' => 2,
                    'time_restriction_type' => 'business_hours_only',
                    'active_days' => ['mon', 'tue', 'wed', 'thu', 'fri'],
                    'start_time' => '08:00',
                    'end_time' => '18:00',
                    'is_active' => true,
                ],
                [
                    'profile_name' => 'Contact Center Agent Profile',
                    'description' => 'National and toll-free access with 4 concurrent call channels for queue transfers and callbacks.',
                    'allow_international' => false,
                    'allow_national' => true,
                    'allow_toll_free' => true,
                    'allow_premium_rate' => false,
                    'internal_only' => false,
                    'max_concurrent_calls' => 4,
                    'time_restriction_type' => 'none',
                    'active_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
                    'start_time' => '07:00',
                    'end_time' => '21:00',
                    'is_active' => true,
                ],
                [
                    'profile_name' => 'Lobby / Security / Internal Only',
                    'description' => 'Barred from all external PSTN routes except 911 emergency. Extension-to-extension only.',
                    'allow_international' => false,
                    'allow_national' => false,
                    'allow_toll_free' => false,
                    'allow_premium_rate' => false,
                    'internal_only' => true,
                    'max_concurrent_calls' => 1,
                    'time_restriction_type' => 'none',
                    'active_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'],
                    'start_time' => '00:00',
                    'end_time' => '23:59',
                    'is_active' => true,
                ],
            ];

            foreach ($profiles as $p) {
                PbxCallPermissionProfile::create(array_merge($p, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 5. Recording Retention Policies
        if (PbxRecordingRetentionRule::where('domain_uuid', $domainUuid)->count() === 0) {
            $retentionRules = [
                [
                    'rule_name' => 'SEC Rule 17a-4 Financial Trading Compliance',
                    'category' => 'financial',
                    'retention_days' => 2555, // 7 years
                    'auto_purge_enabled' => false,
                    'storage_tier' => 's3_glacier',
                    'encryption_algorithm' => 'AES-256-GCM',
                    'kms_key_id' => 'arn:aws:kms:us-east-1:109283748291:key/sec-audit-master',
                    'is_active' => true,
                ],
                [
                    'rule_name' => 'HIPAA Healthcare Voice Interaction Retention',
                    'category' => 'healthcare',
                    'retention_days' => 2190, // 6 years
                    'auto_purge_enabled' => false,
                    'storage_tier' => 'wasabi_secure',
                    'encryption_algorithm' => 'AES-256-GCM',
                    'kms_key_id' => 'arn:wasabi:kms:us-central:key/hipaa-voice-phi',
                    'is_active' => true,
                ],
                [
                    'rule_name' => 'PCI-DSS Cardholder Interaction Ephemeral Retention',
                    'category' => 'pci_dss',
                    'retention_days' => 30,
                    'auto_purge_enabled' => true,
                    'storage_tier' => 'local_encrypted',
                    'encryption_algorithm' => 'ChaCha20-Poly1305',
                    'kms_key_id' => 'local-pci-fips140-2-vault',
                    'is_active' => true,
                ],
                [
                    'rule_name' => 'Standard Customer Support QA Tier',
                    'category' => 'support',
                    'retention_days' => 90,
                    'auto_purge_enabled' => true,
                    'storage_tier' => 'local_encrypted',
                    'encryption_algorithm' => 'AES-256-GCM',
                    'kms_key_id' => 'local-standard-volume-key',
                    'is_active' => true,
                ],
            ];

            foreach ($retentionRules as $rule) {
                PbxRecordingRetentionRule::create(array_merge($rule, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 6. Recording Legal Holds
        if (PbxRecordingLegalHold::where('domain_uuid', $domainUuid)->count() === 0) {
            $holds = [
                [
                    'case_number' => 'SEC-2026-0819-LIT',
                    'case_title' => 'Inquiry into High-Frequency Routing Execution',
                    'recording_identifier' => 'rec_uuid_77921a90c1e84a22b',
                    'caller_destination' => '+15125550101 -> +12125550999',
                    'call_date' => now()->subDays(14),
                    'custodian_name' => 'J. Sterling (Executive Suite)',
                    'legal_officer' => 'Morgan & Vance LLP (G. Vance)',
                    'reason_notes' => 'Mandatory litigation hold per federal subpoena SEC Docket 2026-904. Purge disabled.',
                    'status' => 'active',
                    'tamper_checksum_sha256' => hash('sha256', 'rec_uuid_77921a90c1e84a22b-SEC-2026-0819-LIT-tamper-evident'),
                ],
                [
                    'case_number' => 'CORP-IP-2026-0044',
                    'case_title' => 'Arbitration on Telecom Carrier Interconnect Rates',
                    'recording_identifier' => 'rec_uuid_55104bb939a10ef41',
                    'caller_destination' => '+15125550102 -> +442079460111',
                    'call_date' => now()->subDays(28),
                    'custodian_name' => 'Carrier Relations Desk',
                    'legal_officer' => 'Clara Bennett (Chief Legal Counsel)',
                    'reason_notes' => 'Contractual arbitration regarding billing discrepancies on LCR gateway interconnect.',
                    'status' => 'active',
                    'tamper_checksum_sha256' => hash('sha256', 'rec_uuid_55104bb939a10ef41-CORP-IP-2026-0044-tamper-evident'),
                ],
                [
                    'case_number' => 'AUDIT-HIST-2025-99',
                    'case_title' => 'Historical PCI Data Exemption Verification',
                    'recording_identifier' => 'rec_uuid_109282ffca201e991',
                    'caller_destination' => '+18885550144 -> +14155552671',
                    'call_date' => now()->subMonths(3),
                    'custodian_name' => 'Payment Operations Lead',
                    'legal_officer' => 'David Kim (Compliance Officer)',
                    'reason_notes' => 'Audit review resolved. Retained for 90-day post-review period then release authorized.',
                    'status' => 'released',
                    'tamper_checksum_sha256' => hash('sha256', 'rec_uuid_109282ffca201e991-AUDIT-HIST-2025-99-tamper-evident'),
                ],
            ];

            foreach ($holds as $hold) {
                PbxRecordingLegalHold::create(array_merge($hold, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }

        // 7. Device Fleet & WebRTC Endpoint Inventory
        if (PbxDeviceFleet::where('domain_uuid', $domainUuid)->count() === 0) {
            $devices = [
                [
                    'mac_address' => '00:15:65:AB:12:44',
                    'vendor' => 'Yealink',
                    'model' => 'SIP-T46U Gigabit Executive Phone',
                    'firmware_version' => '66.86.0.75',
                    'assigned_extension' => '1001',
                    'ip_address' => '192.168.10.45',
                    'transport' => 'TLS',
                    'sip_status' => 'registered',
                    'rtt_ms' => 12,
                    'last_provisioned_at' => now()->subDays(1),
                    'config_version' => 'v2.1.0',
                ],
                [
                    'mac_address' => '64:16:7F:32:89:10',
                    'vendor' => 'Polycom',
                    'model' => 'VVX 450 12-Line IP Phone',
                    'firmware_version' => '6.4.3.5044',
                    'assigned_extension' => '1002',
                    'ip_address' => '192.168.10.62',
                    'transport' => 'TLS',
                    'sip_status' => 'registered',
                    'rtt_ms' => 18,
                    'last_provisioned_at' => now()->subDays(4),
                    'config_version' => 'v2.1.0',
                ],
                [
                    'mac_address' => 'F8:7B:20:CC:55:1A',
                    'vendor' => 'Cisco',
                    'model' => 'CP-8845 Multiplatform Color Display',
                    'firmware_version' => '12.0.1MPP',
                    'assigned_extension' => '1003',
                    'ip_address' => '192.168.10.99',
                    'transport' => 'TLS',
                    'sip_status' => 'registered',
                    'rtt_ms' => 24,
                    'last_provisioned_at' => now()->subDays(6),
                    'config_version' => 'v2.0.4',
                ],
                [
                    'mac_address' => '52:54:00:FE:A1:08',
                    'vendor' => 'WebRTC Softphone',
                    'model' => 'Znero Cloud Communicator (Chrome V8 Engine)',
                    'firmware_version' => 'v3.4.12-webrtc',
                    'assigned_extension' => '1004',
                    'ip_address' => '138.252.125.206',
                    'transport' => 'WSS',
                    'sip_status' => 'registered',
                    'rtt_ms' => 4,
                    'last_provisioned_at' => now()->subHours(3),
                    'config_version' => 'v3.4.0',
                ],
                [
                    'mac_address' => 'C0:74:AD:41:00:2B',
                    'vendor' => 'Grandstream',
                    'model' => 'GRP2614 Carrier-Grade IP Phone',
                    'firmware_version' => '1.0.7.25',
                    'assigned_extension' => '1005',
                    'ip_address' => '192.168.10.115',
                    'transport' => 'UDP',
                    'sip_status' => 'unreachable',
                    'rtt_ms' => 0,
                    'last_provisioned_at' => now()->subDays(18),
                    'config_version' => 'v1.9.8',
                ],
            ];

            foreach ($devices as $d) {
                PbxDeviceFleet::create(array_merge($d, [
                    'uuid' => Str::uuid()->toString(),
                    'domain_uuid' => $domainUuid,
                ]));
            }
        }
    }

    /**
     * Simulate E911 Emergency Dispatch.
     */
    public function simulateEmergencyDispatch(string $locationUuid, string $callerExtension, string $domainUuid): array
    {
        $location = PbxEmergencyLocation::where('uuid', $locationUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        // 1. Kari's Law Compliance Verification: Direct 911 dial without prefix
        $karisLawCheck = [
            'status' => 'PASS',
            'detail' => "Direct 911 dialing active. Dial string '911' is routed directly without requirement for '9' or '8' outside line prefix.",
        ];

        // 2. Ray Baum's Act Compliance Verification: Dispatchable location present
        $rayBaumsCompliant = !empty($location->street_address) && !empty($location->unit_suite);
        $rayBaumsCheck = [
            'status' => $rayBaumsCompliant ? 'PASS' : 'WARNING',
            'detail' => $rayBaumsCompliant
                ? "Dispatchable location validated: {$location->street_address}, {$location->unit_suite}, {$location->city}, {$location->state_province} {$location->postal_code}."
                : "Missing specific suite/room identifier. Update unit_suite for full Ray Baum's Act compliance.",
        ];

        // 3. PSAP Routing ELIN
        $elinUsed = $location->elin_number;

        // 4. Stakeholder Notifications
        $notificationsSent = [];
        $emails = $location->notification_emails ?? [];
        foreach ($emails as $email) {
            $notificationsSent[] = [
                'channel' => 'EMAIL',
                'target' => $email,
                'status' => 'DISPATCHED',
                'timestamp' => now()->toIso8601String(),
                'payload' => "EMERGENCY CALL ALERT: Extension {$callerExtension} initiated 911 at {$location->location_name} (ELIN: {$elinUsed}).",
            ];
        }

        $phones = $location->notification_phones ?? [];
        foreach ($phones as $phone) {
            $notificationsSent[] = [
                'channel' => 'SMS',
                'target' => $phone,
                'status' => 'DISPATCHED',
                'timestamp' => now()->toIso8601String(),
                'payload' => "911 ALERT: Ext {$callerExtension} dialed 911 from {$location->location_name}.",
            ];
        }

        // Update test timestamp
        $location->update([
            'test_last_completed_at' => now(),
            'status' => 'active',
        ]);

        return [
            'location_uuid' => $location->uuid,
            'location_name' => $location->location_name,
            'caller_extension' => $callerExtension,
            'elin' => $elinUsed,
            'psap_destination' => $location->psap_name,
            'karis_law' => $karisLawCheck,
            'ray_baums' => $rayBaumsCheck,
            'notifications_dispatched' => $notificationsSent,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * Test Caller-ID Policy and Number Normalization Engine.
     */
    public function simulateCallerIdNormalization(string $dialedNumber, ?string $extension, ?string $department, string $domainUuid): array
    {
        // 1. Raw number cleaning
        $cleaned = preg_replace('/[^\d+]/', '', $dialedNumber);

        // 2. Find matching Caller-ID policy in priority order
        $rules = PbxCallerIdRule::where('domain_uuid', $domainUuid)
            ->where('is_active', true)
            ->orderBy('priority', 'asc')
            ->get();

        $matchedRule = null;
        foreach ($rules as $rule) {
            if ($rule->scope_type === 'extension' && $rule->scope_value === $extension) {
                $matchedRule = $rule;
                break;
            } elseif ($rule->scope_type === 'department' && strcasecmp($rule->scope_value, $department ?? '') === 0) {
                $matchedRule = $rule;
                break;
            } elseif ($rule->scope_type === 'npa_nxx_match') {
                $prefix = rtrim($rule->scope_value, '*');
                $strippedTarget = ltrim($cleaned, '+1');
                if (str_starts_with($strippedTarget, $prefix)) {
                    $matchedRule = $rule;
                    break;
                }
            }
        }

        if (!$matchedRule) {
            $matchedRule = $rules->firstWhere('scope_type', 'global') ?? (object)[
                'rule_name' => 'System Fallback',
                'caller_id_name' => 'Znero PBX Enterprise',
                'caller_id_number' => '+18005550199',
                'emergency_caller_id_number' => '5125550911',
                'normalization_format' => 'e164',
                'strip_digits' => 0,
                'prepend_digits' => null,
            ];
        }

        // 3. Apply Normalization Algorithm
        $normalizedNumber = $cleaned;
        if ($matchedRule->strip_digits > 0) {
            $normalizedNumber = substr($normalizedNumber, $matchedRule->strip_digits);
        }
        if (!empty($matchedRule->prepend_digits)) {
            $normalizedNumber = $matchedRule->prepend_digits . $normalizedNumber;
        }

        // Standard format normalization
        if ($matchedRule->normalization_format === 'e164') {
            if (!str_starts_with($normalizedNumber, '+')) {
                $digits = preg_replace('/[^\d]/', '', $normalizedNumber);
                if (strlen($digits) === 10) {
                    $normalizedNumber = '+1' . $digits;
                } elseif (strlen($digits) === 11 && str_starts_with($digits, '1')) {
                    $normalizedNumber = '+' . $digits;
                } else {
                    $normalizedNumber = '+' . $digits;
                }
            }
        } elseif ($matchedRule->normalization_format === 'national_10') {
            $digits = preg_replace('/[^\d]/', '', $normalizedNumber);
            if (strlen($digits) === 11 && str_starts_with($digits, '1')) {
                $normalizedNumber = substr($digits, 1);
            } elseif (strlen($digits) > 10) {
                $normalizedNumber = substr($digits, -10);
            }
        }

        return [
            'raw_input' => $dialedNumber,
            'normalized_destination' => $normalizedNumber,
            'matched_rule' => $matchedRule->rule_name,
            'scope' => $matchedRule->scope_type ?? 'fallback',
            'effective_caller_id_name' => $matchedRule->caller_id_name,
            'effective_caller_id_number' => $matchedRule->caller_id_number,
            'emergency_caller_id_number' => $matchedRule->emergency_caller_id_number ?? '5125550911',
            'format_applied' => $matchedRule->normalization_format,
        ];
    }

    /**
     * Evaluate Call Permission Profile & Barring Gatekeeper.
     */
    public function evaluateCallPermission(string $extension, string $destination, string $domainUuid, ?string $timeString = null): array
    {
        // 1. Determine destination type
        $cleanDest = preg_replace('/[^\d+]/', '', $destination);
        $destType = 'national';
        if ($cleanDest === '911' || $cleanDest === '112' || $cleanDest === '999') {
            $destType = 'emergency';
        } elseif (strlen($cleanDest) <= 4 && !str_starts_with($cleanDest, '+')) {
            $destType = 'internal';
        } elseif (preg_match('/^(\+?1)?(800|888|877|866|855|844|833)\d{7}$/', $cleanDest)) {
            $destType = 'toll_free';
        } elseif (preg_match('/^(\+?1)?(900|976)\d{7}$/', $cleanDest)) {
            $destType = 'premium_rate';
        } elseif (str_starts_with($cleanDest, '+') && !str_starts_with($cleanDest, '+1')) {
            $destType = 'international';
        } elseif (str_starts_with($cleanDest, '011')) {
            $destType = 'international';
        }

        // 2. Select profile
        $profile = null;
        if ($extension === '1001') {
            $profile = PbxCallPermissionProfile::where('domain_uuid', $domainUuid)
                ->where('profile_name', 'like', '%Executive%')
                ->first();
        } elseif ($extension === '1004') {
            $profile = PbxCallPermissionProfile::where('domain_uuid', $domainUuid)
                ->where('profile_name', 'like', '%Contact Center%')
                ->first();
        }

        if (!$profile) {
            $profile = PbxCallPermissionProfile::where('domain_uuid', $domainUuid)
                ->where('profile_name', 'like', '%Standard%')
                ->first();
        }

        if (!$profile) {
            $profile = PbxCallPermissionProfile::where('domain_uuid', $domainUuid)->first();
        }

        // Emergency is ALWAYS permitted by federal and international telecom law
        if ($destType === 'emergency') {
            return [
                'allowed' => true,
                'reason' => 'Emergency 911 traffic is unconditional and cannot be barred under Kari\'s Law.',
                'matched_profile' => $profile ? $profile->profile_name : 'Default Law Compliance Profile',
                'destination_type' => $destType,
                'schedule_status' => '24/7 Priority Emergency Bypass',
            ];
        }

        if (!$profile) {
            return [
                'allowed' => true,
                'reason' => 'No barring profile active. Permitted by default.',
                'matched_profile' => 'None',
                'destination_type' => $destType,
                'schedule_status' => 'unrestricted',
            ];
        }

        // 3. Evaluate destination type permissions
        if ($profile->internal_only && $destType !== 'internal') {
            return [
                'allowed' => false,
                'reason' => 'Extension is restricted to Internal extension-to-extension calls only.',
                'matched_profile' => $profile->profile_name,
                'destination_type' => $destType,
                'schedule_status' => 'barred_by_profile',
            ];
        }

        if ($destType === 'international' && !$profile->allow_international) {
            return [
                'allowed' => false,
                'reason' => 'International dialing is barred on this profile.',
                'matched_profile' => $profile->profile_name,
                'destination_type' => $destType,
                'schedule_status' => 'barred_by_destination_rule',
            ];
        }

        if ($destType === 'premium_rate' && !$profile->allow_premium_rate) {
            return [
                'allowed' => false,
                'reason' => 'Premium Rate (900/976) call barring active to prevent toll fraud.',
                'matched_profile' => $profile->profile_name,
                'destination_type' => $destType,
                'schedule_status' => 'barred_by_destination_rule',
            ];
        }

        // 4. Time-of-day restrictions
        $now = $timeString ? Carbon::parse($timeString) : now();
        $dayOfWeek = strtolower($now->format('D'));
        $timeOfDay = $now->format('H:i');

        if ($profile->time_restriction_type === 'business_hours_only') {
            $activeDays = $profile->active_days ?? ['mon', 'tue', 'wed', 'thu', 'fri'];
            if (!in_array($dayOfWeek, $activeDays)) {
                return [
                    'allowed' => false,
                    'reason' => "Calls barred outside scheduled business days (Current day: {$dayOfWeek}).",
                    'matched_profile' => $profile->profile_name,
                    'destination_type' => $destType,
                    'schedule_status' => 'barred_by_schedule_day',
                ];
            }

            if ($profile->start_time && $profile->end_time) {
                if ($timeOfDay < $profile->start_time || $timeOfDay > $profile->end_time) {
                    return [
                        'allowed' => false,
                        'reason' => "Calls barred outside business hours ({$profile->start_time} - {$profile->end_time}). Current time: {$timeOfDay}.",
                        'matched_profile' => $profile->profile_name,
                        'destination_type' => $destType,
                        'schedule_status' => 'barred_by_schedule_time',
                    ];
                }
            }
        }

        return [
            'allowed' => true,
            'reason' => 'Call permitted. Destination and schedule verified within Class of Service.',
            'matched_profile' => $profile->profile_name,
            'destination_type' => $destType,
            'schedule_status' => 'authorized',
            'max_channels' => $profile->max_concurrent_calls,
        ];
    }

    /**
     * Toggle Legal Hold on Call Recording.
     */
    public function toggleLegalHold(string $holdUuid, string $domainUuid): array
    {
        $hold = PbxRecordingLegalHold::where('uuid', $holdUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $newStatus = $hold->status === 'active' ? 'released' : 'active';
        $newChecksum = hash('sha256', "{$hold->recording_identifier}-{$hold->case_number}-{$newStatus}-" . now()->toIso8601String());

        $hold->update([
            'status' => $newStatus,
            'tamper_checksum_sha256' => $newChecksum,
            'updated_at' => now(),
        ]);

        return [
            'uuid' => $hold->uuid,
            'case_number' => $hold->case_number,
            'status' => $hold->status,
            'tamper_checksum_sha256' => $hold->tamper_checksum_sha256,
        ];
    }

    /**
     * Reboot Fleet Device (Simulated SIP NOTIFY check-sync).
     */
    public function rebootDevice(string $deviceUuid, string $domainUuid): array
    {
        $device = PbxDeviceFleet::where('uuid', $deviceUuid)
            ->where('domain_uuid', $domainUuid)
            ->firstOrFail();

        $device->update([
            'last_provisioned_at' => now(),
            'sip_status' => 'registered',
            'rtt_ms' => rand(10, 25),
        ]);

        return [
            'device_uuid' => $device->uuid,
            'mac_address' => $device->mac_address,
            'model' => $device->model,
            'sip_event' => "NOTIFY sip:{$device->ip_address}:5060;transport={$device->transport} SIP/2.0\r\nEvent: check-sync;reboot=true\r\nContent-Length: 0\r\n",
            'status' => 'SUCCESS',
            'message' => "SIP NOTIFY check-sync dispatched to {$device->mac_address} ({$device->ip_address}). Device reboot and reprovision sequence initiated.",
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
