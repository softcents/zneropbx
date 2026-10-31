<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterBreakReason extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'contact_center_break_reasons';
    protected $primaryKey = 'break_reason_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'break_reason_uuid',
        'domain_uuid',
        'name',
        'code',
        'max_minutes',
        'is_paid',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
        'max_minutes' => 'integer',
    ];
}
