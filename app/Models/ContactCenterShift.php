<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterShift extends Model
{
    use HasFactory, \App\Models\Traits\TraitUuid;

    protected $table = 'contact_center_shifts';
    protected $primaryKey = 'shift_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'shift_uuid',
        'domain_uuid',
        'name',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'break_duration_minutes' => 'integer',
    ];

    public function schedules()
    {
        return $this->hasMany(ContactCenterSchedule::class, 'shift_uuid', 'shift_uuid');
    }
}
