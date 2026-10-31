<?php

namespace App\Modules\AIAgents\Tools;

/**
 * "Tanya AI Bijan" (Master §12 prinsip 5: tanya, jangan cari).
 * Peringkat 1: padankan niat soalan BM/EN kepada alat sebenar (tanpa kos kredit AI, jawapan tepat dari data).
 * Soalan yang tidak difahami dijawab jujur dengan cadangan — bukan diteka.
 */
class TanyaAiBijan
{
    /** [corak regex, alat, params] — susunan penting: lebih khusus dahulu. */
    private const NIAT = [
        ['/tutup\s*kedai|laporan\s*(harian|hari)|ringkasan\s*hari|close\s*of\s*day/iu', 'pos.laporan_tutup_kedai', []],
        ['/(semak|isu|masalah).*stok|stok.*(tak|tidak)\s*(cukup|dapat)/iu', 'pos.semakan_stok', []],
        ['/stok|inventori|habis|restock|pesan\s*semula/iu', 'stok.rendah', []],
        ['/hutang|belum\s*(bayar|kutip)|lewat\s*bayar|tertunggak|invois|overdue/iu', 'invois.lewat_bayar', []],
        ['/jualan|sales|kutipan\s*hari|berapa\s*(dapat|untung)|hasil\s*hari/iu', 'pos.ringkasan_hari', []],
    ];

    public const CADANGAN = ['Jualan hari ni?', 'Siapa belum bayar?', 'Stok apa nak habis?', 'Laporan tutup kedai'];

    public function __construct(private AgentToolRunner $runner) {}

    public function jawab(string $soalan, ?int $userId): array
    {
        if (preg_match('/^\s*(cari|siapa)\s+pelanggan\s+(.{2,})$/iu', $soalan, $m)) {
            return $this->guna('pelanggan.cari', ['kata' => trim($m[2])], $userId);
        }
        foreach (self::NIAT as [$corak, $alat, $p]) {
            if (preg_match($corak, $soalan)) {
                return $this->guna($alat, $p, $userId);
            }
        }

        return ['faham' => false, 'jawapan' => 'Maaf, saya belum faham soalan itu. Cuba tanya: '.implode(' · ', self::CADANGAN), 'cadangan' => self::CADANGAN];
    }

    private function guna(string $alat, array $p, ?int $userId): array
    {
        $r = $this->runner->jalan('tanya_ai_bijan', $alat, $p, $userId);
        if (! $r['berjaya']) {
            return ['faham' => true, 'alat' => $alat, 'jawapan' => $r['mesej'] ?? 'Tidak dapat dijawab sekarang.', 'status' => $r['status']];
        }

        return ['faham' => true, 'alat' => $alat, 'jawapan' => $this->ayat($alat, $r['hasil']), 'hasil' => $r['hasil']];
    }

    private function ayat(string $alat, array $h): string
    {
        $rm = fn ($n) => 'RM '.number_format((float) $n, 2);

        return match ($alat) {
            'pos.ringkasan_hari' => $h['bilangan_jualan']
                ? "Jualan {$h['tarikh']}: {$rm($h['jumlah'])} daripada {$h['bilangan_jualan']} transaksi (purata {$rm($h['purata'])})."
                : "Belum ada jualan pada {$h['tarikh']}.",
            'invois.lewat_bayar' => $h['bilangan']
                ? "{$h['bilangan']} invois lewat bayar, jumlah {$rm($h['jumlah_tertunggak'])}. Paling lama: ".($h['senarai'][0]['pelanggan'] ?? '-')." ({$rm($h['senarai'][0]['baki'] ?? 0)})."
                : 'Tiada invois lewat bayar. 👍',
            'stok.rendah' => $h['bilangan']
                ? "{$h['bilangan']} barang perlu dipesan semula: ".implode(', ', array_map(fn ($s) => "{$s['nama']} (baki {$s['baki']})", array_slice($h['senarai'], 0, 5))).'.'
                : 'Semua stok di atas paras pesan semula.',
            'pos.semakan_stok' => $h['bilangan'] ? "{$h['bilangan']} jualan stoknya tak dapat dipotong. Semak POS → Kaunter → Semakan Stok." : 'Tiada isu stok pada jualan.',
            'pos.laporan_tutup_kedai' => $h['teks'],
            'pelanggan.cari' => $h['bilangan'] ? 'Jumpa: '.implode(', ', array_map(fn ($c) => $c['name'].($c['contact_phone'] ? " ({$c['contact_phone']})" : ''), $h['senarai'])) : 'Tiada pelanggan sepadan.',
            default => json_encode($h, JSON_UNESCAPED_UNICODE),
        };
    }
}
