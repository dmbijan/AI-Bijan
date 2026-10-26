<?php

namespace App\Modules\PersonalAssistant\Services;

use App\Modules\PersonalAssistant\Models\KnowledgeChunk;
use App\Modules\PersonalAssistant\Models\KnowledgeDocument;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Vector store tenant (BAB 17A.4 / T-08).
 *
 * Pengasingan: SEMUA carian ditapis `tenant_id`. Tiada carian silang tenant
 * walaupun vektor sama — pagar ini diuji.
 *
 * Skala: carian kosinus dalam PHP. Untuk >50k serpihan, tukar ke Qdrant
 * dengan namespace `{tenant_id}_documents` — antara muka kelas ini kekal.
 */
class VectorStoreService
{
    public function __construct(private EmbeddingService $embedder) {}

    /** Simpan/replace dokumen + jana embedding semua serpihan. */
    public function simpan(string $tajuk, string $kandungan, string $jenis = 'custom', ?string $sumber = null): KnowledgeDocument
    {
        $hash = hash('sha256', $tajuk.'|'.$kandungan);
        $serpihan = $this->embedder->pecah($kandungan);

        return DB::transaction(function () use ($tajuk, $kandungan, $jenis, $sumber, $hash, $serpihan) {
            $doc = KnowledgeDocument::updateOrCreate(
                ['content_hash' => $hash],
                [
                    'title' => $tajuk,
                    'doc_type' => $jenis,
                    'source' => $sumber,
                    'content' => $kandungan,
                    'chunk_count' => count($serpihan),
                    'is_active' => true,
                    'uploaded_by' => auth()->id(),
                ]
            );

            // Jana semula serpihan (idempoten).
            $doc->chunks()->delete();
            foreach ($serpihan as $i => $teks) {
                $vektor = $this->embedder->embed($teks);
                KnowledgeChunk::create([
                    'document_id' => $doc->id,
                    'chunk_index' => $i,
                    'text' => $teks,
                    'embedding' => json_encode($vektor),
                    'dimensions' => count($vektor),
                    'embedding_model' => $this->embedder->model(),
                ]);
            }

            app(AuditLogger::class)->log('knowledge.document_indexed', $doc, null, [
                'doc_type' => $jenis,
                'chunks' => count($serpihan),
            ]);

            return $doc;
        });
    }

    /**
     * Carian semantik dalam TENANT SEMASA sahaja.
     *
     * @return array<int, array{document_id: int, title: string, text: string, score: float, doc_type: string}>
     */
    public function cari(string $pertanyaan, int $topK = 5): array
    {
        $vektorSoalan = $this->embedder->embed($pertanyaan);
        $model = $this->embedder->model();

        // Hanya bandingkan vektor dari model sama.
        $calon = KnowledgeChunk::query()
            ->whereNotNull('embedding')
            ->where('embedding_model', $model)
            ->whereHas('document', fn ($q) => $q->where('is_active', true))
            ->get(['id', 'document_id', 'text', 'embedding']);

        $skor = [];

        foreach ($calon as $c) {
            $v = $c->vektor();
            if (! is_array($v) || $v === []) {
                continue;
            }
            $s = $this->embedder->kosinus($vektorSoalan, $v);
            if ($s > 0) {
                $skor[] = ['chunk' => $c, 'score' => $s];
            }
        }

        usort($skor, fn ($a, $b) => $b['score'] <=> $a['score']);
        $skor = array_slice($skor, 0, max(1, $topK));

        $idDokumen = collect($skor)->map(fn ($s) => $s['chunk']->document_id)->unique()->values()->all();

        $dokumen = KnowledgeDocument::whereIn('id', $idDokumen)
            ->get(['id', 'title', 'doc_type'])
            ->keyBy('id');

        return array_map(fn ($s) => [
            'document_id' => $s['chunk']->document_id,
            'title' => $dokumen[$s['chunk']->document_id]->title ?? '',
            'doc_type' => $dokumen[$s['chunk']->document_id]->doc_type ?? '',
            'text' => $s['chunk']->text,
            'score' => round($s['score'], 4),
        ], $skor);
    }

    /** Statistik indeks untuk papan pemuka. */
    public function statistik(): array
    {
        return [
            'documents' => KnowledgeDocument::where('is_active', true)->count(),
            'chunks' => KnowledgeChunk::count(),
            'embedded' => KnowledgeChunk::whereNotNull('embedding')->count(),
            'model' => $this->embedder->model(),
            'dimensions' => $this->embedder->dimensi(),
        ];
    }

    /** Nyahaktif tanpa memadam (jejak audit). */
    public function nyahaktif(int $id): void
    {
        $doc = KnowledgeDocument::findOrFail($id);
        $doc->update(['is_active' => false]);
        app(AuditLogger::class)->log('knowledge.document_deactivated', $doc);
    }
}
