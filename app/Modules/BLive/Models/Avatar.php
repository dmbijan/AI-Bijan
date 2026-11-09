<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Avatar - manusia digital untuk host live.
 *
 * Avatar ialah aset beriversi: setiap perubahan identiti menaikkan
 * `asset_version` supaya manifest render boleh dijejak.
 *
 * PENTING: avatar hanya boleh digunakan untuk live jika `consent_status`
 * ialah `granted`. Ini dijaga oleh ConsentService, bukan oleh model.
 */
class Avatar extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_avatars';

    public const TYPE_AI_GENERATED = 'ai_generated';

    public const TYPE_PHOTO = 'photo_to_avatar';

    public const TYPE_DIGITAL_TWIN = 'custom_digital_twin';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_GENERATING = 'generating';

    public const STATUS_READY = 'ready';

    public const STATUS_FAILED = 'failed';

    public const STATUS_ARCHIVED = 'archived';

    public const CONSENT_PENDING = 'pending';

    public const CONSENT_GRANTED = 'granted';

    public const CONSENT_REVOKED = 'revoked';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'name',
        'slug',
        'description',
        'avatar_type',
        'engine',
        'model_reference',
        'asset_version',
        'voice_profile_id',
        'host_personality_id',
        'facial_profile',
        'animation_profile',
        'consent_record_id',
        'render_quality',
        'asset_storage_path',
        'appearance',
        'body_traits',
        'status',
        'consent_status',
        'consent_reference',
        'is_ai_disclosed',
        'render_minutes_used',
        'created_by',
        'generated_at',
    ];

    protected $casts = [
        'appearance' => 'array',
        'body_traits' => 'array',
        'asset_version' => 'integer',
        'render_minutes_used' => 'integer',
        'is_ai_disclosed' => 'boolean',
        'generated_at' => 'datetime',
    ];

    protected $attributes = [
        'avatar_type' => self::TYPE_AI_GENERATED,
        'engine' => 'simulated',
        'asset_version' => 1,
        'render_quality' => 'high',
        'status' => self::STATUS_DRAFT,
        'consent_status' => self::CONSENT_PENDING,
        'is_ai_disclosed' => true,
        'render_minutes_used' => 0,
    ];

    public function personality(): BelongsTo
    {
        return $this->belongsTo(HostPersonality::class, 'host_personality_id');
    }

    public function consentRecord(): BelongsTo
    {
        return $this->belongsTo(ConsentRecord::class, 'consent_record_id');
    }

    public function liveSessions(): HasMany
    {
        return $this->hasMany(LiveSession::class, 'avatar_id');
    }

    public function renderJobs(): HasMany
    {
        return $this->hasMany(RenderJob::class, 'avatar_id');
    }

    /**
     * Adakah avatar sedia digunakan untuk live?
     */
    public function bolehLive(): bool
    {
        return $this->status === self::STATUS_READY
            && $this->consent_status === self::CONSENT_GRANTED;
    }

    /**
     * Adakah avatar sudah dijana (memegang manifest aset)?
     */
    public function dijana(): bool
    {
        return $this->status === self::STATUS_READY
            || $this->status === self::STATUS_GENERATING;
    }

    /**
     * Senarai Appearance traits yang disokong.
     *
     * @return array<string, string>
     */
    public static function ciriPenampilan(): array
    {
        return [
            'gender' => 'Jantina',
            'age_visual' => 'Umur visual',
            'face_shape' => 'Bentuk muka',
            'hair_style' => 'Gaya rambut',
            'hair_color' => 'Warna rambut',
            'eye_color' => 'Warna mata',
            'skin_tone' => 'Tona kulit',
            'build' => 'Bentuk badan',
            'height' => 'Ketinggian',
            'outfit' => 'Pakaian',
            'accessories' => 'Aksesori',
            'expression' => 'Ekspresi muka',
        ];
    }

    /**
     * Senarai enjin avatar.
     *
     * @return array<string, string>
     */
    public static function enjin(): array
    {
        return [
            'simulated' => 'Simulated (tanpa GPU - manifest deterministik)',
            'metahuman' => 'Unreal Engine MetaHuman',
            'http' => 'HTTP engine tersuai',
        ];
    }
}
