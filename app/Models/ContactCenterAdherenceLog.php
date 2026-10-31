<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterAdherenceLog extends Model
{
    use HasFactory, \App\Models\Traits\TraitUuid;

    protected $table = 'contact_center_adherence_logs';
    protected $primaryKey = 'adherence_log_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'adherence_log_uuid',
        'domain_uuid',
        'agent_uuid',
        'expected_state',
        'actual_state',
        'is_adherent',
        'adherence_percentage',
        'checked_at',
    ];

    protected $casts = [
        'is_adherent' => 'boolean',
        'adherence_percentage' => 'float',
        'checked_at' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }
}
