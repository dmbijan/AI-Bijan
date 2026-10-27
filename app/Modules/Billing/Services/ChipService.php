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

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('billing.chip.base_url', 'https://gate.chip-in.asia/api/v1'), '/');
        $this->apiKey = config('billing.chip.api_key');
        $this->brandId = (string) config('billing.chip.brand_id', '');
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
                'total' => $this->kepadaUnit($data['amount']),
                'products' => [[
                    'name' => $data['product_name'] ?? 'Langganan AI BIJAN',
                    'quantity' => 1,
                    'price' => $this->kepadaUnit($data['amount']),
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

        $payload = $amount !== null ? ['amount' => $this->kepadaUnit($amount)] : [];

        return $this->panggil('post', '/purchases/' . rawurlencode($purchaseId) . '/refund/', $payload);
    }

    public function batal(string $purchaseId): array
    {
        $this->pastikanDikonfigurasi();

        return $this->panggil('post', '/purchases/' . rawurlencode($purchaseId) . '/cancel/', []);
    }

    /**
     * Sahkan tandatangan webhook CHIP.
     *
     * CHIP menandatangani `"{timestamp}.{raw_body}"` dengan RSA-SHA256
     * menggunakan private key mereka; kita sahkan dengan public key mereka.
     *
     * ⚠️ Jika `billing.chip.webhook_public_key` kosong, kita TOLAK semua
     * webhook — jangan sesekali terima tanpa pengesahan.
     */
    public function sahkanTandatangan(string $rawBody, ?string $signature, ?string $timestamp): bool
    {
        $publicKey = config('billing.chip.webhook_public_key');

        if (empty($publicKey) || empty($signature) || empty($timestamp)) {
            Log::warning('CHIP webhook ditolak: kunci awam/tandatangan/timestamp tiada.');

            return false;
        }

        // Elak replay: tolak jika timestamp lebih 5 minit
        $maxBeza = (int) config('billing.chip.webhook_tolerance_seconds', 300);
        if (abs(now()->timestamp - (int) $timestamp) > $maxBeza) {
            Log::warning('CHIP webhook ditolak: timestamp di luar toleransi.', [
                'beza' => now()->timestamp - (int) $timestamp,
            ]);

            return false;
        }

        $mesej = $timestamp . '.' . $rawBody;
        $tandatangan = base64_decode($signature, true);

        if ($tandatangan === false) {
            return false;
        }

        $kunci = openssl_pkey_get_public($publicKey);
        if ($kunci === false) {
            Log::error('CHIP webhook: kunci awam tidak sah.');

            return false;
        }

        $sah = openssl_verify($mesej, $tandatangan, $kunci, OPENSSL_ALGO_SHA256) === 1;

        if (! $sah) {
            Log::warning('CHIP webhook: tandatangan TIDAK SAH.');
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

    /** Tukar jumlah kepada nilai yang CHIP terima (ringgit, 2 titik perpuluhan). */
    private function kepadaUnit(float|int|string $amount): float
    {
        return round((float) $amount, 2);
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
