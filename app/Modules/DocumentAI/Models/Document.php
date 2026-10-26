<?php

namespace App\Modules\DocumentAI\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use BelongsToTenant;

    protected $table = 'documents';
    
    protected $fillable = [
        'tenant_id',
        'document_number',
        'title',
        'description',
        'file_path',
        'file_mime',
        'file_size',
        'file_hash',
        'document_type',
        'ocr_processed',
        'ocr_processed_at',
        'ocr_text',
        'ocr_confidence',
        'ml_extracted',
        'ml_extracted_at',
        'extracted_fields',
        'ml_confidence',
        'classification_status',
        'classification_label',
        'classification_confidence',
        'status',
        'error_message',
        'document_date',
        'sender_name',
        'sender_email',
        'recipient_name',
        'recipient_email',
        'total_amount',
        'currency',
        'reference_number',
        'tags',
        'assigned_to',
        'retention_until',
        'is_confidential',
    ];

    protected $casts = [
        'ocr_processed' => 'boolean',
        'ocr_processed_at' => 'datetime',
        'ocr_confidence' => 'decimal:2',
        'ml_extracted' => 'boolean',
        'ml_extracted_at' => 'datetime',
        'extracted_fields' => 'array',
        'ml_confidence' => 'decimal:2',
        'classification_status' => 'string',
        'classification_confidence' => 'decimal:2',
        'document_date' => 'date',
        'total_amount' => 'decimal:2',
        'tags' => 'array',
        'retention_until' => 'date',
        'is_confidential' => 'boolean',
    ];

    /**
     * Get tenant that owns the document.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get assigned user.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    /**
     * Get document versions.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class);
    }

    /**
     * Get extraction history.
     */
    public function extractionHistory(): HasMany
    {
        return $this->hasMany(DocumentExtractionHistory::class);
    }

    /**
     * Get document shares.
     */
    public function shares(): HasMany
    {
        return $this->hasMany(DocumentShare::class);
    }

    /**
     * Generate document number automatically.
     */
    public static function generateDocumentNumber(): string
    {
        $year = date('Y');
        $lastNumber = static::whereYear('created_at', $year)
            ->max('id');
        $number = ($lastNumber ?? 0) + 1;
        
        return sprintf('DOC-%s-%04d', $year, $number);
    }

    /**
     * Scope by document type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('document_type', $type);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope processed documents.
     */
    public function scopeProcessed($query)
    {
        return $query->where('ocr_processed', true);
    }

    /**
     * Check if document is ready for processing.
     */
    public function isReadyForProcessing(): bool
    {
        return $this->status === 'uploaded';
    }

    /**
     * Check if document is fully processed.
     */
    public function isFullyProcessed(): bool
    {
        return $this->ocr_processed && $this->ml_extracted && $this->status === 'completed';
    }

    /**
     * Add tag to document.
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->tags = $tags;
            $this->save();
        }
    }

    /**
     * Get file URL.
     */
    public function getFileUrl(): string
    {
        return storage_url($this->file_path);
    }
}
