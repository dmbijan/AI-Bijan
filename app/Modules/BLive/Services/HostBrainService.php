<?php

namespace App\Modules\BLive\Services;

use App\Modules\AIGateway\Services\GatewayService;
use App\Modules\BLive\Models\HostPersonality;
use App\Modules\BLive\Models\LiveMessage;
use App\Modules\BLive\Models\LiveSession;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Log;

/**
 * HostBrainService - modul B-AI Brain.
 *
 * Gelung keputusan (blueprint bahagian 9.3):
 *
 *   Receive Live Event
 *          v
 *   Classify Intent
 *          v
 *   Retrieve Knowledge
 *          v
 *   Check Business Rules
 *          v
 *   Check Human Instructions
 *          v
 *   Generate Response
 *          v
 *   Safety & Fact Validation
 *          v
 *   Voice + Animation
 *          v
 *   Broadcast
 *          v
 *   Record Analytics
 *
 * Prinsip keselamatan yang mesti dipegang:
 *   - AI tidak boleh melangkau arahan berhenti manusia.
 *   - AI tidak boleh mencipta harga / fakta produk di luar knowledge base.
 *   - Jika maklumat tidak ditemui, AI mengaku tidak pasti dan eskalasi.
 */
class HostBrainService
{
    /**
     * Corak harga - digunakan untuk mengesan AI yang mencipta harga.
     *
     * @var array<int, string>
     */
    protected array $corakHarga = [
        '/\brm\s?\d[\d,.]*/i',
        '/\b(?:price|harga)[:\s]*(?:only|just|cuma)?\s*(?:rm|usd|sgd)?\s*\d/i',
        '/\bdiskaun\s+\d+\s*%/i',
        '/\b\d+\s*%\s*(?:off|diskaun)/i',
    ];

    /**
     * Frasa yang menunjukkan AI tidak pasti - peraturan ini.
     *
     * @var array<int, string>
     */
    protected array $frasaTidakPasti = [
        'tidak pasti', 'tidakpasti', 'i am not sure', "i don't know", 'i do not know',
        'tidak ada dalam', 'not available in our', 'akan semak', 'let me check',
    ];

    public function __construct(
        private TenantContext $ctx,
        private GatewayService $gateway,
    ) {}

