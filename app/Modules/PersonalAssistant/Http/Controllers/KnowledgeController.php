<?php

namespace App\Modules\PersonalAssistant\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PersonalAssistant\Models\KnowledgeDocument;
use App\Modules\PersonalAssistant\Services\VectorStoreService;
use Illuminate\Http\Request;

/**
 * Pangkalan pengetahuan tenant (BAB 17A.4 / T-08).
 *
 * Dokumen diindeks ke vector store dan digunakan oleh Context Builder AI.
 * Setiap carian terasing kepada tenant semasa.
 */
class KnowledgeController extends Controller
{
    public function __construct(private VectorStoreService $store) {}

    public function index(Request $r)
    {
        $q = KnowledgeDocument::query()
            ->when($r->query('doc_type'), fn ($q, $t) => $q->where('doc_type', $t))
            ->when($r->has('is_active'), fn ($q) => $q->where('is_active', $r->boolean('is_active')))
            ->select(['id', 'title', 'doc_type', 'source', 'chunk_count', 'is_active', 'created_at'])
            ->orderByDesc('id');

        return response()->json($q->paginate((int) $r->query('per_page', 25)));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string|min:20|max:200000',
            'doc_type' => 'nullable|in:'.implode(',', KnowledgeDocument::JENIS),
            'source' => 'nullable|string|max:255',
        ]);

        $doc = $this->store->simpan(
            $d['title'],
            $d['content'],
            $d['doc_type'] ?? 'custom',
            $d['source'] ?? null
        );

        return response()->json([
            'id' => $doc->id,
            'title' => $doc->title,
            'chunk_count' => $doc->chunk_count,
        ], 201);
    }

    public function show(int $id)
    {
        $doc = KnowledgeDocument::withCount('chunks')
            ->findOrFail($id);

        return response()->json([
            'document' => $doc->only(['id', 'title', 'doc_type', 'source', 'chunk_count', 'is_active', 'created_at']),
            'chunks_indexed' => $doc->chunks_count,
        ]);
    }

    public function destroy(int $id)
    {
        $this->store->nyahaktif($id);

        return response()->json(['message' => 'Dokumen dinyahaktifkan (indeks dikekalkan untuk audit).']);
    }

    /** Carian semantik — digunakan oleh UI dan untuk ujian. */
    public function search(Request $r)
    {
        $d = $r->validate([
            'q' => 'required|string|min:2|max:500',
            'top_k' => 'nullable|integer|min:1|max:20',
        ]);

        $hasil = $this->store->cari($d['q'], (int) ($d['top_k'] ?? 5));

        return response()->json(['query' => $d['q'], 'hits' => $hasil, 'count' => count($hasil)]);
    }

    public function stats()
    {
        return response()->json($this->store->statistik());
    }
}
