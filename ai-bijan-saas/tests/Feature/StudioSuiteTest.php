<?php

namespace Tests\Feature;

use App\Modules\MediaEditor\Models\MediaProject;
use App\Modules\MediaEditor\Models\MediaRenderJob;
use App\Modules\MediaEditor\Models\MediaTemplate;
use App\Modules\MediaEditor\Services\MediaEditorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Media Editor, Live Support & Community (BAB 86 / Idea #27, #28, #29).
 */
class StudioSuiteTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@studio.my', 'Studio Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    // ===================================================================
    // MEDIA EDITOR
    // ===================================================================

    public function test_cipta_projek_media(): void
    {
        $s = app(MediaEditorService::class);

        $projek = $s->ciptaProjek($this->t->id, [
            'name' => 'Post Instagram',
            'project_type' => MediaProject::TYPE_IMAGE,
        ]);

        $this->assertNotNull($projek->id);
        $this->assertSame(MediaProject::STATUS_DRAFT, $projek->status);
        $this->assertSame(0, $projek->jumlahLapisan());
        $this->assertSame(1080, $projek->canvas['width']);
    }

    public function test_sahkan_lapisan_sah(): void
    {
        $s = app(MediaEditorService::class);

        $hasil = $s->sahkanLapisan([
            ['type' => 'text', 'x' => 10, 'y' => 20, 'text' => 'Hello'],
            ['type' => 'image', 'x' => 0, 'y' => 0, 'src' => 'https://x/my.png'],
        ]);

        $this->assertTrue($hasil['valid']);
    }

    public function test_sahkan_lapisan_jenis_tidak_sah(): void
    {
        $s = app(MediaEditorService::class);

        $hasil = $s->sahkanLapisan([
            ['type' => 'hologram', 'x' => 0, 'y' => 0],
        ]);

        $this->assertFalse($hasil['valid']);
    }

    public function test_sahkan_lapisan_teks_tanpa_text(): void
    {
        $s = app(MediaEditorService::class);

        $hasil = $s->sahkanLapisan([
            ['type' => 'text', 'x' => 0, 'y' => 0],
        ]);

        $this->assertFalse($hasil['valid']);
    }

    public function test_sahkan_lapisan_imej_tanpa_src(): void
    {
        $s = app(MediaEditorService::class);

        $hasil = $s->sahkanLapisan([
            ['type' => 'image', 'x' => 0, 'y' => 0],
        ]);

        $this->assertFalse($hasil['valid']);
    }

    public function test_tambah_lapisan(): void
    {
        $s = app(MediaEditorService::class);
        $projek = $s->ciptaProjek($this->t->id, ['name' => 'P']);

        $hasil = $s->tambahLapisan($this->t->id, $projek->id, [
            'type' => 'text',
            'x' => 100,
            'y' => 200,
            'text' => 'Tajuk',
        ]);

        $this->assertSame(1, $hasil->jumlahLapisan());
        $this->assertSame(MediaProject::STATUS_EDITING, $hasil->status);
    }

    public function test_buang_lapisan(): void
    {
        $s = app(MediaEditorService::class);
        $projek = $s->ciptaProjek($this->t->id, ['name' => 'P']);
        $s->tambahLapisan($this->t->id, $projek->id, ['type' => 'text', 'x' => 0, 'y' => 0, 'text' => 'A']);
        $s->tambahLapisan($this->t->id, $projek->id, ['type' => 'text', 'x' => 0, 'y' => 0, 'text' => 'B']);

        $hasil = $s->buangLapisan($this->t->id, $projek->id, 0);

        $this->assertSame(1, $hasil->jumlahLapisan());
        $this->assertSame('B', $hasil->layers[0]['text']);
    }

    public function test_buang_lapisan_index_tidak_wujud(): void
    {
        $s = app(MediaEditorService::class);
        $projek = $s->ciptaProjek($this->t->id, ['name' => 'P']);

        $this->expectException(\InvalidArgumentException::class);
        $s->buangLapisan($this->t->id, $projek->id, 5);
    }

    public function test_render_projek(): void
    {
        $s = app(MediaEditorService::class);
        $projek = $s->ciptaProjek($this->t->id, ['name' => 'P']);

        $job = $s->janaRender($this->t->id, $projek->id);

        $this->assertSame(MediaRenderJob::STATUS_COMPLETED, $job->status);
        $this->assertNotNull($job->output_url);
        $this->assertSame(MediaProject::STATUS_READY, $projek->fresh()->status);
        $this->assertTrue($projek->fresh()->sedia());
    }

    public function test_render_format_video_untuk_projek_video(): void
    {
        $s = app(MediaEditorService::class);
        $projek = $s->ciptaProjek($this->t->id, [
            'name' => 'Video',
            'project_type' => MediaProject::TYPE_VIDEO,
        ]);

        $job = $s->janaRender($this->t->id, $projek->id);

        $this->assertSame('mp4', $job->output_format);
    }

    public function test_guna_template_media(): void
    {
        $s = app(MediaEditorService::class);

        $tmpl = MediaTemplate::create([
            'key' => 'tpl_ig',
            'name' => 'Template IG',
            'project_type' => 'image',
            'definition' => [
                'canvas' => ['width' => 1080, 'height' => 1080, 'background' => '#000'],
                'layers' => [['type' => 'text', 'x' => 0, 'y' => 0, 'text' => 'Promo']],
            ],
            'is_active' => true,
        ]);

        $projek = $s->gunaTemplate($this->t->id, $tmpl->id);

        $this->assertSame(1, $projek->jumlahLapisan());
        $this->assertSame(1, $tmpl->fresh()->use_count);
    }

    public function test_ringkasan_media(): void
    {
        $s = app(MediaEditorService::class);
        $projek = $s->ciptaProjek($this->t->id, ['name' => 'P']);
        $s->janaRender($this->t->id, $projek->id);

        $r = $s->ringkasan($this->t->id);

        $this->assertSame(1, $r['jumlah_projek']);
        $this->assertSame(1, $r['siap']);
        $this->assertSame(1, $r['render_berjaya']);
    }

    public function test_api_cipta_projek_media(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/media-editor/projects', [
                'name' => 'Projek API',
                'project_type' => 'image',
            ])
            ->assertCreated();
    }

    public function test_api_tambah_lapisan(): void
    {
        $projek = app(MediaEditorService::class)->ciptaProjek($this->t->id, ['name' => 'P']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/media-editor/projects/{$projek->id}/layers", [
                'type' => 'text',
                'x' => 10,
                'y' => 20,
                'text' => 'Hello',
            ])
            ->assertCreated();
    }

    public function test_api_render(): void
    {
        $projek = app(MediaEditorService::class)->ciptaProjek($this->t->id, ['name' => 'P']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/media-editor/projects/{$projek->id}/render")
            ->assertCreated();
    }

    public function test_api_media_summary(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/media-editor/summary')
            ->assertOk();
    }

    // ===================================================================
    // LIVE SUPPORT
    // ===================================================================

    public function test_terima_panggilan(): void
    {
        $s = app(\App\Modules\LiveSupport\Services\LiveSupportService::class);

        $channel = \App\Modules\LiveSupport\Models\LiveSupportChannel::create([
            'tenant_id' => $this->t->id,
            'name' => 'Talian Utama',
            'ai_enabled' => true,
        ]);

        $call = $s->terimaPanggilan($this->t->id, $channel->id, [
            'caller_number' => '+60123456789',
            'caller_name' => 'Ahmad',
        ]);

        $this->assertSame(\App\Modules\LiveSupport\Models\LiveSupportCall::STATUS_ANSWERED, $call->status);
        $this->assertSame('ai', $call->handler_type);
    }

    public function test_terima_panggilan_gagal_bila_ai_tidak_aktif(): void
    {
        $s = app(\App\Modules\LiveSupport\Services\LiveSupportService::class);

        $channel = \App\Modules\LiveSupport\Models\LiveSupportChannel::create([
            'tenant_id' => $this->t->id,
            'name' => 'Talian',
            'ai_enabled' => false,
        ]);

        $this->expectException(\RuntimeException::class);
        $s->terimaPanggilan($this->t->id, $channel->id, []);
    }

    public function test_transkrip_dan_sentiment(): void
    {
        $s = app(\App\Modules\LiveSupport\Services\LiveSupportService::class);

        $channel = \App\Modules\LiveSupport\Models\LiveSupportChannel::create([
            'tenant_id' => $this->t->id,
            'name' => 'Talian',
        ]);

        $call = $s->terimaPanggilan($this->t->id, $channel->id, []);

        $s->tambahTranskrip($this->t->id, $call->id, [
            'speaker' => 'caller',
            'text' => 'Terima kasih, servis bagus',
        ]);

        $this->assertSame('positive', $call->fresh()->sentiment);
    }

    public function test_eskalasi_kerana_kata_kunci_kritikal(): void
    {
        $s = app(\App\Modules\LiveSupport\Services\LiveSupportService::class);

        $channel = \App\Modules\LiveSupport\Models\LiveSupportChannel::create([
            'tenant_id' => $this->t->id,
            'name' => 'Talian',
            'human_fallback' => true,
        ]);

        $call = $s->terimaPanggilan($this->t->id, $channel->id, []);

        $s->tambahTranskrip($this->t->id, $call->id, [
            'speaker' => 'caller',
            'text' => 'Saya nak refund sekarang',
        ]);

        $fresh = $call->fresh();
        $this->assertSame('human', $fresh->handler_type);
        $this->assertNotNull($fresh->escalation_reason);
    }

    public function test_tamat_panggilan_jana_ringkasan(): void
    {
        $s = app(\App\Modules\LiveSupport\Services\LiveSupportService::class);

        $channel = \App\Modules\LiveSupport\Models\LiveSupportChannel::create([
            'tenant_id' => $this->t->id,
            'name' => 'Talian',
        ]);

        $call = $s->terimaPanggilan($this->t->id, $channel->id, []);
        $s->tambahTranskrip($this->t->id, $call->id, ['speaker' => 'caller', 'text' => 'Hello']);

        $selesai = $s->tamatPanggilan($this->t->id, $call->id);

        $this->assertSame(\App\Modules\LiveSupport\Models\LiveSupportCall::STATUS_COMPLETED, $selesai->status);
        $this->assertNotNull($selesai->ai_summary);
        $this->assertNotNull($selesai->ended_at);
    }

    public function test_ringkasan_live_support(): void
    {
        $s = app(\App\Modules\LiveSupport\Services\LiveSupportService::class);

        $channel = \App\Modules\LiveSupport\Models\LiveSupportChannel::create([
            'tenant_id' => $this->t->id,
            'name' => 'Talian',
        ]);

        $s->terimaPanggilan($this->t->id, $channel->id, []);

        $r = $s->ringkasan($this->t->id);

        $this->assertSame(1, $r['jumlah_panggilan']);
        $this->assertSame(1, $r['dijawab_ai']);
        $this->assertSame(1, $r['saluran_aktif']);
    }

    public function test_api_cipta_saluran(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/live-support/channels', [
                'name' => 'Talian API',
                'channel_type' => 'voice',
            ])
            ->assertCreated();
    }

    public function test_api_terima_panggilan(): void
    {
        $channel = \App\Modules\LiveSupport\Models\LiveSupportChannel::create([
            'tenant_id' => $this->t->id,
            'name' => 'Talian',
        ]);

        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/live-support/calls', [
                'channel_id' => $channel->id,
                'caller_number' => '+60111111111',
            ])
            ->assertCreated();
    }

    public function test_api_live_support_summary(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/live-support/summary')
            ->assertOk();
    }

    // ===================================================================
    // COMMUNITY
    // ===================================================================

    public function test_cipta_ruang_komuniti(): void
    {
        $s = app(\App\Modules\Community\Services\CommunityService::class);

        $ruang = $s->ciptaRuang([
            'name' => 'Showcase AI BIJAN',
            'space_type' => 'showcase',
            'visibility' => 'public',
        ]);

        $this->assertNotNull($ruang->id);
        $this->assertSame('showcase-ai-bijan', $ruang->slug);
        $this->assertTrue($ruang->awam());
    }

    public function test_slug_unik(): void
    {
        $s = app(\App\Modules\Community\Services\CommunityService::class);

        $a = $s->ciptaRuang(['name' => 'Sama']);
        $b = $s->ciptaRuang(['name' => 'Sama']);

        $this->assertNotSame($a->slug, $b->slug);
    }

    public function test_cipta_pos_dan_balas(): void
    {
        $s = app(\App\Modules\Community\Services\CommunityService::class);

        $ruang = $s->ciptaRuang(['name' => 'Help']);
        $pos = $s->ciptaPos($ruang->id, [
            'title' => 'Bagaimana nak setup COD?',
            'body' => 'Saya perlukan bantuan.',
            'post_type' => 'question',
        ], $this->t->id);

        $this->assertSame(1, $ruang->fresh()->post_count);

        $balasan = $s->balas($pos->id, 'Pergi ke tetapan COD.', $this->t->id);

        $this->assertNotNull($balasan->id);
        $this->assertSame(1, $pos->fresh()->reply_count);
    }

    public function test_terima_balasan(): void
    {
        $s = app(\App\Modules\Community\Services\CommunityService::class);

        $ruang = $s->ciptaRuang(['name' => 'Help']);
        $pos = $s->ciptaPos($ruang->id, ['title' => 'Soalan']);
        $balasan = $s->balas($pos->id, 'Jawapan betul');

        $hasil = $s->terimaBalasan($pos->id, $balasan->id);

        $this->assertTrue($hasil->is_solved);
        $this->assertSame($balasan->id, $hasil->accepted_reply_id);
        $this->assertTrue($balasan->fresh()->is_accepted);
    }

    public function test_feed_terbaru(): void
    {
        $s = app(\App\Modules\Community\Services\CommunityService::class);

        $ruang = $s->ciptaRuang(['name' => 'Forum']);
        $s->ciptaPos($ruang->id, ['title' => 'Pos 1']);
        $s->ciptaPos($ruang->id, ['title' => 'Pos 2']);

        $feed = $s->feed();

        $this->assertCount(2, $feed);
    }

    public function test_ringkasan_komuniti(): void
    {
        $s = app(\App\Modules\Community\Services\CommunityService::class);

        $ruang = $s->ciptaRuang(['name' => 'Forum']);
        $s->ciptaPos($ruang->id, ['title' => 'Pos']);

        $r = $s->ringkasan();

        $this->assertSame(1, $r['jumlah_ruang']);
        $this->assertSame(1, $r['jumlah_pos']);
        $this->assertSame(1, $r['ruang_awam']);
    }

    public function test_api_cipta_ruang(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/community/spaces', [
                'name' => 'Ruang API',
                'space_type' => 'forum',
                'visibility' => 'public',
            ])
            ->assertCreated();
    }

    public function test_api_cipta_pos(): void
    {
        $ruang = app(\App\Modules\Community\Services\CommunityService::class)
            ->ciptaRuang(['name' => 'Forum']);

        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/community/posts', [
                'space_id' => $ruang->id,
                'title' => 'Pos API',
                'body' => 'Kandungan',
            ])
            ->assertCreated();
    }

    public function test_api_balas_pos(): void
    {
        $s = app(\App\Modules\Community\Services\CommunityService::class);
        $ruang = $s->ciptaRuang(['name' => 'Forum']);
        $pos = $s->ciptaPos($ruang->id, ['title' => 'Soalan']);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/community/posts/{$pos->id}/reply", ['body' => 'Jawapan'])
            ->assertCreated();
    }

    public function test_api_community_summary(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/community/summary')
            ->assertOk();
    }

    public function test_api_feed(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/community/feed')
            ->assertOk();
    }
}
