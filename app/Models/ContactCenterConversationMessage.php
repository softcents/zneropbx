<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactCenterConversationMessage extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_conversation_messages';
    protected $primaryKey = 'conversation_message_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'conversation_message_uuid',
        'conversation_uuid',
        'domain_uuid',
        'sender_type',
        'sender_id',
        'sender_name',
        'body',
        'attachments',
        'status',
        'delivery_provider',
        'external_message_id',
    ];

    protected $casts = [
        'attachments' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ContactCenterConversation::class, 'conversation_uuid', 'conversation_uuid');
    }
}
