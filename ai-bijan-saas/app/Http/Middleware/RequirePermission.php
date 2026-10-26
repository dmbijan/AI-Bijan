<?php

namespace App\Http\Middleware;

use App\Modules\Entitlement\Services\EntitlementService;
use App\Modules\Entitlement\Services\ModuleGate;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;

/**
 * RequirePermission — semak kebenaran RBAC + entitlemen modul.
 *
 * Dua lapisan:
 *  1. **RBAC** — pengguna ada kebenaran `resource.action` dalam tenant.
 *  2. **Entitlement** — modul yang diminta termasuk dalam langganan tenant.
 *
 * ⚠️ AUDIT BAB 90: sebelum ini lapisan (2) TIDAK wujud langsung — tenant
 * yang bayar RM88 boleh access semua 54 modul. Kini gate enforceable
 * secara automatik untuk setiap laluan `perm:`.
 *
 * Layer platform (members, roles, audit, files, ...) tidak dikawal modul.
 */
class RequirePermission
{
    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    public function handle(Request $request, Closure $next, string ...$perms)
    {
        foreach ($perms as $p) {
            abort_unless($this->ctx()->can($p), 403, 'Anda tiada kebenaran untuk tindakan ini.');
        }

        // Lapisan 2 — entitlemen modul
        if ($perms !== [] && config('entitlement.enabled', true)) {
            $tenantId = (int) $this->ctx()->id();

            if ($tenantId > 0) {
                $hasil = ModuleGate::semak($perms, app(EntitlementService::class), $tenantId);

                if (! $hasil['allowed']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Modul ini tidak termasuk dalam langganan syarikat anda.',
                        'meta' => [
                            'module' => $hasil['module'],
                            'upgrade' => 'Hubungi kami untuk menambah modul.',
                        ],
                    ], 403);
                }
            }
        }

        return $next($request);
    }
}
