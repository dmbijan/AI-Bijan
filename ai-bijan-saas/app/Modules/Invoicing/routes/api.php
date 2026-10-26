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

// Quotation Routes
Route::prefix('quotations')->name('quotations.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [QuotationController::class, 'index'])->name('index');
    Route::post('/', [QuotationController::class, 'store'])->name('store');
    Route::get('/{quotation}', [QuotationController::class, 'show'])->name('show');
    Route::put('/{quotation}', [QuotationController::class, 'update'])->name('update');
    
    // Actions
    Route::post('/{quotation}/submit', [QuotationController::class, 'submit'])->name('submit');
    Route::post('/{quotation}/accept', [QuotationController::class, 'accept'])->name('accept');
    Route::post('/{quotation}/reject', [QuotationController::class, 'reject'])->name('reject');
    Route::post('/{quotation}/convert-to-order', [QuotationController::class, 'convertToOrder'])->name('convert-to-order');
    
    Route::delete('/{quotation}', [QuotationController::class, 'destroy'])->name('destroy');
});

// Invoice Routes
Route::prefix('invoices')->name('invoices.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('index');
    Route::get('/statistics', [InvoiceController::class, 'statistics'])->name('statistics');
    Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
    
    // Create from sales order
    Route::post('/from-order/{orderId}', [InvoiceController::class, 'createFromOrder'])->name('create-from-order');
    
    // Payment actions
    Route::post('/{invoice}/payment', [InvoiceController::class, 'recordPayment'])->name('record-payment');
    
    // e-Invois actions
    Route::post('/{invoice}/submit-einvois', [InvoiceController::class, 'submitEInvois'])->name('submit-einvois');
    
    // Other actions
    Route::post('/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('cancel');
    Route::get('/{invoice}/download-pdf', [InvoiceController::class, 'downloadPdf'])->name('download-pdf');
});

// Credit Note Routes
Route::prefix('credit-notes')->name('credit-notes.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [CreditNoteController::class, 'index'])->name('index');
    Route::post('/', [CreditNoteController::class, 'store'])->name('store');
    Route::post('/{creditNote}/apply', [CreditNoteController::class, 'apply'])->name('apply');
});

// Recurring Billing Routes
Route::prefix('recurring-billings')->name('recurring-billings.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/', [RecurringBillingController::class, 'index'])->name('index');
    Route::get('/upcoming', [RecurringBillingController::class, 'upcoming'])->name('upcoming');
    Route::post('/', [RecurringBillingController::class, 'store'])->name('store');
    Route::put('/{billing}', [RecurringBillingController::class, 'update'])->name('update');
    
    // Actions
    Route::post('/{billing}/pause', [RecurringBillingController::class, 'pause'])->name('pause');
    Route::post('/{billing}/resume', [RecurringBillingController::class, 'resume'])->name('resume');
    Route::post('/{billing}/cancel', [RecurringBillingController::class, 'cancel'])->name('cancel');
    Route::post('/{billing}/process-now', [RecurringBillingController::class, 'processNow'])->name('process-now');
});
