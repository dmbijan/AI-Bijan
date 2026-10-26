<?php

namespace App\Modules\Affiliate\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateClick extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'affiliate_clicks';

    protected $fillable = [
        'tenant_id',
        'affiliate_id',
        'referral_code_id',
        'click_id',
        'ip_address',
        'user_agent',
        'referrer_url',
        'landing_url',
        'country',
        'region',
        'city',
        'device',
        'browser',
        'os',
        'converted',
        'converted_at',
    ];

    protected $casts = [
        'converted' => 'boolean',
        'converted_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the click.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the affiliate who owns the referral link.
     */
    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    /**
     * Get the referral code clicked.
     */
    public function referralCode(): BelongsTo
    {
        return $this->belongsTo(ReferralCode::class, 'referral_code_id');
    }

    /**
     * Scope for today's clicks.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Scope for this week's clicks.
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for converted clicks.
     */
    public function scopeConverted($query)
    {
        return $query->where('converted', true);
    }

    /**
     * Mark click as converted.
     */
    public function markAsConverted(): void
    {
        $this->update([
            'converted' => true,
            'converted_at' => now(),
        ]);
    }
}
