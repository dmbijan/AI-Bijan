<?php

namespace App\Modules\Invoicing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Receipt extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'receipts';

    protected $fillable = [
        'tenant_id',
        'receipt_number',
        'invoice_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'payment_method', // cash, fpx, card, bank_transfer, check
        'reference_number', // Transaction ref, check number
        'amount',
        'currency',
        'notes',
        'received_at',
        'created_by',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'amount' => 'decimal:2',
        'currency' => 'string',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = self::generateReceiptNumber();
            }
            if (empty($receipt->currency)) {
                $receipt->currency = config('invoicing.currency.default', 'MYR');
            }
        });
    }

    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $prefix = 'RCPT';
        
        $lastReceipt = self::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastReceipt ? intval(substr($lastReceipt->receipt_number, -5)) + 1 : 1;
        
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

    /**
     * Record payment against invoice and update status
     */
    public function recordPayment(): void
    {
        if ($this->invoice_id) {
            $invoice = Invoice::find($this->invoice_id);
            if ($invoice) {
                $invoice->recordPayment($this->amount);
            }
        }
    }
}
