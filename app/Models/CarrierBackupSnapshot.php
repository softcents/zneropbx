<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarrierBackupSnapshot extends Model
{
    use HasFactory;

    protected $table = 'carrier_backup_snapshots';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'snapshot_name',
        'snapshot_type',
        'file_size_bytes',
        'storage_target',
        'sha256_checksum',
        'is_verified',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
        'is_verified' => 'boolean',
    ];
}
