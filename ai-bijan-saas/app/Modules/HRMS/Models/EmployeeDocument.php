<?php

namespace App\Modules\HRMS\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeDocument extends Model
{
    use HasFactory;

    protected $table = 'hrms_employee_documents';

    protected $fillable = [
        'employee_id',
        'document_type',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'file_size' => 'integer',
    ];

    /**
     * Get the employee.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Check if document is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Scope pending documents.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope verified documents.
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope expiring soon (within 30 days).
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(30));
    }
}
