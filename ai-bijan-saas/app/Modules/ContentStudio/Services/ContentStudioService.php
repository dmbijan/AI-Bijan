<?php

namespace App\Modules\ContentStudio\Services;

use App\Modules\ContentStudio\Models\ContentBrandProfile;
use App\Modules\ContentStudio\Models\ContentChat;
use App\Modules\ContentStudio\Models\ContentChatMessage;
use App\Modules\ContentStudio\Models\ContentComment;
use App\Modules\ContentStudio\Models\ContentIdea;
use App\Modules\ContentStudio\Models\ContentModerationRule;
use App\Modules\ContentStudio\Models\ContentPiece;
use App\Modules\ContentStudio\Models\ContentPlatform;
use App\Modules\ContentStudio\Models\ContentPlatformMetric;
use App\Modules\ContentStudio\Models\ContentPublication;
use Illuminate\Support\Facades\Log;

/**
 * ContentStudioService — jana, jadual & terbitkan kandungan ke semua platform.
 *
 * ✅ Idea #21 (BAB 86): "auto generate content, auto post pelbagai platform,
 * jaga komen semua platform, jaga platform chat" — seperti "Content department"
 * ReStructure AI, tetapi bersepadan dengan brand voice tenant.
 */
class ContentStudioService
{
    /**
     * Had panjang post mengikut platform.
     *
     * @var array<string, int>
     */
    public const HAD_PLATFORM = [
        ContentPlatform::FACEBOOK => 63206,
        ContentPlatform::INSTAGRAM => 2200,
        ContentPlatform::TIKTOK => 4000,
        ContentPlatform::LINKEDIN => 3000,
        ContentPlatform::X => 280,
        ContentPlatform::WHATSAPP => 4096,
        ContentPlatform::YOUTUBE => 5000,
        ContentPlatform::TELEGRAM => 4096,
        ContentPlatform::GOOGLE_BUSINESS => 1500,
    ];

