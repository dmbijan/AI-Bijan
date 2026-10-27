<?php

namespace App\Modules\SignDocs\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\SignDocs\Services\DocumentService;
use App\Modules\SignDocs\Services\TemplateService;
use App\Modules\SignDocs\Services\SignatureService;
use App\Modules\SignDocs\Services\DocumentWorkflowService;
use Illuminate\Http\Request;

class DocumentController extends Controller
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
    protected $documentService;
    protected $templateService;

    public function __construct(
        DocumentService $documentService,
        TemplateService $templateService
    ) {
        $this->documentService = $documentService;
        $this->templateService = $templateService;
    }

    /**
     * List documents
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'status', 'is_template']);
        
        $documents = $this->documentService->search(
            app(TenantContext::class)->id(),
            $request->search ?? '',
            $filters
        );

        return response()->json($documents);
    }

    /**
     * Get document by ID
     */
    public function show($id)
    {
        $document = \App\Modules\SignDocs\Models\Document::findOrFail($id);
        
        // Verify tenant access
        if ($document->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($document->load(['versions', 'template', 'signatures']));
    }

    /**
     * Upload new document
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:51200',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|in:invoice,contract,proposal,report,letter,form,certificate,general',
            'tags' => 'nullable|array',
            'access_level' => 'nullable|string|in:private,shared,public',
            'is_template' => 'nullable|boolean',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('documents/' . date('Y/m'), 'public');
            
            $fileInfo = pathinfo($file->getClientOriginalName());
            
            $document = $this->documentService->create([
                'tenant_id' => app(TenantContext::class)->id(),
                'user_id' => auth()->id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? '',
                'file_path' => $path,
                'file_type' => $fileInfo['extension'] ?? 'bin',
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'category' => $validated['category'] ?? 'general',
                'tags' => $validated['tags'] ?? [],
                'access_level' => $validated['access_level'] ?? 'private',
                'is_template' => $validated['is_template'] ?? false,
            ]);

            return response()->json($document, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update document
     */
    public function update(Request $request, $id)
    {
        try {
            $document = $this->documentService->update($id, $request->all());
            return response()->json($document);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }

    /**
     * Delete document
     */
    public function destroy($id)
    {
        try {
            $this->documentService->delete($id);
            return response()->json(['message' => 'Document deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Delete failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Download document
     */
    public function download($id)
    {
        return $this->documentService->download($id);
    }

    /**
     * Generate share link
     */
    public function generateShareLink($id)
    {
        $document = \App\Modules\SignDocs\Models\Document::findOrFail($id);
        $shareUrl = $document->generateShareLink();

        return response()->json([
            'share_url' => $shareUrl,
            'expires_at' => $document->share_expires_at,
        ]);
    }

    /**
     * Get shared document by link
     */
    public function getByShareLink($shareLink)
    {
        $document = $this->documentService->getByShareLink($shareLink);

        if (!$document) {
            return response()->json(['message' => 'Document not found or expired'], 404);
        }

        return response()->json($document);
    }

    /**
     * Get document statistics
     */
    public function getStatistics()
    {
        $stats = $this->documentService->getStatistics(app(TenantContext::class)->id());
        return response()->json($stats);
    }

    /**
     * Search documents
     */
    public function search(Request $request)
    {
        $documents = $this->documentService->search(
            app(TenantContext::class)->id(),
            $request->q ?? '',
            $request->only(['category', 'status', 'is_template'])
        );

        return response()->json($documents);
    }

    /**
     * Get document versions
     */
    public function getVersions($id)
    {
        $document = \App\Modules\SignDocs\Models\Document::with('versions')->findOrFail($id);
        return response()->json($document->versions);
    }
}
