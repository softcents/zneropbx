<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCenterQueueSetting extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_queue_settings';
    protected $primaryKey = 'queue_setting_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'queue_setting_uuid',
        'domain_uuid',
        'queue_uuid',
        'sla_seconds',
        'sla_target_percentage',
        'max_wait_time_seconds',
        'abandon_alert_threshold',
        'smart_callback_enabled',
        'smart_callback_threshold_seconds',
        'vip_boost_score',
        'overflow_queue_uuid',
        'overflow_callers_count',
    ];

    protected $casts = [
        'sla_seconds' => 'integer',
        'sla_target_percentage' => 'float',
        'max_wait_time_seconds' => 'integer',
        'abandon_alert_threshold' => 'float',
        'smart_callback_enabled' => 'boolean',
        'smart_callback_threshold_seconds' => 'integer',
        'vip_boost_score' => 'integer',
        'overflow_callers_count' => 'integer',
    ];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(CallCenterQueues::class, 'queue_uuid', 'call_center_queue_uuid');
    }
}
