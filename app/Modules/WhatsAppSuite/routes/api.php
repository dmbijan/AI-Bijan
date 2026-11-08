<?php

use App\Modules\WhatsAppSuite\Http\Controllers\WhatsAppSuiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WhatsAppSuite Routes — automasi WhatsApp (asal: WasapBot MY)
|--------------------------------------------------------------------------
|
| Prefix: /api/v1/whatsapp-suite
|
*/

// Peranti
Route::get('/devices', [WhatsAppSuiteController::class, 'devices'])->middleware('perm:whatsapp_suite.view');
Route::post('/devices', [WhatsAppSuiteController::class, 'storeDevice'])->middleware('perm:whatsapp_suite.manage');
Route::post('/devices/{id}/warmup', [WhatsAppSuiteController::class, 'startWarmup'])->middleware('perm:whatsapp_suite.manage');

// Kontak & pengesahan
Route::get('/contacts', [WhatsAppSuiteController::class, 'contacts'])->middleware('perm:whatsapp_suite.view');
Route::post('/contacts', [WhatsAppSuiteController::class, 'storeContact'])->middleware('perm:whatsapp_suite.manage');
Route::post('/validate-numbers', [WhatsAppSuiteController::class, 'validateNumbers'])->middleware('perm:whatsapp_suite.execute');

// Autoreply
Route::get('/devices/{deviceId}/auto-replies', [WhatsAppSuiteController::class, 'autoReplies'])->middleware('perm:whatsapp_suite.view');
Route::post('/devices/{deviceId}/auto-replies', [WhatsAppSuiteController::class, 'storeAutoReply'])->middleware('perm:whatsapp_suite.manage');

// Kempen
Route::get('/campaigns', [WhatsAppSuiteController::class, 'campaigns'])->middleware('perm:whatsapp_suite.view');
Route::post('/campaigns', [WhatsAppSuiteController::class, 'storeCampaign'])->middleware('perm:whatsapp_suite.manage');
Route::post('/campaigns/{id}/run', [WhatsAppSuiteController::class, 'runCampaign'])->middleware('perm:whatsapp_suite.execute');

// Mesej
Route::get('/messages', [WhatsAppSuiteController::class, 'messages'])->middleware('perm:whatsapp_suite.view');
Route::post('/messages', [WhatsAppSuiteController::class, 'sendMessage'])->middleware('perm:whatsapp_suite.execute');

// Pemberitahuan pesanan
Route::get('/devices/{deviceId}/notifications', [WhatsAppSuiteController::class, 'orderNotifications'])->middleware('perm:whatsapp_suite.view');
Route::post('/devices/{deviceId}/notifications', [WhatsAppSuiteController::class, 'storeOrderNotification'])->middleware('perm:whatsapp_suite.manage');

// Ringkasan
Route::get('/summary', [WhatsAppSuiteController::class, 'summary'])->middleware('perm:whatsapp_suite.view');
