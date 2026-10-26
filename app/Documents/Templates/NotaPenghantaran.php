<?php

namespace App\Documents\Templates;

class NotaPenghantaran extends DokumenKewangan
{
    public function key(): string
    {
        return 'nota_penghantaran';
    }

    public function prefix(): string
    {
        return 'DO';
    }

    public function category(): string
    {
        return 'operasi';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Nota Penghantaran', 'Delivery Order');
    }

    protected function recipientLabel(string $l): string
    {
        return $this->t($l, 'Hantar kepada', 'Deliver to');
    }

    protected function showPrices(): bool
    {
        return false;
    }

    protected function closing(array $d, string $l): ?string
    {
        return $this->t($l, 'Barang di atas diterima dalam keadaan baik dan lengkap.', 'Goods above received in good order and condition.');
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [['label' => $this->t($l, 'Dihantar oleh', 'Delivered by')], ['label' => $this->t($l, 'Diterima oleh', 'Received by')]];
    }
}