    /**
     * Hasilkan jawapan hos untuk satu peristiwa live.
     *
     * @param  array<string, mixed>  $event
     * @return array{
     *     success: bool, content: string, intent: string, confidence: float,
     *     should_speak: bool, escalate_to_human: bool, reason: ?string,
     *     tokens_used: int, cost_rm: float
     * }
     */
    public function jawab(array $event, ?LiveSession $sesi = null): array
    {
        $tenantId = (int) $this->ctx->id();

        if ($tenantId <= 0) {
            return $this->gagal('Tiada tenant aktif.', 'no_tenant');
        }

        $soalan = trim((string) ($event['content'] ?? ''));

        if ($sesi !== null && $sesi->status !== LiveSession::STATUS_LIVE) {
            return $this->gagal('Sesi tidak live.', 'session_not_live');
        }

        if ($sesi !== null && $sesi->session_mode === LiveSession::MODE_HUMAN_LED) {
            return $this->gagal('Sesi dikawal manusia.', 'human_led');
        }

        $personaliti = $this->personaliti($sesi);
        $intent = $this->klasifikasiIntent($soalan);

        // --- Retrieve Knowledge -------------------------------------------
        $pengetahuan = $this->ambilPengetahuan($sesi, $soalan);

        // --- Business Rules ------------------------------------------------
        $semakPeraturan = $this->semakPeraturanBisnes($pengetahuan, $soalan);

        if (! $semakPeraturan['ok']) {
            $this->catat($sesi, $soalan, $intent, 0.0, true, true, $semakPeraturan['reason']);

            return $this->gagal($semakPeraturan['reason'], $semakPeraturan['code'] ?? 'business_rule', escalate: true);
        }

        // --- Generate Response ---------------------------------------------
        $mesej = $this->binaMesejSistem($personaliti, $sesi, $pengetahuan, $event);

        $hasil = $this->gateway->call(
            $tenantId,
            $mesej,
            (string) config('blive.brain.task_type', 'live_host'),
            [
                'temperature' => (float) config('blive.brain.temperature', 0.7),
                'max_output_tokens' => (int) config('blive.brain.max_tokens', 400),
                'source_module' => 'blive',
                'agent_key' => 'host_brain',
                'correlation_id' => $event['correlation_id'] ?? null,
            ]
        );

        if (! $hasil['success']) {
            Log::warning('BLive: gateway gagal', ['error' => $hasil['error'] ?? 'tidak diketahui']);

            return $this->gagal(
                $hasil['error'] ?? 'Model AI tidak tersedia.',
                'gateway_failed',
                escalate: (bool) config('blive.brain.fallback_to_human', true)
            );
        }

        $kandungan = trim((string) $hasil['content']);

        // Buang penanda simulasi gateway supaya tidak diperdengarkan secara langsung.
        if (str_starts_with($kandungan, '[SIMULASI]')) {
            $kandungan = $this->susunSimulasi($kandungan, $soalan, $intent, $pengetahuan);
        }

        // --- Safety & Fact Validation --------------------------------------
        $keselamatan = $this->sahkanKeselamatan($kandungan, $pengetahuan, $soalan);

        $keyakinan = $this->hitungKeyakinan($kandungan, $pengetahuan, $intent);

        $perluEskalasi = $keselamatan['escalate']
            || ($keyakinan < (float) config('blive.brain.confidence_threshold', 0.55))
            || empty($pengetahuan['facts']);

        $perluBercakap = ! $perluEskalasi || ! config('blive.brain.fallback_to_human', true);

        $token = (int) ($hasil['usage']['total_tokens'] ?? 0);
        $kos = $this->anggaranKos($token);

        $this->catat(
            $sesi,
            $kandungan,
            $intent,
            $keyakinan,
            $perluBercakap,
            $perluEskalasi,
            $keselamatan['reason'],
            $token,
            $kos,
            LiveMessage::ROLE_HOST,
            LiveMessage::SOURCE_AI
        );

        return [
            'success' => true,
            'content' => $kandungan,
            'intent' => $intent,
            'confidence' => $keyakinan,
            'should_speak' => $perluBercakap,
            'escalate_to_human' => $perluEskalasi,
            'reason' => $keselamatan['reason'],
            'tokens_used' => $token,
            'cost_rm' => $kos,
        ];
    }

    /**
     * Klasifikasi intent mesej penonton.
     */
    public function klasifikasiIntent(string $teks): string
    {
        $teks = mb_strtolower($teks);

        $peta = [
            'greeting' => ['hello', 'helo', 'hi', 'hai', 'salam', 'assalamualaikum', 'good morning'],
            'price_inquiry' => ['harga', 'price', 'berapa', 'kos', 'bayaran', 'murah'],
            'product_inquiry' => ['produk', 'product', 'barang', 'item', 'adakah ada', 'stock'],
            'order_intent' => ['beli', 'order', 'pesan', 'mahu', 'nak ambil', 'checkout'],
            'shipping' => ['hantar', 'penghantaran', 'delivery', 'pos', 'courier', 'sampai'],
            'payment' => ['bayar', 'payment', 'transfer', 'nombor akaun', 'ewallet', 'tng'],
            'complaint' => ['aduan', 'complaint', 'kecewa', 'buruk', 'lambat', 'problem'],
            'thank_you' => ['terima kasih', 'thank you', 'thanks', 'tq'],
            'farewell' => ['bye', 'goodbye', 'selamat tinggal', 'jumpa lagi'],
        ];

        foreach ($peta as $intent => $kataKunci) {
            foreach ($kataKunci as $kata) {
                if (str_contains($teks, $kata)) {
                    return $intent;
                }
            }
        }

        return 'general';
    }

