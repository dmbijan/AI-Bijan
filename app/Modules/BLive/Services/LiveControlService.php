<?php

namespace App\Modules\BLive\Services;

use App\Modules\BLive\Models\LiveControl;
use App\Modules\BLive\Models\LiveMessage;
use App\Modules\BLive\Models\LiveSession;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;

/**
 * LiveControlService - kawalan hybrid manusia + AI.
 *
 * Peraturan operasi yang WAJIB dipenuhi (blueprint bahagian 5.2):
 *   1. Manusia sentiasa mempunyai kuasa mengambil alih.
 *   2. AI tidak boleh mengabaikan arahan berhenti.
 *   3. Transaksi, diskaun dan janji jualan tertakluk kepada polisi diluluskan.
 *   4. AI tidak boleh mencipta harga atau fakta produk.
 *   5. Identiti manusia digital mesti didedahkan sebagai AI presenter.
 *
 * Setiap arahan direkod dalam `blive_live_controls` supaya boleh diaudit.
 */
class LiveControlService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * Sahkan bahawa rekod milik tenant yang sedang aktif.
     *
     * ⚠️ Perisai tambahan: trait `BelongsToTenant` mengekalkan global scope
     * pada QUERY sahaja. Jika model dimuatkan semasa tenant A dan kemudian
     * `update()` dipanggil semasa tenant B aktif, trait itu TIDAK
     * menolak (ia hanya halang perubahan pada `tenant_id` sendiri).
     *oleh itu setiap operasi tulis mesti mengesahkan pemilikan rekod
     * terlebih dahulu.
     */
    protected function milikTenant(?Model $rekod): bool
    {
        if ($rekod === null) {
            return false;
        }

        $tenantId = (int) $this->ctx->id();

        if ($tenantId <= 0) {
            return false;
        }

        return (int) $rekod->tenant_id === $tenantId;
    }

    /**
     * Ambil alih - manusia mengambil alih mikrofon.
     *
     * @return array{success: bool, session: ?LiveSession, error: ?string}
     */
    public function ambilAlih(LiveSession $sesi, ?int $userId = null): array
    {
        $mula = microtime(true);

        if (! $this->milikTenant($sesi)) {
            return [
                'success' => false,
                'session' => $sesi,
                'error' => 'Sesi ini tidak Owned oleh syarikat anda.',
            ];
        }

        if (! $sesi->aktif()) {
            $this->catat($sesi, LiveControl::TYPE_TAKEOVER, LiveControl::ISSUED_BY_HUMAN, $userId, [], [
                'success' => false,
                'error' => 'Sesi tidak aktif.',
            ]);

            return ['success' => false, 'session' => $sesi, 'error' => 'Sesi tidak aktif.'];
        }

        $sesi->update([
            'session_mode' => LiveSession::MODE_HUMAN_LED,
            'status' => LiveSession::STATUS_LIVE,
        ]);

        $latency = (int) ((microtime(true) - $mula) * 1000);

        $this->catat($sesi, LiveControl::TYPE_TAKEOVER, LiveControl::ISSUED_BY_HUMAN, $userId, [
            'previous_mode' => $sesi->session_mode,
        ], ['success' => true], $latency);

        return ['success' => true, 'session' => $sesi->fresh(), 'error' => null];
    }

    /**
     * Lepaskan takeover - kembali kepada AI.
     */
    public function lepaskan(LiveSession $sesi, ?int $userId = null): array
    {
        $mula = microtime(true);

        if (! $this->milikTenant($sesi)) {
            return ['success' => false, 'session' => $sesi, 'error' => 'Sesi ini tidak dimiliki oleh syarikat anda.'];
        }

        if (! $sesi->aktif()) {
            return ['success' => false, 'session' => $sesi, 'error' => 'Sesi tidak aktif.'];
        }

        $sesi->update([
            'session_mode' => LiveSession::MODE_AI_ASSISTED,
            'status' => LiveSession::STATUS_LIVE,
        ]);

        $latency = (int) ((microtime(true) - $mula) * 1000);

        $this->catat($sesi, LiveControl::TYPE_RELEASE, LiveControl::ISSUED_BY_HUMAN, $userId, [], [
            'success' => true,
        ], $latency);

        return ['success' => true, 'session' => $sesi->fresh(), 'error' => null];
    }

    /**
     * Jeda respons automatik AI.
     */
    public function jedaAi(LiveSession $sesi, ?int $userId = null): array
    {
        if (! $this->milikTenant($sesi)) {
            return ['success' => false, 'session' => $sesi, 'error' => 'Sesi ini tidak dimiliki oleh syarikat anda.'];
        }

        if (! $sesi->aktif()) {
            return ['success' => false, 'session' => $sesi, 'error' => 'Sesi tidak aktif.'];
        }

        $sesi->update(['status' => LiveSession::STATUS_PAUSED]);

        $this->catat($sesi, LiveControl::TYPE_PAUSE_AI, LiveControl::ISSUED_BY_HUMAN, $userId, [], [
            'success' => true,
        ]);

        return ['success' => true, 'session' => $sesi->fresh(), 'error' => null];
    }

    /**
     * Sambung semula respons AI.
     */
    public function sambungAi(LiveSession $sesi, ?int $userId = null): array
    {
        if (! $this->milikTenant($sesi)) {
            return ['success' => false, 'session' => $sesi, 'error' => 'Sesi ini tidak dimiliki oleh syarikat anda.'];
        }

        if (! $sesi->aktif()) {
            return ['success' => false, 'session' => $sesi, 'error' => 'Sesi tidak aktif.'];
        }

        $sesi->update(['status' => LiveSession::STATUS_LIVE]);

        $this->catat($sesi, LiveControl::TYPE_RESUME_AI, LiveControl::ISSUED_BY_HUMAN, $userId, [], [
            'success' => true,
        ]);

        return ['success' => true, 'session' => $sesi->fresh(), 'error' => null];
    }

    /**
     * Beri arahan suara terus kepada hos.
     *
     * @param  array<string, mixed>  $instr
     * @return array{success: bool, session: ?LiveSession, message: ?LiveMessage, error: ?string}
     */
    public function cakap(LiveSession $sesi, string $instr, ?int $userId = null, array $options = []): array
    {
        $instr = trim($instr);

        if (! $this->milikTenant($sesi)) {
            return [
                'success' => false,
                'session' => $sesi,
                'message' => null,
                'error' => 'Sesi ini tidak dimiliki oleh syarikat anda.',
            ];
        }

        if ($instr === '') {
            return ['success' => false, 'session' => $sesi, 'message' => null, 'error' => 'Arahan kosong.'];
        }

        if (! $sesi->aktif()) {
            return ['success' => false, 'session' => $sesi, 'message' => null, 'error' => 'Sesi tidak aktif.'];
        }

        $had = (int) config('blive.limits.max_speech_characters', 2000);

        if (mb_strlen($instr) > $had) {
            return [
                'success' => false,
                'session' => $sesi,
                'message' => null,
                'error' => 'Arahan melebihi had '.$had.' aksara.',
            ];
        }

        // Sesi yang dikontrol manusia masih boleh menerima arahan manual.
        $mesej = LiveMessage::create([
            'live_session_id' => $sesi->id,
            'role' => LiveMessage::ROLE_HOST,
            'source' => LiveMessage::SOURCE_HUMAN,
            'language' => $options['language'] ?? $sesi->language,
            'content' => $instr,
            'content_length' => mb_strlen($instr),
            'intent' => 'manual_directive',
            'confidence' => 1.0,
            'is_answered' => true,
        ]);

        $sesi->increment('speech_count');

        $this->catat($sesi, LiveControl::TYPE_SPEAK, LiveControl::ISSUED_BY_HUMAN, $userId, [
            'instruction' => $instr,
            'language' => $mesej->language,
        ], ['success' => true, 'message_id' => $mesej->id], null, (bool) ($options['human_approved'] ?? true));

        return ['success' => true, 'session' => $sesi->fresh(), 'message' => $mesej, 'error' => null];
    }

    /**
     * Henti kecemasan - hentikan output AI dengan selamat.
     *
     * Henti kecemasan sentiasa tersedia dan tidak boleh dilumpuhkan.
     */
    public function hentiKecemasan(LiveSession $sesi, ?int $userId = null, ?string $sebab = null): array
    {
        $mula = microtime(true);

        if (! $this->milikTenant($sesi)) {
            return [
                'success' => false,
                'session' => $sesi,
                'error' => 'Sesi ini tidak dimiliki oleh syarikat anda.',
            ];
        }

        $sesi->update([
            'status' => LiveSession::STATUS_ENDED,
            'ended_at' => now(),
        ]);

        $latency = (int) ((microtime(true) - $mula) * 1000);

        $this->catat($sesi, LiveControl::TYPE_STOP, LiveControl::ISSUED_BY_HUMAN, $userId, [
            'reason' => $sebab,
        ], ['success' => true], $latency);

        return ['success' => true, 'session' => $sesi->fresh(), 'error' => null];
    }

    /**
     * Rekod komen penonton yang masuk.
     *
     * @param  array<string, mixed>  $data
     */
    public function terimaKomen(LiveSession $sesi, string $kandungan, ?string $platform = null, ?string $externalId = null): LiveMessage
    {
        $kandungan = trim($kandungan);

        if (! $this->milikTenant($sesi)) {
            throw new \RuntimeException('Sesi ini tidak dimiliki oleh syarikat anda.');
        }

        $mesej = LiveMessage::create([
            'live_session_id' => $sesi->id,
            'role' => LiveMessage::ROLE_AUDIENCE,
            'source' => LiveMessage::SOURCE_AI,
            'language' => $sesi->language,
            'content' => $kandungan,
            'content_length' => mb_strlen($kandungan),
            'is_question' => str_contains($kandungan, '?'),
            'platform' => $platform,
            'external_id' => $externalId,
        ]);

        $sesi->increment('comments_handled');

        return $mesej;
    }

    /**
     * Tanda mesej sebagai dijawab.
     */
    public function tandaiDijawab(LiveMessage $mesej): LiveMessage
    {
        $mesej->update(['is_answered' => true]);

        return $mesej->fresh();
    }

    /**
     * Catat arahan kawalan.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $result
     */
    protected function catat(
        LiveSession $sesi,
        string $jenis,
        string $dikeluarkanOleh,
        ?int $userId,
        array $payload = [],
        array $result = [],
        ?int $latencyMs = null,
        bool $approved = false,
    ): LiveControl {
        return LiveControl::create([
            'live_session_id' => $sesi->id,
            'control_type' => $jenis,
            'issued_by' => $dikeluarkanOleh,
            'user_id' => $userId ?? auth()->id(),
            'payload' => $payload,
            'result' => $result,
            'approved' => $approved,
            'latency_ms' => $latencyMs,
        ]);
    }
}
