<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperadminAuditLog extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'superadmin_audit_logs';

    protected $fillable = [
        'actor_user_uuid',
        'actor_name',
        'action',
        'target_type',
        'target_name',
        'details',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public static function log(string $action, ?string $targetType = null, ?string $targetName = null, ?string $details = null)
    {
        $user = auth()->user();
        return self::create([
            'actor_user_uuid' => $user?->user_uuid,
            'actor_name' => $user?->username ?? 'Superadmin (CLI/API)',
            'action' => $action,
            'target_type' => $targetType,
            'target_name' => $targetName,
            'details' => $details,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }
}
