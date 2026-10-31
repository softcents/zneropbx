<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbxRecordingRetentionRule extends Model
{
    use HasFactory;

    protected $table = 'pbx_recording_retention_rules';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'rule_name',
        'category',
        'retention_days',
        'auto_purge_enabled',
        'storage_tier',
        'encryption_algorithm',
        'kms_key_id',
        'is_active',
    ];

    protected $casts = [
        'retention_days' => 'integer',
        'auto_purge_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];
}
