<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiEcosystemWebhookLog extends Model
{
    use HasFactory;

    protected $table = 'api_ecosystem_webhook_logs';

    protected $fillable = [
        'uuid',
        'webhook_uuid',
        'domain_uuid',
        'event_type',
        'idempotency_key',
        'http_status',
        'payload_json',
        'request_signature',
        'latency_ms',
        'attempt_count',
        'delivery_status',
        'error_message',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'http_status' => 'integer',
        'latency_ms' => 'integer',
        'attempt_count' => 'integer',
    ];

    protected $appends = [
        'log_uuid',
    ];

    public function getLogUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }

    public function webhook()
    {
        return $this->belongsTo(ApiEcosystemWebhook::class, 'webhook_uuid', 'uuid');
    }
}
