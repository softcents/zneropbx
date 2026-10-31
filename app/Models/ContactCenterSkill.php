<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterSkill extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contact_center_skills';
    protected $primaryKey = 'skill_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'skill_uuid',
        'domain_uuid',
        'name',
        'code',
        'category',
        'description',
    ];

    public function agentSkills()
    {
        return $this->hasMany(ContactCenterAgentSkill::class, 'skill_uuid', 'skill_uuid');
    }

    public function queueSkills()
    {
        return $this->hasMany(ContactCenterQueueSkill::class, 'skill_uuid', 'skill_uuid');
    }
}
