<?php

namespace App\Documents\Templates;

use App\Documents\Format;

class SuratKelulusanCuti extends Surat
{
    public function key(): string
    {
        return 'surat_kelulusan_cuti';
    }

    public function prefix(): string
    {
        return 'SKC';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Surat Kelulusan Cuti', 'Leave Approval Letter');
    }

    protected function subject(array $d, string $l): string
    {
        return $this->t($l, 'Permohonan cuti ', 'Leave application ').$d['leave_type'] ?? '';
    }

    protected function extraRules(): array
    {
        return ['leave_type' => 'required|string|max:60', 'from' => 'required|date', 'to' => 'required|date|after_or_equal:from', 'days' => 'required|numeric|min:0.5', 'decision' => 'required|in:lulus,tolak', 'reason' => 'nullable|string|max:500', 'balance' => 'nullable|numeric'];
    }

    protected function body(array $d, string $l): array
    {
        return $l === 'en' ? [
            'Your '.$d['leave_type'].' application from '.Format::date($d['from'], $l).' to '.Format::date($d['to'], $l).' ('.$d['days'].' days) is '.($d['decision'] === 'lulus' ? 'APPROVED' : 'NOT APPROVED').'.',
            ! empty($d['reason']) ? 'Remarks: '.$d['reason'] : null,
            isset($d['balance']) ? 'Leave balance after this application: '.$d['balance'].' days.' : null,
        ] : [
            'Permohonan '.$d['leave_type'].' anda dari '.Format::date($d['from'], $l).' hingga '.Format::date($d['to'], $l).' ('.$d['days'].' hari) adalah '.($d['decision'] === 'lulus' ? 'DILULUSKAN' : 'TIDAK DILULUSKAN').'.',
            ! empty($d['reason']) ? 'Catatan: '.$d['reason'] : null,
            isset($d['balance']) ? 'Baki cuti selepas permohonan ini: '.$d['balance'].' hari.' : null,
        ];
    }
}
