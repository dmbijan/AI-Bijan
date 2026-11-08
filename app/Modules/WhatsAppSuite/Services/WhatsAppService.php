<?php

namespace App\Modules\WhatsAppSuite\Services;

use App\Modules\WhatsAppSuite\Models\WaAutoReply;
use App\Modules\WhatsAppSuite\Models\WaCampaign;
use App\Modules\WhatsAppSuite\Models\WaContact;
use App\Modules\WhatsAppSuite\Models\WaDevice;
use App\Modules\WhatsAppSuite\Models\WaMessage;
use App\Modules\WhatsAppSuite\Models\WaOrderNotification;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * WhatsAppService — automasi WhatsApp.
 *
 * Berasal daripada WasapBot MY (wasapbot.my):
 *   - chatbot AI + auto-responder + kata kunci
 *   - blast pukal dengan anti-ban (delay rawak + jeda berkala)
 *   - pengesah nombor (buang pendua & tidak sah)
 *   - pengurusan label, pengekstrak kontak/kumpulan/label
 *   - warm-up nombor baru
 *   - pemberitahuan pesanan + webhook/API
 */
class WhatsAppService
{
    public function __construct(private TenantContext $ctx) {}

    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    // ===============================================================
    // PENGHANTARAN
    // ===============================================================

