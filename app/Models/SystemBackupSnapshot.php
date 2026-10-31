<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemBackupSnapshot extends Model
{
    use HasFactory;

    protected $table = 'system_backup_snapshots';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'snapshot_name',
        'snapshot_type',
        'file_path',
        'file_size_bytes',
        'sha256_checksum',
        'status',
    ];

    protected $casts = [
        'file_size_bytes' => 'integer',
    ];

    protected $appends = [
        'snapshot_uuid',
    ];

    public function getSnapshotUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
