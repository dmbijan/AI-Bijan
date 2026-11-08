<?php

/**
 * Buang BOM (UTF-8 BOM) daripada fail PHP — `Set-Content -Encoding UTF8` di
 * PowerShell menambahkan BOM yang menyebabkan ralat:
 * "Namespace declaration statement has to be the very first statement".
 *
 * Jalankan: php _fix_bom.php [dir]
 */

$root = $argv[1] ?? __DIR__;
$fixed = [];
$checked = 0;

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($it as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $checked++;
    $path = $file->getPathname();
    $content = file_get_contents($path);

    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        file_put_contents($path, substr($content, 3));
        $fixed[] = str_replace($root.'/', '', $path);
    }
}

echo "Disemak: {$checked} fail PHP\n";
echo 'BOM dibuang: '.count($fixed)."\n";

foreach ($fixed as $f) {
    echo "  - {$f}\n";
}
