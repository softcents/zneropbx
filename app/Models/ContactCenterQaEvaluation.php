<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterQaEvaluation extends Model
{
    use HasFactory, \App\Models\Traits\TraitUuid;

    protected $table = 'contact_center_qa_evaluations';
    protected $primaryKey = 'qa_evaluation_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'qa_evaluation_uuid',
        'domain_uuid',
        'scorecard_uuid',
        'xml_cdr_uuid',
        'agent_uuid',
        'supervisor_user_uuid',
        'total_score',
        'max_score',
        'percentage',
        'result',
        'status',
        'answers',
        'feedback',
        'coaching_notes',
        'agent_comments',
        'acknowledged_at',
    ];

    protected $casts = [
        'total_score' => 'float',
        'max_score' => 'float',
        'percentage' => 'float',
        'answers' => 'array',
        'acknowledged_at' => 'datetime',
    ];

    public function scorecard()
    {
        return $this->belongsTo(ContactCenterQaScorecard::class, 'scorecard_uuid', 'qa_scorecard_uuid');
    }

    public function agent()
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }

    public function cdr()
    {
        return $this->belongsTo(CDR::class, 'xml_cdr_uuid', 'xml_cdr_uuid');
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_user_uuid', 'user_uuid');
    }
}
