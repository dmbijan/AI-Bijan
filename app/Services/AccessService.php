<?php

namespace App\Services;

use App\Models\MembershipScope;
use App\Models\OrgUnit;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Setting;
use App\Models\TenantUser;
use App\Modules\Entitlement\Services\EntitlementService;
use App\Modules\Entitlement\Services\ModuleGate;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Collection;

/**
 * OAM: satu tempat untuk keputusan akses.
 * - Kebenaran berkesan = kebenaran peranan + peranan induk (maks 3 tingkat, kitaran diabaikan) − kebenaran DITOLAK.
 * - Had transaksi/kelulusan = nilai tertinggi antara peranan yang dipegang (termasuk warisan).
 * - Skop data = unit organisasi yang boleh dilihat ahli (null = semua dalam syarikat).
 * - Seat = keahlian aktif yang belum tamat; jemputan belum diterima tidak dikira.
 * Lalai = TOLAK.
 */
class AccessService
{
    public const MAX_INHERIT_DEPTH = 3;

    public const MAX_ORG_DEPTH = 6;

    public const LIMIT_KEYS = [
        'approval.amount' => 'Had lulus perbelanjaan / dokumen (RM)',
        'purchase.amount' => 'Had pesanan belian tanpa kelulusan (RM)',
        'pos.discount_amount' => 'Had diskaun POS setiap jualan (RM)',
        'pos.refund_amount' => 'Had pulangan wang POS (RM)',
    ];

    public const DATA_SCOPES = [
        'own' => 'Rekod sendiri sahaja',
        'own_unit' => 'Unit sendiri sahaja',
        'unit_tree' => 'Unit sendiri & di bawahnya',
        'selected' => 'Unit terpilih',
        'all' => 'Seluruh organisasi',
    ];

    /** @return Collection<int, Role> peranan dipegang + semua induknya */
    public function roleChain(Collection $roles): Collection
    {
        $all = Role::with(['permissions:id,key', 'denies:id,key', 'limits'])->get()->keyBy('id');
        $out = collect();
        foreach ($roles as $r) {
            $cur = $all[$r->id] ?? null;
            $seen = [];
            for ($d = 0; $cur && $d <= self::MAX_INHERIT_DEPTH && ! isset($seen[$cur->id]); $d++) {
                $seen[$cur->id] = true;
                $out[$cur->id] = $cur;
                $cur = $cur->parent_id ? ($all[$cur->parent_id] ?? null) : null;
            }
        }

        return $out->values();
    }

    public function permissionsForRoles(Collection $roles): array
    {
        $chain = $this->roleChain($roles);
        $grant = $chain->flatMap(fn ($r) => $r->permissions->pluck('key'))->unique();
        $deny = $chain->flatMap(fn ($r) => $r->denies->pluck('key'))->unique();

        return $grant->diff($deny)->values()->all();
    }

    public function permissionsFor(TenantUser $m): array
    {
        if (! $this->membershipUsable($m)) {
            return [];
        }

        return $this->permissionsForRoles($m->roles()->get());
    }

    public function membershipUsable(TenantUser $m): bool
    {
        return $m->status === 'active' && ! ($m->expires_at && $m->expires_at->isPast());
    }

    /** @return array<string, float> */
    public function limitsFor(TenantUser $m): array
    {
        $out = [];
        foreach ($this->roleChain($m->roles()->get()) as $r) {
            foreach ($r->limits as $l) {
                $out[$l->limit_key] = max($out[$l->limit_key] ?? 0, (float) $l->value);
            }
        }

        return $out;
    }

    // ------------------------------------------------------------ skop data

    /** @return array<int>|null id unit yang boleh dilihat; null = tiada sekatan unit */
    public function visibleUnitIds(TenantUser $m): ?array
    {
        $s = MembershipScope::where('tenant_user_id', $m->id)->first();
        if (! $s || $s->data_scope === 'all') {
            return null;
        }
        if ($s->data_scope === 'selected') {
            return array_values(array_map('intval', $s->selected_unit_ids ?? []));
        }
        if (! $s->org_unit_id) {
            return [];
        }
        if (in_array($s->data_scope, ['own_unit', 'own'], true)) {
            return [$s->org_unit_id];
        }

        return $this->descendants($s->org_unit_id, true);
    }

