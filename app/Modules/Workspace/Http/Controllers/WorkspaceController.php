<?php

namespace App\Modules\Workspace\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Workspace\Models\WorkspaceBlock;
use App\Modules\Workspace\Models\WorkspaceComment;
use App\Modules\Workspace\Models\WorkspaceDatabase;
use App\Modules\Workspace\Models\WorkspaceDatabaseRow;
use App\Modules\Workspace\Models\WorkspaceDatabaseView;
use App\Modules\Workspace\Models\WorkspacePage;
use App\Modules\Workspace\Models\WorkspacePageVersion;
use App\Modules\Workspace\Models\WorkspaceSpace;
use App\Modules\Workspace\Services\WorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WorkspaceController extends Controller
{
    public function __construct(private WorkspaceService $workspace) {}

    /**
     * Senarai ruang kerja.
     */
    public function index(Request $request): JsonResponse
    {
        $query = WorkspaceSpace::query()
            ->whereNull('parent_id')
            ->orderByDesc('is_favorite')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('kind')) {
            $query->where('kind', $request->input('kind'));
        }

        if (! $request->boolean('include_archived')) {
            $query->where('is_archived', false);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
            'meta' => ['jenis' => WorkspaceSpace::jenis()],
        ]);
    }

    /**
     * Cipta ruang kerja.
     */
    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:'.config('workspace.limits.max_title_length', 250)],
            'description' => ['nullable', 'string', 'max:1000'],
            'kind' => ['nullable', 'string', 'in:document,database,board,folder'],
            'icon' => ['nullable', 'string', 'max:40'],
            'color' => ['nullable', 'string', 'max:20'],
            'parent_id' => ['nullable', 'integer', 'exists:workspace_spaces,id'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $space = $this->workspace->createSpace($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ruang kerja berjaya dicipta.',
            'data' => $space,
        ], 201);
    }

    /**
     * Kemas kini ruang kerja.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $space = WorkspaceSpace::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:1000'],
            'kind' => ['nullable', 'string', 'in:document,database,board,folder'],
            'icon' => ['nullable', 'string', 'max:40'],
            'color' => ['nullable', 'string', 'max:20'],
            'is_favorite' => ['nullable', 'boolean'],
            'is_archived' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $space->update($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Ruang kerja berjaya dikemas kini.',
            'data' => $space->fresh(),
        ]);
    }

    /**
     * Buang ruang kerja.
     */
    public function destroy(int $id): JsonResponse
    {
        WorkspaceSpace::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ruang kerja berjaya dibuang.',
        ]);
    }

    /**
     * Senarai halaman dalam ruang kerja.
     */
    public function pages(int $id): JsonResponse
    {
        $space = WorkspaceSpace::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $space->pages()->orderBy('sort_order')->get(),
        ]);
    }

    /**
     * Cipta halaman.
     */
    public function storePage(Request $request, int $id): JsonResponse
    {
        $space = WorkspaceSpace::findOrFail($id);

        $v = Validator::make($request->all(), [
            'title' => ['nullable', 'string', 'max:250'],
            'content' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:40'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $page = $this->workspace->createPage($space, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Halaman berjaya dicipta.',
            'data' => $page,
        ], 201);
    }

    /**
     * Butiran halaman dengan blok.
     */
    public function showPage(int $id): JsonResponse
    {
        $page = WorkspacePage::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'page' => $page,
                'blocks' => $page->blocks()->get(),
                'comments' => $page->comments()->where('is_resolved', false)->get(),
            ],
        ]);
    }

    /**
     * Kemas kini halaman.
     */
    public function updatePage(Request $request, int $id): JsonResponse
    {
        $page = WorkspacePage::findOrFail($id);

        $v = Validator::make($request->all(), [
            'title' => ['sometimes', 'string', 'max:250'],
            'content' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:40'],
            'cover_url' => ['nullable', 'string', 'max:500'],
            'is_full_width' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $page = $this->workspace->updatePage($page, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Halaman berjaya dikemas kini.',
            'data' => $page,
        ]);
    }

    /**
     * Buang halaman.
     */
    public function destroyPage(int $id): JsonResponse
    {
        WorkspacePage::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Halaman berjaya dibuang.',
        ]);
    }

    /**
     * Tambah blok.
     */
    public function storeBlock(Request $request, int $id): JsonResponse
    {
        $page = WorkspacePage::findOrFail($id);

        $v = Validator::make($request->all(), [
            'type' => ['nullable', 'string', 'in:paragraph,heading,bullet,numbered,todo,quote,code,image,table,callout,divider,embed'],
            'content' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'parent_id' => ['nullable', 'integer', 'exists:workspace_blocks,id'],
            'sort_order' => ['nullable', 'integer'],
            'is_checked' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $block = $this->workspace->addBlock($page, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Blok berjaya ditambah.',
            'data' => $block,
        ], 201);
    }

    /**
     * Kemas kini blok.
     */
    public function updateBlock(Request $request, int $pageId, int $blockId): JsonResponse
    {
        WorkspacePage::findOrFail($pageId);
        $block = WorkspaceBlock::findOrFail($blockId);

        $v = Validator::make($request->all(), [
            'type' => ['nullable', 'string', 'in:paragraph,heading,bullet,numbered,todo,quote,code,image,table,callout,divider,embed'],
            'content' => ['nullable', 'string'],
            'data' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
            'is_checked' => ['nullable', 'boolean'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $block->update(array_merge($v->validated(), ['updated_by' => auth()->id()]));

        return response()->json([
            'success' => true,
            'message' => 'Blok berjaya dikemas kini.',
            'data' => $block->fresh(),
        ]);
    }

    /**
     * Susun semula blok.
     */
    public function reorderBlocks(Request $request, int $id): JsonResponse
    {
        $page = WorkspacePage::findOrFail($id);

        $v = Validator::make($request->all(), [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $kira = $this->workspace->reorderBlocks($page, $v->validated()['order']);

        return response()->json([
            'success' => true,
            'message' => 'Susunan blok berjaya dikemas kini.',
            'meta' => ['bilangan_dikemas' => $kira],
        ]);
    }

    /**
     * Buang blok.
     */
    public function destroyBlock(int $pageId, int $blockId): JsonResponse
    {
        WorkspacePage::findOrFail($pageId);
        WorkspaceBlock::findOrFail($blockId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blok berjaya dibuang.',
        ]);
    }

    /**
     * Versi sejarah halaman.
     */
    public function versions(int $id): JsonResponse
    {
        WorkspacePage::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => WorkspacePageVersion::query()
                ->where('page_id', $id)
                ->orderByDesc('created_at')
                ->limit((int) config('workspace.versions.retain', 50))
                ->get(['id', 'title', 'version', 'created_by', 'created_at']),
        ]);
    }

    /**
     * Komen pada halaman.
     */
    public function comments(int $id): JsonResponse
    {
        WorkspacePage::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => WorkspaceComment::query()
                ->where('page_id', $id)
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Tambah komen.
     */
    public function storeComment(Request $request, int $id): JsonResponse
    {
        $page = WorkspacePage::findOrFail($id);

        $v = Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:5000'],
            'block_id' => ['nullable', 'integer', 'exists:workspace_blocks,id'],
            'parent_id' => ['nullable', 'integer', 'exists:workspace_comments,id'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $komen = WorkspaceComment::create(array_merge($v->validated(), [
            'page_id' => $page->id,
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Komen berjaya ditambah.',
            'data' => $komen,
        ], 201);
    }

    /**
     * Selesaikan komen.
     */
    public function resolveComment(int $pageId, int $commentId): JsonResponse
    {
        WorkspacePage::findOrFail($pageId);
        $komen = WorkspaceComment::findOrFail($commentId);

        $komen->update([
            'is_resolved' => true,
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Komen ditandai selesai.',
            'data' => $komen->fresh(),
        ]);
    }

    /**
     * Pangkalan data dalam ruang kerja.
     */
    public function databases(int $id): JsonResponse
    {
        $space = WorkspaceSpace::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => WorkspaceDatabase::query()
                ->where('space_id', $space->id)
                ->with('views')
                ->get(),
        ]);
    }

    /**
     * Cipta pangkalan data.
     */
    public function storeDatabase(Request $request, int $id): JsonResponse
    {
        $space = WorkspaceSpace::findOrFail($id);

        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:250'],
            'description' => ['nullable', 'string', 'max:1000'],
            'columns' => ['nullable', 'array'],
            'columns.*.name' => ['required', 'string', 'max:100'],
            'columns.*.type' => ['nullable', 'string', 'max:30'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $db = $this->workspace->createDatabase($space, $v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Pangkalan data berjaya dicipta.',
            'data' => $db->load('views'),
        ], 201);
    }

    /**
     * Baris data dalam pangkalan data.
     */
    public function rows(int $databaseId): JsonResponse
    {
        $db = WorkspaceDatabase::findOrFail($databaseId);

        return response()->json([
            'success' => true,
            'data' => WorkspaceDatabaseRow::query()
                ->where('database_id', $db->id)
                ->orderBy('sort_order')
                ->limit((int) config('workspace.limits.max_rows_per_database', 5000))
                ->get(),
        ]);
    }

    /**
     * Tambah baris data.
     */
    public function storeRow(Request $request, int $databaseId): JsonResponse
    {
        $db = WorkspaceDatabase::findOrFail($databaseId);

        $v = Validator::make($request->all(), [
            'values' => ['required', 'array'],
            'view_id' => ['nullable', 'integer', 'exists:workspace_database_views,id'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $paparan = $v->validated()['view_id']
            ? WorkspaceDatabaseView::find($v->validated()['view_id'])
            : null;

        $baris = $this->workspace->addRow($db, $v->validated()['values'], $paparan);

        return response()->json([
            'success' => true,
            'message' => 'Baris berjaya ditambah.',
            'data' => $baris,
        ], 201);
    }

    /**
     * Kemas kini baris data.
     */
    public function updateRow(Request $request, int $databaseId, int $rowId): JsonResponse
    {
        WorkspaceDatabase::findOrFail($databaseId);
        $baris = WorkspaceDatabaseRow::findOrFail($rowId);

        $v = Validator::make($request->all(), [
            'values' => ['required', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $baris = $this->workspace->updateRow($baris, $v->validated()['values']);

        return response()->json([
            'success' => true,
            'message' => 'Baris berjaya dikemas kini.',
            'data' => $baris,
        ]);
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
