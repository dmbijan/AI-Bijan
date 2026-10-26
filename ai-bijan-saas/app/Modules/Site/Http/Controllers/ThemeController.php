<?php

namespace App\Modules\Site\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Site\Services\ThemeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ThemeController extends Controller
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    /**
     * List all themes for a tenant.
     */
    public function index(Request $request): JsonResponse
    {
        $themes = $this->themeService->getThemes($request->tenant_id);
        
        return response()->json([
            'success' => true,
            'data' => $themes,
        ]);
    }

    /**
     * Get active theme for a site.
     */
    public function getActive(Request $request, int $siteId): JsonResponse
    {
        $theme = $this->themeService->getActiveTheme($siteId, $request->tenant_id);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'No active theme found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $theme,
        ]);
    }

    /**
     * Create a new theme.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'author' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'preview_image' => 'nullable|string|max:500',
            'settings' => 'nullable|array',
            'site_id' => 'nullable|integer',
        ]);

        $validated['tenant_id'] = $request->tenant_id;

        $theme = $this->themeService->createTheme($validated);

        return response()->json([
            'success' => true,
            'message' => 'Theme created successfully.',
            'data' => $theme,
        ], 201);
    }

    /**
     * Update a theme.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $theme = $this->themeService->getThemes($request->tenant_id)->firstWhere('id', $id);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found.',
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'nullable|string|max:255',
            'version' => 'nullable|string|max:50',
            'author' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'preview_image' => 'nullable|string|max:500',
            'settings' => 'nullable|array',
        ]);

        $theme = $this->themeService->updateTheme($theme, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Theme updated successfully.',
            'data' => $theme,
        ]);
    }

    /**
     * Delete a theme.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $theme = $this->themeService->getThemes($request->tenant_id)->firstWhere('id', $id);

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found.',
            ], 404);
        }

        $this->themeService->deleteTheme($theme);

        return response()->json([
            'success' => true,
            'message' => 'Theme deleted successfully.',
        ]);
    }

    /**
     * Activate a theme for a site.
     */
    public function activate(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|integer',
        ]);

        $theme = $this->themeService->activateTheme(
            $id,
            $validated['site_id'],
            $request->tenant_id
        );

        if (!$theme) {
            return response()->json([
                'success' => false,
                'message' => 'Theme not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Theme activated successfully.',
            'data' => $theme,
        ]);
    }

    /**
     * Apply theme settings.
     */
    public function applySettings(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'site_id' => 'required|integer',
            'settings' => 'required|array',
        ]);

        $result = $this->themeService->applyThemeSettings(
            $validated['site_id'],
            $request->tenant_id,
            $validated['settings']
        );

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply theme settings.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Theme settings applied successfully.',
        ]);
    }

    public function show(Request $request = null)
    {
        return response()->json(['message' => 'Belum dilaksanakan', 'method' => 'show']);
    }
}
