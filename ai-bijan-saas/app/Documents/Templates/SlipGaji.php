<?php

namespace App\Documents\Templates;

use App\Documents\DocumentModel;
use App\Documents\Format;
use App\Documents\Template;

/**
 * Slip gaji. Medan ikut V3.0 §11.5. Angka caruman (KWSP/PERKESO/SIP/PCB) datang dari modul payroll
 * (dikira ikut jadual rasmi berversi) — templat ini TIDAK mengira kadar statutori.
 */
class SlipGaji extends Template
{
    public function key(): string
    {
        return 'slip_gaji';
    }

    public function version(): int
    {
        return 1;
    }

    public function category(): string
    {
        return 'hr';
    }

    public function prefix(): string
    {
        return 'SG';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Slip Gaji', 'Payslip');
    }

    public function sensitive(): bool
    {
        return true;
    }

    public function formats(): array
    {
        return ['pdf', 'docx', 'html', 'json'];
    }

    public function rules(): array
    {
        return [
            'employee.name' => 'required|string|max:120', 'employee.staff_id' => 'required|string|max:30', 'employee.ic_no' => 'nullable|string|max:20',
            'employee.position' => 'nullable|string|max:80', 'employee.department' => 'nullable|string|max:80', 'employee.epf_no' => 'nullable|string|max:20', 'employee.socso_no' => 'nullable|string|max:20', 'employee.tax_no' => 'nullable|string|max:20',
            'period' => 'required|date_format:Y-m', 'pay_date' => 'required|date',
            'earnings' => 'required|array|min:1', 'earnings.*.label' => 'required|string|max:60', 'earnings.*.amount' => 'required|numeric',
            'deductions' => 'array', 'deductions.*.label' => 'required|string|max:60', 'deductions.*.amount' => 'required|numeric|min:0',
            'employer' => 'array', 'employer.*.label' => 'required|string|max:60', 'employer.*.amount' => 'required|numeric|min:0',
            'bank.name' => 'nullable|string|max:60', 'bank.account' => 'nullable|string|max:30', 'payment_ref' => 'nullable|string|max:60', 'notes' => 'nullable|string|max:500',
        ];
    }

    public function filename(array $d, string $number): string
    {
        return 'Slip-Gaji_'.$d['period'].'_Pekerja-'.preg_replace('/[^A-Za-z0-9-]/', '', $d['employee']['staff_id']);
    }

    public function build(array $d, array $issuer, string $number, string $l): DocumentModel
    {
        $e = $d['employee'];
        $kasar = array_sum(array_column($d['earnings'], 'amount'));
        $potong = array_sum(array_column($d['deductions'] ?? [], 'amount'));
        $m = new DocumentModel($this->name($l), $number, $d['pay_date'], $issuer, null, [
            $this->t($l, 'No. Rujukan', 'Reference') => $number,
            $this->t($l, 'Tempoh gaji', 'Pay period') => Format::month($d['period'], $l),
            $this->t($l, 'Tarikh bayaran', 'Payment date') => Format::date($d['pay_date'], $l),
        ], $this->footer($number, $l), true);
        $m->kv(array_filter([
            $this->t($l, 'Nama', 'Name') => $e['name'],
            $this->t($l, 'ID Pekerja', 'Employee ID') => $e['staff_id'],
            $this->t($l, 'No. Kad Pengenalan', 'IC No.') => ! empty($e['ic_no']) ? Format::mask($e['ic_no']) : null,
            $this->t($l, 'Jawatan', 'Position') => $e['position'] ?? null,
            $this->t($l, 'Jabatan', 'Department') => $e['department'] ?? null,
            $this->t($l, 'No. KWSP', 'EPF No.') => $e['epf_no'] ?? null,
            $this->t($l, 'No. PERKESO', 'SOCSO No.') => $e['socso_no'] ?? null,
            $this->t($l, 'No. Cukai Pendapatan', 'Income Tax No.') => $e['tax_no'] ?? null,
        ]), $this->t($l, 'Maklumat pekerja', 'Employee details'), 2);
        // Pendapatan & potongan bersebelahan (format slip gaji lazim, muat satu muka).
        $ded = $d['deductions'] ?? [];
        $rows = [];
        for ($i = 0; $i < max(count($d['earnings']), count($ded)); $i++) {
            $e1 = $d['earnings'][$i] ?? null;
            $d1 = $ded[$i] ?? null;
            $rows[] = [$e1['label'] ?? '', $e1 ? (float) $e1['amount'] : '', $d1['label'] ?? '', $d1 ? (float) $d1['amount'] : ''];
        }
        $m->table([
            ['label' => $this->t($l, 'Pendapatan', 'Earnings')], ['label' => $this->t($l, 'Amaun', 'Amount'), 'align' => 'right', 'money' => true],
            ['label' => $this->t($l, 'Potongan', 'Deductions')], ['label' => $this->t($l, 'Amaun', 'Amount'), 'align' => 'right', 'money' => true],
        ], $rows);
        $m->totals([
            [$this->t($l, 'Jumlah pendapatan kasar', 'Gross earnings'), $kasar],
            [$this->t($l, 'Jumlah potongan', 'Total deductions'), $potong],
            [$this->t($l, 'GAJI BERSIH', 'NET PAY'), $kasar - $potong, true],
        ]);
        $m->paragraph(Format::words($kasar - $potong, $l));
        if (! empty($d['employer'])) {
            $m->kv(collect($d['employer'])->mapWithKeys(fn ($x) => [$x['label'] => Format::rm($x['amount'])])->all(), $this->t($l, 'Caruman majikan (tidak ditolak dari gaji)', 'Employer contributions (not deducted)'), 3);
        }
        if (! empty($d['bank']['name']) || ! empty($d['payment_ref'])) {
            $m->kv(array_filter([
                $this->t($l, 'Bank', 'Bank') => $d['bank']['name'] ?? null,
                $this->t($l, 'No. akaun', 'Account no.') => ! empty($d['bank']['account']) ? Format::mask($d['bank']['account']) : null,
                $this->t($l, 'Rujukan bayaran', 'Payment reference') => $d['payment_ref'] ?? null,
            ]), $this->t($l, 'Bayaran', 'Payment'), 3);
        }
        if (! empty($d['notes'])) {
            $m->note($d['notes']);
        }
        $m->note($this->t($l, 'Slip ini sulit dan untuk penerima yang dinamakan sahaja. Sila maklumkan bahagian HR jika terdapat sebarang percanggahan.', 'This payslip is confidential and intended only for the named recipient. Please notify HR of any discrepancy.'));

        return $m;
    }
}
