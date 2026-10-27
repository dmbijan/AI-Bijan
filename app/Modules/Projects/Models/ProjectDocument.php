<?php

namespace App\Modules\Projects\Models;


use App\Models\Concerns\BelongsToTenant;use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDocument extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'project_documents';

    protected $fillable = [
        'tenant_id',
        'project_id',
        'uploaded_by',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'folder',
        'version',
    ];

    /**
     * Get the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the uploader.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'uploaded_by');
    }

    /**
     * Scope by folder.
     */
    public function scopeByFolder($query, string $folder)
    {
        return $query->where('folder', $folder);
    }

    /**
     * Scope by version.
     */
    public function scopeByVersion($query, string $version)
    {
        return $query->where('version', $version);
    }

    /**
     * Get file URL/Path helper.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Format file size to human readable.
     */
    public function getFormattedSizeAttribute(): string
    {
        $size = $this->file_size;
        
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1024 * 1024) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1024 * 1024 * 1024) {
            return round($size / (1024 * 1024), 2) . ' MB';
        } else {
            return round($size / (1024 * 1024 * 1024), 2) . ' GB';
        }
    }
}
