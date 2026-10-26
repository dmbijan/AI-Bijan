<?php

namespace App\Modules\Link\Services;

use App\Modules\Link\Models\LinkPage;
use App\Modules\Link\Models\DigitalCard;
use App\Modules\Link\Models\Click;
use Illuminate\Pagination\LengthAwarePaginator;

class LinkService
{
    /**
     * Get all link pages for a tenant.
     */
    public function getLinkPages(int $tenantId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = LinkPage::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('short_code', 'like', "%{$search}%");
            });
        }

        return $query->orderByDesc('created_at')->paginate($perPage);
    }

    /**
     * Get link page by short code.
     */
    public function getLinkByCode(string $code, int $tenantId): ?LinkPage
    {
        return LinkPage::where('short_code', strtoupper($code))
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Track a click on link.
     */
    public function trackClick(string $code, int $tenantId, array $clickData = []): Click
    {
        $linkPage = $this->getLinkByCode($code, $tenantId);

        if (!$linkPage) {
            throw new \InvalidArgumentException('Link not found or inactive');
        }

        return Click::create([
            'tenant_id' => $tenantId,
            'link_page_id' => $linkPage->id,
            'click_id' => md5(uniqid('click_', true)),
            'ip_address' => $clickData['ip_address'] ?? request()->ip(),
            'user_agent' => $clickData['user_agent'] ?? request()->userAgent(),
            'referrer_url' => $clickData['referrer_url'] ?? request()->header('referer'),
            'country' => $clickData['country'] ?? null,
            'region' => $clickData['region'] ?? null,
            'city' => $clickData['city'] ?? null,
            'device' => $clickData['device'] ?? null,
            'browser' => $clickData['browser'] ?? null,
            'os' => $clickData['os'] ?? null,
        ]);
    }

    /**
     * Generate unique short code.
     */
    public static function generateShortCode(): string
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (LinkPage::where('short_code', $code)->exists());

        return $code;
    }

    /**
     * Get link analytics.
     */
    public function getLinkAnalytics(int $tenantId, array $filters = []): array
    {
        $totalLinks = LinkPage::where('tenant_id', $tenantId)->count();
        $activeLinks = LinkPage::where('tenant_id', $tenantId)->where('status', 'active')->count();
        $totalClicks = Click::where('tenant_id', $tenantId)->count();
        $uniqueClicks = Click::where('tenant_id', $tenantId)->distinct('click_id')->count('click_id');

        return [
            'total_links' => $totalLinks,
            'active_links' => $activeLinks,
            'total_clicks' => $totalClicks,
            'unique_clicks' => $uniqueClicks,
        ];
    }
}
