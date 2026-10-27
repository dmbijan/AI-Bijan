<?php

namespace App\Modules\TeamAI\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeamAiRepository extends Model
{
    use BelongsToTenant;

    protected $table = 'team_ai_repositories';

    protected $fillable = [
        'tenant_id',
        'owner',
        'repository',
        'branch',
        'access_token_ciphertext',
        'last_commit_sha',
        'last_synced_at',
    ];

    protected $hidden = ['access_token_ciphertext'];

    protected $casts = ['last_synced_at' => 'datetime'];

    public function assets(): HasMany
    {
        return $this->hasMany(TeamAiAsset::class, 'team_ai_repository_id');
    }
}
