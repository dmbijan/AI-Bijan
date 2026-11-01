<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MembershipScope;
use App\Models\OrgUnit;
use App\Models\Role;
use App\Models\TenantUser;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Services\AccessService;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** OAM: struktur organisasi, skop & status keahlian, semakan akses, seat, jemputan, sesi. */
class OrganizationController extends Controller
{
    public function __construct(private AccessService $access) {}

    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    private function log(string $action, $model, ?array $old, array $new): void
    {
        app(AuditLogger::class)->log($action, $model, $old, $new);
    }

    private function unitRule(): array
    {
        return ['nullable', 'integer', Rule::exists('org_units', 'id')->where('tenant_id', $this->ctx()->id())];
    }

    private function isOwner(TenantUser $m): bool
    {
        return $m->roles()->where('key', 'tenant_owner')->exists();
    }

    private function activeOwners(): int
    {
        return TenantUser::where('status', 'active')->whereHas('roles', fn ($q) => $q->where('key', 'tenant_owner'))->count();
    }

    // ------------------------------------------------------------ ringkasan

    public function overview(): JsonResponse
    {
        $units = OrgUnit::where('status', 'active')->select('type', DB::raw('count(*) n'))->groupBy('type')->pluck('n', 'type');
        $ent = app(EntitlementService::class)->ringkasan($this->ctx()->id());

        return response()->json(['data' => [
            'units' => ['group' => (int) ($units['group'] ?? 0), 'company' => (int) ($units['company'] ?? 0), 'branch' => (int) ($units['branch'] ?? 0),
                'department' => (int) ($units['department'] ?? 0), 'team' => (int) ($units['team'] ?? 0)],
            'members' => TenantUser::get()->groupBy(fn ($m) => $m->state)->map->count(),
            'seats' => $this->access->seats($this->ctx()->id()),
            'roles' => Role::count(),
            'subscription' => $ent,
            'pending_approvals' => DB::table('approval_requests')->where('tenant_id', $this->ctx()->id())->where('status', 'pending')->count(),
        ]]);
    }

    // ------------------------------------------------------------ struktur

    public function units(): JsonResponse
    {
        $counts = MembershipScope::whereNotNull('org_unit_id')->select('org_unit_id', DB::raw('count(*) n'))->groupBy('org_unit_id')->pluck('n', 'org_unit_id');

        return response()->json(['data' => OrgUnit::orderBy('type')->orderBy('name')->get()->map(fn ($u) => $u->toArray() + ['members' => (int) ($counts[$u->id] ?? 0)])]);
    }

    private const PARENT_OK = [
        'group' => [null],
        'company' => [null, 'group'],
        'branch' => ['company'],
        'department' => ['company', 'branch', 'department'],
        'team' => ['department', 'branch'],
    ];

    public function saveUnit(Request $r, ?int $id = null): JsonResponse
    {
        $d = $r->validate([
            'type' => ($id ? 'sometimes' : 'required').'|in:group,company,branch,department,team', 'name' => ($id ? 'sometimes' : 'required').'|string|max:150',
            'parent_id' => $this->unitRule(), 'code' => 'nullable|string|max:30', 'legal_name' => 'nullable|string|max:200',
            'registration_number' => 'nullable|string|max:40', 'address' => 'nullable|string|max:255', 'country' => 'nullable|string|size:2',
            'timezone' => 'nullable|timezone', 'currency' => 'nullable|string|size:3',
        ]);
        $u = $id ? OrgUnit::findOrFail($id) : new OrgUnit(['created_by' => $r->user()->id]);
        $type = $d['type'] ?? $u->type;
        $parentId = array_key_exists('parent_id', $d) ? $d['parent_id'] : $u->parent_id;
        $parent = $parentId ? OrgUnit::findOrFail($parentId) : null;
        abort_unless(in_array($parent?->type, self::PARENT_OK[$type], true), 422, 'Unit '.$type.' tidak boleh diletakkan di bawah '.($parent?->type ?? 'akar').'.');
        if ($parent) {
            $depth = 1;
            for ($p = $parent; $p; $p = $p->parent_id ? OrgUnit::find($p->parent_id) : null) {
                abort_if($u->exists && $p->id === $u->id, 422, 'Unit tidak boleh berada di bawah dirinya sendiri.');
                abort_if(++$depth > AccessService::MAX_ORG_DEPTH, 422, 'Struktur terlalu dalam (maks '.AccessService::MAX_ORG_DEPTH.' tingkat).');
            }
        }
        if (! empty($d['code'])) {
            abort_if(OrgUnit::where('code', $d['code'])->when($u->exists, fn ($q) => $q->where('id', '!=', $u->id))->exists(), 422, 'Kod sudah digunakan.');
        }
        if ($type === 'company' && ! $u->exists) {
            $max = app(EntitlementService::class)->had($this->ctx()->id(), 'companies');
            abort_if($max !== null && OrgUnit::where('type', 'company')->where('status', 'active')->count() >= $max, 409, 'Had bilangan syarikat untuk pakej ini sudah dicapai.');
        }
        if ($type === 'branch' && ! $u->exists) {
            $max = app(EntitlementService::class)->plan($this->ctx()->id())?->max_locations;
            abort_if($max !== null && OrgUnit::where('type', 'branch')->where('status', 'active')->count() >= $max, 409, 'Had bilangan cawangan untuk pakej ini sudah dicapai ('.$max.').');
        }
        $old = $u->exists ? $u->only(array_keys($d)) : null;
        $u->fill($d + ['parent_id' => $parentId])->save();
        $this->log($id ? 'org.unit_updated' : 'org.unit_created', $u, $old, $d);

        return response()->json(['data' => $u], $id ? 200 : 201);
    }

