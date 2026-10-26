<?php

use Illuminate\Support\Facades\Route;
use App\Modules\DocumentAI\Http\Controllers\DocumentController;

/*
|--------------------------------------------------------------------------
| B-Document AI Routes
|--------------------------------------------------------------------------
|
| Intelligent Document Processing with OCR and ML Extraction
| Malaysian compliance ready with multi-language support
|
*/

// Documents API
Route::get('/documents', [DocumentController::class, 'index'])->middleware('permission:documents.view');
Route::post('/documents', [DocumentController::class, 'store'])->middleware('permission:documents.issue');
Route::get('/documents/{document}', [DocumentController::class, 'show'])->middleware('permission:documents.view');
Route::put('/documents/{document}', [DocumentController::class, 'update'])->middleware('permission:documents.issue');
Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->middleware('permission:documents.void');
Route::post('/documents/{document}/reprocess', [DocumentController::class, 'reprocess'])->middleware('permission:documents.issue');
Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->middleware('permission:documents.view');
Route::get('/documents/statistics', [DocumentController::class, 'getStatistics'])->middleware('permission:documents.view');
