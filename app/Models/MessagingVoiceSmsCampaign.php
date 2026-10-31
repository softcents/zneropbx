<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagingVoiceSmsCampaign extends Model
{
    use HasFactory;

    protected $table = 'messaging_voice_sms_campaigns';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'campaign_title',
        'tts_voice',
        'tts_script',
        'dtmf_confirm_key',
        'recipients_count',
        'completed_count',
        'acknowledged_count',
        'status',
    ];

    protected $casts = [
        'recipients_count' => 'integer',
        'completed_count' => 'integer',
        'acknowledged_count' => 'integer',
    ];
}
