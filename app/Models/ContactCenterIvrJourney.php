<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCenterIvrJourney extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_ivr_journeys';
    protected $primaryKey = 'journey_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'journey_uuid',
        'domain_uuid',
        'ivr_flow_uuid',
        'caller_id_number',
        'caller_id_name',
        'xml_cdr_uuid',
        'path_taken',
        'drop_off_node',
        'contained',
        'duration_seconds',
    ];

    protected $casts = [
        'path_taken' => 'array',
        'contained' => 'boolean',
        'duration_seconds' => 'integer',
    ];

    public function flow(): BelongsTo
    {
        return $this->belongsTo(ContactCenterIvrFlow::class, 'ivr_flow_uuid', 'ivr_flow_uuid');
    }
}
