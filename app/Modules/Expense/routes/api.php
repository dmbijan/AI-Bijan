<?php

use App\Modules\Expense\Http\Controllers\ExpenseController;
use Illuminate\Support\Facades\Route;

/**
 * Laluan API B-Expense (BAB 89 P1).
 *
 * ⚠️ SEMUA laluan statik DIDAHULUKAN sebelum `/{id}` — jika tidak,
 * `summary` / `categories` / `pending-approvals` akan ditangkap oleh `/{id}`.
 */
Route::prefix('expenses')->middleware(['auth:sanctum'])->group(function () {

    // ---- STATIK (mesti di atas sekali) ----
    Route::get('summary', [ExpenseController::class, 'summary'])
        ->middleware('perm:expense.view');

    Route::get('categories', [ExpenseController::class, 'categories'])
        ->middleware('perm:expense.view');

    Route::get('pending-approvals', [ExpenseController::class, 'pendingApprovals'])
        ->middleware('perm:expense.approve');

    Route::get('suggest-category', [ExpenseController::class, 'suggestCategory'])
        ->middleware('perm:expense.view');

    Route::get('duplicate-check', [ExpenseController::class, 'duplicateCheck'])
        ->middleware('perm:expense.view');

    Route::get('receipts', [ExpenseController::class, 'receipts'])
        ->middleware('perm:expense.view');

    // ---- KATEGORI ----
    Route::post('categories', [ExpenseController::class, 'storeCategory'])
        ->middleware('perm:expense.manage');

    Route::post('categories/seed', [ExpenseController::class, 'seedCategories'])
        ->middleware('perm:expense.manage');

    // ---- TUNTUTAN (statik relatif) ----
    Route::get('claims', [ExpenseController::class, 'claims'])
        ->middleware('perm:expense.view');

    Route::post('claims', [ExpenseController::class, 'storeClaim'])
        ->middleware('perm:expense.manage');

    Route::get('claims/{id}', [ExpenseController::class, 'showClaim'])
        ->middleware('perm:expense.view')->whereNumber('id');

    Route::post('claims/{id}/submit', [ExpenseController::class, 'submitClaim'])
        ->middleware('perm:expense.manage')->whereNumber('id');

    Route::post('claims/{id}/approve', [ExpenseController::class, 'approveClaim'])
        ->middleware('perm:expense.approve')->whereNumber('id');

    Route::post('claims/{id}/reject', [ExpenseController::class, 'rejectClaim'])
        ->middleware('perm:expense.approve')->whereNumber('id');

    Route::post('claims/{id}/reimburse', [ExpenseController::class, 'reimburseClaim'])
        ->middleware('perm:expense.post')->whereNumber('id');

    Route::post('claims/{id}/receipts', [ExpenseController::class, 'uploadClaimReceipt'])
        ->middleware('perm:expense.manage')->whereNumber('id');

    // ---- PERBELANJAAN ----
    Route::get('/', [ExpenseController::class, 'index'])
        ->middleware('perm:expense.view');

    Route::post('/', [ExpenseController::class, 'store'])
        ->middleware('perm:expense.manage');

    Route::get('{id}', [ExpenseController::class, 'show'])
        ->middleware('perm:expense.view')->whereNumber('id');

    Route::post('{id}/submit', [ExpenseController::class, 'submit'])
        ->middleware('perm:expense.manage')->whereNumber('id');

    Route::post('{id}/approve', [ExpenseController::class, 'approve'])
        ->middleware('perm:expense.approve')->whereNumber('id');

    Route::post('{id}/reject', [ExpenseController::class, 'reject'])
        ->middleware('perm:expense.approve')->whereNumber('id');

    Route::post('{id}/post', [ExpenseController::class, 'post'])
        ->middleware('perm:expense.post')->whereNumber('id');

    Route::post('{id}/paid', [ExpenseController::class, 'markPaid'])
        ->middleware('perm:expense.post')->whereNumber('id');

    Route::post('{id}/receipts', [ExpenseController::class, 'uploadReceipt'])
        ->middleware('perm:expense.manage')->whereNumber('id');

    Route::post('receipts/{id}/verify', [ExpenseController::class, 'verifyReceipt'])
        ->middleware('perm:expense.approve')->whereNumber('id');
});
