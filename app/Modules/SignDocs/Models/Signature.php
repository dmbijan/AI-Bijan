<?php

namespace App\Modules\SignDocs\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signature extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'sign_docs_signatures';

    protected $fillable = [
        'tenant_id',
        'document_id',
        'user_id',
        'signer_name',
        'signer_email',
        'signer_role',
        'signature_type',
        'signature_data',
        'signature_image_path',
        'signature_position_x',
        'signature_position_y',
        'signature_page',
        'font_family',
        'font_size',
        'digital_certificate_id',
        'ip_address',
        'user_agent',
        'status',
        'signed_at',
        'audit_trail_data',
        'metadata',
    ];

    protected $casts = [
        'signature_data' => 'array',
        'audit_trail_data' => 'array',
        'metadata' => 'array',
        'signature_position_x' => 'integer',
        'signature_position_y' => 'integer',
        'signature_page' => 'integer',
        'signed_at' => 'datetime',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    // Accessors
    public function getSignatureImageUrlAttribute()
    {
        if ($this->signature_image_path) {
            return asset('storage/' . $this->signature_image_path);
        }
        return null;
    }

    public function getStatusBadgeClassAttribute()
    {
        return [
            'pending' => 'warning',
            'signed' => 'success',
            'rejected' => 'danger',
            'expired' => 'secondary',
        ][$this->status] ?? 'secondary';
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSigned($query)
    {
        return $query->where('status', 'signed');
    }

    // Methods
    public function markAsSigned()
    {
        $this->update([
            'status' => 'signed',
            'signed_at' => now(),
        ]);
    }

    public function markAsRejected($reason = '')
    {
        $this->update([
            'status' => 'rejected',
            'metadata' => array_merge(
                $this->metadata ?? [],
                ['rejection_reason' => $reason]
            ),
        ]);
    }

    public function generateAuditTrail($action, $details = [])
    {
        $trail = $this->audit_trail_data ?? [];
        $trail[] = [
            'action' => $action,
            'timestamp' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'details' => $details,
        ];
        $this->update(['audit_trail_data' => $trail]);
        return $trail;
    }
}
