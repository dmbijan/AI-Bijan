<?php

use App\Http\Controllers\Api\V1\Admin\PlatformController;
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DocumentController;
use App\Http\Controllers\Api\V1\HelpdeskController;
use App\Http\Controllers\Api\V1\InventoryController as Inv;
use App\Http\Controllers\Api\V1\ManufacturingController as Mfg;
use App\Http\Controllers\Api\V1\ProjectController as Prj;
use App\Http\Controllers\Api\V1\TenantController;
use App\Modules\POS\Http\Controllers\CashShiftController;
use App\Modules\POS\Http\Controllers\RegisterController;
use App\Modules\POS\Http\Controllers\SaleController;
use App\Modules\Booking\Http\Controllers\AppointmentController;
use App\Modules\Booking\Http\Controllers\ServiceController;
use App\Modules\Booking\Http\Controllers\SlotController;
use App\Modules\Marketplace\Http\Controllers\ProductController as MktProduct;
use App\Modules\Marketplace\Http\Controllers\VendorController as MktVendor;
use App\Modules\Marketplace\Http\Controllers\OrderController as MktOrder;
use App\Modules\Marketplace\Http\Controllers\CartController as MktCart;
use App\Modules\Marketplace\Http\Controllers\ReviewController as MktReview;
use App\Modules\Site\Http\Controllers\SiteController as SiteCtrl;
use App\Modules\Site\Http\Controllers\PageController as PageCtrl;
use App\Modules\Site\Http\Controllers\ThemeController as ThemeCtrl;
use App\Modules\Site\Http\Controllers\PostController as PostCtrl;
use App\Modules\Site\Http\Controllers\FormController as FormCtrl;
use App\Modules\Site\Http\Controllers\DomainController as DomainCtrl;
use App\Modules\Site\Http\Controllers\AnalyticsController as AnalyticsCtrl;
use App\Modules\Marketing\Http\Controllers\CampaignController as MktCampCtrl;
use App\Modules\Marketing\Http\Controllers\SegmentController as MktSegCtrl;
use App\Modules\Marketing\Http\Controllers\ContactController as MktContCtrl;
use App\Modules\Marketing\Http\Controllers\VoucherController as MktVouCtrl;
use App\Modules\Marketing\Http\Controllers\DealController as MktDealCtrl;
use App\Modules\Marketing\Http\Controllers\SequenceController as MktSeqCtrl;
use App\Modules\Marketing\Http\Controllers\WorkflowController as MktWfCtrl;
use App\Modules\Marketing\Http\Controllers\LeadScoreController as MktLsCtrl;
use App\Modules\Marketing\Http\Controllers\AdIntegrationController as MktAdCtrl;
use App\Modules\Marketing\Http\Controllers\AnalyticsController as MktAnaCtrl;
use App\Modules\Loyalty\Http\Controllers\MemberController as LoyMemCtrl;
use App\Modules\Loyalty\Http\Controllers\PointsController as LoyPtsCtrl;
use App\Modules\Loyalty\Http\Controllers\RewardController as LoyRewCtrl;
use App\Modules\Loyalty\Http\Controllers\TierController as LoyTierCtrl;
use App\Modules\Loyalty\Http\Controllers\LoyaltyAnalyticsController as LoyAnaCtrl;
use App\Modules\Affiliate\Http\Controllers\AffiliateController as AffCtrl;
use App\Modules\Affiliate\Http\Controllers\ReferralController as RefCtrl;
use App\Modules\Affiliate\Http\Controllers\CommissionController as ComCtrl;
use App\Modules\Affiliate\Http\Controllers\PayoutController as PayCtrl;
use App\Modules\Affiliate\Http\Controllers\AffiliateAnalyticsController as AnaCtrl;
use App\Modules\Link\Http\Controllers\LinkController as LinkCtrl;
use App\Modules\Link\Http\Controllers\DigitalCardController as CardCtrl;
use App\Modules\SignDocs\Http\Controllers\DocumentController as DocCtrl;
use App\Modules\SignDocs\Http\Controllers\TemplateController as TempCtrl;
use App\Modules\SignDocs\Http\Controllers\SignatureController as SigCtrl;
use App\Modules\Omnichannel\Http\Controllers\ChannelController;
use App\Modules\Omnichannel\Http\Controllers\ConversationController;
use App\Modules\Omnichannel\Http\Controllers\WebhookController;
use App\Modules\Logistics\Http\Controllers\WarehouseController;
use App\Modules\Logistics\Http\Controllers\StockMoveController;
use App\Modules\Logistics\Http\Controllers\ShipmentController;
use App\Modules\Analytics\Http\Controllers\MetricController;
use App\Modules\Analytics\Http\Controllers\ReportController;
use App\Modules\Analytics\Http\Controllers\DashboardController;
use App\Modules\BrainHub\Http\Controllers\InsightController as BrainInsightCtrl;
use App\Modules\BrainHub\Http\Controllers\ForecastController as BrainForecastCtrl;
use App\Modules\BrainHub\Http\Controllers\RecommendationController as BrainRecCtrl;
use App\Modules\COGS\Http\Controllers\CostCalculationController as COGSCalcCtrl;
use App\Modules\COGS\Http\Controllers\MarginReportController as COGSMarginCtrl;
use App\Modules\IndustryPacks\Http\Controllers\IndustryPackController as IPCtrl;
use App\Modules\AIAgents\Http\Controllers\AIAgentController as AIAgentCtrl;
use App\Modules\PersonalAssistant\Http\Controllers\PersonalAssistantController as PACtrl;
use App\Modules\Boardroom\Http\Controllers\BoardroomController as BoardroomCtrl;
use App\Modules\FlowAutomation\Http\Controllers\FlowAutomationController as FlowCtrl;
use App\Modules\AIGateway\Http\Controllers\AIGatewayController as AGCtrl;
use Illuminate\Support\Facades\Route;Route::prefix('v1')->group(function () {
// Awam (dengan had kadar)
Route::middleware('throttle:auth')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('invitations/accept', [AuthController::class, 'terimaJemputan']);
});
// Portal pelanggan helpdesk (awam, token peribadi, had kadar)
Route::prefix('public/{slug}/helpdesk')->middleware('throttle:30,1')->where(['slug' => '[a-z0-9-]+'])->group(function () {
    Route::post('tickets', [HelpdeskController::class, 'publicCreate'])->middleware('throttle:5,10');
    Route::get('tickets/{token}', [HelpdeskController::class, 'publicShow']);
    Route::post('tickets/{token}/reply', [HelpdeskController::class, 'publicReply'])->middleware('throttle:10,10');
    Route::get('articles', [HelpdeskController::class, 'publicArticles']);
});
// Webhook Vision Service (BAB 56C) — HMAC sahkan dalam controller, tiada sesi pengguna
Route::post('vision/ingest/{cameraId}', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'ingest'])
    ->middleware('throttle:120,1')->whereNumber('cameraId');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/tenant', [AuthController::class, 'pilihTenant']);
    Route::post('auth/2fa/setup', [AuthController::class, 'twoFactorSetup']);
    Route::post('auth/2fa/confirm', [AuthController::class, 'twoFactorConfirm']);
    // Dalam tenant
    Route::middleware('tenant')->group(function () {
        Route::get('tenant', [TenantController::class, 'show']);
        Route::get('tenant/settings', [TenantController::class, 'settings'])->middleware('perm:tenant.settings.view');
        Route::put('tenant/settings', [TenantController::class, 'updateSettings'])->middleware('perm:tenant.settings.update');
        Route::get('members', [TenantController::class, 'members'])->middleware('perm:members.view');
        Route::post('members/invite', [TenantController::class, 'invite'])->middleware('perm:members.invite');
        Route::put('members/{id}', [TenantController::class, 'updateMember'])->middleware('perm:members.manage')->whereNumber('id');
        Route::get('roles', [TenantController::class, 'roles'])->middleware('perm:members.view');
        Route::post('roles', [TenantController::class, 'saveRole'])->middleware('perm:roles.manage');
        Route::put('roles/{id}', [TenantController::class, 'saveRole'])->middleware('perm:roles.manage')->whereNumber('id');
        Route::get('audit', [TenantController::class, 'audit'])->middleware('perm:audit.view');
        Route::post('files', [TenantController::class, 'upload'])->middleware('perm:files.upload');
        Route::get('files/{ulid}', [TenantController::class, 'download'])->middleware('perm:files.view');
        Route::get('api-keys', [TenantController::class, 'apiKeys'])->middleware('perm:api_keys.manage');
        Route::post('api-keys', [TenantController::class, 'createApiKey'])->middleware('perm:api_keys.manage');
        Route::delete('api-keys/{id}', [TenantController::class, 'revokeApiKey'])->middleware('perm:api_keys.manage')->whereNumber('id');
        Route::get('approvals', [TenantController::class, 'approvals'])->middleware('perm:approvals.view');
        Route::post('approvals/{id}/decide', [TenantController::class, 'decide'])->middleware('perm:approvals.decide')->whereNumber('id');
        // Dokumen (kebenaran diperincikan dalam pengawal: sulit vs biasa, atau pemilik dokumen)
        Route::get('documents/catalog', [DocumentController::class, 'catalog']);
        Route::get('documents', [DocumentController::class, 'index']);
        Route::post('documents', [DocumentController::class, 'store']);
        Route::post('documents/preview', [DocumentController::class, 'preview']);
        Route::get('documents/{ulid}', [DocumentController::class, 'show']);
        Route::get('documents/{ulid}/download/{format}', [DocumentController::class, 'download'])->where('format', 'pdf|docx|odt|xlsx|ods|csv|html|json');
        Route::post('documents/{ulid}/void', [DocumentController::class, 'void']);
        // B-Inventory & Warehouse
        Route::middleware('perm:inventory.view')->group(function () {
            Route::get('inventory/warehouses', [Inv::class, 'warehouses']);
            Route::get('inventory/items', [Inv::class, 'items']);
            Route::get('inventory/balances', [Inv::class, 'balances']);
            Route::get('inventory/items/{id}/ledger', [Inv::class, 'ledger'])->whereNumber('id');
            Route::get('inventory/movements', [Inv::class, 'movements']);
            Route::get('inventory/reports/{report}', [Inv::class, 'reports'])->where('report', 'valuation|low-stock|expiring|non-moving');
        });
        Route::middleware('perm:inventory.manage')->group(function () {
            Route::post('inventory/warehouses', [Inv::class, 'saveWarehouse']);
            Route::put('inventory/warehouses/{id}', [Inv::class, 'saveWarehouse'])->whereNumber('id');
            Route::post('inventory/items', [Inv::class, 'saveItem']);
            Route::put('inventory/items/{id}', [Inv::class, 'saveItem'])->whereNumber('id');
            Route::post('inventory/movements', [Inv::class, 'createMovement']);
            Route::post('inventory/movements/{id}/post', [Inv::class, 'post'])->whereNumber('id');
            Route::post('inventory/movements/{id}/receive', [Inv::class, 'receive'])->whereNumber('id');
            Route::post('inventory/movements/{id}/cancel', [Inv::class, 'cancel'])->whereNumber('id');
        });
        Route::put('inventory/valuation', [Inv::class, 'setValuation'])->middleware('perm:roles.manage');
        Route::post('inventory/sale-deduct', [Mfg::class, 'saleDeduct'])->middleware('perm:inventory.sell');

            // Katalog barang & gudang (modul Inventory)
            Route::middleware('perm:inventory.view')->group(function () {
                Route::get('inventory/catalog', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'index']);
                Route::get('inventory/catalog/reorder', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'reorder']);
                Route::get('inventory/catalog/{id}', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'show'])->whereNumber('id');
                Route::get('inventory/catalog/{id}/ledger', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'ledger'])->whereNumber('id');
                Route::get('inventory/catalog/{id}/stock', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'stock'])->whereNumber('id');
                Route::get('inventory/depots/{id}', [\App\Modules\Inventory\Http\Controllers\WarehouseController::class, 'show'])->whereNumber('id');
            });
            Route::middleware('perm:inventory.manage')->group(function () {
                Route::post('inventory/catalog', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'store']);
                Route::put('inventory/catalog/{id}', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'update'])->whereNumber('id');
                Route::delete('inventory/catalog/{id}', [\App\Modules\Inventory\Http\Controllers\ItemController::class, 'destroy'])->whereNumber('id');
                Route::post('inventory/depots', [\App\Modules\Inventory\Http\Controllers\WarehouseController::class, 'store']);
                Route::put('inventory/depots/{id}', [\App\Modules\Inventory\Http\Controllers\WarehouseController::class, 'update'])->whereNumber('id');
                Route::delete('inventory/depots/{id}', [\App\Modules\Inventory\Http\Controllers\WarehouseController::class, 'destroy'])->whereNumber('id');
            });
        // B-BOM & Manufacturing
        Route::middleware('perm:manufacturing.view')->group(function () {
            Route::get('manufacturing/boms', [Mfg::class, 'boms']);
            Route::get('manufacturing/boms/{id}', [Mfg::class, 'showBom'])->whereNumber('id');
            Route::get('manufacturing/work-orders', [Mfg::class, 'workOrders']);
        });
        Route::middleware('perm:manufacturing.manage')->group(function () {
            Route::post('manufacturing/boms', [Mfg::class, 'createBom']);
            Route::post('manufacturing/boms/{id}/deactivate', [Mfg::class, 'deactivateBom'])->whereNumber('id');
            Route::post('manufacturing/work-orders', [Mfg::class, 'createWorkOrder']);
            Route::post('manufacturing/work-orders/{id}/complete', [Mfg::class, 'completeWorkOrder'])->whereNumber('id');
            Route::post('manufacturing/work-orders/{id}/cancel', [Mfg::class, 'cancelWorkOrder'])->whereNumber('id');
        });

            // B-Projects (skop ahli projek dalam pengawal)
            Route::middleware('perm:projects.view')->group(function () {
                Route::get('projects', [Prj::class, 'index']);
                Route::post('projects', [Prj::class, 'save']);
                Route::put('projects/{id}', [Prj::class, 'save'])->whereNumber('id');
                Route::get('projects/{id}', [Prj::class, 'show'])->whereNumber('id');
                Route::get('projects/{id}/board', [Prj::class, 'board'])->whereNumber('id');
                Route::get('projects/{id}/gantt', [Prj::class, 'gantt'])->whereNumber('id');
                Route::post('projects/{projectId}/tasks', [Prj::class, 'saveTask'])->whereNumber('projectId');
                Route::put('projects/{projectId}/tasks/{id}', [Prj::class, 'saveTask'])->whereNumber(['projectId', 'id']);
                Route::post('tasks/{id}/status', [Prj::class, 'taskStatus'])->whereNumber('id');
                Route::post('tasks/{id}/dependencies', [Prj::class, 'addDependency'])->whereNumber('id');
                Route::get('tasks/{id}/comments', [Prj::class, 'comments'])->whereNumber('id');
                Route::post('tasks/{id}/comments', [Prj::class, 'comment'])->whereNumber('id');
                Route::get('projects/{projectId}/timesheets', [Prj::class, 'timesheets'])->whereNumber('projectId');
                Route::post('projects/{projectId}/timesheets', [Prj::class, 'logTime'])->whereNumber('projectId')->middleware('perm:projects.timesheet');
                Route::post('timesheets/{id}/decide', [Prj::class, 'decideTime'])->whereNumber('id');
            });
        // B-Assets
        Route::middleware('perm:assets.view')->group(function () {
            Route::get('assets', [AssetController::class, 'index']);
            Route::get('assets/categories', [AssetController::class, 'categories']);
            Route::get('assets/due', [AssetController::class, 'due']);
            Route::get('assets/{id}', [AssetController::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:assets.manage')->group(function () {
            Route::post('assets/categories', [AssetController::class, 'saveCategory']);
            Route::post('assets', [AssetController::class, 'store']);
            Route::post('assets/depreciation/post', [AssetController::class, 'postDepreciation']);
            Route::post('assets/{id}/move', [AssetController::class, 'move'])->whereNumber('id');
            Route::post('assets/{id}/dispose', [AssetController::class, 'dispose'])->whereNumber('id');
            Route::post('assets/{id}/maintenance', [AssetController::class, 'scheduleMaintenance'])->whereNumber('id');
            Route::post('assets/maintenance/{id}/complete', [AssetController::class, 'completeMaintenance'])->whereNumber('id');
        });
        // B-POS — Point of Sale
        Route::middleware('perm:pos.view')->group(function () {
            Route::get('pos/registers', [RegisterController::class, 'index']);
            Route::get('pos/registers/{id}', [RegisterController::class, 'show'])->whereNumber('id');
            Route::get('pos/shifts', [CashShiftController::class, 'index']);
            Route::get('pos/shifts/open/{registerId}', [CashShiftController::class, 'getOpen'])->whereNumber('registerId');
            Route::get('pos/shifts/{id}', [CashShiftController::class, 'show'])->whereNumber('id');
            Route::get('pos/sales', [SaleController::class, 'index']);
            Route::get('pos/sales/{id}', [SaleController::class, 'show'])->whereNumber('id');
            Route::get('pos/sales/summary', [SaleController::class, 'summary']);
            Route::get('pos/shifts/summary', [CashShiftController::class, 'summary']);
        });
        Route::middleware('perm:pos.manage')->group(function () {
            Route::post('pos/registers', [RegisterController::class, 'store']);
            Route::put('pos/registers/{id}', [RegisterController::class, 'update'])->whereNumber('id');
            Route::delete('pos/registers/{id}', [RegisterController::class, 'destroy'])->whereNumber('id');
            Route::post('pos/registers/{id}/toggle', [RegisterController::class, 'toggleActive'])->whereNumber('id');
            Route::get('pos/registers/{id}/summary', [RegisterController::class, 'salesSummary'])->whereNumber('id');
            Route::post('pos/shifts/open', [CashShiftController::class, 'open']);
            Route::post('pos/shifts/{id}/close', [CashShiftController::class, 'close'])->whereNumber('id');
            Route::post('pos/shifts/{id}/lock', [CashShiftController::class, 'lock'])->whereNumber('id');
            Route::post('pos/sales/draft', [SaleController::class, 'storeDraft']);
            Route::post('pos/sales/{saleId}/items', [SaleController::class, 'addItem'])->whereNumber('saleId');
            Route::put('pos/sales/items/{itemId}', [SaleController::class, 'updateItem'])->whereNumber('itemId');
            Route::delete('pos/sales/items/{itemId}', [SaleController::class, 'removeItem'])->whereNumber('itemId');
            Route::post('pos/sales/{saleId}/complete', [SaleController::class, 'complete'])->whereNumber('saleId');
            Route::post('pos/sales/{saleId}/cancel', [SaleController::class, 'cancel'])->whereNumber('saleId');
            Route::post('pos/sales/{saleId}/refund', [SaleController::class, 'refund'])->whereNumber('saleId');
        });
        // B-Booking — Service Booking & Appointments
        Route::middleware('perm:booking.view')->group(function () {
            Route::get('booking/services', [ServiceController::class, 'index']);
            Route::get('booking/services/{id}', [ServiceController::class, 'show'])->whereNumber('id');
            Route::get('booking/slots', [SlotController::class, 'index']);
            Route::get('booking/slots/daterange', [SlotController::class, 'byDateRange']);
            Route::get('booking/appointments', [AppointmentController::class, 'index']);
            Route::get('booking/appointments/{id}', [AppointmentController::class, 'show'])->whereNumber('id');
            Route::get('booking/appointments/summary', [AppointmentController::class, 'summary']);
        });
        Route::middleware('perm:booking.manage')->group(function () {
            Route::post('booking/services', [ServiceController::class, 'store']);
            Route::put('booking/services/{id}', [ServiceController::class, 'update'])->whereNumber('id');
            Route::delete('booking/services/{id}', [ServiceController::class, 'destroy'])->whereNumber('id');
            Route::post('booking/services/{id}/toggle', [ServiceController::class, 'toggleActive'])->whereNumber('id');
            Route::post('booking/slots/generate', [SlotController::class, 'generate']);
            Route::post('booking/slots/{id}/book', [SlotController::class, 'book'])->whereNumber('id');
            Route::post('booking/slots/{id}/cancel', [SlotController::class, 'cancel'])->whereNumber('id');
            Route::post('booking/slots/{id}/block', [SlotController::class, 'block'])->whereNumber('id');
            Route::post('booking/appointments', [AppointmentController::class, 'store']);
            Route::put('booking/appointments/{id}', [AppointmentController::class, 'update'])->whereNumber('id');
            Route::post('booking/appointments/{id}/confirm', [AppointmentController::class, 'confirm'])->whereNumber('id');
            Route::post('booking/appointments/{id}/start', [AppointmentController::class, 'start'])->whereNumber('id');
            Route::post('booking/appointments/{id}/complete', [AppointmentController::class, 'complete'])->whereNumber('id');
            Route::post('booking/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->whereNumber('id');
            Route::post('booking/appointments/{id}/no-show', [AppointmentController::class, 'markNoShow'])->whereNumber('id');
            Route::post('booking/appointments/{id}/reschedule', [AppointmentController::class, 'reschedule'])->whereNumber('id');
        });
        // ============================================            // B-Marketplace — E-commerce Marketplace            // ============================================
        // Products
        Route::middleware('perm:marketplace.view')->group(function () {
            Route::get('marketplace/products', [MktProduct::class, 'index']);
            Route::get('marketplace/products/featured', [MktProduct::class, 'featured']);
            Route::get('marketplace/products/search', [MktProduct::class, 'search']);
            Route::get('marketplace/products/low-stock', [MktProduct::class, 'lowStock']);
            Route::get('marketplace/products/{id}', [MktProduct::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:marketplace.manage')->group(function () {
            Route::post('marketplace/products', [MktProduct::class, 'store']);
            Route::put('marketplace/products/{id}', [MktProduct::class, 'update'])->whereNumber('id');
            Route::delete('marketplace/products/{id}', [MktProduct::class, 'destroy'])->whereNumber('id');
            Route::post('marketplace/products/{id}/stock', [MktProduct::class, 'updateStock'])->whereNumber('id');
            Route::post('marketplace/products/bulk-status', [MktProduct::class, 'bulkUpdateStatus']);
        });
        // Vendors
        Route::middleware('perm:marketplace.view')->group(function () {
            Route::get('marketplace/vendors', [MktVendor::class, 'index']);
            Route::get('marketplace/vendors/top', [MktVendor::class, 'topVendors']);
            Route::get('marketplace/vendors/{id}', [MktVendor::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:marketplace.manage')->group(function () {
            Route::post('marketplace/vendors', [MktVendor::class, 'store']);
            Route::put('marketplace/vendors/{id}', [MktVendor::class, 'update'])->whereNumber('id');
            Route::delete('marketplace/vendors/{id}', [MktVendor::class, 'destroy'])->whereNumber('id');
            Route::post('marketplace/vendors/{id}/approve', [MktVendor::class, 'approve'])->whereNumber('id');
            Route::post('marketplace/vendors/{id}/reject', [MktVendor::class, 'reject'])->whereNumber('id');
            Route::post('marketplace/vendors/{id}/toggle', [MktVendor::class, 'toggleActive'])->whereNumber('id');
        });
        // Orders
        Route::middleware('perm:marketplace.view')->group(function () {
            Route::get('marketplace/orders', [MktOrder::class, 'index']);
            Route::get('marketplace/orders/statistics', [MktOrder::class, 'statistics']);
            Route::get('marketplace/orders/{id}', [MktOrder::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:marketplace.manage')->group(function () {
            Route::post('marketplace/orders', [MktOrder::class, 'store']);
            Route::post('marketplace/orders/{id}/status', [MktOrder::class, 'updateStatus'])->whereNumber('id');
            Route::post('marketplace/orders/{id}/payment', [MktOrder::class, 'confirmPayment'])->whereNumber('id');
            Route::post('marketplace/orders/{id}/cancel', [MktOrder::class, 'cancel'])->whereNumber('id');
            Route::post('marketplace/orders/{id}/refund', [MktOrder::class, 'refund'])->whereNumber('id');
            Route::post('marketplace/orders/{id}/tracking', [MktOrder::class, 'updateTracking'])->whereNumber('id');
        });
        // Cart
        Route::middleware('perm:marketplace.view')->group(function () {
            Route::get('marketplace/cart', [MktCart::class, 'index']);
            Route::get('marketplace/cart/totals', [MktCart::class, 'totals']);
            Route::get('marketplace/cart/validate', [MktCart::class, 'validate']);
        });
        Route::middleware('perm:marketplace.manage')->group(function () {
            Route::post('marketplace/cart', [MktCart::class, 'store']);
            Route::put('marketplace/cart/{id}', [MktCart::class, 'update'])->whereNumber('id');
            Route::delete('marketplace/cart/{id}', [MktCart::class, 'destroy'])->whereNumber('id');
            Route::delete('marketplace/cart', [MktCart::class, 'clear']);
        });
        // Reviews
        Route::middleware('perm:marketplace.view')->group(function () {
            Route::get('marketplace/reviews/product/{productId}', [MktReview::class, 'index'])->whereNumber('productId');
            Route::get('marketplace/reviews/product/{productId}/statistics', [MktReview::class, 'statistics'])->whereNumber('productId');
            Route::get('marketplace/reviews/most-reviewed', [MktReview::class, 'mostReviewed']);
            Route::get('marketplace/reviews/pending', [MktReview::class, 'pending']);
        });
        Route::middleware('perm:marketplace.manage')->group(function () {
            Route::post('marketplace/reviews', [MktReview::class, 'store']);
            Route::put('marketplace/reviews/{id}', [MktReview::class, 'update'])->whereNumber('id');
            Route::delete('marketplace/reviews/{id}', [MktReview::class, 'destroy'])->whereNumber('id');
            Route::post('marketplace/reviews/{id}/approve', [MktReview::class, 'approve'])->whereNumber('id');
            Route::post('marketplace/reviews/{id}/reject', [MktReview::class, 'reject'])->whereNumber('id');
            Route::post('marketplace/reviews/{id}/helpful', [MktReview::class, 'markHelpful'])->whereNumber('id');
            Route::post('marketplace/reviews/bulk-approve', [MktReview::class, 'bulkApprove']);
            Route::post('marketplace/reviews/bulk-reject', [MktReview::class, 'bulkReject']);
        });
        // ============================================            // B-Site — Website Builder (Shopify Replacement)            // ============================================
        // Sites
        Route::middleware('perm:site.view')->group(function () {
            Route::get('site/sites', [SiteCtrl::class, 'index']);
            Route::get('site/sites/{id}', [SiteCtrl::class, 'show'])->whereNumber('id');
            Route::get('site/sites/slug/{slug}', [SiteCtrl::class, 'getBySlug']);
        });
        Route::middleware('perm:site.manage')->group(function () {
            Route::post('site/sites', [SiteCtrl::class, 'store']);
            Route::put('site/sites/{id}', [SiteCtrl::class, 'update'])->whereNumber('id');
            Route::delete('site/sites/{id}', [SiteCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('site/sites/{id}/toggle-live', [SiteCtrl::class, 'toggleLive'])->whereNumber('id');
            Route::post('site/sites/{id}/toggle-maintenance', [SiteCtrl::class, 'toggleMaintenance'])->whereNumber('id');
            Route::post('site/sites/{id}/set-primary-domain', [SiteCtrl::class, 'setPrimaryDomain'])->whereNumber('id');
        });
        // Pages
        Route::middleware('perm:site.view')->group(function () {
            Route::get('site/sites/{siteId}/pages', [PageCtrl::class, 'index'])->whereNumber('siteId');
            Route::get('site/pages/{id}', [PageCtrl::class, 'show'])->whereNumber('id');
            Route::get('site/sites/{siteId}/pages/slug/{slug}', [PageCtrl::class, 'getBySlug'])->whereNumber('siteId');
        });
        Route::middleware('perm:site.manage')->group(function () {
            Route::post('site/sites/{siteId}/pages', [PageCtrl::class, 'store'])->whereNumber('siteId');
            Route::put('site/pages/{id}', [PageCtrl::class, 'update'])->whereNumber('id');
            Route::delete('site/pages/{id}', [PageCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('site/pages/{id}/publish', [PageCtrl::class, 'publish'])->whereNumber('id');
            Route::post('site/pages/{id}/unpublish', [PageCtrl::class, 'unpublish'])->whereNumber('id');
            Route::post('site/pages/{id}/duplicate', [PageCtrl::class, 'duplicate'])->whereNumber('id');
        });
        // Themes
        Route::middleware('perm:site.view')->group(function () {
            Route::get('site/themes', [ThemeCtrl::class, 'index']);
            Route::get('site/themes/{id}', [ThemeCtrl::class, 'show'])->whereNumber('id');
            Route::get('site/sites/{siteId}/themes/active', [ThemeCtrl::class, 'getActive'])->whereNumber('siteId');
        });
        Route::middleware('perm:site.manage')->group(function () {
            Route::post('site/themes', [ThemeCtrl::class, 'store']);
            Route::put('site/themes/{id}', [ThemeCtrl::class, 'update'])->whereNumber('id');
            Route::delete('site/themes/{id}', [ThemeCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('site/themes/{id}/activate', [ThemeCtrl::class, 'activate'])->whereNumber('id');
            Route::post('site/themes/{id}/apply-settings', [ThemeCtrl::class, 'applySettings'])->whereNumber('id');
        });
        // Posts (Blog)
        Route::middleware('perm:site.view')->group(function () {
            Route::get('site/sites/{siteId}/posts', [PostCtrl::class, 'index'])->whereNumber('siteId');
            Route::get('site/posts/{id}', [PostCtrl::class, 'show'])->whereNumber('id');
            Route::get('site/sites/{siteId}/posts/slug/{slug}', [PostCtrl::class, 'getBySlug'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/posts/recent', [PostCtrl::class, 'recent'])->whereNumber('siteId');
        });
        Route::middleware('perm:site.manage')->group(function () {
            Route::post('site/sites/{siteId}/posts', [PostCtrl::class, 'store'])->whereNumber('siteId');
            Route::put('site/posts/{id}', [PostCtrl::class, 'update'])->whereNumber('id');
            Route::delete('site/posts/{id}', [PostCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('site/posts/{id}/publish', [PostCtrl::class, 'publish'])->whereNumber('id');
            Route::post('site/posts/{id}/schedule', [PostCtrl::class, 'schedule'])->whereNumber('id');
        });
        // Forms
        Route::middleware('perm:site.view')->group(function () {
            Route::get('site/sites/{siteId}/forms', [FormCtrl::class, 'index'])->whereNumber('siteId');
            Route::get('site/forms/{id}', [FormCtrl::class, 'show'])->whereNumber('id');
            Route::get('site/forms/{id}/submissions', [FormCtrl::class, 'submissions'])->whereNumber('id');
        });
        Route::middleware('perm:site.manage')->group(function () {
            Route::post('site/sites/{siteId}/forms', [FormCtrl::class, 'store'])->whereNumber('siteId');
            Route::put('site/forms/{id}', [FormCtrl::class, 'update'])->whereNumber('id');
            Route::delete('site/forms/{id}', [FormCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('site/forms/{id}/toggle-active', [FormCtrl::class, 'toggleActive'])->whereNumber('id');
        });
        // Public form submission
        Route::post('site/forms/{id}/submit', [FormCtrl::class, 'submit'])->whereNumber('id');
        // Domains
        Route::middleware('perm:site.view')->group(function () {
            Route::get('site/domains', [DomainCtrl::class, 'index']);
            Route::get('site/domains/sites/{siteId}', [DomainCtrl::class, 'indexForSite'])->whereNumber('siteId');
            Route::get('site/domains/{id}', [DomainCtrl::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:site.manage')->group(function () {
            Route::post('site/domains', [DomainCtrl::class, 'store']);
            Route::put('site/domains/{id}', [DomainCtrl::class, 'update'])->whereNumber('id');
            Route::delete('site/domains/{id}', [DomainCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('site/domains/{id}/set-primary', [DomainCtrl::class, 'setPrimary'])->whereNumber('id');
            Route::post('site/domains/{id}/enable-ssl', [DomainCtrl::class, 'enableSsl'])->whereNumber('id');
            Route::post('site/domains/{id}/verify-ssl', [DomainCtrl::class, 'verifySsl'])->whereNumber('id');
            Route::post('site/domains/{id}/verify', [DomainCtrl::class, 'verify'])->whereNumber('id');
            Route::post('site/domains/{id}/generate-verification-code', [DomainCtrl::class, 'generateVerificationCode'])->whereNumber('id');
            Route::post('site/domains/{id}/check-dns', [DomainCtrl::class, 'checkDns'])->whereNumber('id');
        });
        // Analytics
        Route::middleware('perm:site.reports')->group(function () {
            Route::get('site/sites/{siteId}/analytics/page-views', [AnalyticsCtrl::class, 'getPageViews'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/traffic-stats', [AnalyticsCtrl::class, 'getTrafficStats'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/form-stats', [AnalyticsCtrl::class, 'getFormStats'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/top-pages', [AnalyticsCtrl::class, 'getTopPages'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/top-posts', [AnalyticsCtrl::class, 'getTopPosts'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/conversion-rate', [AnalyticsCtrl::class, 'getConversionRate'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/visitor-locations', [AnalyticsCtrl::class, 'getVisitorLocations'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/referrers', [AnalyticsCtrl::class, 'getReferrers'])->whereNumber('siteId');
            Route::get('site/sites/{siteId}/analytics/dashboard', [AnalyticsCtrl::class, 'dashboard'])->whereNumber('siteId');
        });
        // ============================================            // B-Marketing — Marketing Automation (HubSpot Replacement)            // ============================================
        // Campaigns
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/campaigns', [MktCampCtrl::class, 'index']);
            Route::get('marketing/campaigns/{id}', [MktCampCtrl::class, 'show'])->whereNumber('id');
            Route::get('marketing/campaigns/stats', [MktCampCtrl::class, 'stats']);
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/campaigns', [MktCampCtrl::class, 'store']);
            Route::put('marketing/campaigns/{id}', [MktCampCtrl::class, 'update'])->whereNumber('id');
            Route::delete('marketing/campaigns/{id}', [MktCampCtrl::class, 'destroy'])->whereNumber('id');
        });
        Route::middleware('perm:marketing.approve')->group(function () {
            Route::post('marketing/campaigns/{id}/start', [MktCampCtrl::class, 'start'])->whereNumber('id');
            Route::post('marketing/campaigns/{id}/pause', [MktCampCtrl::class, 'pause'])->whereNumber('id');
            Route::post('marketing/campaigns/{id}/complete', [MktCampCtrl::class, 'complete'])->whereNumber('id');
        });
        // Segments
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/segments', [MktSegCtrl::class, 'index']);
            Route::get('marketing/segments/{id}', [MktSegCtrl::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/segments', [MktSegCtrl::class, 'store']);
            Route::put('marketing/segments/{id}', [MktSegCtrl::class, 'update'])->whereNumber('id');
            Route::delete('marketing/segments/{id}', [MktSegCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('marketing/segments/{id}/toggle', [MktSegCtrl::class, 'toggleActive'])->whereNumber('id');
            Route::post('marketing/segments/{id}/add-contact', [MktSegCtrl::class, 'addContact'])->whereNumber('id');
            Route::post('marketing/segments/{id}/remove-contact', [MktSegCtrl::class, 'removeContact'])->whereNumber('id');
        });
        Route::middleware('perm:marketing.reports')->group(function () {
            Route::post('marketing/segments/{id}/calculate', [MktSegCtrl::class, 'calculateCount'])->whereNumber('id');
        });
        // Contacts
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/contacts', [MktContCtrl::class, 'index']);
            Route::get('marketing/contacts/{id}', [MktContCtrl::class, 'show'])->whereNumber('id');
            Route::get('marketing/contacts/{id}/opt-in', [MktContCtrl::class, 'checkOptIn'])->whereNumber('id');
            Route::get('marketing/contacts/stats', [MktContCtrl::class, 'stats']);
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/contacts', [MktContCtrl::class, 'store']);
            Route::put('marketing/contacts/{id}', [MktContCtrl::class, 'update'])->whereNumber('id');
            Route::delete('marketing/contacts/{id}', [MktContCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('marketing/contacts/{id}/soft-delete', [MktContCtrl::class, 'softDelete'])->whereNumber('id');
            Route::post('marketing/contacts/{id}/add-tags', [MktContCtrl::class, 'addTags'])->whereNumber('id');
            Route::post('marketing/contacts/{id}/remove-tags', [MktContCtrl::class, 'removeTags'])->whereNumber('id');
            Route::post('marketing/contacts/{id}/set-opt-in', [MktContCtrl::class, 'setOptIn'])->whereNumber('id');
            Route::post('marketing/contacts/sync', [MktContCtrl::class, 'sync']);
            Route::post('marketing/contacts/{id}/activity', [MktContCtrl::class, 'updateActivity'])->whereNumber('id');
        });
        // Vouchers
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/vouchers', [MktVouCtrl::class, 'index']);
            Route::get('marketing/vouchers/{id}', [MktVouCtrl::class, 'show'])->whereNumber('id');
            Route::get('marketing/vouchers/stats', [MktVouCtrl::class, 'stats']);
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/vouchers', [MktVouCtrl::class, 'store']);
            Route::put('marketing/vouchers/{id}', [MktVouCtrl::class, 'update'])->whereNumber('id');
            Route::delete('marketing/vouchers/{id}', [MktVouCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('marketing/vouchers/{id}/activate', [MktVouCtrl::class, 'activate'])->whereNumber('id');
            Route::post('marketing/vouchers/{id}/deactivate', [MktVouCtrl::class, 'deactivate'])->whereNumber('id');
            Route::post('marketing/vouchers/validate', [MktVouCtrl::class, 'validate']);
            Route::post('marketing/vouchers/{id}/use', [MktVouCtrl::class, 'use'])->whereNumber('id');
            Route::post('marketing/vouchers/generate-codes', [MktVouCtrl::class, 'generateCodes']);
        });
        // Deals
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/deals', [MktDealCtrl::class, 'index']);
            Route::get('marketing/deals/{id}', [MktDealCtrl::class, 'show'])->whereNumber('id');
            Route::get('marketing/deals/{id}/activities', [MktDealCtrl::class, 'getActivities'])->whereNumber('id');
            Route::get('marketing/deals/stats', [MktDealCtrl::class, 'stats']);
            Route::get('marketing/deals/forecast', [MktDealCtrl::class, 'forecast']);
            Route::get('marketing/deals/overdue', [MktDealCtrl::class, 'getOverdue']);
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/deals', [MktDealCtrl::class, 'store']);
            Route::put('marketing/deals/{id}', [MktDealCtrl::class, 'update'])->whereNumber('id');
            Route::delete('marketing/deals/{id}', [MktDealCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('marketing/deals/{id}/move-stage', [MktDealCtrl::class, 'moveStage'])->whereNumber('id');
            Route::post('marketing/deals/{id}/activity', [MktDealCtrl::class, 'addActivity'])->whereNumber('id');
        });
        Route::middleware('perm:marketing.approve')->group(function () {
            Route::post('marketing/deals/{id}/won', [MktDealCtrl::class, 'closeAsWon'])->whereNumber('id');
            Route::post('marketing/deals/{id}/lost', [MktDealCtrl::class, 'closeAsLost'])->whereNumber('id');
        });
        // Sequences
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/sequences', [MktSeqCtrl::class, 'index']);
            Route::get('marketing/sequences/{id}', [MktSeqCtrl::class, 'show'])->whereNumber('id');
            Route::get('marketing/sequences/stats', [MktSeqCtrl::class, 'stats']);
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/sequences', [MktSeqCtrl::class, 'store']);
            Route::put('marketing/sequences/{id}', [MktSeqCtrl::class, 'update'])->whereNumber('id');
            Route::delete('marketing/sequences/{id}', [MktSeqCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('marketing/sequences/{id}/toggle', [MktSeqCtrl::class, 'toggleActive'])->whereNumber('id');
            Route::post('marketing/sequences/{id}/start-execution', [MktSeqCtrl::class, 'startExecution'])->whereNumber('id');
            Route::post('marketing/sequences/{id}/execute', [MktSeqCtrl::class, 'execute'])->whereNumber('id');
        });
        // Workflows
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/workflows', [MktWfCtrl::class, 'index']);
            Route::get('marketing/workflows/{id}', [MktWfCtrl::class, 'show'])->whereNumber('id');
            Route::get('marketing/workflows/stats', [MktWfCtrl::class, 'stats']);
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/workflows', [MktWfCtrl::class, 'store']);
            Route::put('marketing/workflows/{id}', [MktWfCtrl::class, 'update'])->whereNumber('id');
            Route::delete('marketing/workflows/{id}', [MktWfCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('marketing/workflows/{id}/toggle', [MktWfCtrl::class, 'toggleActive'])->whereNumber('id');
            Route::post('marketing/workflows/{id}/trigger', [MktWfCtrl::class, 'trigger'])->whereNumber('id');
            Route::post('marketing/workflows/{id}/execute', [MktWfCtrl::class, 'execute'])->whereNumber('id');
        });
        // Lead Scores
        Route::middleware('perm:marketing.view')->group(function () {
            Route::get('marketing/lead-scores', [MktLsCtrl::class, 'index']);
            Route::get('marketing/lead-scores/{id}', [MktLsCtrl::class, 'show'])->whereNumber('id');
            Route::get('marketing/lead-scores/contact/{contactId}', [MktLsCtrl::class, 'showForContact'])->whereNumber('contactId');
        });
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/lead-scores/contact/{contactId}/calculate', [MktLsCtrl::class, 'calculate'])->whereNumber('contactId');
            Route::put('marketing/lead-scores/contact/{contactId}', [MktLsCtrl::class, 'update'])->whereNumber('contactId');
            Route::post('marketing/lead-scores/bulk-recalculate', [MktLsCtrl::class, 'bulkRecalculate']);
        });
        Route::middleware('perm:marketing.reports')->group(function () {
            Route::get('marketing/lead-scores/top', [MktLsCtrl::class, 'topLeads']);
            Route::get('marketing/lead-scores/by-grade', [MktLsCtrl::class, 'byGrade']);
            Route::get('marketing/lead-scores/distribution', [MktLsCtrl::class, 'distribution']);
            Route::get('marketing/lead-scores/averages', [MktLsCtrl::class, 'averages']);
            Route::get('marketing/lead-scores/stats', [MktLsCtrl::class, 'stats']);
        });
        // Ad Integration
        Route::middleware('perm:marketing.manage')->group(function () {
            Route::post('marketing/ads/sync-google', [MktAdCtrl::class, 'syncGoogle']);
            Route::post('marketing/ads/sync-facebook', [MktAdCtrl::class, 'syncFacebook']);
            Route::post('marketing/ads/google-campaign', [MktAdCtrl::class, 'createGoogleCampaign']);
            Route::post('marketing/ads/facebook-campaign', [MktAdCtrl::class, 'createFacebookCampaign']);
            Route::post('marketing/ads/validate-credentials', [MktAdCtrl::class, 'validateCredentials']);
        });
        Route::middleware('perm:marketing.reports')->group(function () {
            Route::get('marketing/ads/performance', [MktAdCtrl::class, 'getPerformance']);
        });
        Route::middleware('perm:marketing.approve')->group(function () {
            Route::post('marketing/ads/pause-all', [MktAdCtrl::class, 'pauseAll']);
            Route::post('marketing/ads/resume-all', [MktAdCtrl::class, 'resumeAll']);
        });
        // Analytics
        Route::middleware('perm:marketing.reports')->group(function () {
            Route::get('marketing/analytics/campaigns', [MktAnaCtrl::class, 'getCampaignAnalytics']);
            Route::get('marketing/analytics/channels', [MktAnaCtrl::class, 'getChannelBreakdown']);
            Route::get('marketing/analytics/trends', [MktAnaCtrl::class, 'getTimeSeriesData']);
            Route::get('marketing/analytics/roi', [MktAnaCtrl::class, 'calculateROI']);
            Route::get('marketing/analytics/attribution', [MktAnaCtrl::class, 'getAttribution']);
            Route::get('marketing/analytics/acquisition', [MktAnaCtrl::class, 'getAcquisitionMetrics']);
            Route::get('marketing/analytics/vouchers', [MktAnaCtrl::class, 'getVoucherAnalytics']);
            Route::get('marketing/analytics/funnel', [MktAnaCtrl::class, 'getFunnelMetrics']);
            Route::get('marketing/analytics/dashboard', [MktAnaCtrl::class, 'getDashboard']);
        });
        // B-Loyalty — Loyalty Program & Rewards System
        Route::prefix('loyalty')->middleware('perm:loyalty.view')->group(function () {
            // Members
            Route::get('members', [LoyMemCtrl::class, 'index']);
            Route::get('members/{id}', [LoyMemCtrl::class, 'show'])->whereNumber('id');
            Route::get('members/code/{code}', [LoyMemCtrl::class, 'getByCode']);
            Route::post('members/enroll-from-contact', [LoyMemCtrl::class, 'enrollFromContact']);
            Route::post('members/bulk-evaluate-tier', [LoyMemCtrl::class, 'bulkEvaluateTiers']);
            // Points
            Route::get('points/balance/{memberId}', [LoyPtsCtrl::class, 'getBalance'])->whereNumber('memberId');
            Route::get('points/ledger/{memberId}', [LoyPtsCtrl::class, 'getRecentEntries'])->whereNumber('memberId');
            Route::post('points/award', [LoyPtsCtrl::class, 'award']);
            Route::post('points/redeem', [LoyPtsCtrl::class, 'redeem']);
            Route::post('points/transfer', [LoyPtsCtrl::class, 'transfer']);
            Route::post('points/adjust', [LoyPtsCtrl::class, 'adjust']);
            Route::post('points/expire', [LoyPtsCtrl::class, 'expire']);
            // Rewards
            Route::get('rewards', [LoyRewCtrl::class, 'index']);
            Route::get('rewards/{id}', [LoyRewCtrl::class, 'show'])->whereNumber('id');
            Route::post('rewards/redeem', [LoyRewCtrl::class, 'redeem']);
            Route::post('rewards/{id}/fulfill', [LoyRewCtrl::class, 'fulfill'])->whereNumber('id');
            Route::post('rewards/{id}/cancel', [LoyRewCtrl::class, 'cancel'])->whereNumber('id');
            Route::get('rewards/stats', [LoyRewCtrl::class, 'stats']);
            // Tiers
            Route::get('tiers', [LoyTierCtrl::class, 'index']);
            Route::get('tiers/{id}', [LoyTierCtrl::class, 'show'])->whereNumber('id');
            Route::post('tiers/promote-member', [LoyTierCtrl::class, 'promoteMember']);
            Route::post('tiers/demote-member', [LoyTierCtrl::class, 'demoteMember']);
            Route::post('tiers/bulk-evaluate', [LoyTierCtrl::class, 'bulkEvaluate']);
            Route::get('tiers/stats', [LoyTierCtrl::class, 'stats']);
            // Analytics
            Route::get('analytics', [LoyAnaCtrl::class, 'index']);
            Route::get('analytics/segmentation', [LoyAnaCtrl::class, 'segmentation']);
            Route::get('analytics/ltv-trends', [LoyAnaCtrl::class, 'ltvTrends']);
            Route::get('analytics/churn-metrics', [LoyAnaCtrl::class, 'churnMetrics']);
            Route::get('analytics/liability', [LoyAnaCtrl::class, 'liability']);
        });
        // B-Loyalty Management Routes
        Route::prefix('loyalty')->middleware('perm:loyalty.manage')->group(function () {
            // Member management
            Route::post('members', [LoyMemCtrl::class, 'store']);
            Route::put('members/{id}', [LoyMemCtrl::class, 'update'])->whereNumber('id');
            Route::post('members/{id}/deactivate', [LoyMemCtrl::class, 'deactivate'])->whereNumber('id');
            Route::post('members/{id}/reactivate', [LoyMemCtrl::class, 'reactivate'])->whereNumber('id');
            Route::post('members/{id}/update-tier', [LoyMemCtrl::class, 'updateTier'])->whereNumber('id');
            // Reward management
            Route::post('rewards', [LoyRewCtrl::class, 'store']);
            Route::put('rewards/{id}', [LoyRewCtrl::class, 'update'])->whereNumber('id');
            Route::delete('rewards/{id}', [LoyRewCtrl::class, 'destroy'])->whereNumber('id');
            // Tier management
            Route::post('tiers', [LoyTierCtrl::class, 'store']);
            Route::put('tiers/{id}', [LoyTierCtrl::class, 'update'])->whereNumber('id');
            Route::delete('tiers/{id}', [LoyTierCtrl::class, 'destroy'])->whereNumber('id');
        });
        // B-Loyalty Approval Routes
        Route::prefix('loyalty')->middleware('perm:loyalty.approve')->group(function () {
            Route::post('redemptions/{id}/approve', [LoyRewCtrl::class, 'approveRedemption'])->whereNumber('id');
            Route::post('redemptions/{id}/reject', [LoyRewCtrl::class, 'rejectRedemption'])->whereNumber('id');
        });
        // B-Loyalty Reports Routes
        Route::prefix('loyalty')->middleware('perm:loyalty.reports')->group(function () {
            Route::get('members/stats', [LoyMemCtrl::class, 'stats']);
            Route::get('rewards/stats', [LoyRewCtrl::class, 'stats']);
            Route::get('tiers/stats', [LoyTierCtrl::class, 'stats']);
            Route::get('analytics', [LoyAnaCtrl::class, 'index']);
            Route::get('analytics/segmentation', [LoyAnaCtrl::class, 'segmentation']);
            Route::get('analytics/ltv-trends', [LoyAnaCtrl::class, 'ltvTrends']);
            Route::get('analytics/churn-metrics', [LoyAnaCtrl::class, 'churnMetrics']);
            Route::get('analytics/liability', [LoyAnaCtrl::class, 'liability']);
        });
        // B-Affiliate — Affiliate Marketing & Commission System
        Route::prefix('affiliate')->middleware('perm:affiliate.view')->group(function () {
            // Dashboard & Analytics
            Route::get('dashboard', [AnaCtrl::class, 'getOverview']);
            Route::get('analytics/top-affiliates', [AnaCtrl::class, 'getTopAffiliates']);
            Route::get('analytics/funnel', [AnaCtrl::class, 'getFunnel']);
            Route::get('analytics/trends', [AnaCtrl::class, 'getTrends']);
            Route::get('analytics/payout-report', [AnaCtrl::class, 'getPayoutReport']);
            Route::get('analytics/roi', [AnaCtrl::class, 'getROI']);
            Route::post('analytics/comparison', [AnaCtrl::class, 'getComparison']);
            // Affiliates CRUD
            Route::get('affiliates', [AffCtrl::class, 'index']);
            Route::get('affiliates/{id}', [AffCtrl::class, 'show'])->whereNumber('id');
            Route::get('affiliates/code/{code}', [AffCtrl::class, 'getByCode']);
            Route::get('affiliates/stats', [AffCtrl::class, 'stats']);
            Route::post('affiliates/evaluate-tiers', [AffCtrl::class, 'evaluateTiers']);
            // Referral Codes
            Route::get('referrals/codes', [RefCtrl::class, 'index']);
            Route::get('referrals/codes/{id}', [RefCtrl::class, 'show'])->whereNumber('id');
            Route::post('referrals/{code}/track', [RefCtrl::class, 'trackClick'])->where('code', '[^/]+');
            Route::post('referrals/deactivate-expired', [RefCtrl::class, 'deactivateExpired']);
            // Commissions
            Route::get('commissions', [ComCtrl::class, 'index']);
            Route::get('commissions/{id}', [ComCtrl::class, 'show'])->whereNumber('id');
            Route::get('commissions/summary', [ComCtrl::class, 'summary']);
            // Payouts
            Route::get('payouts', [PayCtrl::class, 'index']);
            Route::get('payouts/{id}', [PayCtrl::class, 'show'])->whereNumber('id');
        });
        // B-Affiliate Management Routes
        Route::prefix('affiliate')->middleware('perm:affiliate.manage')->group(function () {
            // Affiliate applications
            Route::post('affiliates', [AffCtrl::class, 'store']);
            Route::put('affiliates/{id}', [AffCtrl::class, 'update'])->whereNumber('id');
            Route::post('affiliates/{id}/activate', [AffCtrl::class, 'activate'])->whereNumber('id');
            Route::post('affiliates/{id}/deactivate', [AffCtrl::class, 'deactivate'])->whereNumber('id');
            // Referral codes management
            Route::post('referrals/codes', [RefCtrl::class, 'store']);
            Route::put('referrals/codes/{id}', [RefCtrl::class, 'update'])->whereNumber('id');
            Route::post('referrals/codes/{id}/link', [RefCtrl::class, 'generateLink'])->whereNumber('id');
            // Manual commission creation
            Route::post('commissions', [ComCtrl::class, 'create']);
            // Create payout from commissions
            Route::post('payouts/create', [PayCtrl::class, 'createFromCommissions']);
        });
        // B-Affiliate Approval Routes
        Route::prefix('affiliate')->middleware('perm:affiliate.approve')->group(function () {
            // Approve/reject affiliate applications
            Route::post('affiliates/{id}/approve', [AffCtrl::class, 'approve'])->whereNumber('id');
            Route::post('affiliates/{id}/reject', [AffCtrl::class, 'reject'])->whereNumber('id');
            // Approve/reject commissions
            Route::post('commissions/{id}/approve', [ComCtrl::class, 'approve'])->whereNumber('id');
            Route::post('commissions/{id}/reject', [ComCtrl::class, 'reject'])->whereNumber('id');
            Route::post('commissions/bulk-approve', [ComCtrl::class, 'bulkApprove']);
            // Process/cancel payouts
            Route::post('payouts/{id}/process', [PayCtrl::class, 'process'])->whereNumber('id');
            Route::post('payouts/{id}/cancel', [PayCtrl::class, 'cancel'])->whereNumber('id');
        });
        // B-Affiliate Reports Routes
        Route::prefix('affiliate')->middleware('perm:affiliate.reports')->group(function () {
            Route::get('affiliates/{id}/referrals', [RefCtrl::class, 'getAffiliateReferrals'])->whereNumber('id');
            Route::get('affiliates/{id}/payout-summary', [PayCtrl::class, 'getSummary'])->whereNumber('id');
        });
        // B-Link — Dynamic Links, Digital Cards & Click Tracking
        Route::prefix('link')->middleware('perm:link.view')->group(function () {
            // Link Pages
            Route::get('pages', [LinkCtrl::class, 'index']);
            Route::get('pages/{page}', [LinkCtrl::class, 'show'])->whereNumber('page');
            Route::get('analytics', [LinkCtrl::class, 'getAnalytics']);
            // Digital Cards
            Route::get('cards', [CardCtrl::class, 'index']);
            Route::get('cards/{card}', [CardCtrl::class, 'show'])->whereNumber('card');
            Route::get('cards/{card}/vcard', [CardCtrl::class, 'getVCard'])->whereNumber('card');
            // Click Tracking
            Route::post('track/{code}', [LinkCtrl::class, 'trackClick'])->where('code', '[^/]+');
            Route::post('cards/{card}/view', [CardCtrl::class, 'trackView'])->whereNumber('card');
            Route::post('cards/{card}/share', [CardCtrl::class, 'trackShare'])->whereNumber('card');
        });
        // B-Link Management Routes
        Route::prefix('link')->middleware('perm:link.manage')->group(function () {
            // Link Page management
            Route::post('pages', [LinkCtrl::class, 'store']);
            Route::put('pages/{page}', [LinkCtrl::class, 'update'])->whereNumber('page');
            Route::delete('pages/{page}', [LinkCtrl::class, 'destroy'])->whereNumber('page');
            // Digital Card management
            Route::post('cards', [CardCtrl::class, 'store']);
            Route::put('cards/{card}', [CardCtrl::class, 'update'])->whereNumber('card');
            Route::delete('cards/{card}', [CardCtrl::class, 'destroy'])->whereNumber('card');
        });
        // B-Sign & Docs — Document Management, Templates & Electronic Signatures
        Route::prefix('sign-docs')->middleware('perm:sign_docs.view')->group(function () {
            // Documents
            Route::get('documents', [DocCtrl::class, 'index']);
            Route::get('documents/{id}', [DocCtrl::class, 'show'])->whereNumber('id');
            Route::get('documents/shared', [DocCtrl::class, 'search']);
            Route::get('statistics', [DocCtrl::class, 'getStatistics']);
            // Templates
            Route::get('templates', [TempCtrl::class, 'index']);
            Route::get('templates/{id}', [TempCtrl::class, 'show'])->whereNumber('id');
            // Signatures
            Route::get('documents/{id}/signatures', [SigCtrl::class, 'index'])->whereNumber('id');
            Route::get('signatures/pending', [SigCtrl::class, 'getPending']);
        });
        // B-Sign & Docs Management Routes
        Route::prefix('sign-docs')->middleware('perm:sign_docs.manage')->group(function () {
            // Document management
            Route::post('documents', [DocCtrl::class, 'store']);
            Route::put('documents/{id}', [DocCtrl::class, 'update'])->whereNumber('id');
            Route::delete('documents/{id}', [DocCtrl::class, 'destroy'])->whereNumber('id');
            Route::get('documents/{id}/download', [DocCtrl::class, 'download'])->whereNumber('id');
            Route::post('documents/{id}/share-link', [DocCtrl::class, 'generateShareLink'])->whereNumber('id');
            Route::get('documents/{id}/versions', [DocCtrl::class, 'getVersions'])->whereNumber('id');
            // Template management
            Route::post('templates', [TempCtrl::class, 'store']);
            Route::put('templates/{id}', [TempCtrl::class, 'update'])->whereNumber('id');
            Route::delete('templates/{id}', [TempCtrl::class, 'destroy'])->whereNumber('id');
            Route::post('templates/{id}/generate', [TempCtrl::class, 'generateDocument'])->whereNumber('id');
            Route::post('templates/{id}/duplicate', [TempCtrl::class, 'duplicate'])->whereNumber('id');
        });
        // B-Sign & Docs Signature Routes
        Route::prefix('sign-docs')->middleware('perm:sign_docs.sign')->group(function () {
            // Create signatures
            Route::post('documents/{id}/signatures/drawn', [SigCtrl::class, 'createDrawn'])->whereNumber('id');
            Route::post('documents/{id}/signatures/typed', [SigCtrl::class, 'createTyped'])->whereNumber('id');
            Route::post('documents/{id}/signatures/upload', [SigCtrl::class, 'createUploaded'])->whereNumber('id');
            // Sign/reject
            Route::post('signatures/{id}/complete', [SigCtrl::class, 'sign'])->whereNumber('id');
            Route::post('signatures/{id}/reject', [SigCtrl::class, 'reject'])->whereNumber('id');
            // Generate signed PDF
            Route::get('documents/{id}/signed-pdf', [SigCtrl::class, 'generatePdf'])->whereNumber('id');
        });
        // Shared document access (public)
        Route::get('s/{shareLink}', [DocCtrl::class, 'getByShareLink']);
        // ============================================            // B-Omnichannel — Unified Customer Communication Platform            // ============================================
        // Channels (view)
        Route::prefix('omnichannel/channels')->middleware('perm:omnichannel.view')->group(function () {
            Route::get('/', [ChannelController::class, 'index']);
            Route::get('/{id}', [ChannelController::class, 'show'])->whereNumber('id');
            Route::get('/primary', [ChannelController::class, 'getPrimary']);
            Route::get('/stats', [ChannelController::class, 'getStats']);
        });
        // Channels (manage)
        Route::prefix('omnichannel/channels')->middleware('perm:omnichannel.channels')->group(function () {
            Route::post('/', [ChannelController::class, 'store']);
            Route::put('/{id}', [ChannelController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [ChannelController::class, 'destroy'])->whereNumber('id');
            Route::post('/{id}/connect', [ChannelController::class, 'connect'])->whereNumber('id');
            Route::post('/{id}/disconnect', [ChannelController::class, 'disconnect'])->whereNumber('id');
            Route::post('/{id}/test', [ChannelController::class, 'testConnection'])->whereNumber('id');
        });
        // Conversations (view)
        Route::prefix('omnichannel/conversations')->middleware('perm:omnichannel.view')->group(function () {
            Route::get('/', [ConversationController::class, 'index']);
            Route::get('/{id}', [ConversationController::class, 'show'])->whereNumber('id');
            Route::get('/statistics', [ConversationController::class, 'getStatistics']);
            Route::get('/{id}/messages', [ConversationController::class, 'getMessages'])->whereNumber('id');
            Route::get('/agent/{agentId}/active', [ConversationController::class, 'getAgentActive'])->where('agentId', 'numeric');
            // Analytics
            Route::get('/analytics/dashboard', [ConversationController::class, 'getDashboardStats']);
            Route::get('/analytics/trends', [ConversationController::class, 'getTrends']);
            Route::get('/analytics/agent-performance', [ConversationController::class, 'getAgentPerformance']);
        });
        // Conversations (manage)
        Route::prefix('omnichannel/conversations')->middleware('perm:omnichannel.manage')->group(function () {
            Route::patch('/{id}', [ConversationController::class, 'update'])->whereNumber('id');
            Route::post('/{id}/assign', [ConversationController::class, 'assignAgent'])->whereNumber('id');
            Route::post('/{id}/notes', [ConversationController::class, 'addNote'])->whereNumber('id');
            Route::post('/{id}/tags', [ConversationController::class, 'addTag'])->whereNumber('id');
            Route::post('/{id}/send', [ConversationController::class, 'sendMessage'])->whereNumber('id');
            Route::post('/{id}/upload', [ConversationController::class, 'uploadMedia'])->whereNumber('id');
            Route::post('/{id}/escalate', [ConversationController::class, 'escalate'])->whereNumber('id');
            Route::post('/{id}/transfer', [ConversationController::class, 'transfer'])->whereNumber('id');
        });
        // Public Webhooks (no auth required)
        Route::prefix('webhooks')->group(function () {
            Route::post('/whatsapp', [WebhookController::class, 'handleWhatsApp']);
            Route::post('/facebook', [WebhookController::class, 'handleFacebook']);
            Route::post('/telegram', [WebhookController::class, 'handleTelegram']);
            Route::post('/email', [WebhookController::class, 'handleEmail']);
        });
        // ============================================            // B-Logistics — Warehouse, Stock & Shipping            // ============================================
        // Warehouses (view)
        Route::prefix('logistics/warehouses')->middleware('perm:logistics.view')->group(function () {
            Route::get('/', [WarehouseController::class, 'index']);
            Route::get('/{id}', [WarehouseController::class, 'show'])->whereNumber('id');
            Route::get('/primary', [WarehouseController::class, 'getPrimary']);
            Route::get('/stats', [WarehouseController::class, 'getStats']);
        });
        // Warehouses (manage)
        Route::prefix('logistics/warehouses')->middleware('perm:logistics.manage')->group(function () {
            Route::post('/', [WarehouseController::class, 'store']);
            Route::put('/{id}', [WarehouseController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [WarehouseController::class, 'destroy'])->whereNumber('id');
        });
        // Stock Moves
        Route::prefix('logistics/stock-moves')->middleware('perm:logistics.manage')->group(function () {
            Route::get('/warehouse/{warehouseId}', [StockMoveController::class, 'index'])                    ->whereNumber('warehouseId');
            Route::post('/receive', [StockMoveController::class, 'receive']);
            Route::post('/dispatch', [StockMoveController::class, 'dispatch']);
            Route::post('/adjust', [StockMoveController::class, 'adjust']);
            Route::get('/product/{productId}/available', [StockMoveController::class, 'getAvailable'])                    ->whereNumber('productId');
            Route::get('/stock-levels', [StockMoveController::class, 'getStockLevels']);
            Route::post('/{moveId}/approve', [StockMoveController::class, 'approve'])                    ->whereNumber('moveId');
        });
        // Shipments (view)
        Route::prefix('logistics/shipments')->middleware('perm:logistics.view')->group(function () {
            Route::get('/', [ShipmentController::class, 'index']);
            Route::get('/{id}', [ShipmentController::class, 'show'])->whereNumber('id');
            Route::get('/statistics', [ShipmentController::class, 'getStatistics']);
        });
        // Shipments (manage)
        Route::prefix('logistics/shipments')->middleware('perm:logistics.shipping')->group(function () {
            Route::post('/', [ShipmentController::class, 'store']);
            Route::post('/from-order', [ShipmentController::class, 'createFromOrder']);
            Route::patch('/{id}/status', [ShipmentController::class, 'updateStatus'])->whereNumber('id');
            Route::post('/{id}/label', [ShipmentController::class, 'generateLabel'])->whereNumber('id');
            Route::get('/{id}/track', [ShipmentController::class, 'track'])->whereNumber('id');
        });
        // ============================================            // B-Analytics — Business Intelligence & Analytics Engine            // ============================================
        // Metrics (view)
        Route::prefix('analytics/metrics')->middleware('perm:analytics.view')->group(function () {
            Route::get('/', [MetricController::class, 'index']);
            Route::get('/{id}', [MetricController::class, 'show'])->whereNumber('id');
            Route::get('/{id}/history', [MetricController::class, 'history'])                    ->whereNumber('id');
            Route::post('/{id}/calculate', [MetricController::class, 'calculate'])                    ->whereNumber('id');
        });
        // Metrics (manage)
        Route::prefix('analytics/metrics')->middleware('perm:analytics.manage')->group(function () {
            Route::post('/', [MetricController::class, 'store']);
            Route::put('/{id}', [MetricController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [MetricController::class, 'destroy'])->whereNumber('id');
            Route::post('/batch-calculate', [MetricController::class, 'batchCalculate']);
        });
        // Reports (view)
        Route::prefix('analytics/reports')->middleware('perm:analytics.view')->group(function () {
            Route::get('/', [ReportController::class, 'index']);
            Route::get('/{id}/runs', [ReportController::class, 'runs'])                    ->whereNumber('id');
        });
        // Reports (manage)
        Route::prefix('analytics/reports')->middleware('perm:analytics.manage')->group(function () {
            Route::post('/{id}/generate', [ReportController::class, 'generate'])                    ->whereNumber('id');
            Route::post('/{id}/schedule', [ReportController::class, 'schedule'])                    ->whereNumber('id');
            Route::post('/{id}/cancel-schedule', [ReportController::class, 'cancelSchedule'])                    ->whereNumber('id');
        });
        // Report exports
        Route::prefix('analytics/exports')->middleware('perm:analytics.export')->group(function () {
            Route::get('/{filename}', [ReportController::class, 'download'])                    ->where('filename', '.*');
        });
        // Dashboards (view)
        Route::prefix('analytics/dashboards')->middleware('perm:analytics.view')->group(function () {
            Route::get('/', [DashboardController::class, 'index']);
            Route::get('/default', [DashboardController::class, 'getDefault']);
            Route::get('/{id}', [DashboardController::class, 'show'])->whereNumber('id');
        });
        // Dashboards (manage)
        Route::prefix('analytics/dashboards')->middleware('perm:analytics.manage')->group(function () {
            Route::post('/', [DashboardController::class, 'store']);
            Route::put('/{id}', [DashboardController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [DashboardController::class, 'destroy'])->whereNumber('id');
            Route::post('/{id}/share', [DashboardController::class, 'share'])                    ->whereNumber('id');
            Route::post('/{id}/refresh', [DashboardController::class, 'refresh'])                    ->whereNumber('id');
            // Widgets
            Route::post('/{dashboardId}/widgets', [DashboardController::class, 'addWidget'])                    ->whereNumber('dashboardId');
            Route::put('/widgets/{widgetId}', [DashboardController::class, 'updateWidget'])                    ->whereNumber('widgetId');
            Route::delete('/widgets/{widgetId}', [DashboardController::class, 'deleteWidget'])                    ->whereNumber('widgetId');
        });
        // ============================================            // B-Brain Hub — AI-Powered Insight, Forecast & Recommendation Engine            // ============================================
        // Insights (view)
        Route::prefix('brainhub/insights')->middleware('perm:brainhub.view')->group(function () {
            Route::get('/', [BrainInsightCtrl::class, 'index']);
            Route::get('/{id}', [BrainInsightCtrl::class, 'show'])->whereNumber('id');
            Route::get('/statistics', [BrainInsightCtrl::class, 'getStatistics']);
        });
        // Insights (manage)
        Route::prefix('brainhub/insights')->middleware('perm:brainhub.insights')->group(function () {
            Route::post('/generate', [BrainInsightCtrl::class, 'generate']);
            Route::post('/{id}/mark-read', [BrainInsightCtrl::class, 'markAsRead'])->whereNumber('id');
            Route::post('/{id}/dismiss', [BrainInsightCtrl::class, 'dismiss'])->whereNumber('id');
            Route::post('/{id}/action', [BrainInsightCtrl::class, 'recordAction'])->whereNumber('id');
        });
        // Forecasts
        Route::prefix('brainhub/forecasts')->middleware('perm:brainhub.forecasts')->group(function () {
            Route::post('/metric/{metricId}/generate', [BrainForecastCtrl::class, 'generate'])                    ->whereNumber('metricId');
            Route::get('/metric/{metricId}', [BrainForecastCtrl::class, 'show'])                    ->whereNumber('metricId');
            Route::post('/batch-generate', [BrainForecastCtrl::class, 'batchGenerate']);
            Route::post('/{id}/evaluate', [BrainForecastCtrl::class, 'evaluate'])                    ->whereNumber('id');
        });
        // Recommendations (view)
        Route::prefix('brainhub/recommendations')->middleware('perm:brainhub.view')->group(function () {
            Route::get('/', [BrainRecCtrl::class, 'index']);
            Route::get('/{id}', [BrainRecCtrl::class, 'show'])->whereNumber('id');
            Route::get('/statistics', [BrainRecCtrl::class, 'getStatistics']);
        });
        // Recommendations (manage)
        Route::prefix('brainhub/recommendations')->middleware('perm:brainhub.recommendations')->group(function () {
            Route::post('/{id}/accept', [BrainRecCtrl::class, 'accept'])->whereNumber('id');
            Route::post('/{id}/complete', [BrainRecCtrl::class, 'complete'])->whereNumber('id');
            Route::post('/{id}/feedback', [BrainRecCtrl::class, 'provideFeedback'])->whereNumber('id');
            Route::post('/bulk-generate', [BrainRecCtrl::class, 'bulkGenerate']);
        });
        // ============================================            // B-COGS Engine — Cost of Goods Sold & Margin Analysis            // ============================================
        // Cost Calculation (view)
        Route::prefix('cogs/costs')->middleware('perm:cogs.view')->group(function () {
            Route::get('/product/{productId}/current', [COGSCalcCtrl::class, 'getCurrentCost'])                    ->whereNumber('productId');
            Route::get('/product/{productId}/history', [COGSCalcCtrl::class, 'getHistory'])                    ->whereNumber('productId');
        });
        // Cost Calculation (manage)
        Route::prefix('cogs/costs')->middleware('perm:cogs.manage')->group(function () {
            Route::post('/product/{productId}/calculate', [COGSCalcCtrl::class, 'calculate'])                    ->whereNumber('productId');
            Route::post('/product/{productId}/components', [COGSCalcCtrl::class, 'addComponent'])                    ->whereNumber('productId');
            Route::delete('/components/{componentId}', [COGSCalcCtrl::class, 'removeComponent'])                    ->whereNumber('componentId');
            Route::post('/allocate-overheads', [COGSCalcCtrl::class, 'allocateOverheads']);
            Route::post('/recalculate-all', [COGSCalcCtrl::class, 'recalculateAll']);
        });
        // Margin Reports (view)
        Route::prefix('cogs/margins')->middleware('perm:cogs.view')->group(function () {
            Route::get('/all', [COGSMarginCtrl::class, 'getAllMargins']);
            Route::get('/by-category', [COGSMarginCtrl::class, 'getMarginByCategory']);
            Route::get('/low-margin', [COGSMarginCtrl::class, 'getLowMarginProducts']);
            Route::get('/profitability-ranking', [COGSMarginCtrl::class, 'getProfitabilityRanking']);
            Route::get('/product/{productId}/trend', [COGSMarginCtrl::class, 'getMarginTrend'])                    ->whereNumber('productId');
        });
        // Margin Reports (manage)
        Route::prefix('cogs/margins')->middleware('perm:cogs.reports')->group(function () {
            Route::post('/product/{productId}/calculate', [COGSMarginCtrl::class, 'calculateMargin'])                    ->whereNumber('productId');
            Route::post('/product/{productId}/break-even', [COGSMarginCtrl::class, 'calculateBreakEven'])                    ->whereNumber('productId');
            Route::post('/generate-report', [COGSMarginCtrl::class, 'generateReport']);
        });
        // ============================================            // Industry Packs — Pre-configured Industry Templates            // ============================================
        // Available industries (view)
        Route::prefix('industry-packs')->middleware('perm:industry_packs.view')->group(function () {
            Route::get('/list', [IPCtrl::class, 'listIndustries']);
            Route::get('/{industryKey}/details', [IPCtrl::class, 'getDetails']);
            Route::get('/{industryKey}/dashboard', [IPCtrl::class, 'getDashboardLayout']);
            Route::get('/{industryKey}/widgets/{role}', [IPCtrl::class, 'getWidgetsForRole']);
            Route::post('/recommend', [IPCtrl::class, 'recommend']);
            Route::get('/tenant/{tenantId}', [IPCtrl::class, 'getTenantProfile']);
        });
        // Apply / Change industry pack (manage)
        Route::prefix('industry-packs')->middleware('perm:industry_packs.manage')->group(function () {
            Route::post('/apply', [IPCtrl::class, 'apply']);
            Route::post('/change', [IPCtrl::class, 'changeIndustry']);
            Route::post('/customize-dashboard', [IPCtrl::class, 'customizeDashboard']);
            Route::post('/reset-dashboard', [IPCtrl::class, 'resetDashboard']);
        });
        // ============================================            // B-AI Agents — Multi-Agent System (BAB 16)            // ============================================
        // List available agents (view)
        Route::prefix('ai-agents')->middleware('perm:ai_agents.view')->group(function () {
            Route::get('/list', [AIAgentCtrl::class, 'listAgents']);
            Route::get('/{agentKey}', [AIAgentCtrl::class, 'getAgent']);
            Route::get('/tenant/list', [AIAgentCtrl::class, 'listTenantAgents']);
            Route::get('/{agentKey}/stats', [AIAgentCtrl::class, 'getAgentStats']);
            Route::post('/recommend', [AIAgentCtrl::class, 'recommendAgents']);
            // Business profile
            Route::get('/business-profile', [AIAgentCtrl::class, 'getBusinessProfile']);
            Route::post('/business-profile', [AIAgentCtrl::class, 'saveBusinessProfile']);
            // Conversations
            Route::get('/{agentKey}/conversations', [AIAgentCtrl::class, 'getConversationHistory']);
            Route::post('/conversation/save', [AIAgentCtrl::class, 'saveConversation']);
            // Governance report
            Route::get('/governance/report', [AIAgentCtrl::class, 'getGovernanceReport']);
        });
        // Configure & manage agents (manage)
        Route::prefix('ai-agents')->middleware('perm:ai_agents.manage')->group(function () {
            Route::post('/{agentKey}/configure', [AIAgentCtrl::class, 'configureAgent']);
            Route::put('/{agentKey}/update', [AIAgentCtrl::class, 'updateAgent']);
            Route::post('/{agentKey}/disable', [AIAgentCtrl::class, 'disableAgent']);
            Route::post('/{agentKey}/enable', [AIAgentCtrl::class, 'enableAgent']);
            Route::post('/{agentKey}/reset', [AIAgentCtrl::class, 'resetAgent']);
        });
        // Execute agent tasks & tools (execute)
        Route::prefix('ai-agents')->middleware('perm:ai_agents.execute')->group(function () {
            Route::post('/execute/task', [AIAgentCtrl::class, 'executeTask']);
            Route::post('/execute/tool', [AIAgentCtrl::class, 'executeTool']);
            Route::post('/invoke', [AIAgentCtrl::class, 'invokeAgent']);
        });
        // ============================================            // B-Personal AI Assistant (BAB 17)            // ============================================
        // Assistants & preferences (view)
        Route::prefix('personal-ai')->middleware('perm:personal_ai.view')->group(function () {
            Route::get('/assistants', [PACtrl::class, 'listAssistants']);
            Route::get('/assistants/{key}', [PACtrl::class, 'getAssistant']);
            Route::get('/recommend', [PACtrl::class, 'recommend']);
            Route::get('/preferences', [PACtrl::class, 'getPreferences']);
            Route::get('/memory', [PACtrl::class, 'listMemory']);
            Route::get('/memory/summary', [PACtrl::class, 'memorySummary']);
            Route::get('/tasks', [PACtrl::class, 'listTasks']);
            Route::get('/tasks/{id}', [PACtrl::class, 'showTask']);
            Route::get('/tasks-stats', [PACtrl::class, 'taskStats']);
            Route::get('/alerts', [PACtrl::class, 'listAlerts']);
        });
        // Task execution (tasks)
        Route::prefix('personal-ai')->middleware('perm:personal_ai.tasks')->group(function () {
            Route::post('/tasks', [PACtrl::class, 'createTask']);
            Route::post('/tasks/{id}/transition', [PACtrl::class, 'transitionTask']);
            Route::post('/context', [PACtrl::class, 'buildContext']);
        });
        // Memory management (memory)
        Route::prefix('personal-ai')->middleware('perm:personal_ai.memory')->group(function () {
            Route::post('/memory', [PACtrl::class, 'storeMemory']);
            Route::put('/memory/{id}', [PACtrl::class, 'updateMemory']);
            Route::delete('/memory/{id}', [PACtrl::class, 'deleteMemory']);
            Route::post('/memory/purge', [PACtrl::class, 'purgeMyMemory']);
        });
        // Configuration (configure)
        Route::prefix('personal-ai')->middleware('perm:personal_ai.configure')->group(function () {
            Route::put('/preferences', [PACtrl::class, 'updatePreferences']);
            Route::post('/switch-model', [PACtrl::class, 'switchModel']);
        });

        // ============================================
        // Pangkalan Pengetahuan / vector store (BAB 17A.4 / T-08)
        // ============================================
        Route::middleware('perm:personal_ai.memory')->group(function () {
            Route::get('knowledge', [\App\Modules\PersonalAssistant\Http\Controllers\KnowledgeController::class, 'index']);
            Route::get('knowledge/stats', [\App\Modules\PersonalAssistant\Http\Controllers\KnowledgeController::class, 'stats']);
            Route::get('knowledge/search', [\App\Modules\PersonalAssistant\Http\Controllers\KnowledgeController::class, 'search']);
            Route::get('knowledge/{id}', [\App\Modules\PersonalAssistant\Http\Controllers\KnowledgeController::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:personal_ai.configure')->group(function () {
            Route::post('knowledge', [\App\Modules\PersonalAssistant\Http\Controllers\KnowledgeController::class, 'store']);
            Route::delete('knowledge/{id}', [\App\Modules\PersonalAssistant\Http\Controllers\KnowledgeController::class, 'destroy'])->whereNumber('id');
        });
        // ============================================            // B-Boardroom Workflow (BAB 20)            // ============================================            // Read (view)
        Route::prefix('boardroom')->middleware('perm:boardroom.view')->group(function () {
            Route::get('/sessions', [BoardroomCtrl::class, 'index']);
            Route::get('/sessions/{id}', [BoardroomCtrl::class, 'show']);
            Route::get('/sessions/{id}/findings', [BoardroomCtrl::class, 'listFindings']);
            Route::get('/stats', [BoardroomCtrl::class, 'stats']);
            Route::get('/metadata', [BoardroomCtrl::class, 'metadata']);
        });
        // Open session (create)
        Route::prefix('boardroom')->middleware('perm:boardroom.create')->group(function () {
            Route::post('/sessions', [BoardroomCtrl::class, 'store']);
        });
        // Workflow steps (participate)
        Route::prefix('boardroom')->middleware('perm:boardroom.participate')->group(function () {
            Route::post('/sessions/{id}/select-agents', [BoardroomCtrl::class, 'selectAgents']);
            Route::post('/sessions/{id}/distribute-context', [BoardroomCtrl::class, 'distributeContext']);
            Route::post('/sessions/{id}/findings', [BoardroomCtrl::class, 'addFinding']);
            Route::post('/sessions/{id}/detect-conflicts', [BoardroomCtrl::class, 'detectConflicts']);
            Route::post('/sessions/{id}/prepare-summary', [BoardroomCtrl::class, 'prepareSummary']);
            Route::post('/sessions/{id}/cancel', [BoardroomCtrl::class, 'cancel']);
        });
        // Owner decision (decide)
        Route::prefix('boardroom')->middleware('perm:boardroom.decide')->group(function () {
            Route::post('/sessions/{id}/decide', [BoardroomCtrl::class, 'decide']);
            Route::post('/sessions/{id}/close', [BoardroomCtrl::class, 'close']);
        });
        // ============================================            // B-Flow Automation (BAB 28)            // ============================================            // Read (view)
        Route::prefix('flows')->middleware('perm:flow.view')->group(function () {
            Route::get('/workflows', [FlowCtrl::class, 'index']);
            Route::get('/workflows/{id}', [FlowCtrl::class, 'show']);
            Route::get('/runs', [FlowCtrl::class, 'listRuns']);
            Route::get('/runs/{id}', [FlowCtrl::class, 'showRun']);
            Route::get('/stats', [FlowCtrl::class, 'stats']);
            Route::get('/metadata', [FlowCtrl::class, 'metadata']);
        });
        // Manage workflows (manage)
        Route::prefix('flows')->middleware('perm:flow.manage')->group(function () {
            Route::post('/workflows', [FlowCtrl::class, 'store']);
            Route::put('/workflows/{id}', [FlowCtrl::class, 'update']);
            Route::delete('/workflows/{id}', [FlowCtrl::class, 'destroy']);
            Route::post('/workflows/{id}/dry-run', [FlowCtrl::class, 'dryRun']);
            Route::post('/workflows/{id}/activate', [FlowCtrl::class, 'activate']);
            Route::post('/workflows/{id}/deactivate', [FlowCtrl::class, 'deactivate']);
        });
        // Execute (execute)
        Route::prefix('flows')->middleware('perm:flow.execute')->group(function () {
            Route::post('/workflows/{id}/trigger', [FlowCtrl::class, 'trigger']);
            Route::post('/runs/{id}/retry', [FlowCtrl::class, 'retryRun']);
            Route::post('/runs/{id}/cancel', [FlowCtrl::class, 'cancelRun']);
            Route::post('/process-retries', [FlowCtrl::class, 'processRetries']);
        });
        // Kill switch (kill_switch)
        Route::prefix('flows')->middleware('perm:flow.kill_switch')->group(function () {
            Route::post('/workflows/{id}/kill-switch', [FlowCtrl::class, 'toggleKillSwitch']);
            Route::post('/tenant-kill-switch', [FlowCtrl::class, 'tenantKillSwitch']);
        });
        // ============================================            // B-AI Gateway (BAB 21 + 22)            // ============================================            // Use AI (use)
        Route::prefix('ai-gateway')->middleware('perm:ai_gateway.use')->group(function () {
            Route::post('/chat', [AGCtrl::class, 'chat']);
        });
        // Models (models)
        Route::prefix('ai-gateway')->middleware('perm:ai_gateway.models')->group(function () {
            Route::get('/models', [AGCtrl::class, 'listModels']);
            Route::get('/metadata', [AGCtrl::class, 'metadata']);
        });
        // Wallet (wallet)
        Route::prefix('ai-gateway')->middleware('perm:ai_gateway.wallet')->group(function () {
            Route::get('/wallet', [AGCtrl::class, 'getWallet']);
            Route::get('/wallet/ledger', [AGCtrl::class, 'walletLedger']);
            Route::get('/usage/summary', [AGCtrl::class, 'usageSummary']);
            Route::post('/wallet/top-up', [AGCtrl::class, 'topUp']);
        });
        // Admin (admin)
        Route::prefix('ai-gateway/admin')->middleware('perm:ai_gateway.admin')->group(function () {
            Route::get('/models', [AGCtrl::class, 'adminListModels']);
            Route::post('/models', [AGCtrl::class, 'adminUpsertModel']);
            Route::post('/models/{id}/toggle', [AGCtrl::class, 'adminToggleModel'])->whereNumber('id');
            Route::get('/usage/by-model', [AGCtrl::class, 'adminUsageByModel']);
            Route::get('/usage/events', [AGCtrl::class, 'adminUsageEvents']);
        });
        // B-Helpdesk
        Route::middleware('perm:helpdesk.view')->group(function () {
            Route::get('helpdesk/tickets', [HelpdeskController::class, 'index']);
            Route::post('helpdesk/tickets', [HelpdeskController::class, 'store']);
            Route::get('helpdesk/tickets/{id}', [HelpdeskController::class, 'show'])->whereNumber('id');
            Route::post('helpdesk/tickets/{id}/reply', [HelpdeskController::class, 'reply'])->whereNumber('id');
            Route::put('helpdesk/tickets/{id}', [HelpdeskController::class, 'update'])->whereNumber('id');
            Route::get('helpdesk/categories', [HelpdeskController::class, 'categories']);
            Route::get('helpdesk/articles', [HelpdeskController::class, 'articles']);
            Route::get('helpdesk/sla', [HelpdeskController::class, 'slaPolicies']);
        });
        Route::middleware('perm:helpdesk.manage')->group(function () {
            Route::put('helpdesk/sla', [HelpdeskController::class, 'saveSla']);
            Route::post('helpdesk/categories', [HelpdeskController::class, 'saveCategory']);
            Route::post('helpdesk/articles', [HelpdeskController::class, 'saveArticle']);
            Route::put('helpdesk/articles/{id}', [HelpdeskController::class, 'saveArticle'])->whereNumber('id');
        });
        // B-CRM — Customer Relationship Management & Sales Force Automation
        Route::middleware('perm:crm.view')->group(function () {
            Route::get('crm/customers', [\App\Modules\CRM\Http\Controllers\CustomerController::class, 'index']);
            Route::get('crm/customers/{id}', [\App\Modules\CRM\Http\Controllers\CustomerController::class, 'show'])->whereNumber('id');
            Route::get('crm/leads', [\App\Modules\CRM\Http\Controllers\LeadController::class, 'index']);
            Route::get('crm/leads/{id}', [\App\Modules\CRM\Http\Controllers\LeadController::class, 'show'])->whereNumber('id');
            Route::get('crm/opportunities', [\App\Modules\CRM\Http\Controllers\OpportunityController::class, 'index']);
            Route::get('crm/opportunities/{id}', [\App\Modules\CRM\Http\Controllers\OpportunityController::class, 'show'])->whereNumber('id');
            Route::get('crm/quotations', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'index']);
            Route::get('crm/quotations/{id}', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'show'])->whereNumber('id');
            Route::get('crm/statistics/leads', [\App\Modules\CRM\Http\Controllers\LeadController::class, 'getStatistics']);
            Route::get('crm/statistics/pipeline', [\App\Modules\CRM\Http\Controllers\OpportunityController::class, 'getPipelineSummary']);
            Route::get('crm/statistics/quotations', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'getStatistics']);
            Route::get('crm/follow-ups', [\App\Modules\CRM\Http\Controllers\OpportunityController::class, 'getFollowUps']);
        });
        Route::middleware('perm:crm.manage')->group(function () {
            Route::post('crm/customers', [\App\Modules\CRM\Http\Controllers\CustomerController::class, 'store']);
            Route::put('crm/customers/{id}', [\App\Modules\CRM\Http\Controllers\CustomerController::class, 'update'])->whereNumber('id');
            Route::delete('crm/customers/{id}', [\App\Modules\CRM\Http\Controllers\CustomerController::class, 'destroy'])->whereNumber('id');
            Route::post('crm/leads', [\App\Modules\CRM\Http\Controllers\LeadController::class, 'store']);
            Route::put('crm/leads/{id}', [\App\Modules\CRM\Http\Controllers\LeadController::class, 'update'])->whereNumber('id');
            Route::post('crm/leads/{id}/convert', [\App\Modules\CRM\Http\Controllers\LeadController::class, 'convertToOpportunity'])->whereNumber('id');
            Route::post('crm/leads/{id}/assign', [\App\Modules\CRM\Http\Controllers\LeadController::class, 'assign'])->whereNumber('id');
            Route::post('crm/opportunities', [\App\Modules\CRM\Http\Controllers\OpportunityController::class, 'store']);
            Route::put('crm/opportunities/{id}', [\App\Modules\CRM\Http\Controllers\OpportunityController::class, 'update'])->whereNumber('id');
            Route::post('crm/opportunities/{id}/assign', [\App\Modules\CRM\Http\Controllers\OpportunityController::class, 'assign'])->whereNumber('id');
            Route::post('crm/quotations', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'store']);
            Route::put('crm/quotations/{id}', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'update'])->whereNumber('id');
            Route::post('crm/quotations/{id}/submit', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'submit'])->whereNumber('id');
            Route::post('crm/quotations/{id}/convert-order', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'convertToOrder'])->whereNumber('id');
        });
        Route::middleware('perm:crm.approve')->group(function () {
            Route::post('crm/quotations/{id}/approve', [\App\Modules\CRM\Http\Controllers\QuotationController::class, 'approve'])->whereNumber('id');
        });
        // B-Document AI — Intelligent Document Processing with OCR & ML
        Route::middleware('perm:documents.view')->group(function () {
            Route::get('document-ai/documents', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'index']);
            Route::get('document-ai/documents/{id}', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'show'])->whereNumber('id');
            Route::get('document-ai/documents/{id}/download', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'download'])->whereNumber('id');
            Route::get('document-ai/documents/statistics', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'getStatistics']);
        });
        Route::middleware('perm:documents.issue')->group(function () {
            Route::post('document-ai/documents', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'store']);
            Route::put('document-ai/documents/{id}', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'update'])->whereNumber('id');
            Route::post('document-ai/documents/{id}/reprocess', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'reprocess'])->whereNumber('id');
        });
        Route::middleware('perm:documents.void')->group(function () {
            Route::delete('document-ai/documents/{id}', [\App\Modules\DocumentAI\Http\Controllers\DocumentController::class, 'destroy'])->whereNumber('id');
        });
        // ============================================            // B-Invoicing — Quotation, Invoice, Credit/Debit Note & e-Invois LHDN            // ============================================
        // Quotation Routes (CRM handles basic quotations, Invoicing handles full lifecycle)
        Route::middleware('perm:invoicing.view')->group(function () {
            Route::get('invoicing/quotations', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'index']);
            Route::get('invoicing/quotations/{id}', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'show'])->whereNumber('id');
            Route::get('invoicing/invoices', [\App\Modules\Invoicing\Http\Controllers\InvoiceController::class, 'index']);
            Route::get('invoicing/invoices/statistics', [\App\Modules\Invoicing\Http\Controllers\InvoiceController::class, 'statistics']);
            Route::get('invoicing/invoices/{id}', [\App\Modules\Invoicing\Http\Controllers\InvoiceController::class, 'show'])->whereNumber('id');
            Route::get('invoicing/credit-notes', [\App\Modules\Invoicing\Http\Controllers\CreditNoteController::class, 'index']);
            Route::get('invoicing/recurring-billings', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'index']);
            Route::get('invoicing/recurring-billings/upcoming', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'upcoming']);
        });
        Route::middleware('perm:invoicing.manage')->group(function () {
            // Quotation management
            Route::post('invoicing/quotations', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'store']);
            Route::put('invoicing/quotations/{id}', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'update'])->whereNumber('id');
            Route::post('invoicing/quotations/{id}/submit', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'submit'])->whereNumber('id');
            Route::post('invoicing/quotations/{id}/accept', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'accept'])->whereNumber('id');
            Route::post('invoicing/quotations/{id}/reject', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'reject'])->whereNumber('id');
            Route::post('invoicing/quotations/{id}/convert-order', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'convertToOrder'])->whereNumber('id');
            Route::delete('invoicing/quotations/{id}', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'destroy'])->whereNumber('id');
            // Invoice creation & payment
            Route::post('invoicing/invoices/from-order/{orderId}', [\App\Modules\Invoicing\Http\Controllers\InvoiceController::class, 'createFromOrder'])->whereNumber('orderId');
            Route::post('invoicing/invoices/{id}/payment', [\App\Modules\Invoicing\Http\Controllers\InvoiceController::class, 'recordPayment'])->whereNumber('id');
            // Credit notes
            Route::post('invoicing/credit-notes', [\App\Modules\Invoicing\Http\Controllers\CreditNoteController::class, 'store']);
            Route::post('invoicing/credit-notes/{id}/apply', [\App\Modules\Invoicing\Http\Controllers\CreditNoteController::class, 'apply'])->whereNumber('id');
            // Recurring billing management
            Route::post('invoicing/recurring-billings', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'store']);
            Route::put('invoicing/recurring-billings/{id}', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'update'])->whereNumber('id');
            Route::post('invoicing/recurring-billings/{id}/pause', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'pause'])->whereNumber('id');
            Route::post('invoicing/recurring-billings/{id}/resume', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'resume'])->whereNumber('id');
            Route::post('invoicing/recurring-billings/{id}/cancel', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'cancel'])->whereNumber('id');
            Route::post('invoicing/recurring-billings/{id}/process-now', [\App\Modules\Invoicing\Http\Controllers\RecurringBillingController::class, 'processNow'])->whereNumber('id');
        });
        Route::middleware('perm:invoicing.approve')->group(function () {
            // Approve quotation
            Route::post('invoicing/quotations/{id}/approve', [\App\Modules\Invoicing\Http\Controllers\QuotationController::class, 'accept'])->whereNumber('id');
            // Submit to LHDN e-Invois
            Route::post('invoicing/invoices/{id}/submit-einvois', [\App\Modules\Invoicing\Http\Controllers\InvoiceController::class, 'submitEInvois'])->whereNumber('id');
            // Cancel invoice
            Route::post('invoicing/invoices/{id}/cancel', [\App\Modules\Invoicing\Http\Controllers\InvoiceController::class, 'cancel'])->whereNumber('id');
        });
        // ============================================            // B-Virtual Office — Agent, Task, Board & Schedule Management            // ============================================
        // Agents — Team members & AI agents
        Route::middleware('perm:virtual_office.view')->group(function () {
            Route::get('virtual-office/agents', [\App\Modules\VirtualOffice\Http\Controllers\AgentController::class, 'index']);
            Route::get('virtual-office/agents/{id}', [\App\Modules\VirtualOffice\Http\Controllers\AgentController::class, 'show'])->whereNumber('id');
            Route::get('virtual-office/agents/statistics', [\App\Modules\VirtualOffice\Http\Controllers\AgentController::class, 'statistics']);
        });
        Route::middleware('perm:virtual_office.manage')->group(function () {
            Route::post('virtual-office/agents', [\App\Modules\VirtualOffice\Http\Controllers\AgentController::class, 'store']);
            Route::put('virtual-office/agents/{id}', [\App\Modules\VirtualOffice\Http\Controllers\AgentController::class, 'update'])->whereNumber('id');
            Route::post('virtual-office/agents/{id}/status', [\App\Modules\VirtualOffice\Http\Controllers\AgentController::class, 'updateStatus'])->whereNumber('id');
            Route::delete('virtual-office/agents/{id}', [\App\Modules\VirtualOffice\Http\Controllers\AgentController::class, 'destroy'])->whereNumber('id');
        });
        // Boards — Kanban boards & columns
        Route::middleware('perm:virtual_office.view')->group(function () {
            Route::get('virtual-office/boards', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'index']);
            Route::get('virtual-office/boards/{id}', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:virtual_office.manage')->group(function () {
            Route::post('virtual-office/boards', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'store']);
            Route::put('virtual-office/boards/{id}', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'update'])->whereNumber('id');
            Route::post('virtual-office/boards/{id}/members', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'addMember'])->whereNumber('id');
            Route::delete('virtual-office/boards/{id}/members', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'removeMember'])->whereNumber('id');
            Route::post('virtual-office/boards/{id}/archive', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'archive'])->whereNumber('id');
            Route::post('virtual-office/boards/{id}/restore', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'restore'])->whereNumber('id');
            Route::post('virtual-office/boards/{id}/duplicate', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'duplicate'])->whereNumber('id');
            Route::delete('virtual-office/boards/{id}', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'destroy'])->whereNumber('id');
            Route::post('virtual-office/boards/{id}/columns', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'addColumn'])->whereNumber('id');
            Route::delete('virtual-office/boards/{id}/columns', [\App\Modules\VirtualOffice\Http\Controllers\BoardController::class, 'deleteColumn'])->whereNumber('id');
        });
        // Tasks/Cards — Task management on boards
        Route::middleware('perm:virtual_office.view')->group(function () {
            Route::get('virtual-office/tasks', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'index']);
            Route::get('virtual-office/tasks/{id}', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'show'])->whereNumber('id');
            Route::get('virtual-office/tasks/overdue', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'overdue']);
            Route::get('virtual-office/tasks/statistics', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'statistics']);
        });
        Route::middleware('perm:virtual_office.manage')->group(function () {
            Route::post('virtual-office/tasks', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'store']);
            Route::put('virtual-office/tasks/{id}', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'update'])->whereNumber('id');
            Route::post('virtual-office/tasks/{id}/move', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'move'])->whereNumber('id');
            Route::post('virtual-office/tasks/reorder', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'reorder']);
            Route::post('virtual-office/tasks/{id}/complete', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'complete'])->whereNumber('id');
            Route::post('virtual-office/tasks/{id}/comments', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'addComment'])->whereNumber('id');
            Route::post('virtual-office/tasks/{id}/assign', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'assign'])->whereNumber('id');
            Route::delete('virtual-office/tasks/{id}', [\App\Modules\VirtualOffice\Http\Controllers\TaskController::class, 'destroy'])->whereNumber('id');
        });
        // Schedules — Meetings & calendar events
        Route::middleware('perm:virtual_office.view')->group(function () {
            Route::get('virtual-office/schedules', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'index']);
            Route::get('virtual-office/schedules/{id}', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'show'])->whereNumber('id');
            Route::get('virtual-office/schedules/today', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'today']);
            Route::get('virtual-office/schedules/upcoming', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'upcoming']);
            Route::get('virtual-office/schedules/statistics', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'statistics']);
        });
        Route::middleware('perm:virtual_office.manage')->group(function () {
            Route::post('virtual-office/schedules', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'store']);
            Route::put('virtual-office/schedules/{id}', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'update'])->whereNumber('id');
            Route::post('virtual-office/schedules/{id}/cancel', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'cancel'])->whereNumber('id');
            Route::post('virtual-office/schedules/{id}/complete', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'complete'])->whereNumber('id');
            Route::post('virtual-office/schedules/{id}/respond', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'respond'])->whereNumber('id');
            Route::delete('virtual-office/schedules/{id}', [\App\Modules\VirtualOffice\Http\Controllers\ScheduleController::class, 'destroy'])->whereNumber('id');
        });
        // ============================================            // B-HRMS — Employee, Shift & Leave Management            // ============================================
        // HRMS Employees
        Route::middleware('perm:hrms.view')->group(function () {
            Route::get('hrms/employees', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'index']);
            Route::get('hrms/employees/{id}', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'show'])->whereNumber('id');
            Route::get('hrms/employees/expiring-contracts', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'getExpiringContracts']);
            Route::get('hrms/employees/probationers', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'getProbationers']);
            Route::get('hrms/employees/{id}/attendance-summary', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'attendanceSummary'])->whereNumber('id');
        });
        Route::middleware('perm:hrms.manage')->group(function () {
            Route::post('hrms/employees', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'store']);
            Route::put('hrms/employees/{id}', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'update'])->whereNumber('id');
            Route::post('hrms/employees/{id}/terminate', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'terminate'])->whereNumber('id');
            Route::post('hrms/employees/{id}/assign-shift', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'assignShift'])->whereNumber('id');
            Route::post('hrms/employees/bulk-import', [\App\Modules\HRMS\Http\Controllers\EmployeeController::class, 'bulkImport']);
        });

        // ============================================
        // Biometrik — pagar consent face recognition (BAB 56C.6A)
        // ============================================
        Route::middleware('perm:biometric.view')->group(function () {
            Route::get('hrms/biometric/settings', [\App\Modules\HRMS\Http\Controllers\BiometricController::class, 'settings']);
            Route::get('hrms/biometric/consent-status', [\App\Modules\HRMS\Http\Controllers\BiometricController::class, 'statusConsent']);
        });
        Route::middleware('perm:biometric.configure')->group(function () {
            Route::put('hrms/biometric/settings', [\App\Modules\HRMS\Http\Controllers\BiometricController::class, 'simpanSettings']);
            Route::post('hrms/biometric/consent', [\App\Modules\HRMS\Http\Controllers\BiometricController::class, 'beriConsent']);
            Route::delete('hrms/biometric/consent', [\App\Modules\HRMS\Http\Controllers\BiometricController::class, 'tarikConsent']);
        });
        Route::middleware('perm:biometric.audit')->group(function () {
            Route::get('hrms/biometric/audit', [\App\Modules\HRMS\Http\Controllers\BiometricController::class, 'audit']);
        });

        // ============================================
        // B-Vision — Kamera & peristiwa CCTV (BAB 56C Fasa 1)
        // ============================================
        Route::middleware('perm:vision.view')->group(function () {
            Route::get('vision/cameras', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'cameras']);
            Route::get('vision/cameras/{id}', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'showCamera'])->whereNumber('id');
            Route::get('vision/events', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'events']);
            Route::get('vision/dashboard', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'dashboard']);
            Route::get('vision/alerts/pending', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'belumDiakui']);
        });
        Route::middleware('perm:vision.manage')->group(function () {
            Route::post('vision/cameras', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'storeCamera']);
            Route::put('vision/cameras/{id}', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'updateCamera'])->whereNumber('id');
            Route::delete('vision/cameras/{id}', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'destroyCamera'])->whereNumber('id');
            Route::post('vision/cameras/{id}/test', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'testCamera'])->whereNumber('id');
            Route::put('vision/cameras/{id}/retention', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'setRetention'])->whereNumber('id');
            Route::post('vision/events/{id}/acknowledge', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'acknowledgeEvent'])->whereNumber('id');
            Route::post('vision/view-logs', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'logView']);
        });
        Route::middleware('perm:vision.audit')->group(function () {
            Route::get('vision/view-logs', [\App\Modules\Vision\Http\Controllers\VisionController::class, 'viewLogs']);
        });
        // HRMS Departments
        Route::middleware('perm:hrms.view')->group(function () {
            Route::get('hrms/departments', [\App\Modules\HRMS\Http\Controllers\DepartmentController::class, 'index']);
            Route::get('hrms/departments/{id}', [\App\Modules\HRMS\Http\Controllers\DepartmentController::class, 'show'])->whereNumber('id');
            Route::get('hrms/departments/statistics', [\App\Modules\HRMS\Http\Controllers\DepartmentController::class, 'statistics']);
        });
        Route::middleware('perm:hrms.manage')->group(function () {
            Route::post('hrms/departments', [\App\Modules\HRMS\Http\Controllers\DepartmentController::class, 'store']);
            Route::put('hrms/departments/{id}', [\App\Modules\HRMS\Http\Controllers\DepartmentController::class, 'update'])->whereNumber('id');
            Route::delete('hrms/departments/{id}', [\App\Modules\HRMS\Http\Controllers\DepartmentController::class, 'destroy'])->whereNumber('id');
        });
        // HRMS Leave Types
        Route::middleware('perm:hrms.view')->group(function () {
            Route::get('hrms/leave/types', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'leaveTypes']);
        });
        Route::middleware('perm:hrms.manage')->group(function () {
            Route::post('hrms/leave/types', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'createLeaveType']);
        });
        // HRMS Leave Requests
        Route::middleware('perm:hrms.view')->group(function () {
            Route::get('hrms/leave/pending', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'getPending']);
            Route::get('hrms/leave/balance', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'getBalance']);
            Route::get('hrms/leave/history', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'getHistory']);
        });
        Route::middleware('perm:hrms.approve')->group(function () {
            Route::post('hrms/leave/requests', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'submitRequest']);
            Route::post('hrms/leave/auto-approve', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'autoApprove']);
            Route::post('hrms/leave/requests/{id}/approve', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'approveRequest'])->whereNumber('id');
            Route::post('hrms/leave/requests/{id}/reject', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'rejectRequest'])->whereNumber('id');
        });
        Route::middleware('perm:hrms.manage')->group(function () {
            Route::post('hrms/leave/requests/{id}/cancel', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'cancelRequest'])->whereNumber('id');
            Route::post('hrms/leave/allocate', [\App\Modules\HRMS\Http\Controllers\LeaveController::class, 'allocateLeave']);
        });
        // HRMS Attendance
        Route::middleware('perm:hrms.view')->group(function () {
            Route::post('hrms/attendance/check-in', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'checkIn']);
            Route::post('hrms/attendance/check-out', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'checkOut']);
            Route::get('hrms/attendance/today', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'getToday']);
            Route::get('hrms/attendance', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'index']);
            Route::get('hrms/attendance/daily-summary', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'dailySummary']);
            Route::get('hrms/attendance/monthly-report', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'monthlyReport']);
        });
        Route::middleware('perm:hrms.manage')->group(function () {
            Route::post('hrms/attendance/mark', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'markAttendance']);
            Route::post('hrms/attendance/bulk-mark', [\App\Modules\HRMS\Http\Controllers\AttendanceController::class, 'bulkMark']);
        });
        // HRMS Shifts
        Route::middleware('perm:hrms.view')->group(function () {
            Route::get('hrms/shifts', [\App\Modules\HRMS\Http\Controllers\ShiftController::class, 'index']);
            Route::get('hrms/shifts/{id}', [\App\Modules\HRMS\Http\Controllers\ShiftController::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:hrms.manage')->group(function () {
            Route::post('hrms/shifts', [\App\Modules\HRMS\Http\Controllers\ShiftController::class, 'store']);
            Route::put('hrms/shifts/{id}', [\App\Modules\HRMS\Http\Controllers\ShiftController::class, 'update'])->whereNumber('id');
            Route::delete('hrms/shifts/{id}', [\App\Modules\HRMS\Http\Controllers\ShiftController::class, 'destroy'])->whereNumber('id');
            Route::post('hrms/shifts/{id}/toggle-status', [\App\Modules\HRMS\Http\Controllers\ShiftController::class, 'toggleStatus'])->whereNumber('id');
        });
        // ============================================
        // B-Projects — didaftar oleh ProjectsServiceProvider di prefix `pm/`
        // (elak pertindihan dengan endpoint Api\V1 `projects/*` di atas)
        // ============================================
        // ============================================            // B-Payroll — Malaysian Statutory Payroll            // CORRECTED rates per audit A-02:            // - EPF: 11% employee (ALL salary levels)            // - EPF Employer: 13% (<=RM5K) / 12% (>RM5K)            // - SOCSO: Tier-based per PERKESO table            // - EIS: 0.2% max RM100/year            // - HRDF: 1% NO CAP (corrected from 0.5% with cap)            // - Overtime: 1.5x/2x/3x per Akta Kerja 1955            // ============================================
        // B-Payroll Employees
        Route::middleware('perm:payroll.view')->group(function () {
            Route::get('payroll/employees', [\App\Modules\Payroll\Http\Controllers\EmployeeController::class, 'index']);
            Route::get('payroll/employees/{id}', [\App\Modules\Payroll\Http\Controllers\EmployeeController::class, 'show'])->whereNumber('id');
            Route::get('payroll/statutory-rates/current', [\App\Modules\Payroll\Http\Controllers\StatutoryRatesController::class, 'current']);
        });
        Route::middleware('perm:payroll.manage')->group(function () {
            Route::post('payroll/employees', [\App\Modules\Payroll\Http\Controllers\EmployeeController::class, 'store']);
            Route::put('payroll/employees/{id}', [\App\Modules\Payroll\Http\Controllers\EmployeeController::class, 'update'])->whereNumber('id');
            Route::post('payroll/employees/{id}/deactivate', [\App\Modules\Payroll\Http\Controllers\EmployeeController::class, 'deactivate'])->whereNumber('id');
            // Overtime management
            Route::get('payroll/overtime', [\App\Modules\Payroll\Http\Controllers\OvertimeController::class, 'index']);
            Route::post('payroll/overtime', [\App\Modules\Payroll\Http\Controllers\OvertimeController::class, 'store']);
            Route::put('payroll/overtime/{id}/approve', [\App\Modules\Payroll\Http\Controllers\OvertimeController::class, 'approve'])->whereNumber('id');
            Route::put('payroll/overtime/{id}/reject', [\App\Modules\Payroll\Http\Controllers\OvertimeController::class, 'reject'])->whereNumber('id');
            // Statutory rates configuration
            Route::get('payroll/statutory-rates', [\App\Modules\Payroll\Http\Controllers\StatutoryRatesController::class, 'index']);
            Route::post('payroll/statutory-rates', [\App\Modules\Payroll\Http\Controllers\StatutoryRatesController::class, 'store']);
            // Payroll runs
            Route::get('payroll/runs', [\App\Modules\Payroll\Http\Controllers\PayrollRunController::class, 'index']);
            Route::post('payroll/runs', [\App\Modules\Payroll\Http\Controllers\PayrollRunController::class, 'store']);
            Route::get('payroll/runs/{id}', [\App\Modules\Payroll\Http\Controllers\PayrollRunController::class, 'show'])->whereNumber('id');
            Route::post('payroll/runs/{id}/approve', [\App\Modules\Payroll\Http\Controllers\PayrollRunController::class, 'approve'])->whereNumber('id');
            Route::post('payroll/runs/{id}/finalize', [\App\Modules\Payroll\Http\Controllers\PayrollRunController::class, 'finalize'])->whereNumber('id');
            // Payslip generation
            Route::get('payroll/payslips/{detailId}', [\App\Modules\Payroll\Http\Controllers\PayrollRunController::class, 'payslip'])->whereNumber('detailId');
            Route::get('payroll/payslips/{detailId}/download', [\App\Modules\Payroll\Http\Controllers\PayrollRunController::class, 'downloadPayslip'])->whereNumber('detailId');

            // Eksport statutori (BAB 57) — EPF/SOCSO/EIS/PCB/bank
            Route::get('payroll/runs/{runId}/export', [\App\Modules\Payroll\Http\Controllers\PayrollExportController::class, 'senarai'])->whereNumber('runId');
            Route::get('payroll/runs/{runId}/export/{jenis}', [\App\Modules\Payroll\Http\Controllers\PayrollExportController::class, 'muatTurun'])
                ->whereNumber('runId')->where('jenis', 'epf|socso|eis|pcb|bank|semua');
        });
        // ============================================            // B-Finance — Financial Management            // ============================================
        // Chart of Accounts
        Route::middleware('perm:finance.view')->group(function () {
            Route::get('finance/chart-of-accounts', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'index']);
            Route::get('finance/chart-of-accounts/{id}', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:finance.manage')->group(function () {
            Route::post('finance/chart-of-accounts', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'store']);
            Route::put('finance/chart-of-accounts/{id}', [\App\Modules\Finance\Http\Controllers\ChartOfAccountsController::class, 'update'])->whereNumber('id');
            // Journal Entries
            Route::get('finance/journal-entries', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'index']);
            Route::get('finance/journal-entries/{id}', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'show'])->whereNumber('id');
            Route::post('finance/journal-entries', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'store']);
            Route::post('finance/journal-entries/{id}/post', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'post'])->middleware('perm:finance.approve')->whereNumber('id');
            Route::post('finance/journal-entries/{id}/cancel', [\App\Modules\Finance\Http\Controllers\JournalController::class, 'cancel'])->middleware('perm:finance.approve')->whereNumber('id');
        });
        // Financial Reports (read-only)
        Route::middleware('perm:finance.reports')->group(function () {
            Route::get('finance/reports/profit-loss', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'profitLoss']);
            Route::get('finance/reports/balance-sheet', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'balanceSheet']);
            Route::get('finance/reports/trial-balance', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'trialBalance']);
            Route::get('finance/reports/general-ledger/{accountId}', [\App\Modules\Finance\Http\Controllers\FinancialReportController::class, 'generalLedger'])->whereNumber('accountId');
        });
        // ============================================            // B-SCM — Supply Chain Management            // ============================================
        // Suppliers
        Route::middleware('perm:inventory.view')->group(function () {
            Route::get('scm/suppliers', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'index']);
            Route::get('scm/suppliers/{id}', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'show'])->whereNumber('id');
        });
        Route::middleware('perm:inventory.manage')->group(function () {
            Route::post('scm/suppliers', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'store']);
            Route::put('scm/suppliers/{id}', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'update'])->whereNumber('id');
            Route::post('scm/suppliers/{id}/approve', [\App\Modules\SCM\Http\Controllers\SupplierController::class, 'approve'])->whereNumber('id');
            // Purchase Orders
            Route::get('scm/purchase-orders', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'index']);
            Route::get('scm/purchase-orders/{id}', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'show'])->whereNumber('id');
            Route::post('scm/purchase-orders', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'store']);
            Route::post('scm/purchase-orders/{id}/submit', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'submit'])->whereNumber('id');
            Route::post('scm/purchase-orders/{id}/approve', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'approve'])->whereNumber('id');
            Route::post('scm/purchase-orders/{id}/send', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'send'])->whereNumber('id');
            Route::post('scm/purchase-orders/{id}/receive', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'receive'])->whereNumber('id');
            Route::post('scm/purchase-orders/{id}/cancel', [\App\Modules\SCM\Http\Controllers\PurchaseOrderController::class, 'cancel'])->whereNumber('id');            });
        });
        // Super Admin platform (2FA wajib)
        Route::prefix('admin')->middleware('platform')->group(function () {
            Route::get('tenants', [PlatformController::class, 'tenants']);
            Route::put('tenants/{id}/status', [PlatformController::class, 'setStatus'])->whereNumber('id');
            Route::post('tenants/{id}/support-access', [PlatformController::class, 'grantSupport'])->whereNumber('id');
            Route::delete('support-access/{grant}', [PlatformController::class, 'revokeSupport'])->whereNumber('grant');
            Route::get('plans', [PlatformController::class, 'plans']);
            Route::post('plans', [PlatformController::class, 'savePlan']);
            Route::put('plans/{id}', [PlatformController::class, 'savePlan'])->whereNumber('id');        });    });});
