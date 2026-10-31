<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterCampaignLead extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'contact_center_campaign_leads';
    protected $primaryKey = 'lead_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'lead_uuid',
        'campaign_uuid',
        'domain_uuid',
        'phone_number',
        'first_name',
        'last_name',
        'company',
        'status',
        'attempts',
        'last_dialed_at',
        'call_uuid',
        'disposition',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'last_dialed_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(ContactCenterCampaign::class, 'campaign_uuid', 'campaign_uuid');
    }
}
