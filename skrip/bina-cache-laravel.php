<?php

/**
 * Utiliti dalaman: bina semula `bootstrap/cache/packages.php` dan `services.php`
 * tanpa bergantung pada `is_writable()` — sandbox menyekat semakan tersebut
 * walaupun tulisan fail sebenar berjaya.
 *
 * Jalankan dari akar projek: php skrip/bina-cache-laravel.php
 */

$root = dirname(__DIR__);

require $root.'/vendor/autoload.php';

$cacheDir = $root.'/bootstrap/cache';

if (! is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// ---- packages.php ----
// Format Laravel 12 PackageManifest: setiap pakej mesti ada kunci
// 'providers' (array) supaya `PackageManifest::providers()` berfungsi.
$installed = json_decode(file_get_contents($root.'/vendor/composer/installed.json'), true);
$packages = $installed['packages'] ?? $installed;

$manifest = [];

foreach ($packages as $package) {
    if (! isset($package['name'])) {
        continue;
    }

    $manifest[$package['name']] = [
        'providers' => $package['extra']['laravel']['providers'] ?? [],
        'aliases' => $package['extra']['laravel']['aliases'] ?? [],
        'dont-discover' => $package['extra']['laravel']['dont-discover'] ?? [],
    ];
}

file_put_contents(
    $cacheDir.'/packages.php',
    '<?php return '.var_export($manifest, true).';'
);

$bilanganProvider = 0;
foreach ($manifest as $p) {
    $bilanganProvider += count($p['providers']);
}

echo 'packages.php: '.count($manifest)." packages, {$bilanganProvider} providers\n";

// ---- services.php ----
// Format Laravel 12: ['providers' => [...], 'eager' => [...], 'deferred' => [...], 'when' => [...]]
// Senarai penuh = ServiceProvider::defaultProviders() + bootstrap/providers.php
//                    + provider pakej (dari packages.php)
$bootstrapProviders = require $root.'/bootstrap/providers.php';

$defaultProviders = Illuminate\Support\ServiceProvider::defaultProviders()->toArray();

// Provider pakej yang discovers oleh Laravel.
$packageManifest = require $cacheDir.'/packages.php';
$packageProviders = [];

foreach ($packageManifest as $package) {
    foreach ((array) ($package['providers'] ?? []) as $provider) {
        if (class_exists($provider)) {
            $packageProviders[] = $provider;
        }
    }
}

$providers = array_values(array_unique(array_merge($defaultProviders, $bootstrapProviders, $packageProviders)));

$manifest = [
    'providers' => $providers,
    'eager' => $providers,
    'deferred' => [],
    'when' => [],
];

file_put_contents(
    $cacheDir.'/services.php',
    '<?php return '.var_export($manifest, true).';'
);

echo 'services.php: '.count($manifest['providers']).' providers ('.count($defaultProviders).' default + '.count($bootstrapProviders).' bootstrap + '.count($packageProviders)." package)\n";
