<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperadminDidWarehouse extends Model
{
    use HasFactory;

    protected $table = 'superadmin_did_warehouse';

    protected $fillable = [
        'phone_number',
        'country_code',
        'state_province',
        'rate_center',
        'carrier_provider',
        'wholesale_monthly_cost',
        'retail_monthly_price',
        'status',
        'assigned_domain_uuid',
    ];

    protected $casts = [
        'wholesale_monthly_cost' => 'float',
        'retail_monthly_price' => 'float',
    ];

    public function assignedDomain()
    {
        return $this->belongsTo(Domain::class, 'assigned_domain_uuid', 'domain_uuid');
    }
}
