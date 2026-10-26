<?php

namespace App\Modules\Cod\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Tetapan COD per tenant.
 */
class CodSetting extends Model
{
    use BelongsToTenant;

    protected $table = 'cod_settings';

    protected $fillable = [
        'tenant_id',
        'enabled',
        'surcharge',
        'min_order_amount',
        'max_order_amount',
        'allowed_couriers',
        'excluded_regions',
        'require_confirmation',
        'require_call_confirmation',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'require_confirmation' => 'boolean',
        'require_call_confirmation' => 'boolean',
        'allowed_couriers' => 'array',
        'excluded_regions' => 'array',
        'surcharge' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_order_amount' => 'decimal:2',
    ];

    protected $attributes = [
        'enabled' => false,
        'surcharge' => 0,
        'min_order_amount' => 0,
        'max_order_amount' => 3000,
        'require_confirmation' => true,
        'require_call_confirmation' => false,
    ];

    /**
     * Ambil tetapan COD, cipta default jika tiada.
     */
    public static function untukTenant(int $tenantId): self
    {
        return static::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['enabled' => false]
        );
    }
}