    /** Tiada padam: arkib sahaja, supaya rekod kewangan & sejarah kekal. */
    public function archiveUnit(int $id): JsonResponse
    {
        $u = OrgUnit::findOrFail($id);
        abort_if(OrgUnit::where('parent_id', $u->id)->where('status', 'active')->exists(), 422, 'Arkibkan atau pindahkan unit di bawahnya dahulu.');
        $u->update(['status' => $u->status === 'active' ? 'archived' : 'active']);
        $this->log('org.unit_archived', $u, null, ['status' => $u->status]);

        return response()->json(['data' => $u]);
    }

    // ------------------------------------------------------------ tautan unit (skop data)

    private const LINKS = [
        'warehouse' => [\App\Models\Warehouse::class, 'Gudang / outlet'],
        'register' => [\App\Modules\POS\Models\POSRegister::class, 'Kaunter POS'],
        'employee' => [\App\Modules\HRMS\Models\Employee::class, 'Pekerja'],
    ];

    /** Rekod yang boleh ditautkan ke unit. Hanya untuk pentadbir berskop seluruh organisasi. */
    public function links(): JsonResponse
    {
        abort_if(app(\App\Support\Tenancy\DataScope::class)->restricted(), 403, 'Hanya ahli berskop seluruh organisasi boleh menautkan unit.');
        $out = [];
        foreach (self::LINKS as $type => [$cls, $label]) {
            $out[$type] = ['label' => $label, 'rows' => $cls::withoutGlobalScope('org_unit')->get()->map(fn ($x) => [
                'id' => $x->id, 'org_unit_id' => $x->org_unit_id,
                'name' => $type === 'employee' ? trim($x->first_name.' '.$x->last_name) : $x->name,
                'code' => $x->code ?? $x->employee_no ?? null,
                'hint' => $type === 'register' && ! $x->org_unit_id && $x->warehouse_id ? 'ikut gudang' : null,
            ])->values()];
        }

        return response()->json(['data' => $out]);
    }

    public function setLink(Request $r): JsonResponse
    {
        abort_if(app(\App\Support\Tenancy\DataScope::class)->restricted(), 403, 'Hanya ahli berskop seluruh organisasi boleh menautkan unit.');
        $d = $r->validate(['type' => 'required|in:'.implode(',', array_keys(self::LINKS)), 'ids' => 'required|array|min:1|max:500', 'ids.*' => 'integer', 'org_unit_id' => $this->unitRule()]);
        $cls = self::LINKS[$d['type']][0];
        $rows = $cls::withoutGlobalScope('org_unit')->whereIn('id', $d['ids'])->get();
        abort_if($rows->count() !== count(array_unique($d['ids'])), 404);
        foreach ($rows as $x) {
            $x->forceFill(['org_unit_id' => $d['org_unit_id'] ?? null])->save();
        }
        $this->log('org.linked', $rows->first(), null, ['type' => $d['type'], 'ids' => $d['ids'], 'org_unit_id' => $d['org_unit_id'] ?? null]);

        return response()->json(['data' => ['updated' => $rows->count()]]);
    }

