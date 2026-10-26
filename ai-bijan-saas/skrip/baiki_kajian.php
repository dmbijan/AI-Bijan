<?php
/**
 * Skrip pembaikan fail KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md
 * Buang aksara CJK/Arabic/Korean yang tersusukan, kemudian修复 baris tertentu.
 * Usage: php skrip/baiki_kajian.php
 */

$f = __DIR__ . '/../docs/KAJIAN-FINDS-PROSPECT-BLAST-CLOSE.md';

if (!is_file($f)) {
    fwrite(STDERR, "FAIL: fail tidak dijumpai: $f\n");
    exit(1);
}

// Sandaran
$backup = $f . '.bak.' . date('Ymd_His');
if (!copy($f, $backup)) {
    fwrite(STDERR, "FAIL: sandaran gagal\n");
    exit(1);
}
echo "Sandaran: $backup\n";

$c = file_get_contents($f);
$before = mb_strlen($c, 'UTF-8');

// 1) Buang semua aksara CJK / Arabic / Korean yang tersusukan
$c = preg_replace('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}\x{0600}-\x{06ff}\x{0750}-\x{077f}]+/u', '', $c);

// 2) Baiki frasaspecifically yang rosak (selepas langkah 1, CJK sudah hilang)
$fixes = [
    'Senarai senarai  (skip list)'                          => 'Senarai pengecualian (skip list)',
    'Senarai senarai (skip list)'                           => 'Senarai pengecualian (skip list)',
    'Tiada pemprosesan (queue)'                             => 'Tiada pemprosesan baris gilir (queue)',
    'Wajib —PaPDPA'                                         => 'Wajib — PDPA',
    'Wajib — PaPDPA'                                        => 'Wajib — PDPA',
    'Cadangan: yang complies (compliant).'                  => 'Cadangan: sumber yang patuh (compliant).',
    'Cadangan: yang complies'                               => 'Cadangan: sumber yang patuh',
    'paip yang tiada paip'                                  => 'paip yang tiada hujung',
    'MyData, Malaysia'                                      => 'MyData, Malaysia',
    'satu dua sahaja.'                                      => 'satu dua sahaja.',
];

foreach ($fixes as $from => $to) {
    $c = str_replace($from, $to, $c);
}

// 3) Baiki ayat panjang yang rosak (paip placeholder)
$bad = 'paip精彩';
$c = preg_replace(
    '/### Jurang 2: Tiada penghantaran — paip.*?(?=\n\n### Jurang 3)/s',
    "### Jurang 2: Tiada penghantaran — paip paip paip\n\nTiga titik henti `return true` dalam `WorkflowService` (`executeSendEmail`,\n`executeSendSms`, `executeSendWhatsapp`) bermaksud mesej **tidak pernah** keluar.\nSistem hanya merekod kemahiran, bukan menghantar.",
    $c
);

// 4) Tanda 'egui' / '死活' yang tinggal (ke冲洗 sheet)
$c = str_replace('egui', '', $c);
$c = preg_replace('/死活/u', '', $c);

// 5) Bersihkan lebih-than yang berulang (& 3+ → &)
$c = preg_replace('/-{3,}/', '---', $c);

// 6) Bersihkan '??' dan '?'   yang tidak 자연 (ganti dengan kata sebenar)
$c = str_replace('??', '—', $c);

$after = mb_strlen($c, 'UTF-8');
file_put_contents($f, $c);

// 7) Verify
$check = file_get_contents($f);
$bad_count = preg_match_all('/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{3040}-\x{30ff}\x{ac00}-\x{d7af}\x{0600}-\x{06ff}\x{0750}-\x{077f}]/u', $check);

echo "Panjang: {$before} → {$after} aksara\n";
echo "Aksara CJK/Arabic selepas pembaikan: {$bad_count}\n";

if ($bad_count > 0) {
    fwrite(STDERR, "FAIL: masih ada aksara rosak\n");
    exit(2);
}

echo "OK — fail bersih.\n";
