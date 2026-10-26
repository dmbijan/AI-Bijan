<?php

namespace App\Documents\Templates;

class Invois extends DokumenKewangan
{
    public function key(): string
    {
        return 'invois';
    }

    public function prefix(): string
    {
        return 'INV';
    }

    public function category(): string
    {
        return 'kewangan';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Invois', 'Invoice');
    }

    protected function recipientLabel(string $l): string
    {
        return $this->t($l, 'Kepada', 'Bill to');
    }

    protected function showPrices(): bool
    {
        return true;
    }

    protected function closing(array $d, string $l): ?string
    {
        return null;
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [['label' => $this->t($l, 'Disediakan oleh', 'Prepared by')]];
    }
}