    /**
     * Semak peraturan perniagaan - harga, diskaun, produk diluluskan.
     *
     * @param  array<string, mixed>  $pengetahuan
     * @return array{ok: bool, reason: ?string, code: ?string}
     */
    public function semakPeraturanBisnes(array $pengetahuan, string $soalan): array
    {
        if (config('blive.safety.require_approval_for_discounts', true)) {
            $adaDiskaun = false;

            foreach ($this->corakHarga as $corak) {
                if (preg_match($corak, $soalan)) {
                    $adaDiskaun = true;
                    break;
                }
            }

            if ($adaDiskaun && empty($pengetahuan['approved_prices'])) {
                return [
                    'ok' => false,
                    'reason' => 'Tiada harga diluluskan untuk soalan ini. Perlu semakan manusia.',
                    'code' => 'price_not_approved',
                ];
            }
        }

        return ['ok' => true, 'reason' => null, 'code' => null];
    }

    /**
     * Sahkan jawapan AI: tiada harga karangan, ada pengisytiharan apabila perlu.
     *
     * @param  array<string, mixed>  $pengetahuan
     * @return array{escalate: bool, reason: ?string}
     */
    public function sahkanKeselamatan(string $kandungan, array $pengetahuan, string $soalan): array
    {
        $kandunganLower = mb_strtolower($kandungan);

        // 1. Jangan karang harga di luar senarai diluluskan.
        if (config('blive.safety.block_price_invention', true)) {
            if (preg_match('/\brm\s?\d[\d,.]*/i', $kandungan)) {
                $adaDiluluskan = ! empty($pengetahuan['approved_prices']);

                if (! $adaDiluluskan) {
                    return [
                        'escalate' => true,
                        'reason' => 'Jawapan mengandungi harga yang tiada dalam knowledge base.',
                    ];
                }
            }
        }

        // 2. Pengisytiharan AI - hos digital mesti didedahkan.
        if (config('blive.safety.require_ai_disclosure', true)) {
            $teksDedah = (string) config('blive.safety.disclosure_text');
            $perluDedah = ! str_contains($kandunganLower, 'ai')
                && ! str_contains($kandunganLower, 'artificial intelligence')
                && ! str_contains($kandunganLower, $this->normalise($teksDedah));

            if ($perluDedah && empty($pengetahuan['disclosure_done'])) {
                // Tanda untuk lapisan penstriman, bukan untuk digayakan di sini.
                $pengetahuan['disclosure_done'] = false;
            }
        }

        // 3. Jawapan yang mengakui tidak pasti mesti diekalasi.
        foreach ($this->frasaTidakPasti as $frasa) {
            if (str_contains($kandunganLower, $frasa)) {
                return [
                    'escalate' => true,
                    'reason' => 'AI mengakui maklumat tidak pasti - perlu semakan manusia.',
                ];
            }
        }

        return ['escalate' => false, 'reason' => null];
    }

    /**
     * Ambil pengetahuan daripada knowledge base syarikat.
     *
     * @return array{facts: array<int, string>, approved_prices: array<int, float>, disclosure_done: bool}
     */
    public function ambilPengetahuan(?LiveSession $sesi, string $soalan): array
    {
        $facts = [];
        $approved = [];

        $produkIds = $sesi?->product_ids ?: [];

        foreach ((array) $produkIds as $produkId) {
            $produk = $this->cariProduk((int) $produkId);

            if ($produk === null) {
                continue;
            }

            $facts[] = $produk['nama'].': '.$produk['penerangan'];

            if ($produk['approved_price'] !== null) {
                $approved[] = (float) $produk['approved_price'];
            }
        }

        // Semak berasaskan tenant knowledge base jika syarikat ada.
        $kb = $this->cariKnowledgeBase($soalan);

        foreach ($kb as $fakta) {
            $facts[] = $fakta;
        }

        return [
            'facts' => $facts,
            'approved_prices' => $approved,
            'disclosure_done' => false,
        ];
    }

    /**
     * Bina mesej sistem untuk LLM.
     *
     * @param  array<string, mixed>  $pengetahuan
     * @param  array<string, mixed>  $event
     * @return array<int, array<string, string>>
     */
    protected function binaMesejSistem(
        ?HostPersonality $personaliti,
        ?LiveSession $sesi,
        array $pengetahuan,
        array $event,
    ): array {
        $baris = [];

        $baris[] = [
            'role' => 'system',
            'content' => $this->susunArahanSistem($personaliti, $sesi, $pengetahuan),
        ];

        if (! empty($event['instruction'])) {
            $baris[] = [
                'role' => 'user',
                'content' => 'Arahan ejari: '.$event['instruction'],
            ];
        }

        $soalan = trim((string) ($event['content'] ?? ''));

        if ($soalan !== '') {
            $baris[] = [
                'role' => 'user',
                'content' => $soalan,
            ];
        }

        return $baris;
    }

