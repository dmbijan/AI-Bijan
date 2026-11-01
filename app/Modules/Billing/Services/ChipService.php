<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\BillingCharge;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Klien CHIP Collect API.
 *
 * ⚠️ KUNCI API HANYA DI PELAYAN. Tidak pernah ke frontend, tidak pernah ke log.
 *
 * Endpoint CHIP (rujuk https://docs.chip-in.asia):
 *   POST /purchases/            — cipta invois (hosted checkout)
 *   GET  /purchases/{id}/       — status invois
 *   POST /purchases/{id}/refund/— bayaran balik
 *   POST /purchases/{id}/cancel/— batalkan invois belum bayar
 *
 * @see https://docs.chip-in.asia/chip-collect/api-reference
 */
class ChipService
{
    private string $baseUrl;
    private ?string $apiKey;
    private string $brandId;
    private ?string $publicKey;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('billing.chip.base_url', 'https://gate.chip-in.asia/api/v1'), '/');
        $this->apiKey = config('billing.chip.api_key');
        $this->brandId = (string) config('billing.chip.brand_id', '');
        $this->publicKey = config('billing.chip.webhook_public_key');
    }

    /** Servis CHIP dengan kunci milik kedai (bayaran pelanggan kedai → akaun CHIP kedai sendiri). */
    public static function denganKunci(string $apiKey, string $brandId, ?string $publicKey = null): self
    {
        $s = new self;
        $s->apiKey = $apiKey;
        $s->brandId = $brandId;
        $s->publicKey = $publicKey;

        return $s;
    }

    /** Kunci awam CHIP untuk mengesahkan callback (GET /public_key/). Juga membuktikan kunci API sah. */
    public function kunciAwam(): string
    {
        $this->pastikanDikonfigurasi();
        $respons = Http::withHeaders(['Authorization' => 'Bearer '.$this->apiKey])
            ->timeout((int) config('billing.chip.timeout', 30))->get($this->baseUrl.'/public_key/');
        if ($respons->failed()) {
            throw new RuntimeException('Kunci CHIP tidak sah atau CHIP tidak dapat dihubungi ('.$respons->status().').');
        }
        $k = trim((string) ($respons->json() ?? $respons->body()), "\" \n");

        return str_replace('\\n', "\n", $k);
    }

    public function dikonfigurasi(): bool
    {
        return ! empty($this->apiKey) && ! empty($this->brandId);
    }

    /**
     * Cipta invois (hosted checkout).
     *
     * @param  array{
     *   email: string, full_name?: string, phone?: string,
     *   amount: float|int, currency?: string, reference: string,
     *   success_url: string, failure_url: string, cancel_url?: string,
     *   metadata?: array, product_name?: string
     * }  $data
     * @return array{id: string, checkout_url: string, status: string, raw: array}
     */
    public function ciptaInvois(array $data): array
    {
        $this->pastikanDikonfigurasi();

        $payload = [
            'brand_id' => $this->brandId,
            'client' => array_filter([
                'email' => $data['email'],
                'full_name' => $data['full_name'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]),
            'purchase' => [
                'currency' => $data['currency'] ?? 'MYR',
                // CHIP menerima nilai dalam unit terkecil? TIDAK — ambil ringgit
                // sebagai integer/float. Rujuk dokumentasi: `total` ialah jumlah
                // dalam mata wang, bukan sen, untuk API v1 MYR.
                // CHIP: `products[].price` dalam SEN (integer); `total` dikira oleh CHIP daripada produk.
                // (Dulu dihantar dalam ringgit → RM388 dicaj sebagai RM3.88.)
                'products' => $data['products'] ?? [[
                    'name' => $data['product_name'] ?? 'Langganan AI BIJAN',
                    'quantity' => 1,
                    'price' => $this->kepadaSen($data['amount']),
                ]],
                'reference' => $data['reference'],
                'send_receipt' => true,
            ],
            'reference' => $data['reference'],
            'success_redirect' => $data['success_url'],
            'failure_redirect' => $data['failure_url'],
            'cancel_redirect' => $data['cancel_url'] ?? $data['failure_url'],
            // ⚠️ Webhook mesti disahkan — CHIP menandatangani dengan public key
            'send_params' => true,
        ];

        if (! empty($data['success_callback'])) {
            $payload['success_callback'] = $data['success_callback'];
        }

        if (! empty($data['metadata'])) {
            $payload['metadata'] = $data['metadata'];
        }

        $respons = $this->panggil('post', '/purchases/', $payload);

        return [
            'id' => (string) ($respons['id'] ?? ''),
            'checkout_url' => (string) ($respons['checkout_url'] ?? ''),
            'status' => (string) ($respons['status'] ?? 'created'),
            'raw' => $respons,
        ];
    }

    /**
     * Status invois.
     *
     * @return array{id: string, status: string, raw: array}
     */
    public function statusInvois(string $purchaseId): array
    {
        $this->pastikanDikonfigurasi();

        $respons = $this->panggil('get', '/purchases/' . rawurlencode($purchaseId) . '/');

        return [
            'id' => (string) ($respons['id'] ?? $purchaseId),
            'status' => (string) ($respons['status'] ?? 'unknown'),
            'raw' => $respons,
        ];
    }

    public function refund(string $purchaseId, ?float $amount = null): array
    {
        $this->pastikanDikonfigurasi();

        $payload = $amount !== null ? ['amount' => $this->kepadaSen($amount)] : [];

        return $this->panggil('post', '/purchases/' . rawurlencode($purchaseId) . '/refund/', $payload);
    }

    public function batal(string $purchaseId): array
    {
        $this->pastikanDikonfigurasi();

        return $this->panggil('post', '/purchases/' . rawurlencode($purchaseId) . '/cancel/', []);
    }

    /**
     * Sahkan tandatangan callback/webhook CHIP.
     *
     * Ikut dokumentasi CHIP: header `X-Signature` = base64 tandatangan RSA PKCS#1 v1.5 (SHA-256)
     * ke atas BADAN permintaan mentah. (Dulu disahkan ke atas "{timestamp}.{body}", jadi setiap
     * callback sebenar ditolak.) `$timestamp` diabaikan — CHIP tidak menghantarnya; ulangan
     * dihalang oleh semakan idempoten pada id pembelian.
     *
     * ⚠️ Tiada kunci awam → TOLAK. Jangan sesekali terima tanpa pengesahan.
     */
    public function sahkanTandatangan(string $rawBody, ?string $signature, ?string $timestamp = null): bool
    {
        if (empty($this->publicKey) || empty($signature)) {
            Log::warning('CHIP callback ditolak: kunci awam/tandatangan tiada.');

            return false;
        }

        $tandatangan = base64_decode($signature, true);
        if ($tandatangan === false) {
            return false;
        }

        $kunci = openssl_pkey_get_public($this->publicKey);
        if ($kunci === false) {
            Log::error('CHIP callback: kunci awam tidak sah.');

            return false;
        }

        $sah = openssl_verify($rawBody, $tandatangan, $kunci, OPENSSL_ALGO_SHA256) === 1;
        if (! $sah) {
            Log::warning('CHIP callback: tandatangan TIDAK SAH.');
        }

        return $sah;
    }

    // ---------------------------------------------------------------
    // Dalaman
    // ---------------------------------------------------------------

    private function pastikanDikonfigurasi(): void
    {
        if (! $this->dikonfigurasi()) {
            throw new RuntimeException(
                'CHIP belum dikonfigurasi. Set CHIP_API_KEY dan CHIP_BRAND_ID dalam .env'
            );
        }
    }

    /** Ringgit → sen (integer), unit yang CHIP terima. RM388.00 → 38800. */
    public function kepadaSen(float|int|string $amount): int
    {
        return (int) round(((float) $amount) * 100);
    }

    private function panggil(string $method, string $path, array $payload = []): array
    {
        $url = $this->baseUrl . $path;

        $permintaan = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])
            ->timeout((int) config('billing.chip.timeout', 30))
            ->retry(2, 300, throw: false);

        $respons = $method === 'get'
            ? $permintaan->get($url)
            : $permintaan->post($url, $payload);

        if ($respons->failed()) {
            // ⚠️ JANGAN log payload penuh — mungkin ada e-mel/telefon.
            Log::error('CHIP API gagal', [
                'path' => $path,
                'status' => $respons->status(),
                'body' => mb_substr($respons->body(), 0, 500),
            ]);

            throw new RuntimeException(
                'CHIP API gagal (' . $respons->status() . '): ' . mb_substr($respons->body(), 0, 200)
            );
        }

        return (array) $respons->json();
    }
}
