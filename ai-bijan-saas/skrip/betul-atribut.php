<?php

/**
 * Betulkan atribut `#[Test]` yang rosak (backslash berganda) dan
 * jadikan formatnya kemas.
 */

$fail = __DIR__.'/../tests/Feature/PayrollStatutoryTest.php';
$isi = file_get_contents($fail);

// ⚠️ PHP 8.2 TIDAK membenarkan backslash hadapan dalam atribut
// (`#[\PHPUnit\...]` = ralat sintaks). Guna import + nama pendek.
$isi = str_replace('\\\\', '\\', $isi);
$isi = preg_replace(
    '/^[ \t]*#\[\\\\?PHPUnit\\\\Framework\\\\Attributes\\\\Test\]/m',
    '    #[Test]',
    $isi
);

// Tambah `use` selepas declaration namespace jika belum ada.
if (! str_contains($isi, 'use PHPUnit\Framework\Attributes\Test;')) {
    $isi = preg_replace(
        '/^(namespace [^;]+;\n)/m',
        "$1\nuse PHPUnit\\Framework\\Attributes\\Test;\n",
        $isi,
        1
    );
}

file_put_contents($fail, $isi);

$n = preg_match_all('/#\[Test\]/', $isi);

echo "Atribut #[Test]: {$n}".PHP_EOL;

foreach (explode("\n", $isi) as $i => $baris) {
    if (str_contains($baris, 'use PHPUnit') || str_contains($baris, '#[Test]')) {
        echo ($i + 1).': '.rtrim($baris).PHP_EOL;
    }
}
