<?php

namespace App\Modules\POS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class POSRegister extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'pos_registers';

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'location',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the cash shifts for this register.
     */
    public function cashShifts(): HasMany
    {
        return $this->hasMany(CashShift::class, 'register_id');
    }

    /**
     * Get the sales for this register.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(POSSale::class, 'register_id');
    }

    /**
     * Generate register code.
     */
    public static function generateCode(): string
    {
        $lastCode = static::orderBy('code', 'desc')->value('code');

        if (! $lastCode) {
            return 'POS-REG-001';
        }

        $number = intval(substr($lastCode, -3)) + 1;

        return 'POS-REG-'.str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Scope by tenant.
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Scope active registers only.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
