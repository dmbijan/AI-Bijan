<?php

namespace App\Modules\PersonalAssistant\Services;

/**
 * Penjana embedding (BAB 17A.4 / T-08).
 *
 * Strategi berlapis:
 * 1. `provider` — panggil embedding API sebenar (jika disediakan).
 * 2. `hash`     — embedding deterministik tempatan (lalai).
 *
 * Embedding hash BUKAN semantik sebenar — ia menangkap pertindihan perkataan
 * (hashing trick + normalisasi). Ia membolehkan carian bekerja hari ini tanpa
 * kos API, dan boleh ditukar ke provider tanpa mengubah skema.
 *
 * PENTING: vektor dari dua model BERBEZA tidak boleh dicampur. Setiap chunk
 * menyimpan `embedding_model` untuk mengelak perbandingan silang model.
 */
class EmbeddingService
{
    public const MODEL_HASH = 'hash-v1';

    private int $dimensi;

    public function __construct()
    {
        $this->dimensi = (int) config('ai_gateway.embeddings.dimensions', 256);
    }

    public function model(): string
    {
        return (string) config('ai_gateway.embeddings.model', self::MODEL_HASH);
    }

    public function dimensi(): int
    {
        return $this->dimensi;
    }

    /**
     * Hasilkan embedding untuk teks.
     *
     * @return array<int, float> vektor ternormal (L2 = 1)
     */
    public function embed(string $teks): array
    {
        $model = $this->model();

        // Hanya hash-v1 disokong secara tempatan; provider lain perlu API.
        return $this->embedHash($teks);
    }

    /**
     * Embedding hashing-trick: setiap token → indeks bucket, nilai TF.
     * Vektor dinormalisasi supaya kosinus = dot product.
     *
     * @return array<int, float>
     */
    public function embedHash(string $teks): array
    {
        $vektor = array_fill(0, $this->dimensi, 0.0);

        foreach ($this->token($teks) as $t) {
            $i = crc32($t) % $this->dimensi;
            // Tanda dari bit tinggi mengurangkan pertembungan (signed hashing).
            $tanda = (crc32($t) & 0x80000000) ? -1.0 : 1.0;
            $vektor[$i] += $tanda;
        }

        return $this->normalisasi($vektor);
    }

    /** Kosinus antara dua vektor (anggaran dot product untuk vektor ternormal). */
    public function kosinus(array $a, array $b): float
    {
        $n = min(count($a), count($b));
        $dot = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $dot += $a[$i] * $b[$i];
        }

        return (float) $dot;
    }

    /**
     * Pecahkan teks kepada serpihan bertindih.
     *
     * @return array<int, string>
     */
    public function pecah(string $teks, ?int $saiz = null, ?int $tindih = null): array
    {
        $saiz ??= (int) config('ai_gateway.embeddings.chunk_size', 800);
        $tindih ??= (int) config('ai_gateway.embeddings.chunk_overlap', 100);

        $teks = trim(preg_replace('/\s+/u', ' ', $teks) ?? $teks);
        if ($teks === '') {
            return [];
        }

        $panjang = mb_strlen($teks);
        if ($panjang <= $saiz) {
            return [$teks];
        }

        $serpihan = [];
        $mula = 0;
        $langkah = max(1, $saiz - $tindih);

        while ($mula < $panjang) {
            $serpihan[] = mb_substr($teks, $mula, $saiz);
            $mula += $langkah;
        }

        return $serpihan;
    }

    /**
     * Tokenisasi ringkas: huruf/angka sahaja, huruf kecil, buang kata umum.
     *
     * @return array<int, string>
     */
    private function token(string $teks): array
    {
        $teks = mb_strtolower($teks);
        preg_match_all('/[\p{L}\p{N}]{2,}/u', $teks, $m);

        $henti = ['dan', 'atau', 'yang', 'untuk', 'dengan', 'dari', 'pada', 'ini', 'itu',
            'adalah', 'ialah', 'akan', 'tidak', 'the', 'and', 'or', 'of', 'to', 'in', 'is', 'a'];

        return array_values(array_diff($m[0], $henti));
    }

    /** @param array<int, float> $v */
    private function normalisasi(array $v): array
    {
        $norm = 0.0;
        foreach ($v as $x) {
            $norm += $x * $x;
        }
        $norm = sqrt($norm);

        if ($norm <= 0) {
            return $v;
        }

        return array_map(fn ($x) => $x / $norm, $v);
    }
}
