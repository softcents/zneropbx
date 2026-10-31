<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingTariffPlan extends Model
{
    use HasFactory;

    protected $table = 'billing_tariff_plans';
    protected $primaryKey = 'tariff_plan_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tariff_plan_uuid',
        'domain_uuid',
        'name',
        'currency',
        'initial_interval',
        'subsequent_interval',
        'connect_fee',
        'markup_percentage',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'initial_interval' => 'integer',
        'subsequent_interval' => 'integer',
        'connect_fee' => 'float',
        'markup_percentage' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function rates()
    {
        return $this->hasMany(BillingTariffRate::class, 'tariff_plan_uuid', 'tariff_plan_uuid')->orderBy('prefix', 'asc');
    }
}
