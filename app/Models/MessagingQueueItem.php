<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagingQueueItem extends Model
{
    use HasFactory;

    protected $table = 'messaging_queue_items';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'traffic_type',
        'priority_tier',
        'sender_id',
        'recipient_phone',
        'message_body',
        'segment_count',
        'voice_tts_enabled',
        'status',
        'provider_connector_uuid',
        'external_message_id',
        'dlr_status',
        'dlr_timestamp',
        'cost_amount',
        'retail_billed_amount',
        'retry_count',
    ];

    protected $casts = [
        'priority_tier' => 'integer',
        'segment_count' => 'integer',
        'voice_tts_enabled' => 'boolean',
        'cost_amount' => 'decimal:5',
        'retail_billed_amount' => 'decimal:5',
        'retry_count' => 'integer',
        'dlr_timestamp' => 'datetime',
    ];

    public function connector()
    {
        return $this->belongsTo(MessagingProviderConnector::class, 'provider_connector_uuid', 'uuid');
    }
}
