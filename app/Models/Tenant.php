<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasUlids;

    protected $fillable = ['name', 'slug', 'status', 'plan_id', 'timezone', 'currency', 'locale', 'trial_ends_at'];

    protected $casts = ['trial_ends_at' => 'datetime'];

    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(TenantUser::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_users')->withPivot('status');
    }

    public function isUsable(): bool
    {
        return in_array($this->status, ['trial', 'active', 'past_due'], true);
    }
}
