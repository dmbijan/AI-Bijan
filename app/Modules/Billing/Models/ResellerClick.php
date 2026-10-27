<?php

namespace App\Modules\Billing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Klik pautan rujukan `/r/{code}`.
 *
 * ⚠️ PDPA: IP TIDAK disimpan. Hanya `ip_hash` (SHA-256 dengan app key) dan
 * `user_agent_hash`. Tujuannya atribusi, bukan pengenalan individu.
 */
class ResellerClick extends Model
{
    protected $table = 'reseller_clicks';

    protected $fillable = [
        'reseller_id',
        'code',
        'ip_hash',
        'user_agent_hash',
        'referrer',
        'landing_url',
        'utm_source',
        'utm_campaign',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    /** Hash IP — tidak boleh dibalikkan tanpa app key. */
    public static function hashIp(?string $ip): ?string
    {
        if ($ip === null || $ip === '') {
            return null;
        }

        return hash_hmac('sha256', $ip, config('app.key', 'aibijan'));
    }
}