    // ------------------------------------------------------------ ahli

    public function members(): JsonResponse
    {
        $scopes = MembershipScope::get()->keyBy('tenant_user_id');
        $units = OrgUnit::pluck('name', 'id');
        $last = DB::table('sessions')->whereNotNull('user_id')->select('user_id', DB::raw('MAX(last_activity) t'))->groupBy('user_id')->pluck('t', 'user_id');

        return response()->json(['data' => TenantUser::with(['user:id,name,email', 'roles:id,key,name'])->orderBy('id')->get()->map(fn ($m) => [
            'id' => $m->id, 'user_id' => $m->user_id, 'name' => $m->user->name, 'email' => $m->user->email, 'employee_no' => $m->employee_no,
            'state' => $m->state, 'status_reason' => $m->status_reason, 'roles' => $m->roles->map->only(['key', 'name']),
            'org_unit_id' => $scopes[$m->id]->org_unit_id ?? null, 'org_unit' => isset($scopes[$m->id]) ? ($units[$scopes[$m->id]->org_unit_id] ?? null) : null,
            'data_scope' => $scopes[$m->id]->data_scope ?? 'all', 'selected_unit_ids' => $scopes[$m->id]->selected_unit_ids ?? [],
            'expires_at' => $m->expires_at?->toDateString(), 'joined_at' => $m->joined_at, 'invite_expires_at' => $m->invite_expires_at,
            'last_active_at' => isset($last[$m->user_id]) ? date(DATE_ATOM, (int) $last[$m->user_id]) : null,
            'is_me' => $m->user_id === auth()->id(),
        ]), 'meta' => ['data_scopes' => AccessService::DATA_SCOPES]]);
    }

