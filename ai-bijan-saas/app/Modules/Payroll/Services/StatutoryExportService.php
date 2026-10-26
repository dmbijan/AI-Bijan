<?php

namespace App\Modules\Payroll\Services;

use App\Modules\Payroll\Models\Employee;
use App\Modules\Payroll\Models\PayrollRun;

/**
 * Eksport statutori payroll (BAB 57).
 *
 * Menghasilkan fail berformat untuk penyerahan manual ke portal rasmi:
 * - EPF/KWSP  — i-Akaun (Majikan) caruman bulanan
 * - SOCSO/PERKESO — ASSIST portal
 * - EIS/SIP   — ASSIST portal (sebahagian PERKESO)
 * - PCB/MTD   — e-PCB LHDN
 * - Bank      — fail pindahan bank (IBG/DuitNow)
 *
 * ⚠️ Format ini mengikut susunan lajur yang biasa digunakan. Sebelum penyerahan
 * sebenar, bandingkan dengan templat rasmi terkini dari KWSP/PERKESO/LHDN —
 * format berubah antara tahun. Setiap fail menyertakan checksum baris untuk
 * pengesahan selepas muat naik.
 */
class StatutoryExportService
{
    /** Tajuk lajur EPF — susunan mengikut templat i-Akaun majikan. */
    private const EPF_HEADER = ['NO_KP', 'NO_EPF', 'NAMA', 'GAJI_BULANAN', 'CARUMAN_PEKERJA', 'CARUMAN_MAJIKAN'];

    private const SOCSO_HEADER = ['NO_KP', 'NO_SOCSO', 'NAMA', 'GAJI', 'CARUMAN_PEKERJA', 'CARUMAN_MAJIKAN'];

    private const EIS_HEADER = ['NO_KP', 'NO_EIS', 'NAMA', 'GAJI', 'CARUMAN_PEKERJA', 'CARUMAN_MAJIKAN'];

    private const PCB_HEADER = ['NO_KP', 'NO_CUKAI', 'NAMA', 'GAJI_KASAR', 'PCB'];

    /**
     * Hasilkan fail statutori untuk satu payroll run.
     *
     * @return array<string, array{content: string, filename: string, mime: string, rows: int, total: string}>
     */
    public function jana(PayrollRun $run, string $jenis): array
    {
        $detail = $run->details()->with('employee')->get();
        $tempoh = $run->period_start?->format('Ym') ?? $run->created_at->format('Ym');

        return match ($jenis) {
            'epf' => ['epf' => $this->csv('EPF', self::EPF_HEADER, $detail, $tempoh, 'epf_ee', 'epf_er', fn ($d) => [$d->employee?->ic_no, $d->employee?->epf_no, $d->employee?->name, $d->gross_pay])],
            'socso' => ['socso' => $this->csv('SOCSO', self::SOCSO_HEADER, $detail, $tempoh, 'socso_ee', 'socso_er', fn ($d) => [$d->employee?->ic_no, $d->employee?->socso_no, $d->employee?->name, $d->gross_pay])],
            'eis' => ['eis' => $this->csv('EIS', self::EIS_HEADER, $detail, $tempoh, 'eis_ee', 'eis_er', fn ($d) => [$d->employee?->ic_no, $d->employee?->eis_no, $d->employee?->name, $d->gross_pay])],
            'pcb' => ['pcb' => $this->csvPcb($detail, $tempoh)],
            'bank' => ['bank' => $this->failBank($detail, $tempoh)],
            'semua' => $this->semua($run, $detail, $tempoh),
            default => throw new \InvalidArgumentException("Jenis eksport tidak sah: {$jenis}"),
        };
    }

    /** @return array<string, array{content: string, filename: string, mime: string, rows: int, total: string}> */
    private function semua(PayrollRun $run, $detail, string $tempoh): array
    {
        return [
            'epf' => $this->csv('EPF', self::EPF_HEADER, $detail, $tempoh, 'epf_ee', 'epf_er', fn ($d) => [$d->employee?->ic_no, $d->employee?->epf_no, $d->employee?->name, $d->gross_pay]),
            'socso' => $this->csv('SOCSO', self::SOCSO_HEADER, $detail, $tempoh, 'socso_ee', 'socso_er', fn ($d) => [$d->employee?->ic_no, $d->employee?->socso_no, $d->employee?->name, $d->gross_pay]),
            'eis' => $this->csv('EIS', self::EIS_HEADER, $detail, $tempoh, 'eis_ee', 'eis_er', fn ($d) => [$d->employee?->ic_no, $d->employee?->eis_no, $d->employee?->name, $d->gross_pay]),
            'pcb' => $this->csvPcb($detail, $tempoh),
            'bank' => $this->failBank($detail, $tempoh),
            'ringkasan' => $this->ringkasan($run, $detail, $tempoh),
        ];
    }