    /**
     * Susun arahan sistem untuk hos live.
     *
     * @param  array<string, mixed>  $pengetahuan
     */
    protected function susunArahanSistem(
        ?HostPersonality $personaliti,
        ?LiveSession $sesi,
        array $pengetahuan,
    ): string {
        $bahasa = $personaliti?->locale ?: 'ms-MY';

        $garis = [
            'Anda ialah hos AI untuk sessi=live langsung. Anda bercakap secara ringkas dan mesra.',
            'Bahasa utama: '.$bahasa.'. Boleh bercampur dengan English jika sesuai.',
            'Jangan sebut harga yang tidak tersenarai dalam knowledge base.',
            'Jangan sebut diskaun, promosi, atau janji yang tidak diluluskan.',
            'Jangan seborkan maklumat peribadi pelanggan.',
        ];

        if ($personaliti?->system_instructions) {
            $garis[] = trim($personaliti->system_instructions);
        }

        if (! empty($pengetahuan['facts'])) {
            $garis[] = 'Maklumat produk yang diluluskan:';
            foreach ($pengetahuan['facts'] as $fakta) {
                $garis[] = '- '.$fakta;
            }
        } else {
            $garis[] = 'Tiada maklumat produk diluluskan dalam sesi ini. Jika ditanya tentang produk, akui anda tidak pasti.';
        }

        if (! empty($pengetahuan['approved_prices'])) {
            $harga = array_map(fn ($h) => 'RM'.number_format((float) $h, 2), $pengetahuan['approved_prices']);
            $garis[] = 'Harga yang BOLEH disebut: '.implode(', ', $harga).'.';
        }

        if ($sesi !== null && $sesi->language) {
            $garis[] = 'Bahasa yang diminta untuk jawapan: '.$sesi->language.'.';
        }

        $garis[] = 'Jawab dalam 1-3 ayat pendek. Jangan gunakan penanda seperti [SIMULASI] atau "(" .';

        return implode("\n", $garis);
    }

    /**
     * Susun jawapan simulasi apabila AI Gateway tidak diaktifkan.
     *
     * @param  array<string, mixed>  $pengetahuan
     */
    protected function susunSimulasi(string $kandungan, string $soalan, string $intent, array $pengetahuan): string
    {
        if (! empty($pengetahuan['facts'])) {
            return 'Saya ada maklumat ini untuk anda: '.implode(' ', array_slice($pengetahuan['facts'], 0, 2));
        }

        $peta = [
            'greeting' => 'Selamat datang ke live kami! Ada apa yang boleh saya bantu?',
            'price_inquiry' => 'Saya akan semak maklumat harga untuk anda.',
            'order_intent' => 'Baik, saya akan bantu dengan tempahan anda.',
            'thank_you' => 'Terima kasih sudah menonton!',
            'farewell' => 'Selamat tinggal dan jumpa lagi!',
        ];

        return $peta[$intent] ?? 'Saya sudah menerima soalan anda dan akan semak maklumat berkaitan.';
    }

    /**
     * Hitung keyakinan jawapan.
     *
     * @param  array<string, mixed>  $pengetahuan
     */
    protected function hitungKeyakinan(string $kandungan, array $pengetahuan, string $intent): float
    {
        $keyakinan = 0.5;

        if (! empty($pengetahuan['facts'])) {
            $keyakinan += 0.25;
        }

        if (! empty($pengetahuan['approved_prices'])) {
            $keyakinan += 0.1;
        }

        if ($intent !== 'general') {
            $keyakinan += 0.05;
        }

        if (trim($kandungan) === '') {
            $keyakinan = 0.0;
        }

        return round(min(1.0, $keyakinan), 4);
    }

