<?php

namespace App\Documents\Templates;

class PesananBelian extends DokumenKewangan
{
    public function key(): string
    {
        return 'pesanan_belian';
    }

    public function prefix(): string
    {
        return 'PO';
    }

    public function category(): string
    {
        return 'operasi';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Pesanan Belian', 'Purchase Order');
    }

    protected function recipientLabel(string $l): string
    {
        return $this->t($l, 'Pembekal', 'Supplier');
    }

    protected function showPrices(): bool
    {
        return true;
    }

    protected function closing(array $d, string $l): ?string
    {
        return $this->t($l, 'Sila nyatakan nombor PO ini pada invois dan nota penghantaran anda.', 'Please quote this PO number on your invoice and delivery order.');
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [['label' => $this->t($l, 'Disediakan oleh', 'Prepared by')], ['label' => $this->t($l, 'Diluluskan oleh', 'Approved by')]];
    }
}
