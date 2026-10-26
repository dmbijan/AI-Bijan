<?php

namespace App\Modules\MediaStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Persona video — avatar & suara untuk penjanaan video.
 *
 * ⚠️ Setiap persona WAJIB ada pengesahan identiti sebelum boleh digunakan
 * untuk video blast (rujukan BAB 82.4.2).
 */
class MediaPersona extends Model
{
    use BelongsToTenant;

    protected $table = 'media_personas';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'avatar_ref',
        'voice_ref',
        'language',
        'consent_consent_id',
        'verification_status',
        'verified_at',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function jobs(): HasMany
    {
        return $this->hasMany(MediaJob::class, 'persona_id');
    }

    /**
     * Bolehkah persona ini digunakan untuk menjana video blast?
     */
    public function bolehGuna(): bool
    {
        return $this->is_active
            && $this->verification_status === 'verified'
            && $this->verified_at !== null
            && $this->avatar_ref !== null;
    }
}
