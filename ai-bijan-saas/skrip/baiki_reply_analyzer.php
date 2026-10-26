<?php
/**
 * Betulkan ReplyAnalyzerService — tambah logik negasi.
 */
$f = __DIR__.'/../app/Modules/SalesCopilot/Services/ReplyAnalyzerService.php';
$c = file_get_contents($f);

// 1) Buang aksara CJK yang tersilap
$c = preg_replace('/[\x{4e00}-\x{9fff}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}\x{0600}-\x{06ff}]+/u', '', $c);

// 2) Ganti blok "// Skor tertinggi menang" dengan versi yang ada negasi
$old = <<<'OLD'
        // Skor tertinggi menang; confidence = bahagian daripada jumlah
        arsort($scores);
        $top = array_key_first($scores);
        $total = array_sum($scores);
        $confidence = round($scores[$top] / $total, 2);
OLD;

$new = <<<'NEW'
        // Negasi didahulukan: "Not interested" BUKAN "interested".
        if ($this->adaNegasi($text)) {
            foreach (self::NEGASI as $pola) {
                if (str_contains($text, mb_strtolower($pola))) {
                    return $this->result(
                        $this->kategoriNegatifUntuk($pola),
                        0.9,
                        [$pola],
                        false
                    );
                }
            }
        }

        // Skor tertinggi menang; confidence = bahagian daripada jumlah
        arsort($scores);
        $top = array_key_first($scores);
        $total = array_sum($scores);
        $confidence = round($scores[$top] / $total, 2);
NEW;

if (strpos($c, $old) === false) {
    fwrite(STDERR, "FAIL: blok 'Skor tertinggi menang' tidak dijumpai\n");
    exit(1);
}

$c = str_replace($old, $new, $c);

// 3) Tambah helper methods sebelum method "result"
$helper = <<<'HELPER'
    /**
     * Adakah teks mengandungi penegas (negation)?
     */
    private function adaNegasi(string $text): bool
    {
        foreach (self::NEGASI as $pola) {
            if (str_contains($text, mb_strtolower($pola))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kategori negatif yang sesuai untuk corak negation tertentu.
     */
    private function kategoriNegatifUntuk(string $pola): string
    {
        $pola = mb_strtolower($pola);

        if (str_contains($pola, 'unsubscribe') || str_contains($pola, 'remove')
            || str_contains($pola, 'stop ') || str_contains($pola, 'jangan')) {
            return self::UNSUBSCRIBE;
        }

        if (str_contains($pola, 'not the right') || str_contains($pola, 'bukan saya')
            || str_contains($pola, 'no longer')) {
            return self::WRONG_PERSON;
        }

        if (str_contains($pola, 'do not') || str_contains($pola, 'dont ')
            || str_contains($pola, "don't")) {
            return self::UNSUBSCRIBE;
        }

        return self::NEGATIVE;
    }

    /**
     * @return array{category: string, confidence: float, matched: string[], needs_human: bool}
     */
HELPER;

$anchor = <<<'ANCHOR'
    /**
     * @return array{category: string, confidence: float, matched: string[], needs_human: bool}
     */
    private function result(string $category, float $confidence, array $matched, bool $needsHuman): array
ANCHOR;

if (strpos($c, $anchor) === false) {
    fwrite(STDERR, "FAIL: anchor method result tidak dijumpai\n");
    exit(1);
}

$c = str_replace($anchor, $helper."    private function result(string \$category, float \$confidence, array \$matched, bool \$needsHuman): array\n", $c);

file_put_contents($f, $c);

// Verify
$check = file_get_contents($f);
$bad = preg_match_all('/[\x{4e00}-\x{9fff}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}\x{0600}-\x{06ff}]/u', $check);
echo "CJK tinggal: {$bad}\n";
echo "adaNegasi: ".(strpos($check, 'adaNegasi') !== false ? 'YA' : 'TIDAK')."\n";
echo "kategoriNegatifUntuk: ".(strpos($check, 'kategoriNegatifUntuk') !== false ? 'YA' : 'TIDAK')."\n";
echo "OK\n";
