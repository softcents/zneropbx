<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelecomCarrier extends Model
{
    use HasFactory;

    protected $table = 'telecom_carriers';
    protected $primaryKey = 'carrier_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'carrier_uuid',
        'domain_uuid',
        'name',
        'carrier_code',
        'sip_proxy',
        'tech_prefix',
        'max_channels',
        'current_channels',
        'max_tps',
        'status',
        'asr_percentage',
        'acd_seconds',
        'pdd_ms',
        'consecutive_failures',
        'circuit_breaker_tripped',
        'quarantined_until',
        'priority',
        'weight',
        'is_active',
    ];

    protected $casts = [
        'max_channels' => 'integer',
        'current_channels' => 'integer',
        'max_tps' => 'integer',
        'asr_percentage' => 'float',
        'acd_seconds' => 'integer',
        'pdd_ms' => 'integer',
        'consecutive_failures' => 'integer',
        'circuit_breaker_tripped' => 'boolean',
        'priority' => 'integer',
        'weight' => 'integer',
        'is_active' => 'boolean',
        'quarantined_until' => 'datetime',
    ];

    public function destinations()
    {
        return $this->hasMany(TelecomDestination::class, 'carrier_uuid', 'carrier_uuid');
    }
}
