<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCenterAgentStatusLog extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_agent_status_logs';
    protected $primaryKey = 'log_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'log_uuid',
        'domain_uuid',
        'agent_uuid',
        'status',
        'break_reason_uuid',
        'started_at',
        'ended_at',
        'duration_seconds',
        'call_uuid',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function breakReason(): BelongsTo
    {
        return $this->belongsTo(ContactCenterBreakReason::class, 'break_reason_uuid', 'break_reason_uuid');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }
}
