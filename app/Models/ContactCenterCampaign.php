<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterCampaign extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contact_center_campaigns';
    protected $primaryKey = 'campaign_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'campaign_uuid',
        'domain_uuid',
        'name',
        'dialer_mode',
        'status',
        'queue_uuid',
        'contact_list_uuid',
        'caller_id_name',
        'caller_id_number',
        'pacing_ratio',
        'max_abandon_rate',
        'ring_timeout_seconds',
        'total_leads',
        'dialed_leads',
        'connected_leads',
        'abandoned_leads',
        'description',
    ];

    protected $casts = [
        'pacing_ratio' => 'float',
        'max_abandon_rate' => 'float',
        'ring_timeout_seconds' => 'integer',
        'total_leads' => 'integer',
        'dialed_leads' => 'integer',
        'connected_leads' => 'integer',
        'abandoned_leads' => 'integer',
    ];

    public function queue()
    {
        return $this->belongsTo(CallCenterQueues::class, 'queue_uuid', 'call_center_queue_uuid');
    }

    public function leads()
    {
        return $this->hasMany(ContactCenterCampaignLead::class, 'campaign_uuid', 'campaign_uuid');
    }
}
