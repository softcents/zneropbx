<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierFailoverDrill extends Model
{
    use HasFactory;

    protected $table = 'carrier_failover_drills';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'drill_name',
        'trigger_type',
        'source_node',
        'target_node',
        'failover_duration_ms',
        'channels_reallocated',
        'measured_rto_sec',
        'measured_rpo_sec',
        'result_status',
        'executed_at',
        'logs',
    ];

    protected $casts = [
        'failover_duration_ms' => 'integer',
        'channels_reallocated' => 'integer',
        'measured_rto_sec' => 'decimal:2',
        'measured_rpo_sec' => 'decimal:2',
        'executed_at' => 'datetime',
    ];
}
