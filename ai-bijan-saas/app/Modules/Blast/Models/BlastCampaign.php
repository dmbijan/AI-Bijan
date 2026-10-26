<?php

namespace App\Modules\Blast\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kempen blast — satu kempen, satu atau lebih channel.
 */
class BlastCampaign extends Model
{
    use BelongsToTenant;

    protected $table = 'blast_campaigns';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_REVIEW = 'review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_RUNNING = 'running';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_SMS = 'sms';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const CHANNEL_VIDEO = 'video';
    public const CHANNEL_MULTI = 'multi';

    protected $fillable = [
        'tenant_id',
        'name',
        'channel',
        'subject',
        'body',
        'media_asset_id',
        'sequence_id',
        'status',
        'approval_state',
        'approved_by',
        'approved_at',
        'scheduled_at',
        'started_at',
        'completed_at',
        'autonomy_level',
        'stats',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'stats' => 'array',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'autonomy_level' => 'integer',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(BlastMessage::class, 'campaign_id');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Bolehkah kempen ini dihantar sekarang?
     */
    public function bolehDihantar(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_SCHEDULED,
            self::STATUS_RUNNING,
        ], true);
    }

    public function isSelesai(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ], true);
    }
}
