<?php

namespace App\Support\Tenancy;

use App\Models\MembershipScope;
use App\Services\AccessService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * OAM skop data di lapisan query. Setiap kaedah pulangkan null = tiada sekatan, atau senarai id yang dibenarkan.
 * Hanya ahli yang skopnya bukan "seluruh organisasi" disekat. Tanpa keahlian (kerja sistem) = tiada sekatan.
 * Guna DB::table (bukan model) supaya tiada rekursi dengan skop global model.
 */
class DataScope
{
    private array $cache = [];

    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    private function memo(string $k, callable $fn)
    {
        $key = ($this->ctx()->membership()?->id ?? 0).':'.$k;

        return array_key_exists($key, $this->cache) ? $this->cache[$key] : ($this->cache[$key] = $fn());
    }

    public function flush(): void
    {
        $this->cache = [];
    }

    /** @return array<int>|null */
    public function units(): ?array
    {
        $m = $this->ctx()->membership();
        if (! $m || ! $this->ctx()->has()) {
            return null;
        }

        return $this->memo('units', fn () => app(AccessService::class)->visibleUnitIds($m));
    }

    public function restricted(): bool
    {
        return $this->units() !== null;
    }

    private function ownOnly(): bool
    {
        $m = $this->ctx()->membership();

        return $m && $this->memo('own', fn () => MembershipScope::where('tenant_user_id', $m->id)->value('data_scope') === 'own');
    }

    /** @return array<int>|null */
    public function warehouses(): ?array
    {
        $u = $this->units();

        return $u === null ? null : $this->memo('wh', fn () => DB::table('warehouses')->where('tenant_id', $this->ctx()->id())->whereIn('org_unit_id', $u ?: [0])->pluck('id')->all());
    }

    /** Kaunter: ikut unit kaunter, atau ikut unit gudangnya jika kaunter belum ditautkan. */
    public function registers(): ?array
    {
        $u = $this->units();
        if ($u === null) {
            return null;
        }

        return $this->memo('reg', fn () => DB::table('pos_registers')->where('tenant_id', $this->ctx()->id())
            ->where(fn ($q) => $q->whereIn('org_unit_id', $u ?: [0])->orWhere(fn ($q) => $q->whereNull('org_unit_id')->whereIn('warehouse_id', $this->warehouses() ?: [0])))
            ->pluck('id')->all());
    }

    /** Pekerja dalam unit yang kelihatan, sentiasa termasuk rekod pekerja sendiri. Skop "own" = diri sendiri sahaja. */
    public function employees(): ?array
    {
        $u = $this->units();
        if ($u === null) {
            return null;
        }

        return $this->memo('emp', fn () => DB::table('hrms_employees')->where('tenant_id', $this->ctx()->id())
            ->where(fn ($q) => $q->where('user_id', auth()->id())->when(! $this->ownOnly(), fn ($q) => $q->orWhereIn('org_unit_id', $u ?: [0])))
            ->pluck('id')->all());
    }

    public function idsFor(string $kind): ?array
    {
        return match ($kind) {
            'units' => $this->units(),
            'warehouses' => $this->warehouses(),
            'registers' => $this->registers(),
            'employees' => $this->employees(),
        };
    }

    /** Tapis query ikut lajur. */
    public function apply($query, string $column, string $kind)
    {
        $ids = $this->idsFor($kind);

        return $ids === null ? $query : $query->whereIn($column, $ids ?: [0]);
    }

    public function allows(string $kind, ?int $id): bool
    {
        $ids = $this->idsFor($kind);

        return $ids === null || ($id !== null && in_array($id, $ids, true));
    }

    public function authorize(string $kind, ?int $id, string $msg = 'Rekod ini di luar skop cawangan/jabatan anda.'): void
    {
        abort_unless($this->allows($kind, $id), 403, $msg);
    }
}
