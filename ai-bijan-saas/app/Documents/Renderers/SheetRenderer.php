<?php

namespace App\Documents\Renderers;

use App\Documents\DocumentModel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

/** XLSX (Excel), ODS (OpenDocument) & CSV — data berstruktur. Nombor kekal nombor (bukan teks) supaya boleh dikira. */
class SheetRenderer
{
    public function render(DocumentModel $d, array $theme, string $format = 'xlsx'): string
    {
        if ($format === 'csv') {
            return $this->csv($d);
        }
        $x = new Spreadsheet;
        $x->getProperties()->setTitle($d->title.' '.$d->number)->setCreator('AI Bijan');
        $sh = $x->getActiveSheet();
        $sh->setTitle(mb_substr(preg_replace('/[\\\\\/?*\[\]:]/', '', $d->title), 0, 31));
        $ac = ltrim($theme['accent'], '#');
        $r = 1;
        $sh->setCellValue("A$r", $d->issuer['name'] ?? '')->getStyle("A$r")->getFont()->setBold(true)->setSize(13);
        $r++;
        foreach (array_filter([$d->issuer['reg_no'] ?? null ? 'No. Pendaftaran: '.$d->issuer['reg_no'] : null, $d->issuer['sst_no'] ?? null ? 'No. SST: '.$d->issuer['sst_no'] : null]) as $l) {
            $sh->setCellValue('A'.$r++, $l);
        }
        $r++;
        $sh->setCellValue("A$r", mb_strtoupper($d->title))->getStyle("A$r")->getFont()->setBold(true)->setSize(14)->getColor()->setRGB($ac);
        $r++;
        if ($d->confidential) {
            $sh->setCellValue('A'.$r++, 'SULIT / CONFIDENTIAL');
        }
        if ($d->recipient) {
            $sh->setCellValue("A$r", $d->recipient['label'] ?? '');
            $sh->setCellValue('B'.$r++, $d->recipient['name'] ?? '');
        }
        foreach ($d->meta as $l => $v) {
            $sh->setCellValue("A$r", $l);
            $sh->setCellValueExplicit('B'.$r++, (string) $v, DataType::TYPE_STRING);
        }
        $wang = '"RM"#,##0.00;-"RM"#,##0.00';
        foreach ($d->blocks as $b) {
            $r++;
            if (in_array($b['type'], ['heading', 'paragraph', 'note'])) {
                $sh->setCellValue('A'.$r, $b['text']);
                if ($b['type'] === 'heading') {
                    $sh->getStyle("A$r")->getFont()->setBold(true)->getColor()->setRGB($ac);
                }
            } elseif ($b['type'] === 'kv') {
                if ($b['title']) {
                    $sh->setCellValue('A'.$r, $b['title']);
                    $sh->getStyle("A$r")->getFont()->setBold(true)->getColor()->setRGB($ac);
                    $r++;
                }
                foreach ($b['rows'] as $l => $v) {
                    $sh->setCellValue("A$r", $l);
                    $sh->setCellValueExplicit('B'.$r++, (string) $v, DataType::TYPE_STRING);
                }
                $r--;
            } elseif ($b['type'] === 'table') {
                if ($b['title']) {
                    $sh->setCellValue('A'.$r, $b['title']);
                    $sh->getStyle("A$r")->getFont()->setBold(true)->getColor()->setRGB($ac);
                    $r++;
                }
                foreach ($b['columns'] as $i => $c) {
                    $col = chr(65 + $i);
                    $sh->setCellValue("$col$r", $c['label']);
                    $st = $sh->getStyle("$col$r");
                    $st->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
                    $st->getFill()->setFillType('solid')->getStartColor()->setRGB($ac);
                }
                foreach ($b['rows'] as $row) {
                    $r++;
                    foreach ($b['columns'] as $i => $c) {
                        $col = chr(65 + $i);
                        $v = $row[$i] ?? '';
                        $sh->setCellValue("$col$r", $v);
                        if (! empty($c['money'])) {
                            $sh->getStyle("$col$r")->getNumberFormat()->setFormatCode($wang);
                        }
                    }
                }
            } elseif ($b['type'] === 'totals') {
                foreach ($b['rows'] as $t) {
                    $sh->setCellValue("C$r", $t[0]);
                    $sh->setCellValue("D$r", is_numeric($t[1]) ? (float) $t[1] : $t[1]);
                    if (is_numeric($t[1])) {
                        $sh->getStyle("D$r")->getNumberFormat()->setFormatCode($wang);
                    }
                    if (! empty($t[2])) {
                        $sh->getStyle("C$r:D$r")->getFont()->setBold(true);
                    }
                    $r++;
                }
                $r--;
            } elseif ($b['type'] === 'signatures') {
                foreach ($b['slots'] as $i => $s) {
                    $sh->setCellValue(chr(65 + $i * 2).$r, $s['label'].': ____________________');
                }
            }
        }
        $sh->setCellValue('A'.($r + 2), $d->footer);
        foreach (range('A', 'F') as $c) {
            $sh->getColumnDimension($c)->setAutoSize(true);
        }
        $tmp = tempnam(sys_get_temp_dir(), 'aibxls');
        IOFactory::createWriter($x, $format === 'ods' ? 'Ods' : 'Xlsx')->save($tmp);
        $out = file_get_contents($tmp);
        @unlink($tmp);

        return $out;
    }

    /** CSV: jadual utama dokumen (+ maklumat). UTF-8 BOM supaya Excel buka Bahasa Melayu dengan betul. */
    private function csv(DocumentModel $d): string
    {
        $f = fopen('php://temp', 'r+');
        fwrite($f, "\xEF\xBB\xBF");
        fputcsv($f, [$d->title, $d->number, $d->date], ',', '"', '');
        foreach ($d->meta as $l => $v) {
            fputcsv($f, [$l, $v], ',', '"', '');
        }
        foreach ($d->blocks as $b) {
            if ($b['type'] === 'table') {
                fputcsv($f, [], ',', '"', '');
                fputcsv($f, array_column($b['columns'], 'label'), ',', '"', '');
                foreach ($b['rows'] as $row) {
                    fputcsv($f, array_map(fn ($v) => is_float($v) ? number_format($v, 2, '.', '') : $v, $row), ',', '"', '');
                }
            } elseif ($b['type'] === 'kv') {
                fputcsv($f, [], ',', '"', '');
                foreach ($b['rows'] as $l => $v) {
                    fputcsv($f, [$l, $v], ',', '"', '');
                }
            } elseif ($b['type'] === 'totals') {
                foreach ($b['rows'] as $t) {
                    fputcsv($f, [$t[0], is_numeric($t[1]) ? number_format((float) $t[1], 2, '.', '') : $t[1]], ',', '"', '');
                }
            }
        }
        rewind($f);

        return stream_get_contents($f);
    }
}