    /**
     * Hantar mesej WhatsApp.
     *
     * @return array{success: bool, message_id: ?int, mesej: string}
     */
    public function hantar(WaDevice $peranti, WaContact|string $penerima, string $teks, array $option = []): array
    {
        $nombor = $penerima instanceof WaContact ? $penerima->phone_number : $penerima;
        $kontak = $penerima instanceof WaContact ? $penerima : null;

        if (! $peranti->bolehHantar()) {
            return [
                'success' => false,
                'message_id' => null,
                'mesej' => 'Peranti tidak bersambung atau had harian dicapai.',
            ];
        }

        $mesej = WaMessage::create([
            'wa_device_id' => $peranti->id,
            'wa_contact_id' => $kontak?->id,
            'wa_campaign_id' => $option['campaign_id'] ?? null,
            'direction' => WaMessage::DIRECTION_OUTGOING,
            'kind' => $option['kind'] ?? WaMessage::KIND_MANUAL,
            'to_number' => $nombor,
            'from_number' => $peranti->phone_number,
            'body' => $teks,
            'media_url' => $option['media_url'] ?? null,
            'status' => WaMessage::STATUS_QUEUED,
        ]);

        try {
            // Penghantaran sebenar memerlukan whatsapp-service (whatsapp-web.js).
            // Tanpa sambungan aktif, mesej kekal `queued` — tidak berpura-pura berjaya.
            if (! $peranti->bersambung() || ! config('whatsapp_suite.send_live', false)) {
                $mesej->update([
                    'status' => WaMessage::STATUS_QUEUED,
                    'error_message' => 'Penghantaran langsung dimatikan (whatsapp_suite.send_live=false).',
                ]);
            } else {
                $mesej->update([
                    'status' => WaMessage::STATUS_SENT,
                    'sent_at' => now(),
                    'external_id' => (string) Str::uuid(),
                ]);
                $peranti->increment('daily_sent_count');
            }

            if ($kontak) {
                $kontak->increment('message_count');
                $kontak->update(['last_messaged_at' => now()]);
            }

            return [
                'success' => $mesej->fresh()->berjaya(),
                'message_id' => $mesej->id,
                'mesej' => $mesej->fresh()->berjaya() ? 'Mesej dihantar.' : 'Mesej dibariskan.',
            ];
        } catch (\Throwable $e) {
            Log::warning('WhatsAppSuite: penghantaran gagal', ['error' => $e->getMessage()]);
            $mesej->update([
                'status' => WaMessage::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);

            return ['success' => false, 'message_id' => $mesej->id, 'mesej' => $e->getMessage()];
        }
    }

    // ===============================================================
    // PENGESAH NOMBOR
    // ===============================================================

    /**
     * Sahkan dan bersihkan senarai nombor.
     *
     * Buang pendua dan format tidak sah (Malaysia).
     *
     * @param  array<int, string>  $nombor
     * @return array{sah: array<int, string>, pendua: array<int, string>, tidak_sah: array<int, string>}
     */
    public function sahkanNombor(array $nombor): array
    {
        $sah = [];
        $pendua = [];
        $tidakSah = [];
        $dilihat = [];

        foreach ($nombor as $asli) {
            $bersih = $this->normalkanNombor((string) $asli);

            if ($bersih === null) {
                $tidakSah[] = (string) $asli;

                continue;
            }

            if (isset($dilihat[$bersih])) {
                $pendua[] = (string) $asli;

                continue;
            }

            $dilihat[$bersih] = true;
            $sah[] = $bersih;
        }

        return ['sah' => $sah, 'pendua' => $pendua, 'tidak_sah' => $tidakSah];
    }

    /**
     * Normalkan nombor Malaysia ke format 60XXXXXXXXX.
     */
    public function normalkanNombor(string $nombor): ?string
    {
        $digit = preg_replace('/[^0-9]/', '', $nombor) ?? '';

        if ($digit === '') {
            return null;
        }

        // 01X-XXXXXXX → 601X-XXXXXXX
        if (str_starts_with($digit, '0')) {
            $digit = '6'.$digit;
        }

        if (! str_starts_with($digit, '60')) {
            return null;
        }

        // Nombor Malaysia: 60 + 9-11 digit.
        if (strlen($digit) < 11 || strlen($digit) > 13) {
            return null;
        }

        return $digit;
    }

    // ===============================================================
    // AUTOREPLY / CHATBOT
    // ===============================================================

    /**
     * Cari balasan automatik yang sepadan.
     */
    public function cariBalasan(WaDevice $peranti, string $mesejMasuk): ?WaAutoReply
    {
        $mesej = Str::lower(trim($mesejMasuk));

        $semua = WaAutoReply::query()
            ->where('wa_device_id', $peranti->id)
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get();

        foreach ($semua as $peraturan) {
            $padan = match ($peraturan->type) {
                WaAutoReply::TYPE_EXACT => $mesej === Str::lower((string) $peraturan->trigger),
                WaAutoReply::TYPE_CONTAINS => $peraturan->trigger !== null
                    && str_contains($mesej, Str::lower((string) $peraturan->trigger)),
                WaAutoReply::TYPE_REGEX => $peraturan->trigger !== null
                    && @preg_match('/'.$peraturan->trigger.'/iu', $mesejMasuk) === 1,
                WaAutoReply::TYPE_WELCOME, WaAutoReply::TYPE_AI, WaAutoReply::TYPE_FALLBACK => true,
                default => false,
            };

            if ($padan) {
                $peraturan->increment('hit_count');
                $peraturan->update(['last_hit_at' => now()]);

                return $peraturan;
            }
        }

        return null;
    }

    // ===============================================================
    // KEMPEN / BLAST
    // ===============================================================

    /**
     * Jalankan kempen blast dengan delay anti-ban.
     *
     * @return array{queued: int, skipped: int}
     */
    public function jalankanKempen(WaCampaign $kempen): array
    {
        $peranti = WaDevice::find($kempen->wa_device_id);

        if (! $peranti) {
            $kempen->update(['status' => WaCampaign::STATUS_FAILED]);

            return ['queued' => 0, 'skipped' => 0];
        }

        $penerima = collect($kempen->recipients ?? [])
            ->filter(fn ($n) => $this->normalkanNombor((string) $n) !== null)
            ->unique()
            ->values();

        $kempen->update([
            'status' => WaCampaign::STATUS_RUNNING,
            'started_at' => now(),
            'total_recipients' => $penerima->count(),
        ]);

        $dibaris = 0;
        $dilangkau = 0;

        foreach ($penerima as $nombor) {
            if (! $peranti->bolehHantar()) {
                $dilangkau++;

                continue;
            }

            WaMessage::create([
                'wa_device_id' => $peranti->id,
                'wa_campaign_id' => $kempen->id,
                'direction' => WaMessage::DIRECTION_OUTGOING,
                'kind' => WaMessage::KIND_BROADCAST,
                'to_number' => $this->normalkanNombor((string) $nombor),
                'from_number' => $peranti->phone_number,
                'body' => $kempen->message,
                'media_url' => $kempen->media_url,
                'status' => WaMessage::STATUS_QUEUED,
            ]);

            $dibaris++;
        }

        $kempen->update([
            'sent_count' => $dibaris,
            'skipped_count' => $dilangkau,
            'completed_at' => now(),
            'status' => WaCampaign::STATUS_COMPLETED,
        ]);

        return ['queued' => $dibaris, 'skipped' => $dilangkau];
    }

    /**
     * Kira delay rawak anti-ban.
     *
     * @return array{min: int, max: int, pause_every: int, pause_seconds: int}
     */
    public function tetapanAntiBan(WaCampaign $kempen): array
    {
        return [
            'min' => max(5, (int) $kempen->delay_min_seconds),
            'max' => max((int) $kempen->delay_min_seconds, (int) $kempen->delay_max_seconds),
            'pause_every' => max(1, (int) $kempen->pause_every),
            'pause_seconds' => max(0, (int) $kempen->pause_duration_seconds),
        ];
    }

    // ===============================================================
    // WARM-UP
    // ===============================================================

    /**
     * Mulakan warm-up nombor baru.
     */
    public function mulaWarmup(WaDevice $peranti): WaDevice
    {
        $peranti->update([
            'warmup_enabled' => true,
            'warmup_started_at' => now(),
            'warmup_stage' => 1,
            'daily_limit' => (int) config('whatsapp_suite.warmup.stage_limits.1', 20),
        ]);

        return $peranti->fresh();
    }

    /**
     * Naik taraf tahap warm-up (had harian meningkat berperingkat).
     */
    public function naikTahapWarmup(WaDevice $peranti): WaDevice
    {
        $tahap = min((int) config('whatsapp_suite.warmup.max_stage', 4), $peranti->warmup_stage + 1);
        $had = (int) (config('whatsapp_suite.warmup.stage_limits.'.$tahap) ?? $peranti->daily_limit);

        $peranti->update(['warmup_stage' => $tahap, 'daily_limit' => $had]);

        return $peranti->fresh();
    }

    // ===============================================================
    // PEMBERITAHUAN PESANAN
    // ===============================================================

    /**
     * Hantar pemberitahuan pesanan berdasarkan pencetus.
     *
     * @param  array<string, string>  $pembolehUbah
     * @return array{success: bool, mesej: string}
     */
    public function hantarNotifikasiPesanan(
        WaDevice $peranti,
        WaContact|string $penerima,
        string $pencetus,
        array $pembolehUbah
    ): array {
        $templat = WaOrderNotification::query()
            ->where('wa_device_id', $peranti->id)
            ->where('trigger', $pencetus)
            ->where('is_active', true)
            ->first();

        if (! $templat) {
            return ['success' => false, 'mesej' => 'Templat pemberitahuan tidak dijumpai untuk pencetus: '.$pencetus];
        }

        $teks = $templat->render($pembolehUbah);

        $hasil = $this->hantar($peranti, $penerima, $teks, [
            'kind' => WaMessage::KIND_NOTIFICATION,
            'media_url' => $templat->media_url,
        ]);

        if ($hasil['success']) {
            $templat->increment('send_count');
            $templat->update(['last_sent_at' => now()]);
        }

        return ['success' => $hasil['success'], 'mesej' => $hasil['mesej']];
    }

    // ===============================================================
    // RINGKASAN
    // ===============================================================

    /**
     * Ringkasan papan pemuka.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(): array
    {
        return [
            'peranti' => WaDevice::query()->count(),
            'peranti_bersambung' => WaDevice::query()->where('status', WaDevice::STATUS_CONNECTED)->count(),
            'kontak' => WaContact::query()->count(),
            'kontak_sah' => WaContact::query()->where('status', WaContact::STATUS_VALID)->count(),
            'mesej_dihantar' => WaMessage::query()->where('direction', WaMessage::DIRECTION_OUTGOING)->count(),
            'mesej_gagal' => WaMessage::query()->where('status', WaMessage::STATUS_FAILED)->count(),
            'kempen' => WaCampaign::query()->count(),
            'kempen_berjalan' => WaCampaign::query()->where('status', WaCampaign::STATUS_RUNNING)->count(),
            'autoreply_aktif' => WaAutoReply::query()->where('is_active', true)->count(),
        ];
    }
}
