<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsBiScheduledExport extends Model
{
    use HasFactory;

    protected $table = 'analytics_bi_scheduled_exports';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'report_title',
        'format',
        'frequency',
        'recipient_email',
        'last_exported_at',
        'next_run_at',
        'status',
        'total_runs',
    ];

    protected $casts = [
        'last_exported_at' => 'datetime',
        'next_run_at' => 'datetime',
        'total_runs' => 'integer',
    ];

    protected $appends = [
        'export_uuid',
    ];

    public function getExportUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
