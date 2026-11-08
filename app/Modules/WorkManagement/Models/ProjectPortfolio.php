<?php

namespace App\Modules\WorkManagement\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Modules\Projects\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Portfolio — kumpulan projek yang dipantau bersama (OpenProject portfolios).
 */
class ProjectPortfolio extends Model
{
    use BelongsToTenant;

    protected $table = 'project_portfolios';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'description',
        'owner_id',
        'status',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    protected $attributes = [
        'status' => 'active',
        'is_active' => true,
    ];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(
            Project::class,
            'project_portfolio_project',
            'portfolio_id',
            'project_id'
        )->withTimestamps();
    }
}
