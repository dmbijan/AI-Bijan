<?php

namespace App\Modules\Blast\Services\Providers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * EmailProvider — hantar e-mel melalui Laravel Mail (SMTP) atau Mailgun API.
 *
 * Auto-pilih:
 * - Jika MAIL_MAILER=log atau tiada SMTP → mod simulasi (tidak hantar).
 * - Jika provider = mailgun → guna Mailgun Messages API.
 */
class EmailProvider implements ChannelProviderInterface
{
    public function nama(): string
    {
        return config('blast.providers.email.default', 'smtp');
    }

    public function tersedia(): bool
    {
        return (bool) config('blast.providers.email.enabled');
    }

    /**
     * Hantar e-mel.
     *
     * @param array $options subject, from, from_name, reply_to, unsubscribe_url, html
     */
    public function hantar(string $recipient, string $body, array $options = []): array
    {
        $subject = $options['subject'] ?? '(tanpa subjek)';
        $from = $options['from'] ?? config('blast.from_address');
        $fromName = $options['from_name'] ?? config('blast.from_name');
        $replyTo = $options['reply_to'] ?? config('blast.reply_to');

        // Mod simulasi — untuk dev/test
        if (config('blast.providers.email.mode') === 'log') {
            Log::info('Blast: e-mel (mod log)', [
                'to' => $recipient,
                'subject' => $subject,
            ]);

            return [
                'ok' => true,
                'message_id' => 'log-'.uniqid(),
                'error' => null,
                'raw' => ['mode' => 'log'],
            ];
        }

        try {
            if ($this->nama() === 'mailgun' && config('blast.providers.email.mailgun.key')) {
                return $this->hantarMailgun($recipient, $subject, $body, $from, $fromName, $replyTo, $options);
            }

            // SMTP melalui Laravel Mail
            $headers = [];

            if (! empty($options['unsubscribe_url'])) {
                // RFC 8058 — List-Unsubscribe
                $headers['List-Unsubscribe'] = '<'.$options['unsubscribe_url'].'>';
                $headers['List-Unsubscribe-Post'] = 'List-Unsubscribe=One-Click';
            }

            Mail::html($body, function ($message) use ($recipient, $subject, $from, $fromName, $replyTo, $headers) {
                $message->to($recipient)->subject($subject);

                if ($from) {
                    $message->from($from, $fromName);
                }

                if ($replyTo) {
                    $message->replyTo($replyTo);
                }

                foreach ($headers as $key => $value) {
                    $message->header($key, $value);
                }
            });

            return [
                'ok' => true,
                'message_id' => 'smtp-'.uniqid(),
                'error' => null,
                'raw' => ['provider' => 'smtp'],
            ];
        } catch (\Throwable $e) {
            Log::error('Blast: hantar e-mel gagal', [
                'to' => $recipient,
                'error' => $e->getMessage(),
            ]);

            return [
                'ok' => false,
                'message_id' => null,
                'error' => $e->getMessage(),
                'raw' => [],
            ];
        }
    }

    /**
     * Hantar melalui Mailgun Messages API.
     */
    private function hantarMailgun(
        string $recipient,
        string $subject,
        string $body,
        ?string $from,
        ?string $fromName,
        ?string $replyTo,
        array $options
    ): array {
        $domain = config('blast.providers.email.mailgun.domain');
        $key = config('blast.providers.email.mailgun.key');

        try {
            $response = Http::withBasicAuth('api', $key)
                ->asForm()
                ->post("https://api.mailgun.net/v3/{$domain}/messages", [
                    'from' => trim(($fromName ?: 'AI BIJAN').' <'.($from ?: 'no-reply@'.$domain).'>'),
                    'to' => $recipient,
                    'subject' => $subject,
                    'html' => $body,
                    ...($replyTo ? ['h:Reply-To' => $replyTo] : []),
                    ...(! empty($options['unsubscribe_url']) ? [
                        'h:List-Unsubscribe' => '<'.$options['unsubscribe_url'].'>',
                        'h:List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                    ] : []),
                ]);

            if (! $response->successful()) {
                return [
                    'ok' => false,
                    'message_id' => null,
                    'error' => 'Mailgun '.$response->status().': '.$response->body(),
                    'raw' => $response->json() ?? [],
                ];
            }

            $data = $response->json();

            return [
                'ok' => true,
                'message_id' => $data['id'] ?? null,
                'error' => null,
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message_id' => null, 'error' => $e->getMessage(), 'raw' => []];
        }
    }
}
