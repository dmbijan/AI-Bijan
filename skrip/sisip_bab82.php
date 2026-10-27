<?php
/**
 * Sisip BAB 82 ke blueprint induk + kemas kini versi header + indeks.
 * Usage: php skrip/sisip_bab82.php
 *
 * Catatan 27 Okt 2026: laluan blueprint sebelum ini menunjuk ke akar repositori
 * (`/../../AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md`) sedangkan fail itu berada dalam
 * `docs/`. Scripts ini tidak pernah boleh berjalan. Laluan kini betul.
 */

$blueprint = __DIR__ . '/../docs/AI-BIJAN-MASTER-BLUEPRINT-v8.3 (1).md';
$teks = __DIR__ . '/../docs/bab82_teks.md';

if (!is_file($blueprint)) {
    fwrite(STDERR, "FAIL: blueprint tidak dijumpai: $blueprint\n");
    exit(1);
}
if (!is_file($teks)) {
    fwrite(STDERR, "FAIL: teks BAB 82 tidak dijumpai: $teks\n");
    fwrite(STDERR, "Skrip ini tidak boleh dijalankan semula — BAB 82 sudah disisip dalam blueprint.\n");
    exit(1);
}

$backup = $blueprint . '.bak.' . date('Ymd_His');
copy($blueprint, $backup);
echo "Sandaran: $backup\n";

$c = file_get_contents($blueprint);

// Already inserted?
if (strpos($c, '## BAB 82 — B-Executive AI') !== false) {
    echo "SKIP: BAB 82 sudah wujud.\n";
    exit(0);
}

$bab82 = rtrim(file_get_contents($teks));

// 1) Sisip sebelum "## BAB 99 — Canonical Glossary"
$anchor = '## BAB 99 — Canonical Glossary';
if (strpos($c, $anchor) === false) {
    fwrite(STDERR, "FAIL: anchor BAB 99 tidak dijumpai\n");
    exit(1);
}
$c = str_replace($anchor, $bab82 . "\n\n---\n\n" . $anchor, $c);

// 2) Kemas kini header versi 8.4 -> 8.5
$c = str_replace(
    '**Versi:** 8.4  ',
    '**Versi:** 8.5  ',
    $c
);

$old_note = '**Semakan v8.4:** PART XVII (BAB 81) — Competitive Advantage & Business Growth Engine: 9 kelebihan dengan status kod sebenar, 15 idea daripada 400+ pesaing, analisis kos & skor kompetitif, keutamaan pelaksanaan. Peta pesaing: `ai-bijan-saas/docs/PETA-PESAING-34-MODUL.md`';
$new_note = $old_note . "\n**Semakan v8.5:** PART XVIII (BAB 82) — B-Executive AI: Pembantu Eksekutif Multimodal: 8 keupayaan (termasuk video understanding, video blast, video training, screen capture), pipeline video 10 langkah, Media Vault, tahap autonomy 0–4 dengan pintu kelulusan, Modul Prospecting + Blast Engine + SalesCopilot + MediaStudio, dan analisis pesaing video-avatar (HeyGen, Synthesia, D-ID, Tavus).";
if (strpos($c, $old_note) !== false) {
    $c = str_replace($old_note, $new_note, $c);
} else {
    fwrite(STDERR, "WARN: nota v8.4 tidak dijumpai (header sahaja dikemas kini)\n");
}

// 3) Kemas kini baris indeks: BAB 81 + tambah BAB 82
$old_idx = "| 81 | Competitive Advantage & Business Growth Engine | PART XVII — KELEBIHAN KOMPETITIF & ENJIN PERTUMBUHAN |";
$new_idx = $old_idx . "\n| 82 | B-Executive AI: Pembantu Eksekutif Multimodal | PART XVIII — PEMBANTU EKSEKUTIF MULTIMODAL & ENJIN JUALAN |";
if (strpos($c, $old_idx) !== false) {
    $c = str_replace($old_idx, $new_idx, $c);
} else {
    fwrite(STDERR, "WARN: baris indeks BAB 81 tidak dijumpai\n");
}

// 4) Kemas kini jumlah dalam indeks jika ada
$c = preg_replace('/(\|\s*)89(\s*\/\s*)90(\s*bab)/', '${1}90${2}91${3}', $c);
$c = preg_replace('/(\|\s*)16(\s*\/\s*)17(\s*PART)/', '${1}17${2}18${3}', $c);
$c = str_replace('16 PART', '17 PART', $c);

file_put_contents($blueprint, $c);

// Verify
$check = file_get_contents($blueprint);
$bab82_count = substr_count($check, '## BAB 82 — B-Executive AI');
$v85 = strpos($check, '**Versi:** 8.5') !== false;
$note85 = strpos($check, 'Semakan v8.5') !== false;
$idx82 = strpos($check, '| 82 | B-Executive AI') !== false;

echo "BAB 82 occurrences: {$bab82_count}\n";
echo "Versi 8.5: " . ($v85 ? 'YA' : 'TIDAK') . "\n";
echo "Nota v8.5: " . ($note85 ? 'YA' : 'TIDAK') . "\n";
echo "Indeks BAB 82: " . ($idx82 ? 'YA' : 'TIDAK') . "\n";
echo "Panjang fail: " . mb_strlen($check, 'UTF-8') . " aksara\n";

if ($bab82_count !== 1) {
    fwrite(STDERR, "FAIL: BAB 82 bukan 1!\n");
    exit(2);
}
echo "OK.\n";
