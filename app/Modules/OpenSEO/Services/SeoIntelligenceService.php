<?php

namespace App\Modules\OpenSEO\Services;

use App\Modules\ContentForge\Models\ContentDocument;
use App\Modules\ContentForge\Services\ContentForgeService;
use App\Modules\OpenSEO\Models\SeoBacklink;
use App\Modules\OpenSEO\Models\SeoKeywordResearch;
use App\Modules\OpenSEO\Models\SeoProject;
use App\Modules\OpenSEO\Models\SerpSnapshot;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * SeoIntelligenceService — intel Competitive & SEO.
 *
 * inspired oleh https://github.com/every-app/open-seo
 *
 * Menyediakan:
 *   -Jejak kedudukan SERP (dengan sejarah)
 *   - Penskoran keutamaan kata kunci
 *   - Analisis celah kandungan berbanding pesaing
 *   - Pemantauan backlink (ditemui / hilang)
 *   - Audit teknikal asas (robots, sitemap, meta)
 *
 * Semua sumber data luaran (SERP, backlink) disambungkan secara pluggable
 * melalui konfigurasi `open_seo.data_sources` — tanpa provider, modul
 * berfungsi dengan data yang dimasukkan manual.
 */
class SeoIntelligenceService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * Rekod kedudukan SERP untuk kata kunci.
     *
     * @param  array<string, mixed>  $data
     */
    public function rekodSerp(SeoProject $projek, string $keyword, array $data = []): SerpSnapshot
    {
        $sebelum = SerpSnapshot::query()
            ->where('seo_project_id', $projek->id)
            ->where('keyword', $keyword)
            ->orderByDesc('checked_at')
            ->first();

        $snapshot = SerpSnapshot::create([
            'seo_project_id' => $projek->id,
            'keyword' => $keyword,
            'position' => (int) ($data['position'] ?? 0),
            'previous_position' => $sebelum?->position,
            'url' => $data['url'] ?? null,
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
            'search_volume' => (int) ($data['search_volume'] ?? 0),
            'difficulty' => (int) ($data['difficulty'] ?? 0),
            'device' => $data['device'] ?? $projek->device,
            'country' => $data['country'] ?? $projek->country,
            'serp_features' => $data['serp_features'] ?? [],
            'competitors' => $data['competitors'] ?? [],
            'checked_at' => $data['checked_at'] ?? now(),
        ]);

        // Kemas kini kedudukan semasa pada rekod kata kunci.
        $kataKunci = SeoKeywordResearch::query()
            ->where('seo_project_id', $projek->id)
            ->where('keyword', $keyword)
            ->first();

        if ($kataKunci) {
            $kataKunci->update([
                'current_position' => $snapshot->position,
                'best_position' => min(
                    $kataKunci->best_position > 0 ? $kataKunci->best_position : 999,
                    $snapshot->position > 0 ? $snapshot->position : 999
                ),
            ]);
        }

        $projek->update(['last_crawled_at' => now()]);

        return $snapshot;
    }

    /**
     * Simpan kata kunci daripada penyelidikan dan kira skor keutamaan.
     */
    public function simpanKataKunci(SeoProject $projek, array $data): SeoKeywordResearch
    {
        $kataKunci = SeoKeywordResearch::updateOrCreate(
            [
                'seo_project_id' => $projek->id,
                'keyword' => $data['keyword'],
            ],
            [
                'intent' => $data['intent'] ?? 'informational',
                'search_volume' => (int) ($data['search_volume'] ?? 0),
                'difficulty' => (int) ($data['difficulty'] ?? 0),
                'cpc' => (float) ($data['cpc'] ?? 0),
                'competition' => $data['competition'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        $kataKunci->update([
            'priority_score' => $this->skorKeutamaan($kataKunci),
            'content_gap' => $this->isCelahKandungan($kataKunci),
        ]);

        return $kataKunci->fresh();
    }

    /**
     * Skor keutamaan kata kunci (0–100).
     *
     * Formula: gabungan isipadu carian (40%), kemudahan (40%) dan
     * kedudukan semasa (20%) — kata kunci yang belum TERMSUK dan mudah
     * mendapat skor tinggi.
     */
    public function skorKeutamaan(SeoKeywordResearch $kataKunci): float
    {
        $isipadu = min(1.0, log10(max(1, $kataKunci->search_volume) + 1) / 5);
        $mudah = 1.0 - min(1.0, $kataKunci->difficulty / 100);
        $celah = $kataKunci->current_position === 0 ? 1.0 : max(0.0, 1 - ($kataKunci->current_position / 100));

        return round(($isipadu * 40) + ($mudah * 40) + ($celah * 20), 2);
    }

    /**
     * Adakah kata kunci ini celah kandungan (belum ada halaman ber environmentally)?
     */
    public function isCelahKandungan(SeoKeywordResearch $kataKunci): bool
    {
        $adaHalaman = SerpSnapshot::query()
            ->where('seo_project_id', $kataKunci->seo_project_id)
            ->where('keyword', $kataKunci->keyword)
            ->where('position', '>', 0)
            ->exists();

        return ! $adaHalaman;
    }

    /**
     * Rekod backlink baharu atau kemas kini yang sedia ada.
     */
    public function rekodBacklink(SeoProject $projek, array $data): SeoBacklink
    {
        $sedia = SeoBacklink::query()
            ->where('seo_project_id', $projek->id)
            ->where('source_url', $data['source_url'])
            ->first();

        if ($sedia) {
            $sedia->update([
                'last_seen_at' => now(),
                'is_lost' => false,
                'status' => 'active',
                'anchor_text' => $data['anchor_text'] ?? $sedia->anchor_text,
                'domain_authority' => (int) ($data['domain_authority'] ?? $sedia->domain_authority),
            ]);

            return $sedia->fresh();
        }

        return SeoBacklink::create([
            'seo_project_id' => $projek->id,
            'source_url' => $data['source_url'],
            'source_domain' => $data['source_domain'] ?? $this->domainDaripada($data['source_url']),
            'source_title' => $data['source_title'] ?? null,
            'target_url' => $data['target_url'] ?? $projek->base_url,
            'anchor_text' => $data['anchor_text'] ?? null,
            'domain_authority' => (int) ($data['domain_authority'] ?? 0),
            'page_authority' => (int) ($data['page_authority'] ?? 0),
            'link_type' => $data['link_type'] ?? 'text',
            'is_dofollow' => $data['is_dofollow'] ?? true,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Tandakan backlink yang tidak dijumpai lagi sebagai hilang.
     */
    public function tandakanHilang(SeoProject $projek, array $sourceUrls): int
    {
        $hilang = SeoBacklink::query()
            ->where('seo_project_id', $projek->id)
            ->where('is_lost', false)
            ->whereNotIn('source_url', $sourceUrls)
            ->get();

        foreach ($hilang as $backlink) {
            $backlink->update(['is_lost' => true, 'status' => 'lost']);
        }

        return $hilang->count();
    }

    /**
     * Analisis celah kandungan berbanding pesaing.
     *
     * Bandingkan kata kunci pesaing dengan kata kunci tenant; yang ada pada
     * pesaing tetapi tiada pada tenant = celah kandungan.
     *
     * @return array<string, mixed>
     */
    public function analisisCelah(SeoProject $projek): array
    {
        $kataKunciTenant = SeoKeywordResearch::query()
            ->where('seo_project_id', $projek->id)
            ->pluck('keyword')
            ->all();

        $kataKunciPesaing = SeoKeywordResearch::query()
            ->where('seo_project_id', $projek->id)
            ->where('notes', 'like', 'pesaing:%')
            ->pluck('keyword')
            ->all();

        $celah = array_values(array_diff($kataKunciPesaing, $kataKunciTenant));

        // Kuosyen Snapshot: pesaing yang menduduki 10 teratas tetapi tenant tiada.
        $dominan = SerpSnapshot::query()
            ->where('seo_project_id', $projek->id)
            ->where('position', '>', 0)
            ->where('position', '<=', 10)
            ->pluck('competitors')
            ->flatMap(fn ($senarai) => is_array($senarai) ? $senarai : [])
            ->flatten()
            ->unique()
            ->values()
            ->all();

        return [
            'jumlah_kata_kunci' => count($kataKunciTenant),
            'celah_kandungan' => $celah,
            'jumlah_celah' => count($celah),
            'pesaing_dominan' => $dominan,
        ];
    }

    /**
     * Audit teknikal asas untuk sesebuah URL.
     *
     * @return array<string, mixed>
     */
    public function auditUrl(string $url): array
    {
        $kunci = 'openseo_audit_'.md5($url);
        $audit = Cache::remember($kunci, 600, function () use ($url) {
            $isu = [];

            try {
                $respons = Http::timeout(20)
                    ->withHeaders(['User-Agent' => 'AI-Bijan-SEO-Audit/1.0'])
                    ->get($url);

                if (! $respons->successful()) {
                    $isu[] = ['type' => 'http_error', 'severity' => 'error', 'message' => 'HTTP '.$respons->status()];
                }

                $html = $respons->body();

                if (! preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)) {
                    $isu[] = ['type' => 'missing_title', 'severity' => 'error', 'message' => 'Tiada tag <title>.'];
                } else {
                    $panjang = mb_strlen(trim($m[1]));
                    if ($panjang < 15 || $panjang > 60) {
                        $isu[] = ['type' => 'title_length', 'severity' => 'warning', 'message' => 'Panjang tajuk '.$panjang.' aksara (sasaki 15-60).'];
                    }
                }

                if (! preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)) {
                    $isu[] = ['type' => 'missing_description', 'severity' => 'error', 'message' => 'Tiada meta description.'];
                } else {
                    $panjang = mb_strlen(trim($m[1]));
                    if ($panjang < 50 || $panjang > 160) {
                        $isu[] = ['type' => 'description_length', 'severity' => 'warning', 'message' => 'Panjang description '.$panjang.' aksara (sasaki 50-160).'];
                    }
                }

                // ⚠️ Audit kandungan dalam halaman disemak oleh ContentForge —
                // modul itu mempunyai pemeriksa SEO penuh (skor 0-100).
                $semakanKandungan = $this->auditKandunganHalaman($html);

                if (! preg_match('/<h1[^>]*>/i', $html)) {
                    $isu[] = ['type' => 'missing_h1', 'severity' => 'warning', 'message' => 'Tiada tag H1.'];
                }

                if (preg_match('/<link[^>]+rel=["\']canonical["\']/i', $html)) {
                    $isu[] = ['type' => 'canonical_ok', 'severity' => 'info', 'message' => 'Canonical tag hadir.'];
                }

                if (preg_match('/name=["\']viewport["\']/i', $html)) {
                    $isu[] = ['type' => 'viewport_ok', 'severity' => 'info', 'message' => 'Meta viewport hadir (mesra telefon).'];
                } else {
                    $isu[] = ['type' => 'missing_viewport', 'severity' => 'warning', 'message' => 'Tiada meta viewport.'];
                }

                if (stripos($html, 'application/ld+json') !== false) {
                    $isu[] = ['type' => 'json_ld_ok', 'severity' => 'info', 'message' => 'Struktur data JSON-LD hadir.'];
                }

                return [
                    'url' => $url,
                    'status' => $respons->status(),
                    'isu' => array_merge($isu, $semakanKandungan['isu']),
                    'jumlah_isu' => count($isu) + $semakanKandungan['jumlah_isu'],
                    'jumlah_error' => count(array_filter($isu, fn ($i) => $i['severity'] === 'error'))
                        + $semakanKandungan['jumlah_error'],
                    'skor_kandungan' => $semakanKandungan['skor'],
                ];
            } catch (Throwable $e) {
                Log::info('OpenSEO: audit gagal', ['url' => $url, 'error' => $e->getMessage()]);

                return [
                    'url' => $url,
                    'status' => 0,
                    'isu' => [['type' => 'fetch_failed', 'severity' => 'error', 'message' => $e->getMessage()]],
                    'jumlah_isu' => 1,
                    'jumlah_error' => 1,
                ];
            }
        });

        return $audit;
    }

    /**
     * Semak kandungan halaman dengan pemeriksa SEO ContentForge.
     *
     * ContentForge mempunyai `skorSeo()` penuh (7 komponen: panjang, tajuk,
     * meta, kata kunci, H2, pautan, imej). Kita gunakan ia secara terus
     * supaya modul ini tidak menduplikasi logik SEO.
     *
     * @return array{skor: int, isu: array<int, array<string, mixed>>, jumlah_isu: int, jumlah_error: int}
     */
    protected function auditKandunganHalaman(string $html): array
    {
        try {
            $dokumen = new ContentDocument([
                'body_html' => $html,
                'title' => $this->ambilTajuk($html),
                'meta_description' => $this->ambilMeta($html),
                'keywords' => [],
                'target_word_count' => 1500,
            ]);

            $hasil = app(ContentForgeService::class)
                ->skorSeo($dokumen);

            return [
                'skor' => $hasil['score'],
                'isu' => $hasil['issues'],
                'jumlah_isu' => count($hasil['issues']),
                'jumlah_error' => count(array_filter($hasil['issues'], fn ($i) => ($i['severity'] ?? '') === 'error')),
            ];
        } catch (Throwable $e) {
            Log::info('OpenSEO: audit kandungan ContentForge gagal', ['error' => $e->getMessage()]);

            return ['skor' => 0, 'isu' => [], 'jumlah_isu' => 0, 'jumlah_error' => 0];
        }
    }

    /**
     * Ambil tajuk halaman.
     */
    protected function ambilTajuk(string $html): string
    {
        return preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $m)
            ? trim(strip_tags($m[1]))
            : '';
    }

    /**
     * Ambil meta description.
     */
    protected function ambilMeta(string $html): string
    {
        return preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']/is', $html, $m)
            ? trim(strip_tags($m[1]))
            : '';
    }

    /**
     * Ringkasan prestasi SEO projek.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(SeoProject $projek): array
    {
        $kataKunci = SeoKeywordResearch::query()->where('seo_project_id', $projek->id);
        $serp = SerpSnapshot::query()->where('seo_project_id', $projek->id);

        $posisiTeratas = (clone $serp)->where('position', '>', 0)->where('position', '<=', 10)->count();
        $posisiHalamanSatu = (clone $serp)->where('position', '>', 0)->where('position', '<=', 3)->count();
        $jumlahCarian = (clone $serp)->count();

        $backlink = SeoBacklink::query()->where('seo_project_id', $projek->id);
        $aktif = (clone $backlink)->where('is_lost', false)->count();
        $hilang = (clone $backlink)->where('is_lost', true)->count();

        return [
            'jumlah_kata_kunci' => (clone $kataKunci)->count(),
            'jumlah_celah' => (clone $kataKunci)->where('content_gap', true)->count(),
            'purata_skor_keutamaan' => round((float) (clone $kataKunci)->avg('priority_score'), 2),
            'jumlah_semakan_serp' => $jumlahCarian,
            'posisi_3_teratas' => $posisiHalamanSatu,
            'posisi_10_teratas' => $posisiTeratas,
            'kadar_visibility' => $jumlahCarian > 0 ? round(($posisiTeratas / $jumlahCarian) * 100, 2) : 0.0,
            'backlink_aktif' => $aktif,
            'backlink_hilang' => $hilang,
            'jumlah_domain_rujukan' => (clone $backlink)->where('is_lost', false)->distinct()->count('source_domain'),
            'purata_domain_authority' => round((float) (clone $backlink)->where('is_lost', false)->avg('domain_authority'), 2),
        ];
    }

    /**
     * Cari domain daripada URL.
     */
    protected function domainDaripada(string $url): string
    {
        $hos = parse_url($url, PHP_URL_HOST);

        return $hos ? preg_replace('/^www\./', '', $hos) : '';
    }
}
