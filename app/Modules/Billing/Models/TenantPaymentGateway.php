<?php

namespace App\Modules\Billing\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/** Akaun CHIP milik kedai. Kunci API disulitkan dan tidak pernah dipulangkan dalam API. */
class TenantPaymentGateway extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'provider', 'brand_id', 'api_key', 'public_key', 'is_active', 'verified_at'];

    protected $casts = ['api_key' => 'encrypted', 'is_active' => 'boolean', 'verified_at' => 'datetime'];

    protected $hidden = ['api_key', 'public_key'];

    /** Untuk paparan: 4 aksara terakhir sahaja. */
    public function kunciBertopeng(): string
    {
        return '••••'.substr((string) $this->api_key, -4);
    }
}
