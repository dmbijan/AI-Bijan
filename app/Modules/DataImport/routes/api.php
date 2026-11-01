<?php

use App\Modules\DataImport\Http\Controllers\ImportController as Import;
use Illuminate\Support\Facades\Route;

// Prefix api/v1/imports didaftar oleh DataImportServiceProvider. Setiap jenis ada pagar kebenaran sendiri.
$jenis = [
    'products' => ['inventory.manage', 'inventory.manage'],
    'customers' => ['crm.manage', 'crm.manage'],
    'opening-stock' => ['inventory.manage', 'inventory.approve'], // [pratonton, sahkan]
];

foreach ($jenis as $url => [$lihat, $simpan]) {
    $type = str_replace('-', '_', $url);
    Route::middleware("perm:{$lihat}")->group(function () use ($url, $type) {
        Route::get("/{$url}/template", [Import::class, 'template'])->defaults('type', $type);
        Route::get("/{$url}/fields", [Import::class, 'fields'])->defaults('type', $type);
        Route::get("/{$url}", [Import::class, 'index'])->defaults('type', $type);
        Route::post("/{$url}", [Import::class, 'store'])->defaults('type', $type);
        Route::get("/{$url}/{id}", [Import::class, 'show'])->defaults('type', $type)->whereNumber('id');
        Route::post("/{$url}/{id}/cancel", [Import::class, 'cancel'])->defaults('type', $type)->whereNumber('id');
    });
    Route::middleware("perm:{$simpan}")->post("/{$url}/{id}/commit", [Import::class, 'commit'])->defaults('type', $type)->whereNumber('id');
}
