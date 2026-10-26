<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Pelanggan perkhidmatan — profil lanjutan untuk perniagaan berasaskan projek.
 *
 * BUKAN pengganti `CRM\Customer`: `customer_id` boleh memaut ke rekod CRM
 * supaya tidak berlaku pendua data pelanggan (BAB 87.8).
 */
class ServiceClient extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public const TIER_REGULAR = 'regular';
    public const TIER_SILVER = 'silver';
    public const TIER_GOLD = 'gold';
    public const TIER_PLATINUM = 'platinum';

    public const TIERS = [
        self::TIER_REGULAR,
        self::TIER_SILVER,
        self::TIER_GOLD,
        self::TIER_PLATINUM,
    ];

    protected $table = 'service_clients';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'display_name',
        'company_name',
        'email',
        'phone',
        'whatsapp',
        'address',
        'city',
        'state',
        'postcode',
        'country',
        'source',
        'tier',
        'lifetime_value',
        'total_projects',
        'notes',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
        'lifetime_value' => 'decimal:2',
        'total_projects' => 'integer',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(ServiceProject::class, 'client_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(ServiceQuotation::class, 'client_id');
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(ServiceContract::class, 'client_id');
    }

    /** Nama paparan untuk UI / dokumen. */
    public function namaPenuh(): string
    {
        return $this->company_name
            ? "{$this->display_name} ({$this->company_name})"
            : (string) $this->display_name;
    }
}
