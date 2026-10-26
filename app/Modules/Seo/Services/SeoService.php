<?php

namespace App\Modules\Seo\Services;

use App\Modules\Seo\Models\SeoAuditIssue;
use App\Modules\Seo\Models\SeoKeyword;
use App\Modules\Seo\Models\SeoMetadata;
use App\Modules\Seo\Models\SeoProfile;
use App\Modules\Seo\Models\SeoSitemap;
use App\Modules\Seo\Models\SeoSuggestion;
use Illuminate\Support\Facades\Log;

/**
 * SeoService — enjin SEO & metadata untuk SELURUH objek AI BIJAN.
 *
 * ✅ Idea #20: SEO bukan modul khusus satu platform. Setiap produk,
 * halaman, artikel, video, kategori dalam AI BIJAN perlu metadata,
 * audit, sitemap dan JSON-LD.
 */
class SeoService
{
    /** Had panjang optimum (ikut amalan industri). */
    public const TITLE_MIN = 15;
    public const TITLE_MAX = 60;
    public const DESCRIPTION_MIN = 50;
    public const DESCRIPTION_MAX = 160;
    public const SLUG_MAX = 75;

    /**
     * Dapatkan atau cipta profil SEO tenant.
     */
    public function profil(int $tenantId): SeoProfile
    {
        return SeoProfile::firstOrCreate(
            ['tenant_id' => $tenantId],
            ['site_name' => config('app.name')]
        );
    }

