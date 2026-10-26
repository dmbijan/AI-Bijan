<?php

use Illuminate\Support\Facades\Route;
use App\Modules\ADE\Http\Controllers\AdeController;

/*
|--------------------------------------------------------------------------
| ADE Routes — Agentic Development Environment (BAB 92)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/ade
|
| 4 fasa (corak obra/superpowers, MIT):
|   A1 Skills Engine        → /skills
|   A2 Worktree Isolation   → /sandboxes
|   A3 Sub-Agent Coordinator → /coordinations
|   A4 Review Surface       → /reviews
|
*/

// Gambaran keseluruhan
Route::get('/summary', [AdeController::class, 'ringkasan'])->middleware('perm:ade.view');

// ── A1: Skills Engine ────────────────────────────────────────────────────
Route::get('/skills', [AdeController::class, 'senaraiSkill'])->middleware('perm:ade.view');
Route::post('/skills', [AdeController::class, 'janaSkill'])->middleware('perm:ade.manage');
Route::post('/skills/install', [AdeController::class, 'pasangSkill'])->middleware('perm:ade.manage');
Route::post('/skills/match', [AdeController::class, 'padanSkill'])->middleware('perm:ade.view');
Route::get('/skills/{id}/invocations', [AdeController::class, 'sejarahSkill'])->middleware('perm:ade.view');

// ── A2: Worktree Isolation ───────────────────────────────────────────────
// ⚠️ Laluan statik MESTI sebelum laluan {id}.
Route::get('/sandboxes/branches', [AdeController::class, 'senaraiBranch'])->middleware('perm:ade.view');
Route::get('/sandboxes', [AdeController::class, 'senaraiSandbox'])->middleware('perm:ade.view');
Route::post('/sandboxes', [AdeController::class, 'ciptaSandbox'])->middleware('perm:ade.execute');
Route::post('/sandboxes/{id}/baseline', [AdeController::class, 'rekodBaseline'])->middleware('perm:ade.execute');
Route::delete('/sandboxes/{id}', [AdeController::class, 'buangSandbox'])->middleware('perm:ade.manage');

// ── A3: Sub-Agent Coordinator ────────────────────────────────────────────
Route::get('/coordinations', [AdeController::class, 'senaraiCoordination'])->middleware('perm:ade.view');
Route::post('/coordinations', [AdeController::class, 'ciptaCoordination'])->middleware('perm:ade.manage');
Route::get('/coordinations/{id}', [AdeController::class, 'butiranCoordination'])->middleware('perm:ade.view');
Route::post('/coordinations/{id}/run', [AdeController::class, 'jalankanCoordination'])->middleware('perm:ade.execute');
Route::post('/coordinations/{id}/cross-review', [AdeController::class, 'semakSilang'])->middleware('perm:ade.execute');

// ── A4: Review Surface ───────────────────────────────────────────────────
Route::get('/reviews', [AdeController::class, 'senaraiReview'])->middleware('perm:ade.view');
Route::post('/reviews', [AdeController::class, 'ciptaReview'])->middleware('perm:ade.manage');
Route::get('/reviews/{id}/walkthrough', [AdeController::class, 'walkthrough'])->middleware('perm:ade.view');
Route::post('/reviews/{id}/findings', [AdeController::class, 'tambahTemuan'])->middleware('perm:ade.execute');
Route::post('/reviews/{id}/comments', [AdeController::class, 'tambahKomen'])->middleware('perm:ade.execute');
Route::post('/reviews/{id}/tests', [AdeController::class, 'rekodUjian'])->middleware('perm:ade.execute');
Route::post('/reviews/{id}/approve', [AdeController::class, 'sahkanReview'])->middleware('perm:ade.approve');

// ── Aliran penuh ─────────────────────────────────────────────────────────
Route::post('/run', [AdeController::class, 'jalankanAliran'])->middleware('perm:ade.execute');
