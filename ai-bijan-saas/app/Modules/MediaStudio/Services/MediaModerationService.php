<?php

namespace App\Modules\MediaStudio\Services;

use Illuminate\Support\Facades\Log;

/**
 * MediaModerationService — semak automatik output media.
 *
 * Menegak 4 kategori yang disekat: keganasan, seksual, politik, peniruan.
 * Setiap video blast mesti ada watermark "AI-generated".
 */
class MediaModerationService
{
    /**
     * Corak yang disekat.
     *
     * @var array<string, string[]>
     */
    public const CORAK = [
        'violence' => [
            'kill', 'murder', 'shoot', 'bomb', 'stab', 'torture',
            'bunuh', 'bunuh', 'tembak', 'bom', 'tortur', 'serang',
        ],
        'sexual' => [
            'porn', 'nude', 'sex scene', 'erotic',
            'pornografi', 'telanjang', 'seks',
        ],
        'political' => [
            'election campaign', 'vote for', 'political party',
            'kempen pilihan raya', 'undi', 'parti politik',
        ],
        'impersonation' => [
            'pretend to be', 'impersonate', 'posing as', 'as government',
            'meniru', 'pura-pura',
        ],
    ];

    /**
     * Semak teks (skrip, transkripsi, ringkasan) untuk kandungan terlarang.
     *
     * @return array{passed: bool, violations: array}
     */
    public function semakTeks(string $teks): array
    {
        if (! config('media_studio.moderation.enabled', true)) {
            return ['passed' => true, 'violations' => []];
        }

        $lower = mb_strtolower($teks);
        $violations = [];

        foreach (self::CORAK as $kategori => $patterns) {
            if (! config("media_studio.moderation.block_{$kategori}", true)) {
                continue;
            }

            foreach ($patterns as $pattern) {
                if (str_contains($lower, mb_strtolower($pattern))) {
                    $violations[] = [
                        'kategori' => $kategori,
                        'pattern' => $pattern,
                    ];
                    break;
                }
            }
        }

        return [
            'passed' => empty($violations),
            'violations' => $violations,
        ];
    }

    /**
     * Semak output media sebelum disiarkan.
     *
     * @return array{passed: bool, violations: array, watermark: string}
     */
    public function semakOutput(?string $teks = null, ?string $kandungan = null): array
    {
        $result = $this->semakTeks((string) ($teks ?? ''));

        if ($kandungan !== null) {
            $result['passed'] = $result['passed'] && $this->semakKandungan($kandungan);
        }

        $result['watermark'] = (string) config('media_studio.generation.watermark_text', 'AI-generated');

        return $result;
    }

    /**
     * Semak label/kandungan media (cth metadata video).
     */
    public function semakKandungan(string $kandungan): bool
    {
        $lower = mb_strtolower($kandungan);

        foreach (self::CORAK as $kategori => $patterns) {
            if (! config("media_studio.moderation.block_{$kategori}", true)) {
                continue;
            }

            foreach ($patterns as $pattern) {
                if (str_contains($lower, mb_strtolower($pattern))) {
                    Log::warning('MediaStudio: kandungan dilumpuhkan moderasi', [
                        'kategori' => $kategori,
                    ]);

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Tambah watermark pada skrip video (untuk diletakkan sebagai overlay).
     */
    public function denganWatermark(string $skrip): string
    {
        $watermark = config('media_studio.generation.watermark_text', 'AI-generated');

        return trim($skrip)."\n\n[".$watermark.']';
    }
}
