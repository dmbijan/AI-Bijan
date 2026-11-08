<?php

/**
 * Semak rujukan silang antara modul AI BIJAN.
 *
 * Mengenal pasti rujukan Fully-Qualified (`\App\Modules\X\`) dan import
 * (`use App\Modules\X\`) dari modul lain.
 *
 * Jalankan: php _semak_rujukan.php
 */

$base = __DIR__.'/app/Modules';

$mods = array_values(array_filter(scandir($base), fn ($d) => $d[0] !== '.' && is_dir($base.'/'.$d)));
sort($mods);

$rujukan = [];
$pemakai = [];

foreach ($mods as $mod) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($base.'/'.$mod, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $c = file_get_contents($file->getPathname());

        // Cari `\App\Modules\X\` di dalam badan (FQQN).
        if (preg_match_all('/\\\\App\\\\Modules\\\\(\w+)\\\\/', $c, $m)) {
            foreach ($m[1] as $tuju) {
                if ($tuju !== $mod && in_array($tuju, $mods, true)) {
                    $rujukan[$mod][$tuju] = ($rujukan[$mod][$tuju] ?? 0) + 1;
                    $pemakai[$tuju][$mod] = true;
                }
            }
        }

        // Import `use App\Modules\X\...` — rujukan silang juga.
        if (preg_match_all('/^\s*use\s+App\\\\Modules\\\\(\w+)\\\\/m', $c, $m)) {
            foreach ($m[1] as $tuju) {
                if ($tuju !== $mod && in_array($tuju, $mods, true)) {
                    $rujukan[$mod][$tuju] = ($rujukan[$mod][$tuju] ?? 0) + 1;
                    $pemakai[$tuju][$mod] = true;
                }
            }
        }
    }
}

echo "=== RUJUKAN SILANG (modul -> modul) ===\n";

$jumlah = 0;
foreach ($rujukan as $dari => $ke) {
    foreach ($ke as $keudi => $b) {
        printf("  %-18s -> %-18s (%d)\n", $dari, $keudi, $b);
        $jumlah += $b;
    }
}

if ($jumlah === 0) {
    echo "  (tiada)\n";
}

echo "\nJUMLAH RUJUKAN: {$jumlah}\n";

// Modul tanpa rujukan masuk.
$terpakai = array_keys($pemakai);
$sendiri = array_values(array_filter($mods, fn ($m) => ! in_array($m, $terpakai, true)));

echo "\n=== MODUL TANPA RUJUKAN MASUK (".count($sendiri).") ===\n";

foreach ($sendiri as $m) {
    $jum = iterator_count(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base.'/'.$m, FilesystemIterator::SKIP_DOTS)));
    printf("  %-20s %3d fail\n", $m, $jum);
}

echo "\n=== MODUL BAHARU 2026: STATUS PENGGUNAAN ===\n";

$baru = [
    'OpenGenAI', 'VoiceStudio', 'OpenWhispr', 'OpenSEO', 'Workspace',
    'WorkManagement', 'Scheduling', 'SharedInbox', 'ScreenRecorder', 'VideoStudio',
    'POSBpos', 'OmniOrder', 'SocialBridge', 'ContentForge', 'WhatsAppSuite',
];

foreach ($baru as $m) {
    $keluar = $rujukan[$m] ?? [];
    $masuk = $pemakai[$m] ?? [];

    printf(
        "  %-16s keluar:%-2d masuk:%-2d %s\n",
        $m,
        count($keluar),
        count($masuk),
        (count($keluar) + count($masuk)) > 0 ? '✓ disambungkan' : '— belum'
    );
}
