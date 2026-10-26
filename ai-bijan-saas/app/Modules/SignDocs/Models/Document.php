<?php

namespace App\Modules\SignDocs\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'sign_docs_documents';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'title',
        'description',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
        'category',
        'tags',
        'version',
        'parent_document_id',
        'status',
        'is_template',
        'template_id',
        'expiry_date',
        'access_level',
        'password_protected',
        'watermark_text',
        'download_count',
        'view_count',
        'share_link',
        'share_expires_at',
        'metadata',
    ];

    protected $casts = [
        'tags' => 'array',
        'metadata' => 'array',
        'file_size' => 'integer',
        'download_count' => 'integer',
        'view_count' => 'integer',
        'is_template' => 'boolean',
        'password_protected' => 'boolean',
        'expiry_date' => 'date',
        'share_expires_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function parentDocument()
    {
        return $this->belongsTo(Document::class, 'parent_document_id');
    }

    public function versions()
    {
        return $this->hasMany(Document::class, 'parent_document_id')->orderBy('version', 'desc');
    }

    public function template()
    {
        return $this->belongsTo(Document::class, 'template_id')->where('is_template', true);
    }

    public function signatures()
    {
        return $this->hasMany(Signature::class);
    }

    public function workflowSteps()
    {
        return $this->hasMany(WorkflowStep::class, 'document_id');
    }

    // Accessors
    public function getShareUrlAttribute()
    {
        if ($this->share_link && $this->share_expires_at && $this->share_expires_at->isFuture()) {
            return url('/s/' . $this->share_link);
        }
        return null;
    }

    public function getStatusColorAttribute()
    {
        return [
            'draft' => 'secondary',
            'pending_review' => 'warning',
            'in_review' => 'info',
            'pending_approval' => 'warning',
            'approved' => 'success',
            'pending_signature' => 'primary',
            'signed' => 'success',
            'archived' => 'dark',
            'rejected' => 'danger',
        ][$this->status] ?? 'secondary';
    }

    // Scopes
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeShared($query)
    {
        return $query->whereNotNull('share_link')
            ->where(function($q) {
                $q->whereNull('share_expires_at')
                  ->orWhere('share_expires_at', '>', now());
            });
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    // Methods
    public function generateShareLink()
    {
        $this->share_link = str_random(40);
        $this->save();
        return $this->share_url;
    }

    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function incrementDownloadCount()
    {
        $this->increment('download_count');
    }

    public function getVersionNumber()
    {
        return $this->version ?? 1;
    }

    public function canEdit($user)
    {
        return $this->user_id === $user->id || 
               $user->roles()->pluck('name')->contains('tenant_owner') ||
               $user->roles()->pluck('name')->contains('tenant_admin');
    }

    public function canSign($user)
    {
        return in_array($this->status, ['pending_signature', 'approved']);
    }
}
