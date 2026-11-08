<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\WhatsAppSuite\Models\WaAutoReply;
use App\Modules\WhatsAppSuite\Models\WaCampaign;
use App\Modules\WhatsAppSuite\Models\WaContact;
use App\Modules\WhatsAppSuite\Models\WaDevice;
use App\Modules\WhatsAppSuite\Models\WaMessage;
use App\Modules\WhatsAppSuite\Models\WaOrderNotification;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul WhatsAppSuite (automasi WhatsApp, asal: WasapBot MY).
 *
 * Ditambah 9 Nov 2026. Nombor telefon & mesej pelanggan adalah data
 * peribadi;campaign blast mendedahkan senarai penerima.
 */
class WhatsAppSuiteTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'WhatsAppSuite';

    protected array $model = [
        WaDevice::class,
        WaContact::class,
        WaCampaign::class,
        WaAutoReply::class,
        WaMessage::class,
        WaOrderNotification::class,
    ];

    protected ?string $laluanApi = '/api/v1/whatsapp-suite/devices';

    protected ?string $namaRahsia = 'Peranti Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        switch ($this->rekodSemasa) {
            case WaDevice::class:
                return [
                    'name' => 'Peranti Rahsia Tenant Satu '.$n,
                    'phone_number' => '60123456'.str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                    'status' => WaDevice::STATUS_CONNECTED,
                    'daily_limit' => 200,
                ];

            case WaContact::class:
                return [
                    'phone_number' => '601987654'.($n % 10),
                    'name' => 'Kontak Rahsia Tenant Satu '.$n,
                    'labels' => ['lead-'.$n],
                    'status' => WaContact::STATUS_VALID,
                ];

            case WaCampaign::class:
                $peranti = WaDevice::create([
                    'name' => 'Peranti Kempen '.$n,
                    'phone_number' => '601111111'.($n % 10),
                    'status' => WaDevice::STATUS_CONNECTED,
                ]);

                return [
                    'wa_device_id' => $peranti->id,
                    'name' => 'Kempen Rahsia Tenant Satu '.$n,
                    'message' => 'Mesej Kempen Rahsia Tenant Satu '.$n,
                    'recipients' => ['6012345678'.($n % 10)],
                    'status' => WaCampaign::STATUS_DRAFT,
                ];

            case WaAutoReply::class:
                $peranti = WaDevice::create([
                    'name' => 'Peranti Autoreply '.$n,
                    'phone_number' => '601222222'.($n % 10),
                    'status' => WaDevice::STATUS_CONNECTED,
                ]);

                return [
                    'wa_device_id' => $peranti->id,
                    'name' => 'Autoreply Rahsia Tenant Satu '.$n,
                    'type' => WaAutoReply::TYPE_CONTAINS,
                    'trigger' => 'harga '.$n,
                    'reply' => 'Balasan Rahsia Tenant Satu '.$n,
                ];

            case WaMessage::class:
                $peranti = WaDevice::create([
                    'name' => 'Peranti Mesej '.$n,
                    'phone_number' => '601333333'.($n % 10),
                    'status' => WaDevice::STATUS_CONNECTED,
                ]);

                return [
                    'wa_device_id' => $peranti->id,
                    'direction' => WaMessage::DIRECTION_OUTGOING,
                    'kind' => WaMessage::KIND_MANUAL,
                    'to_number' => '6012345678'.($n % 10),
                    'body' => 'Mesej Rahsia Tenant Satu '.$n,
                    'status' => WaMessage::STATUS_QUEUED,
                ];

            default:
                $peranti = WaDevice::create([
                    'name' => 'Peranti Notifikasi '.$n,
                    'phone_number' => '601444444'.($n % 10),
                    'status' => WaDevice::STATUS_CONNECTED,
                ]);

                return [
                    'wa_device_id' => $peranti->id,
                    'name' => 'Notifikasi Rahsia Tenant Satu '.$n,
                    'trigger' => WaOrderNotification::TRIGGER_THANK_YOU,
                    'message_template' => 'Terima kasih {nama} — pesanan {no} sebanyak {jumlah}',
                ];
        }
    }
}
