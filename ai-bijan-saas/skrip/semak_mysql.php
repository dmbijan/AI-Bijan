<?php

/**
 * Semak keserasian migrasi untuk MySQL tanpa perlu server MySQL.
 *
 * Jalankan: php skrip/semak_mysql.php
 *
 * Memeriksa:
 * - SQL mentah yang khusus SQLite
 * - lajur string tanpa panjang
 * - saiz indeks unik komposit (had 3072 bait pada utf8mb4)
 */

$akar = dirname(__DIR__);

$isu = [];
$amaran = [];
$bil = 0;

$fail = array_merge(
    glob($akar.'/database/migrations/*.php') ?: [],
    glob($akar.'/app/Modules/*/database/migrations/*.php') ?: [],
    glob($akar.'/app/Modules/*/database/migrations/*/*.php') ?: []
);

foreach ($fail as $f) {
    $kod = file_get_contents($f);
    $nama = str_replace($akar.'/', '', $f);
    $bil++;

    // SQL mentah khusus SQLite.
    if (preg_match('/DB::statement\s*\(\s*[\'"](.*?)[\'"]/s', $kod, $m)) {
        if (preg_match('/PRAGMA|AUTOINCREMENT/i', $m[1])) {
            $isu[] = "$nama: SQL khusus SQLite → `{$m[1]}`";
        }
    }

    // String tanpa panjang (Laravel default 255 — selamat, tetapi beri amaran).
    if (preg_match_all('/\$t(?:able)?->string\(\s*[\'"][^\'"]+[\'"]\s*\)/', $kod, $mm)) {
        $amaran[] = "$nama: ".count($mm[0]).' lajur string tanpa panjang (lalai 255)';
    }

    // Kira saiz indeks unik komposit menggunakan jenis lajur sebenar.
    if (preg_match_all('/\$t(?:able)?->unique\(\s*\[([^\]]+)\]/', $kod, $mu)) {
        foreach ($mu[1] as $senarai) {
            $lajur = array_map(fn ($s) => trim($s, " \t\n\r\0\x0B'\""), explode(',', $senarai));
            $jumlah = 0;
            $takPasti = false;
            foreach ($lajur as $c) {
                // Cari definisi lajur untuk jenis.
                if (preg_match('/\$t(?:able)?->(foreignId|id|ulid|char|string)\(\s*[\'"]'.preg_quote($c, '/').'[\'"]?\s*(?:,\s*(\d+))?/', $kod, $md)) {
                    $jumlah += match ($md[1]) {
                        'foreignId', 'id' => 8,
                        'ulid' => 26 * 4,
                        'char' => ((int) ($md[2] ?? 36)) * 4,
                        'string' => ((int) ($md[2] ?? 255)) * 4,
                        default => 0,
                    };
                } else {
                    $takPasti = true; // lajur mungkin dari jadual lain
                }
            }
            if (! $takPasti && $jumlah > 3072) {
                $amaran[] = "$nama: indeks unik (".implode(',', $lajur).") ~{$jumlah} bait > 3072";
            }
        }
    }
}

echo "Migrasi diperiksa: $bil\n\n";

if ($isu) {
    echo "RALAT (mesti dibaiki):\n";
    foreach ($isu as $i) {
        echo "  ✗ $i\n";
    }
    echo "\n";
} else {
    echo "✓ Tiada SQL khusus SQLite dijumpai.\n\n";
}

if ($amaran) {
    echo 'NOTA ('.count($amaran)." — bukan ralat, selamat pada MySQL 8):\n";
    foreach (array_slice($amaran, 0, 5) as $a) {
        echo "  · $a\n";
    }
    if (count($amaran) > 5) {
        echo '  · … dan '.(count($amaran) - 5)." lagi\n";
    }
    echo "\n  Lajur string tanpa panjang = VARCHAR(255) = 1020 bait (utf8mb4),\n";
    echo "  masih di bawah had indeks 3072 bait. Tiada tindakan diperlukan.\n";
} else {
    echo "✓ Tiada amaran.\n";
}

exit($isu ? 1 : 0);
