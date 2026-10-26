<?php

namespace App\Documents\Templates;

use App\Documents\Format;

class SuratTerimaPeletakanJawatan extends Surat
{
    public function key(): string
    {
        return 'surat_terima_peletakan_jawatan';
    }

    public function prefix(): string
    {
        return 'SPLJ';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Penerimaan Peletakan Jawatan', 'Resignation Acceptance Letter');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Penerimaan notis peletakan jawatan', 'Acceptance of resignation').'';
    }

    protected function extraRules(): array
    {
        return ['notice_date' => 'required|date', 'last_day' => 'required|date', 'handover' => 'nullable|string|max:500'];
    }

    protected function body(array $d, string $l): array
    {
        return $l === 'en' ? [
            'The company refers to your resignation notice dated '.Format::date($d['notice_date'], $l).' and hereby accepts it.',
            'Your last working day is '.Format::date($d['last_day'], $l).'.',
            ! empty($d['handover']) ? 'You are required to: '.$d['handover'] : 'Please return all company property and complete your handover before your last day.',
            'Final payment will be settled according to your terms of service and applicable law. Thank you for your contributions.',
        ] : [
            'Pihak syarikat merujuk kepada notis peletakan jawatan anda bertarikh '.Format::date($d['notice_date'], $l).' dan dengan ini menerimanya.',
            'Hari terakhir anda bertugas ialah '.Format::date($d['last_day'], $l).'.',
            ! empty($d['handover']) ? 'Anda dikehendaki: '.$d['handover'] : 'Anda dikehendaki menyerahkan semua harta syarikat dan menyelesaikan serahan tugas sebelum hari terakhir.',
            'Bayaran akhir akan diselesaikan mengikut terma perkhidmatan dan undang-undang yang terpakai. Syarikat mengucapkan terima kasih atas sumbangan anda.',
        ];
    }
}