    /**
     * Cari produk yang diluluskan (dari modul Commerce / CRM jika wujud).
     *
     * @return array{nama: string, penerangan: string, approved_price: ?float}|null
     */
    protected function cariProduk(int $produkId): ?array
    {
        $kelas = 'App\\Models\\Item';

        if (! class_exists($kelas)) {
            return null;
        }

        $item = $kelas::query()->find($produkId);

        if ($item === null) {
            return null;
        }

        $data = $item->toArray();
        $hargaDisyorkan = $data['approved_price'] ?? $data['sell_price'] ?? $data['price'] ?? null;

        return [
            'nama' => (string) ($data['name'] ?? ('Produk #'.$produkId)),
            'penerangan' => (string) ($data['description'] ?? ''),
            'approved_price' => $hargaDisyorkan !== null ? (float) $hargaDisyorkan : null,
        ];
    }

    /**
     * Cari fakta knowledge base.
     *
     * @return array<int, string>
     */
    protected function cariKnowledgeBase(string $soalan): array
    {
        $kelas = 'App\\Models\\KbArticle';

        if (! class_exists($kelas) || $soalan === '') {
            return [];
        }

        try {
            $kata = array_values(array_filter(preg_split('/\s+/', mb_strtolower($soalan)) ?: []));

            if ($kata === []) {
                return [];
            }

            return $kelas::query()
                ->where('tenant_id', (int) $this->ctx->id())
                ->where(function ($q) use ($kata) {
                    foreach (array_slice($kata, 0, 6) as $k) {
                        $q->orWhere('title', 'ilike', '%'.$k.'%');
                    }
                })
                ->limit(3)
                ->pluck('title')
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Personaliti untuk sesi.
     */
    protected function personaliti(?LiveSession $sesi): ?HostPersonality
    {
        if ($sesi?->host_personality_id) {
            $p = HostPersonality::find($sesi->host_personality_id);

            if ($p) {
                return $p;
            }
        }

        if ($sesi?->avatar?->host_personality_id) {
            return HostPersonality::find($sesi->avatar->host_personality_id);
        }

        return HostPersonality::query()->where('is_default', true)->where('is_active', true)->first();
    }

    /**
     * Catat mesej ke sesi.
     */
    protected function catat(
        ?LiveSession $sesi,
        string $kandungan,
        string $intent,
        float $keyakinan,
        bool $perluBercakap,
        bool $perluEskalasi,
        ?string $sebab,
        int $token = 0,
        float $kos = 0.0,
        string $role = LiveMessage::ROLE_HOST,
        string $sumber = LiveMessage::SOURCE_AI,
    ): void {
        if ($sesi === null) {
            return;
        }

        LiveMessage::create([
            'live_session_id' => $sesi->id,
            'role' => $role,
            'source' => $sumber,
            'language' => $sesi->language,
            'content' => $kandungan,
            'content_length' => mb_strlen($kandungan),
            'intent' => $intent,
            'confidence' => $keyakinan,
            'is_question' => $role === LiveMessage::ROLE_AUDIENCE,
            'is_answered' => $role === LiveMessage::ROLE_HOST ? true : false,
            'escalated_to_human' => $perluEskalasi,
            'tokens_used' => $token,
            'cost_rm' => $kos,
        ]);
    }

    /**
     * Anggaran kos jawapan.
     */
    protected function anggaranKos(int $token): float
    {
        $kosPerToken = (float) config('blive.brain.cost_rm_per_token', 0.00002);

        return round($token * $kosPerToken, 6);
    }

    /**
     * Normalise teks untuk perbandingan.
     */
    protected function normalise(string $teks): string
    {
        return mb_strtolower(trim($teks));
    }

    /**
     * Hasil gagal.
     *
     * @return array<string, mixed>
     */
    protected function gagal(string $sebab, string $kod, bool $escalate = false): array
    {
        return [
            'success' => false,
            'content' => '',
            'intent' => 'error',
            'confidence' => 0.0,
            'should_speak' => false,
            'escalate_to_human' => $escalate,
            'reason' => $sebab,
            'code' => $kod,
            'tokens_used' => 0,
            'cost_rm' => 0.0,
        ];
    }
}
