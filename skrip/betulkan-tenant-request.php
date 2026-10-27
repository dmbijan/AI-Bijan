<?php

/**
 * BETULKAN dua kelas pepijat tenant dalam controller modul.
 *
 * ── (A) `$request->tenant_id` sentiasa null ──────────────────────────────
 * 27 controller membaca `$request->tenant_id` (182 rujukan). `Request` TIDAK
 * mempunyai atribut itu dan tiada middleware mengisinya — jadi nilainya
 * sentiasa `null`:
 *
 *   • Service bertaip `int $tenantId` → TypeError → HTTP 500
 *     (POS: `listRegisters(): Argument #1 must be of type int, null given`)
 *   • Service bertaip `?int` → `where tenant_id = null` → senarai kosong
 *
 * ── (B) `TenantContext` tanpa `use` ──────────────────────────────────────
 * ~45 controller guna `TenantContext::` tanpa `use App\Support\Tenancy\...;`
 * → PHP menyelesaikannya dalam namespace controller →
 *   "Class App\Modules\<X>\Http\Controllers\TenantContext does not exist".
 *
 * Ditemui 27 Okt 2026 semasa menulis ujian pengasingan tenant.
 *
 * ── Pembetulan ───────────────────────────────────────────────────────────
 * (A) Ganti dengan `$this->tenantId()` daripada `TenantContext` (keahlian
 *     pengguna, BUKAN input klien) — corak `ChartOfAccountsController` (P0-4).
 * (B) Tambah `use App\Support\Tenancy\TenantContext;` selepas `namespace`.
 *
 * ## Guna
 *   php skrip/betulkan-tenant-request.php            # laporan sahaja (dry run)
 *   php skrip/betulkan-tenant-request.php --tulis    # tulis perubahan
 */

$akar  = dirname(__DIR__);
$tulis = in_array('--tulis', $argv, true);

$fail = glob($akar . '/app/Modules/*/Http/Controllers/*.php') ?: [];

$keputusan = [];
$jumlahRujukan = 0;
$jumlahImport = 0;

foreach ($fail as $f) {
    $asal = file_get_contents($f);
    $kod = $asal;

    $rujukanRequest = preg_match_all('/\$(request|r)->tenant_id\b/', $kod);
    $gunaTenantContext = preg_match('/TenantContext::/', $kod) === 1;
    $adaImport = str_contains($kod, 'use App\Support\Tenancy\TenantContext;');
    $perluImport = $gunaTenantContext && ! $adaImport;

    if ($rujukanRequest === 0 && ! $perluImport) {
        continue;
    }

    // (A) Ganti rujukan `$request->tenant_id`
    if ($rujukanRequest > 0) {
        $kod = preg_replace('/\$(request|r)->tenant_id\b/', '$this->tenantId()', $kod);
    }

    // (B) Tambah import sekali sahaja
    if ($perluImport) {
        $kod = preg_replace(
            '/^namespace [^\n]+\n/m',
            "$0\nuse App\\Support\\Tenancy\\TenantContext;\n",
            $kod,
            1
        );
        $jumlahImport++;
    }

    // 3. Tambah pembantu `tenantId()` selepas `{` kelas — hanya sekali
    if (! preg_match('/function tenantId\(\)/', $kod)) {
        $pembantu = <<<'PHP'

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
PHP;

        $kod = preg_replace(
            '/^(class \w+(?: extends [\w\\\\\\\\]+)?(?: implements [^{]+)?\s*\{)\n/m',
            "$1\n" . $pembantu . "\n",
            $kod,
            1
        );
    }

    $jumlahRujukan += $rujukanRequest;
    $keputusan[] = [substr($f, strlen($akar) + 1), $rujukanRequest, $perluImport];

    if ($kod !== $asal) {
        // Sahkan sintaks SEBELUM menulis
        $sementara = tempnam(sys_get_temp_dir(), 'tenant') . '.php';
        file_put_contents($sementara, $kod);
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($sementara) . ' 2>&1', $keluaran, $kodKeluar);
        @unlink($sementara);

        if ($kodKeluar !== 0) {
            fwrite(STDERR, "❌ Sintaks rosak selepas ubah — TIDAK ditulis: {$f}\n" . implode("\n", $keluaran) . "\n");
            $keputusan[array_key_last($keputusan)][2] = false;

            continue;
        }

        if ($tulis) {
            file_put_contents($f, $kod);
        }
    }
}

echo str_repeat('=', 78) . PHP_EOL;
echo ' BETULKAN tenant controller — ' . ($tulis ? 'TULIS' : 'DRY RUN (tiada fail diubah)') . PHP_EOL;
echo str_repeat('=', 78) . PHP_EOL;

foreach ($keputusan as [$nama, $bilangan, $import]) {
    printf(" %-58s %2d rujukan %s\n", $nama, $bilangan, $import ? '+import' : '');
}

printf(
    "\n %d fail diubah · %d rujukan \$request->tenant_id · %d import ditambah\n",
    count($keputusan),
    $jumlahRujukan,
    $jumlahImport
);

if (! $tulis) {
    echo "\n Jalankan semula dengan --tulis untuk menyimpan.\n";
}

// Bekas: tiada rujukan tinggal dalam KOD (komen/docblock dikecualikan)
$tinggalRequest = 0;
$tinggalImport = 0;

foreach ($fail as $f) {
    $penuh = file_get_contents($f);
    $kandungan = preg_replace(['#/\*.*?\*/#s', '#//[^\n]*#'], '', $penuh);

    if (preg_match('/\$(request|r)->tenant_id\b/', $kandungan)) {
        $tinggalRequest++;
    }

    if (preg_match('/TenantContext::/', $kandungan)
        && ! str_contains($penuh, 'use App\Support\Tenancy\TenantContext;')) {
        $tinggalImport++;
    }
}

echo "\n Baki \$request->tenant_id ......: {$tinggalRequest} fail";
echo "\n Baki TenantContext tanpa import: {$tinggalImport} fail\n";

exit(($tinggalRequest + $tinggalImport) === 0 ? 0 : 1);
