<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiVoiceKnowledgeDocument extends Model
{
    use HasFactory;

    protected $table = 'ai_voice_knowledge_documents';

    protected $fillable = [
        'uuid',
        'domain_uuid',
        'document_title',
        'category',
        'document_content',
        'chunk_count',
        'freshness_date',
        'vector_index_status',
        'access_tier',
    ];

    protected $casts = [
        'chunk_count' => 'integer',
        'freshness_date' => 'datetime',
    ];
}
