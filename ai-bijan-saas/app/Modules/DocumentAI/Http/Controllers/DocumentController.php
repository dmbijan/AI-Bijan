<?php

namespace App\Modules\DocumentAI\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\DocumentAI\Models\Document;
use App\Modules\DocumentAI\Services\DocumentOCRService;
use App\Modules\DocumentAI\Services\DocumentExtractionService;
use App\Modules\DocumentAI\Services\DocumentClassificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DocumentController extends Controller
{
    protected $ocrService;
    protected $extractionService;
    protected $classificationService;

    public function __construct(
        DocumentOCRService $ocrService,
        DocumentExtractionService $extractionService,
        DocumentClassificationService $classificationService
    ) {
        $this->ocrService = $ocrService;
        $this->extractionService = $extractionService;
        $this->classificationService = $classificationService;
    }

    /**
     * Display a listing of documents.
     */
    public function index(Request $request)
    {
        $query = Document::with(['assignee', 'versions'])
            ->where('tenant_id', app(TenantContext::class)->id())
            ->latest();
        
        // Filter by type
        if ($request->filled('type')) {
            $query->byType($request->type);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }
        
        // Filter by classification
        if ($request->filled('classification')) {
            $query->where('classification_status', $request->classification);
        }
        
        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('document_number', 'like', '%' . $request->search . '%')
                  ->orWhere('reference_number', 'like', '%' . $request->search . '%');
            });
        }
        
        $documents = $query->paginate(15);
        
        return response()->json($documents);
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'document_type' => ['nullable', Rule::in(['invoice', 'receipt', 'purchase_order', 'sales_order', 'contract', 'letter', 'identity_card', 'business_registration', 'bank_statement', 'tax_return', 'custom'])],
            'file' => 'required|file|max:51200|mimes:pdf,png,jpg,jpeg,tiff,bmp,gif',
            'tags' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
            'retention_until' => 'nullable|date',
            'is_confidential' => 'nullable|boolean',
        ]);
        
        $file = $request->file('file');
        $path = $file->store('documents/' . app(TenantContext::class)->id(), 'public');
        
        $fileHash = sha256_file($file->getRealPath());
        
        $document = Document::create([
            'tenant_id' => app(TenantContext::class)->id(),
            'document_number' => Document::generateDocumentNumber(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'file_mime' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_hash' => $fileHash,
            'document_type' => $validated['document_type'] ?? 'custom',
            'status' => 'uploaded',
            'tags' => $validated['tags'] ?? [],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'retention_until' => $validated['retention_until'] ?? null,
            'is_confidential' => $validated['is_confidential'] ?? false,
        ]);
        
        // Start processing pipeline
        $this->processDocument($document);
        
        return response()->json($document->load('versions'), 201);
    }

    /**
     * Display the specified document.
     */
    public function show(Document $document)
    {
        return response()->json($document->load(['assignee', 'versions', 'extractionHistory', 'shares']));
    }

    /**
     * Update the specified document.
     */
    public function update(Request $request, Document $document)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'document_type' => ['sometimes', Rule::in(['invoice', 'receipt', 'purchase_order', 'sales_order', 'contract', 'letter', 'identity_card', 'business_registration', 'bank_statement', 'tax_return', 'custom'])],
            'tags' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
            'retention_until' => 'nullable|date',
            'is_confidential' => 'nullable|boolean',
        ]);
        
        $document->update(array_filter($validated));
        
        return response()->json($document->load(['assignee', 'versions']));
    }

    /**
     * Delete the specified document.
     */
    public function destroy(Document $document)
    {
        Storage::disk('public')->delete($document->file_path);
        
        // Delete all versions
        foreach ($document->versions as $version) {
            Storage::disk('public')->delete($version->file_path);
        }
        
        $document->delete();
        
        return response()->json(['message' => 'Document deleted successfully']);
    }

    /**
     * Process document through full pipeline.
     */
    protected function processDocument(Document $document): void
    {
        try {
            $filePath = Storage::path($document->file_path);
            
            // Step 1: OCR Processing
            $ocrResult = $this->ocrService->processOCR($document, $filePath);
            
            // Step 2: ML Extraction (if OCR successful)
            if ($ocrResult['success']) {
                $this->extractionService->extractData($document);
            }
            
            // Step 3: Classification
            $this->classificationService->classify($document);
            
        } catch (\Exception $e) {
            $document->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Re-process document.
     */
    public function reprocess(Document $document)
    {
        $this->processDocument($document);
        
        return response()->json(['message' => 'Document reprocessing started']);
    }

    /**
     * Download document file.
     */
    public function download(Document $document)
    {
        $filePath = Storage::path($document->file_path);
        
        return response()->download($filePath, $document->title . '.' . pathinfo($document->file_path, PATHINFO_EXTENSION));
    }

    /**
     * Get document statistics.
     */
    public function getStatistics()
    {
        $stats = [
            'total' => Document::where('tenant_id', app(TenantContext::class)->id())->count(),
            'by_type' => Document::where('tenant_id', app(TenantContext::class)->id())
                ->selectRaw('document_type, COUNT(*) as count')
                ->groupBy('document_type')
                ->get(),
            'by_status' => Document::where('tenant_id', app(TenantContext::class)->id())
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'average_ocr_confidence' => Document::where('tenant_id', app(TenantContext::class)->id())
                ->whereNotNull('ocr_confidence')
                ->avg('ocr_confidence'),
            'average_ml_confidence' => Document::where('tenant_id', app(TenantContext::class)->id())
                ->whereNotNull('ml_confidence')
                ->avg('ml_confidence'),
        ];
        
        return response()->json($stats);
    }
}
