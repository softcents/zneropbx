<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemProductionDeployment extends Model
{
    use HasFactory;

    protected $table = 'system_production_deployments';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'release_version',
        'git_commit_hash',
        'deployed_by',
        'status',
        'health_audit_score_pct',
        'artifact_provenance_sha256',
        'rollback_snapshot_id',
        'deployed_at',
    ];

    protected $casts = [
        'health_audit_score_pct' => 'decimal:2',
        'deployed_at' => 'datetime',
    ];

    protected $appends = [
        'deployment_uuid',
    ];

    public function getDeploymentUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
