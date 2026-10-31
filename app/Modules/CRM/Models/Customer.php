<?php

namespace App\Modules\CRM\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToTenant;

    protected $table = 'customers';
    
    protected $fillable = [
        'tenant_id',
        'customer_code',
        'customer_type',
        'name',
        'name_bm',
        'ssm_number',
        'sst_number',
        'tax_identification',
        'contact_person',
        'contact_email',
        'contact_phone',
        'address',
        'city',
        'state',
        'postcode',
        'category',
        'status',
        'credit_limit',
        'payment_terms_days',
        'currency',
        'salesperson_id',
        'region',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'notes',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_terms_days' => 'integer',
    ];

    /**
     * Get tenant that owns the customer.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get assigned salesperson.
     */
    public function salesperson(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'salesperson_id');
    }

    /**
     * Get customer contacts.
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    /**
     * Get opportunities for this customer.
     */
    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }

    /**
     * Generate customer code automatically.
     */
    public static function generateCustomerCode(): string
    {
        // customer_code unik secara GLOBAL, tetapi id dikira dalam skop tenant: dulu pelanggan pertama
        // setiap syarikat dapat CUST-YYYY-0001 dan syarikat kedua gagal (500). Sertakan id tenant + siri berkunci.
        $tid = app(\App\Support\Tenancy\TenantContext::class)->id();

        return \App\Support\NumberSeries::next('customer', 'C'.$tid);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Check if customer is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check credit limit.
     */
    public function hasExceededCreditLimit(float $amount): bool
    {
        if ($this->credit_limit <= 0) {
            return false; // No credit limit set
        }
        
        // TODO: Calculate outstanding receivables
        $outstanding = 0; // Query AR from Finance module
        
        return ($outstanding + $amount) > $this->credit_limit;
    }
}
