<?php

namespace App\Modules\Link\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LinkPage extends Model
{
    use BelongsToTenant;

    use HasFactory, SoftDeletes;

    protected $table = 'link_pages';

    protected $fillable = [
        'tenant_id',
        'title',
        'slug',
        'short_code',
        'description',
        'type',
        'content',
        'redirect_url',
        'thumbnail',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'custom_css',
        'custom_js',
        'status',
        'expiry_date',
        'password_protected',
        'password_hash',
        'views_count',
        'unique_views_count',
        'conversions_count',
    ];

    protected $casts = [
        'content' => 'array',
        'expiry_date' => 'date',
        'password_protected' => 'boolean',
    ];

    /**
     * Get the tenant that owns the link page.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get all clicks on this link page.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Scope for active links.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            });
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if link is valid.
     */
    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date < now()) {
            return false;
        }

        return true;
    }

    /**
     * Increment views count.
     */
    public function incrementViews(int $count = 1): self
    {
        $this->increment('views_count', $count);
        return $this;
    }

    /**
     * Increment unique views count.
     */
    public function incrementUniqueViews(): self
    {
        $this->increment('unique_views_count');
        return $this;
    }

    /**
     * Increment conversions count.
     */
    public function incrementConversions(): self
    {
        $this->increment('conversions_count');
        return $this;
    }

    /**
     * Get click-through rate.
     */
    public function getClickThroughRateAttribute(): float
    {
        return $this->views_count > 0 
            ? round(($this->clicks()->count() / $this->views_count) * 100, 2) 
            : 0;
    }
}
