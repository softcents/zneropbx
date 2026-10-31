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
    protected $primaryKey = 'callback_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'callback_uuid',
        'domain_uuid',
        'queue_uuid',
        'caller_number',
        'caller_name',
        'status',
        'attempts',
        'scheduled_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function queue(): BelongsTo
    {
        return $this->belongsTo(CallCenterQueues::class, 'queue_uuid', 'call_center_queue_uuid');
    }
}
