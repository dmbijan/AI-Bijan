<?php

namespace App\Modules\Site\Services;

use App\Modules\Site\Models\Theme;
use Illuminate\Support\Facades\DB;

class ThemeService
{
    /**
     * Get all themes for a tenant.
     */
    public function getThemes(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Theme::where('tenant_id', $tenantId)->get();
    }

    /**
     * Get active theme for a site.
     */
    public function getActiveTheme(int $siteId, int $tenantId): ?Theme
    {
        return Theme::where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create a new theme.
     */
    public function createTheme(array $data): Theme
    {
        return DB::transaction(function () use ($data) {
            return Theme::create($data);
        });
    }

    /**
     * Update a theme.
     */
    public function updateTheme(Theme $theme, array $data): Theme
    {
        return DB::transaction(function () use ($theme, $data) {
            $theme->update($data);
            return $theme->fresh();
        });
    }

    /**
     * Delete a theme.
     */
    public function deleteTheme(Theme $theme): bool
    {
        return $theme->delete();
    }

    /**
     * Activate a theme for a site.
     */
    public function activateTheme(int $themeId, int $siteId, int $tenantId): ?Theme
    {
        // Deactivate all other themes for this site
        Theme::where('site_id', $siteId)
            ->where('tenant_id', $tenantId)
            ->update(['is_active' => false]);

        $theme = Theme::where('id', $themeId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($theme) {
            $theme->update(['is_active' => true]);
            return $theme->fresh();
        }

        return null;
    }

    /**
     * Set default theme.
     */
    public function setDefaultTheme(int $themeId, int $tenantId): ?Theme
    {
        // Deactivate all other default themes
        Theme::where('tenant_id', $tenantId)
            ->where('is_default', true)
            ->update(['is_default' => false]);

        $theme = Theme::where('id', $themeId)
            ->where('tenant_id', $tenantId)
            ->first();

        if ($theme) {
            $theme->update(['is_default' => true]);
            return $theme->fresh();
        }

        return null;
    }

    /**
     * Apply theme settings to site.
     */
    public function applyThemeSettings(int $siteId, int $tenantId, array $settings): bool
    {
        return DB::transaction(function () use ($siteId, $tenantId, $settings) {
            $theme = Theme::where('site_id', $siteId)
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->first();

            if ($theme) {
                $themeSettings = $theme->settings ?? [];
                $theme->update(['settings' => array_merge($themeSettings, $settings)]);
                return true;
            }

            return false;
        });
    }
}
