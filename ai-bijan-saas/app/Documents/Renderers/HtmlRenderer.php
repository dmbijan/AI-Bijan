<?php

namespace App\Documents\Renderers;

use App\Documents\DocumentModel;
use App\Documents\Format;
use Dompdf\Dompdf;
use Dompdf\Options;

/** HTML (pratonton/cetak pelayar) dan PDF (dompdf). CSS ringkas berasaskan jadual supaya serasi dompdf. */
class HtmlRenderer
{
    public function html(DocumentModel $d, array $theme, string $paper = 'A4'): string
    {
        $e = fn ($s) => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
        $tajuk = $theme['title_case'] === 'upper' ? mb_strtoupper($d->title) : $d->title;
        $iss = $d->issuer;
        $logo = ! empty($iss['logo_data']) ? '<img src="'.$iss['logo_data'].'" style="max-height:56px;max-width:160px">' : '';
        $reg = array_filter([
            ! empty($iss['reg_no']) ? 'No. Pendaftaran: '.$e($iss['reg_no']) : null,
            ! empty($iss['sst_no']) ? 'No. SST: '.$e($iss['sst_no']) : null,
        ]);
        $h = '<!doctype html><html lang="ms"><head><meta charset="utf-8"><title>'.$e($d->title.' '.$d->number).'</title><style>'
            .'@page{size:'.$paper.';margin:16mm 14mm 18mm 14mm}'
            .'body{font-family:"'.$theme['font'].'",sans-serif;font-size:9.5pt;color:#111;line-height:1.35}'
            .'table{border-collapse:collapse;width:100%}td,th{vertical-align:top}'
            .'.kepala td{padding:8px 10px;background:'.$theme['header_bg'].';color:'.$theme['header_fg'].'}'
            .'.syarikat{font-size:13pt;font-weight:bold}.kecil{font-size:8.5pt}'
            .'.tajuk{font-size:14pt;font-weight:bold;color:'.$theme['accent'].';letter-spacing:.5px;margin:10px 0 2px}'
            .'.garis{border-bottom:2px solid '.$theme['rule'].';margin:6px 0 10px}'
            .'.meta td{padding:2px 0}.meta td.l{color:#555;width:38%}'
            .'.jadual th{background:'.$theme['accent'].';color:#fff;padding:4px 6px;font-size:8.5pt;text-align:left}.jadual th.r{text-align:right}'
            .'.jadual td{padding:3px 6px;border-bottom:1px solid #e5e7eb}.r{text-align:right}'
            .'.kv td{padding:2px 6px;border-bottom:1px solid #f0f0f0}.kv td.l{color:#555;width:40%}'
            .'.jum td{padding:2px 6px}.jum .b td{font-weight:bold;border-top:2px solid '.$theme['rule'].'}'
            .'h3{font-size:10pt;color:'.$theme['accent'].';margin:10px 0 4px;text-transform:uppercase;letter-spacing:.4px}'
            .'.nota{font-size:8.5pt;color:#444;border-left:3px solid '.$theme['rule'].';padding:4px 8px;margin-top:10px}'
            .'.ttd td{padding-top:40px;width:50%}.ttd .g{border-top:1px solid #333;padding-top:4px;width:80%}'
            .'.sulit{color:#B91C1C;font-weight:bold;font-size:8.5pt;letter-spacing:1px}'
            .'.kaki{position:fixed;bottom:-10mm;left:0;right:0;font-size:7.5pt;color:#777;border-top:1px solid #ddd;padding-top:3px}'
            .'p{margin:0 0 6px}</style></head><body>';
        $h .= '<table class="kepala"><tr><td style="width:65%">'.$logo.'<div class="syarikat">'.$e($iss['name'] ?? '').'</div>'
            .($reg ? '<div class="kecil">'.implode(' · ', $reg).'</div>' : '')
            .'<div class="kecil">'.nl2br($e($iss['address'] ?? '')).'</div>'
            .'<div class="kecil">'.$e(implode(' · ', array_filter([$iss['phone'] ?? null, $iss['email'] ?? null]))).'</div></td>'
            .'<td class="r">'.($d->confidential ? '<div class="sulit">SULIT / CONFIDENTIAL</div>' : '').'</td></tr></table>';
        $h .= '<div class="tajuk">'.$e($tajuk).'</div><div class="garis"></div>';
        $h .= '<table><tr><td style="width:55%">';
        if ($d->recipient) {
            $h .= '<div class="kecil" style="color:#555">'.$e($d->recipient['label'] ?? '').'</div><div style="font-weight:bold">'.$e($d->recipient['name'] ?? '').'</div>'
                .'<div class="kecil">'.implode('<br>', array_map($e, $d->recipient['lines'] ?? [])).'</div>';
        }
        $h .= '</td><td><table class="meta">';
        foreach ($d->meta as $l => $v) {
            $h .= '<tr><td class="l">'.$e($l).'</td><td>'.$e($v).'</td></tr>';
        }
        $h .= '</table></td></tr></table>';
        foreach ($d->blocks as $b) {
            $h .= match ($b['type']) {
                'heading' => '<h3>'.$e($b['text']).'</h3>',
                'paragraph' => '<p>'.nl2br($e($b['text'])).'</p>',
                'note' => '<div class="nota">'.nl2br($e($b['text'])).'</div>',
                'kv' => ($b['title'] ? '<h3>'.$e($b['title']).'</h3>' : '').$this->kv($b, $e),
                'table' => $this->jadual($b, $e),
                'totals' => '<table class="jum" style="width:55%;margin-left:45%;margin-top:6px">'.implode('', array_map(fn ($r) => '<tr'.(! empty($r[2]) ? ' class="b"' : '').'><td>'.$e($r[0]).'</td><td class="r">'.$e(is_numeric($r[1]) ? Format::rm($r[1]) : $r[1]).'</td></tr>', $b['rows'])).'</table>',
                'signatures' => '<table class="ttd"><tr>'.implode('', array_map(fn ($s) => '<td><div class="g">'.$e($s['label']).'<br><span class="kecil">'.$e($s['name'] ?? '').'</span>'.(! empty($s['position']) ? '<br><span class="kecil">'.$e($s['position']).'</span>' : '').'<br><span class="kecil">Tarikh:</span></div></td>', $b['slots'])).'</tr></table>',
                default => '',
            };
        }
        $h .= '<div class="kaki">'.$e($d->footer).'</div></body></html>';

        return $h;
    }

