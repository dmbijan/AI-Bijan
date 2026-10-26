<?php

namespace App\Modules\Blast\Services;

/**
 * TemplateService — merge tag untuk e-mel/SMS/WhatsApp/video.
 *
 * Tag yang disokong: {{nama}}, {{nama_penuh}}, {{syarikat}}, {{jawatapan}},
 * {{kota}}, {{negara}}, {{dynamic_link}}, {{tawaran}}, {{tarikh}}.
 *
 * Tag yang TIDAK disokong akan dibuang (bukan dibiarkan kelihatan sebagai
 * "{{unknown}}" dalam mesej pengguna).
 */
class TemplateService
{
    /** @var array<string, string> */
    private array $data = [];

    private string $customUnsubUrl = '';

    public function dengan(array $data): self
    {
        $clone = clone $this;
        $clone->data = $data;

        return $clone;
    }

    public function denganUnsub(string $url): self
    {
        $clone = clone $this;
        $clone->customUnsubUrl = $url;

        return $clone;
    }

    /**
     * Render template.
     */
    public function render(string $template): string
    {
        $out = $template;

        foreach ($this->data as $key => $value) {
            $value = is_scalar($value) ? (string) $value : '';
            $out = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], $value, $out);
        }

        // Buang tag yang tidakKnown — jangan biarkan user nampak "{{foo}}".
        $out = preg_replace('/\{\{\s*[a-z0-9_.]+\s*\}\}/i', '', $out);

        // Bersihkan ruangACH double
        return trim(preg_replace('/[ \t]+/u', ' ', $out));
    }

    /**
     * Senarai tag yang boleh digunakan dalam template.
     */
    public static function availableTags(): array
    {
        return [
            'nama' => 'Nama pertama prospect',
            'nama_penuh' => 'Nama penuh prospect',
            'syarikat' => 'Nama syarikat',
            'jawatapan' => 'Jawatapan prospect',
            'kota' => 'Bandar (dari enrichment)',
            'negara' => 'Negara (default MY)',
            'tawaran' => 'Tawaran kempen',
            'tarikh' => 'Tarikh hari ini',
            'dynamic_link' => 'Pautan unik untuk kempen (UTM + unsubscribe)',
            'unsubscribe_link' => 'Pautan langganan',
        ];
    }

    /**
     * Bina data merge daripada prospect + kempen.
     */
    public static function dataDaripadaProspect(object $prospect, ?object $campaign = null): array
    {
        $enrichment = is_array($prospect->enrichment ?? null) ? $prospect->enrichment : [];

        $first = trim((string) ($prospect->first_name ?? ''));
        $last = trim((string) ($prospect->last_name ?? ''));

        return [
            'nama' => $first,
            'nama_penuh' => trim($first.' '.$last),
            'syarikat' => (string) ($prospect->company ?? ''),
            'jawatapan' => (string) ($prospect->job_title ?? ''),
            'kota' => (string) ($enrichment['city'] ?? $enrichment['kota'] ?? ''),
            'negara' => (string) ($prospect->country ?? 'MY'),
            'tawaran' => (string) ($enrichment['offer'] ?? $enrichment['tawaran'] ?? ''),
            'tarikh' => now()->format('d M Y'),
        ];
    }
}
