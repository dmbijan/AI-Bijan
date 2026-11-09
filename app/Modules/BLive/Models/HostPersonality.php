<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Host personality - personaliti hos AI.
 *
 * Menentukan archetypes, bahasa, nada bicara dan arahan sistem yang
 * menjadi asas Watak host. Setiap personaliti perlu disahkan
 * (is_active) sebelum boleh digunakan untuk live.
 */
class HostPersonality extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_host_personalities';

    public const ARCHETYPE_PROFESSIONAL = 'professional_presenter';

    public const ARCHETYPE_FRIENDLY = 'friendly_salesperson';

    public const ARCHETYPE_INFLUENCER = 'energetic_influencer';

    public const ARCHETYPE_LUXURY = 'luxury_brand_ambassador';

    public const ARCHETYPE_NEWS = 'news_presenter';

    public const ARCHETYPE_INSTRUCTOR = 'educational_instructor';

    public const ARCHETYPE_SUPPORT = 'customer_service_representative';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'archetype',
        'primary_language',
        'locale',
        'supported_languages',
        'system_instructions',
        'tone_profile',
        'speaking_rate',
        'allow_code_switching',
        'max_speech_characters',
        'is_default',
        'is_active',
        'safety_policies',
        'created_by',
    ];

    protected $casts = [
        'supported_languages' => 'array',
        'tone_profile' => 'array',
        'safety_policies' => 'array',
        'speaking_rate' => 'float',
        'allow_code_switching' => 'boolean',
        'max_speech_characters' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'archetype' => self::ARCHETYPE_PROFESSIONAL,
        'primary_language' => 'ms',
        'locale' => 'ms-MY',
        'speaking_rate' => 1.0,
        'allow_code_switching' => true,
        'is_default' => false,
        'is_active' => true,
    ];

    /**
     * Senarai archetypes yang disokong.
     *
     * @return array<string, string>
     */
    public static function archetypes(): array
    {
        return [
            self::ARCHETYPE_PROFESSIONAL => 'Professional Presenter',
            self::ARCHETYPE_FRIENDLY => 'Friendly Salesperson',
            self::ARCHETYPE_INFLUENCER => 'Energetic Influencer',
            self::ARCHETYPE_LUXURY => 'Luxury Brand Ambassador',
            self::ARCHETYPE_NEWS => 'News Presenter',
            self::ARCHETYPE_INSTRUCTOR => 'Educational Instructor',
            self::ARCHETYPE_SUPPORT => 'Customer Service Representative',
        ];
    }

    public function avatars(): HasMany
    {
        return $this->hasMany(Avatar::class, 'host_personality_id');
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class, 'host_personality_id');
    }

    /**
     * Had aksara untuk satu utterances, dengan fallback ke konfigurasi modul.
     */
    public function hadUcapan(): int
    {
        return (int) ($this->max_speech_characters ?: config('blive.limits.max_speech_characters', 2000));
    }
}
