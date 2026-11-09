<?php

namespace Tests\Feature;

use App\Modules\BLive\Models\Avatar;
use App\Modules\BLive\Models\ConsentRecord;
use App\Modules\BLive\Models\HostPersonality;
use App\Modules\BLive\Models\LiveMessage;
use App\Modules\BLive\Models\LiveSession;
use App\Modules\BLive\Models\StreamDestination;
use App\Modules\BLive\Services\AvatarService;
use App\Modules\BLive\Services\ConsentService;
use App\Modules\BLive\Services\HostBrainService;
use App\Modules\BLive\Services\LiveControlService;
use App\Modules\BLive\Services\LiveSessionService;
use App\Modules\BLive\Services\StreamingGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * B-Live AI - hos digital AI untuk live langsung.
 *
 * Fokus ujian:
 *   - Kitaran hidup avatar (cipta -> jana -> layingan)
 *   - Pagar kebenaran / consent gate
 *   - Kawalan hybrid manusia + AI (takeover, pause, emergency stop)
 *   - Pemisahan tenant (tenant isolation)
 *   - Keselamatan AI Brain (tidak karang harga, eskalasi bila tidak pasti)
 *   - Sasaran penstriman pelbagai platform
 */
class BLiveTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    private $u;

    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@blive.my', 'B-Live Studio');
    }

    /**
     * Cipta personaliti hos.
     */
    private function personaliti(array $ganti = []): HostPersonality
    {
        $this->dalamTenant($this->t);

        return HostPersonality::create(array_merge([
            'name' => 'Hos Profesional',
            'slug' => 'hos-profesional-'.uniqid(),
            'archetype' => HostPersonality::ARCHETYPE_PROFESSIONAL,
            'primary_language' => 'ms',
            'locale' => 'ms-MY',
            'is_active' => true,
        ], $ganti));
    }

    /**
     * Cipta avatar sedia digunakan (status ready, consent granted).
     */
    private function avatarSiap(array $ganti = []): Avatar
    {
        $this->dalamTenant($this->t);

        $avatar = Avatar::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'name' => 'Hos Digital',
            'slug' => 'hos-digital-'.uniqid(),
            'avatar_type' => Avatar::TYPE_AI_GENERATED,
            'engine' => 'simulated',
            'status' => Avatar::STATUS_READY,
            'consent_status' => Avatar::CONSENT_GRANTED,
            'is_ai_disclosed' => true,
        ], $ganti));

        return $avatar;
    }

    /**
     * Cipta sesi live.
     */
    private function sesi(array $ganti = []): LiveSession
    {
        $this->dalamTenant($this->t);

        return LiveSession::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'title' => 'Sesi Ujian',
            'status' => LiveSession::STATUS_LIVE,
            'started_at' => now(),
            'session_mode' => LiveSession::MODE_AI_ASSISTED,
            'language' => 'ms',
        ], $ganti));
    }

    // ---------------------------------------------------------------------
    // B-Avatar Creator
    // ---------------------------------------------------------------------

    public function test_cipta_avatar_berjaya(): void
    {
        $this->dalamTenant($this->t);

        $hasil = app(AvatarService::class)->cipta([
            'name' => 'Presenter AI',
            'avatar_type' => Avatar::TYPE_AI_GENERATED,
            'appearance' => ['gender' => 'female', 'age_visual' => 28],
        ]);

        $this->assertTrue($hasil['success']);
        $this->assertSame('Presenter AI', $hasil['avatar']->name);
        $this->assertSame(Avatar::STATUS_DRAFT, $hasil['avatar']->status);
        $this->assertSame(Avatar::CONSENT_GRANTED, $hasil['avatar']->consent_status);
    }

    public function test_jana_avatar_menghasilkan_manifest_dan_status_ready(): void
    {
        $avatar = $this->avatarSiap(['status' => Avatar::STATUS_DRAFT]);

        $hasil = app(AvatarService::class)->jana($avatar);

        $this->assertTrue($hasil['success'], $hasil['error'] ?? '');
        $this->assertSame(Avatar::STATUS_READY, $hasil['avatar']->status);
        $this->assertNotNull($hasil['avatar']->model_reference);
        $this->assertNotNull($hasil['avatar']->asset_storage_path);
        $this->assertTrue($hasil['job']->selesai());
    }

    public function test_jana_avatar_meningkatkan_versi_aset(): void
    {
        $avatar = $this->avatarSiap(['asset_version' => 1]);

        $hasil = app(AvatarService::class)->jana($avatar);

        $this->assertTrue($hasil['success']);
        $this->assertSame(2, $hasil['avatar']->asset_version);
    }

    // ---------------------------------------------------------------------
    // Consent gate
    // ---------------------------------------------------------------------

    public function test_avatar_ai_generated_layak_live(): void
    {
        $avatar = $this->avatarSiap();

        $semak = app(AvatarService::class)->semakKelayakan($avatar);

        $this->assertTrue($semak['ok'], $semak['reason'] ?? '');
    }

    public function test_avatar_belum_ready_tidak_layak_live(): void
    {
        $avatar = $this->avatarSiap(['status' => Avatar::STATUS_DRAFT]);

        $semak = app(AvatarService::class)->semakKelayakan($avatar);

        $this->assertFalse($semak['ok']);
        $this->assertSame('avatar_not_ready', $semak['code']);
    }

    public function test_avatar_tanpa_pengisytiharan_ai_tidak_layak_live(): void
    {
        $avatar = $this->avatarSiap(['is_ai_disclosed' => false]);

        $semak = app(AvatarService::class)->semakKelayakan($avatar);

        $this->assertFalse($semak['ok']);
        $this->assertSame('ai_disclosure_required', $semak['code']);
    }

    public function test_kebenaran_ditarik_balik_membuat_avatar_tidak_layak(): void
    {
        $this->dalamTenant($this->t);

        $rekod = ConsentRecord::create([
            'uuid' => (string) Str::uuid(),
            'consent_type' => ConsentRecord::TYPE_INDIVIDUAL_RELEASE,
            'status' => ConsentRecord::STATUS_GRANTED,
            'granted_at' => now(),
        ]);

        $avatar = $this->avatarSiap([
            'avatar_type' => Avatar::TYPE_DIGITAL_TWIN,
            'consent_record_id' => $rekod->id,
            'consent_status' => Avatar::CONSENT_GRANTED,
        ]);

        $semakSebelum = app(AvatarService::class)->semakKelayakan($avatar);
        $this->assertTrue($semakSebelum['ok'], $semakSebelum['reason'] ?? '');

        app(ConsentService::class)->tarikBalik($rekod);

        $semakSelepas = app(AvatarService::class)->semakKelayakan($avatar->fresh());
        $this->assertFalse($semakSelepas['ok']);
        $this->assertSame(Avatar::CONSENT_REVOKED, $avatar->fresh()->consent_status);
    }

    public function test_kebenaran_tidak_diluluskan_menolak_avatar_identiti_sebenar(): void
    {
        $this->dalamTenant($this->t);

        $rekod = ConsentRecord::create([
            'uuid' => (string) Str::uuid(),
            'consent_type' => ConsentRecord::TYPE_LICENSED,
            'status' => ConsentRecord::STATUS_PENDING,
        ]);

        $avatar = $this->avatarSiap([
            'avatar_type' => Avatar::TYPE_PHOTO,
            'consent_record_id' => $rekod->id,
            'consent_status' => Avatar::CONSENT_PENDING,
        ]);

        $semak = app(AvatarService::class)->semakKelayakan($avatar);

        $this->assertFalse($semak['ok']);
        $this->assertSame('consent_invalid', $semak['code']);
    }

    // ---------------------------------------------------------------------
    // Sesi live
    // ---------------------------------------------------------------------

    public function test_sesi_tidak_boleh_mula_tanpa_avatar_layak(): void
    {
        $avatar = $this->avatarSiap(['status' => Avatar::STATUS_DRAFT]);

        $sesi = $this->sesi([
            'avatar_id' => $avatar->id,
            'status' => LiveSession::STATUS_DRAFT,
        ]);

        $hasil = app(LiveSessionService::class)->mula($sesi);

        $this->assertFalse($hasil['success']);
        $this->assertSame('avatar_not_ready', $hasil['code']);
    }

    public function test_sesi_boleh_mula_dengan_avatar_layak(): void
    {
        $avatar = $this->avatarSiap();
        $sesi = $this->sesi(['avatar_id' => $avatar->id, 'status' => LiveSession::STATUS_DRAFT]);

        $hasil = app(LiveSessionService::class)->mula($sesi);

        $this->assertTrue($hasil['success'], $hasil['error'] ?? '');
        $this->assertSame(LiveSession::STATUS_LIVE, $hasil['session']->status);
        $this->assertNotNull($hasil['session']->started_at);
    }

    public function test_henti_sesi_mencatat_metrik_rendering(): void
    {
        $sesi = $this->sesi();

        $hasil = app(LiveSessionService::class)->henti($sesi);

        $this->assertTrue($hasil['success']);
        $this->assertSame(LiveSession::STATUS_ENDED, $hasil['session']->status);
        $this->assertNotNull($hasil['session']->ended_at);
    }

    // ---------------------------------------------------------------------
    // Kawalan hybrid manusia + AI
    // ---------------------------------------------------------------------

    public function test_takeover_mengubah_mod_sesi_kepada_human_led(): void
    {
        $sesi = $this->sesi();

        $hasil = app(LiveControlService::class)->ambilAlih($sesi, $this->u->id);

        $this->assertTrue($hasil['success']);
        $this->assertSame(LiveSession::MODE_HUMAN_LED, $hasil['session']->session_mode);
    }

    public function test_release_mengembalikan_kawalan_kepada_ai(): void
    {
        $sesi = $this->sesi(['session_mode' => LiveSession::MODE_HUMAN_LED]);

        $hasil = app(LiveControlService::class)->lepaskan($sesi, $this->u->id);

        $this->assertTrue($hasil['success']);
        $this->assertSame(LiveSession::MODE_AI_ASSISTED, $hasil['session']->session_mode);
    }

    public function test_pause_ai_menukar_status_kepada_paused(): void
    {
        $sesi = $this->sesi();

        $hasil = app(LiveControlService::class)->jedaAi($sesi, $this->u->id);

        $this->assertTrue($hasil['success']);
        $this->assertSame(LiveSession::STATUS_PAUSED, $hasil['session']->status);
    }

    public function test_resume_ai_mengembalikan_status_live(): void
    {
        $sesi = $this->sesi(['status' => LiveSession::STATUS_PAUSED]);

        $hasil = app(LiveControlService::class)->sambungAi($sesi, $this->u->id);

        $this->assertTrue($hasil['success']);
        $this->assertSame(LiveSession::STATUS_LIVE, $hasil['session']->status);
    }

    public function test_cakap_menjana_mesej_dengan_sumber_human(): void
    {
        $sesi = $this->sesi();

        $hasil = app(LiveControlService::class)->cakap($sesi, 'Selamat datang ke live kami!', $this->u->id);

        $this->assertTrue($hasil['success'], $hasil['error'] ?? '');
        $this->assertSame(LiveMessage::SOURCE_HUMAN, $hasil['message']->source);
        $this->assertSame(LiveMessage::ROLE_HOST, $hasil['message']->role);
        $this->assertSame(1, $sesi->fresh()->speech_count);
    }

    public function test_cakap_menolak_teks_kosong(): void
    {
        $sesi = $this->sesi();

        $hasil = app(LiveControlService::class)->cakap($sesi, '   ', $this->u->id);

        $this->assertFalse($hasil['success']);
    }

    public function test_henti_kecemasan_menakhiri_sesi(): void
    {
        $sesi = $this->sesi();

        $hasil = app(LiveControlService::class)->hentiKecemasan($sesi, $this->u->id, 'Ujian');

        $this->assertTrue($hasil['success']);
        $this->assertSame(LiveSession::STATUS_ENDED, $hasil['session']->status);
    }

    public function test_kawalan_meriekod_jejak_audit(): void
    {
        $sesi = $this->sesi();

        app(LiveControlService::class)->ambilAlih($sesi, $this->u->id);

        $this->assertSame(1, $sesi->controls()->count());
        $this->assertSame('takeover', $sesi->controls()->first()->control_type);
    }

    // ---------------------------------------------------------------------
    // AI Brain
    // ---------------------------------------------------------------------

    public function test_klasifikasi_intent_mengenali_soalan(): void
    {
        $brain = app(HostBrainService::class);

        $this->assertSame('greeting', $brain->klasifikasiIntent('Helo, apa khabar?'));
        $this->assertSame('price_inquiry', $brain->klasifikasiIntent('Berapakah harga produk ini?'));
        $this->assertSame('order_intent', $brain->klasifikasiIntent('Saya nak beli'));
        $this->assertSame('general', $brain->klasifikasiIntent('Hujan hari ini'));
    }

    public function test_aturan_bisnes_menolak_soalan_harga_tanpa_harga_diluluskan(): void
    {
        $brain = app(HostBrainService::class);

        $hasil = $brain->semakPeraturanBisnes(
            ['facts' => [], 'approved_prices' => []],
            'Ada diskaun 50% hari ini?'
        );

        $this->assertFalse($hasil['ok']);
        $this->assertSame('price_not_approved', $hasil['code']);
    }

    public function test_aturan_bisnes_membenarkan_soalan_harga_dengan_harga_diluluskan(): void
    {
        $brain = app(HostBrainService::class);

        $hasil = $brain->semakPeraturanBisnes(
            ['facts' => ['Produk A'], 'approved_prices' => [99.90]],
            'Berapakah harga produk ini?'
        );

        $this->assertTrue($hasil['ok'], $hasil['reason'] ?? '');
    }

    public function test_keselamatan_men_escalate_bila_ai_tidak_pasti(): void
    {
        $brain = app(HostBrainService::class);

        $hasil = $brain->sahkanKeselamatan(
            'Saya tidak pasti tentang maklumat itu.',
            ['facts' => [], 'approved_prices' => []],
            'soalan'
        );

        $this->assertTrue($hasil['escalate']);
    }

    public function test_keselamatan_men_escalate_bila_ada_harga_karangan(): void
    {
        $brain = app(HostBrainService::class);

        $hasil = $brain->sahkanKeselamatan(
            'Harganya RM50 sahaja.',
            ['facts' => [], 'approved_prices' => []],
            'soalan'
        );

        $this->assertTrue($hasil['escalate']);
    }

    public function test_keselamatan_menerima_harga_diluluskan(): void
    {
        $brain = app(HostBrainService::class);

        $hasil = $brain->sahkanKeselamatan(
            'Harganya RM50 sahaja.',
            ['facts' => ['Produk A'], 'approved_prices' => [50.00]],
            'soalan'
        );

        $this->assertFalse($hasil['escalate']);
    }

    public function test_brain_menolak_jawab_bila_sesi_bukan_live(): void
    {
        $sesi = $this->sesi(['status' => LiveSession::STATUS_DRAFT]);

        $hasil = app(HostBrainService::class)->jawab(['content' => 'Helo'], $sesi);

        $this->assertFalse($hasil['success']);
        $this->assertSame('session_not_live', $hasil['code']);
    }

    public function test_brain_menolak_jawab_bila_mod_human_led(): void
    {
        $sesi = $this->sesi(['session_mode' => LiveSession::MODE_HUMAN_LED]);

        $hasil = app(HostBrainService::class)->jawab(['content' => 'Helo'], $sesi);

        $this->assertFalse($hasil['success']);
        $this->assertSame('human_led', $hasil['code']);
    }

    // ---------------------------------------------------------------------
    // Penstriman
    // ---------------------------------------------------------------------

    public function test_sambung_sasaran_memerlukan_rujukan_kelayakan(): void
    {
        $sesi = $this->sesi();

        $hasil = app(StreamingGatewayService::class)->sambung($sesi, [
            'platform' => StreamDestination::PLATFORM_YOUTUBE,
            'ingest_url' => 'rtmp://a.rtmp.youtube.com/live2',
        ]);

        $this->assertFalse($hasil['success']);
        $this->assertStringContainsString('kelayakan', $hasil['error']);
    }

    public function test_sambung_sasaran_youtube_berjaya(): void
    {
        $sesi = $this->sesi();

        $hasil = app(StreamingGatewayService::class)->sambung($sesi, [
            'platform' => StreamDestination::PLATFORM_YOUTUBE,
            'channel_name' => 'Saluran Ujian',
            'credential_reference' => 'vault://youtube/abc123',
            'ingest_url' => 'rtmp://a.rtmp.youtube.com/live2',
        ]);

        $this->assertTrue($hasil['success'], $hasil['error'] ?? '');
        $this->assertSame(StreamDestination::PLATFORM_YOUTUBE, $hasil['destination']->platform);
        $this->assertSame('vault://youtube/abc123', $hasil['destination']->credential_reference);
    }

    public function test_platform_tiktok_tak_aktif_ditolak(): void
    {
        $sesi = $this->sesi();

        $hasil = app(StreamingGatewayService::class)->sambung($sesi, [
            'platform' => StreamDestination::PLATFORM_TIKTOK,
            'credential_reference' => 'vault://tiktok/abc123',
            'ingest_url' => 'rtmp://tiktok.com/live',
        ]);

        $this->assertFalse($hasil['success']);
    }

    public function test_mula_sasaran_tanpa_ingest_url_gagal(): void
    {
        $sesi = $this->sesi();

        $sambung = app(StreamingGatewayService::class)->sambung($sesi, [
            'platform' => StreamDestination::PLATFORM_YOUTUBE,
            'credential_reference' => 'vault://youtube/abc123',
        ]);

        $this->assertTrue($sambung['success']);

        $hasil = app(StreamingGatewayService::class)->mula($sambung['destination']);

        $this->assertFalse($hasil['success']);
        $this->assertSame(StreamDestination::STATUS_FAILED, $hasil['destination']->stream_status);
    }

    public function test_henti_semua_men出息kan_semua_sasaran(): void
    {
        $sesi = $this->sesi();

        $gateway = app(StreamingGatewayService::class);

        $sambung = $gateway->sambung($sesi, [
            'platform' => StreamDestination::PLATFORM_YOUTUBE,
            'credential_reference' => 'vault://youtube/abc123',
            'ingest_url' => 'rtmp://a.rtmp.youtube.com/live2',
        ]);

        $gateway->mula($sambung['destination']);

        $jumlah = $gateway->hentiSemua($sesi);

        $this->assertSame(1, $jumlah);
        $this->assertSame(StreamDestination::STATUS_DISCONNECTED, $sambung['destination']->fresh()->stream_status);
    }

    // ---------------------------------------------------------------------
    // Tenant isolation
    // ---------------------------------------------------------------------

    public function test_avatar_syarikat_lain_tidak_dilihat(): void
    {
        $avatar = $this->avatarSiap();

        $this->assertSame(1, Avatar::count());

        // Syarikat kedua.
        [$u2, $t2] = $this->daftar('pemilik2@blive.my', 'Syarikat Kedua');
        $this->dalamTenant($t2);

        $this->assertSame(0, Avatar::count());
        $this->assertNull(Avatar::find($avatar->id));
    }

    public function test_sesi_syarikat_lain_tidak_boleh_diubah(): void
    {
        $sesi = $this->sesi();

        [$u2, $t2] = $this->daftar('pemilik3@blive.my', 'Syarikat Ketiga');
        $this->dalamTenant($t2);

        $hasil = app(LiveControlService::class)->ambilAlih($sesi, $u2->id);

        $this->assertFalse($hasil['success']);
        $this->assertSame(LiveSession::MODE_AI_ASSISTED, $sesi->fresh()->session_mode);
    }

    // ---------------------------------------------------------------------
    // API
    // ---------------------------------------------------------------------

    public function test_api_avatar_mengembalikan_data(): void
    {
        $this->avatarSiap();

        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/blive/avatars')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_api_cipta_avatar_melalui_http(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/blive/avatars', [
                'name' => 'Hos Ujian API',
                'avatar_type' => Avatar::TYPE_AI_GENERATED,
            ])
            ->assertCreated()
            ->assertJsonPath('success', true);
    }

    public function test_api_tolak_input_tidak_sah(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/blive/avatars', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_api_perlu_autentikasi(): void
    {
        $this->getJson('/api/v1/blive/avatars')->assertUnauthorized();
    }

    public function test_api_sesi_live_dan_kawalan(): void
    {
        $avatar = $this->avatarSiap();

        $sesi = $this->sesi(['avatar_id' => $avatar->id]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/blive/live-sessions/{$sesi->id}/takeover")
            ->assertOk()
            ->assertJsonPath('data.session_mode', LiveSession::MODE_HUMAN_LED);
    }

    public function test_api_henti_kecemasan(): void
    {
        $sesi = $this->sesi();

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/blive/live-sessions/{$sesi->id}/emergency-stop")
            ->assertOk()
            ->assertJsonPath('data.status', LiveSession::STATUS_ENDED);
    }
}
