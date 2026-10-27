<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Loyalty\Models\Tier;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Loyalty (tier, mata, penebusan).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Mata dan baki boleh ditukar dengan wang —
 * kebocoran bermakna tenant boleh menebus mata pelanggan tenant lain.
 *
 * ⚠️ `loyalty_members` memerlukan `crm_contact_id` NOT NULL (FK ke
 * `customer_contacts`) — jadi ujian ini fokus pada `Tier` sebagai model
 * bertrait, ditambah semakan bahawa model tanpa `$table` yang betul gagal
 * (pepijat `Reward` 26 Okt).
 */
class LoyaltyTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Loyalty';

    protected array $model = [
        Tier::class,
    ];

    protected ?string $laluanApi = '/api/v1/loyalty/tiers';

    protected ?string $namaRahsia = 'Tier Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'name' => 'Tier Rahsia Tenant Satu ' . $n,
            'slug' => 'tier-rahsia-' . $n,
            'min_points' => 0,
            'max_points' => 1000,
            'point_multiplier' => 1,
            'is_active' => true,
        ];
    }

    /**
     * Regresi 26 Okt 2026 — `Reward` tiada `$table` dan tiada trait →
     * Eloquent meneka `rewards` → "no such table".
     */
    public function test_semua_model_loyalty_menunjuk_jadual_sebenar(): void
    {
        $arah = app_path('Modules/Loyalty/Models');
        $semua = glob($arah . '/*.php') ?: [];

        $this->assertNotEmpty($semua, 'Folder model Loyalty tidak dijumpai.');

        foreach ($semua as $fail) {
            $kelas = 'App\\Modules\\Loyalty\\Models\\' . basename($fail, '.php');

            if (! class_exists($kelas)) {
                continue;
            }

            /** @var \Illuminate\Database\Eloquent\Model $instans */
            $instans = new $kelas;
            $jadual = $instans->getTable();

            $this->assertTrue(
                \Illuminate\Support\Facades\Schema::hasTable($jadual),
                $kelas . ' menunjuk jadual "' . $jadual . '" yang TIADA dalam pangkalan data.'
            );
        }
    }
}
