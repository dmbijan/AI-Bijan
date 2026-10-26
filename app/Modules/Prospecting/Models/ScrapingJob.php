<?php

namespace App\Modules\Prospecting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Job scraping —yh fetching halaman awam yang dibenarkan robots.txt.
 */
class ScrapingJob extends Model
{
    use BelongsToTenant;

    protected $table = 'scraping_jobs';

    protected $fillable = [
        'tenant_id',
        'source_id',
        'name',
        'target_url_patterns',
        'status',
        'respect_robots',
        'rate_limit_per_minute',
        'total_fetched',
        'total_skipped',
        'total_extracted',
        'started_at',
        'finished_at',
        'error',
    ];

    protected $casts = [
        'target_url_patterns' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'total_fetched' => 'integer',
        'total_skipped' => 'integer',
        'total_extracted' => 'integer',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(ScrapedPage::class, 'job_id');
    }

    public function isFinished(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled'], true);
    }
}
