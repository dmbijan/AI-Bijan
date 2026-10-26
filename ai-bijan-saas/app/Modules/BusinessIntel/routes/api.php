<?php

use Illuminate\Support\Facades\Route;
use App\Modules\BusinessIntel\Http\Controllers\BusinessIntelController;

/*
|--------------------------------------------------------------------------
| Business Intelligence Routes (BAB 86 / Idea #23, #24, #25)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/business-intel
| AI self-improve, kajian bisnes, idea feed & competitor watch.
|
| PENTING: laluan statik mesti didahulukan sebelum {id}.
|
*/

// Kesihatan perniagaan
Route::get('/health', [BusinessIntelController::class, 'kesihatan'])->middleware('perm:business_intel.view');
Route::post('/health/snapshot', [BusinessIntelController::class, 'simpanKesihatan'])->middleware('perm:business_intel.execute');
Route::get('/health/latest', [BusinessIntelController::class, 'snapshotTerkini'])->middleware('perm:business_intel.view');
Route::get('/health/trend', [BusinessIntelController::class, 'trend'])->middleware('perm:business_intel.view');

// Kajian
Route::get('/studies', [BusinessIntelController::class, 'kajian'])->middleware('perm:business_intel.view');
Route::post('/studies', [BusinessIntelController::class, 'jalankanKajian'])->middleware('perm:business_intel.execute');
Route::get('/studies/{id}', [BusinessIntelController::class, 'butiranKajian'])->middleware('perm:business_intel.view');
Route::get('/data', [BusinessIntelController::class, 'dataBisnes'])->middleware('perm:business_intel.view');

// Idea feed
Route::get('/ideas', [BusinessIntelController::class, 'idea'])->middleware('perm:business_intel.view');
Route::post('/ideas', [BusinessIntelController::class, 'ciptaIdea'])->middleware('perm:business_intel.manage');
Route::post('/ideas/{id}/accept', [BusinessIntelController::class, 'terimaIdea'])->middleware('perm:business_intel.manage');
Route::post('/ideas/{id}/reject', [BusinessIntelController::class, 'tolakIdea'])->middleware('perm:business_intel.manage');
Route::post('/ideas/{id}/complete', [BusinessIntelController::class, 'lengkapkanIdea'])->middleware('perm:business_intel.manage');

// Pesaing
Route::get('/competitors', [BusinessIntelController::class, 'pesaing'])->middleware('perm:business_intel.view');
Route::post('/competitors', [BusinessIntelController::class, 'simpanPesaing'])->middleware('perm:business_intel.manage');
Route::post('/competitors/{id}/snapshot', [BusinessIntelController::class, 'rekodSnapshotPesaing'])->middleware('perm:business_intel.manage');
Route::get('/competitors/summary', [BusinessIntelController::class, 'ringkasanPersaingan'])->middleware('perm:business_intel.view');

// Sinyal pasaran
Route::get('/signals', [BusinessIntelController::class, 'sinyal'])->middleware('perm:business_intel.view');
Route::post('/signals/generate', [BusinessIntelController::class, 'janaSinyal'])->middleware('perm:business_intel.execute');

// Pembelajaran AI
Route::get('/learnings', [BusinessIntelController::class, 'pembelajaran'])->middleware('perm:business_intel.view');
