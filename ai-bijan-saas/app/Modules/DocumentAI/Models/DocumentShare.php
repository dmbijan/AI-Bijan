<?php

namespace App\Modules\DocumentAI\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentShare extends Model
{
    protected $table = 'document_shares';
    
    protected $fillable = [
        'document_id',
        'share_token',
        'shared_by',
        'shared_with_user',
        'shared_with_email',
        'access_level',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get document that is shared.
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get sharer.
     */
    public function sharer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'shared_by');
    }

    /**
     * Get shared recipient user.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'shared_with_user');
    }

    /**
     * Check if share is still valid.
     */
    public function isValid(): bool
    {
        return $this->is_active && ($this->expires_at === null || $this->expires_at > now());
    }

    /**
     * Check if access level allows viewing.
     */
    public function canView(): bool
    {
        return in_array($this->access_level, ['view', 'download', 'edit']);
    }

    /**
     * Check if access level allows downloading.
     */
    public function canDownload(): bool
    {
        return in_array($this->access_level, ['download', 'edit']);
    }

    /**
     * Check if access level allows editing.
     */
    public function canEdit(): bool
    {
        return $this->access_level === 'edit';
    }
}
