<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactCenterIvrFlow extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_ivr_flows';
    protected $primaryKey = 'ivr_flow_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ivr_flow_uuid',
        'domain_uuid',
        'name',
        'extension',
        'description',
        'status',
        'version',
        'language_default',
        'languages_supported',
        'vip_boost_enabled',
        'business_hours_schedule_uuid',
        'nodes_definition',
        'containment_rate_target',
        'total_calls_handled',
    ];

    protected $casts = [
        'version' => 'integer',
        'languages_supported' => 'array',
        'vip_boost_enabled' => 'boolean',
        'nodes_definition' => 'array',
        'containment_rate_target' => 'float',
        'total_calls_handled' => 'integer',
    ];

    public function journeys(): HasMany
    {
        return $this->hasMany(ContactCenterIvrJourney::class, 'ivr_flow_uuid', 'ivr_flow_uuid')->orderBy('created_at', 'desc');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }
}
