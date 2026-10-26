<?php

namespace App\Modules\SalesCopilot\Services;

use App\Modules\Blast\Models\BlastMessage;
use App\Modules\Prospecting\Models\Prospect;
use Illuminate\Support\Facades\Log;

/**
 * ReplyAnalyzerService — klasifikasi balasan prospect.
 *
 * Kategori:
 * - interested       → cipta opportunity
 * - not_now          → follow-up nanti
 * - unsubscribe      → tarik consent + suppress
 * - wrong_person     → cari kontak lain
 * - negative         → tandakan lost
 * - competitor       → nota intel
 * - question         → jawab dengan AI
 * - unknown          → manusia
 */
class ReplyAnalyzerService
{
    public const INTERESTED = 'interested';
    public const NOT_NOW = 'not_now';
    public const UNSUBSCRIBE = 'unsubscribe';
    public const WRONG_PERSON = 'wrong_person';
    public const NEGATIVE = 'negative';
    public const COMPETITOR = 'competitor';
    public const QUESTION = 'question';
    public const UNKNOWN = 'unknown';

    /**
     * Corak kata kunci setiap kategori (BM + EN).
     *
     * @var array<string, string[]>
     */
    public const PATTERNS = [
        self::INTERESTED => [
            'interested', 'interested in', 'tell me more', 'more details', 'how much',
            'pricing', 'price', 'demo', 'meeting', 'call me', 'available',
            'berinterested', 'minat', 'boleh kongsi', 'boleh bincang', 'demo',
            'harga', 'berjumpa', 'hubungi saya', 'bolehhubungi',
        ],
        self::NOT_NOW => [
            'not now', 'later', 'next quarter', 'next month', 'busy', 'maybe',
            'revisit', 'follow up', 'touch base', 'not the right time',
            'belum', 'nanti', 'lain kali', 'sibuk', 'akan hubungi',
            'follow up', 'hubungi semula',
        ],
        self::UNSUBSCRIBE => [
            'unsubscribe', 'stop sending', 'remove me', 'do not contact',
            'takut', 'berhenti', 'jangan hantar', 'buang', 'remove from list',
            'unsubscribe please', 'stop emailing me',
        ],
        self::WRONG_PERSON => [
            'wrong person', 'not the right contact', 'i left', 'i no longer work',
            'bukan orang betul', 'sudah pindah', 'bukan saya',
        ],
        self::NEGATIVE => [
            'not interested', 'no thanks', 'no thank you', 'not relevant',
            'dont need', "don't need", 'too expensive', 'not interested',
            'tidak minat', 'tidak perlu', 'terlalu mahal', 'tidak relevan',
            'no thank you', 'pass', 'not for me',
        ],
        self::COMPETITOR => [
            'using', 'already have', 'competitor', 'signed with', 'contract with',
            'dengan', 'sudah guna', 'pesaing', 'kontrak dengan',
        ],
        self::QUESTION => [
            '?', 'how does', 'can you', 'do you', 'apakah', 'bolehkah', 'bagaimana',
            'boleh', 'macam mana', 'adakah',
        ],
    ];

    /**
     * Corak yang menunjukkan negation — patuh diprioritykan atas padanan positif.
     *
     * Contoh: "Not interested" MESTI diklasifikasi sebagai negative, bukan interested.
     *
     * @var array<string, string[]>
     */
    public const NEGASI = [
        'not interested', 'not relevant', 'no thanks', 'no thank you',
        'not interested', 'do not', 'dont ', "don't ",
        'tidak minat', 'tidak perlu', 'tidak relevan', 'jangan',
        'bukan saya', 'no longer', 'not the right',
    ];

    /**
     * Analisis satu balasan.
     *
     * @return array{category: string, confidence: float, matched: string[], needs_human: bool}
     */
    public function analyze(string $reply): array
    {
        $text = mb_strtolower(trim($reply));

        if ($text === '') {
            return $this->result(self::UNKNOWN, 0.0, [], true);
        }

        $scores = [];
        $matches = [];

        foreach (self::PATTERNS as $category => $patterns) {
            $score = 0;
            $catMatches = [];

            foreach ($patterns as $pattern) {
                if (str_contains($text, mb_strtolower($pattern))) {
                    $score++;
                    $catMatches[] = $pattern;
                }
            }

            if ($score > 0) {
                $scores[$category] = $score;
                $matches[$category] = $catMatches;
            }
        }

        if (empty($scores)) {
            return $this->result(self::UNKNOWN, 0.0, [], true);
        }

        // Negasi didahulukan: "Not interested" BUKAN "interested".
        if ($this->adaNegasi($text)) {
            foreach (self::NEGASI as $pola) {
                if (str_contains($text, mb_strtolower($pola))) {
                    return $this->result(
                        $this->kategoriNegatifUntuk($pola),
                        0.9,
                        [$pola],
                        false
                    );
                }
            }
        }

        // Skor tertinggi menang; confidence = bahagian daripada jumlah
        arsort($scores);
        $top = array_key_first($scores);
        $total = array_sum($scores);
        $confidence = round($scores[$top] / $total, 2);

        // Unsubscribe adalah paling kritikal — sentiasa
        if ($top === self::UNSUBSCRIBE) {
            $confidence = max($confidence, 0.95);
        }

        $threshold = (float) config('sales_copilot.reply_analysis.confidence_threshold', 0.7);

        return $this->result(
            $top,
            $confidence,
            $matches[$top] ?? [],
            $confidence < $threshold
        );
    }