    /**
     * Jana kandungan dengan AI.
     */
    public function janaKandungan(int $tenantId, array $brief): ContentPiece
    {
        $profil = $this->profilDefault($tenantId);

        $prompt = $this->binaPrompt($brief, $profil);
        $platform = $brief['platform'] ?? null;

        $body = '';
        $hook = null;
        $cta = null;
        $hashtags = [];
        $aiMeta = [];

        try {
            $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

            $hasil = $gateway->call(
                $tenantId,
                [
                    ['role' => 'system', 'content' => $profil?->systemPrompt() ?? 'Anda penulis kandungan profesional.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'content_generation',
                [
                    'max_output_tokens' => 1200,
                    'source_module' => 'content_studio',
                    'temperature' => (float) ($brief['temperature'] ?? 0.8),
                ]
            );

            $body = (string) ($hasil['content'] ?? '');
            $aiMeta = [
                'model' => $hasil['model'] ?? null,
                'credits' => $hasil['usage']['credits_charged'] ?? 0,
                'simulated' => $hasil['simulated'] ?? false,
            ];
        } catch (\Throwable $e) {
            Log::warning('ContentStudio: jana AI gagal', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            $body = $this->fallbackBody($brief);
        }

        // Gebus baris pertama sebagai hook, baris terakhir sebagai CTA
        $baris = array_values(array_filter(array_map('trim', explode("\n", $body))));
        if (! empty($baris)) {
            $hook = $baris[0];
            $cta = end($baris) ?: null;
        }

        // Hashtag dari brief
        if (! empty($brief['hashtags'])) {
            $hashtags = array_map(
                fn ($h) => str_starts_with((string) $h, '#') ? $h : '#' . $h,
                (array) $brief['hashtags']
            );
        } else {
            $hashtags = $this->ekstrakHashtag($body);
        }

        $piece = ContentPiece::create([
            'tenant_id' => $tenantId,
            'title' => $brief['title'] ?? ($hook ? mb_substr($hook, 0, 120) : 'Kandungan tanpa tajuk'),
            'body' => $body,
            'hook' => $hook,
            'cta' => $cta,
            'content_type' => $brief['content_type'] ?? 'post',
            'status' => ContentPiece::STATUS_DRAFT,
            'brand_profile_id' => $profil?->id,
            'hashtags' => $hashtags ?: null,
            'ai_meta' => $aiMeta ?: null,
            'media_assets' => $brief['media_assets'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Idea yang digunakan → tandakan digunakan
        if (! empty($brief['idea_id'])) {
            $idea = ContentIdea::query()
                ->where('tenant_id', $tenantId)
                ->find($brief['idea_id']);

            if ($idea) {
                $idea->update([
                    'status' => ContentIdea::STATUS_USED,
                    'used_in_piece_id' => $piece->id,
                ]);
            }
        }

        return $piece;
    }

    /**
     * Bina prompt AI.
     */
    private function binaPrompt(array $brief, ?ContentBrandProfile $profil): string
    {
        $bahagian = [];

        if (! empty($brief['topic'])) {
            $bahagian[] = "Topik: {$brief['topic']}";
        }

        if (! empty($brief['brief'])) {
            $bahagian[] = "Brief: {$brief['brief']}";
        }

        if (! empty($brief['audience'])) {
            $bahagian[] = "Sasaran: {$brief['audience']}";
        }

        if (! empty($brief['platform'])) {
            $bahagian[] = "Platform: {$brief['platform']}";
        }

        if (! empty($brief['keywords'])) {
            $bahagian[] = 'Kata kunci: ' . implode(', ', (array) $brief['keywords']);
        }

        if (! empty($brief['tone'])) {
            $bahagian[] = "Nada: {$brief['tone']}";
        }

        if (! empty($brief['language'])) {
            $bahagian[] = "Bahasa: {$brief['language']}";
        }

        $bahagian[] = "\nTulis kandungan yangTTUAT. Mula dengan hook yang kuat, tutup dengan CTA yang jelas.";
        $bahagian[] = 'HANYA kandungan — tiada penerangan, tiada markdown, tiada komen.';

        return implode("\n", $bahagian);
    }

    /**
     * Fallback tanpa AI.
     */
    private function fallbackBody(array $brief): string
    {
        $topik = $brief['topic'] ?? ($brief['title'] ?? 'produk kami');
        $cta = $brief['cta'] ?? 'Hubungi kami hari ini.';

        return "{$topik}\n\n"
            . "Kami menyediakan penyelesaian terbaik untuk-RVEN needs anda. "
            . "Kualiti terjamin, harga bercompetitive, penghantaran pantas.\n\n"
            . $cta;
    }

    /**
     * Ekstrak hashtag dari teks.
     */
    private function ekstrakHashtag(string $teks): array
    {
        preg_match_all('/#[\p{L}\p{N}_]+/u', $teks, $m);

        return array_slice(array_unique($m[0]), 0, 10);
    }

    /**
     * Profil brand default tenant.
     */
    public function profilDefault(int $tenantId): ?ContentBrandProfile
    {
        return ContentBrandProfile::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    /**
     * Adaptasi kandungan untuk platform tertentu (format + had).
     */
    public function adaptasiUntukPlatform(ContentPiece $piece, string $platform): array
    {
        $body = (string) $piece->body;
        $had = self::HAD_PLATFORM[$platform] ?? 5000;

        $dipotong = false;
        if (mb_strlen($body) > $had) {
            $body = mb_substr($body, 0, $had - 3) . '...';
            $dipotong = true;
        }

        // Tambah hashtags untuk platform yang menyokong
        if (in_array($platform, [ContentPlatform::INSTAGRAM, ContentPlatform::TIKTOK], true)
            && ! empty($piece->hashtags)) {
            $body .= "\n\n" . implode(' ', $piece->hashtags);
        }

        // X: potong dengan halus
        if ($platform === ContentPlatform::X && mb_strlen($body) > 280) {
            $badan = trim(mb_substr($body, 0, 240));
            $body = mb_substr($badan, 0, (int) mb_strrpos($badan, ' ')) . '…';
            $dipotong = true;
        }

        return [
            'body' => $body,
            'had' => $had,
            'dipotong' => $dipotong,
            'panjang' => mb_strlen($body),
        ];
    }

    /**
     * Jadwal penerbitan ke platform.
     */
    public function jadualTerbit(int $tenantId, int $pieceId, array $platformIds, ?string $scheduledAt = null): array
    {
        $piece = ContentPiece::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($pieceId);

        $status = $scheduledAt && $scheduledAt > now()
            ? ContentPiece::STATUS_SCHEDULED
            : ContentPiece::STATUS_APPROVED;

        $piece->update([
            'status' => $status,
            'scheduled_at' => $scheduledAt,
        ]);

        $publications = [];

        foreach ($platformIds as $platformId) {
            $platform = ContentPlatform::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->find($platformId);

            if (! $platform) {
                continue;
            }

            $adaptasi = $this->adaptasiUntukPlatform($piece, $platform->platform);

            $publications[] = ContentPublication::create([
                'tenant_id' => $tenantId,
                'content_piece_id' => $piece->id,
                'content_platform_id' => $platform->id,
                'status' => ContentPublication::STATUS_QUEUED,
                'platform_payload' => [
                    'body' => $adaptasi['body'],
                    'hashtags' => $piece->hashtags,
                    'media' => $piece->media_assets,
                    'dipotong' => $adaptasi['dipotong'],
                ],
                'scheduled_at' => $scheduledAt ?? now(),
            ]);
        }

        return $publications;
    }

    /**
     * Terbitkan queued publications (stub adapter — platform sebenar perlu OAuth).
     */
    public function terbitQueued(int $tenantId, int $limit = 20): array
    {
        $publications = ContentPublication::query()
            ->where('tenant_id', $tenantId)
            ->where('status', ContentPublication::STATUS_QUEUED)
            ->where('scheduled_at', '<=', now())
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();

        $berjaya = 0;
        $gagal = 0;

        foreach ($publications as $pub) {
            $platform = $pub->platform;

            if (! $platform || ! $platform->is_active) {
                $pub->update([
                    'status' => ContentPublication::STATUS_SKIPPED,
                    'error_message' => 'Platform tidak aktif',
                ]);
                continue;
            }

            // Had posting harian
            $hariIni = ContentPublication::query()
                ->where('tenant_id', $tenantId)
                ->where('content_platform_id', $platform->id)
                ->where('status', ContentPublication::STATUS_PUBLISHED)
                ->whereDate('published_at', now()->toDateString())
                ->count();

            if ($hariIni >= $platform->max_daily_posts) {
                $pub->update([
                    'status' => ContentPublication::STATUS_SKIPPED,
                    'error_message' => "Had {$platform->max_daily_posts} posting harian tercapai",
                ]);
                continue;
            }

            // Auto-publish memerlukan kebenaran platform
            if (! $platform->auto_publish && ! $platform->auto_publish) {
                $pub->update([
                    'status' => ContentPublication::STATUS_SKIPPED,
                    'error_message' => 'Auto-publish tidak diaktifkan untuk platform ini',
                ]);
                continue;
            }

            // Simulasi terbitan (adapter sebenar memerlukan OAuth token)
            $pub->update([
                'status' => ContentPublication::STATUS_PUBLISHED,
                'platform_post_id' => 'sim_' . uniqid(),
                'platform_url' => 'https://' . $platform->platform . '/sim/' . uniqid(),
                'published_at' => now(),
                'attempts' => $pub->attempts + 1,
            ]);

            $platform->update(['last_posted_at' => now()]);
            $berjaya++;
        }

        // Kemas kini status piece
        foreach ($publications as $pub) {
            $pub->refresh();
            if ($pub->berjaya()) {
                ContentPiece::query()
                    ->where('tenant_id', $tenantId)
                    ->where('id', $pub->content_piece_id)
                    ->update([
                        'status' => ContentPiece::STATUS_PUBLISHED,
                        'published_at' => now(),
                    ]);
            }
        }

        return ['berjaya' => $berjaya, 'gagal' => $gagal, 'diproses' => $publications->count()];
    }

    /**
     * Ingest komen dari platform.
     */
    public function ingestKomen(int $tenantId, int $platformId, array $data): ContentComment
    {
        $platform = ContentPlatform::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($platformId);

        $komen = ContentComment::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'content_platform_id' => $platformId,
                'external_id' => $data['external_id'],
            ],
            [
                'content_piece_id' => $data['content_piece_id'] ?? null,
                'platform_post_id' => $data['platform_post_id'] ?? null,
                'author_name' => $data['author_name'] ?? 'Tidak diketahui',
                'author_id' => $data['author_id'] ?? null,
                'author_avatar' => $data['author_avatar'] ?? null,
                'body' => $data['body'] ?? '',
                'like_count' => $data['like_count'] ?? 0,
                'posted_at' => $data['posted_at'] ?? now(),
            ]
        );

        // Analisis sentiment + moderation
        $this->analisisKomen($tenantId, $komen);

        return $komen->fresh();
    }

    /**
     * Analisis sentiment + semak moderation rules.
     */
    public function analisisKomen(int $tenantId, ContentComment $komen): array
    {
        $sentiment = $this->deteksiSentiment($komen->body);
        $komen->update([
            'sentiment' => $sentiment['label'],
            'sentiment_score' => $sentiment['score'],
        ]);

        // Semak moderation rules
        $rules = ContentModerationRule::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $berlaku = [];

        foreach ($rules as $rule) {
            if (! $rule->sepadan($komen->body)) {
                continue;
            }

            $rule->increment('trigger_count');
            $berlaku[] = $rule;

            match ($rule->action) {
                ContentModerationRule::ACTION_HIDE => $komen->update(['status' => ContentComment::STATUS_HIDDEN]),
                ContentModerationRule::ACTION_BLOCK, ContentModerationRule::ACTION_ESCALATE => $komen->update(['status' => ContentComment::STATUS_FLAGGED]),
                ContentModerationRule::ACTION_FLAG => $komen->update(['status' => ContentComment::STATUS_FLAGGED]),
                default => null,
            };
        }

        return [
            'sentiment' => $sentiment,
            'rules_berlaku' => count($berlaku),
            'status' => $komen->fresh()->status,
        ];
    }

    /**
     * Deteksi sentiment ringkas (leksikon).
     */
    private function deteksiSentiment(string $teks): array
    {
        $teks = mb_strtolower($teks);

        $positif = ['bagus', 'hebat', 'syarik', 'terima kasih', 'cantik', 'love', 'best', 'mantap', 'great', 'thanks', 'excellent', 'perfect', ' lovely'];
        $negatif = ['buruk', 'lambat', 'kecewa', 'refund', 'bocor', 'rosak', 'bad', 'slow', 'angry', 'terrible', 'awful', 'complaint', 'rude'];

        $skorPos = 0;
        $skorNeg = 0;

        foreach ($positif as $kata) {
            if (str_contains($teks, $kata)) {
                $skorPos++;
            }
        }

        foreach ($negatif as $kata) {
            if (str_contains($teks, $kata)) {
                $skorNeg++;
            }
        }

        $skor = $skorPos - $skorNeg;

        return [
            'label' => $skor > 0
                ? ContentComment::SENTIMENT_POSITIVE
                : ($skor < 0 ? ContentComment::SENTIMENT_NEGATIVE : ContentComment::SENTIMENT_NEUTRAL),
            'score' => (float) $skor,
        ];
    }

    /**
     * Jana cadangan balasan AI untuk komen.
     */
    public function janaBalasanAI(int $tenantId, ContentComment $komen): array
    {
        $profil = $this->profilDefault($tenantId);
        $platform = $komen->platform;

        $prompt = "Anda mewakili {$platform?->display_name} membalas komen pelanggan.\n"
            . "Nama pelanggan: {$komen->author_name}\n"
            . "Komen: {$komen->body}\n"
            . "Sentiment: {$komen->sentiment}\n\n"
            . 'Tulis balasan ringkas, mesra, profesional (maks 2 kalimat). '
            . 'Jika komen negatif atau minta bayaran balik, cadangkan supaya manusia/vendor chamada.';

        try {
            $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

            $hasil = $gateway->call(
                $tenantId,
                [
                    ['role' => 'system', 'content' => $profil?->systemPrompt() ?? 'Anda ejen perkhidmatan pelanggan.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'content_reply',
                ['max_output_tokens' => 300, 'source_module' => 'content_studio']
            );

            $balasan = (string) ($hasil['content'] ?? '');

            // Mod simulasi gateway tidak memahami konteks — gabungkan fallback
            if (trim($balasan) === '' || str_starts_with($balasan, '[SIMULASI]')) {
                $balasan = $komen->sentiment === ContentComment::SENTIMENT_NEGATIVE
                    ? 'Maaf atas ketidaksesuaian ini. Kami akan hubungi anda untuk penyelesaian.'
                    : 'Terima kasih atas komen anda! Kami menghargai maklum balas anda.';
            }

            $keyakinan = $komen->sentiment === ContentComment::SENTIMENT_NEGATIVE ? 0.55 : 0.85;

            $komen->update([
                'ai_reply_suggestion' => $balasan,
                'ai_confidence' => $keyakinan,
            ]);

            return ['balasan' => $balasan, 'keyakinan' => $keyakinan];
        } catch (\Throwable $e) {
            Log::warning('ContentStudio: jana balasan AI gagal', ['error' => $e->getMessage()]);

            $fallback = $komen->sentiment === ContentComment::SENTIMENT_NEGATIVE
                ? 'Maaf atas ketidaksesuaian ini. Kami akan hubungi anda untuk penyelesaian.'
                : 'Terima kasih atas komen anda! Kami menghargai maklum balas anda.';

            $komen->update(['ai_reply_suggestion' => $fallback, 'ai_confidence' => 0.4]);

            return ['balasan' => $fallback, 'keyakinan' => 0.4];
        }
    }

    /**
     * Balas komen (manual atau auto).
     */
    public function balasKomen(int $tenantId, int $commentId, string $balasan, bool $dariAi = false): ContentComment
    {
        $komen = ContentComment::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($commentId);

        $komen->update([
            'reply_body' => $balasan,
            'status' => ContentComment::STATUS_REPLIED,
            'replied_at' => now(),
            'is_from_ai' => $dariAi,
        ]);

        return $komen->fresh();
    }

    /**
     * Sembunyikan komen.
     */
    public function sembunyiKomen(int $tenantId, int $commentId): ContentComment
    {
        $komen = ContentComment::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($commentId);

        $komen->update(['status' => ContentComment::STATUS_HIDDEN]);

        return $komen->fresh();
    }

    /**
     * Ingest chat / DM.
     */
    public function ingestChat(int $tenantId, int $platformId, array $data): ContentChat
    {
        $chat = ContentChat::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'content_platform_id' => $platformId,
                'external_conversation_id' => $data['external_conversation_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
            ],
            [
                'customer_name' => $data['customer_name'] ?? 'Pelanggan',
                'customer_avatar' => $data['customer_avatar'] ?? null,
                'last_message' => $data['body'] ?? null,
                'last_message_from' => 'customer',
                'unread_count' => $chat->unread_count ?? 0,
            ]
        );

        ContentChatMessage::create([
            'tenant_id' => $tenantId,
            'content_chat_id' => $chat->id,
            'external_id' => $data['external_id'] ?? null,
            'from' => 'customer',
            'body' => $data['body'] ?? '',
            'sent_at' => $data['sent_at'] ?? now(),
        ]);

        $chat->update([
            'message_count' => $chat->message_count + 1,
            'unread_count' => $chat->unread_count + 1,
            'last_message_at' => now(),
            'status' => ContentChat::STATUS_OPEN,
        ]);

        // Flag untuk manusia jika ada kata kunci kritikal
        $kritikal = ['refund', 'complaint', 'saman', 'police', 'scam', 'saya nak Authority'];
        $perlu = false;
        foreach ($kritikal as $kata) {
            if (stripos((string) $data['body'], $kata) !== false) {
                $perlu = true;
                break;
            }
        }

        if ($perlu) {
            $chat->update(['requires_human' => true, 'status' => ContentChat::STATUS_PENDING]);
        }

        return $chat->fresh();
    }

    /**
     * Hantar balasan dalam chat.
     */
    public function balasChat(int $tenantId, int $chatId, string $balasan, bool $dariAi = false): ContentChat
    {
        $chat = ContentChat::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($chatId);

        ContentChatMessage::create([
            'tenant_id' => $tenantId,
            'content_chat_id' => $chat->id,
            'from' => $dariAi ? 'ai' : 'human',
            'body' => $balasan,
            'sent_at' => now(),
            'is_read' => true,
        ]);

        $chat->update([
            'last_message' => $balasan,
            'last_message_from' => $dariAi ? 'ai' : 'human',
            'last_message_at' => now(),
            'unread_count' => 0,
            'requires_human' => false,
        ]);

        return $chat->fresh();
    }

    /**
     * Ringkasan dashboard content.
     */
    public function ringkasan(int $tenantId, int $hari = 30): array
    {
        $dari = now()->subDays($hari);

        $pieces = ContentPiece::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $dari)
            ->get();

        $komen = ContentComment::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $dari)
            ->get();

        $chats = ContentChat::query()
            ->where('tenant_id', $tenantId)
            ->where('last_message_at', '>=', $dari)
            ->get();

        $published = $pieces->where('status', ContentPiece::STATUS_PUBLISHED);

        return [
            'kandungan_dijana' => $pieces->count(),
            'kandungan_diterbit' => $published->count(),
            'kandungan_jadual' => $pieces->where('status', ContentPiece::STATUS_SCHEDULED)->count(),
            'draf_menunggu' => $pieces->where('status', ContentPiece::STATUS_IN_REVIEW)->count(),
            'jumlah_tontonan' => (int) $published->sum('view_count'),
            'jumlah_engagement' => (int) $published->sum('engagement_count'),
            'kadar_engagement' => $published->sum('view_count') > 0
                ? round($published->sum('engagement_count') / $published->sum('view_count') * 100, 2)
                : 0.0,
            'komen_diterima' => $komen->count(),
            'komen_menunggu_balasan' => $komen->filter(fn ($k) => $k->perlukanTindakan())->count(),
            'komen_negatif' => $komen->where('sentiment', ContentComment::SENTIMENT_NEGATIVE)->count(),
            'chat_aktif' => $chats->where('status', ContentChat::STATUS_OPEN)->count(),
            'chat_perlu_human' => $chats->where('requires_human', true)->count(),
            'chat_belum_dibaca' => $chats->sum('unread_count'),
            'platform_aktif' => ContentPlatform::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->count(),
            'periode_hari' => $hari,
        ];
    }

    /**
     * Analisis prestasi platform.
     */
    public function prestasiPlatform(int $tenantId, int $hari = 30): array
    {
        $metrics = ContentPlatformMetric::query()
            ->where('tenant_id', $tenantId)
            ->where('metric_date', '>=', now()->subDays($hari)->toDateString())
            ->get();

        $ikutPlatform = $metrics
            ->groupBy('content_platform_id')
            ->map(function ($group, $platformId) {
                $platform = ContentPlatform::find($platformId);

                return [
                    'platform' => $platform?->platform ?? 'unknown',
                    'nama' => $platform?->display_name,
                    'followers' => (int) $group->max('followers'),
                    'followers_gained' => (int) $group->sum('followers_gained'),
                    'reach' => (int) $group->sum('reach'),
                    'impressions' => (int) $group->sum('impressions'),
                    'engagements' => (int) $group->sum('engagements'),
                    'clicks' => (int) $group->sum('clicks'),
                    'posts' => (int) $group->sum('posts_published'),
                    'komen_negatif' => (int) $group->sum('negative_comments'),
                    'kadar_engagement' => round((float) $group->avg('engagement_rate'), 2),
                ];
            })
            ->sortByDesc('reach')
            ->values()
            ->toArray();

        return [
            'platform' => $ikutPlatform,
            'jumlah_reach' => (int) $metrics->sum('reach'),
            'jumlah_engagement' => (int) $metrics->sum('engagements'),
            'jumlah_klik' => (int) $metrics->sum('clicks'),
            'purata_engagement_rate' => round((float) $metrics->avg('engagement_rate'), 2),
            'periode_hari' => $hari,
        ];
    }
}
