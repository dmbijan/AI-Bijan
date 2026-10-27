<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Invoicing\Http\Controllers\QuotationController;
use App\Modules\Invoicing\Http\Controllers\InvoiceController;
use App\Modules\Invoicing\Http\Controllers\CreditNoteController;
use App\Modules\Invoicing\Http\Controllers\RecurringBillingController;

/*
|--------------------------------------------------------------------------
| B-Invoicing API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register the B-Invoicing routes for your application.
| 
| Modules: Quotation, Sales Order, Invoice, Credit Note, Debit Note, Receipt, Recurring Billing
| Malaysian Compliance: e-Invois LHDN integration
|
*/

/*
| T-105 (28 Okt 2026): laluan Invoicing hanya ada `auth:sanctum` + `tenant` —
| tiada pagar kebenaran, jadi `cashier`/`staff` boleh lulus quotation, rekod
| bayaran dan hantar e-Invois ke LHDN. Kini:
|   invoicing.view    → baca
|   invoicing.manage  → tulis/ubah
|   invoicing.approve → submit/payment/e-Invois/cancel (tindakan kewangan)
*/

// Quotation Routes
Route::prefix('quotations')->name('quotations.')->middleware(['auth:sanctum'])->group(function () {
    Route::middleware('perm:invoicing.view')->group(function () {
        Route::get('/', [QuotationController::class, 'index'])->name('index');
        Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
    });

    Route::middleware('perm:invoicing.manage')->group(function () {
        Route::post('/', [QuotationController::class, 'store'])->name('store');
        Route::put('/{quotation}', [QuotationController::class, 'update'])->name('update');
        Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
    });

    // Tindakan — meluluskan/hantar/menukar kepada pesanan
    Route::middleware('perm:invoicing.approve')->group(function () {
        Route::post('/{quotation}/submit', [QuotationController::class, 'submit'])->name('submit');
        Route::post('/{quotation}/accept', [QuotationController::class, 'accept'])->name('accept');
        Route::post('/{quotation}/reject', [QuotationController::class, 'reject'])->name('reject');
        Route::post('/{quotation}/convert-to-order', [QuotationController::class, 'convertToOrder'])->name('convert-to-order');
    });
});

// Invoice Routes
Route::prefix('invoices')->name('invoices.')->middleware(['auth:sanctum'])->group(function () {
    Route::middleware('perm:invoicing.view')->group(function () {
        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/statistics', [InvoiceController::class, 'statistics'])->name('statistics');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('download-pdf');
    });

    Route::post('/from-order/{orderId}', [InvoiceController::class, 'createFromOrder'])
        ->middleware('perm:invoicing.manage')->name('create-from-order');

    // Tindakan kewangan + pematuhan LHDN
    Route::middleware('perm:invoicing.approve')->group(function () {
        Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('record-payment');
        Route::post('/{invoice}/submit-einvois', [InvoiceController::class, 'submitEInvois'])->name('submit-einvois');
        Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
    });
});

// Credit Note Routes
Route::prefix('credit-notes')->name('credit-notes.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [CreditNoteController::class, 'index'])->middleware('perm:invoicing.view')->name('index');
    Route::post('/', [CreditNoteController::class, 'store'])->middleware('perm:invoicing.manage')->name('store');
    Route::post('/{creditNote}/apply', [CreditNoteController::class, 'apply'])->middleware('perm:invoicing.approve')->name('apply');
});

// Recurring Billing Routes
Route::prefix('recurring-billings')->name('recurring-billings.')->middleware(['auth:sanctum'])->group(function () {
    Route::middleware('perm:invoicing.view')->group(function () {
        Route::get('/', [RecurringBillingController::class, 'index'])->name('index');
        Route::get('/upcoming', [RecurringBillingController::class, 'upcoming'])->name('upcoming');
    });

    Route::middleware('perm:invoicing.manage')->group(function () {
        Route::post('/', [RecurringBillingController::class, 'store'])->name('store');
        Route::put('/{billing}', [RecurringBillingController::class, 'update'])->name('update');
    });

    Route::middleware('perm:invoicing.approve')->group(function () {
        Route::post('/{billing}/pause', [RecurringBillingController::class, 'pause'])->name('pause');
        Route::post('/{billing}/resume', [RecurringBillingController::class, 'resume'])->name('resume');
        Route::post('/{billing}/cancel', [RecurringBillingController::class, 'cancel'])->name('cancel');
        Route::post('/{billing}/process-now', [RecurringBillingController::class, 'processNow'])->name('process-now');
    });
});
