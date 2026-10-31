<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCenterCallback extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_callbacks';
    protected $primaryKey = 'contact_center_callback_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'contact_center_callback_uuid',
        'domain_uuid',
        'queue_uuid',
        'number',
        'status',
        'attempts',
        'next_attempt_at',
        'expires_at',
        'last_error',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'next_attempt_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $appends = [
        'caller_number',
        'scheduled_at',
    ];

    public function getCallerNumberAttribute(): string
    {
        return $this->number ?? '';
    }

    public function getScheduledAtAttribute()
    {
        return $this->next_attempt_at;
    }

    public function queue(): BelongsTo
    {
        return $this->belongsTo(CallCenterQueues::class, 'queue_uuid', 'call_center_queue_uuid');
    }
}
