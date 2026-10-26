<?php

namespace Tests\Feature;

use App\Modules\MediaStudio\Models\MediaAsset;
use App\Modules\MediaStudio\Models\MediaJob;
use App\Modules\MediaStudio\Models\MediaPersona;
use App\Modules\MediaStudio\Services\ConsentGateService;
use App\Modules\MediaStudio\Services\MediaModerationService;
use App\Modules\MediaStudio\Services\MediaRetentionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Media Studio (BAB 82 / Fasa 4).
 *
 * Fokus: consent gate persona, moderasi, retensi, API.
 */
class MediaStudioTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@media.my', 'Media Sdn Bhd');

        Storage::fake('media');
    }

    private function persona(array $ganti = []): MediaPersona
    {
        $this->dalamTenant($this->t);

        return MediaPersona::create(array_merge([
            'name' => 'Avatar Eksekutif',
            'avatar_ref' => 'avatar_123',
            'voice_ref' => 'voice_456',
            'language' => 'ms',
            'verification_status' => 'verified',
            'verified_at' => now(),
            'is_active' => true,
        ], $ganti));
    }

    // ---------------------------------------------------------------------
    // Consent gate persona
    // ---------------------------------------------------------------------

    public function test_persona_belum_disahkan_tidak_boleh_guna(): void
    {
        $persona = $this->persona(['verification_status' => 'pending', 'verified_at' => null]);

        $semak = app(ConsentGateService::class)->semakPersona($persona);

        $this->assertFalse($semak['ok']);
        $this->assertSame('persona_not_verified', $semak['reason']);
        $this->assertFalse($persona->bolehGuna());
    }

    public function test_persona_tanpa_avatar_tidak_boleh_guna(): void
    {
        $persona = $this->persona(['avatar_ref' => null]);

        $semak = app(ConsentGateService::class)->semakPersona($persona);

        $this->assertFalse($semak['ok']);
        $this->assertSame('persona_missing_avatar', $semak['reason']);
    }

    public function test_persona_disahkan_boleh_guna(): void
    {
        $persona = $this->persona();

        $semak = app(ConsentGateService::class)->semakPersona($persona);

        $this->assertTrue($semak['ok']);
        $this->assertNull($semak['reason']);
        $this->assertTrue($persona->bolehGuna());
    }

    public function test_persona_tidak_aktif_tidak_boleh_guna(): void
    {
        $persona = $this->persona(['is_active' => false]);

        $semak = app(ConsentGateService::class)->semakPersona($persona);

        $this->assertFalse($semak['ok']);
        $this->assertSame('persona_not_active', $semak['reason']);
    }

    public function test_inferens_biometrik_tanpa_consent_ditolak(): void
    {
        $this->assertFalse(app(ConsentGateService::class)->bolehInferBiometrik($this->t->id, null));
    }

    // ---------------------------------------------------------------------
    // Moderasi
    // ---------------------------------------------------------------------

    public function test_moderasi_tolak_keganasan(): void
    {
        $hasil = app(MediaModerationService::class)->semakTeks('Mari kita bunuh mereka semua');

        $this->assertFalse($hasil['passed']);
        $this->assertNotEmpty($hasil['violations']);
    }

    public function test_moderasi_tolak_kandungan_politik(): void
    {
        $hasil = app(MediaModerationService::class)->semakTeks('Undi untuk parti politik ini');

        $this->assertFalse($hasil['passed']);
    }

    public function test_moderasi_lulus_teks_biasa(): void
    {
        $hasil = app(MediaModerationService::class)->semakTeks('Mesy meeting excited untuk dibincangkan');

        $this->assertTrue($hasil['passed']);
        $this->assertEmpty($hasil['violations']);
    }

    public function test_watermark_ditambah_ke_skrip(): void
    {
        $skrip = app(MediaModerationService::class)->denganWatermark('Helo dunia');

        $this->assertStringContainsString('AI-generated', $skrip);
        $this->assertStringContainsString('Helo dunia', $skrip);
    }

    // ---------------------------------------------------------------------
    // Retensi
    // ---------------------------------------------------------------------

    public function test_purge_padam_aset_luput_tempoh(): void
    {
        $this->dalamTenant($this->t);

        Storage::disk('media')->put('1/2026/10/luput.mp4', 'kandungan');

        $asset = MediaAsset::create([
            'kind' => 'video',
            'storage_disk' => 'media',
            'storage_path' => '1/2026/10/luput.mp4',
            'mime' => 'video/mp4',
            'size_bytes' => 100,
            'source' => 'upload',
            'retention_expires_at' => now()->subDay(),
        ]);

        $hasil = app(MediaRetentionService::class)->purgeExpired();

        $this->assertSame(1, $hasil['purged']);
        $this->assertNotNull($asset->fresh()->deleted_at);
        Storage::disk('media')->assertMissing('1/2026/10/luput.mp4');
    }

    public function test_purge_baki_aset_yang_belum_luput(): void
    {
        $this->dalamTenant($this->t);

        Storage::disk('media')->put('1/2026/10/masih.mp4', 'kandungan');

        MediaAsset::create([
            'kind' => 'video',
            'storage_disk' => 'media',
            'storage_path' => '1/2026/10/masih.mp4',
            'mime' => 'video/mp4',
            'size_bytes' => 100,
            'source' => 'upload',
            'retention_expires_at' => now()->addDays(30),
        ]);

        $hasil = app(MediaRetentionService::class)->purgeExpired();

        $this->assertSame(0, $hasil['purged']);
        Storage::disk('media')->assertExists('1/2026/10/masih.mp4');
    }

    // ---------------------------------------------------------------------
    // API
    // ---------------------------------------------------------------------

    public function test_api_muat_naik_fail(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/media/upload', [
                'file' => \Illuminate\Http\UploadedFile::fake()->image('gambar.jpg'),
                'kind' => 'image',
            ])
            ->assertCreated()
            ->assertJsonPath('data.kind', 'image')
            ->assertJsonPath('data.source', 'upload');
    }

    public function test_api_senarai_aset(): void
    {
        $this->dalamTenant($this->t);

        MediaAsset::create([
            'kind' => 'video',
            'storage_disk' => 'media',
            'storage_path' => '1/x.mp4',
            'mime' => 'video/mp4',
            'source' => 'upload',
        ]);

        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/media')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_api_jana_video_tolak_persona_tidak_disahkan(): void
    {
        $persona = $this->persona(['verification_status' => 'pending', 'verified_at' => null]);

        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/media/generate-video', [
                'script' => 'Helo, ini pengenalan kami.',
                'persona_id' => $persona->id,
            ])
            ->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_api_jana_video_gagal_moderasi(): void
    {
        $persona = $this->persona();

        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/media/generate-video', [
                'script' => 'Kita akan bunuh pesaing kita.',
                'persona_id' => $persona->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_api_daftar_persona(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/media/personas', [
                'name' => 'Avatar Baru',
                'language' => 'ms',
            ])
            ->assertCreated()
            ->assertJsonPath('data.verification_status', 'pending')
            ->assertJsonPath('data.is_active', true);
    }

    public function test_api_sahkan_persona(): void
    {
        $persona = $this->persona(['verification_status' => 'pending', 'verified_at' => null]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/media/personas/{$persona->id}/verify", ['approved' => true])
            ->assertOk()
            ->assertJsonPath('data.verification_status', 'verified');
    }

    public function test_api_tenant_lain_tidak_nampak_aset(): void
    {
        $this->dalamTenant($this->t);

        MediaAsset::create([
            'kind' => 'video',
            'storage_disk' => 'media',
            'storage_path' => '1/x.mp4',
            'mime' => 'video/mp4',
            'source' => 'upload',
        ]);

        [$u2, $t2] = $this->daftar('pemilik2@media.my', 'Media Kedua');

        $this->sebagai($u2, $t2)
            ->getJson('/api/v1/media')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}
