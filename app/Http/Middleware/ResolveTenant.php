<?php

namespace App\Http\Middleware;

use App\Models\SupportAccessGrant;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;

/**
 * Tentukan tenant dari sesi/token yang disahkan + keahlian aktif pengguna.
 * Header X-Tenant hanya MEMILIH antara tenant yang pengguna memang ahli; ia bukan bukti kebenaran.
 */
class ResolveTenant
{
    /** Kebenaran baca sahaja semasa akses sokongan. */
    public const KEBENARAN_SOKONGAN = ['tenant.settings.view', 'members.view', 'audit.view'];

    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    private function aksesSokongan(Request $request, Closure $next, $user, string $ulid)
    {
        abort_unless($user->two_factor_confirmed_at && $request->hasSession() && $request->session()->get('2fa_ok') === $user->id, 403, 'Sahkan 2FA dahulu.');
        $tenant = Tenant::where('ulid', $ulid)->first();
        abort_unless($tenant, 404);
        $grant = SupportAccessGrant::where('tenant_id', $tenant->id)->where('admin_user_id', $user->id)->whereNull('revoked_at')->where('expires_at', '>', now())->latest()->first();
        abort_unless($grant, 403, 'Tiada akses sokongan yang sah untuk syarikat ini.');
        abort_unless($request->isMethod('GET'), 403, 'Akses sokongan adalah baca sahaja.');
        $this->ctx()->set($tenant, null, self::KEBENARAN_SOKONGAN);
        $this->tetapkanTenantRequest($request, $tenant);
        app(AuditLogger::class)->log('support.access', $tenant, null, ['path' => $request->path(), 'grant' => $grant->id]);
        try {
            return $next($request);
        } finally {
            $this->ctx()->clear();
        }
    }

    /**
     * Banyak controller modul membaca `$request->tenant_id`. Tulis ganti dengan tenant yang disahkan supaya
     * nilainya sentiasa betul dan klien tak boleh hantar tenant_id syarikat lain dalam body/query.
     */
    private function tetapkanTenantRequest(Request $request, Tenant $tenant): void
    {
        $request->merge(['tenant_id' => $tenant->id]);
        $request->query->set('tenant_id', $tenant->id);
    }

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        abort_unless($user && $user->is_active, 401);
        $pilih = $request->header('X-Tenant') ?: ($request->hasSession() ? $request->session()->get('tenant_ulid') : null);
        $q = TenantUser::withoutGlobalScope('tenant')->where('user_id', $user->id)->where('status', 'active');
        $m = $pilih ? (clone $q)->whereHas('tenant', fn ($t) => $t->where('ulid', $pilih))->first() : null;
        $m ??= $pilih ? null : $q->orderBy('id')->first();
        if (! $m && $pilih && $user->is_platform_admin) {
            return $this->aksesSokongan($request, $next, $user, $pilih);
        }
        abort_unless($m, 403, 'Anda bukan ahli syarikat ini.');
        $tenant = Tenant::find($m->tenant_id);
        abort_unless($tenant && $tenant->isUsable(), 403, 'Akaun syarikat digantung atau dibatalkan.');
        $this->ctx()->set($tenant, $m);
        $this->tetapkanTenantRequest($request, $tenant);
        try {
            return $next($request);
        } finally {
            $this->ctx()->clear();
        }
    }
}
