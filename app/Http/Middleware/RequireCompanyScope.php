<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\DataScope;
use Closure;
use Illuminate\Http\Request;

/**
 * OAM: data peringkat syarikat (bank, tunai, SST, bajet, kunci kira-kira, imbangan duga) tidak boleh dipecah
 * ikut cawangan dengan betul. Ahli berskop cawangan/jabatan ditolak dan diberitahu sebabnya.
 */
class RequireCompanyScope
{
    public function handle(Request $request, Closure $next)
    {
        abort_if(app(DataScope::class)->restricted(), 403, 'Maklumat ini peringkat syarikat. Hanya ahli berskop seluruh organisasi boleh melihatnya.');

        return $next($request);
    }
}
