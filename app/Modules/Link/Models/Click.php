<?php

namespace App\Modules\Link\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'link_clicks';

    protected $fillable = [
        'tenant_id',
        'link_page_id',
        'card_id',
        'click_id',
        'ip_address',
        'user_agent',
        'referrer_url',
        'country',
        'region',
        'city',
        'device',
        'browser',
        'os',
    ];

    /**
     * Get the tenant that owns the click.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the link page clicked.
     */
    public function linkPage(): BelongsTo
    {
        return $this->belongsTo(LinkPage::class);
    }

    /**
     * Get the digital card clicked.
     */
    public function digitalCard(): BelongsTo
    {
        return $this->belongsTo(DigitalCard::class, 'card_id');
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
}
