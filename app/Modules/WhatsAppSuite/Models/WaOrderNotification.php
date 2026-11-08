<?php

namespace App\Modules\WhatsAppSuite\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Pemberitahuan pesanan — hantar WhatsApp automatik pada perubahan status.
 *
 * Berasal daripada WasapBot MY (Order Notification: WooCommerce, Shopify,
 * Shoppegram, Onpay).
 */
class WaOrderNotification extends Model
{
    use BelongsToTenant;

    protected $table = 'wa_order_notifications';

    public const TRIGGER_THANK_YOU = 'thank_you';

    public const TRIGGER_COMPLETED = 'completed';

    public const TRIGGER_ABANDONED_CART = 'abandoned_cart';

    public const TRIGGER_REPEAT_ORDER = 'repeat_order';

    public const TRIGGER_TRACKING = 'tracking';

    public const TRIGGER_SHIPPED = 'shipped';

    public const TRIGGER_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id',
        'wa_device_id',
        'name',
        'trigger',
        'source_platform',
        'message_template',
        'media_url',
        'delay_minutes',
        'is_active',
        'send_count',
        'last_sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'delay_minutes' => 'integer',
        'send_count' => 'integer',
        'last_sent_at' => 'datetime',
    ];

    protected $attributes = [
        'trigger' => self::TRIGGER_THANK_YOU,
        'delay_minutes' => 0,
        'is_active' => true,
        'send_count' => 0,
    ];

    /**
     * Ganti pemboleh ubah templat.
     *
     * @param  array<string, string>  $pembolehUbah
     */
    public function render(array $pembolehUbah): string
    {
        $teks = (string) $this->message_template;

        foreach ($pembolehUbah as $kunci => $nilai) {
            $teks = str_replace('{'.$kunci.'}', (string) $nilai, $teks);
        }

        return $teks;
    }

    public static function senaraiPencetus(): array
    {
        return [
            self::TRIGGER_THANK_YOU => 'Ucapan Terima Kasih',
            self::TRIGGER_COMPLETED => 'Pesanan Selesai',
            self::TRIGGER_ABANDONED_CART => 'Troli Ditinggalkan',
            self::TRIGGER_REPEAT_ORDER => 'Beli Semula',
            self::TRIGGER_TRACKING => 'Nombor Tracking',
            self::TRIGGER_SHIPPED => 'Dihantar',
            self::TRIGGER_CANCELLED => 'Dibatalkan',
        ];
    }
}
