<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelecomDestination extends Model
{
    use HasFactory;

    protected $table = 'telecom_destinations';
    protected $primaryKey = 'destination_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'destination_uuid',
        'carrier_uuid',
        'destination_name',
        'prefix',
        'cost_per_minute',
        'connect_fee',
        'rate_increment',
        'effective_date',
        'is_active',
    ];

    protected $casts = [
        'cost_per_minute' => 'float',
        'connect_fee' => 'float',
        'rate_increment' => 'integer',
        'is_active' => 'boolean',
        'effective_date' => 'datetime',
    ];

    public function carrier()
    {
        return $this->belongsTo(TelecomCarrier::class, 'carrier_uuid', 'carrier_uuid');
    }
}
