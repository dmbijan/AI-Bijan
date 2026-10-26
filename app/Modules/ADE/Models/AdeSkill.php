<?php

namespace App\Modules\ADE\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ADE Skill — kaedah kerja berulang yang boleh dicetuskan secara automatik.
 *
 * Corak `obra/superpowers` (MIT): skill ialah unit kerja berstruktur yang
 * dikuatkuasakan melalui `skills/` + `hooks/` — bukan sekadar arahan.
 */
class AdeSkill extends Model
{
    protected $table = 'ade_skills';

    protected $fillable = [
        'tenant_id', 'key', 'name', 'category', 'description', 'system_prompt',
        'steps', 'triggers', 'tools', 'config', 'source', 'origin_project',
        'license', 'is_enabled', 'is_public', 'install_count', 'usage_count',
        'success_rate',
    ];

    protected $casts = [
        'steps' => 'array',
        'triggers' => 'array',
        'tools' => 'array',
        'config' => 'array',
        'is_enabled' => 'boolean',
        'is_public' => 'boolean',
        'install_count' => 'integer',
        'usage_count' => 'integer',
        'success_rate' => 'float',
    ];

    protected $attributes = [
        'category' => 'general',
        'source' => 'builtin',
        'origin_project' => 'obra/superpowers',
        'license' => 'MIT',
        'is_enabled' => true,
        'is_public' => false,
        'install_count' => 0,
        'usage_count' => 0,
        'success_rate' => 0.00,
    ];

    public function invocations(): HasMany
    {
        return $this->hasMany(AdeSkillInvocation::class, 'ade_skill_id');
    }

    /**
     * Skill global (tenant_id null) boleh dipasang oleh mana-mana tenant.
     */
    public function isGlobal(): bool
    {
        return $this->tenant_id === null;
    }

    /**
     * Skill dicetuskan oleh sekumpulan pencetus (kata kunci / frasa).
     */
    public function pencetus(): array
    {
        return is_array($this->triggers) ? $this->triggers : [];
    }

    /**
     * Senarai langkah dalam bentuk berstruktur.
     */
    public function langkah(): array
    {
        return is_array($this->steps) ? $this->steps : [];
    }

    /**
     * Kunci unik dalam skop tenant — skill global memerlukan semakan berasingan.
     */
    public static function scopedKey(string $key, ?int $tenantId): string
    {
        return $tenantId === null ? 'global:' . $key : 'tenant:' . $tenantId . ':' . $key;
    }
}
