<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Super Admin platform: mesti pentadbir platform DAN 2FA disahkan dalam sesi ini. */
class PlatformAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $u = $request->user();
        abort_unless($u && $u->is_platform_admin && $u->is_active, 403);
        abort_unless($u->two_factor_confirmed_at && $request->session()->get('2fa_ok') === $u->id, 403, 'Sahkan 2FA dahulu.');

        return $next($request);
    }
}
