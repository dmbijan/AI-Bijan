<?php

namespace App\Documents\Templates;

use App\Documents\DocumentModel;
use App\Documents\Format;
use App\Documents\Template;

/**
 * Format surat rasmi Malaysia: Rujukan, Tarikh, alamat penerima, salam, PERKARA, perenggan bernombor,
 * penutup ("Sekian, terima kasih." / "Yang benar,"), tandatangan, s.k., dan (pilihan) akuan penerimaan.
 */
abstract class Surat extends Template
{
    public function category(): string
    {
        return 'hr';
    }

    public function version(): int
    {
        return 1;
    }

    public function sensitive(): bool
    {
        return true;
    }

    /** Perenggan isi (bernombor). */
    abstract protected function body(array $d, string $l): array;

    abstract protected function subject(array $d, string $l): string;

    /** Akuan penerimaan oleh penerima (cth surat tawaran). */
    protected function acknowledgement(array $d, string $l): ?string
    {
        return null;
    }

    protected function extraRules(): array
    {
        return [];
    }

    public function rules(): array
    {
        return array_merge([
            'date' => 'required|date',
            'recipient.name' => 'required|string|max:120', 'recipient.ic_no' => 'nullable|string|max:20', 'recipient.staff_id' => 'nullable|string|max:30',
            'recipient.address' => 'nullable|string|max:400', 'recipient.position' => 'nullable|string|max:80',
            'signatory.name' => 'required|string|max:120', 'signatory.position' => 'required|string|max:80',
            'cc' => 'nullable|array', 'cc.*' => 'string|max:120', 'salutation' => 'nullable|in:Tuan,Puan,Tuan/Puan,Encik,Cik',
        ], $this->extraRules());
    }

    public function filename(array $d, string $number): string
    {
        $id = $d['recipient']['staff_id'] ?? null;

        return str_replace(' ', '-', $this->name()).'_'.$number.($id ? '_Pekerja-'.preg_replace('/[^A-Za-z0-9-]/', '', $id) : '');
    }

    public function build(array $d, array $issuer, string $number, string $l): DocumentModel
    {
        $r = $d['recipient'];
        $lines = array_values(array_filter([$r['position'] ?? null, ! empty($r['ic_no']) ? $this->t($l, 'No. K/P: ', 'IC No.: ').Format::mask($r['ic_no']) : null, ...explode("\n", (string) ($r['address'] ?? ''))]));
        $m = new DocumentModel($this->name($l), $number, $d['date'], $issuer, ['label' => '', 'name' => $r['name'], 'lines' => $lines], [
            $this->t($l, 'Rujukan Kami', 'Our Ref.') => $number,
            $this->t($l, 'Tarikh', 'Date') => Format::date($d['date'], $l),
        ], $this->footer($number, $l), $this->sensitive());
        $m->paragraph(($d['salutation'] ?? $this->t($l, 'Tuan/Puan', 'Dear Sir/Madam')).',');
        $m->heading($this->t($l, 'PERKARA: ', 'RE: ').mb_strtoupper($this->subject($d, $l)));
        foreach (array_values(array_filter($this->body($d, $l))) as $i => $p) {
            $m->paragraph(($i + 1).'.  '.$p);
        }
        $m->paragraph($this->t($l, 'Sekian, terima kasih.', 'Thank you.'));
        $m->paragraph($this->t($l, 'Yang benar,', 'Yours faithfully,'));
        $m->signatures([['label' => $issuer['name'] ?? '', 'name' => $d['signatory']['name'], 'position' => $d['signatory']['position']]]);
        if (! empty($d['cc'])) {
            $m->paragraph('s.k.  '.implode("\n       ", $d['cc']));
        }
        if ($ack = $this->acknowledgement($d, $l)) {
            $m->heading($this->t($l, 'Akuan Penerimaan', 'Acknowledgement'));
            $m->paragraph($ack);
            $m->signatures([['label' => $this->t($l, 'Tandatangan penerima', "Recipient's signature"), 'name' => $r['name'], 'position' => ! empty($r['ic_no']) ? $this->t($l, 'No. K/P: ', 'IC No.: ').Format::mask($r['ic_no']) : null]]);
        }

        return $m;
    }

    public function review(): array
    {
        return ['status' => 'perlu_semakan', 'note' => 'Templat surat pekerjaan; klausa perlu disemak oleh peguam/pakar HR mengikut Akta Kerja 1955 (atau Ordinan Buruh Sabah/Sarawak) dan kontrak syarikat sebelum digunakan.', 'references' => ['Akta Kerja 1955 (Akta 265)', 'Akta Perlindungan Data Peribadi 2010 (Akta 709)']];
    }
}
