<?php

namespace App\Documents\Templates;

use App\Documents\Format;

class SuratPengesahanJawatan extends Surat
{
    public function key(): string
    {
        return 'surat_pengesahan_jawatan';
    }

    public function prefix(): string
    {
        return 'SPJ';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Pengesahan Jawatan', 'Letter of Confirmation');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Pengesahan dalam jawatan ', 'Confirmation of appointment as ').$d['position'] ?? '';
    }

    protected function extraRules(): array
    {
        return ['position' => 'required|string|max:80', 'effective_date' => 'required|date', 'new_salary' => 'nullable|numeric|min:0'];
    }

    protected function body(array $d, string $l): array
    {
        return $l === 'en' ? [
            'We are pleased to confirm your appointment as '.$d['position'].' with effect from '.Format::date($d['effective_date'], $l).'.',
            ! empty($d['new_salary']) ? 'Your basic salary is revised to '.Format::rm($d['new_salary']).' per month.' : null,
            'All other terms and conditions remain as stated in your letter of appointment.',
            'Congratulations, and we look forward to your continued commitment.',
        ] : [
            'Sukacita dimaklumkan bahawa anda telah disahkan dalam jawatan '.$d['position'].' berkuat kuasa mulai '.Format::date($d['effective_date'], $l).'.',
            ! empty($d['new_salary']) ? 'Gaji pokok anda diselaraskan kepada '.Format::rm($d['new_salary']).' sebulan.' : null,
            'Terma dan syarat perkhidmatan lain kekal seperti dalam surat lantikan anda.',
            'Syarikat mengucapkan tahniah dan berharap anda terus memberikan komitmen yang cemerlang.',
        ];
    }
}
