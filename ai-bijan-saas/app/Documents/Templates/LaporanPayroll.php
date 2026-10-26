<?php

namespace App\Documents\Templates;

use App\Documents\DocumentModel;
use App\Documents\Format;
use App\Documents\Template;

/** Ringkasan payroll bulanan (berizin sahaja). XLSX/CSV untuk analisis; PDF untuk arkib. */
class LaporanPayroll extends Template
{
    public function key(): string
    {
        return 'laporan_payroll';
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
        return 'LPR';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Ringkasan Payroll', 'Payroll Summary');
    }

    public function sensitive(): bool
    {
        return true;
    }

    public function formats(): array
    {
        return ['xlsx', 'ods', 'csv', 'pdf', 'html', 'json'];
    }

    public function rules(): array
    {
        return ['period' => 'required|date_format:Y-m', 'rows' => 'required|array|min:1|max:5000', 'rows.*.staff_id' => 'required|string|max:30', 'rows.*.name' => 'required|string|max:120',
            'rows.*.gross' => 'required|numeric', 'rows.*.epf_ee' => 'numeric', 'rows.*.socso_ee' => 'numeric', 'rows.*.eis_ee' => 'numeric', 'rows.*.pcb' => 'numeric', 'rows.*.other_ded' => 'numeric',
            'rows.*.epf_er' => 'numeric', 'rows.*.socso_er' => 'numeric', 'rows.*.eis_er' => 'numeric'];
    }

    public function filename(array $d, string $number): string
    {
        return 'Ringkasan-Payroll_'.$d['period'];
    }

    public function build(array $d, array $issuer, string $number, string $l): DocumentModel
    {
        $m = new DocumentModel($this->name($l), $number, $d['period'].'-01', $issuer, null, [$this->t($l, 'Tempoh', 'Period') => Format::month($d['period'], $l), $this->t($l, 'Bil. pekerja', 'Employees') => count($d['rows'])], $this->footer($number, $l), true);
        $f = ['gross', 'epf_ee', 'socso_ee', 'eis_ee', 'pcb', 'other_ded', 'net', 'epf_er', 'socso_er', 'eis_er'];
        $jum = array_fill_keys($f, 0);
        $rows = [];
        foreach ($d['rows'] as $r) {
            $r['net'] = (float) $r['gross'] - array_sum(array_map(fn ($k) => (float) ($r[$k] ?? 0), ['epf_ee', 'socso_ee', 'eis_ee', 'pcb', 'other_ded']));
            foreach ($f as $k) {
                $jum[$k] += (float) ($r[$k] ?? 0);
            }
            $rows[] = array_merge([$r['staff_id'], $r['name']], array_map(fn ($k) => round((float) ($r[$k] ?? 0), 2), $f));
        }
        $rows[] = array_merge(['', $this->t($l, 'JUMLAH', 'TOTAL')], array_map(fn ($k) => round($jum[$k], 2), $f));
        $lab = $l === 'en' ? ['Gross', 'EPF (EE)', 'SOCSO (EE)', 'EIS (EE)', 'PCB', 'Other ded.', 'Net', 'EPF (ER)', 'SOCSO (ER)', 'EIS (ER)'] : ['Kasar', 'KWSP (P)', 'PERKESO (P)', 'SIP (P)', 'PCB', 'Potongan lain', 'Bersih', 'KWSP (M)', 'PERKESO (M)', 'SIP (M)'];
        $m->table(array_merge([['label' => 'ID'], ['label' => $this->t($l, 'Nama', 'Name')]], array_map(fn ($x) => ['label' => $x, 'align' => 'right', 'money' => true], $lab)), $rows);
        $m->note($this->t($l, '(P) = pekerja, (M) = majikan. Angka dari modul payroll; sila sahkan dengan jadual rasmi KWSP/PERKESO/LHDN.', '(EE) = employee, (ER) = employer. Figures from payroll module; verify against official EPF/SOCSO/LHDN schedules.'));

        return $m;
    }
}
