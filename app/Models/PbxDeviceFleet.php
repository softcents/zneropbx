<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxDeviceFleet extends Model
{
    use HasFactory;

    protected $table = 'pbx_device_fleet_inventory';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'mac_address',
        'vendor',
        'model',
        'firmware_version',
        'assigned_extension',
        'ip_address',
        'transport',
        'sip_status',
        'rtt_ms',
        'last_provisioned_at',
        'config_version',
    ];

    protected $casts = [
        'rtt_ms' => 'integer',
        'last_provisioned_at' => 'datetime',
    ];
}
