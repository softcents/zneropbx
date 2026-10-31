<?php

namespace App\Models;

use App\Models\Traits\TraitUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupervisorScope extends Model
{
    use HasFactory, TraitUuid;

    protected $table = 'supervisor_scopes';
    protected $primaryKey = 'scope_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'scope_uuid',
        'domain_uuid',
        'user_uuid',
        'role_name',
        'all_queues',
        'assigned_queues',
        'can_listen',
        'can_whisper',
        'can_barge',
        'can_takeover',
        'can_force_logout',
    ];

    protected $casts = [
        'all_queues' => 'boolean',
        'assigned_queues' => 'array',
        'can_listen' => 'boolean',
        'can_whisper' => 'boolean',
        'can_barge' => 'boolean',
        'can_takeover' => 'boolean',
        'can_force_logout' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_uuid', 'user_uuid');
    }
}
