<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityCompliancePolicy extends Model
{
    use HasFactory;

    protected $table = 'security_compliance_policies';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'jurisdiction',
        'recording_retention_days',
        'cdr_retention_days',
        'enforce_pci_dtmf_masking',
        'gdpr_right_to_be_forgotten_enabled',
        'immutable_audit_logging',
        'require_mfa_admin_actions',
    ];

    protected $casts = [
        'recording_retention_days' => 'integer',
        'cdr_retention_days' => 'integer',
        'enforce_pci_dtmf_masking' => 'boolean',
        'gdpr_right_to_be_forgotten_enabled' => 'boolean',
        'immutable_audit_logging' => 'boolean',
        'require_mfa_admin_actions' => 'boolean',
    ];

    protected $appends = [
        'policy_uuid',
    ];

    public function getPolicyUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
