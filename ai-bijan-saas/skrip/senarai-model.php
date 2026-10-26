<?php

/** Senaraikan model utama setiap modul (untuk penjanaan resource). */

$asas = __DIR__.'/../app/Modules';

foreach (glob($asas.'/*', GLOB_ONLYDIR) as $modul) {
    $nama = basename($modul);
    $models = glob($modul.'/Models/*.php') ?: [];
    $senarai = array_map(fn ($f) => basename($f, '.php'), $models);
    sort($senarai);
    echo str_pad($nama, 20).' : '.implode(', ', $senarai).PHP_EOL;
}
