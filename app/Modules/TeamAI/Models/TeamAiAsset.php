<?php

namespace App\Modules\TeamAI\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamAiAsset extends Model
{
    use BelongsToTenant;

    protected $table = 'team_ai_assets';

    protected $fillable = [
        'tenant_id',
        'team_ai_repository_id',
        'path',
        'category',
        'title',
        'git_sha',
        'content',
    ];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(TeamAiRepository::class, 'team_ai_repository_id');
    }
}
