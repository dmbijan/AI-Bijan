<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Consent record - bukti kebenaran penggunaan identiti / suara.
 *
 * Wajib untuk avatar jenis `photo_to_avatar` dan `custom_digital_twin`.
 * Avatar `ai_generated` masih memerlukan rekod, tetapi ia boleh
 * diluluskan automatik oleh sistem kerana tiada identiti sebenar digunakan.
 */
class ConsentRecord extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_consent_records';

    public const TYPE_AI_GENERATED = 'ai_generated';

    public const TYPE_LICENSED = 'licensed';

    public const TYPE_INDIVIDUAL_RELEASE = 'individual_release';

    public const STATUS_PENDING = 'pending';

    public const STATUS_GRANTED = 'granted';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'tenant_id',
        'uuid',
        'avatar_id',
        'consent_type',
        'subject_name',
        'subject_reference',
        'status',
        'evidence_path',
        'notes',
        'granted_at',
        'revoked_at',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'granted_at' => 'datetime',
        'revoked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected $attributes = [
        'consent_type' => self::TYPE_AI_GENERATED,
        'status' => self::STATUS_PENDING,
    ];

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    /**
     * Adakah kebenaran ini sah pada masa ini?
     */
    public function sah(): bool
    {
        if ($this->status !== self::STATUS_GRANTED) {
            return false;
        }

        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