    /**
     * Simpan metadata objek.
     */
    public function simpanMetadata(int $tenantId, string $objectType, int $objectId, array $data): SeoMetadata
    {
        $metadata = SeoMetadata::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'object_type' => $objectType,
                'object_id' => $objectId,
            ],
            array_merge($data, [
                'tenant_id' => $tenantId,
                'object_type' => $objectType,
                'object_id' => $objectId,
            ])
        );

        // Audit automatik setiap kali metadata berubah
        $this->auditMetadata($tenantId, $metadata);

        return $metadata->fresh();
    }

    /**
     * Ambil metadata objek.
     */
    public function metadata(int $tenantId, string $objectType, int $objectId): ?SeoMetadata
    {
        return SeoMetadata::query()
            ->where('tenant_id', $tenantId)
            ->where('object_type', $objectType)
            ->where('object_id', $objectId)
            ->first();
    }

    /**
     * Audit metadata — Jana isu SEO.
     */
    public function auditMetadata(int $tenantId, SeoMetadata $metadata): array
    {
        // Buat isu lama (yang tidak lagi terpakai)
        $metadata->issues()->where('is_resolved', false)->delete();

        $isu = [];
        $title = trim((string) $metadata->title);
        $desc = trim((string) $metadata->description);

        // Title
        if ($title === '') {
            $isu[] = [SeoAuditIssue::RULE_TITLE_MISSING, SeoAuditIssue::SEVERITY_ERROR,
                'Tiada meta title', 'Tambahkan tajuk 15–60 aksara yang mengandungi kata kunci utama.'];
        } else {
            $len = mb_strlen($title);
            if ($len > self::TITLE_MAX) {
                $isu[] = [SeoAuditIssue::RULE_TITLE_TOO_LONG, SeoAuditIssue::SEVERITY_WARNING,
                    "Meta title {$len} aksara (had {$this->titleMax()})",
                    'Pendekkan kepada 50–60 aksara — enjin carian memotong lebih.'];
            } elseif ($len < self::TITLE_MIN) {
                $isu[] = [SeoAuditIssue::RULE_TITLE_TOO_SHORT, SeoAuditIssue::SEVERITY_WARNING,
                    "Meta title {$len} aksara terlalu pendek",
                    'Tambahkan konteks atau kata kunci untuk mengisi lebih ruang carian.'];
            }
        }

        // Description
        if ($desc === '') {
            $isu[] = [SeoAuditIssue::RULE_DESCRIPTION_MISSING, SeoAuditIssue::SEVERITY_ERROR,
                'Tiada meta description', 'Tulis ringkasan 50–160 aksara yang memancing klik.'];
        } else {
            $len = mb_strlen($desc);
            if ($len > self::DESCRIPTION_MAX) {
                $isu[] = [SeoAuditIssue::RULE_DESCRIPTION_TOO_LONG, SeoAuditIssue::SEVERITY_WARNING,
                    "Meta description {$len} aksara terlalu panjang",
                    'Pendekkan kepada 120–155 aksara.'];
            } elseif ($len < self::DESCRIPTION_MIN) {
                $isu[] = [SeoAuditIssue::RULE_DESCRIPTION_TOO_SHORT, SeoAuditIssue::SEVERITY_WARNING,
                    "Meta description {$len} aksara terlalu pendek",
                    'Tambah butiran untuk memberi konteks kepada pengguna.'];
            }
        }

        // Keywords
        if (trim((string) $metadata->keywords) === '') {
            $isu[] = [SeoAuditIssue::RULE_KEYWORDS_MISSING, SeoAuditIssue::SEVERITY_INFO,
                'Tiada keywords tag', 'Tambah 3–7 kata kunci relevan (strategi moden: kata kunci penting dalam title & description sudah cukup).'];
        }

        // Open Graph
        if (trim((string) $metadata->og_title) === '') {
            $isu[] = [SeoAuditIssue::RULE_OG_TITLE_MISSING, SeoAuditIssue::SEVERITY_INFO,
                'Tiada og:title', 'Set og:title supaya paparan lebih menarik bila dikongsi di media sosial.'];
        }

        if (trim((string) $metadata->og_image_url) === '') {
            $isu[] = [SeoAuditIssue::RULE_OG_IMAGE_MISSING, SeoAuditIssue::SEVERITY_WARNING,
                'Tiada og:image', 'Tambah imej 1200×630px — pautan tanpa gambar kurang diklik.'];
        }

        // Canonical
        if (trim((string) $metadata->canonical_url) === '') {
            $isu[] = [SeoAuditIssue::RULE_CANONICAL_MISSING, SeoAuditIssue::SEVERITY_INFO,
                'Tiada canonical URL', 'Set canonical URL untuk mengelakkan kandungan berulang.'];
        }

        // JSON-LD
        if (empty($metadata->json_ld)) {
            $isu[] = [SeoAuditIssue::RULE_JSONLD_MISSING, SeoAuditIssue::SEVERITY_INFO,
                'Tiada schema.org JSON-LD', 'Tambah structured data supaya Google boleh memahami kandungan.'];
        }

        // Slug
        if ($metadata->slug !== null && mb_strlen($metadata->slug) > self::SLUG_MAX) {
            $isu[] = [SeoAuditIssue::RULE_SLUG_TOO_LONG, SeoAuditIssue::SEVERITY_INFO,
                'Slug panjang', 'Pendekkan slug — melebihi 75 aksara akan dipotong oleh Google.'];
        }

        // No-index
        if ($metadata->no_index) {
            $isu[] = [SeoAuditIssue::RULE_NO_INDEX_SET, SeoAuditIssue::SEVERITY_WARNING,
                'Halaman diset noindex', 'Halaman ini tidak akan muncul dalam carian.'];
        }

        // Simpan isu
        foreach ($isu as [$rule, $severity, $message, $suggestion]) {
            SeoAuditIssue::create([
                'tenant_id' => $tenantId,
                'seo_metadata_id' => $metadata->id,
                'object_type' => $metadata->object_type,
                'object_id' => $metadata->object_id,
                'rule' => $rule,
                'severity' => $severity,
                'message' => $message,
                'suggestion' => $suggestion,
            ]);
        }

        // Kira skor SEO (0–100)
        $berat = [
            SeoAuditIssue::SEVERITY_ERROR => 20,
            SeoAuditIssue::SEVERITY_WARNING => 8,
            SeoAuditIssue::SEVERITY_INFO => 3,
        ];
        $penalti = 0;
        foreach ($isu as [$rule, $severity]) {
            $penalti += $berat[$severity] ?? 0;
        }

        $metadata->update([
            'seo_score' => max(0, round(100 - $penalti, 2)),
            'audit_findings' => array_map(fn ($i) => [
                'rule' => $i[0],
                'severity' => $i[1],
                'message' => $i[2],
            ], $isu),
        ]);

        return $isu;
    }

    /**
     * Jana cadangan AI untuk metadata (guna AI Gateway).
     */
    public function janaCadanganAI(int $tenantId, SeoMetadata $metadata, array $konteks = []): array
    {
        $profil = $this->profil($tenantId);
        $siteName = $profil->site_name ?? 'AI BIJAN';

        $brief = "Anda pakar SEO untuk {$siteName}.\n"
            ."Objek: {$metadata->object_type}\n"
            ."Tajuk semasa: " . ($metadata->title ?: '(tiada)') . "\n"
            ."Penerangan semasa: " . ($metadata->description ?: '(tiada)') . "\n"
            . 'Kata kunci: ' . ($metadata->keywords ?: '(tiada)') . "\n";

        if (! empty($konteks['content'])) {
            $brief .= "Kandungan (ringkas): " . mb_substr($konteks['content'], 0, 800) . "\n";
        }

        $brief .= "\nBerikan cadangan dalam JSON dengan kunci: title, description, keywords (array), reasoning.\n"
            ."Title mesti 15-60 aksara, description 50-160 aksara. Gunakan Bahasa Malaysia atau Bahasa Inggeris mengikut kandungan.\n"
            .'HANYA JSON, tiada teks lain.';

        $cadangan = [];

        try {
            $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

            $hasil = $gateway->call(
                $tenantId,
                [
                    ['role' => 'system', 'content' => 'Anda pakar SEO yang memulangkan JSON sahaja.'],
                    ['role' => 'user', 'content' => $brief],
                ],
                'seo',
                ['max_output_tokens' => 800, 'source_module' => 'seo']
            );

            $kandungan = (string) ($hasil['content'] ?? '');
            $json = $this->ekstrakJson($kandungan);

            if (isset($json['title'])) {
                $cadangan[] = $this->ciptaCadangan($tenantId, $metadata, 'title', $metadata->title, (string) $json['title'], $json['reasoning'] ?? null, 0.9);
            }

            if (isset($json['description'])) {
                $cadangan[] = $this->ciptaCadangan($tenantId, $metadata, 'description', $metadata->description, (string) $json['description'], $json['reasoning'] ?? null, 0.85);
            }

            if (! empty($json['keywords']) && is_array($json['keywords'])) {
                $cadangan[] = $this->ciptaCadangan(
                    $tenantId,
                    $metadata,
                    'keywords',
                    $metadata->keywords,
                    implode(', ', array_slice($json['keywords'], 0, 7)),
                    $json['reasoning'] ?? null,
                    0.75
                );
            }
        } catch (\Throwable $e) {
            Log::warning('Seo: jana cadangan AI gagal', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            // Fallback heuristik tanpa AI
            $cadangan[] = $this->ciptaCadangan(
                $tenantId,
                $metadata,
                'title',
                $metadata->title,
                trim((string) $metadata->title) . ' | ' . $siteName,
                'Tambah nama jenama pada tajuk untuk meningkatkan kadar klik dan pengenalan.',
                0.5
            );
        }

        return array_values(array_filter($cadangan));
    }

    /**
     * Cadangan heuristik (tanpa AI) — berdasarkan struktur metadata.
     */
    public function janaCadanganHeuristik(int $tenantId, SeoMetadata $metadata): array
    {
        $profil = $this->profil($tenantId);
        $siteName = $profil->site_name ?? '';
        $cadangan = [];

        $title = trim((string) $metadata->title);

        if ($title !== '' && $siteName !== '' && ! str_contains($title, $siteName)) {
            $baru = mb_substr($title . ' | ' . $siteName, 0, self::TITLE_MAX);
            $cadangan[] = $this->ciptaCadangan($tenantId, $metadata, 'title', $title, $baru,
                'Tajuk tanpa nama jenama — penjenamaan menaikkan kadar klik.', 0.6);
        }

        if (trim((string) $metadata->description) === '' && $title !== '') {
            $cadangan[] = $this->ciptaCadangan($tenantId, $metadata, 'description', null,
                mb_substr($title . ' — Penyelesaian terbaik untuk keperluan perniagaan anda.', 0, self::DESCRIPTION_MAX),
                'Tiada description — enjin carian akan jana sendiri (biasanya buruk).', 0.7);
        }

        if (trim((string) $metadata->keywords) === '') {
            $cadangan[] = $this->ciptaCadangan($tenantId, $metadata, 'keywords', null,
                implode(', ', array_slice(array_filter(explode(' ', $title)), 0, 5)),
                'Tambah kata kunci teras daripada tajuk.', 0.5);
        }

        return array_values(array_filter($cadangan));
    }

    /**
     * Simpan cadangan.
     */
    private function ciptaCadangan(int $tenantId, SeoMetadata $metadata, string $field, ?string $current, ?string $suggested, ?string $reasoning, float $confidence): ?SeoSuggestion
    {
        if ($suggested === null || trim($suggested) === '') {
            return null;
        }

        if ($current !== null && trim($current) === trim($suggested)) {
            return null;
        }

        return SeoSuggestion::create([
            'tenant_id' => $tenantId,
            'seo_metadata_id' => $metadata->id,
            'object_type' => $metadata->object_type,
            'object_id' => $metadata->object_id,
            'field' => $field,
            'current_value' => $current,
            'suggested_value' => $suggested,
            'reasoning' => $reasoning,
            'confidence' => $confidence,
            'status' => SeoSuggestion::STATUS_PENDING,
        ]);
    }

    /**
     * Terima & terpakai cadangan.
     */
    public function terimaCadangan(int $tenantId, int $suggestionId): SeoMetadata
    {
        $cadangan = SeoSuggestion::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($suggestionId);

        $metadata = SeoMetadata::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($cadangan->seo_metadata_id);

        $metadata->update([$cadangan->field => $cadangan->suggested_value]);

        $cadangan->update([
            'status' => SeoSuggestion::STATUS_APPLIED,
            'applied_at' => now(),
        ]);

        // Audit semula
        $this->auditMetadata($tenantId, $metadata->fresh());

        return $metadata->fresh();
    }

    /**
     * Tolak cadangan.
     */
    public function tolakCadangan(int $tenantId, int $suggestionId): SeoSuggestion
    {
        $cadangan = SeoSuggestion::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($suggestionId);

        $cadangan->update(['status' => SeoSuggestion::STATUS_REJECTED]);

        return $cadangan->fresh();
    }

    /**
     * Jana schema.org JSON-LD untuk objek.
     */
    public function janaJsonLd(string $objectType, array $data): array
    {
        $base = [
            '@context' => 'https://schema.org',
        ];

        return match ($objectType) {
            SeoMetadata::TYPE_PRODUCT => array_merge($base, [
                '@type' => 'Product',
                'name' => $data['name'] ?? '',
                'description' => $data['description'] ?? '',
                'sku' => $data['sku'] ?? null,
                'image' => $data['image'] ?? null,
                'brand' => isset($data['brand']) ? ['@type' => 'Brand', 'name' => $data['brand']] : null,
                'offers' => [
                    '@type' => 'Offer',
                    'price' => $data['price'] ?? null,
                    'priceCurrency' => $data['currency'] ?? 'MYR',
                    'availability' => $data['availability'] ?? 'https://schema.org/InStock',
                ],
            ]),
            SeoMetadata::TYPE_ARTICLE, SeoMetadata::TYPE_BLOG_POST => array_merge($base, [
                '@type' => 'Article',
                'headline' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'image' => $data['image'] ?? null,
                'datePublished' => $data['published_at'] ?? null,
                'dateModified' => $data['updated_at'] ?? null,
                'author' => isset($data['author']) ? ['@type' => 'Person', 'name' => $data['author']] : null,
                'publisher' => isset($data['publisher']) ? ['@type' => 'Organization', 'name' => $data['publisher']] : null,
            ]),
            SeoMetadata::TYPE_VIDEO => array_merge($base, [
                '@type' => 'VideoObject',
                'name' => $data['title'] ?? '',
                'description' => $data['description'] ?? '',
                'thumbnailUrl' => $data['thumbnail'] ?? null,
                'uploadDate' => $data['published_at'] ?? null,
                'duration' => $data['duration'] ?? null,
            ]),
            SeoMetadata::TYPE_CATEGORY => array_merge($base, [
                '@type' => 'CollectionPage',
                'name' => $data['title'] ?? '',
                'description' => $data['description'] ?? null,
            ]),
            default => array_merge($base, [
                '@type' => 'WebPage',
                'name' => $data['title'] ?? '',
                'description' => $data['description'] ?? null,
            ]),
        };
    }

    /**
     * Jana sitemap XML.
     */
    public function janaSitemap(int $tenantId, ?string $objectType = null): string
    {
        $query = SeoSitemap::query()->where('tenant_id', $tenantId);

        if ($objectType) {
            $query->where('object_type', $objectType);
        }

        $urls = $query->orderByDesc('priority')->get();

        $xml = ['<?xml version="1.0" encoding="UTF-8"?>'];
        $xml[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $u) {
            $xml[] = '  <url>';
            $xml[] = '    <loc>' . htmlspecialchars($u->url, ENT_XML1) . '</loc>';

            if ($u->lastmod) {
                $xml[] = '    <lastmod>' . $u->lastmod->toAtomString() . '</lastmod>';
            }

            $xml[] = '    <changefreq>' . $u->changefreq . '</changefreq>';
            $xml[] = '    <priority>' . number_format((float) $u->priority, 1) . '</priority>';
            $xml[] = '  </url>';
        }

        $xml[] = '</urlset>';

        return implode("\n", $xml);
    }

    /**
     * Tambah URL ke sitemap.
     */
    public function tambahSitemap(int $tenantId, string $objectType, int $objectId, string $url, string $changefreq = 'weekly', float $priority = 0.5): SeoSitemap
    {
        return SeoSitemap::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'object_type' => $objectType,
                'object_id' => $objectId,
            ],
            [
                'url' => $url,
                'changefreq' => $changefreq,
                'priority' => $priority,
                'lastmod' => now(),
            ]
        );
    }

    /**
     * Audit SEO seluruh tenant — semua objek.
     */
    public function auditTenant(int $tenantId): array
    {
        $metadata = SeoMetadata::query()->where('tenant_id', $tenantId)->get();

        $jumlahIsu = 0;
        $skor = [];

        foreach ($metadata as $m) {
            $isu = $this->auditMetadata($tenantId, $m);
            $jumlahIsu += count($isu);
            $skor[] = (float) $m->fresh()->seo_score;
        }

        $purata = $skor ? round(array_sum($skor) / count($skor), 2) : 0.0;

        $issues = SeoAuditIssue::query()
            ->where('tenant_id', $tenantId)
            ->where('is_resolved', false)
            ->get()
            ->groupBy('severity')
            ->map(fn ($g) => $g->count())
            ->toArray();

        return [
            'jumlah_metadata' => $metadata->count(),
            'jumlah_isu' => $jumlahIsu,
            'purata_skor' => $purata,
            'isu_terbuka' => SeoAuditIssue::query()
                ->where('tenant_id', $tenantId)
                ->where('is_resolved', false)
                ->count(),
            'ikut_keseriusan' => $issues,
        ];
    }

    /**
     * Preview SERP — paparan mesra enjin carian.
     */
    public function previewSerp(SeoMetadata $metadata): array
    {
        $title = $metadata->title ?: '(Tiada tajuk)';
        $desc = $metadata->description ?: '(Tiada description — enjin carian akan jana sendiri)';
        $url = $metadata->canonical_url ?: 'https://contoh.my/halaman';

        return [
            'google' => [
                'title' => mb_substr($title, 0, 60),
                'title_truncated' => mb_strlen($title) > 60,
                'description' => mb_substr($desc, 0, 160),
                'description_truncated' => mb_strlen($desc) > 160,
                'url' => $url,
                'breadcrumbs' => ['AI BIJAN', ucfirst($metadata->object_type)],
            ],
            'facebook' => [
                'title' => $metadata->og_title ?: $title,
                'description' => $metadata->og_description ?: $desc,
                'image' => $metadata->og_image_url,
                'url' => $url,
            ],
            'whatsapp' => [
                'title' => $metadata->og_title ?: $title,
                'description' => mb_substr($metadata->og_description ?: $desc, 0, 120),
                'image' => $metadata->og_image_url,
            ],
            'seo_score' => (float) $metadata->seo_score,
        ];
    }

    /**
     * Ekstrak JSON daripada output AI (yang mungkin dibalut markdown).
     */
    private function ekstrakJson(string $teks): array
    {
        // Cuba JSON terus
        $decoded = json_decode($teks, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // Cari blok {...}
        if (preg_match('/\{.*\}/s', $teks, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function titleMax(): int
    {
        return (int) config('seo.limits.title_max', self::TITLE_MAX);
    }
}
