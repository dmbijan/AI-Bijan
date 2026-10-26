<?php

use Illuminate\Support\Facades\Route;
use App\Modules\CRM\Http\Controllers\CustomerController;
use App\Modules\CRM\Http\Controllers\LeadController;
use App\Modules\CRM\Http\Controllers\OpportunityController;
use App\Modules\CRM\Http\Controllers\QuotationController;

/*
|--------------------------------------------------------------------------
| B-CRM Routes
|--------------------------------------------------------------------------
|
| Customer Relationship Management & Sales Force Automation
| Malaysian compliance ready with SSM, SST, Tax ID support
|
*/

// Customers API
Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:crm.view');
Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:crm.manage');
Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:crm.view');
Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:crm.manage');
Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:crm.manage');

// Leads API
Route::get('/leads', [LeadController::class, 'index'])->middleware('permission:crm.view');
Route::post('/leads', [LeadController::class, 'store'])->middleware('permission:crm.manage');
Route::get('/leads/{lead}', [LeadController::class, 'show'])->middleware('permission:crm.view');
Route::put('/leads/{lead}', [LeadController::class, 'update'])->middleware('permission:crm.manage');
Route::post('/leads/{lead}/convert', [LeadController::class, 'convertToOpportunity'])->middleware('permission:crm.manage');
Route::post('/leads/{lead}/assign', [LeadController::class, 'assign'])->middleware('permission:crm.manage');
Route::get('/leads/statistics', [LeadController::class, 'statistics'])->middleware('permission:crm.reports');

// Opportunities API
Route::get('/opportunities', [OpportunityController::class, 'index'])->middleware('permission:crm.view');
Route::post('/opportunities', [OpportunityController::class, 'store'])->middleware('permission:crm.manage');
Route::get('/opportunities/{opportunity}', [OpportunityController::class, 'show'])->middleware('permission:crm.view');
Route::put('/opportunities/{opportunity}', [OpportunityController::class, 'update'])->middleware('permission:crm.manage');
Route::post('/opportunities/{opportunity}/assign', [OpportunityController::class, 'assign'])->middleware('permission:crm.manage');
Route::get('/opportunities/pipeline-summary', [OpportunityController::class, 'getPipelineSummary'])->middleware('permission:crm.reports');
Route::get('/opportunities/follow-ups', [OpportunityController::class, 'getFollowUps'])->middleware('permission:crm.view');

// Quotations API
Route::get('/quotations', [QuotationController::class, 'index'])->middleware('permission:crm.view');
Route::post('/quotations', [QuotationController::class, 'store'])->middleware('permission:crm.manage');
Route::get('/quotations/{quotation}', [QuotationController::class, 'show'])->middleware('permission:crm.view');
Route::put('/quotations/{quotation}', [QuotationController::class, 'update'])->middleware('permission:crm.manage');
Route::post('/quotations/{quotation}/submit', [QuotationController::class, 'submit'])->middleware('permission:crm.approve');
Route::post('/quotations/{quotation}/approve', [QuotationController::class, 'approve'])->middleware('permission:crm.approve');
Route::post('/quotations/{quotation}/convert-order', [QuotationController::class, 'convertToOrder'])->middleware('permission:crm.manage');
Route::get('/quotations/statistics', [QuotationController::class, 'getStatistics'])->middleware('permission:crm.reports');
