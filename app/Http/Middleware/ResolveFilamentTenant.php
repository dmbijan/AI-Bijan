<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantUser;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;

/**
 * ResolveFilamentTenant — tetapkan TenantContext untuk panel Filament.
 *
 * ⚠️ Panel Filament menggunakan guard `web` (sesi), bukan laluan API.
 * Middleware `tenant` sedia ada tidak dijalankan, jadi kita perlu
 * menetapkan konteks tenant SEBELUM sebarang resource/widget query.
 *
 * Peraturan keselamatan (sama seperti ResolveTenant):
 *  - Tenant dipilih daripada KEAHLIAN pengguna, bukan input pengguna.
 *  - Sesi `tenant_ulid` hanya MEMILIH antara tenant yang pengguna ahli.
 *  - Tiada keahlian → konteks dikosongkan (query tenant akan pulang 0 rekod).
 */
class ResolveFilamentTenant
{
    public function handle(Request $request, Closure $next)
    {
        $ctx = app(TenantContext::class);
        $ctx->clear();

        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Pilih tenant daripada sesi (jika ada)
        $pilih = $request->hasSession() ? $request->session()->get('tenant_ulid') : null;

        $q = TenantUser::withoutGlobalScope('tenant')
            ->where('user_id', $user->id)
            ->where('status', 'active');

        $membership = $pilih
            ? (clone $q)->whereHas('tenant', fn ($t) => $t->where('ulid', $pilih))->first()
            : $q->orderBy('id')->first();

        if (! $membership) {
            return $next($request);
        }

        $tenant = Tenant::find($membership->tenant_id);

        if (! $tenant || ! $tenant->isUsable()) {
            return $next($request);
        }

        $ctx->set($tenant, $membership);

        // Pastikan konteks dibersihkan selepas request (elak bocor antara request)
        try {
            return $next($request);
        } finally {
            $ctx->clear();
        }
    }
}
