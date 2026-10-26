<?php

namespace App\Modules\Entitlement\Services;

use Illuminate\Support\Str;

/**
 * ModuleGate — semak entitlemen modul berdasarkan namespace kebenaran.
 *
 * ⚠️ AUDIT BAB 90: jadual `plans`, `subscriptions`, `tenant_modules` sudah
 * wujud dalam platform core tetapi TIDAK PERNAH digunakan. Modul ini
 * menghidupkannya sebagai gate sebenar.
 *
 * Mekanisme:
 *  - Permission dicam: `orders.view` → prefix `orders` → module `orders`
 *  - Alias dipetakan (cth `content.view` → `content_studio`)
 *  - Permission teras (platform) TIDAK dikawal modul
 *  - Jika tenant tiada langganan → deny (deny-by-default)
 */
class ModuleGate
{
    /**
     * Permission namespace yang BUKAN modul domain — sentiasa dibenarkan.
     */
    public const TERAS = [
        'members', 'roles', 'audit', 'files', 'api_keys', 'approvals',
        'documents', 'biometric', 'ai_gateway', 'tenant', 'platform',
        'entitlement', 'fundamental',
    ];

    /**
     * Alias namespace permission → module_key.
     *
     * Permission namespace tidak sentiasa sama dengan nama folder modul.
     */
    public const ALIAS = [
        'content' => 'content_studio',
        'workflow' => 'workflow_studio',
        'media' => 'media_studio',
        'media_editor' => 'media_editor',
        'service_os' => 'service_os',
        'mcp' => 'mcp_studio',
        'agent_studio' => 'agent_studio',
        'personal_ai' => 'personal_assistant',
        'virtual_office' => 'virtual_office',
        'ai_agents' => 'ai_agents',
        'business_intel' => 'business_intel',
        'proof_of_value' => 'proof_of_value',
        'industry_packs' => 'industry_packs',
        'sign_docs' => 'sign_docs',
        'sales_copilot' => 'sales_copilot',
        'executive_ai' => 'executive_ai',
        'live_support' => 'live_support',
        'ade' => 'ade',

        // ⚠️ AUDIT T-71: Resource Filament menggunakan namespace permission
        // yang wujud dalam jadual `permissions`, tetapi kunci modul dalam
        // jadual `plans` menggunakan nama domain yang berbeza. Tanpa alias
        // ini, menu tersembunyi walaupun pemilik mempunyai kebenaran.
        'finance' => 'expense',          // Perbelanjaan (modul `expense`)
        'omnichannel' => 'orders',       // Pesanan (modul `orders`)
        'projects' => 'service_os',      // Projek perkhidmatan (modul `service_os`)
        'marketing' => 'content_studio', // Kandungan (modul `content_studio`)
        'brainhub' => 'business_intel',  // Idea bisnes (modul `business_intel`)
    ];

    /**
     * Adakah namespace permission ini modul domain (bukan teras)?
     */
    public static function ialahModul(string $namespace): bool
    {
        if (in_array($namespace, self::TERAS, true)) {
            return false;
        }

        return true;
    }

    /**
     * Dapatkan module_key bagi namespace permission.
     */
    public static function moduleKey(string $namespace): string
    {
        if (isset(self::ALIAS[$namespace])) {
            return self::ALIAS[$namespace];
        }

        return $namespace;
    }

    /**
     * Semak entitlemen untuk set permission.
     *
     * @return array{allowed: bool, module: ?string}
     */
    public static function semak(array $permissions, EntitlementService $servis, ?int $tenantId): array
    {
        if ($tenantId === null || $tenantId === 0) {
            return ['allowed' => true, 'module' => null];
        }

        $modulPerlu = [];

        foreach ($permissions as $permission) {
            $namespace = Str::before($permission, '.');

            if (! self::ialahModul($namespace)) {
                continue;
            }

            $modulPerlu[] = self::moduleKey($namespace);
        }

        if ($modulPerlu === []) {
            return ['allowed' => true, 'module' => null];
        }

        // Semua modul yang diperlukan mesti dibenarkan
        foreach (array_unique($modulPerlu) as $module) {
            if (! $servis->bolehModul($tenantId, $module)) {
                return ['allowed' => false, 'module' => $module];
            }
        }

        return ['allowed' => true, 'module' => null];
    }
}
