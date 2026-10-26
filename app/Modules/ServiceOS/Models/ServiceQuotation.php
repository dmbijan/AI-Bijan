<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Sebut harga berversi. Hanya satu versi boleh `is_current = true`
 * bagi setiap projek — dikawal oleh ServiceOSService::ciptaSebutHarga().
 */
class ServiceQuotation extends Model
{
    use BelongsToTenant;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_SENT,
        self::STATUS_ACCEPTED,
        self::STATUS_REJECTED,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    protected $table = 'service_quotations';

    protected $fillable = [
        'tenant_id',
        'service_project_id',
        'client_id',
        'quote_no',
        'version',
        'status',
        'currency',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'deposit_amount',
        'balance_amount',
        'line_items',
        'terms',
        'notes',
        'valid_until',
        'sent_at',
        'accepted_at',
        'rejected_at',
        'rejection_reason',
        'is_current',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'line_items' => 'array',
        'version' => 'integer',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_current' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ServiceProject::class, 'service_project_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(ServiceClient::class, 'client_id');
    }

    public function contract(): HasOne
    {
        return $this->hasOne(ServiceContract::class, 'service_quotation_id');
    }

    /** Kira semula jumlah daripada line_items. */
    public function kiraJumlah(float $kadarCukai = 0.0): array
    {
        $subtotal = 0.0;

        foreach ((array) $this->line_items as $item) {
            $qty = (float) ($item['qty'] ?? $item['quantity'] ?? 0);
            $harga = (float) ($item['unit_price'] ?? $item['price'] ?? 0);
            $subtotal += $qty * $harga;
        }

        $diskaun = (float) ($this->discount_amount ?? 0);
        $bercukai = max(0, $subtotal - $diskaun);
        $cukai = round($bercukai * $kadarCukai, 2);
        $jumlah = round($bercukai + $cukai, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($diskaun, 2),
            'tax_amount' => $cukai,
            'total_amount' => $jumlah,
            'deposit_amount' => round((float) ($this->deposit_amount ?? 0), 2),
            'balance_amount' => round($jumlah - (float) ($this->deposit_amount ?? 0), 2),
        ];
    }

    /** Adakah sebut harga masih sah pada tarikh tertentu? */
    public function masihSah(?\DateTimeInterface $pada = null): bool
    {
        if (! $this->valid_until) {
            return true;
        }

        $pada = $pada ? \Illuminate\Support\Carbon::parse($pada) : now();

        return $pada->lte($this->valid_until->endOfDay());
    }
}
