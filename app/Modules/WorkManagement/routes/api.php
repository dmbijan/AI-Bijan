<?php

use App\Modules\WorkManagement\Http\Controllers\WorkManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WorkManagement Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/work-management
| inspired oleh https://github.com/opf/openproject
|
*/

// Garis masa & aktiviti
Route::get('/projects/{projectId}/timeline', [WorkManagementController::class, 'timeline'])->middleware('perm:work_management.view');
Route::post('/projects/{projectId}/activities', [WorkManagementController::class, 'storeActivity'])->middleware('perm:work_management.manage');
Route::put('/projects/{projectId}/activities/{activityId}', [WorkManagementController::class, 'updateActivity'])->middleware('perm:work_management.manage');
Route::delete('/projects/{projectId}/activities/{activityId}', [WorkManagementController::class, 'destroyActivity'])->middleware('perm:work_management.manage');

// Garis dasar & variance
Route::get('/projects/{projectId}/baselines', [WorkManagementController::class, 'baselines'])->middleware('perm:work_management.view');
Route::post('/projects/{projectId}/baselines', [WorkManagementController::class, 'captureBaseline'])->middleware('perm:work_management.manage');
Route::get('/projects/{projectId}/variance', [WorkManagementController::class, 'variance'])->middleware('perm:work_management.view');

// Bajet
Route::get('/projects/{projectId}/budget', [WorkManagementController::class, 'budget'])->middleware('perm:work_management.view');
Route::post('/projects/{projectId}/budget', [WorkManagementController::class, 'storeBudgetLine'])->middleware('perm:work_management.manage');
Route::put('/projects/{projectId}/budget/{lineId}', [WorkManagementController::class, 'updateBudgetLine'])->middleware('perm:work_management.manage');
Route::delete('/projects/{projectId}/budget/{lineId}', [WorkManagementController::class, 'destroyBudgetLine'])->middleware('perm:work_management.manage');

// Portfolio
Route::get('/portfolios', [WorkManagementController::class, 'portfolios'])->middleware('perm:work_management.view');
Route::post('/portfolios', [WorkManagementController::class, 'storePortfolio'])->middleware('perm:work_management.manage');
Route::put('/portfolios/{id}', [WorkManagementController::class, 'updatePortfolio'])->middleware('perm:work_management.manage');
Route::delete('/portfolios/{id}', [WorkManagementController::class, 'destroyPortfolio'])->middleware('perm:work_management.manage');
