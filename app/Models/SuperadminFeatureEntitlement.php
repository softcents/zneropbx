<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuperadminFeatureEntitlement extends Model
{
    use HasFactory;

    protected $table = 'superadmin_feature_entitlements';
    protected $primaryKey = 'domain_uuid';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'domain_uuid',
        'package_tier',
        'feature_ai_agents',
        'feature_contact_center',
        'feature_stir_shaken',
        'feature_omnichannel',
        'feature_developer_api',
        'feature_video_conference',
        'feature_call_recording_ai',
    ];

    protected $casts = [
        'feature_ai_agents' => 'boolean',
        'feature_contact_center' => 'boolean',
        'feature_stir_shaken' => 'boolean',
        'feature_omnichannel' => 'boolean',
        'feature_developer_api' => 'boolean',
        'feature_video_conference' => 'boolean',
        'feature_call_recording_ai' => 'boolean',
    ];

    public function domain()
    {
        return $this->belongsTo(Domain::class, 'domain_uuid', 'domain_uuid');
    }
}
