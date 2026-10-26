<?php

namespace App\Documents\Templates;

class Resit extends DokumenKewangan
{
    public function key(): string
    {
        return 'resit';
    }

    public function prefix(): string
    {
        return 'RS';
    }

    public function category(): string
    {
        return 'kewangan';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Resit Rasmi', 'Official Receipt');
    }

    protected function recipientLabel(string $l): string
    {
        return $this->t($l, 'Diterima daripada', 'Received from');
    }

    protected function showPrices(): bool
    {
        return true;
    }

    protected function closing(array $d, string $l): ?string
    {
        return $this->t($l, 'Terima kasih atas bayaran anda.', 'Thank you for your payment.');
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [];
    }
}
