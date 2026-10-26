<?php

namespace App\Documents\Renderers;

use App\Documents\DocumentModel;
use App\Documents\Format;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\Language;

/** DOCX (Microsoft Word) & ODT (OpenDocument) — boleh disunting. */
class WordRenderer
{
    public function render(DocumentModel $d, array $theme, string $format = 'docx', string $paper = 'A4'): string
    {
        $w = new PhpWord;
        $w->getSettings()->setThemeFontLang(new Language('ms-MY'));
        $w->setDefaultFontName(str_contains($theme['font'], 'Serif') ? 'Times New Roman' : 'Arial');
        $w->setDefaultFontSize(10);
        $w->getDocInfo()->setTitle($d->title.' '.$d->number)->setCreator('AI Bijan');
        $ac = ltrim($theme['accent'], '#');
        $s = $w->addSection(['paperSize' => $paper === 'Letter' ? 'Letter' : 'A4', 'marginTop' => Converter::cmToTwip(1.6), 'marginBottom' => Converter::cmToTwip(1.8), 'marginLeft' => Converter::cmToTwip(1.4), 'marginRight' => Converter::cmToTwip(1.4)]);
        $s->addFooter()->addPreserveText($d->footer.'   ·   Muka {PAGE}/{NUMPAGES}', ['size' => 7, 'color' => '777777']);
        $iss = $d->issuer;
        if (! empty($iss['logo_path']) && is_file($iss['logo_path'])) {
            $s->addImage($iss['logo_path'], ['height' => 40]);
        }
        $s->addText($iss['name'] ?? '', ['bold' => true, 'size' => 13]);
        $reg = array_filter([! empty($iss['reg_no']) ? 'No. Pendaftaran: '.$iss['reg_no'] : null, ! empty($iss['sst_no']) ? 'No. SST: '.$iss['sst_no'] : null]);
        foreach (array_filter([implode(' · ', $reg), $iss['address'] ?? null, implode(' · ', array_filter([$iss['phone'] ?? null, $iss['email'] ?? null]))]) as $l) {
            foreach (explode("\n", $l) as $x) {
                $s->addText($x, ['size' => 8.5], ['spaceAfter' => 0]);
            }
        }
        if ($d->confidential) {
            $s->addText('SULIT / CONFIDENTIAL', ['bold' => true, 'color' => 'B91C1C', 'size' => 8.5]);
        }
        $s->addText($theme['title_case'] === 'upper' ? mb_strtoupper($d->title) : $d->title, ['bold' => true, 'size' => 15, 'color' => $ac], ['spaceBefore' => 200, 'borderBottomSize' => 12, 'borderBottomColor' => ltrim($theme['rule'], '#')]);
        $t = $s->addTable(['width' => 100 * 50, 'unit' => 'pct']);
        $t->addRow();
        $kiri = $t->addCell(Converter::cmToTwip(9.5));
        if ($d->recipient) {
            $kiri->addText($d->recipient['label'] ?? '', ['size' => 8.5, 'color' => '555555'], ['spaceAfter' => 0]);
            $kiri->addText($d->recipient['name'] ?? '', ['bold' => true], ['spaceAfter' => 0]);
            foreach ($d->recipient['lines'] ?? [] as $l) {
                $kiri->addText($l, ['size' => 8.5], ['spaceAfter' => 0]);
            }
        }
        $kanan = $t->addCell(Converter::cmToTwip(8));
        foreach ($d->meta as $l => $v) {
            $r = $kanan->addTextRun(['spaceAfter' => 0]);
            $r->addText($l.': ', ['color' => '555555', 'size' => 9]);
            $r->addText((string) $v, ['size' => 9]);
        }
        foreach ($d->blocks as $b) {
            match ($b['type']) {
                'heading' => $s->addText(mb_strtoupper($b['text']), ['bold' => true, 'color' => $ac, 'size' => 10.5], ['spaceBefore' => 200]),
                'paragraph' => array_map(fn ($p) => $s->addText($p, [], ['spaceAfter' => 120]), explode("\n", $b['text'])),
                'note' => $s->addText($b['text'], ['size' => 8.5, 'italic' => true, 'color' => '444444'], ['spaceBefore' => 160]),
                'kv' => $this->kv($s, $b, $ac),
                'table' => $this->jadual($s, $b, $ac),
                'totals' => $this->jumlah($s, $b),
                'signatures' => $this->ttd($s, $b),
                default => null,
            };
        }
        $tmp = tempnam(sys_get_temp_dir(), 'aibdoc');
        IOFactory::createWriter($w, $format === 'odt' ? 'ODText' : 'Word2007')->save($tmp);
        $out = file_get_contents($tmp);
        @unlink($tmp);

        return $out;
    }

