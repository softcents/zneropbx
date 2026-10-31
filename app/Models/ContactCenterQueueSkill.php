<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterQueueSkill extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contact_center_queue_skills';
    protected $primaryKey = 'queue_skill_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'queue_skill_uuid',
        'domain_uuid',
        'queue_uuid',
        'skill_uuid',
        'min_proficiency',
        'is_mandatory',
    ];

    protected $casts = [
        'min_proficiency' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    public function skill()
    {
        return $this->belongsTo(ContactCenterSkill::class, 'skill_uuid', 'skill_uuid');
    }

    public function queue()
    {
        return $this->belongsTo(CallCenterQueues::class, 'queue_uuid', 'call_center_queue_uuid');
    }
}
