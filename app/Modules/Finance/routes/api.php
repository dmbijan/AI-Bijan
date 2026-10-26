<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| B-Finance API Routes
|--------------------------------------------------------------------------
|
| ⚠️ JANGAN tambah Route::prefix('finance') di sini.
| FinanceServiceProvider sudah mendaftar ->prefix('api/v1/finance').
| Menambah prefix di sini menghasilkan laluan berganda api/v1/finance/finance/*.
|
*/

// Chart of Accounts
Route::get('/chart-of-accounts', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'index']);
Route::get('/chart-of-accounts/{id}', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'show']);
Route::post('/chart-of-accounts', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'store']);
Route::put('/chart-of-accounts/{id}', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'update']);

// Journal Entries
Route::get('/journal-entries', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'index']);
Route::get('/journal-entries/{id}', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'show']);
Route::post('/journal-entries', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'store']);
Route::post('/journal-entries/{id}/post', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'post']);
Route::post('/journal-entries/{id}/cancel', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'cancel']);

// Financial Reports
Route::get('/reports/profit-loss', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'profitLoss']);
Route::get('/reports/balance-sheet', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'balanceSheet']);
Route::get('/reports/trial-balance', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'trialBalance']);
Route::get('/reports/general-ledger/{accountId}', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'generalLedger']);