    private function csv(string $awalan, array $tajuk, $detail, string $tempoh, string $medanEe, string $medanEr, callable $lajur): array
    {
        $baris = [];
        $jumlahEe = 0.0;
        $jumlahEr = 0.0;

        foreach ($detail as $d) {
            $ee = (float) $d->{$medanEe};
            $er = (float) $d->{$medanEr};
            $jumlahEe += $ee;
            $jumlahEr += $er;

            $baris[] = array_merge($lajur($d), [
                number_format($ee, 2, '.', ''),
                number_format($er, 2, '.', ''),
            ]);
        }

        // Baris jumlah + checksum untuk pengesahan.
        $baris[] = ['JUMLAH', '', '', '', number_format($jumlahEe, 2, '.', ''), number_format($jumlahEr, 2, '.', '')];

        $isi = $this->tulisCsv($tajuk, $baris);
        $checksum = substr(hash('sha256', $isi), 0, 12);

        return [
            'content' => $isi,
            'filename' => sprintf('%s_%s_%s.csv', strtolower($awalan), $tempoh, $checksum),
            'mime' => 'text/csv; charset=UTF-8',
            'rows' => count($baris),
            'total' => number_format($jumlahEe + $jumlahEr, 2, '.', ''),
            'checksum' => $checksum,
        ];
    }

    private function csvPcb($detail, string $tempoh): array
    {
        $baris = [];
        $jumlah = 0.0;

        foreach ($detail as $d) {
            $pcb = (float) $d->pcb;
            $jumlah += $pcb;
            $baris[] = [$d->employee?->ic_no, $d->employee?->tax_no, $d->employee?->name,
                number_format((float) $d->gross_pay, 2, '.', ''), number_format($pcb, 2, '.', '')];
        }
        $baris[] = ['JUMLAH', '', '', '', number_format($jumlah, 2, '.', '')];

        $isi = $this->tulisCsv(self::PCB_HEADER, $baris);
        $checksum = substr(hash('sha256', $isi), 0, 12);

        return [
            'content' => $isi,
            'filename' => "pcb_{$tempoh}_{$checksum}.csv",
            'mime' => 'text/csv; charset=UTF-8',
            'rows' => count($baris),
            'total' => number_format($jumlah, 2, '.', ''),
            'checksum' => $checksum,
        ];
    }

    /** Fail pindahan bank — format generik IBG (akaun, nama, amaun). */
    private function failBank($detail, string $tempoh): array
    {
        $baris = [];
        $jumlah = 0.0;

        foreach ($detail as $d) {
            $net = (float) $d->net_pay;
            $jumlah += $net;

            $baris[] = [
                $d->employee?->bank_name ?? '',
                $d->employee?->bank_account_no ?? '',
                $d->employee?->name ?? '',
                number_format($net, 2, '.', ''),
                'GAJI '.$tempoh,
            ];
        }
        $baris[] = ['JUMLAH', '', '', number_format($jumlah, 2, '.', ''), ''];

        $isi = $this->tulisCsv(['BANK', 'NO_AKAUN', 'NAMA_PENERIMA', 'AMAUN', 'RUJUKAN'], $baris);
        $checksum = substr(hash('sha256', $isi), 0, 12);

        return [
            'content' => $isi,
            'filename' => "bank_{$tempoh}_{$checksum}.csv",
            'mime' => 'text/csv; charset=UTF-8',
            'rows' => count($baris),
            'total' => number_format($jumlah, 2, '.', ''),
            'checksum' => $checksum,
        ];
    }

    /** Ringkasan silang untuk pengesahan manual sebelum penyerahan. */
    private function ringkasan(PayrollRun $run, $detail, string $tempoh): array
    {
        $jumlah = fn (string $m) => $detail->sum(fn ($d) => (float) $d->{$m});

        $baris = [
            ['Bilangan pekerja', (string) $detail->count()],
            ['Jumlah kasar', number_format($jumlah('gross_pay'), 2, '.', '')],
            ['EPF pekerja', number_format($jumlah('epf_ee'), 2, '.', '')],
            ['EPF majikan', number_format($jumlah('epf_er'), 2, '.', '')],
            ['SOCSO pekerja', number_format($jumlah('socso_ee'), 2, '.', '')],
            ['SOCSO majikan', number_format($jumlah('socso_er'), 2, '.', '')],
            ['EIS pekerja', number_format($jumlah('eis_ee'), 2, '.', '')],
            ['EIS majikan', number_format($jumlah('eis_er'), 2, '.', '')],
            ['PCB', number_format($jumlah('pcb'), 2, '.', '')],
            ['HRDF', number_format($jumlah('hrdf'), 2, '.', '')],
            ['Jumlah bersih', number_format($jumlah('net_pay'), 2, '.', '')],
        ];

        $isi = $this->tulisCsv(['PERKARA', 'AMAUN'], $baris);
        $checksum = substr(hash('sha256', $isi), 0, 12);

        return [
            'content' => $isi,
            'filename' => "ringkasan_{$tempoh}_{$checksum}.csv",
            'mime' => 'text/csv; charset=UTF-8',
            'rows' => count($baris),
            'total' => number_format($jumlah('net_pay'), 2, '.', ''),
            'checksum' => $checksum,
        ];
    }

    /** CSV dengan BOM UTF-8 supaya Excel papar aksara Melayu dengan betul. */
    private function tulisCsv(array $tajuk, array $baris): string
    {
        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF");
        fputcsv($fh, $tajuk);
        foreach ($baris as $b) {
            fputcsv($fh, $b);
        }
        rewind($fh);
        $isi = stream_get_contents($fh);
        fclose($fh);

        return $isi;
    }
}
