<?php

use App\Modules\Finance\Http\Controllers\BankReconciliationController as Bank;
use App\Modules\Finance\Http\Controllers\ChartOfAccountsController as Coa;
use App\Modules\Finance\Http\Controllers\FinancialReportController as Report;
use App\Modules\Finance\Http\Controllers\JournalController as Journal;
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
| T-105: setiap laluan tenant MESTI ada pagar `perm:`. (Dulu laluan Finance tiada pagar,
| jadi mana-mana ahli tenant, cth. juruwang, boleh mencipta dan posting jurnal.)
|
*/

Route::middleware('perm:finance.view')->group(function () {
    Route::get('/chart-of-accounts', [Coa::class, 'index']);
    Route::get('/chart-of-accounts/{id}', [Coa::class, 'show']);
    Route::get('/journal-entries', [Journal::class, 'index']);
    Route::get('/journal-entries/{id}', [Journal::class, 'show']);

    // FIN-017 Rekonsiliasi Bank
    Route::get('/bank-accounts', [Bank::class, 'accounts']);
    Route::get('/bank-accounts/{id}/lines', [Bank::class, 'lines'])->whereNumber('id');
    Route::get('/bank-accounts/{id}/summary', [Bank::class, 'summary'])->whereNumber('id');
    Route::get('/bank-lines/{lineId}/candidates', [Bank::class, 'candidates'])->whereNumber('lineId');
});

Route::middleware('perm:finance.manage')->group(function () {
    Route::post('/chart-of-accounts', [Coa::class, 'store']);
    Route::put('/chart-of-accounts/{id}', [Coa::class, 'update']);
    Route::post('/journal-entries', [Journal::class, 'store']);

    Route::post('/bank-accounts', [Bank::class, 'storeAccount']);
    Route::post('/bank-accounts/{id}/import', [Bank::class, 'import'])->whereNumber('id');
    Route::post('/bank-accounts/{id}/auto-match', [Bank::class, 'autoMatch'])->whereNumber('id');
    Route::post('/bank-lines/{lineId}/match', [Bank::class, 'match'])->whereNumber('lineId');
    Route::post('/bank-lines/{lineId}/unmatch', [Bank::class, 'unmatch'])->whereNumber('lineId');
    Route::post('/bank-lines/{lineId}/ignore', [Bank::class, 'ignore'])->whereNumber('lineId');
});

Route::middleware('perm:finance.approve')->group(function () {
    Route::post('/journal-entries/{id}/post', [Journal::class, 'post']);
    Route::post('/journal-entries/{id}/cancel', [Journal::class, 'cancel']);
});

Route::middleware('perm:finance.reports')->group(function () {
    Route::get('/reports/profit-loss', [Report::class, 'profitLoss']);
    Route::get('/reports/balance-sheet', [Report::class, 'balanceSheet']);
    Route::get('/reports/trial-balance', [Report::class, 'trialBalance']);
    Route::get('/reports/general-ledger/{accountId}', [Report::class, 'generalLedger']);
});