    /**
     * Proses balasan prospect — kemas kini status, consent, dan log.
     *
     * @return array{category: string, confidence: float, actions: array}
     */
    public function prosesBalasan(Prospect $prospect, string $reply, ?BlastMessage $message = null): array
    {
        $analysis = $this->analyze($reply);
        $category = $analysis['category'];
        $actions = [];

        if ($message) {
            $message->update([
                'replied_at' => now(),
                'status' => \App\Modules\Blast\Models\BlastMessage::STATUS_DELIVERED,
            ]);
        }

        switch ($category) {
            case self::INTERESTED:
                $prospect->update(['status' => Prospect::STATUS_REPLIED]);
                $actions[] = 'cipta_opportunity';
                break;

            case self::UNSUBSCRIBE:
                $prospect->update([
                    'status' => Prospect::STATUS_LOST,
                    'consent_status' => Prospect::CONSENT_DENIED,
                    'consent_at' => null,
                    'consent_evidence' => null,
                ]);

                // Suppress supaya tidak dihantar semula
                app(\App\Modules\Blast\Services\OptOutService::class)->suppress(
                    $prospect->tenant_id,
                    $prospect->email,
                    $prospect->phone,
                    'email',
                    'unsubscribe'
                );
                $actions[] = 'suppress';
                break;

            case self::NEGATIVE:
                $prospect->update(['status' => Prospect::STATUS_LOST]);
                $actions[] = 'tandakan_lost';
                break;

            case self::NOT_NOW:
                $prospect->update(['status' => Prospect::STATUS_CONTACTED]);
                $actions[] = 'jadual_follow_up';
                break;

            case self::QUESTION:
            case self::COMPETITOR:
            case self::WRONG_PERSON:
            case self::UNKNOWN:
            default:
                $prospect->update(['status' => Prospect::STATUS_REPLIED]);
                $actions[] = 'serahkan_kepada_human';
                break;
        }

        $prospect->markTouched();

        Log::info('SalesCopilot: balasan dianalisis', [
            'tenant_id' => $prospect->tenant_id,
            'prospect_id' => $prospect->id,
            'category' => $category,
            'confidence' => $analysis['confidence'],
        ]);

        return [
            'category' => $category,
            'confidence' => $analysis['confidence'],
            'actions' => $actions,
        ];
    }

    /**
     * Adakah teks mengandungi penegas (negation)?
     */
    private function adaNegasi(string $text): bool
    {
        foreach (self::NEGASI as $pola) {
            if (str_contains($text, mb_strtolower($pola))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kategori negatif yang sesuai untuk corak negation tertentu.
     */
    private function kategoriNegatifUntuk(string $pola): string
    {
        $pola = mb_strtolower($pola);

        if (str_contains($pola, 'unsubscribe') || str_contains($pola, 'remove')
            || str_contains($pola, 'stop ') || str_contains($pola, 'jangan')) {
            return self::UNSUBSCRIBE;
        }

        if (str_contains($pola, 'not the right') || str_contains($pola, 'bukan saya')
            || str_contains($pola, 'no longer')) {
            return self::WRONG_PERSON;
        }

        if (str_contains($pola, 'do not') || str_contains($pola, 'dont ')
            || str_contains($pola, "don't")) {
            return self::UNSUBSCRIBE;
        }

        return self::NEGATIVE;
    }

    /**
     * @return array{category: string, confidence: float, matched: string[], needs_human: bool}
     */    private function result(string $category, float $confidence, array $matched, bool $needsHuman): array

    {
        return [
            'category' => $category,
            'confidence' => $confidence,
            'matched' => $matched,
            'needs_human' => $needsHuman,
        ];
    }
}
