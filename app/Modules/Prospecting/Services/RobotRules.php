<?php

namespace App\Modules\Prospecting\Services;

/**
 * RobotRules — parser robots.txt yang ringkas.
 *
 *_goal:erer RFC 9309 (Robots Exclusion Protocol).
 * - Allow dan Disallow mengikut urutan panjang (panjang terpanjang menang).
 * - User-agent '*' terpakai jika tiada padanan khusus.
 * - Wildcard '*' dan anchor '$' disokong.
 */
class RobotRules
{
    /** @var array<string, array{allow: string[], disallow: string[]}> */
    private array $groups = [];

    private string $userAgent;

    public function __construct(string $content, string $userAgent = '*')
    {
        $this->userAgent = strtolower($userAgent);
        $this->parse($content);
    }

    /**
     * Parse kandungan robots.txt.
     */
    private function parse(string $content): void
    {
        $currentAgents = [];
        $lastDirective = null;

        foreach (preg_split('/\r\n|\r|\n/', $content) as $line) {
            // Buang komen
            $line = trim(explode('#', $line, 2)[0]);

            if ($line === '') {
                continue;
            }

            if (! preg_match('/^([^:]+):(.*)$/', $line, $m)) {
                continue;
            }

            $field = strtolower(trim($m[1]));
            $value = trim($m[2]);

            if ($field === 'user-agent') {
                if ($lastDirective !== 'user-agent') {
                    // User-agent baharu bermula kumpulan baharu
                    $currentAgents = [];
                }
                $currentAgents[] = strtolower($value);
                $lastDirective = 'user-agent';

                foreach ($currentAgents as $agent) {
                    if (! isset($this->groups[$agent])) {
                        $this->groups[$agent] = ['allow' => [], 'disallow' => []];
                    }
                }

                continue;
            }

            if ($lastDirective === null || $currentAgents === []) {
                continue;
            }

            if ($field === 'disallow' || $field === 'allow') {
                // Disallow kosong bermaksud "tiada sekatan" — abaikan.
                if ($field === 'disallow' && $value === '') {
                    $lastDirective = $field;

                    continue;
                }

                foreach ($currentAgents as $agent) {
                    $this->groups[$agent][$field === 'allow' ? 'allow' : 'disallow'][] = $value;
                }

                $lastDirective = $field;
            }
        }
    }

    /**
     * Adakah URL dibenarkan untuk user agent ini?
     */
    public function dibenarkan(string $path): bool
    {
        $rules = $this->rulesFor();

        if ($rules === null) {
            return true; // Tiada rules → semua dibenarkan
        }

        $best = null;
        $bestLen = -1;
        $bestAllow = true;

        foreach ($rules['allow'] as $pattern) {
            if ($this->padanan($path, $pattern) && strlen($pattern) > $bestLen) {
                $bestLen = strlen($pattern);
                $best = $pattern;
                $bestAllow = true;
            }
        }

        foreach ($rules['disallow'] as $pattern) {
            if ($this->padanan($path, $pattern) && strlen($pattern) > $bestLen) {
                $bestLen = strlen($pattern);
                $best = $pattern;
                $bestAllow = false;
            }
        }

        if ($best === null) {
            return true;
        }

        return $bestAllow;
    }

    /**
     * Cari rules yang terpakai — padanan khusus atau '*'.
     *
     * @return array{allow: string[], disallow: string[]}|null
     */
    private function rulesFor(): ?array
    {
        $agent = $this->userAgent;

        if (isset($this->groups[$agent])) {
            return $this->groups[$agent];
        }

        // Cuba padanan separa (cth 'aibijan' untuk 'aibijan-scraper')
        foreach ($this->groups as $key => $rules) {
            if ($key !== '*' && $key !== '' && str_contains($agent, $key)) {
                return $rules;
            }
        }

        return $this->groups['*'] ?? null;
    }

    /**
     * Padanan wildcard sederhana dengan support anchor '$'.
     */
    private function padanan(string $path, string $pattern): bool
    {
        $anchorEnd = str_ends_with($pattern, '$');
        $pattern = rtrim($pattern, '$');

        if ($pattern === '') {
            return false;
        }

        // Escape regex, kemudian tukar '*' → '.*'
        $regex = preg_quote($pattern, '#');
        $regex = str_replace('\*', '.*', $regex);

        if ($anchorEnd) {
            $regex .= '$';
        }

        return (bool) preg_match('#^'.$regex.'#i', $path);
    }
}
