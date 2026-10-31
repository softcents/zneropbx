<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityQuarantinedIp extends Model
{
    use HasFactory;

    protected $table = 'security_quarantined_ips';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'ip_address',
        'reason',
        'trigger_rule_uuid',
        'threat_score',
        'quarantined_at',
        'expires_at',
        'status',
        'mitigation_type',
    ];

    protected $casts = [
        'threat_score' => 'decimal:2',
        'quarantined_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'quarantine_uuid',
    ];

    public function getQuarantineUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }

    public function rule()
    {
        return $this->belongsTo(SecurityFraudRule::class, 'trigger_rule_uuid', 'uuid');
    }
}
