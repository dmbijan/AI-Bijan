<?php

namespace App\Modules\Marketing\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\Marketing\Services\CampaignService;
use App\Modules\Marketing\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$request->tenant_id` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected CampaignService $campaignService;

    public function __construct(CampaignService $campaignService)
    {
        $this->campaignService = $campaignService;
    }

    /**
     * List all campaigns.
     */
    public function index(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $filters = $request->only(['status', 'type', 'active_only']);

        $campaigns = $this->campaignService->getCampaigns($tenantId, $filters);

        return response()->json([
            'success' => true,
            'data' => $campaigns,
        ]);
    }

    /**
     * Get a single campaign.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();

        $campaign = $this->campaignService->getCampaignById($id, $tenantId);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $campaign,
        ]);
    }

    /**
     * Create a new campaign.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:email,sms,whatsapp,social_media,paid_ads,multi_channel',
            'channels' => 'required|array',
            'channels.*' => 'in:email,sms,whatsapp,social_media,paid_ads',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'goals' => 'nullable|array',
        ]);

        $tenantId = app(TenantContext::class)->id();
        $data = $request->all();
        $data['tenant_id'] = $tenantId;

        $campaign = $this->campaignService->createCampaign($data);

        return response()->json([
            'success' => true,
            'data' => $campaign,
            'message' => 'Campaign created successfully.',
        ], 201);
    }

    /**
     * Update a campaign.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $campaign = $this->campaignService->getCampaignById($id, $tenantId);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:email,sms,whatsapp,social_media,paid_ads,multi_channel',
            'channels' => 'sometimes|array',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:draft,scheduled,running,paused,completed,cancelled',
        ]);

        $campaign = $this->campaignService->updateCampaign($campaign, $request->all());

        return response()->json([
            'success' => true,
            'data' => $campaign,
            'message' => 'Campaign updated successfully.',
        ]);
    }

    /**
     * Delete a campaign.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $campaign = $this->campaignService->getCampaignById($id, $tenantId);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        $this->campaignService->deleteCampaign($campaign);

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully.',
        ]);
    }

    /**
     * Start a campaign.
     */
    public function start(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $campaign = $this->campaignService->getCampaignById($id, $tenantId);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        $campaign = $this->campaignService->startCampaign($campaign);

        return response()->json([
            'success' => true,
            'data' => $campaign,
            'message' => 'Campaign started.',
        ]);
    }

    /**
     * Pause a campaign.
     */
    public function pause(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $campaign = $this->campaignService->getCampaignById($id, $tenantId);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        $campaign = $this->campaignService->pauseCampaign($campaign);

        return response()->json([
            'success' => true,
            'data' => $campaign,
            'message' => 'Campaign paused.',
        ]);
    }

    /**
     * Complete a campaign.
     */
    public function complete(Request $request, int $id): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $campaign = $this->campaignService->getCampaignById($id, $tenantId);

        if (!$campaign) {
            return response()->json([
                'success' => false,
                'message' => 'Campaign not found.',
            ], 404);
        }

        $campaign = $this->campaignService->completeCampaign($campaign);

        return response()->json([
            'success' => true,
            'data' => $campaign,
            'message' => 'Campaign completed.',
        ]);
    }

    /**
     * Get campaign statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $tenantId = app(TenantContext::class)->id();
        $stats = $this->campaignService->getCampaignStats($tenantId);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
