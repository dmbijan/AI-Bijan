<?php

namespace App\Http\Middleware;

use App\Modules\Entitlement\Services\EntitlementService;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;

/**
 * RequireModule — gate akses modul mengikut langganan tenant.
 *
 * ⚠️ AUDIT BAB 90 (Audit MD §4.3): sebelum ini TIADA Mechanism yang
 * mengekawal modul mana boleh diakses. Tenant yang bayar RM88 boleh
 * access semua 55 modul — lubang bisnes.
 *
 * Penggunaan: `Route::get(...)->middleware(['tenant', 'module:seo'])`
 * atau `['tenant', 'module:seo,content']` (salah satu suffice).
 *
 * Cara lain (tanpa middleware): semak dalam servis/kawalload.
 */
class RequireModule
{
    public function handle(Request $request, Closure $next, string ...$modules)
    {
        $tenantId = app(TenantContext::class)->id();

        if (! $tenantId) {
            return response()->json([
                'success' => false,
                'message' => 'Konteks syarikat tidak dijumpai.',
            ], 403);
        }

        $servis = app(EntitlementService::class);

        foreach ($modules as $module) {
            if ($servis->bolehModul($tenantId, $module)) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Modul ini tidak termasuk dalam langganan syarikat anda.',
            'meta' => [
                'required_modules' => $modules,
                'upgrade' => 'Hubungi kami untuk menambah modul.',
            ],
        ], 403);
    }
}
