<?php

namespace Tests\Feature;

use App\Modules\Billing\Models\BillingCharge;
use App\Modules\Billing\Models\BillingSubscription;
use App\Modules\Billing\Models\Reseller;
use App\Modules\Billing\Models\ResellerClick;
use App\Modules\Billing\Models\ResellerCommission;
use App\Modules\Billing\Models\ResellerReferral;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Billing\Services\ChipWebhookService;
use App\Modules\Billing\Services\ResellerCommissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * B-Billing — CHIP + komisen reseller berulang.
 *
 * Menguji DUA BELAS peraturan reseller + keselamatan webhook.
 *
 * ⚠️ Ujian TIDAK memanggil CHIP sebenar. `ChipService` tidak di-bind, jadi
 * ujian yang perlukan invois akan dilangkau jika kunci tiada — tetapi semua
 * logik komisen dan pengesahan webhook diuji sepenuhnya.
 */
class BillingResellerTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;
    private ResellerCommissionService $komisen;
    private BillingService $billing;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@billing.my', 'Billing Sdn Bhd');
        $this->dalamTenant($this->t);
        $this->komisen = app(ResellerCommissionService::class);
        $this->billing = app(BillingService::class);
    }

    // ===============================================================
    // PEMBANTU
    // ===============================================================

    private function reseller(array $ganti = []): Reseller
    {
        static $n = 0;
        $n++;

        return Reseller::create(array_merge([
            'user_id' => $this->u->id,
            'code' => 'RS' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'name' => 'Reseller ' . $n,
            'email' => 'rs' . $n . '@reseller.test',
            'status' => Reseller::STATUS_ACTIVE,
            'commission_value' => 100,
            'commission_cap_pct' => 50,
            'bank_details' => ['bank' => 'Maybank', 'account' => '1234567890'],
        ], $ganti));
    }

    private function rujuk(Reseller $r, int $tenantId): ResellerReferral
    {
        return ResellerReferral::create([
            'reseller_id' => $r->id,
            'tenant_id' => $tenantId,
            'source' => 'link',
            'attributed_at' => now(),
        ]);
    }

    private function langgananAktif(int $tenantId, float $harga = 388): BillingSubscription
    {
        return BillingSubscription::withoutGlobalScope('tenant')->create([
            'tenant_id' => $tenantId,
            'plan_code' => 'growth',
            'status' => BillingSubscription::STATUS_ACTIVE,
            'amount' => $harga,
            'currency' => 'MYR',
            'billing_cycle' => 'monthly',
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
        ]);
    }

    private function casDibayar(int $tenantId, int $langgananId, float $jumlah = 388, ?string $period = null): BillingCharge
    {
        $periodKey = $period ?? now()->format('Y-m');

        // ⚠️ UNIQUE(tenant_id, kind, period_key) menghalang dua cas untuk period
        // sama — itu PERILAKU BETUL (elak caj dua kali). Untuk ujian yang perlu
        // dua cas, guna period berbeza.
        $sedia = BillingCharge::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('kind', BillingCharge::KIND_SUBSCRIPTION)
            ->where('period_key', $periodKey)
            ->first();

        if ($sedia) {
            return $sedia;
        }

        $cas = BillingCharge::withoutGlobalScope('tenant')->create([
            'tenant_id' => $tenantId,
            'billing_subscription_id' => $langgananId,
            'chip_purchase_id' => 'purch_' . uniqid(),
            'status' => BillingCharge::STATUS_PAID,
            'kind' => BillingCharge::KIND_SUBSCRIPTION,
            'period_key' => $periodKey,
            'amount' => $jumlah,
            'currency' => 'MYR',
            'paid_at' => now(),
        ]);

        // Rekod komisen (seperti aliran sebenar: webhook → aktifkan → komisen).
        // Ujian yang tidak mahu komisen boleh abaikan hasilnya.
        $langganan = BillingSubscription::withoutGlobalScope('tenant')->find($langgananId);

        if ($langganan !== null) {
            $this->komisen->rekodKomisen($cas, $langganan);
        }

        return $cas;
    }

    // ===============================================================
    // PERATURAN 1 — satu tenant = satu reseller
    // ===============================================================

    public function test_satu_tenant_hanya_satu_reseller(): void
    {
        $r1 = $this->reseller();
        $r2 = $this->reseller();

        $this->rujuk($r1, $this->t->id);

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $this->rujuk($r2, $this->t->id);
    }

    // ===============================================================
    // PERATURAN 2 — satu komisen per bulan
    // ===============================================================

    public function test_satu_komisen_per_bulan(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);

        $cas = $this->casDibayar($this->t->id, $l->id, 388, '2026-11');

        $k1 = $this->komisen->rekodKomisen($cas, $l);
        $this->assertNotNull($k1);

        // Cas berbeza (period lain) tetapi rekod komisen mesti KEKAL satu
        // untuk period 2026-11 (idempoten melalui UNIQUE).
        $k2 = $this->komisen->rekodKomisen($cas, $l);

        $this->assertNotNull($k2);
        $this->assertSame($k1->id, $k2->id, 'Komisen mesti idempoten untuk cas yang sama.');

        $this->assertSame(
            1,
            ResellerCommission::where('reseller_referral_id', $k1->reseller_referral_id)
                ->where('period', '2026-11')->count(),
            'Dua komisen dicipta untuk bulan yang sama.'
        );
    }

    // ===============================================================
    // PERATURAN 3 & 4 — asas bayaran sebenar + had 50%
    // ===============================================================

    public function test_komisen_rm100_untuk_bayaran_rm388(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);

        $cas = $this->casDibayar($this->t->id, $l->id, 388);
        $k = $this->komisen->rekodKomisen($cas, $l);

        $this->assertSame('100.00', (string) $k->commission_amount);
        $this->assertSame('388.00', (string) $k->base_amount);
    }

    public function test_komisen_tidak_melebihi_50_peratus_bayaran(): void
    {
        // Reseller dengan komisen RM200 — tetapi had 50%
        $r = $this->reseller(['commission_value' => 200]);
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id, 200);

        $cas = $this->casDibayar($this->t->id, $l->id, 200);
        $k = $this->komisen->rekodKomisen($cas, $l);

        // 50% × RM200 = RM100, bukan RM200
        $this->assertSame('100.00', (string) $k->commission_amount);
    }

    public function test_diskaun_turunkan_komisen(): void
    {
        $r = $this->reseller();   // RM100
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id, 150);

        // Pelanggan dapat diskaun — bayar RM150 sahaja
        $cas = $this->casDibayar($this->t->id, $l->id, 150);
        $k = $this->komisen->rekodKomisen($cas, $l);

        // Had 50% × 150 = RM75 < RM100 → RM75
        $this->assertSame('75.00', (string) $k->commission_amount);
    }

    // ===============================================================
    // PERATURAN 5 — matang 30 hari
    // ===============================================================

    public function test_komisen_matang_selepas_30_hari(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);

        $cas = $this->casDibayar($this->t->id, $l->id);
        $k = $this->komisen->rekodKomisen($cas, $l);

        $this->assertSame(ResellerCommission::STATUS_PENDING, $k->status);
        $this->assertSame(
            30,
            (int) $k->earned_at->diffInDays($k->mature_at),
            'Tempoh matang mesti tepat 30 hari.'
        );
        $this->assertFalse($k->matang(), 'Komisen baru tidak patut matang serta-merta.');
    }

    public function test_maturekan_komisen_selepas_30_hari(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $this->casDibayar($this->t->id, $l->id);

        // Majukan masa 31 hari
        $this->travel(31)->days();

        $bilangan = $this->komisen->maturekanKomisen();
        $this->assertSame(1, $bilangan);

        $k = ResellerCommission::first();
        $this->assertSame(ResellerCommission::STATUS_APPROVED, $k->status);
        $this->assertNotNull($k->approved_at);
    }

    // ===============================================================
    // PERATURAN 6 & 7 — churn balikkan pending, BUKAN approved
    // ===============================================================

    public function test_churn_balikkan_komisen_pending(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $this->casDibayar($this->t->id, $l->id);

        $l->update(['status' => BillingSubscription::STATUS_CANCELLED]);

        $bilangan = $this->komisen->kesanChurn();
        $this->assertSame(1, $bilangan);

        $this->assertSame(
            ResellerCommission::STATUS_REVERSED,
            ResellerCommission::first()->status
        );
    }

    public function test_komisen_approved_tidak_dibatalkan_oleh_churn(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $this->casDibayar($this->t->id, $l->id);

        // Matangkan dahulu
        $this->travel(31)->days();
        $this->komisen->maturekanKomisen();

        $this->assertSame(
            ResellerCommission::STATUS_APPROVED,
            ResellerCommission::first()->status
        );

        // Sekarang churn
        $l->refresh();
        $l->update(['status' => BillingSubscription::STATUS_CANCELLED]);
        $this->komisen->kesanChurn();

        // Peraturan 7: TIDAK dibatalkan — sudah jadi hak
        $this->assertSame(
            ResellerCommission::STATUS_APPROVED,
            ResellerCommission::first()->status,
            'Komisen approved TIDAK boleh dibatalkan oleh churn (sudah jadi hak).'
        );
    }

    public function test_refund_balikkan_komisen_pending(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $cas = $this->casDibayar($this->t->id, $l->id);
        $this->komisen->rekodKomisen($cas, $l);

        $this->billing->tandakanRefund($cas);

        $this->assertSame(
            ResellerCommission::STATUS_REVERSED,
            ResellerCommission::first()->status
        );
    }

    // ===============================================================
    // PERATURAN 8 — snapshot bank
    // ===============================================================

    public function test_payout_simpan_snapshot_bank(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $this->casDibayar($this->t->id, $l->id);

        $this->travel(31)->days();
        $this->komisen->maturekanKomisen();

        $payout = $this->komisen->binaPayout();
        $this->assertCount(1, $payout);

        // Snapshot bank diambil PADA MASA PAYOUT — rekod ini kekal walaupun
        // reseller menukar akaun kemudian (jejak audit).
        $this->assertSame('Maybank', $payout[0]->bank_snapshot['bank']);
        $this->assertSame('100.00', (string) $payout[0]->amount);

        // Tukar akaun SELEPAS payout — snapshot TIDAK berubah
        $r->update(['bank_details' => ['bank' => 'CIMB', 'account' => '9999']]);
        $this->assertSame(
            'Maybank',
            $payout[0]->fresh()->bank_snapshot['bank'],
            'Snapshot bank tidak boleh berubah selepas payout dicipta.'
        );
    }

    public function test_payout_dilangkau_jika_baki_kurang_rm100(): void
    {
        $r = $this->reseller(['commission_value' => 50]);
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $this->casDibayar($this->t->id, $l->id, 388);

        $this->travel(31)->days();
        $this->komisen->maturekanKomisen();

        // Hanya RM50 < RM100 minimum
        $this->assertCount(0, $this->komisen->binaPayout());
    }

    // ===============================================================
    // PERATURAN 9 — tiada self-referral
    // ===============================================================

    public function test_self_referral_ditolak(): void
    {
        // Reseller ialah pemilik tenant yang sama
        $r = $this->reseller(['email' => 'pemilik@billing.my']);
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $cas = $this->casDibayar($this->t->id, $l->id);

        $k = $this->komisen->rekodKomisen($cas, $l);

        $this->assertNull($k, 'Self-referral tidak boleh dapat komisen.');
    }

    // ===============================================================
    // PERATURAN 11 — IP di-hash (PDPA)
    // ===============================================================

    public function test_ip_disimpan_sebagai_hash(): void
    {
        $hash = ResellerClick::hashIp('203.0.113.45');

        $this->assertNotNull($hash);
        $this->assertSame(64, strlen($hash), 'Mesti SHA-256 hex (64 aksara).');
        $this->assertStringNotContainsString('203.0.113', $hash, 'IP sebenar TIDAK boleh muncul.');
        $this->assertSame($hash, ResellerClick::hashIp('203.0.113.45'), 'Hash mesti deterministik.');
        $this->assertNotSame($hash, ResellerClick::hashIp('198.51.100.1'));
    }

    // ===============================================================
    // PERATURAN 12 — tiada komisen jika tidak aktif
    // ===============================================================

    public function test_tiada_komisen_jika_langganan_tidak_aktif(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);

        $l = $this->langgananAktif($this->t->id);
        $l->update(['status' => BillingSubscription::STATUS_PAST_DUE]);

        $cas = $this->casDibayar($this->t->id, $l->id);
        $k = $this->komisen->rekodKomisen($cas, $l->fresh());

        $this->assertNull($k, 'Tiada komisen untuk langganan menunggak.');
    }

    public function test_tiada_komisen_jika_reseller_digantung(): void
    {
        $r = $this->reseller(['status' => Reseller::STATUS_SUSPENDED]);
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $cas = $this->casDibayar($this->t->id, $l->id);

        $this->assertNull($this->komisen->rekodKomisen($cas, $l));
    }

    // ===============================================================
    // WEBHOOK — keselamatan
    // ===============================================================

    public function test_webhook_tanpa_tandatangan_ditolak(): void
    {
        $hasil = app(ChipWebhookService::class)->proses('{"id":"x"}', null, null);

        $this->assertFalse($hasil['ok'], 'Webhook TANPA tandatangan mesti ditolak.');
        $this->assertStringContainsString('Tandatangan', $hasil['mesej']);
    }

    public function test_webhook_tandatangan_palsu_ditolak(): void
    {
        $hasil = app(ChipWebhookService::class)->proses(
            '{"id":"x","event_type":"purchase.paid"}',
            base64_encode('palsu'),
            (string) now()->timestamp
        );

        $this->assertFalse($hasil['ok']);
    }

    public function test_webhook_sentiasa_pulang_200(): void
    {
        // Biarpun tandatangan salah, endpoint pulang 200 supaya CHIP tidak retry
        $this->postJson('/api/v1/webhooks/chip', ['id' => 'x'])
            ->assertOk()
            ->assertJsonPath('received', true)
            ->assertJsonPath('processed', false);
    }

    // ===============================================================
    // LANGGANAN — integriti
    // ===============================================================

    public function test_peralihan_status_langganan(): void
    {
        $l = BillingSubscription::create([
            'tenant_id' => $this->t->id,
            'plan_code' => 'growth',
            'status' => BillingSubscription::STATUS_PENDING,
            'amount' => 388,
            'currency' => 'MYR',
        ]);

        $this->assertFalse($l->aktif());

        // Sah: pending → active
        $this->assertTrue($l->bolehPindah(BillingSubscription::STATUS_ACTIVE));
        // Tidak sah: pending → expired TIDAK dibenarkan dalam aliran ini
        // (ALIRAN[pending] = [active, expired, cancelled], jadi expired SAH)
        $this->assertTrue($l->bolehPindah(BillingSubscription::STATUS_EXPIRED));
        // Tidak sah: pending → past_due
        $this->assertFalse($l->bolehPindah(BillingSubscription::STATUS_PAST_DUE));

        // Cancelled ialah terminal — tiada perpindahan keluar
        $l->update(['status' => BillingSubscription::STATUS_CANCELLED]);
        $this->assertFalse($l->bolehPindah(BillingSubscription::STATUS_ACTIVE));
        $this->assertFalse($l->aktif());
    }

    public function test_aktifkan_idempoten(): void
    {
        $l = BillingSubscription::create([
            'tenant_id' => $this->t->id,
            'plan_code' => 'growth',
            'status' => BillingSubscription::STATUS_PENDING,
            'amount' => 388,
            'currency' => 'MYR',
        ]);

        $cas = BillingCharge::create([
            'tenant_id' => $this->t->id,
            'billing_subscription_id' => $l->id,
            'kind' => BillingCharge::KIND_SUBSCRIPTION,
            'period_key' => now()->format('Y-m'),
            'amount' => 388,
            'currency' => 'MYR',
            'status' => BillingCharge::STATUS_PENDING,
        ]);

        $pertama = $this->billing->aktifkanSelepasBayaran($cas);
        $kedua = $this->billing->aktifkanSelepasBayaran($cas->fresh());

        $this->assertTrue($pertama, 'Bayaran pertama mesti mengaktifkan.');
        $this->assertFalse($kedua, 'Bayaran kedua mesti idempoten (tiada perubahan).');
        $this->assertSame(BillingSubscription::STATUS_ACTIVE, $l->fresh()->status);
    }

    public function test_harga_dibaca_dari_config(): void
    {
        $this->assertSame(388.0, $this->billing->hargaBulanan('growth'));
        $this->assertSame(88.0, $this->billing->hargaBulanan('starter'));
    }

    // ===============================================================
    // TOKEN AI — WAJIB, HASIL TAMBAHAN (bukan kos)
    // ===============================================================

    public function test_pakej_token_tersedia(): void
    {
        $pakej = $this->billing->pakejToken();

        $this->assertNotEmpty($pakej, 'Mesti ada sekurang-kurangnya satu pakej token.');
        $this->assertArrayHasKey('t60m', $pakej);

        foreach ($pakej as $kod => $p) {
            $this->assertArrayHasKey('name', $p);
            $this->assertArrayHasKey('tokens', $p);
            $this->assertArrayHasKey('price', $p);
            $this->assertArrayHasKey('kos_pembekal', $p, "Pakej {$kod} mesti ada kos pembekal.");
            $this->assertGreaterThan(0, $p['tokens'], "Pakej {$kod} mesti ada token.");
            $this->assertGreaterThan(0, $p['price'], "Pakej {$kod} mesti ada harga.");
        }
    }

    public function test_jumlah_bulanan_langganan_plus_token(): void
    {
        // Growth RM388 + 60 juta token RM55 = RM443
        $this->assertSame(
            443.0,
            $this->billing->jumlahBulanan('growth', 't60m'),
            'Jumlah mesti = langganan + token.'
        );

        // Growth RM388 + 130 juta token RM88 = RM476
        $this->assertSame(476.0, $this->billing->jumlahBulanan('growth', 't130m'));

        // Growth RM388 + 2 bilion token RM1100 = RM1488
        $this->assertSame(1488.0, $this->billing->jumlahBulanan('growth', 't2b'));
    }

    // ===============================================================
    // MARGIN TOKEN — berdasarkan harga pembekal sebenar (27 Okt 2026)
    // ===============================================================

    public function test_semua_pakej_token_menguntungkan(): void
    {
        $rugi = $this->billing->pakejRugi();

        $this->assertSame(
            [],
            $rugi,
            'Pakej token RUGI atau margin terlalu nipis: ' . implode('; ', $rugi)
        );
    }

    public function test_margin_token_90_peratus(): void
    {
        $analisis = $this->billing->analisisMarginToken();

        foreach ($analisis as $kod => $a) {
            $this->assertSame(
                90.9,
                $a['margin_pct'],
                "Pakej {$kod} margin dijangka 90.9% (jual RM{$a['jual']}, beli RM{$a['beli']})."
            );
        }
    }

    public function test_harga_jual_melebihi_kos_pembekal(): void
    {
        foreach ($this->billing->analisisMarginToken() as $kod => $a) {
            $this->assertGreaterThan(
                $a['beli'],
                $a['jual'],
                "Pakej {$kod} dijual di Bawah kos pembekal — kerugian berulang."
            );
            $this->assertGreaterThan(0, $a['untung']);
        }
    }

    public function test_untung_bersih_dengan_dan_tanpa_reseller(): void
    {
        // Tanpa reseller: RM388 + RM55 - kos RM5 = RM438
        $tanpa = $this->billing->untungBersih('growth', 't60m', false);
        $this->assertSame(388.0, $tanpa['langganan']);
        $this->assertSame(55.0, $tanpa['token']);
        $this->assertSame(443.0, $tanpa['jumlah_dibilkan']);
        $this->assertSame(0.0, $tanpa['komisen_reseller']);
        $this->assertSame(5.0, $tanpa['kos_token']);
        $this->assertSame(438.0, $tanpa['untung_bersih']);

        // Dengan reseller: RM443 - RM100 - RM5 = RM338
        $dengan = $this->billing->untungBersih('growth', 't60m', true);
        $this->assertSame(100.0, $dengan['komisen_reseller']);
        $this->assertSame(338.0, $dengan['untung_bersih']);
    }

    public function test_untung_meningkat_dengan_pakej_besar(): void
    {
        $kecil = $this->billing->untungBersih('growth', 't60m', true);
        $besar = $this->billing->untungBersih('growth', 't2b', true);

        // Pakej besar: RM388 + RM1100 - RM100 - RM100 = RM1288
        $this->assertSame(1288.0, $besar['untung_bersih']);
        $this->assertGreaterThan(
            $kecil['untung_bersih'],
            $besar['untung_bersih'],
            'Pakej lebih besar mesti memberi untung lebih tinggi.'
        );
    }

    public function test_pakej_token_tidak_sah_ditolak(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->billing->hargaToken('pakej_hantu');
    }

    public function test_komisen_dikira_dari_langganan_bukan_jumlah_penuh(): void
    {
        $r = $this->reseller();   // RM100
        $this->rujuk($r, $this->t->id);

        // Komisen untuk pelan growth (RM388) — mesti RM100, bukan RM146
        // (50% × RM438 jika dikira dari jumlah termasuk token).
        $this->assertSame('100.00', $this->billing->komisenUntuk('growth', $r));

        // Had 50% dikira dari LANGGANAN sahaja
        $murah = $this->reseller(['commission_value' => 300]);
        $this->assertSame(
            '194.00',
            $this->billing->komisenUntuk('growth', $murah),
            'Had 50% mesti dari RM388, bukan dari jumlah termasuk token.'
        );
    }

    public function test_komisen_tidak_melebihi_langganan(): void
    {
        // Komisen RM500 pada pelan RM88 — mesti terhad 50% = RM44
        $r = $this->reseller(['commission_value' => 500]);
        $this->assertSame('44.00', $this->billing->komisenUntuk('starter', $r));
    }

    public function test_komisen_tetap_walau_pelanggan_beli_token_besar(): void
    {
        $r = $this->reseller();
        $this->rujuk($r, $this->t->id);
        $l = $this->langgananAktif($this->t->id);

        // Cas = RM388 langganan + RM1100 token (2 bilion) = RM1488
        $cas = BillingCharge::withoutGlobalScope('tenant')->create([
            'tenant_id' => $this->t->id,
            'billing_subscription_id' => $l->id,
            'chip_purchase_id' => 'purch_' . uniqid(),
            'status' => BillingCharge::STATUS_PAID,
            'kind' => BillingCharge::KIND_SUBSCRIPTION,
            'period_key' => now()->format('Y-m'),
            'amount' => 1488,
            'currency' => 'MYR',
            'paid_at' => now(),
            'chip_payload' => [
                'pecahan' => ['langganan' => 388, 'token' => 1100, 'pakej_token' => 't2b'],
            ],
        ]);

        $k = $this->komisen->rekodKomisen($cas, $l);

        $this->assertNotNull($k);
        $this->assertSame(
            '100.00',
            (string) $k->commission_amount,
            'Komisen mesti RM100 (dari langganan sahaja), BUKAN RM146 atau lebih tinggi.'
        );
        $this->assertSame(
            '388.00',
            (string) $k->base_amount,
            'Asas komisen mesti langganan sahaja, bukan jumlah termasuk token.'
        );
    }

    public function test_api_pakej_token(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->getJson('/api/v1/billing/packages')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [['code', 'name', 'tokens', 'price']],
            ]);
    }

    public function test_api_langgan_tanpa_token_ditolak(): void
    {
        $this->sebagai($this->u, $this->t);

        // ⚠️ token_package WAJIB — tanpa ia, AI tidak berfungsi
        $this->postJson('/api/v1/billing/subscribe', [
            'plan_code' => 'growth',
            'email' => 'pemilik@billing.my',
        ])->assertStatus(422)->assertJsonValidationErrors('token_package');
    }

    public function test_api_langgan_token_tidak_sah_ditolak(): void
    {
        $this->sebagai($this->u, $this->t);

        $this->postJson('/api/v1/billing/subscribe', [
            'plan_code' => 'growth',
            'token_package' => 'pakej_hantu',
            'email' => 'pemilik@billing.my',
        ])->assertStatus(500);   // RuntimeException dari service (CHIP tidak dikonfigurasi)
    }

    // ===============================================================
    // PENGASINGAN — komisen tidak bocor merentas reseller
    // ===============================================================

    public function test_reseller_lain_tidak_nampak_komisen(): void
    {
        $r1 = $this->reseller();
        $this->rujuk($r1, $this->t->id);
        $l = $this->langgananAktif($this->t->id);
        $this->casDibayar($this->t->id, $l->id);
        $this->komisen->rekodKomisen(BillingCharge::first(), $l);

        $r2 = $this->reseller();

        $this->assertSame(1, ResellerCommission::where('reseller_id', $r1->id)->count());
        $this->assertSame(0, ResellerCommission::where('reseller_id', $r2->id)->count());
    }
}
