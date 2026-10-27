<?php

namespace App\Modules\SignDocs\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\SignDocs\Services\TemplateService;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected $templateService;

    public function __construct(TemplateService $templateService)
    {
        $this->templateService = $templateService;
    }

    /**
     * List templates
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'active_only']);
        
        $templates = $this->templateService->getAll(
            app(TenantContext::class)->id(),
            $filters
        );

        return response()->json($templates);
    }

    /**
     * Get template by ID
     */
    public function show($id)
    {
        $template = $this->templateService->getById($id);
        
        // Verify tenant access
        if ($template->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($template);
    }

    /**
     * Create new template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|in:invoice,contract,proposal,report,letter,form,certificate,other',
            'content' => 'nullable|string',
            'placeholders' => 'nullable|array',
            'file_path' => 'nullable|string',
            'file_type' => 'nullable|string',
            'thumbnail_path' => 'nullable|string',
            'tags' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        try {
            $template = $this->templateService->create([
                'tenant_id' => app(TenantContext::class)->id(),
                'user_id' => auth()->id(),
                'name' => $validated['name'],
                'description' => $validated['description'] ?? '',
                'category' => $validated['category'] ?? 'other',
                'content' => $validated['content'] ?? '',
                'placeholders' => $validated['placeholders'] ?? [],
                'file_path' => $validated['file_path'] ?? null,
                'file_type' => $validated['file_type'] ?? null,
                'thumbnail_path' => $validated['thumbnail_path'] ?? null,
                'tags' => $validated['tags'] ?? [],
                'metadata' => $validated['metadata'] ?? [],
            ]);

            return response()->json($template, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Create failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update template
     */
    public function update(Request $request, $id)
    {
        try {
            $template = $this->templateService->update($id, $request->all());
            return response()->json($template);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete template
     */
    public function destroy($id)
    {
        try {
            $this->templateService->delete($id);
            return response()->json(['message' => 'Template deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate document from template
     */
    public function generateDocument(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'data' => 'required|array',
            'file_path' => 'nullable|string',
            'file_type' => 'nullable|string',
            'file_size' => 'nullable|integer',
            'mime_type' => 'nullable|string',
        ]);

        try {
            $document = $this->templateService->generateFromTemplate($id, $validated['data']);
            return response()->json($document, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Generation failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Duplicate template
     */
    public function duplicate($id)
    {
        try {
            $template = $this->templateService->duplicate($id);
            return response()->json($template);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Duplicate failed: ' . $e->getMessage()], 500);
        }
    }
}
