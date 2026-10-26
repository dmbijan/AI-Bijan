<?php

namespace App\Documents\Templates;

use App\Documents\Format;

class SuratTawaran extends Surat
{
    public function key(): string
    {
        return 'surat_tawaran';
    }

    public function prefix(): string
    {
        return 'ST';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Tawaran Pekerjaan', 'Letter of Employment Offer');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Tawaran pekerjaan sebagai ', 'Offer of employment as ').$d['offer']['position'];
    }

    protected function extraRules(): array
    {
        return ['offer.position' => 'required|string|max:80', 'offer.department' => 'nullable|string|max:80', 'offer.start_date' => 'required|date', 'offer.basic_salary' => 'required|numeric|min:0',
            'offer.allowances' => 'nullable|array', 'offer.allowances.*.label' => 'required|string|max:60', 'offer.allowances.*.amount' => 'required|numeric|min:0',
            'offer.probation_months' => 'nullable|integer|min:0|max:12', 'offer.work_hours' => 'nullable|string|max:200', 'offer.work_location' => 'nullable|string|max:200',
            'offer.annual_leave_days' => 'nullable|integer|min:0|max:60', 'offer.sick_leave_days' => 'nullable|integer|min:0|max:60', 'offer.notice_period' => 'nullable|string|max:100',
            'offer.reply_by' => 'nullable|date', 'offer.other_terms' => 'nullable|array', 'offer.other_terms.*' => 'string|max:500'];
    }

    protected function body(array $d, string $l): array
    {
        $o = $d['offer'];
        $elaun = collect($o['allowances'] ?? [])->map(fn ($a) => $a['label'].' '.Format::rm($a['amount']))->implode(', ');

        return [
            $this->t($l, 'Dengan sukacitanya dimaklumkan bahawa pihak syarikat menawarkan anda jawatan ', 'We are pleased to offer you the position of ').$o['position'].(! empty($o['department']) ? $this->t($l, ' di ', ' in ').$o['department'] : '').$this->t($l, ' tertakluk kepada terma dan syarat berikut.', ', subject to the following terms and conditions.'),
            $this->t($l, 'Tarikh mula bertugas: ', 'Commencement date: ').Format::date($o['start_date'], $l).'.',
            $this->t($l, 'Gaji pokok: ', 'Basic salary: ').Format::rm($o['basic_salary']).$this->t($l, ' sebulan', ' per month').($elaun ? $this->t($l, '; elaun: ', '; allowances: ').$elaun : '').$this->t($l, '. Caruman KWSP, PERKESO dan SIP serta potongan cukai bulanan (PCB) akan dibuat mengikut undang-undang yang berkuat kuasa.', '. EPF, SOCSO and EIS contributions and monthly tax deductions (PCB) will be made in accordance with prevailing law.'),
            ! empty($o['probation_months']) ? $this->t($l, 'Anda akan berada dalam tempoh percubaan selama ', 'You will be on probation for ').$o['probation_months'].$this->t($l, ' bulan. Pengesahan jawatan tertakluk kepada prestasi dan penilaian.', ' months. Confirmation is subject to performance and evaluation.') : null,
            ! empty($o['work_hours']) ? $this->t($l, 'Waktu bekerja: ', 'Working hours: ').$o['work_hours'].'.' : null,
            ! empty($o['work_location']) ? $this->t($l, 'Tempat bertugas: ', 'Place of work: ').$o['work_location'].'.' : null,
            (isset($o['annual_leave_days']) || isset($o['sick_leave_days'])) ? $this->t($l, 'Kelayakan cuti: ', 'Leave entitlement: ').implode(', ', array_filter([isset($o['annual_leave_days']) ? $this->t($l, 'cuti tahunan ', 'annual leave ').$o['annual_leave_days'].$this->t($l, ' hari', ' days') : null, isset($o['sick_leave_days']) ? $this->t($l, 'cuti sakit ', 'sick leave ').$o['sick_leave_days'].$this->t($l, ' hari', ' days') : null])).$this->t($l, ' setahun, tidak kurang daripada kelayakan minimum di bawah Akta Kerja 1955 di mana terpakai.', ' per year, not less than the statutory minimum under the Employment Act 1955 where applicable.') : null,
            ! empty($o['notice_period']) ? $this->t($l, 'Tempoh notis penamatan: ', 'Notice period: ').$o['notice_period'].'.' : null,
            ...($o['other_terms'] ?? []),
            $this->t($l, 'Maklumat peribadi anda akan diproses untuk tujuan pekerjaan selaras dengan Akta Perlindungan Data Peribadi 2010.', 'Your personal data will be processed for employment purposes in accordance with the Personal Data Protection Act 2010.'),
            ! empty($o['reply_by']) ? $this->t($l, 'Sila tandatangani akuan penerimaan di bawah dan kembalikan salinan surat ini selewat-lewatnya pada ', 'Please sign the acknowledgement below and return a copy of this letter by ').Format::date($o['reply_by'], $l).'.' : null,
        ];
    }

    protected function acknowledgement(array $d, string $l): ?string
    {
        return $this->t($l, 'Saya, ', 'I, ').$d['recipient']['name'].$this->t($l, ', telah membaca, memahami dan menerima tawaran pekerjaan ini berserta terma dan syaratnya.', ', have read, understood and accept this offer of employment and its terms and conditions.');
    }
}
