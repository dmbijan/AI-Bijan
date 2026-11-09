<?php

namespace App\Modules\BLive\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Live metric - satu ukuran analitik.
 *
 * Menyimpan metrik sebagai baris (bukan lajur) supaya skema kekal
 * stabil apabila metrik baharu ditambah tanpa migrasi.
 */
class LiveMetric extends Model
{
    use BelongsToTenant;

    protected $table = 'blive_live_metrics';

    public const SCOPE_SESSION = 'session';

    public const SCOPE_DESTINATION = 'destination';

    public const SCOPE_DAILY = 'daily';

    protected $fillable = [
        'tenant_id',
        'live_session_id',
        'metric_key',
        'metric_scope',
        'value_numeric',
        'value_decimal',
        'platform',
        'recorded_on',
        'dimensions',
    ];

    protected $casts = [
        'value_numeric' => 'integer',
        'value_decimal' => 'float',
        'recorded_on' => 'date',
        'dimensions' => 'array',
    ];

    protected $attributes = [
        'metric_scope' => self::SCOPE_SESSION,
        'value_numeric' => 0,
        'value_decimal' => 0,
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class, 'live_session_id');
    }

    /**
     * Kunci metrik standard.
     *
     * @return array<string, string>
     */
    public static function kunci(): array
    {
        return [
            'viewers_peak' => 'Penonton puncak',
            'viewers_average' => 'Purata penonton',
            'comments_received' => 'Komen diterima',
            'comments_answered' => 'Komen dijawab',
            'speech_count' => 'Bilangan ucapan hos',
            'takeovers' => 'Bilangan takeover',
            'escalations' => 'Eskalasi kepada manusia',
            'product_clicks' => 'Klik produk',
            'conversions' => 'Penukaran jualan',
            'render_minutes' => 'Menit rendering',
            'cost_rm' => 'Kos (RM)',
        ];
    }
}
