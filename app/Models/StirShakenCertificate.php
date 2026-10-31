<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StirShakenCertificate extends Model
{
    use HasFactory;

    protected $table = 'stir_shaken_certificates';
    protected $primaryKey = 'certificate_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'certificate_uuid',
        'domain_uuid',
        'cert_name',
        'x5u_url',
        'private_key',
        'public_cert',
        'spc_code',
        'tn_ranges',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'tn_ranges' => 'array',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];
}