    public function descendants(int $unitId, bool $includeSelf = false): array
    {
        $byParent = OrgUnit::get(['id', 'parent_id'])->groupBy('parent_id');
        $out = $includeSelf ? [$unitId] : [];
        $stack = [$unitId];
        $guard = 0;
        while ($stack && $guard++ < 10000) {
            $id = array_pop($stack);
            foreach ($byParent[$id] ?? [] as $c) {
                $out[] = $c->id;
                $stack[] = $c->id;
            }
        }

        return $out;
    }

    // ------------------------------------------------------------ semakan akses

    /**
     * Semak satu tindakan, ikut urutan blueprint OAM §6.3. Pulangkan sebab penolakan yang pertama.
     *
     * @param  array{amount?:float, limit_key?:string, org_unit_id?:int}  $ctx
     */
    public function check(TenantUser $m, string $permission, array $ctx = []): array
    {
        $tenantId = app(TenantContext::class)->id();
        $deny = fn (string $step, string $reason) => ['allowed' => false, 'step' => $step, 'reason' => $reason];

        if ($m->status !== 'active') {
            return $deny('membership', $m->state === 'revoked' ? 'Akses telah ditarik balik.' : ($m->status === 'invited' ? 'Jemputan belum diterima.' : 'Keahlian digantung.'));
        }
        if ($m->expires_at && $m->expires_at->isPast()) {
            return $deny('membership', 'Tempoh akses telah tamat.');
        }
        $gate = ModuleGate::semak([$permission], app(EntitlementService::class), $tenantId);
        if (! $gate['allowed']) {
            return $deny('entitlement', 'Modul '.$gate['module'].' tidak termasuk dalam langganan syarikat.');
        }
        $roles = $m->roles()->get();
        $chain = $this->roleChain($roles);
        if ($chain->contains(fn ($r) => $r->denies->contains('key', $permission))) {
            return $deny('permission', 'Kebenaran ini ditolak secara jelas oleh peranan.');
        }
        if (! in_array($permission, $this->permissionsForRoles($roles), true)) {
            return $deny('permission', 'Tiada kebenaran '.$permission.'.');
        }
        if (! empty($ctx['org_unit_id'])) {
            $vis = $this->visibleUnitIds($m);
            if ($vis !== null && ! in_array((int) $ctx['org_unit_id'], $vis, true)) {
                return $deny('data_scope', 'Unit ini di luar skop data anda.');
            }
        }
        if (isset($ctx['amount'])) {
            $key = $ctx['limit_key'] ?? 'approval.amount';
            $lim = $this->limitsFor($m)[$key] ?? null;
            if ($lim !== null && (float) $ctx['amount'] > $lim) {
                return $deny('limit', 'Melebihi had anda RM'.number_format($lim, 2).'.');
            }
        }

        return ['allowed' => true, 'step' => null, 'reason' => null];
    }

    /** Elak naik pangkat sendiri: pemberi mesti sudah ada setiap kebenaran yang diberi (kecuali pemegang roles.manage). */
    public function canGrant(array $actorPerms, Collection $roles): array
    {
        if (in_array('roles.manage', $actorPerms, true)) {
            return [];
        }

        return array_values(array_diff($this->permissionsForRoles($roles), $actorPerms));
    }

    // ------------------------------------------------------------ seat

    public function seats(int $tenantId): array
    {
        $ent = app(EntitlementService::class);
        $included = $ent->had($tenantId, 'seats');
        if ($included === null) {
            $plan = $ent->plan($tenantId);
            $included = $plan?->max_users;
        }
        $addon = (int) (Setting::where('key', 'oam.addon_seats')->value('value') ?? 0);
        $used = TenantUser::where('status', 'active')->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))->count();
        $pending = TenantUser::where('status', 'invited')->count();
        $unlimited = $included === null;

        return [
            'unlimited' => $unlimited,
            'included' => $included,
            'addon' => $addon,
            'used' => $used,
            'pending_invites' => $pending,
            'available' => $unlimited ? null : max(0, $included + $addon - $used),
            'plan' => $ent->plan($tenantId)?->name,
        ];
    }

    public function seatAvailable(int $tenantId): bool
    {
        $s = $this->seats($tenantId);

        return $s['unlimited'] || $s['available'] > 0;
    }
}
