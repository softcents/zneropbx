<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiEcosystemIdempotencyKey extends Model
{
    use HasFactory;

    protected $table = 'api_ecosystem_idempotency_keys';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'idempotency_key',
        'endpoint',
        'request_hash',
        'http_response_code',
        'response_payload',
        'expires_at',
    ];

    protected $casts = [
        'response_payload' => 'array',
        'http_response_code' => 'integer',
        'expires_at' => 'datetime',
    ];
}
