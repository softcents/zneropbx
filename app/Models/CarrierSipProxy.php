<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierSipProxy extends Model
{
    use HasFactory;

    protected $table = 'carrier_sip_proxies';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'proxy_name',
        'vip_address',
        'listen_interface',
        'balancing_algorithm',
        'options_ping_interval_sec',
        'options_ping_timeout_ms',
        'failure_threshold_count',
        'status',
        'active_nodes_count',
        'quarantined_nodes_count',
    ];

    protected $casts = [
        'options_ping_interval_sec' => 'integer',
        'options_ping_timeout_ms' => 'integer',
        'failure_threshold_count' => 'integer',
        'active_nodes_count' => 'integer',
        'quarantined_nodes_count' => 'integer',
    ];
}