    /** Senarai label→nilai; cols=2 susun dua pasangan sebaris (jimat ruang). */
    private function kv(array $b, callable $e): string
    {
        $pasangan = array_map(null, array_keys($b['rows']), array_values($b['rows']));
        $n = max(1, (int) ($b['cols'] ?? 1));
        $h = '<table class="kv">';
        foreach (array_chunk($pasangan, $n) as $baris) {
            $h .= '<tr>';
            foreach ($baris as [$l, $v]) {
                $h .= '<td class="l"'.($n > 1 ? ' style="width:'.(40 / $n).'%"' : '').'>'.$e($l).'</td><td>'.$e($v).'</td>';
            }
            $h .= str_repeat('<td></td><td></td>', $n - count($baris)).'</tr>';
        }

        return $h.'</table>';
    }

    private function jadual(array $b, callable $e): string
    {
        $h = ($b['title'] ? '<h3>'.$e($b['title']).'</h3>' : '').'<table class="jadual"><tr>';
        foreach ($b['columns'] as $c) {
            $h .= '<th'.(($c['align'] ?? '') === 'right' ? ' class="r"' : '').'>'.$e($c['label']).'</th>';
        }
        $h .= '</tr>';
        foreach ($b['rows'] as $row) {
            $h .= '<tr>';
            foreach ($b['columns'] as $i => $c) {
                $v = $row[$i] ?? '';
                $h .= '<td'.(($c['align'] ?? '') === 'right' ? ' class="r"' : '').'>'.$e(! empty($c['money']) && is_numeric($v) ? Format::rm($v) : $v).'</td>';
            }
            $h .= '</tr>';
        }

        return $h.'</table>';
    }

    public function pdf(DocumentModel $d, array $theme, string $paper = 'A4'): string
    {
        $o = new Options;
        $o->set('isRemoteEnabled', false);   // tiada muat turun luar (elak SSRF)
        $o->set('isPhpEnabled', false);
        $o->set('defaultFont', $theme['font']);
        $o->setChroot(base_path('vendor/dompdf/dompdf'));
        $pdf = new Dompdf($o);
        $pdf->loadHtml($this->html($d, $theme, $paper), 'UTF-8');
        $pdf->setPaper($paper, 'portrait');
        $pdf->addInfo('Title', $d->title.' '.$d->number);
        $pdf->addInfo('Creator', 'AI Bijan');
        $pdf->render();
        $c = $pdf->getCanvas();
        $c->page_text(520, 815, 'Muka {PAGE_NUM}/{PAGE_COUNT}', $pdf->getFontMetrics()->getFont($theme['font']), 7, [0.45, 0.45, 0.45]);

        return $pdf->output();
    }
}
