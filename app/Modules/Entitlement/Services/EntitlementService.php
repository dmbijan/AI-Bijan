<?php

namespace App\Modules\Entitlement\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\TenantModule;
use App\Modules\Entitlement\Models\EntitlementGrant;
use App\Modules\Entitlement\Models\PlanLimit;
use App\Modules\Entitlement\Models\UsageCounter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * EntitlementService — kawalan akses modul & had penggunaan (BAB 90).
 *
 * ⚠️ PENEMUAN AUDIT: jadual `plans`, `subscriptions`, `tenant_modules`
 * sudah wujud dalam platform core TETAPI tidak pernah digunakan oleh
 * mana-mana middleware/service. Modul ini menghidupkannya sebagai gate
 * sebenar — TANPA mencipta jadual pendua.
 *
 * Corak penggunaan:
 *   if (! app(EntitlementService::class)->bolehModul($tenantId, 'seo')) { abort(403); }
 *
 * Had penggunaan guna pattern reserve → settle (atomic) untuk elak
 * AI credit overspend.
 */
class EntitlementService
{
    /** Cache dalam memória (per request). */
    private array $cache = [];

    // ===============================================================
    // PLAN & LANGGANAN
    // ===============================================================

    /**
     * Plan tenant semasa (via `tenants.plan_id`).
     *
     * Tiada fallback — jika tenant belum ada `plan_id`, ia tidak mempunyai
     * plan dan access modul mesti ditolak (deny-by-default).
     */
    public function plan(int $tenantId): ?Plan
    {
        $planId = DB::table('tenants')->where('id', $tenantId)->value('plan_id');

        return $planId ? Plan::find($planId) : null;
    }

