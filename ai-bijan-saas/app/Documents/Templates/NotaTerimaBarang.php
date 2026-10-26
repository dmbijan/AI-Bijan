<?php

namespace App\Documents\Templates;

class NotaTerimaBarang extends DokumenKewangan
{
    public function key(): string
    {
        return 'nota_terima_barang';
    }

    public function prefix(): string
    {
        return 'GRN';
    }

    public function category(): string
    {
        return 'operasi';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Nota Penerimaan Barang', 'Goods Received Note');
    }

    protected function recipientLabel(string $l): string
    {
        return $this->t($l, 'Pembekal', 'Supplier');
    }

    protected function showPrices(): bool
    {
        return false;
    }

    protected function closing(array $d, string $l): ?string
    {
        return null;
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [['label' => $this->t($l, 'Diterima oleh', 'Received by')], ['label' => $this->t($l, 'Disemak oleh', 'Checked by')]];
    }
}
