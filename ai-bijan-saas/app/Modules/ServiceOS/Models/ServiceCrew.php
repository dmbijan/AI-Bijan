<?php

namespace App\Modules\ServiceOS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kru / pekerja perkhidmatan.
 *
 * `user_id` memaut ke akaun sistem (jika ada), `employee_id` ke HRMS.
 * Freelancer biasanya tiada `user_id` dan tiada akses kewangan.
 */
class ServiceCrew extends Model
{
    use BelongsToTenant;

    public const ROLE_LEAD = 'lead';
    public const ROLE_CREW = 'crew';
    public const ROLE_ASSISTANT = 'assistant';
    public const ROLE_SPECIALIST = 'specialist';
    public const ROLE_DRIVER = 'driver';
    public const ROLE_COORDINATOR = 'coordinator';

    public const ROLES = [
        self::ROLE_LEAD,
        self::ROLE_CREW,
        self::ROLE_ASSISTANT,
        self::ROLE_SPECIALIST,
        self::ROLE_DRIVER,
        self::ROLE_COORDINATOR,
    ];

    protected $table = 'service_crews';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'employee_id',
        'name',
        'role',
        'phone',
        'email',
        'day_rate',
        'hour_rate',
        'skills',
        'is_freelance',
        'is_active',
    ];

    protected $casts = [
        'day_rate' => 'decimal:2',
        'hour_rate' => 'decimal:2',
        'skills' => 'array',
        'is_freelance' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(ServiceAssignment::class, 'crew_id');
    }

    /** Adakah kru ini mempunyai kemahiran tertentu? */
    public function adaKemahiran(string $kemahiran): bool
    {
        $skills = array_map('strtolower', (array) ($this->skills ?? []));

        return in_array(strtolower($kemahiran), $skills, true);
    }

    /** Kira kos untuk sesuatu tempoh (hari). */
    public function kiraKosHarian(float $hari = 1.0): float
    {
        return round((float) $this->day_rate * $hari, 2);
    }
}
