<?php

namespace App\Documents\Templates;

use App\Documents\Format;

class SuratKenaikanGaji extends Surat
{
    public function key(): string
    {
        return 'surat_kenaikan_gaji';
    }

    public function prefix(): string
    {
        return 'SKG';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Perubahan Gaji', 'Salary Revision Letter');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Perubahan gaji ', 'Salary revision ').'';
    }

    protected function extraRules(): array
    {
        return ['effective_date' => 'required|date', 'old_salary' => 'required|numeric|min:0', 'new_salary' => 'required|numeric|min:0', 'new_position' => 'nullable|string|max:80', 'reason' => 'nullable|string|max:300'];
    }

    protected function body(array $d, string $l): array
    {
        return $l === 'en' ? [
            'We are pleased to inform you that your basic salary is revised from '.Format::rm($d['old_salary']).' to '.Format::rm($d['new_salary']).' per month with effect from '.Format::date($d['effective_date'], $l).'.',
            ! empty($d['new_position']) ? 'Your new position is '.$d['new_position'].'.' : null,
            ! empty($d['reason']) ? $d['reason'] : null,
            'All other terms and conditions remain unchanged.',
        ] : [
            'Pihak syarikat dengan sukacitanya memaklumkan perubahan gaji pokok anda daripada '.Format::rm($d['old_salary']).' kepada '.Format::rm($d['new_salary']).' sebulan berkuat kuasa mulai '.Format::date($d['effective_date'], $l).'.',
            ! empty($d['new_position']) ? 'Jawatan baharu anda ialah '.$d['new_position'].'.' : null,
            ! empty($d['reason']) ? $d['reason'] : null,
            'Terma dan syarat perkhidmatan lain kekal tidak berubah.',
        ];
    }
}
