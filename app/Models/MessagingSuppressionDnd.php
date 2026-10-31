<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagingSuppressionDnd extends Model
{
    use HasFactory;

    protected $table = 'messaging_suppression_dnd';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'phone_number',
        'reason',
        'opt_out_timestamp',
        'source_channel',
        'is_active',
    ];

    protected $casts = [
        'opt_out_timestamp' => 'datetime',
        'is_active' => 'boolean',
    ];
}
