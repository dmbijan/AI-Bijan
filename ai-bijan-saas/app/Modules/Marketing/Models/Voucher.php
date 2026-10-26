<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Voucher extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_vouchers';

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'name',
        'code',
        'description',
        'type',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'usage_per_customer',
        'used_count',
        'start_date',
        'end_date',
        'status',
        'applicable_products',
        'applicable_categories',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_per_customer' => 'integer',
        'used_count' => 'integer',
        'applicable_products' => 'array',
        'applicable_categories' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the tenant that owns the voucher.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the campaign this voucher belongs to.
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    /**
     * Get all contacts who used this voucher.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(Contact::class, 'marketing_voucher_usage')
            ->withTimestamps()
            ->withPivot(['used_at', 'order_id', 'amount_used']);
    }

    /**
     * Generate unique code.
     */
    public static function generateCode(): string
    {
        return strtoupper('MKT-' . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8));
    }

    /**
     * Scope active vouchers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('start_date')
                  ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            });
    }

    /**
     * Check if voucher is valid.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->start_date && $this->start_date > now()) {
            return false;
        }

        if ($this->end_date && $this->end_date < now()) {
            return false;
        }

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
