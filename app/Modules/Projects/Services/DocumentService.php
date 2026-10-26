<?php

namespace App\Modules\Projects\Services;

use App\Modules\Projects\Models\ProjectDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    /**
     * Get documents for a project.
     */
    public function getProjectDocuments(int $projectId): \Illuminate\Database\Eloquent\Collection
    {
        return ProjectDocument::where('project_id', $projectId)
            ->with(['uploader'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get document by ID.
     */
    public function getDocument(int $documentId, int $tenantId): ?ProjectDocument
    {
        return ProjectDocument::where('id', $documentId)
            ->where('tenant_id', $tenantId)
            ->with(['uploader', 'project'])
            ->first();
    }

    /**
     * Upload a new document.
     */
    public function uploadDocument(array $data, int $tenantId, int $userId): ProjectDocument
    {
        $file = $data['file'];

        // Validate file
        if (!$file || !$file->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['Invalid uploaded file.']
            ]);
        }

        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = uniqid('doc_', true) . '.' . $extension;
        $folder = $data['folder'] ?? 'general';

        // Store file
        $path = $file->storeAs('projects/' . $data['project_id'] . '/' . $folder, $fileName);

        if (!$path) {
            throw new \Exception('Failed to store file.');
        }

        $document = ProjectDocument::create([
            'tenant_id' => $tenantId,
            'project_id' => $data['project_id'],
            'uploaded_by' => $userId,
            'file_path' => $path,
            'file_name' => $originalName,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'folder' => $folder,
            'version' => '1.0',
        ]);

        return $document->fresh();
    }

    /**
     * Update document version.
     */
    public function updateVersion(ProjectDocument $document, array $data, int $userId): ProjectDocument
    {
        $file = $data['file'];

        if (!$file || !$file->isValid()) {
            throw ValidationException::withMessages([
                'file' => ['Invalid uploaded file.']
            ]);
        }

        // Delete old file
        Storage::disk('public')->delete($document->file_path);

        // Generate new filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileName = uniqid('doc_', true) . '.' . $extension;
        $folder = $data['folder'] ?? $document->folder;

        // Store new file
        $path = $file->storeAs('projects/' . $document->project_id . '/' . $folder, $fileName);

        if (!$path) {
            throw new \Exception('Failed to store file.');
        }

        // Increment version
        $currentVersion = explode('.', $document->version);
        $major = $currentVersion[0];
        $minor = intval($currentVersion[1]) + 1;
        $newVersion = $major . '.' . $minor;

        $document->update([
            'file_path' => $path,
            'file_name' => $originalName,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'folder' => $folder,
            'version' => $newVersion,
        ]);

        return $document->fresh();
    }

    /**
     * Delete a document.
     */
    public function deleteDocument(ProjectDocument $document): bool
    {
        // Delete file from storage
        Storage::disk('public')->delete($document->file_path);

        return $document->delete();
    }

    /**
     * Get documents by folder.
     */
    public function getDocumentsByFolder(int $projectId, string $folder): \Illuminate\Database\Eloquent\Collection
    {
        return ProjectDocument::where('project_id', $projectId)
            ->where('folder', $folder)
            ->with(['uploader'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Search documents by name.
     */
    public function searchDocuments(int $projectId, string $query): \Illuminate\Database\Eloquent\Collection
    {
        return ProjectDocument::where('project_id', $projectId)
            ->where('file_name', 'like', '%' . $query . '%')
            ->with(['uploader'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get document download URL.
     */
    public function getDownloadUrl(ProjectDocument $document): string
    {
        return Storage::url($document->file_path);
    }
}
