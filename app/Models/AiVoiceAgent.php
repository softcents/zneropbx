<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVoiceAgent extends Model
{
    use HasFactory;

    protected $table = 'ai_voice_agents';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'agent_name',
        'assigned_extension',
        'persona_role',
        'primary_model',
        'tts_voice',
        'speaking_rate',
        'barge_in_enabled',
        'system_prompt',
        'prompt_version',
        'rag_knowledge_base_enabled',
        'escalation_sentiment_threshold',
        'human_handoff_queue_uuid',
        'max_turn_duration_sec',
        'status',
        'total_calls_handled',
        'avg_latency_ms',
        'csat_score',
    ];

    protected $casts = [
        'speaking_rate' => 'decimal:2',
        'barge_in_enabled' => 'boolean',
        'rag_knowledge_base_enabled' => 'boolean',
        'escalation_sentiment_threshold' => 'decimal:2',
        'max_turn_duration_sec' => 'integer',
        'total_calls_handled' => 'integer',
        'avg_latency_ms' => 'integer',
        'csat_score' => 'decimal:2',
    ];

    protected $appends = [
        'agent_uuid',
    ];

    public function getAgentUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }

    public function sessions()
    {
        return $this->hasMany(AiVoiceCallSession::class, 'agent_uuid', 'uuid');
    }
}
