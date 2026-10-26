<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Marketing\Http\Controllers\CampaignController;
use App\Modules\Marketing\Http\Controllers\SegmentController;
use App\Modules\Marketing\Http\Controllers\ContactController;
use App\Modules\Marketing\Http\Controllers\VoucherController;
use App\Modules\Marketing\Http\Controllers\DealController;
use App\Modules\Marketing\Http\Controllers\SequenceController;
use App\Modules\Marketing\Http\Controllers\WorkflowController;
use App\Modules\Marketing\Http\Controllers\LeadScoreController;
use App\Modules\Marketing\Http\Controllers\AdIntegrationController;
use App\Modules\Marketing\Http\Controllers\AnalyticsController;

/*
|--------------------------------------------------------------------------
| B-Marketing Routes
|--------------------------------------------------------------------------
|
| Marketing Automation module — campaign management, segmentation,
| contact management, vouchers, deal tracking, email sequences,
| workflow automation, lead scoring, ad integration & analytics.
|
*/

// Campaigns
Route::get('/campaigns', [CampaignController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/campaigns/{id}', [CampaignController::class, 'show'])->middleware(['permission:marketing.view']);
Route::post('/campaigns', [CampaignController::class, 'store'])->middleware(['permission:marketing.manage']);
Route::put('/campaigns/{id}', [CampaignController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::delete('/campaigns/{id}', [CampaignController::class, 'destroy'])->middleware(['permission:marketing.manage']);
Route::post('/campaigns/{id}/start', [CampaignController::class, 'start'])->middleware(['permission:marketing.approve']);
Route::post('/campaigns/{id}/pause', [CampaignController::class, 'pause'])->middleware(['permission:marketing.approve']);
Route::post('/campaigns/{id}/complete', [CampaignController::class, 'complete'])->middleware(['permission:marketing.approve']);
Route::get('/campaigns/stats', [CampaignController::class, 'stats'])->middleware(['permission:marketing.reports']);

// Segments
Route::get('/segments', [SegmentController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/segments/{id}', [SegmentController::class, 'show'])->middleware(['permission:marketing.view']);
Route::post('/segments', [SegmentController::class, 'store'])->middleware(['permission:marketing.manage']);
Route::put('/segments/{id}', [SegmentController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::delete('/segments/{id}', [SegmentController::class, 'destroy'])->middleware(['permission:marketing.manage']);
Route::post('/segments/{id}/toggle', [SegmentController::class, 'toggleActive'])->middleware(['permission:marketing.manage']);
Route::post('/segments/{id}/add-contact', [SegmentController::class, 'addContact'])->middleware(['permission:marketing.manage']);
Route::post('/segments/{id}/remove-contact', [SegmentController::class, 'removeContact'])->middleware(['permission:marketing.manage']);
Route::post('/segments/{id}/calculate', [SegmentController::class, 'calculateCount'])->middleware(['permission:marketing.reports']);

// Contacts
Route::get('/contacts', [ContactController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/contacts/{id}', [ContactController::class, 'show'])->middleware(['permission:marketing.view']);
Route::post('/contacts', [ContactController::class, 'store'])->middleware(['permission:marketing.manage']);
Route::put('/contacts/{id}', [ContactController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::delete('/contacts/{id}', [ContactController::class, 'destroy'])->middleware(['permission:marketing.manage']);
Route::post('/contacts/{id}/soft-delete', [ContactController::class, 'softDelete'])->middleware(['permission:marketing.manage']);
Route::post('/contacts/{id}/add-tags', [ContactController::class, 'addTags'])->middleware(['permission:marketing.manage']);
Route::post('/contacts/{id}/remove-tags', [ContactController::class, 'removeTags'])->middleware(['permission:marketing.manage']);
Route::post('/contacts/{id}/set-opt-in', [ContactController::class, 'setOptIn'])->middleware(['permission:marketing.manage']);
Route::get('/contacts/{id}/opt-in', [ContactController::class, 'checkOptIn'])->middleware(['permission:marketing.view']);
Route::post('/contacts/sync', [ContactController::class, 'sync'])->middleware(['permission:marketing.manage']);
Route::post('/contacts/{id}/activity', [ContactController::class, 'updateActivity'])->middleware(['permission:marketing.manage']);
Route::get('/contacts/stats', [ContactController::class, 'stats'])->middleware(['permission:marketing.reports']);

// Vouchers
Route::get('/vouchers', [VoucherController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/vouchers/{id}', [VoucherController::class, 'show'])->middleware(['permission:marketing.view']);
Route::post('/vouchers', [VoucherController::class, 'store'])->middleware(['permission:marketing.manage']);
Route::put('/vouchers/{id}', [VoucherController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::delete('/vouchers/{id}', [VoucherController::class, 'destroy'])->middleware(['permission:marketing.manage']);
Route::post('/vouchers/{id}/activate', [VoucherController::class, 'activate'])->middleware(['permission:marketing.manage']);
Route::post('/vouchers/{id}/deactivate', [VoucherController::class, 'deactivate'])->middleware(['permission:marketing.manage']);
Route::post('/vouchers/validate', [VoucherController::class, 'validate'])->middleware(['permission:marketing.manage']);
Route::post('/vouchers/{id}/use', [VoucherController::class, 'use'])->middleware(['permission:marketing.manage']);
Route::post('/vouchers/generate-codes', [VoucherController::class, 'generateCodes'])->middleware(['permission:marketing.manage']);
Route::get('/vouchers/stats', [VoucherController::class, 'stats'])->middleware(['permission:marketing.reports']);

// Deals
Route::get('/deals', [DealController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/deals/{id}', [DealController::class, 'show'])->middleware(['permission:marketing.view']);
Route::post('/deals', [DealController::class, 'store'])->middleware(['permission:marketing.manage']);
Route::put('/deals/{id}', [DealController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::delete('/deals/{id}', [DealController::class, 'destroy'])->middleware(['permission:marketing.manage']);
Route::post('/deals/{id}/move-stage', [DealController::class, 'moveStage'])->middleware(['permission:marketing.manage']);
Route::post('/deals/{id}/activity', [DealController::class, 'addActivity'])->middleware(['permission:marketing.manage']);
Route::get('/deals/{id}/activities', [DealController::class, 'getActivities'])->middleware(['permission:marketing.view']);
Route::post('/deals/{id}/won', [DealController::class, 'closeAsWon'])->middleware(['permission:marketing.approve']);
Route::post('/deals/{id}/lost', [DealController::class, 'closeAsLost'])->middleware(['permission:marketing.approve']);
Route::get('/deals/stats', [DealController::class, 'stats'])->middleware(['permission:marketing.reports']);
Route::get('/deals/forecast', [DealController::class, 'forecast'])->middleware(['permission:marketing.reports']);
Route::get('/deals/overdue', [DealController::class, 'getOverdue'])->middleware(['permission:marketing.reports']);

// Sequences
Route::get('/sequences', [SequenceController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/sequences/{id}', [SequenceController::class, 'show'])->middleware(['permission:marketing.view']);
Route::post('/sequences', [SequenceController::class, 'store'])->middleware(['permission:marketing.manage']);
Route::put('/sequences/{id}', [SequenceController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::delete('/sequences/{id}', [SequenceController::class, 'destroy'])->middleware(['permission:marketing.manage']);
Route::post('/sequences/{id}/toggle', [SequenceController::class, 'toggleActive'])->middleware(['permission:marketing.manage']);
Route::post('/sequences/{id}/start-execution', [SequenceController::class, 'startExecution'])->middleware(['permission:marketing.manage']);
Route::post('/sequences/{id}/execute', [SequenceController::class, 'execute'])->middleware(['permission:marketing.manage']);
Route::get('/sequences/stats', [SequenceController::class, 'stats'])->middleware(['permission:marketing.reports']);

// Workflows
Route::get('/workflows', [WorkflowController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/workflows/{id}', [WorkflowController::class, 'show'])->middleware(['permission:marketing.view']);
Route::post('/workflows', [WorkflowController::class, 'store'])->middleware(['permission:marketing.manage']);
Route::put('/workflows/{id}', [WorkflowController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::delete('/workflows/{id}', [WorkflowController::class, 'destroy'])->middleware(['permission:marketing.manage']);
Route::post('/workflows/{id}/toggle', [WorkflowController::class, 'toggleActive'])->middleware(['permission:marketing.manage']);
Route::post('/workflows/{id}/trigger', [WorkflowController::class, 'trigger'])->middleware(['permission:marketing.manage']);
Route::post('/workflows/{id}/execute', [WorkflowController::class, 'execute'])->middleware(['permission:marketing.manage']);
Route::get('/workflows/stats', [WorkflowController::class, 'stats'])->middleware(['permission:marketing.reports']);

// Lead Scores
Route::get('/lead-scores', [LeadScoreController::class, 'index'])->middleware(['permission:marketing.view']);
Route::get('/lead-scores/{id}', [LeadScoreController::class, 'show'])->middleware(['permission:marketing.view']);
Route::get('/lead-scores/contact/{contactId}', [LeadScoreController::class, 'showForContact'])->middleware(['permission:marketing.view']);
Route::post('/lead-scores/contact/{contactId}/calculate', [LeadScoreController::class, 'calculate'])->middleware(['permission:marketing.manage']);
Route::put('/lead-scores/contact/{contactId}', [LeadScoreController::class, 'update'])->middleware(['permission:marketing.manage']);
Route::get('/lead-scores/top', [LeadScoreController::class, 'topLeads'])->middleware(['permission:marketing.reports']);
Route::get('/lead-scores/by-grade', [LeadScoreController::class, 'byGrade'])->middleware(['permission:marketing.reports']);
Route::get('/lead-scores/distribution', [LeadScoreController::class, 'distribution'])->middleware(['permission:marketing.reports']);
Route::get('/lead-scores/averages', [LeadScoreController::class, 'averages'])->middleware(['permission:marketing.reports']);
Route::post('/lead-scores/bulk-recalculate', [LeadScoreController::class, 'bulkRecalculate'])->middleware(['permission:marketing.manage']);
Route::get('/lead-scores/stats', [LeadScoreController::class, 'stats'])->middleware(['permission:marketing.reports']);

// Ad Integration
Route::post('/ads/sync-google', [AdIntegrationController::class, 'syncGoogle'])->middleware(['permission:marketing.manage']);
Route::post('/ads/sync-facebook', [AdIntegrationController::class, 'syncFacebook'])->middleware(['permission:marketing.manage']);
Route::post('/ads/google-campaign', [AdIntegrationController::class, 'createGoogleCampaign'])->middleware(['permission:marketing.manage']);
Route::post('/ads/facebook-campaign', [AdIntegrationController::class, 'createFacebookCampaign'])->middleware(['permission:marketing.manage']);
Route::get('/ads/performance', [AdIntegrationController::class, 'getPerformance'])->middleware(['permission:marketing.reports']);
Route::post('/ads/validate-credentials', [AdIntegrationController::class, 'validateCredentials'])->middleware(['permission:marketing.manage']);
Route::post('/ads/pause-all', [AdIntegrationController::class, 'pauseAll'])->middleware(['permission:marketing.approve']);
Route::post('/ads/resume-all', [AdIntegrationController::class, 'resumeAll'])->middleware(['permission:marketing.approve']);

// Analytics
Route::get('/analytics/campaigns', [AnalyticsController::class, 'getCampaignAnalytics'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/channels', [AnalyticsController::class, 'getChannelBreakdown'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/trends', [AnalyticsController::class, 'getTimeSeriesData'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/roi', [AnalyticsController::class, 'calculateROI'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/attribution', [AnalyticsController::class, 'getAttribution'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/acquisition', [AnalyticsController::class, 'getAcquisitionMetrics'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/vouchers', [AnalyticsController::class, 'getVoucherAnalytics'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/funnel', [AnalyticsController::class, 'getFunnelMetrics'])->middleware(['permission:marketing.reports']);
Route::get('/analytics/dashboard', [AnalyticsController::class, 'getDashboard'])->middleware(['permission:marketing.reports']);
