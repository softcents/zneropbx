<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterAgentSkill extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contact_center_agent_skills';
    protected $primaryKey = 'agent_skill_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'agent_skill_uuid',
        'domain_uuid',
        'agent_uuid',
        'skill_uuid',
        'proficiency_level',
    ];

    protected $casts = [
        'proficiency_level' => 'integer',
    ];

    public function skill()
    {
        return $this->belongsTo(ContactCenterSkill::class, 'skill_uuid', 'skill_uuid');
    }

    public function agent()
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }
}
