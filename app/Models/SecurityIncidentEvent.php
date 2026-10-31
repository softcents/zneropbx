<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityIncidentEvent extends Model
{
    use HasFactory;

    protected $table = 'security_incident_events';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'incident_code',
        'title',
        'severity',
        'source_ip',
        'affected_extension_or_trunk',
        'risk_score',
        'detected_vector',
        'status',
        'mitigation_action_taken',
        'audit_notes',
        'detected_at',
        'resolved_at',
    ];

    protected $casts = [
        'risk_score' => 'decimal:2',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected $appends = [
        'incident_uuid',
    ];

    public function getIncidentUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
