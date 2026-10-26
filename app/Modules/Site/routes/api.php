<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Site\Http\Controllers\SiteController;
use App\Modules\Site\Http\Controllers\PageController;
use App\Modules\Site\Http\Controllers\ThemeController;
use App\Modules\Site\Http\Controllers\PostController;
use App\Modules\Site\Http\Controllers\FormController;
use App\Modules\Site\Http\Controllers\DomainController;
use App\Modules\Site\Http\Controllers\AnalyticsController;

// B-Site Routes — Website Builder (Shopify Replacement)
// Site management routes
    Route::prefix('sites')->name('sites.')->group(function () {
        Route::get('/', [SiteController::class, 'index'])->middleware('perm:site.view');
        Route::post('/', [SiteController::class, 'store'])->middleware('perm:site.manage');
        Route::get('/{id}', [SiteController::class, 'show'])->middleware('perm:site.view');
        Route::put('/{id}', [SiteController::class, 'update'])->middleware('perm:site.manage');
        Route::delete('/{id}', [SiteController::class, 'destroy'])->middleware('perm:site.manage');
        Route::post('/{id}/toggle-live', [SiteController::class, 'toggleLive'])->middleware('perm:site.manage');
        Route::post('/{id}/toggle-maintenance', [SiteController::class, 'toggleMaintenance'])->middleware('perm:site.manage');
        Route::post('/{id}/set-primary-domain', [SiteController::class, 'setPrimaryDomain'])->middleware('perm:site.manage');
        
        // Get site by slug
        Route::get('/slug/{slug}', [SiteController::class, 'getBySlug'])->middleware('perm:site.view');
    });

    // Page management routes
    Route::prefix('sites/{siteId}/pages')->name('pages.')->group(function () {
        Route::get('/', [PageController::class, 'index'])->middleware('perm:site.view');
        Route::post('/', [PageController::class, 'store'])->middleware('perm:site.manage');
        Route::get('/{id}', [PageController::class, 'show'])->middleware('perm:site.view');
        Route::put('/{id}', [PageController::class, 'update'])->middleware('perm:site.manage');
        Route::delete('/{id}', [PageController::class, 'destroy'])->middleware('perm:site.manage');
        Route::post('/{id}/publish', [PageController::class, 'publish'])->middleware('perm:site.manage');
        Route::post('/{id}/unpublish', [PageController::class, 'unpublish'])->middleware('perm:site.manage');
        Route::post('/{id}/duplicate', [PageController::class, 'duplicate'])->middleware('perm:site.manage');
        
        // Get page by slug
        Route::get('/slug/{slug}', [PageController::class, 'getBySlug'])->middleware('perm:site.view');
    });

    // Theme management routes
    Route::prefix('themes')->name('themes.')->group(function () {
        Route::get('/', [ThemeController::class, 'index'])->middleware('perm:site.view');
        Route::post('/', [ThemeController::class, 'store'])->middleware('perm:site.manage');
        Route::get('/{id}', [ThemeController::class, 'show'])->middleware('perm:site.view');
        Route::put('/{id}', [ThemeController::class, 'update'])->middleware('perm:site.manage');
        Route::delete('/{id}', [ThemeController::class, 'destroy'])->middleware('perm:site.manage');
        Route::post('/{id}/activate', [ThemeController::class, 'activate'])->middleware('perm:site.manage');
        Route::post('/{id}/apply-settings', [ThemeController::class, 'applySettings'])->middleware('perm:site.manage');
        
        // Get active theme for site
        Route::get('/sites/{siteId}/active', [ThemeController::class, 'getActive'])->middleware('perm:site.view');
    });

    // Post (Blog) management routes
    Route::prefix('sites/{siteId}/posts')->name('posts.')->group(function () {
        Route::get('/', [PostController::class, 'index'])->middleware('perm:site.view');
        Route::post('/', [PostController::class, 'store'])->middleware('perm:site.manage');
        Route::get('/{id}', [PostController::class, 'show'])->middleware('perm:site.view');
        Route::put('/{id}', [PostController::class, 'update'])->middleware('perm:site.manage');
        Route::delete('/{id}', [PostController::class, 'destroy'])->middleware('perm:site.manage');
        Route::post('/{id}/publish', [PostController::class, 'publish'])->middleware('perm:site.manage');
        Route::post('/{id}/schedule', [PostController::class, 'schedule'])->middleware('perm:site.manage');
        
        // Recent published posts
        Route::get('/recent', [PostController::class, 'recent'])->middleware('perm:site.view');
        
        // Get post by slug
        Route::get('/slug/{slug}', [PostController::class, 'getBySlug'])->middleware('perm:site.view');
    });

    // Form management routes
    Route::prefix('sites/{siteId}/forms')->name('forms.')->group(function () {
        Route::get('/', [FormController::class, 'index'])->middleware('perm:site.view');
        Route::post('/', [FormController::class, 'store'])->middleware('perm:site.manage');
        Route::get('/{id}', [FormController::class, 'show'])->middleware('perm:site.view');
        Route::put('/{id}', [FormController::class, 'update'])->middleware('perm:site.manage');
        Route::delete('/{id}', [FormController::class, 'destroy'])->middleware('perm:site.manage');
        Route::post('/{id}/toggle-active', [FormController::class, 'toggleActive'])->middleware('perm:site.manage');
        Route::get('/{id}/submissions', [FormController::class, 'submissions'])->middleware('perm:site.view');
        
        // Public form submission endpoint
        Route::post('/{id}/submit', [FormController::class, 'submit']);
    });

    // Domain management routes
    Route::prefix('domains')->name('domains.')->group(function () {
        Route::get('/', [DomainController::class, 'index'])->middleware('perm:site.view');
        Route::post('/', [DomainController::class, 'store'])->middleware('perm:site.manage');
        Route::get('/{id}', [DomainController::class, 'show'])->middleware('perm:site.view');
        Route::put('/{id}', [DomainController::class, 'update'])->middleware('perm:site.manage');
        Route::delete('/{id}', [DomainController::class, 'destroy'])->middleware('perm:site.manage');
        Route::post('/{id}/set-primary', [DomainController::class, 'setPrimary'])->middleware('perm:site.manage');
        Route::post('/{id}/enable-ssl', [DomainController::class, 'enableSsl'])->middleware('perm:site.manage');
        Route::post('/{id}/verify-ssl', [DomainController::class, 'verifySsl'])->middleware('perm:site.manage');
        Route::post('/{id}/verify', [DomainController::class, 'verify'])->middleware('perm:site.manage');
        Route::post('/{id}/generate-verification-code', [DomainController::class, 'generateVerificationCode'])->middleware('perm:site.manage');
        Route::post('/{id}/check-dns', [DomainController::class, 'checkDns'])->middleware('perm:site.manage');
        
        // Get domains for site
        Route::get('/sites/{siteId}', [DomainController::class, 'indexForSite'])->middleware('perm:site.view');
    });

    // Analytics routes
    Route::prefix('sites/{siteId}/analytics')->name('analytics.')->group(function () {
        Route::get('/page-views', [AnalyticsController::class, 'getPageViews'])->middleware('perm:site.reports');
        Route::get('/traffic-stats', [AnalyticsController::class, 'getTrafficStats'])->middleware('perm:site.reports');
        Route::get('/form-stats', [AnalyticsController::class, 'getFormStats'])->middleware('perm:site.reports');
        Route::get('/top-pages', [AnalyticsController::class, 'getTopPages'])->middleware('perm:site.reports');
        Route::get('/top-posts', [AnalyticsController::class, 'getTopPosts'])->middleware('perm:site.reports');
        Route::get('/conversion-rate', [AnalyticsController::class, 'getConversionRate'])->middleware('perm:site.reports');
        Route::get('/visitor-locations', [AnalyticsController::class, 'getVisitorLocations'])->middleware('perm:site.reports');
        Route::get('/referrers', [AnalyticsController::class, 'getReferrers'])->middleware('perm:site.reports');
        Route::get('/dashboard', [AnalyticsController::class, 'dashboard'])->middleware('perm:site.reports');
    });
