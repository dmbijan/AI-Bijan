<?php

namespace App\Modules\Blast\Services\Providers;

/**
 * ChannelProviderInterface — kontrak untuk setiap saluran penghantaran.
 *
 * Pattern adapter: pertukaran provider (Mailgun → SES, Twilio → MTwilio)
 * tidak memerlukan penulisan semula logik blast.
 */
interface ChannelProviderInterface
{
    /**
     * @return array{ok: bool, message_id: ?string, error: ?string, raw: array}
     */
    public function hantar(string $recipient, string $body, array $options = []): array;

    /**
     * Adakah provider dikonfigurasi untuk tenant ini?
     */
    public function tersedia(): bool;

    /**
     * Nama provider (untuk log & status).
     */
    public function nama(): string;
}
