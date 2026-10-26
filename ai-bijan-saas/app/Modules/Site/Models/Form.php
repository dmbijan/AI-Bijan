<?php

namespace App\Modules\Site\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    use BelongsToTenant;

    protected $table = 'site_forms';

    protected $fillable = [
        'tenant_id',
        'site_id',
        'name',
        'slug',
        'title',
        'description',
        'fields',
        'submit_action',
        'success_message',
        'is_active',
        'spam_protection',
        'notification_email',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_active' => 'boolean',
        'spam_protection' => 'array',
        'notification_email' => 'array',
    ];

    /**
     * Get the tenant that owns the form.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get the site this form belongs to.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * Get all form submissions.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class, 'form_id');
    }

    /**
     * Generate slug from name.
     */
    public static function generateSlug(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]+/', '-', $name));
    }

    /**
     * Scope active forms.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
