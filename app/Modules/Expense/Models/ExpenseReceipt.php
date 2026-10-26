<?php

namespace App\Modules\Expense\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Resit perbelanjaan — pautan ke `DocumentAI\Document` untuk OCR.
 *
 * Staff boleh upload terus ke AI BIJAN. Setiap resit perlu
 * `is_verified` sebelum dikira sah untuk kelulusan.
 */
class ExpenseReceipt extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $table = 'expense_receipts';

    protected $fillable = [
        'tenant_id',
        'expense_id',
        'expense_claim_id',
        'document_id',
        'file_path',
        'file_mime',
        'file_size',
        'file_hash',
        'ocr_text',
        'ocr_confidence',
        'extracted_fields',
        'ocr_processed',
        'ocr_processed_at',
        'merchant_name',
        'receipt_date',
        'receipt_total',
        'receipt_number',
        'is_verified',
        'verified_by_user_id',
        'verified_at',
        'duplicate_of_receipt_id',
        'is_duplicate_suspected',
        'is_primary',
        'notes',
    ];

    protected $casts = [
        'extracted_fields' => 'array',
        'ocr_processed' => 'boolean',
        'ocr_confidence' => 'decimal:2',
        'ocr_processed_at' => 'datetime',
        'receipt_date' => 'date',
        'receipt_total' => 'decimal:2',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'is_duplicate_suspected' => 'boolean',
        'is_primary' => 'boolean',
    ];

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class, 'expense_id');
    }

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ExpenseClaim::class, 'expense_claim_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\DocumentAI\Models\Document::class, 'document_id');
    }

    /**
     * Adakah resit ini sudah disahkan manusia?
     */
    public function disahkan(): bool
    {
        return (bool) $this->is_verified;
    }
}
