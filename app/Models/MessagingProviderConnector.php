<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagingProviderConnector extends Model
{
    use HasFactory;

    protected $table = 'messaging_provider_connectors';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'provider_name',
        'protocol',
        'smpp_system_id',
        'smpp_bind_mode',
        'host',
        'port',
        'tps_limit',
        'enquire_link_interval_sec',
        'bind_status',
        'success_rate_pct',
        'cost_per_segment',
        'is_active',
        'health_last_checked_at',
    ];

    protected $casts = [
        'port' => 'integer',
        'tps_limit' => 'integer',
        'enquire_link_interval_sec' => 'integer',
        'success_rate_pct' => 'decimal:2',
        'cost_per_segment' => 'decimal:5',
        'is_active' => 'boolean',
        'health_last_checked_at' => 'datetime',
    ];
}
