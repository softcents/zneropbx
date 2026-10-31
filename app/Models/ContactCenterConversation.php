<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactCenterConversation extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_conversations';
    protected $primaryKey = 'conversation_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'conversation_uuid',
        'domain_uuid',
        'customer_uuid',
        'assigned_agent_uuid',
        'channel',
        'customer_identifier',
        'customer_name',
        'subject',
        'status',
        'priority',
        'unread_count',
        'last_message_at',
        'last_message_preview',
    ];

    protected $casts = [
        'unread_count' => 'integer',
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(ContactCenterConversationMessage::class, 'conversation_uuid', 'conversation_uuid')->orderBy('created_at', 'asc');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(ContactCenterCustomer::class, 'customer_uuid', 'customer_uuid');
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(CallCenterAgents::class, 'assigned_agent_uuid', 'call_center_agent_uuid');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }
}
