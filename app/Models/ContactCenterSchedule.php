<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterSchedule extends Model
{
    use HasFactory, \App\Models\Traits\TraitUuid;

    protected $table = 'contact_center_schedules';
    protected $primaryKey = 'schedule_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'schedule_uuid',
        'domain_uuid',
        'agent_uuid',
        'shift_uuid',
        'schedule_date',
        'start_time',
        'end_time',
        'expected_activity',
        'published',
        'created_by',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'published' => 'boolean',
    ];

    public function agent()
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }

    public function shift()
    {
        return $this->belongsTo(ContactCenterShift::class, 'shift_uuid', 'shift_uuid');
    }
}
