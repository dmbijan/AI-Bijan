<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\User;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** Cipta tenant baru lengkap: peranan lalai, langganan percubaan, pemilik. Satu transaksi. */
class TenantProvisioner
{
    private TenantContext $ctx;

    private AuditLogger $audit;

    public function __construct()
    {
        $this->ctx = app(TenantContext::class);
        $this->audit = app(AuditLogger::class);
    }

    public static function syncPermissions(): void
    {
        foreach (config('aibijan.permissions') as $key => $desc) {
            Permission::updateOrCreate(['key' => $key], ['module' => Str::before($key, '.'), 'description' => $desc]);
        }
    }

    public function create(string $name, User $owner, ?string $planCode = null): Tenant
    {
        return DB::transaction(function () use ($name, $owner, $planCode) {
            self::syncPermissions();

            // AUDIT BAB 90: pastikan plan lalai wujud + tenant dapat langganan
            // (tanpa ini, gate entitlemen menolak semua laluan `perm:`).
            app(\App\Modules\Entitlement\Services\EntitlementService::class)->janaPlanLalai();

            $plan = Plan::where('code', $planCode ?? 'percubaan')->first()
                ?? Plan::where('code', 'starter')->first()
                ?? Plan::where('is_active', true)->orderBy('price_monthly')->first();

            $tenant = Tenant::create([
                'name' => $name, 'slug' => $this->slug($name), 'status' => 'trial', 'plan_id' => $plan?->id,
                'trial_ends_at' => now()->addDays(config('aibijan.trial_days')),
            ]);
            $sebelum = [$this->ctx->has() ? $this->ctx->tenant() : null, $this->ctx->membership()];
            $this->ctx->set($tenant);
            try {
                $semua = Permission::pluck('id', 'key');
                $roles = [];
                foreach (config('aibijan.roles') as $key => $def) {
                    $role = Role::create(['key' => $key, 'name' => $def['name'], 'is_system' => true]);
                    $role->permissions()->sync($def['permissions'] === ['*'] ? $semua->values() : $semua->only($def['permissions'])->values());
                    $roles[$key] = $role;
                }
                $m = TenantUser::create(['user_id' => $owner->id, 'status' => 'active', 'joined_at' => now()]);
                $m->roles()->attach($roles['tenant_owner']->id);
                if ($plan) {
                    Subscription::create([
                        'tenant_id' => $tenant->id,
                        'plan_id' => $plan->id,
                        'status' => 'trial',
                        'starts_at' => now(),
                        'ends_at' => $tenant->trial_ends_at,
                    ]);

                    // Sync entitlemen modul daripada plan
                    app(\App\Modules\Entitlement\Services\EntitlementService::class)
                        ->syncModul($tenant->id, $plan);
                }
                $this->audit->log('tenant.created', $tenant, null, ['name' => $name, 'owner' => $owner->email]);
            } finally {
                $sebelum[0] ? $this->ctx->set($sebelum[0], $sebelum[1]) : $this->ctx->clear();
            }

            return $tenant;
        });
    }

    private function slug(string $name): string
    {
        $base = Str::limit(Str::slug($name) ?: 'syarikat', 40, '');
        $slug = $base;
        while (Tenant::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }
}
