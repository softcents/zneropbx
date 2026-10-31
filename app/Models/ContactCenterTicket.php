<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactCenterTicket extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_tickets';
    protected $primaryKey = 'ticket_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'ticket_uuid',
        'domain_uuid',
        'customer_uuid',
        'assigned_agent_uuid',
        'ticket_number',
        'subject',
        'description',
        'category',
        'priority',
        'status',
        'source',
        'sla_due_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'sla_due_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ContactCenterCustomer::class, 'customer_uuid', 'customer_uuid');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(CallCenterAgents::class, 'assigned_agent_uuid', 'call_center_agent_uuid');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ContactCenterCustomerInteraction::class, 'ticket_uuid', 'ticket_uuid')->orderBy('created_at', 'desc');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }
}
