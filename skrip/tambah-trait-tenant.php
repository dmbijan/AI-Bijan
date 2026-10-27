<?php

/**
 * tambah-trait-tenant.php — tambah trait `BelongsToTenant` pada model yang
 * ada kolum `tenant_id` tetapi tiada pengasingan tenant.
 *
 * Idempoten: model yang sudah ada trait dilangkau.
 *
 * Guna: php skrip/tambah-trait-tenant.php [--dry-run]
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$dryRun = in_array('--dry-run', $argv ?? [], true);

$sasaran = [
    'Milestone',
    'ProjectDocument',
    'ProjectActivity',
    'AssetDocument',
    'AssetTransfer',
    'MaintenanceRequest',
    'DocumentShare',
    'DocumentTemplate',
    'DocumentVersion',
    'DocumentExtractionHistory',
];

$diubah = 0;
$dilangkau = 0;
$gagal = 0;

foreach ($sasaran as $nama) {
    // Cari fail model
    $fail = null;
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path('Modules'), FilesystemIterator::SKIP_DOTS)
    );

    foreach ($it as $f) {
        if ($f->isFile() && $f->getFilename() === $nama . '.php'
            && str_contains(str_replace('\\', '/', $f->getPathname()), '/Models/')) {
            $fail = $f->getPathname();
            break;
        }
    }

    if ($fail === null) {
        echo "⚠️  {$nama}: fail tidak dijumpai\n";
        $gagal++;
        continue;
    }

    $kod = file_get_contents($fail);

    if (str_contains($kod, 'BelongsToTenant')) {
        echo "⏭️  {$nama}: sudah ada trait\n";
        $dilangkau++;
        continue;
    }

    $asal = $kod;
    $beza = [];

    // 1. Tambah `use` import selepas namespace
    if (! str_contains($kod, 'use App\Models\Concerns\BelongsToTenant;')) {
        $kod = preg_replace(
            '/^(namespace [^;]+;\s*\n)/m',
            "$1\nuse App\\Models\\Concerns\\BelongsToTenant;",
            $kod,
            1
        );
        $beza[] = 'import';
    }

    // 2. Tambah trait dalam kelas
    if (preg_match('/\{\s*\n(\s*)use HasFactory;/', $kod)) {
        // Kes: `use HasFactory;` sudah ada
        $kod = preg_replace(
            '/(\{\s*\n\s*)use HasFactory;/',
            '$1use BelongsToTenant, HasFactory;',
            $kod,
            1
        );
        $beza[] = 'trait (gabung HasFactory)';
    } elseif (preg_match('/class \w+ extends Model\s*\n\{\s*\n/', $kod, $m)) {
        // Kes: tiada trait sama sekali — tambah blok use
        $kod = preg_replace(
            '/(class \w+ extends Model\s*\n\{\s*\n)/',
            "$1    use BelongsToTenant;\n\n",
            $kod,
            1
        );
        $beza[] = 'trait (blok baharu)';
    } else {
        echo "❌ {$nama}: corak kelas tidak dikenali — semak manual\n";
        $gagal++;
        continue;
    }

    if ($kod === $asal) {
        echo "❌ {$nama}: tiada perubahan dibuat\n";
        $gagal++;
        continue;
    }

    if ($dryRun) {
        echo "🔍 {$nama}: AKAN diubah (" . implode(', ', $beza) . ")\n";
        $diubah++;
        continue;
    }

    file_put_contents($fail, $kod);
    echo "✅ {$nama}: " . implode(', ', $beza) . "\n";
    $diubah++;
}

echo PHP_EOL . str_repeat('-', 50) . PHP_EOL;
echo 'Diubah   : ' . $diubah . PHP_EOL;
echo 'Dilangkau: ' . $dilangkau . PHP_EOL;
echo 'Gagal    : ' . $gagal . PHP_EOL;

if ($dryRun) {
    echo PHP_EOL . '(mod dry-run — tiada fail diubah)' . PHP_EOL;
}

exit($gagal === 0 ? 0 : 1);
