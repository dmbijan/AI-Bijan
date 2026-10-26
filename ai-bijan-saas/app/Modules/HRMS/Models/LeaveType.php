<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    use BelongsToTenant;

    use HasFactory;

    protected $table = 'hrms_leave_types';

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'max_days_per_year',
        'encashable',
        'requires_document',
        'description',
        'is_active',
    ];

    protected $casts = [
        'encashable' => 'boolean',
        'requires_document' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the leave type.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }

    /**
     * Get leave allocations for this leave type.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(LeaveAllocation::class);
    }

    /**
     * Get leave requests for this leave type.
     */
    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
