<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Seo\Http\Controllers\SeoController;

/*
|--------------------------------------------------------------------------
| SEO & Metadata Routes (BAB 86 / Idea #20)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/seo
| SEO untuk SEMUA objek AI BIJAN — produk, halaman, artikel, video.
|
*/

// Profil
Route::get('/profile', [SeoController::class, 'profil'])->middleware('perm:seo.view');
Route::put('/profile', [SeoController::class, 'kemasProfil'])->middleware('perm:seo.manage');

// Metadata objek
Route::get('/metadata', [SeoController::class, 'index'])->middleware('perm:seo.view');
Route::post('/metadata', [SeoController::class, 'simpan'])->middleware('perm:seo.manage');
Route::get('/metadata/{type}/{id}', [SeoController::class, 'show'])->middleware('perm:seo.view');

// Audit
Route::post('/metadata/{type}/{id}/audit', [SeoController::class, 'audit'])->middleware('perm:seo.view');
Route::get('/audit', [SeoController::class, 'auditSemua'])->middleware('perm:seo.view');
Route::get('/issues', [SeoController::class, 'isu'])->middleware('perm:seo.view');
Route::post('/issues/{id}/resolve', [SeoController::class, 'selesaikanIsu'])->middleware('perm:seo.manage');

// Cadangan AI
Route::post('/metadata/{type}/{id}/suggest', [SeoController::class, 'janaCadangan'])->middleware('perm:seo.execute');
Route::get('/suggestions', [SeoController::class, 'cadangan'])->middleware('perm:seo.view');
Route::post('/suggestions/{id}/accept', [SeoController::class, 'terimaCadangan'])->middleware('perm:seo.manage');
Route::post('/suggestions/{id}/reject', [SeoController::class, 'tolakCadangan'])->middleware('perm:seo.manage');

// Sitemap & robots (perlu awam untuk enjin carian — intermediate)
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->middleware('perm:seo.view');
Route::get('/robots.txt', [SeoController::class, 'robots'])->middleware('perm:seo.view');

// JSON-LD
Route::post('/json-ld', [SeoController::class, 'jsonLd'])->middleware('perm:seo.view');

// Kata kunci
Route::get('/keywords', [SeoController::class, 'kataKunci'])->middleware('perm:seo.view');
Route::post('/keywords', [SeoController::class, 'simpanKataKunci'])->middleware('perm:seo.manage');
