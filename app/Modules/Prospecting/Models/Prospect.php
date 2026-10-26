<?php

namespace App\Modules\Prospecting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Prospek — calon pelanggan yang belum menjadi marketing_contacts.
 *
 * Consent adalah FIELDS PENUH: blast hanya boleh dihantar jika
 * consent_status = 'granted' DAN consent_evidence tidak kosong.
 */
class Prospect extends Model
{
    use BelongsToTenant;

    protected $table = 'prospects';

    public const CONSENT_UNKNOWN = 'unknown';
    public const CONSENT_GRANTED = 'granted';
    public const CONSENT_DENIED = 'denied';

    public const STATUS_NEW = 'new';
    public const STATUS_ENRICHED = 'enriched';
    public const STATUS_SCORED = 'scored';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_REPLIED = 'replied';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_LOST = 'lost';

    protected $fillable = [
        'tenant_id',
        'source_id',
        'list_id',
        'crm_contact_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'whatsapp_number',
        'company',
        'job_title',
        'website',
        'linkedin',
        'country',
        'company_size',
        'revenue_estimate',
        'enrichment',
        'tags',
        'consent_status',
        'consent_source',
        'consent_at',
        'consent_evidence',
        'consent_expires_at',
        'verification_status',
        'score',
        'score_breakdown',
        'dedupe_hash',
        'status',
        'owner_user_id',
        'first_touched_at',
        'last_touched_at',
    ];

    protected $casts = [
        'enrichment' => 'array',
        'tags' => 'array',
        'score_breakdown' => 'array',
        'consent_at' => 'datetime',
        'consent_expires_at' => 'datetime',
        'first_touched_at' => 'datetime',
        'last_touched_at' => 'datetime',
        'score' => 'integer',
        'revenue_estimate' => 'decimal:2',
    ];

    /**
     * Default nilai untuk medan yang wajib.
     *
     * Eloquent tidak menghantar default jadual SQLite, jadi medan
     * enum/mandu mesti ada default di sini.
     */
    protected $attributes = [
        'consent_status' => self::CONSENT_UNKNOWN,
        'verification_status' => 'pending',
        'status' => self::STATUS_NEW,
        'score' => 0,
    ];

    public function source(): BelongsTo
    {
        return $this->belongsTo(ProspectSource::class, 'source_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'owner_user_id');
    }

    public function blastMessages(): HasMany
    {
        return $this->hasMany(\App\Modules\Blast\Models\BlastMessage::class, 'prospect_id');
    }

    /**
     * Adakah prospect ini boleh dihubungi melalui channel tertentu?
     *
     * PERINGATAN: ini_Size safeguard pertama sahaja. Channel provider
     * juga mesti menyemak semula sebelum hantar.
     */
    public function bolehDihantar(string $channel): bool
    {
        if ($this->consent_status !== self::CONSENT_GRANTED) {
            return false;
        }

        if (empty($this->consent_evidence)) {
            return false;
        }

        // Consent tamat tempoh
        if ($this->consent_expires_at && $this->consent_expires_at->isPast()) {
            return false;
        }

        // Channel mesti ada nombor/alamat yang sesuai
        return match ($channel) {
            'email' => ! empty($this->email),
            'sms', 'whatsapp' => ! empty($this->phone),
            'video' => ! empty($this->email) || ! empty($this->phone),
            default => false,
        };
    }

    public function markTouched(): void
    {
        $now = now();
        $this->first_touched_at = $this->first_touched_at ?: $now;
        $this->last_touched_at = $now;
        $this->save();
    }
}
