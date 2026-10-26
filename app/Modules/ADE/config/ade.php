<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ADE — Agentic Development Environment
    |--------------------------------------------------------------------------
    |
    | 4 fasa mengikut `obra/superpowers` (MIT):
    |   A1 Skills Engine · A2 Worktree Isolation · A3 Coordinator · A4 Review
    |
    */

    'nama' => 'ADE',
    'versi' => '1.0.0',

    // ── A1 Skills Engine ───────────────────────────────────────────────
    'skills' => [
        'had_per_tenant' => 200,
        'had_langkah' => 20,
        'had_pencetus' => 15,
        'pasang_builtin_auto' => true,
        // Lesen yang boleh dipasang semula ke tenant lain.
        'lesen_dibenarkan' => ['MIT', 'Apache-2.0', 'BSD', 'BSD-3-Clause'],
    ],

    // ── A2 Worktree Isolation ──────────────────────────────────────────
    'sandbox' => [
        'jenis_lalai' => 'worktree',
        'had_aktif' => 20,
        'had_jumlah' => 100,
        // Lokasi worktree relatif kepada storage.
        'direktori' => 'app/ade/worktrees',
        // Benarkan git worktree sebenar? Jika false, hanya 'directory'/'none'.
        'benar_kan_worktree' => true,
        'asas_branch' => 'main',
    ],

    // ── A3 Sub-Agent Coordinator ───────────────────────────────────────
    'coordinator' => [
        'had_task' => 50,
        'parallel_maks' => 10,
        'parallel_lalai' => 4,
        'peranan' => ['implementer', 'tester', 'reviewer', 'researcher'],
    ],

    // ── A4 Review Surface ──────────────────────────────────────────────
    'review' => [
        'had_temuan' => 100,
        'had_komen' => 200,
        'had_baris_diff' => 2000,
        // Penemuan kritikal menyekat kelulusan automatik.
        'kritikal_menyekat' => true,
        // Ujian wajib lulus sebelum review boleh diluluskan.
        'perlu_ujian_lulus' => true,
    ],
];
