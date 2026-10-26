<?php

namespace App\Documents\Templates;

use App\Documents\Format;

class SuratPengesahanPerkhidmatan extends Surat
{
    public function key(): string
    {
        return 'surat_pengesahan_perkhidmatan';
    }

    public function prefix(): string
    {
        return 'SPP';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Pengesahan Perkhidmatan', 'Letter of Service Confirmation');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Pengesahan perkhidmatan ', 'Confirmation of service ').$d['position'] ?? '';
    }

    protected function extraRules(): array
    {
        return ['position' => 'required|string|max:80', 'start_date' => 'required|date', 'end_date' => 'nullable|date', 'salary' => 'nullable|numeric|min:0', 'purpose' => 'nullable|string|max:200'];
    }

    protected function body(array $d, string $l): array
    {
        return $l === 'en' ? [
            'This is to confirm that the above-named '.(empty($d['end_date']) ? 'is employed' : 'was employed').' with the company as '.$d['position'].' from '.Format::date($d['start_date'], $l).(! empty($d['end_date']) ? ' to '.Format::date($d['end_date'], $l) : '').'.',
            ! empty($d['salary']) ? 'Current basic salary: '.Format::rm($d['salary']).' per month.' : null,
            ! empty($d['purpose']) ? 'This letter is issued at the request of the named person for the purpose of '.$d['purpose'].'.' : 'This letter is issued at the request of the named person.',
        ] : [
            'Dengan ini disahkan bahawa penama di atas '.(empty($d['end_date']) ? 'sedang berkhidmat' : 'telah berkhidmat').' dengan syarikat ini sebagai '.$d['position'].' mulai '.Format::date($d['start_date'], $l).(! empty($d['end_date']) ? ' hingga '.Format::date($d['end_date'], $l) : '').'.',
            ! empty($d['salary']) ? 'Gaji pokok semasa: '.Format::rm($d['salary']).' sebulan.' : null,
            ! empty($d['purpose']) ? 'Surat ini dikeluarkan atas permintaan penama untuk tujuan '.$d['purpose'].'.' : 'Surat ini dikeluarkan atas permintaan penama.',
        ];
    }
}
