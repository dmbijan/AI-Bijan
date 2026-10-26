<?php

namespace App\Modules\SignDocs\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DocumentService
{
    /**
     * Create a new document
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $document = \App\Modules\SignDocs\Models\Document::create([
                'tenant_id' => $data['tenant_id'],
                'user_id' => $data['user_id'],
                'title' => $data['title'],
                'description' => $data['description'] ?? '',
                'file_path' => $data['file_path'],
                'file_type' => $data['file_type'],
                'file_size' => $data['file_size'] ?? 0,
                'mime_type' => $data['mime_type'] ?? null,
                'category' => $data['category'] ?? 'general',
                'tags' => $data['tags'] ?? [],
                'version' => 1,
                'status' => 'draft',
                'is_template' => $data['is_template'] ?? false,
                'access_level' => $data['access_level'] ?? 'private',
            ]);

            return $document;
        });
    }

    /**
     * Update document
     */
    public function update($documentId, array $data)
    {
        $document = \App\Modules\SignDocs\Models\Document::findOrFail($documentId);
        
        // Check if user can edit
        if (isset($data['user_id']) && !$document->canEdit(auth()->user())) {
            throw new \Exception('Unauthorized to edit this document');
        }

        // Handle version increment if file is being replaced
        if (isset($data['file_path']) && $data['file_path'] !== $document->file_path) {
            $data['version'] = $document->version + 1;
            $data['parent_document_id'] = $document->id;
        }

        $document->update($data);
        return $document;
    }

    /**
     * Upload document file
     */
    public function uploadFile($userId, $file, $category = 'general')
    {
        $validated = $file->validate([
            'file' => 'required|file|max:51200|mimes:' . implode(',', config('sign-docs.documents.allowed_types', ['pdf', 'docx', 'xlsx'])),
        ]);

        $path = $file->store('documents/' . date('Y/m'), 'public');

        $fileInfo = pathinfo($file->getClientOriginalName());
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $document = $this->create([
            'tenant_id' => app(TenantContext::class)->id(),
            'user_id' => $userId,
            'title' => $fileInfo['filename'],
            'description' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $fileInfo['extension'] ?? 'bin',
            'file_size' => $fileSize,
            'mime_type' => $mimeType,
            'category' => $category,
            'tags' => [],
        ]);

        return $document;
    }

    /**
     * Delete document and its file
     */
    public function delete($documentId)
    {
        $document = \App\Modules\SignDocs\Models\Document::findOrFail($documentId);
        
        // Delete file from storage
        Storage::disk('public')->delete($document->file_path);
        
        // Soft delete document
        $document->delete();
        
        return true;
    }

    /**
     * Get document by share link
     */
    public function getByShareLink($shareLink)
    {
        $document = \App\Modules\SignDocs\Models\Document::where('share_link', $shareLink)
            ->where(function($query) {
                $query->whereNull('share_expires_at')
                    ->orWhere('share_expires_at', '>', now());
            })
            ->first();

        if ($document) {
            $document->incrementViewCount();
        }

        return $document;
    }

    /**
     * Download document
     */
    public function download($documentId)
    {
        $document = \App\Modules\SignDocs\Models\Document::findOrFail($documentId);
        $document->incrementDownloadCount();

        return Storage::disk('public')->download($document->file_path, $document->title . '.' . $document->file_type);
    }

    /**
     * Search documents
     */
    public function search($tenantId, $searchTerm, $filters = [])
    {
        $query = \App\Modules\SignDocs\Models\Document::where('tenant_id', $tenantId);

        if (!empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['is_template'])) {
            $query->templates();
        }

        if (!empty($searchTerm)) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        return $query->orderBy('updated_at', 'desc')->paginate(20);
    }

    /**
     * Get document statistics
     */
    public function getStatistics($tenantId)
    {
        $stats = [
            'total_documents' => \App\Modules\SignDocs\Models\Document::where('tenant_id', $tenantId)->count(),
            'total_templates' => \App\Modules\SignDocs\Models\Document::where('tenant_id', $tenantId)->where('is_template', true)->count(),
            'shared_documents' => \App\Modules\SignDocs\Models\Document::where('tenant_id', $tenantId)->shared()->count(),
            'signed_documents' => \App\Modules\SignDocs\Models\Document::where('tenant_id', $tenantId)->where('status', 'signed')->count(),
            'pending_signatures' => \App\Modules\SignDocs\Models\Signature::whereHas('document', function($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->where('status', 'pending')->count(),
        ];

        return $stats;
    }
}
