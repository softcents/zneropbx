<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxEmergencyLocation extends Model
{
    use HasFactory;

    protected $table = 'pbx_emergency_locations';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'location_name',
        'elin_number',
        'street_address',
        'unit_suite',
        'city',
        'state_province',
        'postal_code',
        'country',
        'psap_name',
        'notification_emails',
        'notification_phones',
        'karis_law_compliant',
        'ray_baums_compliant',
        'status',
        'test_last_completed_at',
    ];

    protected $casts = [
        'notification_emails' => 'array',
        'notification_phones' => 'array',
        'karis_law_compliant' => 'boolean',
        'ray_baums_compliant' => 'boolean',
        'test_last_completed_at' => 'datetime',
    ];
}
