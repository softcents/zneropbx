<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterCallTranscript extends Model
{
    use HasFactory;

    protected $table = 'contact_center_call_transcripts';
    protected $primaryKey = 'transcript_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'transcript_uuid',
        'domain_uuid',
        'xml_cdr_uuid',
        'caller_id_number',
        'caller_id_name',
        'agent_uuid',
        'agent_name',
        'duration_seconds',
        'language',
        'full_transcript',
        'redacted_transcript',
        'summary',
        'key_points',
        'action_items',
        'utterances',
        'talk_time_agent_sec',
        'talk_time_customer_sec',
        'silence_sec',
        'overtalk_sec',
        'talk_ratio_agent',
        'overall_sentiment',
        'customer_sentiment_score',
        'agent_empathy_score',
        'compliance_score',
        'compliance_flags',
        'keywords_spotted',
        'ai_auto_score',
        'ai_scorecard_suggestions',
        'status',
    ];

    protected $casts = [
        'key_points' => 'array',
        'action_items' => 'array',
        'utterances' => 'array',
        'compliance_flags' => 'array',
        'keywords_spotted' => 'array',
        'ai_scorecard_suggestions' => 'array',
        'talk_time_agent_sec' => 'integer',
        'talk_time_customer_sec' => 'integer',
        'silence_sec' => 'integer',
        'overtalk_sec' => 'integer',
        'talk_ratio_agent' => 'float',
        'customer_sentiment_score' => 'float',
        'agent_empathy_score' => 'float',
        'compliance_score' => 'float',
        'ai_auto_score' => 'float',
    ];

    public function cdr()
    {
        return $this->belongsTo(CDR::class, 'xml_cdr_uuid', 'xml_cdr_uuid');
    }

    public function agent()
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }
}
