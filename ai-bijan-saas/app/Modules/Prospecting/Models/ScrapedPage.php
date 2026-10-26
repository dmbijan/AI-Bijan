<?php

namespace App\Modules\Prospecting\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Halaman yang telah diambil — cache supaya tidak diambil semula.
 */
class ScrapedPage extends Model
{
    use BelongsToTenant;

    protected $table = 'scraped_pages';

    protected $fillable = [
        'tenant_id',
        'job_id',
        'source_id',
        'url',
        'final_url',
        'http_status',
        'content_hash',
        'title',
        'extracted',
        'fetched_at',
    ];

    protected $casts = [
        'extracted' => 'array',
        'fetched_at' => 'datetime',
    ];

    public function job(): BelongsTo
    {
        return $this->belongsTo(ScrapingJob::class, 'job_id');
    }
}
