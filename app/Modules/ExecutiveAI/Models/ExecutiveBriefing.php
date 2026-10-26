<?php

namespace App\Modules\ExecutiveAI\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Briefing yang dijana untuk eksekutif.
 */
class ExecutiveBriefing extends Model
{
    use BelongsToTenant;

    protected $table = 'executive_briefings';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'type',
        'content',
        'generated_at',
        'read_at',
    ];

    protected $casts = [
        'content' => 'array',
        'generated_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function isDibaca(): bool
    {
        return $this->read_at !== null;
    }
}
