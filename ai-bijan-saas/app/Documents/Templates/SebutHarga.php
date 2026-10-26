<?php

namespace App\Documents\Templates;

class SebutHarga extends DokumenKewangan
{
    public function key(): string
    {
        return 'sebut_harga';
    }

    public function prefix(): string
    {
        return 'SH';
    }

    public function category(): string
    {
        return 'kewangan';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Sebut Harga', 'Quotation');
    }

    protected function recipientLabel(string $l): string
    {
        return $this->t($l, 'Kepada', 'To');
    }

    protected function showPrices(): bool
    {
        return true;
    }

    protected function closing(array $d, string $l): ?string
    {
        return $this->t($l, 'Sebut harga ini sah selama 14 hari dari tarikh di atas melainkan dinyatakan sebaliknya. Harga tertakluk kepada terma yang dinyatakan.', 'This quotation is valid for 14 days from the date above unless stated otherwise. Prices are subject to the stated terms.');
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [['label' => $this->t($l, 'Disediakan oleh', 'Prepared by')], ['label' => $this->t($l, 'Diterima oleh (pelanggan)', 'Accepted by (customer)')]];
    }
}
