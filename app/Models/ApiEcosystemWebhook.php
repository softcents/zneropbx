<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiEcosystemWebhook extends Model
{
    use HasFactory;

    protected $table = 'api_ecosystem_webhooks';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'credential_uuid',
        'name',
        'target_url',
        'signing_secret',
        'subscribed_events',
        'is_active',
        'consecutive_failures',
        'status',
        'total_delivered',
        'total_failed',
    ];

    protected $casts = [
        'subscribed_events' => 'array',
        'is_active' => 'boolean',
        'consecutive_failures' => 'integer',
        'total_delivered' => 'integer',
        'total_failed' => 'integer',
    ];

    protected $appends = [
        'webhook_uuid',
    ];

    public function getWebhookUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }

    public function logs()
    {
        return $this->hasMany(ApiEcosystemWebhookLog::class, 'webhook_uuid', 'uuid');
    }

    public function credential()
    {
        return $this->belongsTo(ApiEcosystemCredential::class, 'credential_uuid', 'uuid');
    }
}
