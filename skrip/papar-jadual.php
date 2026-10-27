<?php

/**
 * Papar DDL SQLite untuk jadual (untuk semak NOT NULL / CHECK).
 *   php skrip/papar-jadual.php assets customers
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (array_slice($argv, 1) as $jadual) {
    $row = Illuminate\Support\Facades\DB::selectOne(
        "select sql from sqlite_master where type = 'table' and name = ?",
        [$jadual]
    );

    echo PHP_EOL . '── ' . $jadual . PHP_EOL;
    echo $row ? $row->sql : '  (tiada)';
    echo PHP_EOL;
}
