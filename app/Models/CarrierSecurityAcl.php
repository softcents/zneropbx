<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierSecurityAcl extends Model
{
    use HasFactory;

    protected $table = 'carrier_security_acls';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'acl_name',
        'cidr_network',
        'rule_action',
        'port_range',
        'is_active',
        'hits_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'hits_count' => 'integer',
    ];
}
