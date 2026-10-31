<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsBiCarrierQosBenchmark extends Model
{
    use HasFactory;

    protected $table = 'analytics_bi_carrier_qos_benchmarks';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'carrier_name',
        'asr_percent',
        'acd_seconds',
        'pdd_ms',
        'jitter_ms',
        'mos_score',
        'cost_per_minute_usd',
        'margin_percent',
        'total_minutes_billed',
        'total_revenue_usd',
        'sample_date',
    ];

    protected $casts = [
        'asr_percent' => 'decimal:2',
        'acd_seconds' => 'integer',
        'pdd_ms' => 'integer',
        'jitter_ms' => 'decimal:2',
        'mos_score' => 'decimal:2',
        'cost_per_minute_usd' => 'decimal:5',
        'margin_percent' => 'decimal:2',
        'total_minutes_billed' => 'integer',
        'total_revenue_usd' => 'decimal:2',
        'sample_date' => 'date',
    ];

    protected $appends = [
        'benchmark_uuid',
    ];

    public function getBenchmarkUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
