<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SupportAccessGrant;
use App\Models\Tenant;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

/**
 * Super Admin platform. Hanya metadata tenant (nama, status, pelan, bilangan ahli) — TIADA data perniagaan.
 * Akses data tenant hanya melalui geran sokongan bersebab & terhad masa (baca sahaja, dilog di kedua-dua pihak).
 */
class PlatformController extends Controller
{
    private function audit(): AuditLogger
    {
        return app(AuditLogger::class);
    }

    public function tenants(Request $r)
    {
        return response()->json(Tenant::with('plan:id,code,name')->withCount(['members' => fn ($q) => $q->withoutGlobalScope('tenant')->where('status', 'active')])
            ->when($r->query('q'), fn ($q, $s) => $q->where('name', 'like', "%$s%"))->orderByDesc('id')->paginate(50, ['id', 'ulid', 'name', 'slug', 'status', 'plan_id', 'trial_ends_at', 'created_at']));
    }

    public function setStatus(Request $r, int $id)
    {
        $d = $r->validate(['status' => 'required|in:trial,active,past_due,suspended,cancelled', 'reason' => 'required|string|max:255']);
        $t = Tenant::findOrFail($id);
        $before = ['status' => $t->status];
        $t->update(['status' => $d['status']]);
        $this->audit()->log('platform.tenant_status', $t, $before, ['status' => $d['status'], 'reason' => $d['reason']], $t->id);
        $this->audit()->log('platform.tenant_status', $t, $before, ['status' => $d['status'], 'reason' => $d['reason'], 'tenant' => $t->ulid], null);

        return response()->json(['ok' => true]);
    }

    public function plans()
    {
        return response()->json(Plan::orderBy('price_monthly')->get());
    }

    public function savePlan(Request $r, ?int $id = null)
    {
        $d = $r->validate(['code' => 'required|string|max:40|regex:/^[a-z0-9_-]+$/', 'name' => 'required|string|max:80', 'max_users' => 'nullable|integer|min:1', 'max_locations' => 'nullable|integer|min:1',
            'storage_mb' => 'nullable|integer|min:1', 'ai_credits_monthly' => 'nullable|integer|min:0', 'modules' => 'nullable|array', 'price_monthly' => 'required|numeric|min:0', 'is_active' => 'boolean']);
        $p = $id ? Plan::findOrFail($id) : new Plan;
        $before = $p->exists ? $p->toArray() : null;
        $p->fill($d)->save();
        $this->audit()->log($id ? 'platform.plan_updated' : 'platform.plan_created', $p, $before, $p->toArray(), null);

        return response()->json($p, $id ? 200 : 201);
    }

    public function grantSupport(Request $r, int $id)
    {
        $d = $r->validate(['reason' => 'required|string|min:10|max:255', 'minutes' => 'required|integer|min:5|max:240']);
        $t = Tenant::findOrFail($id);
        $g = SupportAccessGrant::create(['tenant_id' => $t->id, 'admin_user_id' => $r->user()->id, 'reason' => $d['reason'], 'expires_at' => now()->addMinutes($d['minutes'])]);
        $this->audit()->log('support.granted', $g, null, ['reason' => $d['reason'], 'minutes' => $d['minutes'], 'admin' => $r->user()->email], $t->id);

        return response()->json(['id' => $g->id, 'tenant' => $t->ulid, 'expires_at' => $g->expires_at], 201);
    }

    public function revokeSupport(Request $r, int $grant)
    {
        $g = SupportAccessGrant::where('admin_user_id', $r->user()->id)->findOrFail($grant);
        $g->update(['revoked_at' => now()]);
        $this->audit()->log('support.revoked', $g, null, null, $g->tenant_id);

        return response()->json(['ok' => true]);
    }
}
