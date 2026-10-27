<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SecureHeaders — tambah header keselamatan HTTP pada setiap respons.
 *
 * ⚠️ AUDIT BAB 90 (S2/S3/S4): sebelum ini AI BIJAN TIDAK ada sebarang
 * security headers. Corak diambil daripada B-POS `SecureHeaders`
 * (rujukan reka bentuk sahaja — tiada kod disalin).
 *
 * Header yang ditambah:
 * - Content-Security-Policy (dengan nonce untuk script inline)
 * - Strict-Transport-Security (HSTS)
 * - X-Content-Type-Options: nosniff
 * - X-Frame-Options: DENY
 * - Referrer-Policy: strict-origin-when-cross-origin
 * - Permissions-Policy (hadkan API kamera/mikrofon/geolocation)
 * - Cross-Origin-Opener-Policy
 * - X-XSS-Protection (legacy, untuk browser lama)
 *
 * NOTA: CSP hanya diletak ketat pada respons HTML. Respons JSON API
 * hanya dapat header ringkas (CSP tidak berguna pada JSON).
 */
class SecureHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // ---- Header universal (HTML + JSON) ----
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self), payment=(self), usb=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        // HSTS — hanya jika sudah HTTPS (jangan patahkan dev HTTP)
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // ---- CSP hanya untuk HTML ----
        if ($this->mauHtml($response)) {
            // Hormati CSP yang sudah ditetapkan oleh aplikasi (contoh:
            // pratonton dokumen guna `default-src 'none'` yang lebih ketat).
            if (! $response->headers->has('Content-Security-Policy')) {
                $nonce = base64_encode(random_bytes(16));

                $response->headers->set('Content-Security-Policy', $this->csp($nonce));
                $response->headers->set('X-Nonce', $nonce);
            }
        }

        return $response;
    }

    /**
     * Adakah respons ini HTML? (JSON tidak perlukan CSP)
     */
    private function mauHtml($response): bool
    {
        $contentType = (string) $response->headers->get('Content-Type', '');

        return str_contains($contentType, 'text/html');
    }

    /**
     * Bina CSP daripada config.
     *
     * `nonce` perlu dimasukkan ke tag `<script>` — frontend perlu
     * baca header `X-Nonce` atau letak nonce dalam templat.
     */
    private function csp(string $nonce): string
    {
        $cfg = config('security.csp', []);

        // ⚠️ Alpine.js (teras Livewire/Filament) menggunakan `new Function()`
        // untuk menilai ekspresi `x-*` dan `wire:*`. Tanpa `'unsafe-eval'`,
        // SEMUA komponen Livewire rosak (butang tidak berfungsi).
        //
        // ⚠️ Livewire menyuntik `<script>` inline TANPA nonce. Dalam CSP,
        // jika `nonce` hadir, `'unsafe-inline'` DIABAIKAN — jadi inline
        // script gagal. Penyelesaian: JANGAN letak nonce, guna
        // `'unsafe-inline'` sahaja untuk panel.
        //
        // Skrip yang disuntik tetap mesti lulus `script-src`, dan
        // `object-src 'none'` + `base-uri 'self'` melindungi vektor biasa.
        $gunaNonce = (bool) ($cfg['use_nonce'] ?? false);
        $evalSrc = (bool) ($cfg['allow_unsafe_eval'] ?? true) ? " 'unsafe-eval'" : '';
        $inlineSrc = (bool) ($cfg['allow_unsafe_inline_scripts'] ?? true) ? " 'unsafe-inline'" : '';

        $scriptSrc = "script-src 'self'" . ($gunaNonce ? " 'nonce-{$nonce}'" : '') . $evalSrc . $inlineSrc;
        $scriptSrc .= $this->tambahan($cfg['script_extra'] ?? '');

        $directives = [
            "default-src 'self'",
            $scriptSrc,
            "style-src 'self' 'unsafe-inline'" . $this->tambahan($cfg['style_extra'] ?? ''),
            "img-src 'self' data: blob: https:" . $this->tambahan($cfg['img_extra'] ?? ''),
            "font-src 'self' data:" . $this->tambahan($cfg['font_extra'] ?? ''),
            "connect-src 'self'" . $this->tambahan($cfg['connect_extra'] ?? ''),
            $this->sumberFrame(),
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
            "upgrade-insecure-requests",
        ];

        return implode('; ', array_filter($directives));
    }

    private function sumberFrame(): string
    {
        $url = parse_url((string) config('openbot.url'));
        if (! is_array($url) || ! in_array($url['scheme'] ?? '', ['http', 'https'], true) || empty($url['host'])) {
            return "frame-src 'self'";
        }

        $origin = $url['scheme'].'://'.$url['host'];
        if (isset($url['port'])) {
            $origin .= ':'.$url['port'];
        }

        return "frame-src 'self' ".$origin;
    }

    /**
     * Bina sambungan direktif CSP yang bersih (tiada ruang berganda).
     */
    private function tambahan(string $nilai): string
    {
        $nilai = trim($nilai);

        return $nilai === '' ? '' : ' ' . $nilai;
    }
}
