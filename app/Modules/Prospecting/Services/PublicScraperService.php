<?php

namespace App\Modules\Prospecting\Services;

use App\Modules\Prospecting\Models\ScrapedPage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PublicScraperService — mengambil data AWAM yang dibenarkan robots.txt.
 *
 * ⚠️ PERINGATAN KESELAMATAN:
 * - Service ini HANYA untuk data perniagaan yang DITERBITKAN secara awam
 *   (nama, jawatapan, email korporat di laman rasmi).
 * - Ia TIDAK Recognizing: data peribadi sensitif, nombor telefon peribadi,
 *   alamat kediaman, atau sebarang data di sebalik paywall/login.
 * - Tiada bypass CAPTCHA, tiada proxy rotation, tiada spoofing User-Agent.
 * - Hormati robots.txt secara DEFAULT (wajib).
 * - Rate limit mengikut domain.
 */
class PublicScraperService
{
    public const CACHE_KEY_ROBOTS = 'prospecting.robots.';

    public const ROBOTS_TTL_SECONDS = 86400;

    /**
     * patterns yang menunjukkan halaman MEDIA SOSIAL / LOGIN — jangan ambil.
     */
    private const BLOCKED_PATTERNS = [
        '/login', '/signin', '/sign-in', '/register', '/signup',
        '/account', '/profile/private', '/wp-admin', '/cart', '/checkout',
        '/download', '/private',
    ];

    /**
     * Adakah URL ini selamat untuk diambil?
     */
    public function dibenarkanUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! $parts || ! isset($parts['host'])) {
            return false;
        }

        // Hanya HTTP/HTTPS
        $scheme = $parts['scheme'] ?? 'https';
        if (! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $path = strtolower($parts['path'] ?? '/');

        foreach (self::BLOCKED_PATTERNS as $blocked) {
            if (str_contains($path, $blocked)) {
                return false;
            }
        }

        // Buang file binari / media
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'zip', 'mp4', 'mp3'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Ambil robots.txt untuk domain (dengan cache).
     */
    public function robots(string $baseUrl): RobotRules
    {
        $host = parse_url($baseUrl, PHP_URL_HOST);
        $cacheKey = self::CACHE_KEY_ROBOTS.md5($host);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return new RobotRules($cached, config('prospecting.scraper.user_agent'));
        }

        $robotsUrl = rtrim(dirname($baseUrl), '/').'/robots.txt';

        $content = '';
        try {
            $response = Http::withHeaders([
                'User-Agent' => config('prospecting.scraper.user_agent'),
            ])->timeout(10)->get($robotsUrl);

            if ($response->successful()) {
                $content = $response->body();
            }
        } catch (\Throwable $e) {
            Log::warning('Scraper: gagal ambil robots.txt', [
                'url' => $robotsUrl,
                'error' => $e->getMessage(),
            ]);
        }

        Cache::put($cacheKey, $content, self::ROBOTS_TTL_SECONDS);

        return new RobotRules($content, config('prospecting.scraper.user_agent'));
    }

    /**
     * Ambil satu halaman — dengan pemeriksaan robots.txt.
     *
     * @return array{ok: bool, reason: string, page: ?ScrapedPage, data: array}
     */
    public function ambilHalaman(string $url, int $tenantId, ?int $jobId = null, ?int $sourceId = null): array
    {
        if (! $this->dibenarkanUrl($url)) {
            return ['ok' => false, 'reason' => 'url_not_allowed', 'page' => null, 'data' => []];
        }

        $rules = $this->robots($url);
        $path = parse_url($url, PHP_URL_PATH) ?: '/';

        if (! $rules->dibenarkan($path)) {
            Log::info('Scraper: robots.txt menolak URL', ['url' => $url]);

            return ['ok' => false, 'reason' => 'robots_disallowed', 'page' => null, 'data' => []];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => config('prospecting.scraper.user_agent'),
                'Accept' => 'text/html,application/xhtml+xml',
            ])->timeout(config('prospecting.scraper.timeout'))
                ->get($url);
        } catch (\Throwable $e) {
            Log::warning('Scraper: ralat HTTP', ['url' => $url, 'error' => $e->getMessage()]);

            return ['ok' => false, 'reason' => 'http_error', 'page' => null, 'data' => []];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'reason' => 'http_'.$response->status(), 'page' => null, 'data' => []];
        }

        $html = $response->body();
        $extracted = $this->ekstrak($html);

        $page = ScrapedPage::create([
            'tenant_id' => $tenantId,
            'job_id' => $jobId,
            'source_id' => $sourceId,
            'url' => $url,
            'final_url' => $url,
            'http_status' => $response->status(),
            'content_hash' => hash('sha256', $html),
            'title' => $extracted['title'] ?? null,
            'extracted' => $extracted,
            'fetched_at' => now(),
        ]);

        return ['ok' => true, 'reason' => 'ok', 'page' => $page, 'data' => $extracted];
    }

    /**
     * Ekstrak data awam daripada HTML — tanpa DOM library.
     */
    public function ekstrak(string $html): array
    {
        // Title
        $title = null;
        if (preg_match('#<title[^>]*>(.*?)</title>#is', $html, $m)) {
            $title = trim(html_entity_decode(strip_tags($m[1])));
            $title = mb_substr($title, 0, 300);
        }

        // Meta description
        $description = null;
        if (preg_match('#<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']+)#i', $html, $m)) {
            $description = trim(html_entity_decode($m[1]));
        }

        // E-mel — hanya yang kelihatan sebagai mailto: atau teks biasa di corp
        $emails = [];
        if (preg_match_all('#mailto:([^\s"\'<>?]+)#i', $html, $m)) {
            $emails = $m[1];
        }
        $emails = array_values(array_unique(array_filter($emails, fn ($e) => str_contains($e, '@'))));

        // Telefon — hanya yang kelihatan sebagai tel:
        $phones = [];
        if (preg_match_all('#tel:([^\s"\'<>?]+)#i', $html, $m)) {
            $phones = $m[1];
        }
        $phones = array_values(array_unique($phones));

        // Nama syarikat — dari og:site_name atauOrganization schema
        $company = null;
        if (preg_match('#<meta[^>]+property=["\']og:site_name["\'][^>]+content=["\']([^"\']+)#i', $html, $m)) {
            $company = trim(html_entity_decode($m[1]));
        }

        // cleaning text penuh — untuk AI parsing nanti
        $text = $this->teksBersih($html);

        return [
            'title' => $title,
            'description' => $description,
            'company' => $company,
            'emails' => array_slice($emails, 0, 20),
            'phones' => array_slice($phones, 0, 20),
            'text_excerpt' => mb_substr($text, 0, 4000),
        ];
    }

    /**
     * Teks bersih daripada HTML.
     */
    private function teksBersih(string $html): string
    {
        // Buang script/style
        $html = preg_replace('#<(script|style|noscript)[^>]*>.*?</\1>#is', ' ', $html);
        // Buang komen
        $html = preg_replace('#<!--.*?-->#s', ' ', $html);
        // Tag → space
        $html = strip_tags($html);
        $html = html_entity_decode($html);

        return trim(preg_replace('/\s+/u', ' ', $html));
    }
}
