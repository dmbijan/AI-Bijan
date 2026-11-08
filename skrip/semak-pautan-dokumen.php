<?php

/**
 * Semak pautan dokumen rosak di seluruh repositori.
 *
 * Mengesan:
 *   1. Pautan relatif ke fail yang tidak wujud
 *   2. Pautan ke dokumen yang telah diarkibkan
 *   3. Rujukan ke nama fail lama
 *
 * Jalankan: php skrip/semak-pautan-dokumen.php [--tulis]  (--tulis = betulkan automatik)
 */

$root = dirname(__DIR__);
$tulis = in_array('--tulis', $argv, true);

$fail = [];
$skrip = [];

foreach (['docs', 'skrip', '.github', 'tests', 'app'] as $dir) {
    $p = $root.'/'.$dir;

    if (! is_dir($p)) {
        continue;
    }

    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($p, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }

        $ext = strtolower($file->getExtension());

        if (! in_array($ext, ['md', 'php'], true)) {
            continue;
        }

        $c = file_get_contents($file->getPathname());

        // Pautan markdown: [teks](fail.md)
        if (preg_match_all('/\]\(([^)#\s]+\.md)(?:#[^)]*)?\)/i', $c, $m)) {
            foreach ($m[1] as $link) {
                if (preg_match('#^(https?:)?//#i', $link)) {
                    continue;
                }

                $sasaran = dirname($file->getPathname()).'/'.urldecode($link);

                if (! file_exists($sasaran)) {
                    $fail[] = [
                        'dari' => str_replace($root.'/', '', $file->getPathname()),
                        'link' => $link,
                    ];
                }
            }
        }
    }
}

echo "=== PAUTAN ROSAK (".count($fail).") ===\n";

$ikut = [];

foreach ($fail as $f) {
    echo "  {$f['dari']}\n    → {$f['link']}\n";
    $ikut[$f['dari']][] = $f['link'];
}

if ($fail === []) {
    echo "  (tiada — semua pautan sah ✓)\n";
}

// Nama fail lama yang perlu_clean.
echo "\n=== NAMA FAIL LAMA ===\n";

$lama = [
    'AI-BIJAN-MASTER-BLUEPRINT.md' => 'BLUEPRINT-INDUK.md',
    'AI-BIJAN-MASTER-BLUEPRINT-v8.3' => 'BLUEPRINT-INDUK.md',
];

$jumpa = [];

foreach ($lama as $lamaNama => $baru) {
    foreach (glob($root.'/docs/*.md') ?: [] as $f) {
        $c = file_get_contents($f);

        if (str_contains($c, $lamaNama)) {
            $jumpa[] = basename($f).' → masih rujuk '.$lamaNama;
        }
    }
}

if ($jumpa === []) {
    echo "  (tiada ✓)\n";
} else {
    foreach ($jumpa as $j) {
        echo "  {$j}\n";
    }
}

if ($tulis && $jumpa !== []) {
    foreach (glob($root.'/docs/*.md') ?: [] as $f) {
        $c = file_get_contents($f);

        foreach ($lama as $lamaNama => $baru) {
            $c = str_replace($lamaNama, $baru, $c);
        }

        file_put_contents($f, $c);
    }

    echo "\n→ Nama fail lama dikemas kini.\n";
}

exit($fail === [] ? 0 : 1);
