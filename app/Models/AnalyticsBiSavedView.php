<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsBiSavedView extends Model
{
    use HasFactory;

    protected $table = 'analytics_bi_saved_views';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'view_name',
        'category',
        'filter_criteria_json',
        'is_pinned',
        'created_by_user_uuid',
    ];

    protected $casts = [
        'filter_criteria_json' => 'array',
        'is_pinned' => 'boolean',
    ];

    protected $appends = [
        'view_uuid',
    ];

    public function getViewUuidAttribute()
    {
        return $this->attributes['uuid'] ?? null;
    }
}
