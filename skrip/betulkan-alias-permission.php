<?php

/**
 * BETULKAN alias middleware `permission:` → `perm:` dalam fail laluan modul.
 *
 * ── Mengapa ──────────────────────────────────────────────────────────────
 * `bootstrap/app.php` HANYA mendaftarkan alias `perm`
 * (`'perm' => RequirePermission::class`). Tiada alias `permission`.
 * 88 baris dalam 4 modul (CRM 27, DocumentAI 8, HRMS 18, Projects 35) guna
 * `->middleware('permission:x.y')`, jadi setiap permintaan ke laluan itu gagal:
 *
 *     BindingResolutionException: Target class [permission] does not exist
 *
 * Ini bermakna SEMUA laluan CRM, DocumentAI, HRMS dan Projects rosak —
 * termasuk pemeriksaan kebenaran (ia tidak pernah dijalankan).
 *
 * Nota projek sudah menulis: "Alias middleware ialah `perm:`, BUKAN
 * `permission:`" (docs/TODO.md) — tetapi laluan ini terlepas.
 *
 * Ditemui 27 Okt 2026 oleh `ProjectsTenantIsolationTest`.
 *
 * ## Guna
 *   php skrip/betulkan-alias-permission.php            # dry run
 *   php skrip/betulkan-alias-permission.php --tulis    # tulis
 */

$akar  = dirname(__DIR__);
$tulis = in_array('--tulis', $argv, true);

$fail = [];

foreach (['routes', 'app/Modules'] as $d) {
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($akar . '/' . $d, FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $f) {
        if ($f->isFile() && $f->getFilename() === 'api.php') {
            $fail[] = $f->getPathname();
        }
    }
}

$keputusan = [];
$jumlah = 0;

foreach ($fail as $f) {
    $asal = file_get_contents($f);

    if (substr_count($asal, "'permission:") === 0) {
        continue;
    }

    $bil = substr_count($asal, "'permission:");
    $kod = str_replace("'permission:", "'perm:", $asal);

    $keluaran = [];
    $kodKeluar = 0;
    $sementara = tempnam(sys_get_temp_dir(), 'alias') . '.php';
    file_put_contents($sementara, $kod);
    exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($sementara) . ' 2>&1', $keluaran, $kodKeluar);
    @unlink($sementara);

    if ($kodKeluar !== 0) {
        fwrite(STDERR, "❌ Sintaks rosak — TIDAK ditulis: {$f}\n");

        continue;
    }

    if ($tulis) {
        file_put_contents($f, $kod);
    }

    $jumlah += $bil;
    $keputusan[] = [str_replace([$akar . '\\', $akar . '/'], '', $f), $bil];
}

echo str_repeat('=', 66) . PHP_EOL;
echo ' ALIAS permission: → perm: — ' . ($tulis ? 'TULIS' : 'DRY RUN') . PHP_EOL;
echo str_repeat('=', 66) . PHP_EOL;

foreach ($keputusan as [$nama, $bil]) {
    printf(" %-46s %3d baris\n", $nama, $bil);
}

printf("\n %d fail · %d rujukan\n", count($keputusan), $jumlah);

$tinggal = 0;
foreach ($fail as $f) {
    $tinggal += substr_count(file_get_contents($f), "'permission:");
}

echo "\n Baki 'permission: dalam fail laluan: {$tinggal}\n";

exit($tinggal === 0 ? 0 : 1);
