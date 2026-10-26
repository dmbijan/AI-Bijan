<?php

namespace App\Modules\Blast\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Senarai penindihan — nombor/e-mel yang TIDAK BOLEH dihubungi.
 *
 * Sumber: unsubscribe, bounce keras,-complaint, atau manual.
 */
class BlastSuppression extends Model
{
    use BelongsToTenant;

    protected $table = 'blast_suppressions';

    protected $fillable = [
        'tenant_id',
        'hash',
        'channel',
        'email',
        'phone',
        'reason',
        'source',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function reasonFor(string $status): ?string
    {
        return match ($status) {
            BlastMessage::STATUS_BOUNCED => 'bounce',
            BlastMessage::STATUS_UNSUBSCRIBED => 'unsubscribe',
            default => null,
        };
    }
}
