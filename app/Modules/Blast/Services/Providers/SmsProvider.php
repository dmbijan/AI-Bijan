<?php

namespace App\Modules\Blast\Services\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SmsProvider — Twilio (adapter). MTwilio boleh ditambah dengan mudah.
 */
class SmsProvider implements ChannelProviderInterface
{
    public function nama(): string
    {
        return config('blast.providers.sms.default', 'twilio');
    }

    public function tersedia(): bool
    {
        return (bool) config('blast.providers.sms.twilio.sid');
    }

    /**
     * Hantar SMS.
     *
     * ⚠️ Malaysia: mesej SMS yang flirtasi/penjualan AUTOMATIK tertakluk
     * kepada lesen MCMC. Pemohon lesen WASTI diperlukan untuk blast komersial.
     */
    public function hantar(string $recipient, string $body, array $options = []): array
    {
        $sid = config('blast.providers.sms.twilio.sid');
        $token = config('blast.providers.sms.twilio.token');
        $from = $options['from'] ?? config('blast.providers.sms.twilio.from');

        if (! $sid || ! $token) {
            return [
                'ok' => false,
                'message_id' => null,
                'error' => 'SMS provider tidak dikonfigurasi',
                'raw' => [],
            ];
        }

        // Had 160 aksara (atau 7 bit segment)
        if (mb_strlen($body) > 160) {
            $body = mb_substr($body, 0, 157).'...';
        }

        try {
            $response = Http::asForm()
                ->withBasicAuth($sid, $token)
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json", [
                    'To' => $recipient,
                    'From' => $from,
                    'Body' => $body,
                ]);

            if (! $response->successful()) {
                $error = 'Twilio '.$response->status().': '.$response->body();

                Log::error('Blast: hantar SMS gagal', ['to' => $recipient, 'error' => $error]);

                return ['ok' => false, 'message_id' => null, 'error' => $error, 'raw' => $response->json() ?? []];
            }

            $data = $response->json();

            return [
                'ok' => true,
                'message_id' => $data['sid'] ?? null,
                'error' => null,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message_id' => null, 'error' => $e->getMessage(), 'raw' => []];
        }
    }
}
