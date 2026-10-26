<?php

namespace App\Modules\PersonalAssistant\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Serpihan dokumen + embedding (BAB 17A.4). */
class KnowledgeChunk extends Model
{
    use BelongsToTenant;

    protected $table = 'knowledge_chunks';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'chunk_index',
        'text',
        'embedding',
        'dimensions',
        'embedding_model',
    ];

    protected $hidden = ['embedding'];

    protected $casts = [
        'chunk_index' => 'integer',
        'dimensions' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(KnowledgeDocument::class, 'document_id');
    }

    /** @return array<int, float>|null */
    public function vektor(): ?array
    {
        return $this->embedding ? json_decode($this->embedding, true) : null;
    }
}
