<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxCallPermissionProfile extends Model
{
    use HasFactory;

    protected $table = 'pbx_call_permission_profiles';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'profile_name',
        'description',
        'allow_international',
        'allow_national',
        'allow_toll_free',
        'allow_premium_rate',
        'internal_only',
        'max_concurrent_calls',
        'time_restriction_type',
        'active_days',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected $casts = [
        'allow_international' => 'boolean',
        'allow_national' => 'boolean',
        'allow_toll_free' => 'boolean',
        'allow_premium_rate' => 'boolean',
        'internal_only' => 'boolean',
        'max_concurrent_calls' => 'integer',
        'active_days' => 'array',
        'is_active' => 'boolean',
    ];
}
