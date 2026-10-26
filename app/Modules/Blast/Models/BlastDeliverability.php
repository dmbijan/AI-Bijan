<?php

namespace App\Modules\Blast\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Deliverability per domain — pantau bounce & complaint.
 */
class BlastDeliverability extends Model
{
    use BelongsToTenant;

    protected $table = 'blast_deliverability';

    protected $fillable = [
        'tenant_id',
        'domain',
        'daily_quota',
        'sent',
        'delivered',
        'bounced',
        'complaints',
        'opened',
        'clicked',
        'reputation_score',
        'last_reset_at',
        'is_paused',
        'pause_reason',
    ];

    protected $casts = [
        'last_reset_at' => 'datetime',
        'sent' => 'integer',
        'delivered' => 'integer',
        'bounced' => 'integer',
        'complaints' => 'integer',
        'opened' => 'integer',
        'clicked' => 'integer',
        'daily_quota' => 'integer',
        'reputation_score' => 'integer',
        'is_paused' => 'boolean',
    ];

    /**
     * Peratus bounce — jika melebihi ambang, kempen dijeda.
     */
    public function bounceRate(): float
    {
        if ($this->sent === 0) {
            return 0.0;
        }

        return round(($this->bounced / $this->sent) * 100, 2);
    }

    /**
     * Peratus complaint — had sangat ketat.
     */
    public function complaintRate(): float
    {
        if ($this->sent === 0) {
            return 0.0;
        }

        return round(($this->complaints / $this->sent) * 100, 3);
    }
}
