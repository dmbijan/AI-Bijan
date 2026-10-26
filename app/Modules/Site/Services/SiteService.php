<?php

namespace App\Modules\Site\Services;

use App\Modules\Site\Models\Site;
use Illuminate\Support\Facades\DB;

class SiteService
{
    /**
     * Get all sites for a tenant.
     */
    public function getSites(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Site::where('tenant_id', $tenantId)->get();
    }

    /**
     * Get a site by ID.
     */
    public function getSiteById(int $siteId, int $tenantId): ?Site
    {
        return Site::where('id', $siteId)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Get a site by slug.
     */
    public function getSiteBySlug(string $slug, int $tenantId): ?Site
    {
        return Site::where('slug', $slug)->where('tenant_id', $tenantId)->first();
    }

    /**
     * Create a new site.
     */
    public function createSite(array $data): Site
    {
        if (empty($data['slug'])) {
            $data['slug'] = Site::generateSlug($data['name']);
        }

        return DB::transaction(function () use ($data) {
            return Site::create($data);
        });
    }

    /**
     * Update a site.
     */
    public function updateSite(Site $site, array $data): Site
    {
        return DB::transaction(function () use ($site, $data) {
            $site->update($data);
            return $site->fresh();
        });
    }

    /**
     * Delete a site.
     */
    public function deleteSite(Site $site): bool
    {
        return $site->delete();
    }

    /**
     * Toggle live status.
     */
    public function toggleLive(Site $site): Site
    {
        return $site->update(['is_live' => !$site->is_live]) ? $site->fresh() : null;
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Site $site): Site
    {
        return $site->update(['maintenance_mode' => !$site->maintenance_mode]) ? $site->fresh() : null;
    }

    /**
     * Toggle coming soon mode.
     */
    public function toggleComingSoon(Site $site): Site
    {
        return $site->update(['coming_soon_mode' => !$site->coming_soon_mode]) ? $site->fresh() : null;
    }

    /**
     * Set primary domain.
     */
    public function setPrimaryDomain(Site $site, string $domain): Site
    {
        return $site->update(['primary_domain' => $domain]) ? $site->fresh() : null;
    }

    /**
     * Add secondary domain.
     */
    public function addSecondaryDomain(Site $site, string $domain): Site
    {
        $domains = $site->secondary_domains ?? [];
        if (!in_array($domain, $domains)) {
            $domains[] = $domain;
            $site->update(['secondary_domains' => $domains]);
        }
        return $site->fresh();
    }

    /**
     * Remove secondary domain.
     */
    public function removeSecondaryDomain(Site $site, string $domain): Site
    {
        $domains = collect($site->secondary_domains ?? [])
            ->reject(fn($d) => $d === $domain)
            ->values()
            ->toArray();
        $site->update(['secondary_domains' => $domains]);
        return $site->fresh();
    }

    /**
     * Get unique slug.
     */
    public function getUniqueSlug(string $name, int $tenantId): string
    {
        $baseSlug = Site::generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (Site::where('slug', $slug)->where('tenant_id', $tenantId)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
