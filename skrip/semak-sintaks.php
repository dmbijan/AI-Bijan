<?php

/**
 * Semak sintaks SEMUA fail PHP projek (app, routes, config, database, skrip, tests).
 *
 * Ditambah 27 Okt 2026 selepas `php -l` dalam gelung PowerShell memberi
 * POSITIF PALSU (PowerShell memecahkan argumen). Skrip ini berjalan dalam PHP
 * sepenuhnya — keputusannya boleh dipercayai.
 *
 *   php skrip/semak-sintaks.php
 */

$akar = dirname(__DIR__);
$akar = str_replace('\\', '/', $akar);

$folder = ['app', 'routes', 'config', 'database', 'skrip', 'tests', 'bootstrap'];
$gagal = [];
$jumlah = 0;

foreach ($folder as $d) {
    $dir = $akar . '/' . $d;

    if (! is_dir($dir)) {
        continue;
    }

    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

    foreach ($it as $f) {
        if (! $f->isFile() || $f->getExtension() !== 'php') {
            continue;
        }

        $jumlah++;

        $output = [];
        $kod = 0;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($f->getPathname()) . ' 2>&1', $output, $kod);

        if ($kod !== 0) {
            $gagal[] = str_replace($akar . '/', '', str_replace('\\', '/', $f->getPathname()))
                . ' → ' . implode(' ', $output);
        }
    }
}

echo str_repeat('=', 66) . PHP_EOL;
printf(" SEMAK SINTAKS — %d fail PHP%s", $jumlah, PHP_EOL);
echo str_repeat('=', 66) . PHP_EOL;

if ($gagal === []) {
    echo ' ✅ Tiada ralat sintaks.' . PHP_EOL;

    exit(0);
}

echo ' ❌ ' . count($gagal) . ' fail gagal:' . PHP_EOL;
foreach ($gagal as $g) {
    echo '   • ' . $g . PHP_EOL;
}

exit(1);