    public function setScope(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'org_unit_id' => $this->unitRule(), 'data_scope' => 'required|in:'.implode(',', array_keys(AccessService::DATA_SCOPES)),
            'selected_unit_ids' => 'nullable|array', 'selected_unit_ids.*' => ['integer', Rule::exists('org_units', 'id')->where('tenant_id', $this->ctx()->id())],
            'expires_at' => 'nullable|date|after:today', 'employee_no' => 'nullable|string|max:30',
        ]);
        $m = TenantUser::findOrFail($id);
        abort_if(in_array($d['data_scope'], ['own_unit', 'unit_tree', 'own'], true) && empty($d['org_unit_id']), 422, 'Pilih unit untuk skop ini.');
        abort_if($d['data_scope'] === 'selected' && empty($d['selected_unit_ids']), 422, 'Pilih sekurang-kurangnya satu unit.');
        abort_if($this->isOwner($m) && ! empty($d['expires_at']), 422, 'Akses pemilik tidak boleh bertarikh tamat.');
        abort_if($m->user_id === $r->user()->id && $d['data_scope'] !== 'all' && ! $this->ctx()->can('roles.manage'), 422, 'Tidak boleh menyempitkan skop diri sendiri.');
        $old = MembershipScope::where('tenant_user_id', $m->id)->first()?->toArray();
        MembershipScope::updateOrCreate(['tenant_user_id' => $m->id], ['org_unit_id' => $d['org_unit_id'] ?? null, 'data_scope' => $d['data_scope'], 'selected_unit_ids' => $d['selected_unit_ids'] ?? null]);
        $m->update(['expires_at' => $d['expires_at'] ?? null, 'employee_no' => $d['employee_no'] ?? $m->employee_no]);
        $this->log('members.scope_changed', $m, $old, $d);

        return response()->json(['ok' => true]);
    }

    private function changeState(Request $r, int $id, string $to): JsonResponse
    {
        $d = $r->validate(['reason' => 'nullable|string|max:255']);
        $m = TenantUser::findOrFail($id);
        abort_if($m->user_id === $r->user()->id && $to !== 'active', 422, 'Tidak boleh menggantung atau menarik balik akses diri sendiri.');
        abort_if($this->isOwner($m) && ! $this->ctx()->can('roles.manage'), 403, 'Hanya pemilik boleh mengubah akses pemilik.');
        $before = ['state' => $m->state];
        DB::transaction(function () use ($m, $to, $d) {
            if ($to === 'active') {
                abort_if($m->revoked_at, 422, 'Akses yang ditarik balik perlu dijemput semula.');
                abort_unless($m->status === 'active' || $this->access->seatAvailable($this->ctx()->id()), 409, 'Seat pengguna sudah penuh.');
                $m->update(['status' => 'active', 'suspended_at' => null, 'status_reason' => null]);
            } else {
                $m->update(['status' => 'disabled', 'status_reason' => $d['reason'] ?? null, 'suspended_at' => $m->suspended_at ?? now()] + ($to === 'revoked' ? ['revoked_at' => now(), 'invite_token_hash' => null] : []));
            }
            abort_if($this->activeOwners() < 1, 422, 'Mesti ada sekurang-kurangnya seorang pemilik aktif.');
        });
        $this->log('members.'.$to, $m, $before, ['state' => $m->fresh()->state, 'reason' => $d['reason'] ?? null]);

        return response()->json(['data' => ['state' => $m->fresh()->state]]);
    }

    public function suspend(Request $r, int $id): JsonResponse
    {
        return $this->changeState($r, $id, 'suspended');
    }

    public function revoke(Request $r, int $id): JsonResponse
    {
        return $this->changeState($r, $id, 'revoked');
    }

    public function reactivate(Request $r, int $id): JsonResponse
    {
        return $this->changeState($r, $id, 'active');
    }

    public function effective(int $id): JsonResponse
    {
        $m = TenantUser::with('roles')->findOrFail($id);
        $chain = $this->access->roleChain($m->roles);

        return response()->json(['data' => [
            'permissions' => $this->access->permissionsFor($m),
            'denied' => $chain->flatMap(fn ($r) => $r->denies->pluck('key'))->unique()->values(),
            'inherited_from' => $chain->whereNotIn('id', $m->roles->pluck('id'))->pluck('name')->values(),
            'limits' => $this->access->limitsFor($m),
            'visible_unit_ids' => $this->access->visibleUnitIds($m),
            'state' => $m->state,
        ]]);
    }

    public function check(Request $r): JsonResponse
    {
        $d = $r->validate([
            'permission' => 'required|string|max:80', 'member_id' => 'nullable|integer', 'amount' => 'nullable|numeric|min:0',
            'limit_key' => 'nullable|in:'.implode(',', array_keys(AccessService::LIMIT_KEYS)), 'org_unit_id' => 'nullable|integer',
        ]);
        $m = ! empty($d['member_id']) ? TenantUser::findOrFail($d['member_id']) : $this->ctx()->membership();
        abort_unless($m, 403);
        abort_if($m->id !== $this->ctx()->membership()?->id && ! $this->ctx()->can('members.view'), 403);

        return response()->json(['data' => $this->access->check($m, $d['permission'], array_filter($d, fn ($v) => $v !== null))]);
    }

    // ------------------------------------------------------------ peranan

    public function cloneRole(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['name' => 'required|string|max:80']);
        $src = Role::with(['permissions', 'denies', 'limits'])->findOrFail($id);
        $lebih = $this->access->canGrant($this->ctx()->permissions(), collect([$src]));
        abort_if($lebih, 403, 'Anda tidak boleh menyalin peranan yang lebih berkuasa daripada anda.');
        $key = Str::slug($d['name'], '_') ?: 'peranan';
        $base = $key;
        for ($i = 2; Role::where('key', $key)->exists(); $i++) {
            $key = mb_substr($base, 0, 36).'_'.$i;
        }
        $role = DB::transaction(function () use ($src, $d, $key) {
            $role = Role::create(['key' => $key, 'name' => $d['name'], 'is_system' => false, 'description' => 'Salinan '.$src->name]);
            $role->permissions()->sync($src->permissions->pluck('id'));
            $role->denies()->sync($src->denies->pluck('id'));
            foreach ($src->limits as $l) {
                $role->limits()->create(['limit_key' => $l->limit_key, 'value' => $l->value]);
            }

            return $role;
        });
        $this->log('roles.cloned', $role, null, ['from' => $src->key]);

        return response()->json(['data' => ['id' => $role->id, 'key' => $role->key]], 201);
    }

    /** Matriks peranan × modul: hanya modul yang dilanggan boleh diberi (entitlement ≠ kebenaran). */
    public function modules(): JsonResponse
    {
        $ent = app(EntitlementService::class);
        $tid = $this->ctx()->id();
        $catalog = DB::table('permissions')->orderBy('key')->get(['key', 'module', 'description'])->groupBy(fn ($p) => Str::before($p->key, '.'));
        $gate = \App\Modules\Entitlement\Services\ModuleGate::class;
        $roles = Role::with(['permissions:id,key', 'denies:id,key'])->orderBy('id')->get();
        $mods = $catalog->map(fn ($ps, $mod) => [
            'module' => $mod,
            'subscribed' => ! $gate::ialahModul($mod) || $ent->bolehModul($tid, $gate::moduleKey($mod)),
            'permissions' => $ps->values(),
            'roles' => $roles->mapWithKeys(fn ($r) => [$r->id => $this->access->permissionsForRoles(collect([$r]))])->map(fn ($eff) => $ps->pluck('key')->intersect($eff)->values()),
        ])->values();

        return response()->json(['data' => ['modules' => $mods, 'roles' => $roles->map->only(['id', 'key', 'name'])]]);
    }

    // ------------------------------------------------------------ seat & jemputan

    public function seats(): JsonResponse
    {
        return response()->json(['data' => $this->access->seats($this->ctx()->id())]);
    }

    public function invitations(): JsonResponse
    {
        return response()->json(['data' => TenantUser::with(['user:id,email', 'roles:id,name'])->where('status', 'invited')->latest('id')->get()->map(fn ($m) => [
            'id' => $m->id, 'email' => $m->user->email, 'roles' => $m->roles->pluck('name'), 'expires_at' => $m->invite_expires_at,
            'expired' => $m->invite_expires_at?->isPast() ?? true, 'invited_by' => $m->invited_by,
        ])]);
    }

    public function resendInvitation(Request $r, int $id): JsonResponse
    {
        $m = TenantUser::where('status', 'invited')->findOrFail($id);
        $token = Str::random(64);
        $m->forceFill(['invite_token_hash' => hash('sha256', $token), 'invite_expires_at' => now()->addDays(7)])->save();
        $this->log('members.invite_renewed', $m, null, []);

        return response()->json(['data' => ['invite_url' => url('/jemputan/'.$token), 'expires_at' => $m->invite_expires_at]]);
    }

    public function cancelInvitation(int $id): JsonResponse
    {
        $m = TenantUser::where('status', 'invited')->findOrFail($id);
        $m->forceFill(['status' => 'disabled', 'revoked_at' => now(), 'invite_token_hash' => null, 'invite_expires_at' => null, 'status_reason' => 'Jemputan dibatalkan'])->save();
        $this->log('members.invite_cancelled', $m, null, []);

        return response()->json(['ok' => true]);
    }

    // ------------------------------------------------------------ sesi (milik sendiri)

    public function sessions(Request $r): JsonResponse
    {
        $cur = $r->hasSession() ? $r->session()->getId() : null;

        return response()->json(['data' => DB::table('sessions')->where('user_id', $r->user()->id)->orderByDesc('last_activity')->limit(50)->get(['id', 'ip_address', 'user_agent', 'last_activity'])
            ->map(fn ($s) => ['id' => hash('sha256', $s->id), 'ip_address' => $s->ip_address, 'user_agent' => Str::limit((string) $s->user_agent, 160),
                'last_activity' => date(DATE_ATOM, (int) $s->last_activity), 'current' => $s->id === $cur])]);
    }

    public function revokeSession(Request $r, string $id): JsonResponse
    {
        $row = DB::table('sessions')->where('user_id', $r->user()->id)->get(['id'])->first(fn ($s) => hash('sha256', $s->id) === $id);
        abort_unless($row, 404);
        abort_if($r->hasSession() && $row->id === $r->session()->getId(), 422, 'Guna Log Keluar untuk sesi ini.');
        DB::table('sessions')->where('id', $row->id)->delete();
        app(AuditLogger::class)->log('auth.session_revoked', $r->user(), null, []);

        return response()->json(['ok' => true]);
    }
}
