<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorAuditLog extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'supervisor_audit_logs';
    protected $primaryKey = 'audit_uuid';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'audit_uuid',
        'domain_uuid',
        'supervisor_user_uuid',
        'supervisor_extension',
        'action_type',
        'target_agent_uuid',
        'target_agent_name',
        'target_channel_uuid',
        'target_queue_uuid',
        'reason_code',
        'reason_description',
        'ip_address',
        'status',
        'details',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_user_uuid', 'user_uuid');
    }
}
