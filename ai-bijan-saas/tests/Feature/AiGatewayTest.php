<?php

namespace Tests\Feature;

use App\Modules\AIGateway\Models\AiCreditWallet;
use App\Modules\AIGateway\Models\AiModel;
use App\Modules\AIGateway\Services\GatewayService;
use App\Modules\AIGateway\Services\PiiRedactor;
use App\Modules\AIGateway\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian AI Gateway (BAB 21 + 22).
 */
class AiGatewayTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected int $tenantId;
    protected int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();

        [$user, $tenant] = $this->daftar('gw@ujian.test', 'Syarikat Gateway');
        $this->userId = $user->id;
        $this->tenantId = $tenant->id;
        $this->dalamTenant($tenant);

        config(['ai_gateway.features.live_calls_enabled' => false]);
    }

    private function model(string $tier = 'standard', string $id = 'asai/ujian-model'): AiModel
    {
        // Seeder katalog mungkin sudah mencipta ID ini — guna firstOrCreate.
        return AiModel::firstOrCreate(
            ['provider_slug' => 'serveras', 'provider_model_id' => $id],
            [
                'display_name' => 'Model Ujian',
                'tier' => $tier,
                'capabilities' => ['text'],
                'max_context' => 128000,
                'is_active' => true,
                'availability_status' => 'available',
            ]
        );
    }

    // ===== Kiraan Kredit =====

    public function test_kiraan_kredit_tier_flash(): void
    {
        $m = $this->model('flash');

        // Flash multiplier 1.0, base 0.0001
        // 1000 input + 200 output = (1000×0.0001) + (200×0.0001) = 0.12 → bulat 100
        $credits = $m->calculateCredits(1000, 200);

        $this->assertSame(100, $credits);
    }

    public function test_kiraan_kredit_tier_premium_lebih_tinggi(): void
    {
        $flash = $this->model('flash', 'asai/model-flash');
        $premium = $this->model('premium', 'asai/model-premium');

        $cFlash = $flash->calculateCredits(10000, 5000);
        $cPremium = $premium->calculateCredits(10000, 5000);

        $this->assertGreaterThan($cFlash, $cPremium);
    }

    public function test_kredit_bulat_ke_100(): void
    {
        $m = $this->model('flash');

        foreach ([1, 50, 500, 1000] as $tokens) {
            $this->assertSame(0, $m->calculateCredits($tokens, 0) % 100);
        }
    }

    public function test_multiplier_tier_sepadan_config(): void
    {
        $this->assertSame(1.0, $this->model('flash', 'a')->tierMultiplier());
        $this->assertSame(1.5, $this->model('standard', 'b')->tierMultiplier());
        $this->assertSame(2.0, $this->model('premium', 'c')->tierMultiplier());
        $this->assertSame(2.5, $this->model('ultra', 'd')->tierMultiplier());
    }

    public function test_paparan_pelanggan_tiada_id_dalaman(): void
    {
        $m = $this->model('standard', 'asai/rahsia-dalaman');
        $arr = $m->toCustomerArray();

        $this->assertArrayNotHasKey('provider_model_id', $arr);
        $this->assertArrayNotHasKey('provider_slug', $arr);
        $this->assertArrayHasKey('display_name', $arr);
        $this->assertSame('Model Ujian', $arr['display_name']);
    }

    // ===== PII Redaction =====

    public function test_redaksi_emel(): void
    {
        $r = app(PiiRedactor::class);
        $out = $r->redactText('Hubungi saya di ali@contoh.com ya');

        $this->assertStringNotContainsString('ali@contoh.com', $out);
        $this->assertStringContainsString('[REDACTED]', $out);
    }

    public function test_redaksi_telefon_malaysia(): void
    {
        $r = app(PiiRedactor::class);
        $out = $r->redactText('Nombor saya +60123456789');

        $this->assertStringNotContainsString('+60123456789', $out);
    }

    public function test_redaksi_no_ic(): void
    {
        $r = app(PiiRedactor::class);
        $out = $r->redactText('IC saya 900101-01-1234');

        $this->assertStringNotContainsString('900101-01-1234', $out);
    }

    public function test_redaksi_kunci_sensitif_dalam_array(): void
    {
        $r = app(PiiRedactor::class);

        $out = $r->redact([
            'nama' => 'Ali',
            'password' => 'rahsia123',
            'api_key' => 'asai_sk_xxx',
            'nested' => ['secret' => 'abc'],
        ]);

        $this->assertSame('Ali', $out['nama']);
        $this->assertSame('[REDACTED]', $out['password']);
        $this->assertSame('[REDACTED]', $out['api_key']);
        $this->assertSame('[REDACTED]', $out['nested']['secret']);
    }

    public function test_redaksi_mesej_chat(): void
    {
        $r = app(PiiRedactor::class);

        $out = $r->redactMessages([
            ['role' => 'user', 'content' => 'E-mel saya ali@contoh.com'],
            ['role' => 'assistant', 'content' => 'Baik'],
        ]);

        $this->assertStringNotContainsString('ali@contoh.com', $out[0]['content']);
        $this->assertSame('Baik', $out[1]['content']);
    }

    // ===== Wallet =====

    public function test_wallet_baru_bermula_kosong(): void
    {
        $w = AiCreditWallet::forTenant($this->tenantId);

        $this->assertEquals(0, $w->balance);
        $this->assertEquals(0, $w->period_quota);
    }

    public function test_top_up_menambah_baki(): void
    {
        $svc = app(WalletService::class);
        $w = $svc->topUp($this->tenantId, 500);

        $this->assertEquals(500, $w->balance);
    }

    public function test_top_up_jumlah_negatif_ditolak(): void
    {
        $svc = app(WalletService::class);

        $this->expectException(\InvalidArgumentException::class);
        $svc->topUp($this->tenantId, -100);
    }

    public function test_deduct_guna_kuota_dahulu(): void
    {
        $svc = app(WalletService::class);
        $svc->resetMonthlyQuota($this->tenantId, 1000);
        $svc->topUp($this->tenantId, 500);

        $w = AiCreditWallet::forTenant($this->tenantId);
        $w->deduct(300);

        // Kuota: 300 digunakan, baki topup kekal 500
        $this->assertEquals(300, $w->fresh()->used_this_period);
        $this->assertEquals(500, $w->fresh()->balance);
    }

    public function test_deduct_guna_balance_selepas_kuota_habis(): void
    {
        $svc = app(WalletService::class);
        $svc->resetMonthlyQuota($this->tenantId, 100);
        $svc->topUp($this->tenantId, 500);

        $w = AiCreditWallet::forTenant($this->tenantId);
        $w->deduct(300); // 100 dari kuota, 200 dari balance

        $this->assertEquals(100, $w->fresh()->used_this_period);
        $this->assertEquals(300, $w->fresh()->balance);
    }

    public function test_deduct_melebihi_baki_ditolak(): void
    {
        $w = AiCreditWallet::forTenant($this->tenantId);

        $this->expectException(\RuntimeException::class);
        $w->deduct(100);
    }

    public function test_peratus_penggunaan(): void
    {
        $svc = app(WalletService::class);
        $svc->resetMonthlyQuota($this->tenantId, 1000);

        $w = AiCreditWallet::forTenant($this->tenantId);
        $w->deduct(250);

        $this->assertEquals(25.0, $w->fresh()->usagePercent());
    }

    public function test_alert_pada_threshold(): void
    {
        $svc = app(WalletService::class);
        $svc->resetMonthlyQuota($this->tenantId, 1000);

        $w = AiCreditWallet::forTenant($this->tenantId);
        $w->deduct(850);

        $alerts = $w->fresh()->applicableAlerts();
        $this->assertNotEmpty($alerts);
    }

    public function test_reset_period_mengosongkan_penggunaan(): void
    {
        $svc = app(WalletService::class);
        $svc->resetMonthlyQuota($this->tenantId, 1000);

        $w = AiCreditWallet::forTenant($this->tenantId);
        $w->deduct(500);
        $this->assertEquals(500, $w->fresh()->used_this_period);

        $w->fresh()->resetPeriod(1000);
        $this->assertEquals(0, $w->fresh()->used_this_period);
        $this->assertEquals(1000, $w->fresh()->period_quota);
    }

    // ===== Gateway (mod simulasi) =====

    public function test_mod_simulasi_tiada_panggilan_sebenar(): void
    {
        $this->model('standard', config('ai_gateway.model_routing.reasoning'));

        $res = app(GatewayService::class)->call(
            $this->tenantId,
            [['role' => 'user', 'content' => 'Ujian']],
            'reasoning',
            ['user_id' => $this->userId]
        );

        $this->assertTrue($res['success']);
        $this->assertTrue($res['simulated'] ?? false);
        $this->assertStringContainsString('SIMULASI', $res['content']);
    }

    public function test_baki_kosong_menghalang_panggilan_live(): void
    {
        config(['ai_gateway.features.live_calls_enabled' => true]);
        $this->model('standard', config('ai_gateway.model_routing.reasoning'));

        // Wallet kosong
        $res = app(GatewayService::class)->call(
            $this->tenantId,
            [['role' => 'user', 'content' => str_repeat('x', 5000)]],
            'reasoning',
            ['user_id' => $this->userId]
        );

        $this->assertFalse($res['success']);
        $this->assertStringContainsString('kredit', strtolower($res['error']));
    }

    public function test_route_model_ikut_task_type(): void
    {
        $expected = config('ai_gateway.model_routing.coding');
        $this->model('standard', $expected);

        $m = app(GatewayService::class)->routeModel('coding');

        $this->assertNotNull($m);
        $this->assertSame($expected, $m->provider_model_id);
    }
}
