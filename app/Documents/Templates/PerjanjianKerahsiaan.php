<?php

namespace App\Documents\Templates;

use App\Documents\DocumentModel;
use App\Documents\Format;
use App\Documents\Template;

class PerjanjianKerahsiaan extends Template
{
    public function key(): string
    {
        return 'perjanjian_kerahsiaan';
    }

    public function version(): int
    {
        return 1;
    }

    public function category(): string
    {
        return 'korporat';
    }

    public function prefix(): string
    {
        return 'NDA';
    }

    public function name(string $l = 'ms'): string
    {
        return $this->t($l, 'Perjanjian Kerahsiaan', 'Non-Disclosure Agreement');
    }

    public function formats(): array
    {
        return ['pdf', 'docx', 'odt', 'html', 'json'];
    }

    public function rules(): array
    {
        return ['date' => 'required|date', 'party.name' => 'required|string|max:150', 'party.reg_no' => 'nullable|string|max:40', 'party.address' => 'nullable|string|max:400',
            'party.signatory' => 'required|string|max:120', 'party.position' => 'nullable|string|max:80', 'purpose' => 'required|string|max:500', 'term_years' => 'required|integer|min:1|max:10',
            'mutual' => 'boolean', 'signatory.name' => 'required|string|max:120', 'signatory.position' => 'required|string|max:80'];
    }

    public function build(array $d, array $issuer, string $number, string $l): DocumentModel
    {
        $p = $d['party'];
        $m = new DocumentModel($this->name($l), $number, $d['date'], $issuer, ['label' => $this->t($l, 'Pihak Kedua', 'Second Party'), 'name' => $p['name'],
            'lines' => array_values(array_filter([! empty($p['reg_no']) ? $this->t($l, 'No. Pendaftaran: ', 'Reg. No.: ').$p['reg_no'] : null, $p['address'] ?? null]))],
            [$this->t($l, 'No. Perjanjian', 'Agreement No.') => $number, $this->t($l, 'Tarikh', 'Date') => Format::date($d['date'], $l)], $this->footer($number, $l), true);
        $dua = ! empty($d['mutual']);
        $klausa = $l === 'en' ? [
            ['Purpose', 'The parties wish to exchange information for the purpose of: '.$d['purpose'].' ("Purpose").'],
            ['Confidential Information', 'Any business, technical, financial, customer or personal data disclosed by '.($dua ? 'either party' : 'the First Party').', in any form, which is marked or reasonably understood to be confidential.'],
            ['Obligations', 'The receiving party shall use Confidential Information solely for the Purpose, shall not disclose it to any third party without prior written consent, and shall protect it with at least reasonable care.'],
            ['Exclusions', 'Obligations do not apply to information that is public through no fault of the receiving party, already lawfully known, independently developed, or required to be disclosed by law or court order (with prompt notice where lawful).'],
            ['Personal Data', 'Personal data shall be processed in accordance with the Personal Data Protection Act 2010.'],
            ['Term', 'This Agreement remains in force for '.$d['term_years'].' year(s) from the date above; confidentiality obligations survive termination for the same period.'],
            ['Return of Information', 'Upon request or termination, the receiving party shall return or destroy Confidential Information, subject to legal retention requirements.'],
            ['Governing Law', 'This Agreement is governed by the laws of Malaysia and the parties submit to the jurisdiction of the courts of Malaysia.'],
        ] : [
            ['Tujuan', 'Pihak-pihak ingin bertukar maklumat bagi tujuan: '.$d['purpose'].' ("Tujuan").'],
            ['Maklumat Sulit', 'Apa-apa maklumat perniagaan, teknikal, kewangan, pelanggan atau data peribadi yang didedahkan oleh '.($dua ? 'mana-mana pihak' : 'Pihak Pertama').' dalam apa jua bentuk, yang ditanda atau sewajarnya difahami sebagai sulit.'],
            ['Kewajipan', 'Pihak penerima hendaklah menggunakan Maklumat Sulit hanya bagi Tujuan, tidak mendedahkannya kepada pihak ketiga tanpa persetujuan bertulis terlebih dahulu, dan melindunginya dengan sekurang-kurangnya penjagaan yang munasabah.'],
            ['Pengecualian', 'Kewajipan tidak terpakai kepada maklumat yang menjadi umum bukan kerana kesalahan pihak penerima, telah diketahui secara sah, dibangunkan secara bebas, atau dikehendaki didedahkan oleh undang-undang atau perintah mahkamah (dengan notis segera jika dibenarkan).'],
            ['Data Peribadi', 'Data peribadi hendaklah diproses selaras dengan Akta Perlindungan Data Peribadi 2010.'],
            ['Tempoh', 'Perjanjian ini berkuat kuasa selama '.$d['term_years'].' tahun dari tarikh di atas; kewajipan kerahsiaan berterusan selepas penamatan bagi tempoh yang sama.'],
            ['Pemulangan Maklumat', 'Atas permintaan atau penamatan, pihak penerima hendaklah memulangkan atau memusnahkan Maklumat Sulit, tertakluk kepada keperluan simpanan di bawah undang-undang.'],
            ['Undang-undang Terpakai', 'Perjanjian ini ditadbir oleh undang-undang Malaysia dan pihak-pihak tunduk kepada bidang kuasa mahkamah Malaysia.'],
        ];
        $m->paragraph($this->t($l, 'PERJANJIAN INI dibuat pada tarikh di atas ANTARA ', 'THIS AGREEMENT is made on the date above BETWEEN ').($issuer['name'] ?? '').(! empty($issuer['reg_no']) ? ' ('.$issuer['reg_no'].')' : '').$this->t($l, ' ("Pihak Pertama") DAN ', ' ("First Party") AND ').$p['name'].(! empty($p['reg_no']) ? ' ('.$p['reg_no'].')' : '').$this->t($l, ' ("Pihak Kedua").', ' ("Second Party")').($dua ? $this->t($l, ' Perjanjian ini adalah bersama (dua hala).', ' This is a mutual agreement.') : ''));
        foreach ($klausa as $i => [$tajuk, $isi]) {
            $m->heading(($i + 1).'. '.$tajuk);
            $m->paragraph($isi);
        }
        $m->paragraph($this->t($l, 'SEBAGAI SAKSI, pihak-pihak telah menandatangani Perjanjian ini.', 'IN WITNESS WHEREOF, the parties have signed this Agreement.'));
        $m->signatures([
            ['label' => $this->t($l, 'Pihak Pertama', 'First Party'), 'name' => $d['signatory']['name'], 'position' => $d['signatory']['position']],
            ['label' => $this->t($l, 'Pihak Kedua', 'Second Party'), 'name' => $p['signatory'], 'position' => $p['position'] ?? null],
        ]);

        return $m;
    }

    public function review(): array
    {
        return ['status' => 'perlu_semakan', 'note' => 'Templat perjanjian umum; perlu disemak peguam bagi transaksi sebenar (duti setem mungkin terpakai).', 'references' => ['Akta Kontrak 1950', 'Akta Perlindungan Data Peribadi 2010', 'Akta Setem 1949']];
    }
}
