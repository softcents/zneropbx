<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelecomRouteSimulationLog extends Model
{
    use HasFactory;

    protected $table = 'telecom_route_simulation_logs';
    protected $primaryKey = 'log_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'log_uuid',
        'domain_uuid',
        'destination_number',
        'normalized_prefix',
        'policy_applied',
        'routes_evaluated',
        'primary_carrier_uuid',
        'failover_carrier_uuids',
        'freeswitch_dialstring',
        'decision_trace',
    ];

    protected $casts = [
        'routes_evaluated' => 'array',
        'failover_carrier_uuids' => 'array',
        'decision_trace' => 'array',
    ];

    public function primaryCarrier()
    {
        return $this->belongsTo(TelecomCarrier::class, 'primary_carrier_uuid', 'carrier_uuid');
    }
}
