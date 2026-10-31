<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCenterQaScorecard extends Model
{
    use HasFactory, \App\Models\Traits\TraitUuid;

    protected $table = 'contact_center_qa_scorecards';
    protected $primaryKey = 'qa_scorecard_uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'qa_scorecard_uuid',
        'domain_uuid',
        'name',
        'description',
        'passing_score',
        'sections',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'passing_score' => 'float',
        'sections' => 'array',
        'is_active' => 'boolean',
    ];

    public function evaluations()
    {
        return $this->hasMany(ContactCenterQaEvaluation::class, 'scorecard_uuid', 'qa_scorecard_uuid');
    }
}
