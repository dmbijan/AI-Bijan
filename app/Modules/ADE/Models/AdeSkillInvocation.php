<?php

namespace App\Modules\ADE\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rekod pemanggilan skill — jejak audit + sumber pembelajaran.
 */
class AdeSkillInvocation extends Model
{
    use BelongsToTenant;

    protected $table = 'ade_skill_invocations';

    protected $fillable = [
        'tenant_id', 'ade_skill_id', 'context_type', 'context_ref',
        'input_summary', 'output_summary', 'succeeded', 'score',
        'duration_ms', 'meta',
    ];

    protected $casts = [
        'succeeded' => 'boolean',
        'score' => 'float',
        'duration_ms' => 'integer',
        'meta' => 'array',
    ];

    protected $attributes = [
        'context_type' => 'manual',
        'succeeded' => true,
    ];

    public function skill(): BelongsTo
    {
        return $this->belongsTo(AdeSkill::class, 'ade_skill_id');
    }
}
