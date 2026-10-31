<?php

namespace App\Modules\SignDocs\Services;

use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SignatureService
{
    /**
     * Create a signature for a document
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $signature = \App\Modules\SignDocs\Models\Signature::create([
                'tenant_id' => $data['tenant_id'],
                'document_id' => $data['document_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'signer_name' => $data['signer_name'] ?? auth()->user()->name,
                'signer_email' => $data['signer_email'] ?? auth()->user()->email,
                'signer_role' => $data['signer_role'] ?? '',
                'signature_type' => $data['signature_type'],
                'signature_data' => $data['signature_data'] ?? null,
                'signature_image_path' => $data['signature_image_path'] ?? null,
                'signature_position_x' => $data['signature_position_x'] ?? 0,
                'signature_position_y' => $data['signature_position_y'] ?? 0,
                'signature_page' => $data['signature_page'] ?? 1,
                'font_family' => $data['font_family'] ?? 'Arial',
                'font_size' => $data['font_size'] ?? 16,
                'digital_certificate_id' => $data['digital_certificate_id'] ?? null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status' => 'pending',
                'audit_trail_data' => [],
            ]);

            // Generate audit trail
            $signature->generateAuditTrail('signature_created');

            return $signature;
        });
    }

    /**
     * Draw signature (for canvas-based drawing)
     */
    public function createDrawnSignature($documentId, $signatureData, $positionX = 0, $positionY = 0, $page = 1)
    {
        // Save signature image to storage
        $imagePath = 'signatures/drawn/' . time() . '_' . auth()->id() . '.png';
        
        // Decode base64 signature data and save as image
        if (!empty($signatureData)) {
            $imagePath = Storage::disk('public')->put('signatures/drawn', $signatureData);
        }

        return $this->create([
            'document_id' => $documentId,
            'signature_type' => 'drawn',
            'signature_image_path' => $imagePath,
            'signature_position_x' => $positionX,
            'signature_position_y' => $positionY,
            'signature_page' => $page,
        ]);
    }

    /**
     * Type signature
     */
    public function createTypedSignature($documentId, $text, $fontFamily = 'Arial', $fontSize = 16, $positionX = 0, $positionY = 0, $page = 1)
    {
        return $this->create([
            'document_id' => $documentId,
            'signature_type' => 'typed',
            'signature_data' => [
                'text' => $text,
                'font_family' => $fontFamily,
                'font_size' => $fontSize,
            ],
            'signature_position_x' => $positionX,
            'signature_position_y' => $positionY,
            'signature_page' => $page,
        ]);
    }

    /**
     * Upload signature image
     */
    public function createUploadedSignature($documentId, $file, $positionX = 0, $positionY = 0, $page = 1)
    {
        $validated = $file->validate([
            'signature_file' => 'required|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $path = $file->store('signatures/uploaded', 'public');

        return $this->create([
            'document_id' => $documentId,
            'signature_type' => 'uploaded',
            'signature_image_path' => $path,
            'signature_position_x' => $positionX,
            'signature_position_y' => $positionY,
            'signature_page' => $page,
        ]);
    }

    /**
     * Sign document (complete the signing process)
     */
    public function signDocument($signatureId)
    {
        $signature = \App\Modules\SignDocs\Models\Signature::findOrFail($signatureId);

        // Mark signature as signed
        $signature->markAsSigned();
        $signature->generateAuditTrail('document_signed');

        // Check if all signatures are complete
        $document = $signature->document;
        $allSignatures = \App\Modules\SignDocs\Models\Signature::where('document_id', $document->id)->get();
        $allSigned = $allSignatures->every(function($sig) {
            return $sig->status === 'signed';
        });

        if ($allSigned) {
            // Update document status to signed
            $document->update(['status' => 'signed']);
            
            // Notify all parties
            // TODO: Implement notification system
        }

        return $signature;
    }

    /**
     * Reject signature
     */
    public function rejectSignature($signatureId, $reason = '')
    {
        $signature = \App\Modules\SignDocs\Models\Signature::findOrFail($signatureId);
        $signature->markAsRejected($reason);
        $signature->generateAuditTrail('signature_rejected', ['reason' => $reason]);

        return $signature;
    }

    /**
     * Get document signatures
     */
    public function getDocumentSignatures($documentId)
    {
        return \App\Modules\SignDocs\Models\Signature::where('document_id', $documentId)
            ->with('user')
            ->orderBy('signed_at')
            ->get();
    }

    /**
     * Get pending signatures for user
     */
    public function getPendingForUser($userId)
    {
        return \App\Modules\SignDocs\Models\Signature::whereHas('document', function($q) use ($userId) {
            $q->where('tenant_id', app(TenantContext::class)->id());
        })
        ->where('status', 'pending')
        ->with('document')
        ->get();
    }

    /**
     * Generate PDF with signatures
     */
    public function generateSignedPdf($documentId)
    {
        $document = \App\Modules\SignDocs\Models\Document::findOrFail($documentId);
        $signatures = $this->getDocumentSignatures($documentId);

        // TODO: Implement PDF generation with signature overlays
        // This would typically use a library like TCPDF, DomPDF, or similar
        
        return [
            'document' => $document,
            'signatures' => $signatures,
            'pdf_path' => null, // Would be generated here
        ];
    }
}
