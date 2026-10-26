<?php

namespace App\Modules\Invoicing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class DebitNote extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'debit_notes';

    protected $fillable = [
        'tenant_id',
        'debit_note_number',
        'invoice_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'reason', // Undercharge, additional services, price error
        // ⚠️ 27 Okt 2026: kolum DB sebenar ialah `amount` (bukan
        // original_amount/additional_amount). Fillable lama tidak sepadan →
        // MassAssignmentException setiap kali cipta nota debit.
        'amount',
        'currency',
        'notes',
        'status', // draft, issued, paid
        'issued_at',
        'paid_at',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'original_amount' => 'decimal:2',
        'additional_amount' => 'decimal:2',
        'currency' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($note) {
            if (empty($note->debit_note_number)) {
                $note->debit_note_number = self::generateDebitNoteNumber();
            }
            if (empty($note->currency)) {
                $note->currency = config('invoicing.currency.default', 'MYR');
            }
            if (empty($note->status)) {
                $note->status = 'draft';
            }
        });
    }

    public static function generateDebitNoteNumber(): string
    {
        $year = date('Y');
        $prefix = 'DN';
        
        $lastNote = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastNote ? intval(substr($lastNote->debit_note_number, -5)) + 1 : 1;
        
        return sprintf('%s-%s-%05d', $prefix, $year, $sequence);
    }

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\App\Modules\CRM\Models\Customer::class, 'customer_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Methods
    public function canBeIssued(): bool
    {
        return $this->status === 'draft';
    }

    public function issue(): void
    {
        $this->status = 'issued';
        $this->issued_at = now();
        $this->save();
    }
}
