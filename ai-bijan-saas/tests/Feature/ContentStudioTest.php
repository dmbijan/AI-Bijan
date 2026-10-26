<?php

namespace Tests\Feature;

use App\Modules\ContentStudio\Models\ContentBrandProfile;
use App\Modules\ContentStudio\Models\ContentChat;
use App\Modules\ContentStudio\Models\ContentChatMessage;
use App\Modules\ContentStudio\Models\ContentComment;
use App\Modules\ContentStudio\Models\ContentIdea;
use App\Modules\ContentStudio\Models\ContentModerationRule;
use App\Modules\ContentStudio\Models\ContentPiece;
use App\Modules\ContentStudio\Models\ContentPlatform;
use App\Modules\ContentStudio\Models\ContentPublication;
use App\Modules\ContentStudio\Services\ContentStudioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Content Studio & Omnichannel Publishing (BAB 86 / Idea #21).
 */
class ContentStudioTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@content.my', 'Content Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function platform(array $ganti = []): ContentPlatform
    {
        return ContentPlatform::create(array_merge([
            'tenant_id' => $this->t->id,
            'platform' => ContentPlatform::INSTAGRAM,
            'display_name' => 'IG Kedai',
            'capabilities' => ['post', 'story', 'comment', 'message'],
            'is_active' => true,
        ], $ganti));
    }

    private function piece(array $ganti = []): ContentPiece
    {
        return ContentPiece::create(array_merge([
            'tenant_id' => $this->t->id,
            'title' => 'Kandungan Ujian',
            'body' => 'Ini kandungan ujian untuk AI BIJAN.',
            'status' => ContentPiece::STATUS_DRAFT,
        ], $ganti));
    }

    // -------------------------------------------------------------------
    // Platform
    // -------------------------------------------------------------------

    public function test_cipta_platform(): void
    {
        $p = $this->platform();

        $this->assertNotNull($p->id);
        $this->assertTrue($p->is_active);
        $this->assertTrue($p->boleh('post'));
        $this->assertFalse($p->boleh('live'));
    }

    public function test_credentials_dienkripsi(): void
    {
        $p = ContentPlatform::create([
            'tenant_id' => $this->t->id,
            'platform' => ContentPlatform::FACEBOOK,
            'credentials' => ['access_token' => 'RAHSIA123'],
        ]);

        // Nilai mentah dalam DB tidak boleh plaintext
        $raw = \Illuminate\Support\Facades\DB::table('content_platforms')
            ->where('id', $p->id)
            ->value('credentials');

        $this->assertStringNotContainsString('RAHSIA123', (string) $raw);

        // Tetapi model boleh baca
        $this->assertSame('RAHSIA123', $p->fresh()->credentials['access_token']);
    }

    // -------------------------------------------------------------------
    // Brand voice
    // -------------------------------------------------------------------

    public function test_brand_profile_system_prompt(): void
    {
        $profil = ContentBrandProfile::create([
            'tenant_id' => $this->t->id,
            'name' => 'Kedai Saya',
            'tone' => 'mesra dan profesional',
            'key_phrases' => ['kod', 'luarbiasa'],
            'banned_words' => ['murah sangat'],
            'default_cta' => 'Hubungi kami hari ini',
        ]);

        $prompt = $profil->systemPrompt();

        $this->assertStringContainsString('mesra dan profesional', $prompt);
        $this->assertStringContainsString('kod, luarbiasa', $prompt);
        $this->assertStringContainsString('murah sangat', $prompt);
    }

    // -------------------------------------------------------------------
    // Jana kandungan
    // -------------------------------------------------------------------

    public function test_jana_kandungan_fallback_tanpa_gateway_live(): void
    {
        $s = app(ContentStudioService::class);

        $piece = $s->janaKandungan($this->t->id, [
            'topic' => 'Promosi Bulan Ini',
            'cta' => 'Beli sekarang',
        ]);

        $this->assertNotNull($piece->id);
        $this->assertSame(ContentPiece::STATUS_DRAFT, $piece->status);
        $this->assertNotEmpty($piece->body);
    }

    public function test_jana_kandungan_ambil_hashtag(): void
    {
        $s = app(ContentStudioService::class);

        $piece = $s->janaKandungan($this->t->id, [
            'topic' => 'Promosi',
            'hashtags' => ['promosi', '#diskaun'],
        ]);

        $this->assertContains('#promosi', $piece->hashtags);
        $this->assertContains('#diskaun', $piece->hashtags);
    }

    public function test_jana_kandungan_menandakan_idea_digunakan(): void
    {
        $s = app(ContentStudioService::class);

        $idea = ContentIdea::create([
            'tenant_id' => $this->t->id,
            'title' => 'Idea Content',
        ]);

        $item = $s->janaKandungan($this->t->id, [
            'topic' => 'X',
            'idea_id' => $idea->id,
        ]);

        $this->assertSame(ContentIdea::STATUS_USED, $idea->fresh()->status);
        $this->assertSame($item->id, $idea->fresh()->used_in_piece_id);
    }

    // -------------------------------------------------------------------
    // Adaptasi platform
    // -------------------------------------------------------------------

    public function test_adaptasi_potong_untuk_x(): void
    {
        $s = app(ContentStudioService::class);
        $piece = $this->piece(['body' => str_repeat('Perkataan panjang. ', 50)]);

        $hasil = $s->adaptasiUntukPlatform($piece, ContentPlatform::X);

        $this->assertTrue($hasil['dipotong']);
        $this->assertLessThanOrEqual(280, $hasil['panjang']);
    }

    public function test_adaptasi_tambah_hashtag_instagram(): void
    {
        $s = app(ContentStudioService::class);
        $piece = $this->piece(['hashtags' => ['#promosi', '#diskaun']]);

        $hasil = $s->adaptasiUntukPlatform($piece, ContentPlatform::INSTAGRAM);

        $this->assertStringContainsString('#promosi', $hasil['body']);
    }

    public function test_adaptasi_tidak_potong_bila_pendek(): void
    {
        $s = app(ContentStudioService::class);
        $piece = $this->piece(['body' => 'Pendek sahaja.']);

        $hasil = $s->adaptasiUntukPlatform($piece, ContentPlatform::FACEBOOK);

        $this->assertFalse($hasil['dipotong']);
    }

    // -------------------------------------------------------------------
    // Jadwal & terbit
    // -------------------------------------------------------------------

    public function test_jadual_penerbitan(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();
        $piece = $this->piece();

        $pubs = $s->jadualTerbit($this->t->id, $piece->id, [$p->id]);

        $this->assertCount(1, $pubs);
        $this->assertSame(ContentPublication::STATUS_QUEUED, $pubs[0]->status);
        $this->assertSame(ContentPiece::STATUS_APPROVED, $piece->fresh()->status);
    }

    public function test_jadual_dengan_tarikh_future_menjadi_scheduled(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();
        $piece = $this->piece();

        $s->jadualTerbit($this->t->id, $piece->id, [$p->id], now()->addDay()->toDateTimeString());

        $this->assertSame(ContentPiece::STATUS_SCHEDULED, $piece->fresh()->status);
    }

    public function test_terbit_queued_dilewati_tanpa_auto_publish(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform(['auto_publish' => false]);
        $piece = $this->piece();

        $s->jadualTerbit($this->t->id, $piece->id, [$p->id]);
        $hasil = $s->terbitQueued($this->t->id);

        $this->assertSame(0, $hasil['berjaya']);
        $this->assertSame(
            ContentPublication::STATUS_SKIPPED,
            ContentPublication::where('content_piece_id', $piece->id)->value('status')
        );
    }

    public function test_terbit_queued_berjaya_dengan_auto_publish(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform(['auto_publish' => true]);
        $piece = $this->piece();

        $s->jadualTerbit($this->t->id, $piece->id, [$p->id]);
        $hasil = $s->terbitQueued($this->t->id);

        $this->assertSame(1, $hasil['berjaya']);
        $this->assertSame(ContentPiece::STATUS_PUBLISHED, $piece->fresh()->status);
    }

    public function test_terbit_hormati_had_harian(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform(['auto_publish' => true, 'max_daily_posts' => 1]);
        $piece1 = $this->piece(['title' => 'A']);
        $piece2 = $this->piece(['title' => 'B']);

        $s->jadualTerbit($this->t->id, $piece1->id, [$p->id]);
        $s->terbitQueued($this->t->id);

        $s->jadualTerbit($this->t->id, $piece2->id, [$p->id]);
        $hasil = $s->terbitQueued($this->t->id);

        $this->assertSame(0, $hasil['berjaya']);
    }

    // -------------------------------------------------------------------
    // Komen
    // -------------------------------------------------------------------

    public function test_ingest_komen(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $komen = $s->ingestKomen($this->t->id, $p->id, [
            'external_id' => 'CMT-001',
            'author_name' => 'Ahmad',
            'body' => 'Barang bagus! Terima kasih.',
        ]);

        $this->assertNotNull($komen->id);
        $this->assertSame(ContentComment::SENTIMENT_POSITIVE, $komen->sentiment);
    }

    public function test_komen_negatif_dikesan(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $komen = $s->ingestKomen($this->t->id, $p->id, [
            'external_id' => 'CMT-002',
            'body' => 'Barang rosak dan lambat sampai. Saya kecewa.',
        ]);

        $this->assertSame(ContentComment::SENTIMENT_NEGATIVE, $komen->sentiment);
    }

    public function test_ingest_komen_idempotent(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $data = ['external_id' => 'CMT-DUP', 'body' => 'Bagus'];

        $s->ingestKomen($this->t->id, $p->id, $data);
        $s->ingestKomen($this->t->id, $p->id, $data);

        $this->assertSame(1, ContentComment::where('content_platform_id', $p->id)->count());
    }

    public function test_moderation_rule_menyembunyi_komen(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        ContentModerationRule::create([
            'tenant_id' => $this->t->id,
            'name' => 'Kata Kotor',
            'type' => ContentModerationRule::TYPE_KEYWORD,
            'pattern' => 'bodoh',
            'action' => ContentModerationRule::ACTION_HIDE,
            'is_active' => true,
        ]);

        $komen = $s->ingestKomen($this->t->id, $p->id, [
            'external_id' => 'CMT-003',
            'body' => 'Kedai bodoh, tipu pelanggan.',
        ]);

        $this->assertSame(ContentComment::STATUS_HIDDEN, $komen->status);
    }

    public function test_balas_komen(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $komen = $s->ingestKomen($this->t->id, $p->id, [
            'external_id' => 'CMT-004',
            'body' => 'Bagus',
        ]);

        $balas = $s->balasKomen($this->t->id, $komen->id, 'Terima kasih!');

        $this->assertSame(ContentComment::STATUS_REPLIED, $balas->status);
        $this->assertSame('Terima kasih!', $balas->reply_body);
        $this->assertNotNull($balas->replied_at);
    }

    public function test_jana_balasan_ai_fallback(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $komen = $s->ingestKomen($this->t->id, $p->id, [
            'external_id' => 'CMT-005',
            'body' => 'Produk lambat dan rosak. Saya kecewa.',
        ]);

        $hasil = $s->janaBalasanAI($this->t->id, $komen);

        $this->assertArrayHasKey('balasan', $hasil);
        $this->assertNotEmpty($hasil['balasan']);
    }

    // -------------------------------------------------------------------
    // Chat
    // -------------------------------------------------------------------

    public function test_ingest_chat(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $chat = $s->ingestChat($this->t->id, $p->id, [
            'external_conversation_id' => 'CONV-1',
            'customer_name' => 'Siti',
            'body' => 'Bila barang sampai?',
        ]);

        $this->assertNotNull($chat->id);
        $this->assertSame(1, $chat->message_count);
        $this->assertSame(1, $chat->unread_count);
        $this->assertSame(1, ContentChatMessage::where('content_chat_id', $chat->id)->count());
    }

    public function test_chat_refund_flag_perlu_manusia(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $chat = $s->ingestChat($this->t->id, $p->id, [
            'external_conversation_id' => 'CONV-2',
            'body' => 'Saya nak refund sekarang',
        ]);

        $this->assertTrue($chat->requires_human);
        $this->assertSame(ContentChat::STATUS_PENDING, $chat->status);
    }

    public function test_balas_chat(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform();

        $chat = $s->ingestChat($this->t->id, $p->id, [
            'external_conversation_id' => 'CONV-3',
            'body' => 'Hello',
        ]);

        $hasil = $s->balasChat($this->t->id, $chat->id, 'Hai! Ada apa boleh bantu?');

        $this->assertSame(0, $hasil->unread_count);
        $this->assertSame('human', $hasil->last_message_from);
        $this->assertSame(2, ContentChatMessage::where('content_chat_id', $chat->id)->count());
    }

    // -------------------------------------------------------------------
    // Ringkasan
    // -------------------------------------------------------------------

    public function test_ringkasan(): void
    {
        $s = app(ContentStudioService::class);
        $p = $this->platform(['auto_publish' => true]);
        $piece = $this->piece();

        $s->jadualTerbit($this->t->id, $piece->id, [$p->id]);
        $s->terbitQueued($this->t->id);
        $s->ingestKomen($this->t->id, $p->id, ['external_id' => 'C1', 'body' => 'Bagus']);

        $r = $s->ringkasan($this->t->id);

        $this->assertSame(1, $r['kandungan_dijana']);
        $this->assertSame(1, $r['kandungan_diterbit']);
        $this->assertSame(1, $r['komen_diterima']);
        $this->assertSame(1, $r['platform_aktif']);
    }

    public function test_prestasi_platform_kosong(): void
    {
        $s = app(ContentStudioService::class);

        $hasil = $s->prestasiPlatform($this->t->id);

        $this->assertSame(0, $hasil['jumlah_reach']);
        $this->assertSame([], $hasil['platform']);
    }

    // -------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------

    public function test_piece_tenant_lain_tidak_nampak(): void
    {
        $piece = $this->piece();

        [$u2, $t2] = $this->daftar('lawan@content.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(ContentPiece::find($piece->id));
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_senarai_platform(): void
    {
        $this->platform();

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/content/platforms')
            ->assertOk();

        $this->assertCount(1, $res->json('data'));
        // Credentials tidak boleh didedahkan
        $this->assertArrayNotHasKey('credentials', $res->json('data.0'));
    }

    public function test_api_cipta_platform(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/content/platforms', [
                'platform' => 'tiktok',
                'display_name' => 'TikTok Kedai',
                'capabilities' => ['post', 'video'],
            ])
            ->assertCreated();
    }

    public function test_api_jana_kandungan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/content/generate', [
                'topic' => 'Promosi Merdeka',
                'content_type' => 'post',
            ])
            ->assertCreated();
    }

    public function test_api_jana_tanpa_input_422(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/content/generate', [])
            ->assertStatus(422);
    }

    public function test_api_jadual_penerbitan(): void
    {
        $p = $this->platform();
        $piece = $this->piece();

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/content/pieces/{$piece->id}/schedule", [
                'platform_ids' => [$p->id],
            ])
            ->assertCreated();
    }

    public function test_api_ingest_komen(): void
    {
        $p = $this->platform();

        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/content/comments/ingest', [
                'platform_id' => $p->id,
                'external_id' => 'API-CMT-1',
                'body' => 'Bagus!',
            ])
            ->assertCreated();
    }

    public function test_api_balas_komen(): void
    {
        $p = $this->platform();
        $komen = app(ContentStudioService::class)->ingestKomen($this->t->id, $p->id, [
            'external_id' => 'API-CMT-2',
            'body' => 'Bagus',
        ]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/content/comments/{$komen->id}/reply", ['reply' => 'Terima kasih'])
            ->assertOk();
    }

    public function test_api_ringkasan(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/content/summary')
            ->assertOk();
    }

    public function test_api_moderation_rule(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/content/moderation-rules', [
                'name' => 'Anti Spam',
                'type' => 'keyword',
                'pattern' => 'promosi percuma',
                'action' => 'hide',
            ])
            ->assertCreated();
    }
}
