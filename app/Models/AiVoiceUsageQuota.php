<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVoiceUsageQuota extends Model
{
    use HasFactory;

    protected $table = 'ai_voice_usage_quotas';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'monthly_token_quota',
        'monthly_minutes_quota',
        'tokens_consumed_month',
        'minutes_consumed_month',
        'monthly_spend_usd',
        'spend_budget_ceiling_usd',
    ];

    protected $casts = [
        'monthly_token_quota' => 'integer',
        'monthly_minutes_quota' => 'integer',
        'tokens_consumed_month' => 'integer',
        'minutes_consumed_month' => 'integer',
        'monthly_spend_usd' => 'decimal:4',
        'spend_budget_ceiling_usd' => 'decimal:4',
    ];
}
