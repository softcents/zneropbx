<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagingRoutingPolicy extends Model
{
    use HasFactory;

    protected $table = 'messaging_routing_policies';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'policy_name',
        'traffic_type',
        'queue_priority',
        'primary_connector_uuid',
        'failover_connector_uuid',
        'prefix_match',
        'sender_id_type',
        'max_retries',
        'is_active',
    ];

    protected $casts = [
        'queue_priority' => 'integer',
        'max_retries' => 'integer',
        'is_active' => 'boolean',
    ];

    public function primaryConnector()
    {
        return $this->belongsTo(MessagingProviderConnector::class, 'primary_connector_uuid', 'uuid');
    }

    public function failoverConnector()
    {
        return $this->belongsTo(MessagingProviderConnector::class, 'failover_connector_uuid', 'uuid');
    }
}