    private function kv($s, array $b, string $ac): void
    {
        if ($b['title']) {
            $s->addText(mb_strtoupper($b['title']), ['bold' => true, 'color' => $ac, 'size' => 10.5], ['spaceBefore' => 200]);
        }
        $t = $s->addTable(['borderBottomSize' => 4, 'borderBottomColor' => 'EEEEEE', 'cellMargin' => 50]);
        foreach ($b['rows'] as $l => $v) {
            $t->addRow();
            $t->addCell(Converter::cmToTwip(6))->addText($l, ['color' => '555555'], ['spaceAfter' => 0]);
            $t->addCell(Converter::cmToTwip(11))->addText((string) $v, [], ['spaceAfter' => 0]);
        }
    }

    private function jadual($s, array $b, string $ac): void
    {
        if ($b['title']) {
            $s->addText(mb_strtoupper($b['title']), ['bold' => true, 'color' => $ac, 'size' => 10.5], ['spaceBefore' => 200]);
        }
        $t = $s->addTable(['borderSize' => 4, 'borderColor' => 'DDDDDD', 'cellMargin' => 60, 'width' => 100 * 50, 'unit' => 'pct']);
        $t->addRow(null, ['tblHeader' => true]);
        foreach ($b['columns'] as $c) {
            $t->addCell(null, ['bgColor' => $ac])->addText($c['label'], ['bold' => true, 'color' => 'FFFFFF', 'size' => 9], ['alignment' => ($c['align'] ?? '') === 'right' ? 'right' : 'left', 'spaceAfter' => 0]);
        }
        foreach ($b['rows'] as $row) {
            $t->addRow();
            foreach ($b['columns'] as $i => $c) {
                $v = $row[$i] ?? '';
                $t->addCell()->addText(! empty($c['money']) && is_numeric($v) ? Format::rm($v) : (string) $v, ['size' => 9], ['alignment' => ($c['align'] ?? '') === 'right' ? 'right' : 'left', 'spaceAfter' => 0]);
            }
        }
    }

    private function jumlah($s, array $b): void
    {
        $t = $s->addTable(['alignment' => 'right', 'cellMargin' => 50]);
        foreach ($b['rows'] as $r) {
            $t->addRow();
            $f = ! empty($r[2]) ? ['bold' => true] : [];
            $t->addCell(Converter::cmToTwip(5.5))->addText($r[0], $f, ['spaceAfter' => 0]);
            $t->addCell(Converter::cmToTwip(3.5))->addText(is_numeric($r[1]) ? Format::rm($r[1]) : (string) $r[1], $f, ['alignment' => 'right', 'spaceAfter' => 0]);
        }
    }

    private function ttd($s, array $b): void
    {
        $t = $s->addTable(['cellMargin' => 80]);
        $t->addRow(Converter::cmToTwip(2.2));
        foreach ($b['slots'] as $x) {
            $c = $t->addCell(Converter::cmToTwip(8.5), ['valign' => 'bottom']);
            $c->addText('______________________________', [], ['spaceAfter' => 0]);
            $c->addText($x['label'], ['bold' => true], ['spaceAfter' => 0]);
            foreach (array_filter([$x['name'] ?? null, $x['position'] ?? null, 'Tarikh:']) as $l) {
                $c->addText($l, ['size' => 8.5], ['spaceAfter' => 0]);
            }
        }
    }
}
