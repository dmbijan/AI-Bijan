<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Community (BAB 86 / Idea #29)
    |--------------------------------------------------------------------------
    |
    | Komuniti AI BIJAN: showcase, academy, help, idea board.
    |
    | Idea komuniti:
    | - Showcase — tenant tunjuk hasil kerja (opt-in) → inspirasi + SEO funnel
    | - Academy — tutorial rasmi, resipi automasi, sertifikasi
    | - Help — soal jawab; jawapan diterima jadi artikel Knowledge Base
    | - Idea — undi ciri baharu; tertinggi masuk backlog
    |
    */

    'spaces' => [
        // Ruang rasmi yang dicipta untuk setiap pemasangan
        'official' => [
            ['slug' => 'showcase', 'name' => 'Showcase', 'space_type' => 'showcase', 'visibility' => 'public'],
            ['slug' => 'academy', 'name' => 'Academy', 'space_type' => 'academy', 'visibility' => 'public'],
            ['slug' => 'help', 'name' => 'Bantuan', 'space_type' => 'help', 'visibility' => 'members'],
            ['slug' => 'ideas', 'name' => 'Idea & Cadangan', 'space_type' => 'forum', 'visibility' => 'members'],
        ],
    ],

    'moderation' => [
        // Semak kandungan pos secara automatik
        'auto_moderate' => (bool) env('COMMUNITY_AUTO_MODERATE', true),
        // Had pos sehari per pengguna
        'max_posts_per_day' => (int) env('COMMUNITY_MAX_POSTS', 20),
        // Had panjang pos
        'max_post_length' => (int) env('COMMUNITY_MAX_POST_LENGTH', 20000),
        'max_reply_length' => (int) env('COMMUNITY_MAX_REPLY_LENGTH', 10000),
    ],

    'discovery' => [
        // Feed lalai
        'default_sort' => 'terbaru',
        // Bilangan pos setiap halaman
        'per_page' => (int) env('COMMUNITY_PER_PAGE', 25),
        // Benarkan carian awam (SEO)
        'public_search' => (bool) env('COMMUNITY_PUBLIC_SEARCH', true),
    ],

    'reputation' => [
        // Sistem reputasi (poin untuk jawapan diterima, like, dsb.)
        'enabled' => (bool) env('COMMUNITY_REPUTATION', true),
        'points' => [
            'accepted_answer' => 25,
            'post_like' => 2,
            'reply' => 5,
        ],
    ],
];
