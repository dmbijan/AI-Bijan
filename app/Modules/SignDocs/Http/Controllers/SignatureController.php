<?php

namespace App\Modules\SignDocs\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SignDocs\Services\SignatureService;
use App\Modules\SignDocs\Services\DocumentWorkflowService;
use Illuminate\Http\Request;

class SignatureController extends Controller
{
    protected $signatureService;
    protected $workflowService;

    public function __construct(
        SignatureService $signatureService,
        DocumentWorkflowService $workflowService
    ) {
        $this->signatureService = $signatureService;
        $this->workflowService = $workflowService;
    }

    /**
     * List signatures for a document
     */
    public function index($documentId)
    {
        $signatures = $this->signatureService->getDocumentSignatures($documentId);
        return response()->json($signatures);
    }

    /**
     * Get pending signatures for user
     */
    public function getPending()
    {
        $signatures = $this->signatureService->getPendingForUser(auth()->id());
        return response()->json($signatures);
    }

    /**
     * Create drawn signature
     */
    public function createDrawn(Request $request, $documentId)
    {
        $validated = $request->validate([
            'signature_data' => 'required|string', // base64 canvas data
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'page' => 'nullable|integer',
        ]);

        try {
            $signature = $this->signatureService->createDrawnSignature(
                $documentId,
                $validated['signature_data'],
                $validated['position_x'] ?? 0,
                $validated['position_y'] ?? 0,
                $validated['page'] ?? 1
            );

            return response()->json($signature, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Create failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Create typed signature
     */
    public function createTyped(Request $request, $documentId)
    {
        $validated = $request->validate([
            'text' => 'required|string|max:100',
            'font_family' => 'nullable|string',
            'font_size' => 'nullable|integer',
            'position_x' => 'nullable|integer',
            'position_y' => 'nullable|integer',
            'page' => 'nullable|integer',
        ]);

        try {
            $signature = $this->signatureService->createTypedSignature(
                $documentId,
                $validated['text'],
                $validated['font_family'] ?? 'Arial',
                $validated['font_size'] ?? 16,
                $validated['position_x'] ?? 0,
                $validated['position_y'] ?? 0,
                $validated['page'] ?? 1
            );

            return response()->json($signature, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Create failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload signature image
     */
    public function createUploaded(Request $request, $documentId)
    {
        try {
            $signature = $this->signatureService->createUploadedSignature(
                $documentId,
                $request->file('signature_file'),
                $request->position_x ?? 0,
                $request->position_y ?? 0,
                $request->page ?? 1
            );

            return response()->json($signature, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Sign document (complete signing)
     */
    public function sign($id)
    {
        try {
            $signature = $this->signatureService->signDocument($id);
            return response()->json($signature);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Sign failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Reject signature
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $signature = $this->signatureService->rejectSignature($id, $validated['reason'] ?? '');
            return response()->json($signature);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Reject failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Generate signed PDF
     */
    public function generatePdf($documentId)
    {
        try {
            $result = $this->signatureService->generateSignedPdf($documentId);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => 'PDF generation failed: ' . $e->getMessage()], 500);
        }
    }
}
