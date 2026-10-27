<?php

/**
 * Ujian pengasingan HANYA — tapis kelas DALAM folder ini, bukan seluruh suite.
 *
 * Sebab: `--filter=<NamaKelas>` melalui shell/PowerShell tidak selamat di
 * Windows (argumen boleh pecah), jadi lebih dipercayai menghantar fail kelas
 * secara terus kepada PHPUnit. Cth kegagalan senyap: pepijat dalam kelas
 * LAIN dilaporkan seolah-olah ujian pengasingan sendiri gagal.
 *
 *   php skrip/ujian-pengasingan.php                 # semua kelas
 *   php skrip/ujian-pengasingan.php POSTenantIsolationTest
 */

require __DIR__ . '/../vendor/autoload.php';

$akar = str_replace('\\', '/', dirname(__DIR__));
$dir  = $akar . '/tests/Feature/Pengasingan';

$kelas = [];

foreach (glob($dir . '/*.php') ?: [] as $f) {
    $nama = basename($f, '.php');

    if ($nama === '') {
        continue;
    }

    if (isset($argv[1]) && $argv[1] !== '' && $nama !== $argv[1]) {
        continue;
    }

    $kelas[] = $f;
}

if ($kelas === []) {
    fwrite(STDERR, "Tiada kelas ujian dijumpai dalam {$dir}" . PHP_EOL);
    exit(1);
}

echo 'Ujian pengasingan: ' . count($kelas) . ' kelas' . PHP_EOL;

$arahan = [
    '"' . PHP_BINARY . '"',
    '"' . $akar . '/vendor/phpunit/phpunit/phpunit"',
    '-c',
    '"' . $akar . '/phpunit.xml"',
    '--colors=never',
];

foreach ($kelas as $f) {
    $arahan[] = '"' . str_replace('/', '\\', $f) . '"';
}

passthru(implode(' ', $arahan), $kod);

exit($kod);
