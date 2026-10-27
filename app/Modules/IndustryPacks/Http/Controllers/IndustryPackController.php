<?php

namespace App\Modules\IndustryPacks\Http\Controllers;

use App\Support\Tenancy\TenantContext;

use App\Http\Controllers\Controller;
use App\Modules\IndustryPacks\Models\IndustryProfile;
use App\Modules\IndustryPacks\Services\IndustryProfileService;
use App\Modules\IndustryPacks\Services\DashboardLayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class IndustryPackController extends Controller
{

    /**
     * ID tenant semasa — MESTI dari konteks keahlian, BUKAN input klien.
     *
     * ⚠️ 27 Okt 2026: kod lama guna `$this->tenantId()` yang sentiasa
     * `null` (Request tiada atribut itu) → HTTP 500 atau senarai kosong.
     */
    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }
    protected $profileService;
    protected $dashboardService;

    public function __construct(
        IndustryProfileService $profileService,
        DashboardLayoutService $dashboardService
    ) {
        $this->profileService = $profileService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * List all available industries
     */
    public function listIndustries(): JsonResponse
    {
        $industries = $this->profileService->getAvailableIndustries();
        $formatted = [];

        foreach ($industries as $key => $industry) {
            $formatted[] = [
                'key' => $key,
                'name' => $industry['name'],
                'description' => $industry['description'],
                'enabled_modules' => $industry['enabled_modules'],
                'default_dashboard' => $industry['default_dashboard'],
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $formatted,
        ]);
    }

    /**
     * Get industry details including metrics, costs, templates
     */
    public function getDetails(string $industryKey): JsonResponse
    {
        $industry = IndustryProfile::getByKey($industryKey);

        if (!$industry) {
            return response()->json([
                'success' => false,
                'message' => "Industry '{$industryKey}' not found.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'info' => $industry,
                'metrics' => config('industry_packs.metrics')[$industryKey] ?? [],
                'cost_components' => config('industry_packs.cost_components')[$industryKey] ?? [],
                'templates' => config('industry_packs.templates')[$industryKey] ?? [],
                'workflows' => config('industry_packs.workflows')[$industryKey] ?? [],
                'dashboard' => config('industry_packs.dashboards')[$industryKey] ?? null,
            ],
        ]);
    }

    /**
     * Apply industry pack to tenant
     */
    public function apply(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'industry_key' => 'required|string|in:' . implode(',', array_keys(IndustryProfile::getAvailableIndustries())),
        ]);

        try {
            $result = $this->profileService->applyIndustryPack(
                $this->tenantId(),
                $request->industry_key
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get current industry profile for tenant
     */
    public function getTenantProfile(int $tenantId): JsonResponse
    {
        $profile = $this->profileService->getTenantProfile($tenantId);

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'No industry profile found for this tenant.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $profile,
        ]);
    }

    /**
     * Change industry pack for tenant
     */
    public function changeIndustry(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'industry_key' => 'required|string|in:' . implode(',', array_keys(IndustryProfile::getAvailableIndustries())),
        ]);

        try {
            $result = $this->profileService->changeIndustryPack(
                $this->tenantId(),
                $request->industry_key
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Recommend industry based on business description
     */
    public function recommend(Request $request): JsonResponse
    {
        $request->validate([
            'business_keywords' => 'required|string|min:10',
        ]);

        $recommendation = $this->profileService->recommendIndustry(
            $request->business_keywords
        );

        return response()->json([
            'success' => true,
            'data' => $recommendation,
        ]);
    }

    /**
     * Get dashboard layout for industry
     */
    public function getDashboardLayout(string $industryKey): JsonResponse
    {
        try {
            $layout = $this->dashboardService->getDashboardLayout($industryKey);

            return response()->json([
                'success' => true,
                'data' => $layout,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get widgets filtered by role
     */
    public function getWidgetsForRole(string $industryKey, string $role): JsonResponse
    {
        $widgets = $this->dashboardService->getWidgetsForRole($industryKey, $role);

        return response()->json([
            'success' => true,
            'data' => $widgets,
        ]);
    }

    /**
     * Customize dashboard for tenant
     */
    public function customizeDashboard(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'industry_key' => 'required|string',
            'widgets' => 'required|array',
        ]);

        try {
            $result = $this->dashboardService->customizeDashboard(
                $this->tenantId(),
                $request->industry_key,
                $request->widgets
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reset dashboard to default
     */
    public function resetDashboard(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => 'required|integer',
            'industry_key' => 'required|string',
        ]);

        try {
            $result = $this->dashboardService->resetToDefault(
                $this->tenantId(),
                $request->industry_key
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
