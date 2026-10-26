<?php

namespace App\Modules\Assets\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetDocument extends Model
{
    use HasFactory;

    protected $table = 'asset_documents';

    protected $fillable = [
        'tenant_id',
        'asset_id',
        'uploaded_by',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    /**
     * Get the asset this document belongs to.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get file URL for download/viewing.
     */
    public function getFileUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }
}
