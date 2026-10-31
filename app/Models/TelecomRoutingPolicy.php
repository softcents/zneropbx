<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelecomRoutingPolicy extends Model
{
    use HasFactory;

    protected $table = 'telecom_routing_policies';
    protected $primaryKey = 'policy_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'policy_uuid',
        'domain_uuid',
        'name',
        'strategy',
        'min_asr',
        'max_pdd_ms',
        'failover_limit',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'min_asr' => 'float',
        'max_pdd_ms' => 'integer',
        'failover_limit' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];
}
