<?php

/**
 * Model yang SENGAJA tidak menggunakan trait `BelongsToTenant`
 * walaupun jadualnya mempunyai kolum `tenant_id`.
 *
 * ⚠️ Setiap entri MESTI ada justifikasi. `php skrip/audit-tenant.php`
 * akan gagal jika ada model bercanggah yang tidak disenaraikan di sini.
 *
 * Peraturan pemilihan:
 *   - Daftar HANYA jika model dibaca/ditulis semasa TenantContext kosong
 *     (provisioning, CLI, seed) ATAU ia berkongsi data merentas tenant secara
 *     reka bentuk (katalog global, kadar statutori global).
 *   - Jika didaftar, PASTIKAN setiap query menulis `where('tenant_id', $id)`
 *     secara eksplisit — tiada jaminan automatik.
 *   - Jika tidak pasti: TAMBAH trait. Ia lebih selamat.
 */

return [

    'models' => [

        // Katalog global AI — tenant_id NULL bermaksud "tersedia untuk semua".
        // Dibaca oleh seeder & routing model, sering tanpa TenantContext.
        \App\Modules\AIGateway\Models\AiModel::class,

        // Skill global (tenant_id NULL) + salinan per tenant.
        // ADE menggunakan whereNull('tenant_id') untuk katalog global.
        \App\Modules\ADE\Models\AdeSkill::class,

        // Kadar statutori LHDN/KWSP — nilai global (tenant_id NULL) + override tenant.
        // Dibaca dalam pengiraan payroll yang berjalan dalam konteks jelas.
        \App\Modules\Payroll\Models\StatutoryRate::class,

        // Grant entitlemen — ditulis oleh provisioning SEBELUM konteks wujud.
        // Membaca/menulis dengan withoutGlobalScope('tenant') secara eksplisit.
        \App\Modules\Entitlement\Models\EntitlementGrant::class,

        // Kaunter penggunaan — reserve/settle berlaku semasa provisioning & job.
        // Semua query menulis where('tenant_id', …) eksplisit.
        \App\Modules\Entitlement\Models\UsageCounter::class,

        // Had per plan — data platform, dikongsi merentas tenant.
        \App\Modules\Entitlement\Models\PlanLimit::class,

        // Templat media rasmi (tenant_id NULL) + templat tenant.
        // Disemak 27 Okt 2026: SEMUA query guna
        //   ->where(fn($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $id))
        // Trait akan menyembunyikan templat rasmi → ciri hilang.
        \App\Modules\MediaEditor\Models\MediaTemplate::class,

        // Templat aliran kerja rasmi (tenant_id NULL) + templat tenant.
        // Sama seperti MediaTemplate — tiada trait sengaja.
        \App\Modules\WorkflowStudio\Models\WorkflowTemplate::class,

        // Akses sokongan Super Admin — dibaca SEBELUM TenantContext ditetapkan
        // (`ResolveTenant` semak grant untuk tentukan tenant). Trait akan
        // menapis tenant_id = 0 dan grant tidak pernah dijumpai → akses
        // sokongan MUSTAHIL. Query eksplisit: where('tenant_id', $tenant->id).
        \App\Models\SupportAccessGrant::class,

        // ── B-Billing / Reseller (27 Okt 2026) ─────────────────────
        // Rujukan reseller: `tenant_id` ialah SUBJEK rujukan, bukan pemilik
        // rekod. Dibaca oleh cron platform (kematangan komisen, churn, payout)
        // yang berjalan TANPA TenantContext — tiada tenant "semasa".
        // Setiap query MESTI eksplisit: where('tenant_id', $id).
        \App\Modules\Billing\Models\ResellerReferral::class,

        // Komisen reseller: sama seperti di atas, dibaca platform-wide oleh
        // cron. Trait akan menyembunyikan SEMUA komisen semasa cron berjalan
        // (konteks kosong → tenant_id = 0) → payout tidak pernah terbina.
        \App\Modules\Billing\Models\ResellerCommission::class,

    ],

];
