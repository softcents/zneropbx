<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContactCenterCustomer extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_customers';
    protected $primaryKey = 'customer_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'customer_uuid',
        'domain_uuid',
        'first_name',
        'last_name',
        'company',
        'phone',
        'email',
        'tier',
        'account_number',
        'status',
        'sentiment',
        'notes',
        'total_calls_count',
        'total_spend',
        'last_interaction_at',
        'custom_attributes',
    ];

    protected $casts = [
        'total_calls_count' => 'integer',
        'total_spend' => 'float',
        'last_interaction_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'full_name',
    ];

    public function getFullNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')) ?: 'Unnamed Customer';
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(ContactCenterTicket::class, 'customer_uuid', 'customer_uuid')->orderBy('created_at', 'desc');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(ContactCenterCustomerInteraction::class, 'customer_uuid', 'customer_uuid')->orderBy('created_at', 'desc');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }
}
