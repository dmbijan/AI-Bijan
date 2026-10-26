<?php

$pangkalan = __DIR__.'/..';

echo 'pangkalan = '.realpath($pangkalan).PHP_EOL;

$corak = $pangkalan.'/app/Modules/*/Models/JournalEntry.php';
echo 'corak = '.$corak.PHP_EOL;
echo 'glob = '.json_encode(glob($corak)).PHP_EOL;

echo 'modul dir = '.json_encode(array_slice(glob($pangkalan.'/app/Modules/*', GLOB_ONLYDIR), 0, 3)).PHP_EOL;
echo 'modules/Finance/Models = '.json_encode(glob($pangkalan.'/app/Modules/Finance/Models/*.php')).PHP_EOL;
