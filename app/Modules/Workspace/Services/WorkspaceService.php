<?php

namespace App\Modules\Workspace\Services;

use App\Modules\ContentStudio\Services\ContentStudioService;
use App\Modules\Workspace\Models\WorkspaceBlock;
use App\Modules\Workspace\Models\WorkspaceDatabase;
use App\Modules\Workspace\Models\WorkspaceDatabaseRow;
use App\Modules\Workspace\Models\WorkspaceDatabaseView;
use App\Modules\Workspace\Models\WorkspacePage;
use App\Modules\Workspace\Models\WorkspaceSpace;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WorkspaceService — enjin dokumen kolaboratif.
 *
 * inspired oleh https://github.com/AppFlowy-IO/AppFlowy
 *
 * Menyediakan:
 *   - ruang kerja, halaman dan blok boleh susun semula
 *   - pangkalan data dengan paparan (grid/table/kanban/kalendar)
 *   - versi sejarah kandungan (untukrollback)
 *   - komen dan sokongan penyuntingan
 */
class WorkspaceService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * ID tenant semasa.
     */
    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    /**
     * Cipta ruang kerja.
     *
     * @param  array<string, mixed>  $data
     */
    public function createSpace(array $data): WorkspaceSpace
    {
        return WorkspaceSpace::create(array_merge($data, [
            'slug' => $data['slug'] ?? Str::slug($data['name']).'-'.Str::random(4),
            'created_by' => $data['created_by'] ?? auth()->id(),
        ]));
    }

    /**
     * Cipta halaman dalam ruang kerja.
     *
     * @param  array<string, mixed>  $data
     */
    public function createPage(WorkspaceSpace $space, array $data): WorkspacePage
    {
        $halaman = WorkspacePage::create([
            'space_id' => $space->id,
            'title' => $data['title'] ?? 'Halaman tanpa tajuk',
            'slug' => $data['slug'] ?? Str::slug($data['title'] ?? 'halaman').'-'.Str::random(4),
            'icon' => $data['icon'] ?? null,
            'content' => $data['content'] ?? null,
            'created_by' => $data['created_by'] ?? auth()->id(),
            'updated_by' => $data['created_by'] ?? auth()->id(),
            'last_edited_at' => now(),
        ]);

        // Blok awal automatik daripada kandungan HTML/rtf ringkas.
        if (! empty($data['content'])) {
            $this->blocksFromContent($halaman, (string) $data['content']);
        } else {
            WorkspaceBlock::create([
                'page_id' => $halaman->id,
                'type' => WorkspaceBlock::TYPE_PARAGRAPH,
                'content' => '',
                'sort_order' => 0,
                'created_by' => $halaman->created_by,
            ]);
        }

        return $halaman->fresh();
    }

    /**
     * Kemas kini kandungan halaman — menyimpan versi sejarah.
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePage(WorkspacePage $page, array $data): WorkspacePage
    {
        // Simpan versi lama sebelum ditulis.
        $this->simpanVersi($page);

        $page->update(array_merge($data, [
            'updated_by' => auth()->id(),
            'last_edited_at' => now(),
        ]));

        return $page->fresh();
    }

    /**
     * Tambah blok ke halaman.
     *
     * @param  array<string, mixed>  $data
     */
    public function addBlock(WorkspacePage $page, array $data): WorkspaceBlock
    {
        $maks = (int) WorkspaceBlock::query()
            ->where('page_id', $page->id)
            ->whereNull('parent_id')
            ->max('sort_order');

        return WorkspaceBlock::create([
            'page_id' => $page->id,
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'] ?? WorkspaceBlock::TYPE_PARAGRAPH,
            'content' => $data['content'] ?? '',
            'data' => $data['data'] ?? null,
            'sort_order' => $data['sort_order'] ?? ($maks + 1),
            'is_checked' => (bool) ($data['is_checked'] ?? false),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Jana kandungan halaman AI melalui ContentStudio.
     *
     * Ini menyambungkan Workspace kepada enjin penulisan platform — tenant
     * boleh mulakan dokumen kosong dan biarkan AI mengisi kandungan.
     *
     * @param  array<string, mixed>  $brief
     * @return array{success: bool, body: ?string, mesej: string}
     */
    public function janaKandungan(WorkspacePage $page, array $brief = []): array
    {
        try {
            $keputusan = app(ContentStudioService::class)
                ->janaKandungan((int) $page->tenant_id, array_merge([
                    'title' => $page->title,
                    'platform' => 'document',
                    'content_type' => 'article',
                ], $brief));

            $badan = (string) ($keputusan->body ?? '');

            if ($badan === '') {
                return ['success' => false, 'body' => null, 'mesej' => 'AI tidak menghasilkan kandungan.'];
            }

            $this->addBlock($page, [
                'type' => WorkspaceBlock::TYPE_PARAGRAPH,
                'content' => $badan,
            ]);

            return ['success' => true, 'body' => $badan, 'mesej' => 'Kandungan dijana.'];
        } catch (\Throwable $e) {
            Log::warning('Workspace: jana kandungan AI gagal', [
                'page_id' => $page->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'body' => null, 'mesej' => $e->getMessage()];
        }
    }

    /**
     * Susun semula blok (bulk reorder).
     *
     * @param  array<int, int>  $susunan  senarai ID blok mengikut urutan baharu
     */
    public function reorderBlocks(WorkspacePage $page, array $susunan): int
    {
        $kira = 0;

        foreach ($susunan as $posisi => $id) {
            $kira += WorkspaceBlock::query()
                ->where('page_id', $page->id)
                ->whereKey($id)
                ->update(['sort_order' => $posisi, 'updated_by' => auth()->id()]);
        }

        $page->update(['last_edited_at' => now(), 'updated_by' => auth()->id()]);

        return $kira;
    }

    /**
     * Bintai pangkalan data + paparan lalai.
     */
    public function createDatabase(WorkspaceSpace $space, array $data): WorkspaceDatabase
    {
        $pangkalan = WorkspaceDatabase::create([
            'space_id' => $space->id,
            'name' => $data['name'] ?? 'Pangkalan Data Baharu',
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $kolum = $data['columns'] ?? [
            ['name' => 'Nama', 'type' => 'text', 'width' => 200],
            ['name' => 'Status', 'type' => 'select', 'width' => 120],
            ['name' => 'Tarikh', 'type' => 'date', 'width' => 120],
        ];

        WorkspaceDatabaseView::create([
            'database_id' => $pangkalan->id,
            'name' => 'Paparan Utama',
            'type' => 'grid',
            'columns' => $kolum,
            'is_default' => true,
            'sort_order' => 0,
        ]);

        return $pangkalan->fresh();
    }

    /**
     * Tambah baris data.
     *
     * @param  array<string, mixed>  $values
     */
    public function addRow(WorkspaceDatabase $pangkalan, array $values, ?WorkspaceDatabaseView $paparan = null): WorkspaceDatabaseRow
    {
        $maks = (int) WorkspaceDatabaseRow::query()
            ->where('database_id', $pangkalan->id)
            ->max('sort_order');

        return WorkspaceDatabaseRow::create([
            'database_id' => $pangkalan->id,
            'view_id' => $paparan?->id,
            'values' => $values,
            'sort_order' => $maks + 1,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Kemas kini baris data.
     *
     * @param  array<string, mixed>  $values
     */
    public function updateRow(WorkspaceDatabaseRow $baris, array $values): WorkspaceDatabaseRow
    {
        $baris->update([
            'values' => array_merge($baris->values ?? [], $values),
            'updated_by' => auth()->id(),
        ]);

        return $baris->fresh();
    }

    /**
     * Bina blok daripada kandungan ringkas (perenggan dipisah baris baharu).
     */
    protected function blocksFromContent(WorkspacePage $page, string $content): void
    {
        $baris = preg_split('/\R/u', $content) ?: [];
        $susunan = 0;

        foreach ($baris as $barisKandungan) {
            $teks = trim(strip_tags($barisKandungan));

            if ($teks === '') {
                continue;
            }

            $jenis = WorkspaceBlock::TYPE_PARAGRAPH;

            if (preg_match('/^#{1,6}\s+/u', $teks)) {
                $jenis = WorkspaceBlock::TYPE_HEADING;
                $teks = preg_replace('/^#{1,6}\s+/u', '', $teks) ?? $teks;
            } elseif (preg_match('/^[-*]\s+/u', $teks)) {
                $jenis = WorkspaceBlock::TYPE_BULLET;
                $teks = preg_replace('/^[-*]\s+/u', '', $teks) ?? $teks;
            } elseif (preg_match('/^\d+[.)]\s+/u', $teks)) {
                $jenis = WorkspaceBlock::TYPE_NUMBERED;
                $teks = preg_replace('/^\d+[.)]\s+/u', '', $teks) ?? $teks;
            } elseif (preg_match('/^\[[ xX]\]\s+/u', $teks)) {
                $jenis = WorkspaceBlock::TYPE_TODO;
                $teks = preg_replace('/^\[[ xX]\]\s+/u', '', $teks) ?? $teks;
            } elseif (preg_match('/^>\s+/u', $teks)) {
                $jenis = WorkspaceBlock::TYPE_QUOTE;
                $teks = preg_replace('/^>\s+/u', '', $teks) ?? $teks;
            }

            WorkspaceBlock::create([
                'page_id' => $page->id,
                'type' => $jenis,
                'content' => $teks,
                'sort_order' => $susunan++,
                'created_by' => $page->created_by,
                'updated_by' => $page->created_by,
            ]);
        }
    }

    /**
     * Simpan versi sejarah kandungan halaman.
     */
    protected function simpanVersi(WorkspacePage $page): void
    {
        WorkspacePageVersion::create([
            'page_id' => $page->id,
            'title' => $page->title,
            'content' => $page->content,
            'blocks_snapshot' => WorkspaceBlock::query()
                ->where('page_id', $page->id)
                ->orderBy('sort_order')
                ->get(['type', 'content', 'data', 'sort_order'])
                ->toArray(),
            'created_by' => auth()->id(),
        ]);
    }
}
