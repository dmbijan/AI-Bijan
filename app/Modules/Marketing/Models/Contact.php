<?php

namespace App\Modules\Marketing\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use BelongsToTenant;

    protected $table = 'marketing_contacts';

    protected $fillable = [
        'tenant_id',
        'segment_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company',
        'job_title',
        'source',
        'properties',
        'tags',
        'lead_score',
        'status',
        'opt_in_email',
        'opt_in_sms',
        'opt_in_whatsapp',
        'consent_data',
        'last_activity_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'tags' => 'array',
        'lead_score' => 'integer',
        'opt_in_email' => 'boolean',
        'opt_in_sms' => 'boolean',
        'opt_in_whatsapp' => 'boolean',
        'consent_data' => 'array',
        'last_activity_at' => 'datetime',
    ];

    /**
     * Get the tenant that owns the contact.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the segment this contact belongs to.
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class, 'segment_id');
    }

    /**
     * Get all deals for this contact.
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'contact_id');
    }

    /**
     * Get all vouchers used by this contact.
     */
    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'marketing_voucher_usage')
            ->withTimestamps()
            ->withPivot(['used_at', 'order_id', 'amount_used']);
    }

    /**
     * Get all lead scores for this contact.
     */
    public function leadScores(): HasMany
    {
        return $this->hasMany(LeadScore::class, 'contact_id');
    }

    /**
     * Scope by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by source.
     */
    public function scopeBySource($query, string $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Scope contacts with tag.
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }

    /**
     * Scope opted in for email.
     */
    public function scopeEmailOptIn($query)
    {
        return $query->where('opt_in_email', true);
    }

    /**
     * Add a tag to contact.
     */
    public function addTag(string $tag): void
    {
        $tags = $this->tags ?? [];
        if (!in_array($tag, $tags)) {
            $tags[] = $tag;
            $this->update(['tags' => $tags]);
        }
    }

    /**
     * Remove a tag from contact.
     */
    public function removeTag(string $tag): void
    {
        $tags = collect($this->tags ?? [])
            ->reject(fn($t) => $t === $tag)
            ->values()
            ->toArray();
        $this->update(['tags' => $tags]);
    }

    /**
     * Update last activity.
     */
    public function updateLastActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Check if contact is opted in for a channel.
     */
    public function isOptedIn(string $channel): bool
    {
        $field = 'opt_in_' . strtolower($channel);
        return $this->$field ?? false;
    }
}
