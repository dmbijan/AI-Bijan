<?php

namespace App\Modules\OmniOrder\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Kurier — penyedia penghantaran yang disokong (8+).
 *
 * Berasal daripada Luxana.
 */
class OmniCourier extends Model
{
    use BelongsToTenant;

    protected $table = 'omni_couriers';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'provider',
        'supports_cod',
        'supports_awb',
        'api_endpoint',
        'config',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'supports_cod' => 'boolean',
        'supports_awb' => 'boolean',
        'config' => 'array',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $attributes = [
        'supports_cod' => false,
        'supports_awb' => true,
        'is_active' => true,
        'sort_order' => 0,
    ];

    public function bolehCod(): bool
    {
        return $this->supports_cod && $this->is_active;
    }
}
