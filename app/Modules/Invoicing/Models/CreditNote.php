<?php

namespace App\Modules\Invoicing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class CreditNote extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'credit_notes';

    protected $fillable = [
        'tenant_id',
        'credit_note_number',
        'invoice_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'reason', // Return, damage, overcharge, discount
        'original_amount',
        'credit_amount',
        'applied_amount',
        'remaining_credit',
        'currency',
        'notes',
        'status', // draft, issued, applied, expired
        'issued_at',
        'expires_at',
        'applied_at',
        'created_by',
        'approved_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'applied_at' => 'datetime',
        'original_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'remaining_credit' => 'decimal:2',
        'currency' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($note) {
            if (empty($note->credit_note_number)) {
                $note->credit_note_number = self::generateCreditNoteNumber();
            }
            if (empty($note->currency)) {
                $note->currency = config('invoicing.currency.default', 'MYR');
            }
            if (empty($note->status)) {
                $note->status = 'draft';
            }
        });
    }

    public static function generateCreditNoteNumber(): string
    {
        $year = date('Y');
        $prefix = 'CN';
        
        $lastNote = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastNote ? intval(substr($lastNote->credit_note_number, -5)) + 1 : 1;
        
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
    public function canBeApplied(): bool
    {
        return $this->status === 'issued' && $this->remaining_credit > 0;
    }

    public function applyToInvoice(float $amount): void
    {
        $applyAmount = min($amount, $this->remaining_credit);
        
        $this->applied_amount += $applyAmount;
        $this->remaining_credit -= $applyAmount;
        
        // Update invoice
        if ($this->invoice_id) {
            $invoice = Invoice::find($this->invoice_id);
            if ($invoice) {
                $invoice->recordPayment($applyAmount);
                
                if ($this->remaining_credit <= 0) {
                    $this->status = 'applied';
                    $this->applied_at = now();
                }
                $this->save();
            }
        }
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at) && $this->status !== 'applied';
    }
}
