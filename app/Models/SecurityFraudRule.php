<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecurityFraudRule extends Model
{
    use HasFactory;

    protected $table = 'security_fraud_rules';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'rule_name',
        'rule_type',
        'threshold_value',
        'time_window_sec',
        'action',
        'is_enabled',
        'severity',
        'auto_mitigate',
    ];

    protected $casts = [
        'threshold_value' => 'integer',
        'time_window_sec' => 'integer',
        'is_enabled' => 'boolean',
        'auto_mitigate' => 'boolean',
    ];

    protected $appends = [
        'rule_uuid',
    ];

    public function getRuleUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