    /**
     * Langganan aktif tenant (via `subscriptions`).
     */
    public function langganan(int $tenantId): ?Subscription
    {
        return Subscription::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Jana plan lalai (idempoten) — guna jadual `plans` sedia ada.
     */
    public function janaPlanLalai(): int
    {
        $dicipta = 0;

        foreach ((array) config('entitlement.default_plans', []) as $data) {
            $plan = Plan::query()->where('code', $data['code'])->first();

            $atribut = [
                'name' => $data['name'],
                'max_users' => $data['max_users'] ?? null,
                'max_locations' => $data['max_locations'] ?? null,
                'storage_mb' => $data['storage_mb'] ?? null,
                'ai_credits_monthly' => $data['ai_credits_monthly'] ?? 0,
                'modules' => $data['modules'] ?? [],
                'price_monthly' => $data['price_monthly'] ?? 0,
                'is_active' => true,
            ];

            if ($plan) {
                $plan->update($atribut);
            } else {
                $plan = Plan::create(array_merge($atribut, ['code' => $data['code']]));
                $dicipta++;
            }

            foreach ((array) ($data['limits'] ?? []) as $limit) {
                PlanLimit::query()->updateOrCreate(
                    ['plan_id' => $plan->id, 'limit_key' => $limit['limit_key']],
                    [
                        'name' => $limit['name'],
                        'limit_value' => $limit['limit_value'] ?? null,
                        'unit' => $limit['unit'] ?? null,
                        'period' => $limit['period'] ?? 'monthly',
                    ]
                );
            }
        }

        return $dicipta;
    }

    /**
     * Tukar plan tenant — resync `tenant_modules`.
     */
    public function tukarPlan(int $tenantId, int $planId, array $pilihan = []): Subscription
    {
        $plan = Plan::findOrFail($planId);

        return DB::transaction(function () use ($tenantId, $plan, $pilihan) {
            DB::table('tenants')->where('id', $tenantId)->update(['plan_id' => $plan->id]);

            $langganan = Subscription::query()->updateOrCreate(
                ['tenant_id' => $tenantId],
                array_merge([
                    'tenant_id' => $tenantId,
                    'plan_id' => $plan->id,
                    'status' => 'active',
                    'starts_at' => now(),
                    'ends_at' => now()->addMonth(),
                ], $pilihan),
                ['id']
            );

            $this->syncModul($tenantId, $plan);

            return $langganan;
        });
    }

    /**
     * Sync `tenant_modules` daripada plan.
     *
     * Plan `modules` mungkin senarai module_key, atau ['*'] untuk semua.
     */
    public function syncModul(int $tenantId, Plan $plan): int
    {
        $modul = (array) ($plan->modules ?? []);
        $semua = in_array('*', $modul, true);

        $grantAktif = EntitlementGrant::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('enabled', true)
            ->get()
            ->filter(fn ($g) => $g->aktif())
            ->pluck('module')
            ->all();

        $existing = TenantModule::withoutGlobalScope('tenant')->where('tenant_id', $tenantId)->get();

        foreach ($existing as $tm) {
            $dalamPlan = $semua || in_array($tm->module, $modul, true);
            $adaGrant = in_array($tm->module, $grantAktif, true);

            if (! $dalamPlan && ! $adaGrant) {
                $tm->update(['enabled' => false]);
            }
        }

        $aktif = 0;

        if ($semua) {
            foreach ($this->senaraiSemuaModul() as $key) {
                $this->aktifkanModul($tenantId, $key);
                $aktif++;
            }

            return $aktif;
        }

        foreach ($modul as $key) {
            $this->aktifkanModul($tenantId, $key);
            $aktif++;
        }

        return $aktif;
    }

    /**
     * Aktifkan satu modul dalam `tenant_modules`.
     */
    private function aktifkanModul(int $tenantId, string $module): void
    {
        $ada = TenantModule::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('module', $module)
            ->first();

        if ($ada) {
            $ada->update(['enabled' => true]);

            return;
        }

        $baru = new TenantModule;
        $baru->forceFill([
            'tenant_id' => $tenantId,
            'module' => $module,
            'enabled' => true,
            'source' => 'plan',
            'enabled_at' => now(),
        ])->save();
    }

    /**
     * Senarai semua module_key yang wujud dalam sistem.
     *
     * ⚠️ `Str::snake()` tidak sesuai untuk nama modul — ia menghasilkan
     * `a_i_agents` untuk `AIAgents`. Guna regex yang mengekalkan
     * aksara besar berturut-turut sebagai satu token.
     */
    public function senaraiSemuaModul(): array
    {
        $direktori = app_path('Modules');

        if (! is_dir($direktori)) {
            return [];
        }

        $senarai = [];

        foreach (scandir($direktori) as $entri) {
            if ($entri === '.' || $entri === '..' || ! is_dir($direktori . '/' . $entri)) {
                continue;
            }

            // AIAgents → ai_agents, ContentStudio → content_studio
            $kunci = strtolower((string) preg_replace(
                '/(?<=[A-Z])(?=[A-Z][a-z])|(?<=[a-z0-9])(?=[A-Z])/',
                '_',
                $entri
            ));

            $senarai[] = $kunci;
        }

        return $senarai;
    }

    // ===============================================================
    // GATE MODUL
    // ===============================================================

    /**
     * Bolehkah tenant mengakses modul ini?
     */
    public function bolehModul(int $tenantId, string $module): bool
    {
        $kunci = $tenantId.':'.$module;

        if (isset($this->cache[$kunci])) {
            return $this->cache[$kunci];
        }

        $langganan = $this->langganan($tenantId);

        if (! $langganan) {
            if (config('entitlement.allow_without_subscription', false)) {
                return $this->cache[$kunci] = true;
            }

            return $this->cache[$kunci] = false;
        }

        if (! $this->langgananAktif($langganan)) {
            return $this->cache[$kunci] = false;
        }

        // Grant manual (add-on / trial) — tertinggi
        $grant = EntitlementGrant::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('module', $module)
            ->first();

        if ($grant && $grant->aktif()) {
            return $this->cache[$kunci] = true;
        }

        // `tenant_modules` — flag sokongan
        $tenantModule = TenantModule::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('module', $module)
            ->first();

        if ($tenantModule && $tenantModule->enabled) {
            return $this->cache[$kunci] = true;
        }

        // Plan — senarai `modules`
        $plan = $this->plan($tenantId);

        if ($plan) {
            $modul = (array) ($plan->modules ?? []);

            if (in_array('*', $modul, true) || in_array($module, $modul, true)) {
                return $this->cache[$kunci] = true;
            }
        }

        return $this->cache[$kunci] = false;
    }

    /**
     * Adakah langganan masih boleh digunakan?
     */
    public function langgananAktif(Subscription $langganan): bool
    {
        if (in_array($langganan->status, ['cancelled', 'suspended'], true)) {
            return false;
        }

        if (in_array($langganan->status, ['trial', 'past_due'], true)
            && $langganan->ends_at
            && $langganan->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Senarai modul yang boleh diakses.
     */
    public function modulAktif(int $tenantId): array
    {
        $senarai = TenantModule::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('enabled', true)
            ->pluck('module')
            ->all();

        $grants = EntitlementGrant::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('enabled', true)
            ->get()
            ->filter(fn ($g) => $g->aktif())
            ->pluck('module')
            ->all();

        return array_values(array_unique(array_merge($senarai, $grants)));
    }

    /**
     * Grant modul manual.
     */
    public function grantModul(int $tenantId, string $module, string $source = 'addon', ?\DateTimeInterface $expiresAt = null): EntitlementGrant
    {
        // ⚠️ Skop tenant global menapis ikut TenantContext, bukan $tenantId.
        // Tanpa `withoutGlobalScope`, grant dicipta dengan tenant_id = 0
        // apabila konteks kosong (cth. semasa provisioning atau ujian).
        $grant = EntitlementGrant::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('module', $module)
            ->first();

        if ($grant) {
            $grant->forceFill([
                'source' => $source,
                'enabled' => true,
                'enabled_at' => now(),
                'disabled_at' => null,
                'expires_at' => $expiresAt,
            ])->save();
        } else {
            $grant = new EntitlementGrant;
            $grant->forceFill([
                'tenant_id' => $tenantId,
                'module' => $module,
                'source' => $source,
                'enabled' => true,
                'enabled_at' => now(),
                'disabled_at' => null,
                'expires_at' => $expiresAt,
            ])->save();
        }

        $this->clearCache();

        return $grant;
    }

    /**
     * Revoke modul — buang grant + flag `tenant_modules`.
     */
    public function revokeModul(int $tenantId, string $module): bool
    {
        $grant = EntitlementGrant::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('module', $module)
            ->first();

        if ($grant) {
            $grant->update(['enabled' => false, 'disabled_at' => now()]);
        }

        TenantModule::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('module', $module)
            ->update(['enabled' => false]);

        $this->clearCache();

        return $grant !== null;
    }

    /**
     * Ringkasan entitlement untuk UI / dashboard.
     */
    public function ringkasan(int $tenantId): array
    {
        $langganan = $this->langganan($tenantId);
        $plan = $this->plan($tenantId);

        return [
            'langganan' => $langganan ? [
                'status' => $langganan->status,
                'aktif' => $this->langgananAktif($langganan),
                'starts_at' => $langganan->starts_at?->toIso8601String(),
                'ends_at' => $langganan->ends_at?->toIso8601String(),
            ] : null,
            'plan' => $plan ? [
                'code' => $plan->code,
                'name' => $plan->name,
                'price_monthly' => (float) $plan->price_monthly,
                'max_users' => $plan->max_users,
                'max_locations' => $plan->max_locations,
                'storage_mb' => $plan->storage_mb,
                'ai_credits_monthly' => (int) $plan->ai_credits_monthly,
            ] : null,
            'jumlah_modul_aktif' => count($this->modulAktif($tenantId)),
            'modul_aktif' => $this->modulAktif($tenantId),
            'had' => $this->semakSemuaHad($tenantId),
        ];
    }

    // ===============================================================
    // HAD PENGGUNAAN
    // ===============================================================

    /**
     * Ambil had bagi satu limit_key.
     */
    public function had(int $tenantId, string $limitKey): ?int
    {
        $plan = $this->plan($tenantId);

        if (! $plan) {
            return null;
        }

        $limit = PlanLimit::query()
            ->where('plan_id', $plan->id)
            ->where('limit_key', $limitKey)
            ->first();

        return $limit ? $limit->limit_value : null;
    }

    /**
     * Kunci period untuk usage.
     */
    public function periodKey(string $period = 'monthly'): string
    {
        return match ($period) {
            'daily' => now()->format('Y-m-d'),
            'weekly' => now()->format('o-W').str_pad((string) now()->isoWeek(), 2, '0', STR_PAD_LEFT),
            'yearly' => now()->format('Y'),
            'total' => 'total',
            default => now()->format('Y-m'),
        };
    }

    /**
     * Kaunter usage semasa.
     */
    public function kaunter(int $tenantId, string $limitKey, string $period = 'monthly'): UsageCounter
    {
        $periodKey = $this->periodKey($period);

        return UsageCounter::query()->firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'limit_key' => $limitKey,
                'period_key' => $periodKey,
            ],
            [
                'used_value' => 0,
                'reserved_value' => 0,
                'period_start' => now()->startOfMonth(),
                'period_end' => now()->endOfMonth(),
            ]
        );
    }

    /**
     * Periksa had — bolehkah guna tambahan unit?
     */
    public function bolehGuna(int $tenantId, string $limitKey, int $jumlah = 1): bool
    {
        $had = $this->had($tenantId, $limitKey);

        if ($had === null) {
            return true;
        }

        $kaunter = $this->kaunter($tenantId, $limitKey, $this->periodForLimit($limitKey));

        return ($kaunter->jumlahDigunakan() + $jumlah) <= $had;
    }

    /**
     * Reserve usage secara atomic (untuk AI token).
     */
    public function reserveUsage(int $tenantId, string $limitKey, int $jumlah): bool
    {
        $had = $this->had($tenantId, $limitKey);

        if ($had === null) {
            return true;
        }

        $period = $this->periodForLimit($limitKey);
        $periodKey = $this->periodKey($period);

        return DB::transaction(function () use ($tenantId, $limitKey, $periodKey, $jumlah, $had) {
            $kaunter = UsageCounter::query()
                ->where('tenant_id', $tenantId)
                ->where('limit_key', $limitKey)
                ->where('period_key', $periodKey)
                ->lockForUpdate()
                ->first();

            if (! $kaunter) {
                // Kunter belum wujud — sahkan dahulu jumlah tidak melebihi had
                if ($jumlah > $had) {
                    return false;
                }

                UsageCounter::query()->create([
                    'tenant_id' => $tenantId,
                    'limit_key' => $limitKey,
                    'period_key' => $periodKey,
                    'used_value' => 0,
                    'reserved_value' => $jumlah,
                    'period_start' => now()->startOfMonth(),
                    'period_end' => now()->endOfMonth(),
                ]);

                return true;
            }

            if (($kaunter->jumlahDigunakan() + $jumlah) > $had) {
                return false;
            }

            $kaunter->update([
                'reserved_value' => (int) $kaunter->reserved_value + $jumlah,
            ]);

            return true;
        });
    }

    /**
     * Settle reservation (reserved → used).
     */
    public function settleUsage(int $tenantId, string $limitKey, int $jumlah): void
    {
        $kaunter = $this->kaunter($tenantId, $limitKey, $this->periodForLimit($limitKey));

        $kaunter->update([
            'used_value' => (int) $kaunter->used_value + $jumlah,
            'reserved_value' => max(0, (int) $kaunter->reserved_value - $jumlah),
        ]);
    }

    /**
     * Release reservation (gagal).
     */
    public function releaseUsage(int $tenantId, string $limitKey, int $jumlah): void
    {
        $kaunter = $this->kaunter($tenantId, $limitKey, $this->periodForLimit($limitKey));

        $kaunter->update([
            'reserved_value' => max(0, (int) $kaunter->reserved_value - $jumlah),
        ]);
    }

    /**
     * Tambah usage terus.
     */
    public function tambahUsage(int $tenantId, string $limitKey, int $jumlah = 1): void
    {
        $kaunter = $this->kaunter($tenantId, $limitKey, $this->periodForLimit($limitKey));

        $kaunter->update([
            'used_value' => (int) $kaunter->used_value + $jumlah,
        ]);
    }

    /**
     * Semak semua had — untuk dashboard.
     */
    public function semakSemuaHad(int $tenantId): array
    {
        $plan = $this->plan($tenantId);

        if (! $plan) {
            return [];
        }

        $limits = PlanLimit::query()->where('plan_id', $plan->id)->get();
        $hasil = [];

        foreach ($limits as $limit) {
            $kaunter = $this->kaunter($tenantId, $limit->limit_key, $this->periodForLimit($limit->limit_key));

            $had = $limit->limit_value;
            $digunakan = $kaunter->jumlahDigunakan();

            $hasil[] = [
                'limit_key' => $limit->limit_key,
                'name' => $limit->name,
                'limit' => $had,
                'used' => $digunakan,
                'remaining' => $had === null ? null : max(0, $had - $digunakan),
                'percent' => ($had && $had > 0) ? round(($digunakan / $had) * 100, 1) : null,
                'unit' => $limit->unit,
                'period' => $limit->period,
                'breach' => $had !== null && $digunakan >= $had,
            ];
        }

        return $hasil;
    }

    /**
     * Period bagi satu limit_key.
     */
    private function periodForLimit(string $limitKey): string
    {
        $mapping = (array) config('entitlement.limit_periods', []);

        return $mapping[$limitKey] ?? 'monthly';
    }

    /**
     * Clear cache.
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }
}
