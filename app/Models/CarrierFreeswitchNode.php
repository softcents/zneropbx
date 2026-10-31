<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierFreeswitchNode extends Model
{
    use HasFactory;

    protected $table = 'carrier_freeswitch_nodes';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'node_name',
        'node_role',
        'region',
        'private_ip',
        'public_ip',
        'sip_port',
        'esl_port',
        'status',
        'max_channels_capacity',
        'active_channels',
        'cps_capacity',
        'current_cps',
        'cpu_usage_pct',
        'memory_usage_pct',
        'rtt_ms',
        'uptime_seconds',
        'esl_connected',
        'supports_transcoding',
        'supports_webrtc',
        'supports_conferencing',
        'drain_mode',
        'health_last_probed_at',
    ];

    protected $casts = [
        'sip_port' => 'integer',
        'esl_port' => 'integer',
        'max_channels_capacity' => 'integer',
        'active_channels' => 'integer',
        'cps_capacity' => 'integer',
        'current_cps' => 'decimal:2',
        'cpu_usage_pct' => 'integer',
        'memory_usage_pct' => 'integer',
        'rtt_ms' => 'integer',
        'uptime_seconds' => 'integer',
        'esl_connected' => 'boolean',
        'supports_transcoding' => 'boolean',
        'supports_webrtc' => 'boolean',
        'supports_conferencing' => 'boolean',
        'drain_mode' => 'boolean',
        'health_last_probed_at' => 'datetime',
    ];
}
