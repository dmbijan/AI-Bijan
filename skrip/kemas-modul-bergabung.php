<?php

/**
 * kemas-modul-bergabung.php — selaraskan `$modul` ujian pengasingan BERGABUNG
 * dengan modul yang benar-benar diimportnya (T-111, 28 Okt 2026).
 *
 * Sebelum ini `WorkflowVisionSupportIsolationTest` menetapkan `$modul =
 * 'WorkflowStudio'` sahaja walaupun ia menguji Vision dan LiveSupport; audit
 * `skrip/audit-tenant.php` pula memberi kredit kepada modul-modul itu melalui
 * semakan import pada KORPUS gabungan (bukan fail). Skrip ini menjadikan
 * pengakuan itu eksplisit.
 *
 * Guna:
 *   php skrip/kemas-modul-bergabung.php            # lapor sahaja
 *   php skrip/kemas-modul-bergabung.php --tulis    # tulis perubahan
 */

$root = dirname(__DIR__);
$tulis = in_array('--tulis', $argv ?? [], true);
$dir = $root . '/tests/Feature/Pengasingan';

$diubah = 0;

foreach (glob($dir . '/*.php') as $f) {
    $isi = (string) file_get_contents($f);

    if (preg_match("/protected\s+string\s+\\\$modul\s*=\s*'([^']*)';/", $isi, $m) !== 1) {
        continue;
    }

    $semasa = $m[1];
    $import = [];
    if (preg_match_all('/use\s+App\\\\Modules\\\\([A-Za-z]+)\\\\/', $isi, $im) > 0) {
        $import = array_values(array_unique($im[1]));
    }

    // Hanya fail BERGABUNG (>1 import) diubah. Fail satu modul dibiarkan.
    if (count($import) < 2) {
        continue;
    }

    $senarai = array_values(array_unique(array_merge([$semasa], $import)));
    sort($senarai);
    $baru = implode(',', $senarai);

    if ($baru === $semasa) {
        continue;
    }

    $ganti = "protected string \$modul = '{$baru}';";
    $keluaran = str_replace($m[0], $ganti, $isi);

    echo basename($f) . "\n  lama: {$semasa}\n  baru: {$baru}\n";

    if ($tulis) {
        file_put_contents($f, $keluaran);
        $diubah++;
    }
}

echo $tulis
    ? "\nDitulis: {$diubah} fail.\n"
    : "\n(laporan sahaja — guna --tulis)\n";
