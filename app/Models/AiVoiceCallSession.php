<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVoiceCallSession extends Model
{
    use HasFactory;

    protected $table = 'ai_voice_call_sessions';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'agent_uuid',
        'caller_phone',
        'caller_name',
        'session_duration_sec',
        'sentiment_score',
        'hand_off_triggered',
        'handoff_reason',
        'stt_turnaround_ms',
        'llm_inference_ms',
        'tts_synthesis_ms',
        'total_latency_ms',
        'tokens_used',
        'call_summary',
        'full_transcript_json',
        'cost_usd',
    ];

    protected $casts = [
        'session_duration_sec' => 'integer',
        'sentiment_score' => 'decimal:2',
        'hand_off_triggered' => 'boolean',
        'stt_turnaround_ms' => 'integer',
        'llm_inference_ms' => 'integer',
        'tts_synthesis_ms' => 'integer',
        'total_latency_ms' => 'integer',
        'tokens_used' => 'integer',
        'cost_usd' => 'decimal:5',
        'full_transcript_json' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo(AiVoiceAgent::class, 'agent_uuid', 'uuid');
    }
}
