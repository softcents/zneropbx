<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StirShakenSigningLog extends Model
{
    use HasFactory;

    protected $table = 'stir_shaken_signing_logs';
    protected $primaryKey = 'log_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'log_uuid',
        'domain_uuid',
        'orig_tn',
        'dest_tn',
        'attestation',
        'origid_uuid',
        'passport_jwt',
        'identity_header',
        'verification_status',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];
}
