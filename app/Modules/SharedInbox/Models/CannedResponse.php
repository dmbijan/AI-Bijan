<?php

namespace App\Modules\SharedInbox\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ulasan sedia ada (canned response) — teks pintasan untuk ejen.
 */
class CannedResponse extends Model
{
    use BelongsToTenant;

    protected $table = 'canned_responses';

    protected $fillable = [
        'tenant_id',
        'shared_inbox_id',
        'short_code',
        'title',
        'content',
        'category',
        'language',
        'usage_count',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'short_code' => 'hello',
        'language' => 'ms',
        'usage_count' => 0,
        'is_active' => true,
    ];

    public function sharedInbox(): BelongsTo
    {
        return $this->belongsTo(SharedInbox::class, 'shared_inbox_id');
    }
}
