<?php

namespace App\Documents\Templates;

use App\Documents\Format;

class SuratAmaran extends Surat
{
    public function key(): string
    {
        return 'surat_amaran';
    }

    public function prefix(): string
    {
        return 'SA';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Amaran', 'Warning Letter');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Amaran berhubung ', 'Warning regarding ').$d['matter'] ?? '';
    }

    protected function extraRules(): array
    {
        return ['matter' => 'required|string|max:150', 'incident_date' => 'nullable|date', 'details' => 'required|string|max:2000', 'expected' => 'required|string|max:1000', 'level' => 'nullable|in:pertama,kedua,terakhir'];
    }

    protected function body(array $d, string $l): array
    {
        return $l === 'en' ? [
            'Management refers to the matter of '.$d['matter'].(! empty($d['incident_date']) ? ' on '.Format::date($d['incident_date'], $l) : '').'.',
            'Details: '.$d['details'].'',
            'You are required to: '.$d['expected'].'',
            'This letter serves as a '.($d['level'] ?? 'first').' warning and will be placed in your personal file. Failure to improve may result in further disciplinary action in accordance with company policy and applicable employment law.',
            'You may submit a written explanation within 7 days of the date of this letter.',
        ] : [
            'Pihak pengurusan merujuk kepada perkara '.$d['matter'].(! empty($d['incident_date']) ? ' pada '.Format::date($d['incident_date'], $l) : '').'.',
            'Butiran: '.$d['details'].'',
            'Anda dikehendaki: '.$d['expected'].'',
            'Surat ini merupakan amaran '.($d['level'] ?? 'pertama').' dan akan disimpan dalam fail peribadi anda. Kegagalan memperbaiki tingkah laku/prestasi boleh menyebabkan tindakan tatatertib selanjutnya selaras dengan polisi syarikat dan undang-undang pekerjaan yang terpakai.',
            'Anda berhak mengemukakan penjelasan secara bertulis dalam tempoh 7 hari dari tarikh surat ini.',
        ];
    }

    protected function acknowledgement(array $d, string $l): ?string
    {
        return $this->t($l, 'Saya, '.$d['recipient']['name'].', mengaku telah menerima surat ini.', 'I, '.$d['recipient']['name'].', acknowledge receipt of this letter.');
    }
}
