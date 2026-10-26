<?php

namespace App\Documents\Templates;

class NotaKredit extends DokumenKewangan
{
    public function key(): string
    {
        return 'nota_kredit';
    }

    public function prefix(): string
    {
        return 'NK';
    }

    public function category(): string
    {
        return 'kewangan';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Nota Kredit', 'Credit Note');
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
        return $this->t($l, 'Nota kredit ini dikeluarkan sebagai pelarasan kepada dokumen rujukan di atas.', 'This credit note is issued as an adjustment to the referenced document above.');
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [['label' => $this->t($l, 'Diluluskan oleh', 'Approved by')]];
    }
}
