<?php

namespace App\Documents\Templates;

use App\Documents\DocumentModel;
use App\Documents\Format;
use App\Documents\Template;

/**
 * Asas dokumen kewangan berbaris (invois, sebut harga, resit, nota kredit, PO, nota penghantaran).
 * Pengiraan jumlah di sini untuk paparan sahaja; nilai rasmi datang dari modul kewangan.
 */
abstract class DokumenKewangan extends Template
{
    public function category(): string
    {
        return 'kewangan';
    }

    public function version(): int
    {
        return 1;
    }

    public function formats(): array
    {
        return ['pdf', 'docx', 'odt', 'xlsx', 'ods', 'csv', 'html', 'json'];
    }

    /** Label penerima: Kepada / Pembekal / Hantar kepada. */
    protected function recipientLabel(string $l): string
    {
        return $this->t($l, 'Kepada', 'Bill to');
    }

    protected function showPrices(): bool
    {
        return true;
    }

    protected function extraMeta(array $d, string $l): array
    {
        return [];
    }

    protected function closing(array $d, string $l): ?string
    {
        return null;
    }

    protected function signatureSlots(array $d, string $l): array
    {
        return [];
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date', 'due_date' => 'nullable|date', 'reference' => 'nullable|string|max:60',
            'party.name' => 'required|string|max:150', 'party.reg_no' => 'nullable|string|max:40', 'party.tin' => 'nullable|string|max:40', 'party.address' => 'nullable|string|max:400', 'party.phone' => 'nullable|string|max:30', 'party.email' => 'nullable|email|max:150',
            'items' => 'required|array|min:1|max:200', 'items.*.description' => 'required|string|max:300', 'items.*.qty' => 'required|numeric|min:0', 'items.*.uom' => 'nullable|string|max:15',
            'items.*.unit_price' => ($this->showPrices() ? 'required' : 'nullable').'|numeric', 'items.*.discount' => 'nullable|numeric|min:0', 'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'tax_label' => 'nullable|string|max:40', 'payment_terms' => 'nullable|string|max:300', 'bank' => 'nullable|string|max:300', 'notes' => 'nullable|string|max:1000',
            'paid_amount' => 'nullable|numeric|min:0', 'payment_method' => 'nullable|string|max:40',
        ];
    }

    public function build(array $d, array $issuer, string $number, string $l): DocumentModel
    {
        $p = $d['party'];
        $lines = array_values(array_filter([
            ! empty($p['reg_no']) ? $this->t($l, 'No. Pendaftaran: ', 'Reg. No.: ').$p['reg_no'] : null,
            ! empty($p['tin']) ? 'TIN: '.$p['tin'] : null,
            $p['address'] ?? null, $p['phone'] ?? null, $p['email'] ?? null,
        ]));
        $meta = array_filter([
            $this->t($l, 'No.', 'No.') => $number,
            $this->t($l, 'Tarikh', 'Date') => Format::date($d['date'], $l),
            $this->t($l, 'Tarikh akhir', 'Due date') => ! empty($d['due_date']) ? Format::date($d['due_date'], $l) : null,
            $this->t($l, 'Rujukan', 'Reference') => $d['reference'] ?? null,
        ]) + $this->extraMeta($d, $l);
        $m = new DocumentModel($this->name($l), $number, $d['date'], $issuer, ['label' => $this->recipientLabel($l), 'name' => $p['name'], 'lines' => $lines], $meta, $this->footer($number, $l));
        $sub = $cukai = 0;
        $rows = [];
        foreach ($d['items'] as $i => $it) {
            $qty = (float) $it['qty'];
            $harga = (float) ($it['unit_price'] ?? 0);
            $amaun = round($qty * $harga - (float) ($it['discount'] ?? 0), 2);
            $tx = round($amaun * (float) ($it['tax_rate'] ?? 0) / 100, 2);
            $sub += $amaun;
            $cukai += $tx;
            $rows[] = $this->showPrices()
                ? [$i + 1, $it['description'], Format::num($qty, $qty == (int) $qty ? 0 : 2).(! empty($it['uom']) ? ' '.$it['uom'] : ''), $harga, $amaun]
                : [$i + 1, $it['description'], Format::num($qty, $qty == (int) $qty ? 0 : 2).(! empty($it['uom']) ? ' '.$it['uom'] : '')];
        }
        $cols = [['label' => '#'], ['label' => $this->t($l, 'Keterangan', 'Description')], ['label' => $this->t($l, 'Kuantiti', 'Qty'), 'align' => 'right']];
        if ($this->showPrices()) {
            $cols[] = ['label' => $this->t($l, 'Harga seunit', 'Unit price'), 'align' => 'right', 'money' => true];
            $cols[] = ['label' => $this->t($l, 'Amaun', 'Amount'), 'align' => 'right', 'money' => true];
        }
        $m->table($cols, $rows);
        if ($this->showPrices()) {
            $jum = round($sub + $cukai, 2);
            $t = [[$this->t($l, 'Jumlah kecil', 'Subtotal'), $sub]];
            if ($cukai > 0) {
                $t[] = [$d['tax_label'] ?? $this->t($l, 'Cukai (SST)', 'Tax (SST)'), $cukai];
            }
            $t[] = [$this->t($l, 'JUMLAH', 'TOTAL'), $jum, true];
            if (isset($d['paid_amount'])) {
                $t[] = [$this->t($l, 'Dibayar', 'Paid'), (float) $d['paid_amount']];
                $t[] = [$this->t($l, 'Baki', 'Balance'), $jum - (float) $d['paid_amount'], true];
            }
            $m->totals($t);
            $m->paragraph(Format::words($jum, $l));
        }
        if ($c = $this->closing($d, $l)) {
            $m->paragraph($c);
        }
        if (! empty($d['payment_terms'])) {
            $m->kv([$this->t($l, 'Terma bayaran', 'Payment terms') => $d['payment_terms']]);
        }
        if (! empty($d['bank'])) {
            $m->kv([$this->t($l, 'Butiran bank', 'Bank details') => $d['bank']]);
        }
        if (! empty($d['notes'])) {
            $m->note($d['notes']);
        }
        if ($s = $this->signatureSlots($d, $l)) {
            $m->signatures($s);
        }

        return $m;
    }
}
