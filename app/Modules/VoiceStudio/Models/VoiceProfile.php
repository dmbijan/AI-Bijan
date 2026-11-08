<?php

namespace App\Modules\VoiceStudio\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Profil suara — timbre, gaya, bahasa dan tetapan sintesis.
 *
 * inspired oleh https://github.com/debpalash/VoiceStudio
 */
class VoiceProfile extends Model
{
    use BelongsToTenant;

    protected $table = 'voice_profiles';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ARCHIVED = 'archived';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'language',
        'locale',
        'gender',
        'age_range',
        'pitch',
        'speed',
        'timbre',
        'emotion',
        'engine',
        'engine_voice_id',
        'sample_path',
        'status',
        'is_default',
        'is_cloned',
        'settings',
        'usage_count',
        'total_characters',
    ];

    protected $casts = [
        'settings' => 'array',
        'pitch' => 'float',
        'speed' => 'float',
        'is_default' => 'boolean',
        'is_cloned' => 'boolean',
        'usage_count' => 'integer',
        'total_characters' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'language' => 'ms',
        'locale' => 'ms-MY',
        'pitch' => 1.0,
        'speed' => 1.0,
        'is_default' => false,
        'is_cloned' => false,
        'usage_count' => 0,
        'total_characters' => 0,
    ];

    /**
     * Bahasa yang disokong.
     *
     * @return array<string, string>
     */
    public static function bahasa(): array
    {
        return [
            'ms' => 'Bahasa Melayu',
            'en' => 'English',
            'zh' => '中文 (Chinese)',
            'ta' => 'தமிழ் (Tamil)',
            'id' => 'Bahasa Indonesia',
            'ar' => 'العربية (Arabic)',
            'hi' => 'हिन्दी (Hindi)',
        ];
    }

    public static function gaya(): array
    {
        return ['neutral', 'friendly', 'professional', 'narration', 'advertisement', 'news', 'conversation'];
    }
}
