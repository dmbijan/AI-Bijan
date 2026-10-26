<?php

namespace App\Modules\DocumentAI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentTemplate extends Model
{
    protected $table = 'document_templates';
    
    protected $fillable = [
        'tenant_id',
        'template_name',
        'template_type',
        'field_definitions',
        'sample_image_path',
        'is_active',
        'usage_count',
        'average_confidence',
    ];

    protected $casts = [
        'field_definitions' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'average_confidence' => 'decimal:2',
    ];

    /**
     * Get tenant that owns this template.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Scope active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('template_type', $type);
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(float $confidence): void
    {
        $this->usage_count++;
        
        // Update average confidence
        $totalConfidence = $this->average_confidence * ($this->usage_count - 1) + $confidence;
        $this->average_confidence = $totalConfidence / $this->usage_count;
        
        $this->save();
    }
}
