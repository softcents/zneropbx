<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxCallerIdRule extends Model
{
    use HasFactory;

    protected $table = 'pbx_caller_id_rules';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'rule_name',
        'scope_type',
        'scope_value',
        'caller_id_name',
        'caller_id_number',
        'emergency_caller_id_number',
        'normalization_format',
        'strip_digits',
        'prepend_digits',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'strip_digits' => 'integer',
        'priority' => 'integer',
    ];
}
