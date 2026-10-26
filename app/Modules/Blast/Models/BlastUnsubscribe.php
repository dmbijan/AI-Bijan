<?php

namespace App\Modules\Blast\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Langganan (unsubscribe) — JWT short-code untuk one-click unsubscribe.
 */
class BlastUnsubscribe extends Model
{
    use BelongsToTenant;

    protected $table = 'blast_unsubscribes';

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'prospect_id',
        'channel',
        'email',
        'phone',
        'email_hash',
        'phone_hash',
        'token',
        'reason',
        'source',
        'ip_address',
    ];

    protected $casts = [
        'ip_address' => 'string',
    ];
}
