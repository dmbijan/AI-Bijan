<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Workspace\Models\WorkspaceBlock;
use App\Modules\Workspace\Models\WorkspaceComment;
use App\Modules\Workspace\Models\WorkspaceDatabase;
use App\Modules\Workspace\Models\WorkspaceDatabaseRow;
use App\Modules\Workspace\Models\WorkspaceDatabaseView;
use App\Modules\Workspace\Models\WorkspacePage;
use App\Modules\Workspace\Models\WorkspacePageVersion;
use App\Modules\Workspace\Models\WorkspaceSpace;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Workspace (dokumen & pangkalan data kolaboratif).
 *
 * Ditambah 8 Nov 2026. Dokumen dalaman syarikat (pelan, SOP, mesyuarat)
 * adalah data peribadi dan rahsia perniagaan.
 */
class WorkspaceTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Workspace';

    protected array $model = [
        WorkspaceSpace::class,
        WorkspacePage::class,
        WorkspaceBlock::class,
        WorkspaceComment::class,
        WorkspacePageVersion::class,
        WorkspaceDatabase::class,
        WorkspaceDatabaseView::class,
        WorkspaceDatabaseRow::class,
    ];

    protected ?string $laluanApi = '/api/v1/workspace/spaces';

    protected ?string $namaRahsia = 'Ruang Kerja Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        $space = WorkspaceSpace::create([
            'name' => 'Ruang Kerja Anak '.$n,
            'slug' => 'ruang-kerja-anak-'.$n,
            'kind' => WorkspaceSpace::KIND_DOCUMENT,
        ]);

        $page = WorkspacePage::create([
            'space_id' => $space->id,
            'title' => 'Halaman Anak '.$n,
            'slug' => 'halaman-anak-'.$n,
        ]);

        $block = WorkspaceBlock::create([
            'page_id' => $page->id,
            'type' => WorkspaceBlock::TYPE_PARAGRAPH,
            'content' => 'Kandungan Anak '.$n,
            'sort_order' => 0,
        ]);

        switch ($this->rekodSemasa) {
            case WorkspaceSpace::class:
                return [
                    'name' => 'Ruang Kerja Rahsia Tenant Satu '.$n,
                    'slug' => 'ruang-kerja-rahsia-'.$n,
                    'kind' => WorkspaceSpace::KIND_DOCUMENT,
                ];

            case WorkspacePage::class:
                return [
                    'space_id' => $space->id,
                    'title' => 'Halaman Rahsia Tenant Satu '.$n,
                    'slug' => 'halaman-rahsia-'.$n,
                ];

            case WorkspaceBlock::class:
                return [
                    'page_id' => $page->id,
                    'type' => WorkspaceBlock::TYPE_PARAGRAPH,
                    'content' => 'Kandungan Rahsia Tenant Satu '.$n,
                    'sort_order' => 1,
                ];

            case WorkspaceComment::class:
                return [
                    'page_id' => $page->id,
                    'block_id' => $block->id,
                    'body' => 'Komen Rahsia Tenant Satu '.$n,
                ];

            case WorkspacePageVersion::class:
                return [
                    'page_id' => $page->id,
                    'title' => 'Halaman Rahsia Tenant Satu '.$n,
                    'content' => 'Versi Rahsia Tenant Satu '.$n,
                    'version' => 1,
                ];

            case WorkspaceDatabase::class:
                return [
                    'space_id' => $space->id,
                    'name' => 'Pangkalan Data Rahsia Tenant Satu '.$n,
                ];

            case WorkspaceDatabaseView::class:
                $db = WorkspaceDatabase::create([
                    'space_id' => $space->id,
                    'name' => 'Pangkalan Data Anak '.$n,
                ]);

                return [
                    'database_id' => $db->id,
                    'name' => 'Paparan Rahsia Tenant Satu '.$n,
                    'type' => 'grid',
                    'is_default' => true,
                ];

            default:
                $db = WorkspaceDatabase::create([
                    'space_id' => $space->id,
                    'name' => 'Pangkalan Data Baris '.$n,
                ]);

                return [
                    'database_id' => $db->id,
                    'values' => ['Nama' => 'Baris Rahsia Tenant Satu '.$n],
                    'sort_order' => 0,
                ];
        }
    }
}
