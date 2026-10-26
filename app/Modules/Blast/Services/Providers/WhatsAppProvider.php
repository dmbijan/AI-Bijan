<?php

namespace App\Modules\Blast\Services\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppProvider — Meta WhatsApp Business Cloud API.
 *
 * ⚠️ NOTA PEMATUHAN: WhatsApp Business API memerlukan:
 * 1. Template yang diluluskan Meta (untuk mesej di luar 24 jam Window).
 * 2. Opt-in pengguna yang jelas.
 * 3. Rate limit mengikut conversation tier.
 */
class WhatsAppProvider implements ChannelProviderInterface
{
    public function nama(): string
    {
        return 'meta_waba';
    }

    public function tersedia(): bool
    {
        return (bool) config('blast.providers.whatsapp.access_token');
    }

    /**
     * Hantar mesej WhatsApp.
     *
     * @param array $options template, parameters, phone_number_id, media_url
     */
    public function hantar(string $recipient, string $body, array $options = []): array
    {
        $token = config('blast.providers.whatsapp.access_token');
        $phoneNumberId = $options['phone_number_id'] ?? config('blast.providers.whatsapp.phone_number_id');

        if (! $token || ! $phoneNumberId) {
            return [
                'ok' => false,
                'message_id' => null,
                'error' => 'WhatsApp provider tidak dikonfigurasi',
                'raw' => [],
            ];
        }

        // Normalkan nombor ke format antarabangsa (tanpa +)
        $to = preg_replace('/\D+/', '', $recipient);
        if (str_starts_with($to, '60')) {
            $to = substr($to, 2);
        }

        $templateName = $options['template'] ?? null;

        try {
            if ($templateName) {
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'template',
                    'template' => [
                        'name' => $templateName,
                        'language' => ['code' => 'ms'],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => array_map(fn ($p) => ['type' => 'text', 'text' => (string) $p], $options['parameters'] ?? [$body]),
                        ]],
                    ],
                ];
            } else {
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'to' => $to,
                    'type' => 'text',
                    'text' => ['preview_url' => false, 'body' => $body],
                ];
            }

            $response = Http::withToken($token)
                ->post("https://graph.facebook.com/v21.0/{$phoneNumberId}/messages", $payload);

            if (! $response->successful()) {
                $error = 'WhatsApp '.$response->status().': '.$response->body();

                Log::error('Blast: hantar WhatsApp gagal', ['to' => $recipient, 'error' => $error]);

                return ['ok' => false, 'message_id' => null, 'error' => $error, 'raw' => $response->json() ?? []];
            }

            $data = $response->json();

            return [
                'ok' => true,
                'message_id' => $data['messages'][0]['id'] ?? null,
                'error' => null,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            Log::error('Blast: ralat WhatsApp', ['to' => $recipient, 'error' => $e->getMessage()]);

            return ['ok' => false, 'message_id' => null, 'error' => $e->getMessage(), 'raw' => []];
        }
    }
}
