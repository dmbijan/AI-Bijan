<?php

use Illuminate\Support\Facades\Route;
use App\Modules\MediaEditor\Http\Controllers\MediaEditorController;

/*
|--------------------------------------------------------------------------
| Media Editor Routes (BAB 86 / Idea #27)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/media-editor
| Editor gambar/video ala Canva/CapCut — tenant edit sendiri.
|
*/

// Statik dahulu
Route::get('/resolutions', [MediaEditorController::class, 'resolutions'])->middleware('perm:media_editor.view');
Route::get('/layer-types', [MediaEditorController::class, 'layerTypes'])->middleware('perm:media_editor.view');
Route::get('/templates', [MediaEditorController::class, 'templates'])->middleware('perm:media_editor.view');
Route::post('/templates/{id}/use', [MediaEditorController::class, 'useTemplate'])->middleware('perm:media_editor.manage');
Route::get('/summary', [MediaEditorController::class, 'ringkasan'])->middleware('perm:media_editor.view');

// Projek
Route::get('/projects', [MediaEditorController::class, 'index'])->middleware('perm:media_editor.view');
Route::post('/projects', [MediaEditorController::class, 'store'])->middleware('perm:media_editor.manage');
Route::get('/projects/{id}', [MediaEditorController::class, 'show'])->middleware('perm:media_editor.view');
Route::put('/projects/{id}', [MediaEditorController::class, 'update'])->middleware('perm:media_editor.manage');
Route::post('/projects/{id}/layers', [MediaEditorController::class, 'tambahLapisan'])->middleware('perm:media_editor.manage');
Route::delete('/projects/{id}/layers/{index}', [MediaEditorController::class, 'buangLapisan'])->middleware('perm:media_editor.manage');
Route::post('/projects/{id}/render', [MediaEditorController::class, 'render'])->middleware('perm:media_editor.execute');
Route::get('/projects/{id}/jobs', [MediaEditorController::class, 'jobs'])->middleware('perm:media_editor.view');
