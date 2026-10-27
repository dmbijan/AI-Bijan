<?php

/**
 * Senarai putih untuk `skrip/semak-kebenaran.php` (T-105).
 *
 * `laluan_awam` — laluan awam tulen (tiada `auth:sanctum`). Skrip sudah
 * mengabaikan laluan tanpa `auth`, jadi senarai ini hanya dokumentasi.
 *
 * `laluan_tanpa_perm` — laluan BERautentikasi yang sengaja tiada `perm:`.
 * Setiap entri MESTI ada sebab. Pola menyokong wildcard `*` (Str::is).
 */
return [
    'laluan_awam' => [
        'api/v1/auth/*',            // log masuk, daftar, lupa kata laluan
        'api/v1/public/*',          // portal awam (helpdesk, e-receipt)
        'api/v1/health',            // liveness — tiada I/O
        'api/v1/fundamental/health/live',  // probe load balancer (withoutMiddleware auth+tenant)
        'api/v1/webhooks/*',        // tandatangan HMAC/rahasia webhook menggantikan RBAC
        'api/v1/vision/ingest/*',   // webhook HMAC kamera (lihat VisionIngestService)
        'api/v1/site/forms/*/submit',      // borang laman awam (tenant dari laman, bukan auth)
        'api/v1/site/sites/*/forms/*/submit',
        'api/v1/s/*',               // dokumen kongsi — token sekali guna (share link)
    ],

    'laluan_tanpa_perm' => [
        // Data PLATFORM (bukan tenant). Skop = `auth()->id()` dalam pengawal:
        // reseller hanya nampak komisen miliknya sendiri.
        'api/v1/reseller/me',
        'api/v1/reseller/commissions',

        // Dokumen: kebenaran diperincikan dalam `DocumentController::bolehAkses()` —
        // pekerja boleh muat turun slip gajinya SENDIRI walaupun tiada
        // `documents.view`. Pagar `perm:` di laluan akan mematahkan kes ini.
        // Senarai ini hanya untuk BACA/papar; `store`/`void` berkunci `perm:`.
        'api/v1/documents/catalog',
        'api/v1/documents',
        'api/v1/documents/*',
        'api/v1/documents/*/download/*',
    ],
];
