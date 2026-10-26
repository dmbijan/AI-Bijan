<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'code', 'name', 'name_bm', 'description', 'tier',
        'max_users', 'max_locations', 'storage_mb', 'ai_credits_monthly',
        'modules', 'price_monthly', 'price_yearly', 'currency', 'trial_days',
        'is_public', 'features', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'modules' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_users' => 'integer',
        'max_locations' => 'integer',
        'storage_mb' => 'integer',
        'ai_credits_monthly' => 'integer',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];
}
