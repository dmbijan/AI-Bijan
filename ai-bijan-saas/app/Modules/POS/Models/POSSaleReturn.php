<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class POSSaleReturn extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'pos_sale_returns';

    protected $fillable = [
        'tenant_id',
        'sale_id',
        'returned_by',
        'approved_by',
        'return_no',
        'status',
        'return_amount',
        'reason',
        'approved_at',
    ];

    protected $casts = [
        'return_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the original sale.
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(POSSale::class, 'sale_id');
    }

    /**
     * Get the user who returned the items.
     */
    public function returner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'returned_by');
    }

    /**
     * Get the approver.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /**
     * Generate unique return number.
     */
    public static function generateReturnNo(): string
    {
        $year = now()->format('Y');
        $lastNo = static::whereYear('created_at', $year)
            ->orderBy('return_no', 'desc')
            ->value('return_no');

        $number = $lastNo ? intval(substr($lastNo, -6)) + 1 : 1;

        return 'RET-' . $year . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
