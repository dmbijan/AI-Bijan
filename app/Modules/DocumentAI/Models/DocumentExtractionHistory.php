<?php

namespace App\Modules\DocumentAI\Models;


use App\Models\Concerns\BelongsToTenant;use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentExtractionHistory extends Model
{
    use BelongsToTenant;

    protected $table = 'document_extraction_history';
    
    protected $fillable = [
        'document_id',
        'extraction_type',
        'model_version',
        'input_data',
        'output_data',
        'confidence_score',
        'processing_time_ms',
        'status',
        'error_message',
    ];

    protected $casts = [
        'input_data' => 'array',
        'output_data' => 'array',
        'confidence_score' => 'decimal:2',
        'processing_time_ms' => 'integer',
    ];

    /**
     * Get document that owns this extraction.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
