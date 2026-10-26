<?php

namespace App\Modules\PersonalAssistant\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Dokumen pengetahuan tenant (BAB 17A). */
class KnowledgeDocument extends Model
{
    use BelongsToTenant;

    protected $table = 'knowledge_documents';

    protected $fillable = [
        'tenant_id',
        'title',
        'doc_type',
        'source',
        'content',
        'content_hash',
        'chunk_count',
        'is_active',
        'uploaded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'chunk_count' => 'integer',
    ];

    public const JENIS = ['sop', 'policy', 'contract', 'product_info', 'training', 'notes', 'custom'];

    public function chunks()
    {
        return $this->hasMany(KnowledgeChunk::class, 'document_id');
    }
}
