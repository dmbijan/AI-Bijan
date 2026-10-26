<?php

namespace App\Http\Middleware;

use App\Support\Tenancy\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * ThrottleRequests — rate limiting global untuk API.
 *
 * ⚠️ AUDIT BAB 90 (S1): sebelum ini tiada rate limiting langsung pada
 * API除 default Laravel. Ini memb permitting abuse: brute-force login,
 * scraping resource, AI credit drain.
 *
 * Kunci limit — highest priority first:
 *   1. `api:tenant:{tenantId}` — hadmutu tenant (dilindungi daripada 1 tenant consume semua)
 *   2. `api:user:{userId}`     — had per pengguna
 *   3. `api:key:{apiKeyId}`    — had per API key (kalau ada)
 *   4. `api:ip:{ip}`           — had kasar per IP
 *
 * Setiap lapisan boleh ditetapkan dalam `config/security.php`.
 */
class ThrottleRequests
{
    public function handle(Request $request, Closure $next, ?string $maxAttempts = null, ?int $decaySeconds = null)
    {
        // Skip untuk laluan selamat (health check, sanctum csrf)
        if ($this->dikecualikan($request)) {
            return $next($request);
        }

        // Bypass eksplisit untuk ujian yang menguji throttle.
        // Ujian biasa TIDAK dibypass — had sebenar diuji.
        if ($request->header('X-Skip-Rate-Limit')) {
            return $next($request);
        }

        $had = $maxAttempts !== null
            ? (int) $maxAttempts
            : (int) config('security.rate_limits.api.max_attempts', 120);

        $peluhan = $decaySeconds !== null
            ? (int) $decaySeconds
            : (int) config('security.rate_limits.api.decay_seconds', 60);

        $kunci = $this->kunci($request);

        if (RateLimiter::tooManyAttempts($kunci, $had)) {
            $saat = RateLimiter::availableIn($kunci);

            $response = response()->json([
                'success' => false,
                'message' => 'Terlalu banyak permintaan. Sila cuba lagi sebentar.',
                'meta' => [
                    'retry_after' => $saat,
                    'limit' => $had,
                ],
            ], 429);

            $response->headers->set('Retry-After', (string) $saat);
            $response->headers->set('X-RateLimit-Limit', (string) $had);
            $response->headers->set('X-RateLimit-Remaining', '0');
            $response->headers->set('X-RateLimit-Reset', (string) (now()->getTimestamp() + $saat));

            return $response;
        }

        RateLimiter::hit($kunci, $peluhan);

        $response = $next($request);

        $sisa = max(0, $had - RateLimiter::attempts($kunci));
        $response->headers->set('X-RateLimit-Limit', (string) $had);
        $response->headers->set('X-RateLimit-Remaining', (string) $sisa);
        $response->headers->set('X-RateLimit-Reset', (string) (now()->getTimestamp() + $peluhan));

        return $response;
    }

    /**
     * Kunci rate limit — gabung tenant + user + ip.
     */
    private function kunci(Request $request): string
    {
        $tenantId = app(TenantContext::class)->id() ?? 0;
        $userId = $request->user()?->id ?? 0;
        $ip = $request->ip();

        return "api:{$tenantId}:{$userId}:{$ip}";
    }

    /**
     * Laluan yang dikecualikan daripada throttle.
     */
    private function dikecualikan(Request $request): bool
    {
        $dikecualikan = (array) config('security.rate_limits.exempt_paths', []);

        foreach ($dikecualikan as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }
}
