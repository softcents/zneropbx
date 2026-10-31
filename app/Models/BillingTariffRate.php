<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingTariffRate extends Model
{
    use HasFactory;

    protected $table = 'billing_tariff_rates';
    protected $primaryKey = 'rate_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'rate_uuid',
        'tariff_plan_uuid',
        'destination_name',
        'prefix',
        'rate_per_minute',
        'custom_connect_fee',
        'custom_initial_interval',
        'custom_subsequent_interval',
        'is_active',
    ];

    protected $casts = [
        'rate_per_minute' => 'float',
        'custom_connect_fee' => 'float',
        'custom_initial_interval' => 'integer',
        'custom_subsequent_interval' => 'integer',
        'is_active' => 'boolean',
    ];

    public function plan()
    {
        return $this->belongsTo(BillingTariffPlan::class, 'tariff_plan_uuid', 'tariff_plan_uuid');
    }
}
