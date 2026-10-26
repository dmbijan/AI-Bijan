<?php

namespace Tests\Feature;

use App\Modules\AIGateway\Models\AiModel;
use App\Modules\AIGateway\Services\GatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Ujian integrasi AI Gateway LIVE (T-07).
 *
 * Ujian ini DILANGKAU secara automatik jika AI_GATEWAY_LIVE bukan 'true'
 * atau AI_GATEWAY_KEY tiada — supaya CI kekal hijau tanpa kunci sebenar.
 *
 * Jalankan manual:
 *   pwsh -File skrip/tetapan_ai.ps1 -Aktifkan
 *   php artisan config:clear
 *   php artisan test --filter=AiGatewayLiveTest
 */
class AiGatewayLiveTest extends TestCase
{
    use AksesTenant, RefreshDatabase;

    protected $seed = true;

    protected function setUp(): void
    {
        parent::setUp();

        if (! filter_var(env('AI_GATEWAY_LIVE', false), FILTER_VALIDATE_BOOL)) {
            $this->markTestSkipped('AI_GATEWAY_LIVE bukan true — ujian integrasi dilangkau.');
        }
        if (blank(env('AI_GATEWAY_KEY'))) {
            $this->markTestSkipped('AI_GATEWAY_KEY tiada — ujian integrasi dilangkau.');
        }

        $this->setUpAkses();
    }

    public function test_panggilan_live_memulangkan_kandungan_sebenar(): void
    {
        [$u, $t] = $this->daftar('ai@kedai-a.my', 'Kedai AI');
        $this->dalamTenant($t);

        // Pastikan ada model aktif dalam katalog.
        $model = AiModel::where('is_active', true)->first();
        $this->assertNotNull($model, 'Katalog model kosong — jalankan AiModelCatalogSeeder.');

        $hasil = app(GatewayService::class)->call($t->id, [
            ['role' => 'user', 'content' => 'Balas dengan satu perkataan: OK'],
        ], 'default', ['model' => $model->provider_model_id, 'user_id' => $u->id]);

        $this->assertTrue($hasil['ok'] ?? false, 'Panggilan gagal: '.json_encode($hasil['error'] ?? $hasil));
        $this->assertNotEmpty($hasil['content'] ?? '', 'Kandungan kosong.');
        $this->assertStringNotContainsString('[SIMULASI]', $hasil['content'], 'Masih dalam mod simulasi.');
        $this->assertNotEmpty($hasil['usage'] ?? [], 'Metadata penggunaan tiada — semak ledger.');
    }

    public function test_penggunaan_token_direkod_dalam_ledger(): void
    {
        [$u, $t] = $this->daftar('ai2@kedai-a.my', 'Kedai AI 2');
        $this->dalamTenant($t);

        $model = AiModel::where('is_active', true)->firstOrFail();
        app(GatewayService::class)->call($t->id, [
            ['role' => 'user', 'content' => 'Sebut nombor 7 sahaja.'],
        ], 'default', ['model' => $model->provider_model_id, 'user_id' => $u->id]);

        $this->assertDatabaseCount('ai_usage_events', 1);
    }

    public function test_kunci_provider_tidak_didedahkan_dalam_respons(): void
    {
        [$u, $t] = $this->daftar('ai3@kedai-a.my', 'Kedai AI 3');
        $this->dalamTenant($t);

        $model = AiModel::where('is_active', true)->firstOrFail();
        $hasil = app(GatewayService::class)->call($t->id, [
            ['role' => 'user', 'content' => 'Hi'],
        ], 'default', ['model' => $model->provider_model_id, 'user_id' => $u->id]);

        $json = json_encode($hasil);
        $this->assertStringNotContainsString(env('AI_GATEWAY_KEY'), $json);
        $this->assertStringNotContainsString('asai_sk_', $json);
    }
}
