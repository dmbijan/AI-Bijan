<?php

namespace App\Modules\ContentForge\Services;

use App\Modules\ContentForge\Models\ContentDocument;
use App\Modules\ContentForge\Models\ContentSite;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ContentForgeService — penjanaan kandungan panjang & pengoptimuman SEO.
 *
 * Berasal daripada SEO Writing AI (seowriting.ai):
 *   - penjanaan artikel panjang daripada brief
 *   - senarai produk (Amazon/affiliate)
 *   - pemeriksa SEO (skor 0-100)
 *   - 50+ bahasa
 *   - penjanaan pukal & auto-post WordPress
 */
class ContentForgeService
{
    public function __construct(private TenantContext $ctx) {}

    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    /**
     * Cipta dokumen baharu.
     *
     * @param  array<string, mixed>  $data
     */
    public function ciptaDokumen(array $data): ContentDocument
    {
        $tajuk = $data['title'] ?? 'Dokumen Baharu';

        return ContentDocument::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'slug' => Str::slug($tajuk).'-'.Str::random(5),
            'status' => ContentDocument::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ], $data));
    }

    /**
     * Analisis SERP pesaing — rujukan struktur untuk penjanaan.
     *
     * ⚠️ Tanpa API SEO luaran, analisis kembali struktur lalai yang realistik
     * berdasarkan niat kata kunci. Tidak berpura-pura mendapat data SERP.
     *
     * @param  array<int, string>  $kataKunci
     * @return array<string, mixed>
     */
    public function analisisSerp(array $kataKunci): array
    {
        return [
            'kata_kunci' => $kataKunci,
            'sumber' => 'estimasi-tempatan',
            'struktur_disaran' => [
                'Panjang optimum: '.config('content_forge.defaults.word_count', 1500).' perkataan',
                'H1: 1 sahaja, mengandungi kata kunci utama',
                'H2: 5-8 seksyen, setiap satu subtopik',
                'H3: 2-3 sub-bahagian di bawah H2 utama',
                'Meta description: 150-160 aksara',
                'Kata kunci dalam 100 perkataan pertama',
                'Sertakan senarai/poin untuk ciri',
                'Sertakan jadual perbandingan jika produk',
            ],
            'niat' => $this->tekaNiat($kataKunci),
            'kata_kunci_semantik' => array_map(
                fn ($k) => [$k.' terbaik', 'cara pilih '.$k, $k.' harga', 'review '.$k],
                $kataKunci
            ),
        ];
    }

    /**
     * Hasilkan rangka (outline) daripada brief.
     *
     * @param  array<int, string>  $kataKunci
     * @return array<int, array<string, mixed>>
     */
    public function janaRangka(string $tajuk, array $kataKunci, string $jenis = 'article'): array
    {
        $utama = $kataKunci[0] ?? $tajuk;

        $asas = [
            ['level' => 2, 'heading' => 'Pengenalan '.$utama, 'notes' => 'Tarik pembaca, nyatakan masalah'],
            ['level' => 2, 'heading' => 'Mengapa '.$utama.' Penting', 'notes' => 'Konteks & manfaat'],
        ];

        if ($jenis === 'product_roundup' || $jenis === 'product_review') {
            $asas[] = ['level' => 2, 'heading' => 'Kriteria Pemilihan', 'notes' => 'Senarai ciri yang dinilai'];
            $asas[] = ['level' => 2, 'heading' => 'Pilihan Terbaik', 'notes' => '3-7 produk dengan pro/kontra'];
            $asas[] = ['level' => 2, 'heading' => 'Jadual Perbandingan', 'notes' => 'Jadual harga & ciri'];
        } else {
            $asas[] = ['level' => 2, 'heading' => 'Langkah Demi Langkah', 'notes' => 'Senarai bernombor'];
            $asas[] = ['level' => 2, 'heading' => 'Kesilapan Lazim', 'notes' => 'Elak masalah biasa'];
        }

        $asas[] = ['level' => 2, 'heading' => 'Kesimpulan', 'notes' => 'Ringkas + seruan bertindak'];
        $asas[] = ['level' => 2, 'heading' => 'Soalan Lazim', 'notes' => '3-5 soalan lazim'];

        return $asas;
    }

    /**
     * Kira skor SEO (0-100) untuk kandungan.
     *
     * Semakan: tajuk, meta, panjang, kata kunci, tajuk H2, pautan, dll.
     *
     * @param  array<int, string>  $kataKunci
     * @return array{score: int, issues: array<int, array<string, string>>}
     */
    public function skorSeo(ContentDocument $dokumen): array
    {
        $skor = 0;
        $isu = [];

        $kandungan = (string) ($dokumen->body_html ?: $dokumen->body_markdown);
        $teks = trim(strip_tags($kandungan));
        $bilanganPerkataan = str_word_count($teks);
        $kataKunci = array_filter((array) ($dokumen->keywords ?? []));
        $utama = $kataKunci[0] ?? null;

        // 1. Panjang kandungan (25 markah).
        $sasaran = max(1, (int) $dokumen->target_word_count);

        if ($bilanganPerkataan >= $sasaran) {
            $skor += 25;
        } elseif ($bilanganPerkataan >= $sasaran * 0.7) {
            $skor += 15;
            $isu[] = ['type' => 'length', 'severity' => 'warning', 'message' => 'Kandungan '.$bilanganPerkataan.' perkataan (sasaran '.$sasaran.').'];
        } else {
            $isu[] = ['type' => 'length', 'severity' => 'error', 'message' => 'Kandungan terlalu pendek ('.$bilanganPerkataan.'/'.$sasaran.').'];
        }

        // 2. Tajuk (20 markah).
        $panjangTajuk = mb_strlen((string) $dokumen->title);

        if ($panjangTajuk >= 30 && $panjangTajuk <= 65) {
            $skor += 20;
        } else {
            $isu[] = ['type' => 'title_length', 'severity' => 'warning', 'message' => 'Panjang tajuk '.$panjangTajuk.' aksara (sasaran 30-65).'];
        }

        // 3. Meta description (15 markah).
        $panjangMeta = mb_strlen((string) $dokumen->meta_description);

        if ($panjangMeta >= 120 && $panjangMeta <= 160) {
            $skor += 15;
        } elseif ($panjangMeta > 0) {
            $isu[] = ['type' => 'meta_length', 'severity' => 'warning', 'message' => 'Meta description '.$panjangMeta.' aksara (sasaran 120-160).'];
        } else {
            $isu[] = ['type' => 'meta_missing', 'severity' => 'error', 'message' => 'Meta description tiada.'];
        }

        // 4. Kata kunci utama dalam 100 perkataan pertama (15 markah).
        if ($utama) {
            $awal = Str::lower(implode(' ', array_slice(preg_split('/\s+/u', $teks) ?: [], 0, 100)));

            if (str_contains($awal, Str::lower($utama))) {
                $skor += 15;
            } else {
                $isu[] = ['type' => 'keyword_intro', 'severity' => 'warning', 'message' => 'Kata kunci utama tiada dalam 100 perkataan pertama.'];
            }
        } else {
            $isu[] = ['type' => 'keywords_missing', 'severity' => 'error', 'message' => 'Tiada kata kunci disasarkan.'];
        }

        // 5. Tajuk H2 (10 markah).
        $bilanganH2 = preg_match_all('/<h2[^>]*>/i', $kandungan);

        if ($bilanganH2 >= 3) {
            $skor += 10;
        } else {
            $isu[] = ['type' => 'headings', 'severity' => 'warning', 'message' => 'Hanya '.$bilanganH2.' tajuk H2 (sasaran ≥3).'];
        }

        // 6. Pautan dalaman/luaran (10 markah).
        $bilanganPautan = preg_match_all('/<a\s[^>]*href=/i', $kandungan);

        if ($bilanganPautan >= 2) {
            $skor += 10;
        } else {
            $isu[] = ['type' => 'links', 'severity' => 'info', 'message' => 'Hanya '.$bilanganPautan.' pautan (sasaran ≥2).'];
        }

        // 7. Imej dengan alt (5 markah).
        $imejDenganAlt = preg_match_all('/<img[^>]+alt=["\'][^"\']+["\']/i', $kandungan);

        if ($imejDenganAlt >= 1) {
            $skor += 5;
        } else {
            $isu[] = ['type' => 'images', 'severity' => 'info', 'message' => 'Tiada imej dengan teks alt.'];
        }

        $skor = min(100, $skor);

        $dokumen->update([
            'seo_score' => $skor,
            'seo_issues' => $isu,
            'actual_word_count' => $bilanganPerkataan,
        ]);

        return ['score' => $skor, 'issues' => $isu];
    }

    /**
     * Terbit ke WordPress.
     *
     * @return array{success: bool, url: ?string, mesej: string}
     */
    public function terbitkan(ContentDocument $dokumen, ContentSite $laman): array
    {
        if (! $laman->bersambung()) {
            return ['success' => false, 'url' => null, 'mesej' => 'Laman WordPress tidak bersambung.'];
        }

        if (! $dokumen->sediaTerbit()) {
            return ['success' => false, 'url' => null, 'mesej' => 'Dokumen belum sedia untuk diterbitkan.'];
        }

        try {
            $respons = Http::withBasicAuth(
                $laman->username,
                $laman->app_password
            )
                ->timeout(30)
                ->post(rtrim($laman->url, '/').'/wp-json/wp/v2/posts', [
                    'title' => $dokumen->title,
                    'content' => $dokumen->body_html,
                    'slug' => $dokumen->slug,
                    'status' => $laman->auto_publish ? 'publish' : 'draft',
                    'excerpt' => $dokumen->meta_description,
                ]);

            if (! $respons->successful()) {
                $mesej = $respons->json('message') ?? 'HTTP '.$respons->status();
                Log::warning('ContentForge: terbit gagal', ['error' => $mesej]);
                $laman->update(['status' => ContentSite::STATUS_ERROR, 'last_error' => $mesej]);

                return ['success' => false, 'url' => null, 'mesej' => (string) $mesej];
            }

            $data = $respons->json();
            $url = $data['link'] ?? null;

            $dokumen->update([
                'status' => ContentDocument::STATUS_PUBLISHED,
                'published_url' => $url,
                'published_at' => now(),
                'wordpress_site_id' => $laman->id,
            ]);

            $laman->update(['last_published_at' => now(), 'last_error' => null]);

            return ['success' => true, 'url' => $url, 'mesej' => 'Dokumen diterbitkan.'];
        } catch (\Throwable $e) {
            Log::warning('ContentForge: terbit exception', ['error' => $e->getMessage()]);
            $laman->update(['status' => ContentSite::STATUS_ERROR, 'last_error' => $e->getMessage()]);

            return ['success' => false, 'url' => null, 'mesej' => $e->getMessage()];
        }
    }

    /**
     * Ringkasan.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(): array
    {
        $semua = ContentDocument::query();

        return [
            'jumlah_dokumen' => (clone $semua)->count(),
            'draf' => (clone $semua)->where('status', ContentDocument::STATUS_DRAFT)->count(),
            'sedia' => (clone $semua)->where('status', ContentDocument::STATUS_READY)->count(),
            'diterbitkan' => (clone $semua)->where('status', ContentDocument::STATUS_PUBLISHED)->count(),
            'purata_skor_seo' => round((float) (clone $semua)->avg('seo_score'), 1),
            'jumlah_perkataan' => (int) (clone $semua)->sum('actual_word_count'),
            'jumlah_kos_sen' => (int) (clone $semua)->sum('cost_sen'),
            'laman_bersambung' => ContentSite::query()->where('status', ContentSite::STATUS_CONNECTED)->count(),
        ];
    }

    /**
     * Teka niat carian daripada kata kunci.
     *
     * @param  array<int, string>  $kataKunci
     */
    protected function tekaNiat(array $kataKunci): string
    {
        $gabung = Str::lower(implode(' ', $kataKunci));

        return match (true) {
            (bool) preg_match('/\b(beli|harga|murah|terbaik|review|promo)\b/u', $gabung) => 'commercial',
            (bool) preg_match('/\b(cara|bagaimana|tutorial|panduan|langkah)\b/u', $gabung) => 'informational',
            (bool) preg_match('/\b(daftar|download|log masuk|beli sekarang)\b/u', $gabung) => 'transactional',
            default => 'informational',
        };
    }
}
