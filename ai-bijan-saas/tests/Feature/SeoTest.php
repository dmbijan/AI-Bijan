<?php

namespace Tests\Feature;

use App\Modules\Seo\Models\SeoAuditIssue;
use App\Modules\Seo\Models\SeoKeyword;
use App\Modules\Seo\Models\SeoMetadata;
use App\Modules\Seo\Models\SeoProfile;
use App\Modules\Seo\Models\SeoSuggestion;
use App\Modules\Seo\Services\SeoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * SEO & Metadata Engine (BAB 86 / Idea #20).
 */
class SeoTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@seo.my', 'SEO Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function metadata(array $ganti = []): SeoMetadata
    {
        return SeoMetadata::create(array_merge([
            'tenant_id' => $this->t->id,
            'object_type' => SeoMetadata::TYPE_PRODUCT,
            'object_id' => 1,
            'slug' => 'produk-uji',
            'title' => 'Produk Uji Terbaik Untuk Perniagaan Anda',
            'description' => 'Produk uji yang membantu perniagaan anda berkembang dengan lebih cepat dan cekap setiap hari.',
            'keywords' => 'produk, uji, perniagaan',
        ], $ganti));
    }

    // -------------------------------------------------------------------
    // Profil
    // -------------------------------------------------------------------

    public function test_profil_dicipta_untuk_tenant(): void
    {
        $s = app(SeoService::class);

        $profil = $s->profil($this->t->id);

        $this->assertNotNull($profil->id);
        $this->assertSame($this->t->id, $profil->tenant_id);
        $this->assertTrue($profil->is_indexable);
    }

    public function test_profil_unik_per_tenant(): void
    {
        $s = app(SeoService::class);

        $a = $s->profil($this->t->id);
        $b = $s->profil($this->t->id);

        $this->assertSame($a->id, $b->id);
    }

    public function test_render_tajuk(): void
    {
        $profil = SeoProfile::create([
            'tenant_id' => $this->t->id,
            'site_name' => 'Kedai Saya',
        ]);

        $this->assertSame('Produk A | Kedai Saya', $profil->renderTitle('Produk A'));
    }

    public function test_robots_lalai(): void
    {
        $profil = SeoProfile::create([
            'tenant_id' => $this->t->id,
            'site_name' => 'Kedai',
            'site_url' => 'https://kedai.my',
            'is_indexable' => true,
        ]);

        $robots = $profil->robotsLalai();

        $this->assertStringContainsString('User-agent: *', $robots);
        $this->assertStringContainsString('Sitemap:', $robots);
    }

    public function test_robots_block_semua_bila_tidak_indexable(): void
    {
        $profil = SeoProfile::create([
            'tenant_id' => $this->t->id,
            'is_indexable' => false,
        ]);

        $this->assertStringContainsString('Disallow: /', $profil->robotsLalai());
    }

    // -------------------------------------------------------------------
    // Metadata
    // -------------------------------------------------------------------

    public function test_simpan_metadata(): void
    {
        $s = app(SeoService::class);

        $m = $s->simpanMetadata($this->t->id, SeoMetadata::TYPE_PRODUCT, 1, [
            'title' => 'Tajuk Ujian Yang Baik',
            'description' => 'Penerangan yang cukup panjang untuk memenuhi keperluan minimum enjin carian moden.',
            'keywords' => 'ujian, tajuk',
        ]);

        $this->assertNotNull($m->id);
        $this->assertGreaterThan(0, (float) $m->seo_score);
    }

    public function test_simpan_metadata_idempotent(): void
    {
        $s = app(SeoService::class);

        $s->simpanMetadata($this->t->id, SeoMetadata::TYPE_PAGE, 5, ['title' => 'A']);
        $s->simpanMetadata($this->t->id, SeoMetadata::TYPE_PAGE, 5, ['title' => 'B']);

        $this->assertSame(1, SeoMetadata::where('object_type', SeoMetadata::TYPE_PAGE)->where('object_id', 5)->count());
        $this->assertSame('B', SeoMetadata::where('object_type', SeoMetadata::TYPE_PAGE)->where('object_id', 5)->value('title'));
    }

    public function test_ambil_metadata(): void
    {
        $s = app(SeoService::class);
        $this->metadata();

        $m = $s->metadata($this->t->id, SeoMetadata::TYPE_PRODUCT, 1);

        $this->assertNotNull($m);
        $this->assertSame('produk-uji', $m->slug);
    }

    public function test_meta_tags_dijana(): void
    {
        $m = $this->metadata([
            'canonical_url' => 'https://kedai.my/produk',
            'no_index' => true,
        ]);

        $tags = $m->metaTags();

        $this->assertArrayHasKey('description', $tags);
        $this->assertArrayHasKey('canonical', $tags);
        $this->assertSame('noindex', $tags['robots']);
    }

    public function test_open_graph_tags(): void
    {
        $m = $this->metadata(['og_image_url' => 'https://kedai.my/img.jpg']);

        $tags = $m->openGraphTags();

        $this->assertArrayHasKey('og:title', $tags);
        $this->assertArrayHasKey('og:image', $tags);
    }

    // -------------------------------------------------------------------
    // Audit
    // -------------------------------------------------------------------

    public function test_audit_kesan_tajuk_hilang(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata(['title' => '']);

        $isu = $s->auditMetadata($this->t->id, $m);

        $this->assertTrue(collect($isu)->contains(fn ($i) => $i[0] === SeoAuditIssue::RULE_TITLE_MISSING));
    }

    public function test_audit_kesan_tajuk_terlalu_panjang(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata(['title' => str_repeat('A', 80)]);

        $isu = $s->auditMetadata($this->t->id, $m);

        $this->assertTrue(collect($isu)->contains(fn ($i) => $i[0] === SeoAuditIssue::RULE_TITLE_TOO_LONG));
    }

    public function test_audit_kesan_description_hilang(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata(['description' => '']);

        $isu = $s->auditMetadata($this->t->id, $m);

        $this->assertTrue(collect($isu)->contains(fn ($i) => $i[0] === SeoAuditIssue::RULE_DESCRIPTION_MISSING));
    }

    public function test_audit_kesan_og_image_hilang(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata();

        $isu = $s->auditMetadata($this->t->id, $m);

        $this->assertTrue(collect($isu)->contains(fn ($i) => $i[0] === SeoAuditIssue::RULE_OG_IMAGE_MISSING));
    }

    public function test_audit_metadata_sempurna_skor_tinggi(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata([
            'title' => 'Tajuk Yang Baik Untuk SEO',
            'description' => 'Penerangan yang cukup panjang untuk memenuhi keperluan minimum enjin carian moden dan mesra pengguna.',
            'keywords' => 'uji, seo',
            'og_title' => 'OG Tajuk',
            'og_image_url' => 'https://kedai.my/og.jpg',
            'canonical_url' => 'https://kedai.my/p',
            'json_ld' => ['@type' => 'Product'],
        ]);

        $s->auditMetadata($this->t->id, $m);

        $this->assertGreaterThan(70, (float) $m->fresh()->seo_score);
    }

    public function test_audit_menyimpan_isu(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata(['title' => '']);

        $s->auditMetadata($this->t->id, $m);

        $this->assertGreaterThan(0, SeoAuditIssue::where('seo_metadata_id', $m->id)->count());
    }

    public function test_audit_tenant(): void
    {
        $s = app(SeoService::class);
        $this->metadata(['object_id' => 1]);
        $this->metadata(['object_id' => 2]);

        $hasil = $s->auditTenant($this->t->id);

        $this->assertSame(2, $hasil['jumlah_metadata']);
        $this->assertArrayHasKey('purata_skor', $hasil);
        $this->assertArrayHasKey('ikut_keseriusan', $hasil);
    }

    // -------------------------------------------------------------------
    // Cadangan
    // -------------------------------------------------------------------

    public function test_cadangan_heuristik(): void
    {
        $s = app(SeoService::class);
        SeoProfile::create([
            'tenant_id' => $this->t->id,
            'site_name' => 'Kedai Saya',
        ]);

        $m = $this->metadata(['title' => 'Produk Uji']);

        $cadangan = $s->janaCadanganHeuristik($this->t->id, $m);

        $this->assertNotEmpty($cadangan);
        $this->assertSame('title', $cadangan[0]->field);
    }

    public function test_cadangan_ai_fallback_tanpa_gateway(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata();

        $cadangan = $s->janaCadanganAI($this->t->id, $m);

        $this->assertIsArray($cadangan);
    }

    public function test_terima_cadangan_mengemas_kini_metadata(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata();

        $cadangan = SeoSuggestion::create([
            'tenant_id' => $this->t->id,
            'seo_metadata_id' => $m->id,
            'object_type' => $m->object_type,
            'object_id' => $m->object_id,
            'field' => 'title',
            'current_value' => $m->title,
            'suggested_value' => 'Tajuk Cadangan AI Yang Baru',
            'confidence' => 0.9,
        ]);

        $s->terimaCadangan($this->t->id, $cadangan->id);

        $this->assertSame('Tajuk Cadangan AI Yang Baru', $m->fresh()->title);
        $this->assertSame(SeoSuggestion::STATUS_APPLIED, $cadangan->fresh()->status);
    }

    public function test_tolak_cadangan(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata();

        $cadangan = SeoSuggestion::create([
            'tenant_id' => $this->t->id,
            'seo_metadata_id' => $m->id,
            'field' => 'title',
            'suggested_value' => 'X',
        ]);

        $s->tolakCadangan($this->t->id, $cadangan->id);

        $this->assertSame(SeoSuggestion::STATUS_REJECTED, $cadangan->fresh()->status);
        $this->assertNotSame('X', $m->fresh()->title);
    }

    // -------------------------------------------------------------------
    // JSON-LD & Sitemap
    // -------------------------------------------------------------------

    public function test_json_ld_produk(): void
    {
        $s = app(SeoService::class);

        $ld = $s->janaJsonLd(SeoMetadata::TYPE_PRODUCT, [
            'name' => 'Produk A',
            'description' => 'Penerangan',
            'price' => 99.00,
            'currency' => 'MYR',
        ]);

        $this->assertSame('Product', $ld['@type']);
        $this->assertSame('https://schema.org', $ld['@context']);
        $this->assertSame('MYR', $ld['offers']['priceCurrency']);
    }

    public function test_json_ld_artikel(): void
    {
        $s = app(SeoService::class);

        $ld = $s->janaJsonLd(SeoMetadata::TYPE_ARTICLE, ['title' => 'Tajuk']);

        $this->assertSame('Article', $ld['@type']);
    }

    public function test_json_ld_video(): void
    {
        $s = app(SeoService::class);

        $ld = $s->janaJsonLd(SeoMetadata::TYPE_VIDEO, ['title' => 'Video']);

        $this->assertSame('VideoObject', $ld['@type']);
    }

    public function test_sitemap_dijana(): void
    {
        $s = app(SeoService::class);

        $s->tambahSitemap($this->t->id, SeoMetadata::TYPE_PRODUCT, 1, 'https://kedai.my/p/1');
        $s->tambahSitemap($this->t->id, SeoMetadata::TYPE_PAGE, 2, 'https://kedai.my/about');

        $xml = $s->janaSitemap($this->t->id);

        $this->assertStringContainsString('<urlset', $xml);
        $this->assertStringContainsString('https://kedai.my/p/1', $xml);
        $this->assertStringContainsString('https://kedai.my/about', $xml);
    }

    public function test_sitemap_escape_ampersand(): void
    {
        $s = app(SeoService::class);

        $s->tambahSitemap($this->t->id, SeoMetadata::TYPE_PAGE, 9, 'https://kedai.my/a?b=1&c=2');

        $xml = $s->janaSitemap($this->t->id);

        $this->assertStringContainsString('&amp;', $xml);
    }

    // -------------------------------------------------------------------
    // Preview SERP
    // -------------------------------------------------------------------

    public function test_preview_serp(): void
    {
        $s = app(SeoService::class);
        $m = $this->metadata();

        $preview = $s->previewSerp($m);

        $this->assertArrayHasKey('google', $preview);
        $this->assertArrayHasKey('facebook', $preview);
        $this->assertArrayHasKey('whatsapp', $preview);
        $this->assertArrayHasKey('seo_score', $preview);
    }

    // -------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------

    public function test_metadata_tenant_lain_tidak_nampak(): void
    {
        $m = $this->metadata();

        [$u2, $t2] = $this->daftar('lawan@seo.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(SeoMetadata::find($m->id));
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_profil(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/seo/profile')
            ->assertOk();
    }

    public function test_api_kemas_profil(): void
    {
        $this->sebagai($this->u, $this->t)
            ->putJson('/api/v1/seo/profile', [
                'site_name' => 'Kedai Ujian',
                'site_url' => 'https://kedai.my',
            ])
            ->assertOk();

        $this->dalamTenant($this->t);
        $this->assertSame('Kedai Ujian', SeoProfile::where('tenant_id', $this->t->id)->value('site_name'));
    }

    public function test_api_simpan_metadata(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/seo/metadata', [
                'object_type' => 'product',
                'object_id' => 42,
                'title' => 'Tajuk API Yang Baik',
                'description' => 'Penerangan yang cukup panjang untuk lulus audit SEO asas dalam sistem ini.',
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, SeoMetadata::where('object_type', 'product')->where('object_id', 42)->count());
    }

    public function test_api_audit(): void
    {
        $this->metadata();

        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/seo/metadata/product/1/audit')
            ->assertOk();
    }

    public function test_api_audit_semua(): void
    {
        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/seo/audit')
            ->assertOk();
    }

    public function test_api_sitemap_xml(): void
    {
        app(SeoService::class)->tambahSitemap($this->t->id, 'page', 1, 'https://kedai.my/');

        $res = $this->sebagai($this->u, $this->t)->get('/api/v1/seo/sitemap.xml');

        $res->assertOk();
        $this->assertStringContainsString('<urlset', $res->getContent());
    }

    public function test_api_robots_txt(): void
    {
        $res = $this->sebagai($this->u, $this->t)->get('/api/v1/seo/robots.txt');

        $res->assertOk();
        $this->assertStringContainsString('User-agent', $res->getContent());
    }

    public function test_api_json_ld(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/seo/json-ld', [
                'object_type' => 'product',
                'data' => ['name' => 'A', 'price' => 10],
            ])
            ->assertOk();
    }

    public function test_api_kata_kunci(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/seo/keywords', [
                'keyword' => 'sistem pos malaysia',
                'intent' => 'commercial',
                'search_volume' => 1200,
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, SeoKeyword::where('tenant_id', $this->t->id)->count());
    }
}
