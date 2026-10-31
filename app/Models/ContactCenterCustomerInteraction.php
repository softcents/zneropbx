<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCenterCustomerInteraction extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_customer_interactions';
    protected $primaryKey = 'interaction_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'interaction_uuid',
        'domain_uuid',
        'customer_uuid',
        'agent_uuid',
        'ticket_uuid',
        'channel',
        'direction',
        'summary',
        'details',
        'sentiment',
        'duration_seconds',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ContactCenterCustomer::class, 'customer_uuid', 'customer_uuid');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(ContactCenterTicket::class, 'ticket_uuid', 'ticket_uuid');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(CallCenterAgents::class, 'agent_uuid', 'call_center_agent_uuid');
    }
}
