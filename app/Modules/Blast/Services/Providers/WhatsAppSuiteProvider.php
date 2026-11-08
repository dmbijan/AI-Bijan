<?php

namespace App\Modules\Blast\Services\Providers;

use App\Modules\WhatsAppSuite\Models\WaDevice;
use App\Modules\WhatsAppSuite\Models\WaMessage;
use App\Modules\WhatsAppSuite\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppSuiteProvider — hantar blast melalui peranti WhatsAppSuite
 * (wasapbot.my / whatsapp-web.js).
 *
 * Berbeda daripada `WhatsAppProvider` (Meta WhatsApp Business Cloud API):
 *   - guna nombor/device milik tenant sendiri (tanpa Meta approval)
 *   - ada kawalan anti-ban (delay rawak + jeda berkala)
 *   - sokong label, kempen dan auto-responder
 *
 * Diaktifkan dengan konfigurasi `blast.providers.whatsapp_suite.enabled`.
 */
class WhatsAppSuiteProvider implements ChannelProviderInterface
{
    public function __construct(private WhatsAppService $wa) {}

    public function nama(): string
    {
        return 'whatsapp_suite';
    }

    public function tersedia(): bool
    {
        if (! config('blast.providers.whatsapp_suite.enabled', false)) {
            return false;
        }

        // Perlu ada sekurang-kurangnya satu peranti bersambung.
        return WaDevice::query()->where('status', WaDevice::STATUS_CONNECTED)->exists();
    }

    /**
     * Hantar mesej WhatsApp melalui peranti tenant.
     *
     * @param  array<string, mixed>  $options  device_id, media_url
     */
    public function hantar(string $recipient, string $body, array $options = []): array
    {
        $peranti = $this->pilihPeranti($options['device_id'] ?? null);

        if (! $peranti) {
            return [
                'ok' => false,
                'message_id' => null,
                'error' => 'Tiada peranti WhatsAppSuite bersambung.',
                'raw' => [],
            ];
        }

        $hasil = $this->wa->hantar($peranti, $recipient, $body, [
            'media_url' => $options['media_url'] ?? null,
            'kind' => WaMessage::KIND_BROADCAST,
        ]);

        return [
            'ok' => $hasil['success'],
            'message_id' => $hasil['message_id'] ? (string) $hasil['message_id'] : null,
            'error' => $hasil['success'] ? null : $hasil['mesej'],
            'raw' => [
                'device_id' => $peranti->id,
                'device' => $peranti->name,
            ],
        ];
    }

    /**
     * Pilih peranti — tertentu atau yang pertama bersambung.
     */
    protected function pilihPeranti(?int $deviceId): ?WaDevice
    {
        $query = WaDevice::query()->where('status', WaDevice::STATUS_CONNECTED);

        if ($deviceId !== null) {
            $query->whereKey($deviceId);
        }

        $peranti = $query->first();

        if (! $peranti) {
            Log::info('Blast: WhatsAppSuite tiada peranti sesuai', ['device_id' => $deviceId]);
        }

        return $peranti;
    }
}
