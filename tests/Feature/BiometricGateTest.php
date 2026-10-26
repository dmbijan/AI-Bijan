<?php

namespace Tests\Feature;

use App\Modules\HRMS\Models\BiometricAccessLog;
use App\Modules\HRMS\Models\BiometricConsent;
use App\Modules\HRMS\Models\BiometricEmbedding;
use App\Modules\HRMS\Models\BiometricSetting;
use App\Modules\HRMS\Services\BiometricGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian pagar consent biometrik (BAB 56C.6A).
 *
 * Mengesahkan face recognition TIDAK boleh aktif tanpa pagar lengkap.
 */
class BiometricGateTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('owner@kedai-a.my', 'Kedai A');
    }

    /** Data pagar lengkap (untuk ujian positif). */
    private function pagarLengkap(): array
    {
        return [
            'alternative_method' => 'rfid',
            'dpo_name' => 'Encik Data',
            'dpo_email' => 'dpo@kedai-a.my',
            'retention_days' => 30,
            'consent_version' => 'v1',
            'legal_review_date' => now()->toDateString(),
            'privacy_notice' => 'Notis PDPA versi 1',
        ];
    }

    public function test_aktifkan_wajah_tanpa_alternatif_ditolak(): void
    {
        $d = $this->pagarLengkap();
        unset($d['alternative_method']);

        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/hrms/biometric/settings', $d + ['enabled' => true])
            ->assertStatus(422);
    }

    public function test_aktifkan_wajah_tanpa_pegawai_pdpa_ditolak(): void
    {
        $d = $this->pagarLengkap();
        unset($d['dpo_name'], $d['dpo_email']);

        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/hrms/biometric/settings', $d + ['enabled' => true])
            ->assertStatus(422);
    }

    public function test_pekerja_tanpa_consent_tiada_embedding_dicipta(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/hrms/biometric/settings', $this->pagarLengkap() + ['enabled' => true])
            ->assertOk();

        $this->dalamTenant($this->t);
        $gate = app(BiometricGateService::class);
        $hasil = $gate->bolehPadan($this->u->id);

        $this->assertFalse($hasil['ok']);
        $this->assertStringContainsString('consent', $hasil['sebab']);
        $this->assertSame(0, BiometricEmbedding::count());
    }

    public function test_tarik_balik_consent_padam_embedding(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/hrms/biometric/settings', $this->pagarLengkap() + ['enabled' => true])
            ->assertOk();

        $this->postJson('/api/v1/hrms/biometric/consent', ['user_id' => $this->u->id])->assertCreated();

        $this->dalamTenant($this->t);
        $consent = BiometricConsent::aktifUntuk($this->u->id);
        $this->assertNotNull($consent);

        // Simulasi embedding disimpan selepas consent.
        BiometricEmbedding::create([
            'user_id' => $this->u->id,
            'consent_id' => $consent->id,
            'embedding' => str_repeat('0', 32),
            'quality_score' => 0.95,
        ]);
        $this->assertSame(1, BiometricEmbedding::count());

        $this->deleteJson('/api/v1/hrms/biometric/consent', ['user_id' => $this->u->id])->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame(0, BiometricEmbedding::count());
        $this->assertNull(BiometricConsent::aktifUntuk($this->u->id));
    }

    public function test_pekerja_berhenti_embedding_padam_rekod_kehadiran_kekal(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/hrms/biometric/settings', $this->pagarLengkap() + ['enabled' => true])
            ->assertOk();
        $this->postJson('/api/v1/hrms/biometric/consent', ['user_id' => $this->u->id])->assertCreated();

        $this->dalamTenant($this->t);
        $consent = BiometricConsent::aktifUntuk($this->u->id);
        BiometricEmbedding::create([
            'user_id' => $this->u->id,
            'consent_id' => $consent->id,
            'embedding' => str_repeat('1', 32),
            'quality_score' => 0.9,
        ]);

        // Rekod kehadiran (bukan biometrik) mesti kekal selepas consent ditarik.
        $gate = app(BiometricGateService::class);
        $gate->logPadanan($this->u->id, 'CAM-01', true, 0.93, 'ok');

        $this->deleteJson('/api/v1/hrms/biometric/consent', ['user_id' => $this->u->id])->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame(0, BiometricEmbedding::count());
        $this->assertSame(1, BiometricAccessLog::where('user_id', $this->u->id)->where('matched', true)->count());
    }

    public function test_tujuan_dilarang_tidak_dibenarkan(): void
    {
        $this->assertContains('attendance', BiometricGateService::tujuanDibenarkan());
        $this->assertNotContains('productivity', BiometricGateService::tujuanDibenarkan());
        $this->assertContains('productivity', BiometricGateService::tujuanDilarang());
        $this->assertContains('performance', BiometricGateService::tujuanDilarang());
    }

    public function test_semakan_undang_undang_lama_lumpuhkan_ciri(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/hrms/biometric/settings', $this->pagarLengkap() + ['enabled' => true])
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertTrue(BiometricSetting::aktifUntukTenant());

        // Semakan undang-undang melebihi 12 bulan → ciri auto-lumpuh.
        BiometricSetting::query()->first()->forceFill([
            'legal_review_date' => now()->subMonths(13)->toDateString(),
        ])->save();

        $this->assertFalse(BiometricSetting::aktifUntukTenant());
        $this->assertContains('legal_review_expired', BiometricSetting::query()->first()->pagarBelumLengkap());
    }

    public function test_laporan_pagar_menunjukkan_yang_belum_lengkap(): void
    {
        $r = $this->sebagai($this->u, $this->t)->getJson('/api/v1/hrms/biometric/settings')->assertOk()->json();

        $this->assertFalse($r['can_enable']);
        $this->assertNotEmpty($r['pending_gates']);
        $this->assertContains('alternative_method', $r['pending_gates']);
    }
}
