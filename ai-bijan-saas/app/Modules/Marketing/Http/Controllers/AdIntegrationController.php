<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;
use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\AdIntegrationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdIntegrationController extends Controller
{
    protected AdIntegrationService $adIntegrationService;

    public function __construct(AdIntegrationService $adIntegrationService)
    {
        $this->adIntegrationService = $adIntegrationService;
    }

    /**
     * Sync Google Ads performance.
     */
    public function syncGoogle(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $result = $this->adIntegrationService->syncGoogleAds(
            app(TenantContext::class)->id(),
            $request->customer_id,
            [
                'start' => $request->date_from,
                'end' => $request->date_to,
            ]
        );

        return response()->json($result);
    }

    /**
     * Sync Facebook Ads performance.
     */
    public function syncFacebook(Request $request): JsonResponse
    {
        $request->validate([
            'ad_account_id' => 'required|string',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        $result = $this->adIntegrationService->syncFacebookAds(
            app(TenantContext::class)->id(),
            $request->ad_account_id,
            [
                'start' => $request->date_from,
                'end' => $request->date_to,
            ]
        );

        return response()->json($result);
    }

    /**
     * Create Google Ads campaign.
     */
    public function createGoogleCampaign(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|string',
            'name' => 'required|string|max:255',
            'budget' => 'required|array',
            'bidding_strategy' => 'required|array',
        ]);

        $result = $this->adIntegrationService->createGoogleAdCampaign($request->all());

        return response()->json($result);
    }

    /**
     * Create Facebook Ads campaign.
     */
    public function createFacebookCampaign(Request $request): JsonResponse
    {
        $request->validate([
            'account_id' => 'required|string',
            'name' => 'required|string|max:255',
            'objective' => 'required|string',
            'daily_budget' => 'required|numeric|min:1',
        ]);

        $result = $this->adIntegrationService->createFacebookAdCampaign($request->all());

        return response()->json($result);
    }

    /**
     * Get campaign performance summary.
     */
    public function getPerformance(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:google,facebook',
        ]);

        $result = $this->adIntegrationService->getPerformanceSummary(
            app(TenantContext::class)->id(),
            $request->platform
        );

        return response()->json($result);
    }

    /**
     * Validate API credentials.
     */
    public function validateCredentials(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:google,facebook',
        ]);

        $result = $this->adIntegrationService->validateCredentials($request->platform);

        return response()->json($result);
    }

    /**
     * Pause all campaigns on a platform.
     */
    public function pauseAll(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:google,facebook',
            'account_id' => 'required|string',
        ]);

        $success = $this->adIntegrationService->pauseAllCampaigns(
            $request->platform,
            $request->account_id
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'All campaigns paused.' : 'Failed to pause campaigns.',
        ]);
    }

    /**
     * Resume all campaigns on a platform.
     */
    public function resumeAll(Request $request): JsonResponse
    {
        $request->validate([
            'platform' => 'required|in:google,facebook',
            'account_id' => 'required|string',
        ]);

        $success = $this->adIntegrationService->resumeAllCampaigns(
            $request->platform,
            $request->account_id
        );

        return response()->json([
            'success' => $success,
            'message' => $success ? 'All campaigns resumed.' : 'Failed to resume campaigns.',
        ]);
    }
}
