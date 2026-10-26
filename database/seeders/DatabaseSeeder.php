<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Services\TenantProvisioner;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        TenantProvisioner::syncPermissions();
        
        // Seed Malaysian statutory rates (corrected per audit A-02)
        $this->call(StatutoryRatesSeeder::class);
        
        // Seed katalog model AI (BAB 21.2)
        $this->call(AiModelCatalogSeeder::class);
        
        // Plan percubaan — semua modul tersedia semasa tempoh percubaan.
        // (Harga pelan berbayar ditetapkan oleh pemilik platform / Super Admin.)
        Plan::updateOrCreate(['code' => 'percubaan'], [
            'name' => 'Percubaan 14 hari',
            'max_users' => 5,
            'max_locations' => 1,
            'storage_mb' => 1024,
            'ai_credits_monthly' => 0,
            'price_monthly' => 0,
            'is_active' => true,
            'modules' => ['*'],
        ]);

        // Plan lalai (starter / growth / enterprise) — BAB 90
        app(\App\Modules\Entitlement\Services\EntitlementService::class)->janaPlanLalai();
    }
}
