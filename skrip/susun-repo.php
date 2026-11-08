<?php

/**
 * Susun repositori AI BIJAN.
 *
 * Bahagian 1 — Arkibkan blueprint duplikat:
 *   Fail IDENTIK di `docs/` dan `docs/archive/blueprint-lama/` dipindahkan
 *   ke arkib supaya setiap blueprint hanya ada satu salinan.
 *
 * Bahagian 2 — Kemas kini indeks `docs/README.md`:
 *   Dokumen dit organisersmengikut kategori dengan penunjuk ke fail sebenar.
 *
 * Jalankan:
 *   php skrip/susun-repo.php          # pratonton (tiada perubahan)
 *   php skrip/susun-repo.php --tulis  # lakukan perubahan
 */

$root = dirname(__DIR__);
$docs = $root.'/docs';
$arkib = $docs.'/archive/blueprint-lama';
$tulis = in_array('--tulis', $argv, true);

if (! is_dir($arkib)) {
    mkdir($arkib, 0777, true);
}

// ===============================================================
// BAHAGIAN 1 — ARKIBKAN DUPLIKAT
// ===============================================================

$pindah = [];
$kekal = [];

foreach (glob($docs.'/*.md') ?: [] as $fail) {
    $nama = basename($fail);
    $sasaran = $arkib.'/'.$nama;

    if (! file_exists($sasaran)) {
        $kekal[] = $nama;

        continue;
    }

    if (md5_file($fail) === md5_file($sasaran)) {
        $pindah[$nama] = $sasaran;
    } else {
        $kekal[] = $nama;
    }
}

echo "=== BAHAGIAN 1 — ARKIBKAN DUPLIKAT ===\n";
printf("  Fail di docs/           : %d\n", count(glob($docs.'/*.md') ?: []));
printf("  Dupikat (identik)       : %d\n", count($pindah));
printf("  Kekal (t.unique/berbeza) : %d\n", count($kekal));

if ($tulis && $pindah !== []) {
    foreach ($pindah as $nama => $sasaran) {
        rename($docs.'/'.$nama, $sasaran);
    }
    echo "  → ".count($pindah)." fail dipindahkan ke archive/blueprint-lama/\n";
} elseif ($pindah !== []) {
    echo "  → jalankan dengan --tulis untuk memindahkan\n";
}

// ===============================================================
// BAHAGIAN 2 — KEMAS KINI INDEKS
// ===============================================================

$kategori = [
    'Mula di Sini' => [
        'BACA-SAYA.md',
        'getting-started.md',
        'cara-pasang.md',
        'README.md',
    ],
    'Rujukan Tunggal' => [
        'AUDIT-BASELINE.md',
        'TODO.md',
        'CHANGELOG.md',
        'SENARAI-MODUL.md',
    ],
    'Strategi & Perancangan' => [
        'AI-BIJAN-MASTER-BLUEPRINT.md',
        'BLUEPRINT-SISTEM-LENGKAP.md',
        'SISTEM-AI-BIJAN.md',
        'BUSINESS-GROWTH-ENGINE.md',
        'PETA-PESAING-34-MODUL.md',
        'ANALISIS-KOMPETITIF.md',
        'competitive-analysis-report.md',
        'ai-bijan-vs-world-comparison.md',
        'RESELLER-KOMISEN-BERULANG.md',
        'CADANGAN-IMPAK-TINGGI.md',
    ],
    'Audit' => [
        'AI-BIJAN-AUDIT-INDUK-LENGKAP.md',
        'AUDIT-DOKUMEN.md',
        'AUDIT-TENANT-ISOLATION.md',
    ],
    'Integrasi & Modul' => [
        'INTEGRASI-SUMBER-TERBUKA.md',
        'INTEGRASI-BPOS.md',
        'INTEGRASI-4-LAMAN-WEB.md',
        'INTEGRASI-VOYNANN.md',
        'MODUL-AI-INTEGRASI.md',
        'OPENBOT-INTEGRATION.md',
        'REVERSE-ENGINEERING-200-APPS-50-OSS.md',
    ],
    'Operasional' => [
        'PASANG.md',
        'AGENTS.md',
        'CLAUDE.md',
        'THIRD-PARTY-NOTICES.md',
        'MODEL-HASIL-TOKEN.md',
        'KATALOG-MODUL-AI-BIJAN.md',
    ],
];

echo "\n=== BAHAGIAN 2 — INDEKS ===\n";

$ada = array_map('basename', glob($docs.'/*.md') ?: []);
sort($ada);

$disenaraikan = [];

foreach ($kategori as $tajuk => $failSenarai) {
    $adaDalam = array_values(array_intersect($failSenarai, $ada));

    if ($adaDalam === []) {
        continue;
    }

    echo "  {$tajuk}:\n";

    foreach ($adaDalam as $f) {
        echo "    - {$f}\n";
    }

    $disenaraikan = array_merge($disenaraikan, $adaDalam);
}

// Fail tidak disenaraikan → letak di "Lain-lain".
$lain = array_values(array_diff($ada, $disenaraikan));

if ($lain !== []) {
    echo "  Lain-lain:\n";

    foreach ($lain as $f) {
        echo "    - {$f}\n";
    }
}

echo "\n  Total didokumentasikan: ".count($disenaraikan)." / ".count($ada)."\n";

if (! $tulis) {
    echo "\n(Jalankan dengan --tulis untuk arkib; indeks ditulis secara manual.)\n";
}
