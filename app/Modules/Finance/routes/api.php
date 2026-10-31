<?php

use App\Modules\Finance\Http\Controllers\BankReconciliationController as Bank;
use App\Modules\Finance\Http\Controllers\BudgetController as Budget;
use App\Modules\Finance\Http\Controllers\CashManagementController as Cash;
use App\Modules\Finance\Http\Controllers\ChartOfAccountsController as Coa;
use App\Modules\Finance\Http\Controllers\FinancialReportController as Report;
use App\Modules\Finance\Http\Controllers\JournalController as Journal;
use App\Modules\Finance\Http\Controllers\TaxController as Tax;
use App\Modules\Finance\Http\Controllers\TreasuryController as Treasury;
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

    // FIN-018 Pengurusan Tunai
    Route::get('/cash-boxes', [Cash::class, 'boxes']);
    Route::get('/cash-boxes/{id}/report', [Cash::class, 'report'])->whereNumber('id');
    Route::get('/cash-denominations', [Cash::class, 'denominations']);

    // FIN-010 Cukai SST
    Route::get('/tax-codes', [Tax::class, 'codes']);
    Route::get('/tax-settings', [Tax::class, 'settings']);
    Route::post('/tax/calculate', [Tax::class, 'calculate']);
    Route::get('/tax/period', [Tax::class, 'period']);

    // FIN-005 Bajet
    Route::get('/budgets', [Budget::class, 'index']);
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

    Route::post('/cash-boxes', [Cash::class, 'storeBox']);
    Route::post('/cash-boxes/{id}/entries', [Cash::class, 'entry'])->whereNumber('id');
    Route::post('/cash-boxes/{id}/top-up', [Cash::class, 'topUp'])->whereNumber('id');
    Route::post('/cash-boxes/{id}/deposit', [Cash::class, 'deposit'])->whereNumber('id');
    Route::post('/cash-boxes/{id}/receive-drop', [Cash::class, 'receiveDrop'])->whereNumber('id');
    Route::post('/cash-counts', [Cash::class, 'count']);

    Route::post('/tax-codes', [Tax::class, 'storeCode']);
    Route::put('/tax-codes/{id}', [Tax::class, 'updateCode'])->whereNumber('id');
    Route::put('/tax-settings', [Tax::class, 'updateSettings']);

    Route::post('/budgets', [Budget::class, 'store']);
    Route::put('/budgets/{id}/lines', [Budget::class, 'setLines'])->whereNumber('id');
});

Route::middleware('perm:finance.approve')->group(function () {
    Route::post('/journal-entries/{id}/post', [Journal::class, 'post']);
    Route::post('/journal-entries/{id}/cancel', [Journal::class, 'cancel']);
    Route::post('/cash-counts/{countId}/adjust', [Cash::class, 'adjust'])->whereNumber('countId');
    Route::post('/budgets/{id}/approve', [Budget::class, 'approve'])->whereNumber('id');
});

Route::middleware('perm:finance.reports')->group(function () {
    Route::get('/reports/profit-loss', [Report::class, 'profitLoss']);
    Route::get('/reports/balance-sheet', [Report::class, 'balanceSheet']);
    Route::get('/reports/trial-balance', [Report::class, 'trialBalance']);
    Route::get('/reports/general-ledger/{accountId}', [Report::class, 'generalLedger']);
    Route::get('/reports/sst-02', [Tax::class, 'sst02']);

    // FIN-006 Treasury
    Route::get('/treasury/position', [Treasury::class, 'position']);
    Route::get('/treasury/forecast', [Treasury::class, 'forecast']);
    Route::get('/budgets/{id}/report', [Budget::class, 'report'])->whereNumber('id');
});
