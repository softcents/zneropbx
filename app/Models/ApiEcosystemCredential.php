<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiEcosystemCredential extends Model
{
    use HasFactory;

    protected $table = 'api_ecosystem_credentials';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'name',
        'key_id',
        'secret_hash',
        'secret_preview',
        'scopes',
        'rate_limit_per_minute',
        'status',
        'last_used_at',
        'expires_at',
    ];

    protected $casts = [
        'scopes' => 'array',
        'rate_limit_per_minute' => 'integer',
        'last_used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'credential_uuid',
    ];

    public function getCredentialUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }

    public function webhooks()
    {
        return $this->hasMany(ApiEcosystemWebhook::class, 'credential_uuid', 'uuid');
    }
}
