<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVoiceProviderAdapter extends Model
{
    use HasFactory;

    protected $table = 'ai_voice_provider_adapters';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'provider_name',
        'api_endpoint',
        'api_key_masked',
        'supports_realtime_audio',
        'is_healthy',
        'priority_order',
        'cost_per_minute_usd',
    ];

    protected $casts = [
        'supports_realtime_audio' => 'boolean',
        'is_healthy' => 'boolean',
        'priority_order' => 'integer',
        'cost_per_minute_usd' => 'decimal:5',
    ];
}
