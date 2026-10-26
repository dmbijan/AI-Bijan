<?php

namespace App\Modules\ADE\Services;

use App\Modules\ADE\Models\AdeSkill;
use App\Modules\ADE\Models\AdeSkillInvocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AdeSkillService — A1 Skills Engine.
 *
 * Superpowers (MIT) idea asas: skill ialah "unit kerja berstruktur" yang
 * dipicu oleh konteks, bukan arahan sekali guna. Supercode mantangkan
 * 7 langkah metodologi; AI BIJAN berfungsi sebagai pustaka skill
 * yang boleh dipasang (tenant) dan dikongsi (marketplace).
 *
 * Corak reka bentuk yang dipegang:
 *  1. Skill sentiasa Boleh Dipasang — `source` menjejak asal (builtin/
 *     marketplace/user/learned).
 *  2. Pencetus dicetuskan secara automatik bila teks mengandungi
 *     kata kunci/frasa triggers.
 *  3. Setiap pemInvocation direkod — menjadi sumber data untuk
 *     "AI self-improve" (BusinessIntel learning loop).
 *  4. Tiada skill boleh writes data tanpa tenant scope.
 */
class AdeSkillService
{
    /**
     * Had keselamatan — skill marketplace ialah input tidak dipercayai.
     */
    public const HAD_SKILL_PER_TENANT = 200;
    public const HAD_LANGKAH = 20;
    public const HAD_PENCETUS = 15;
    public const MIN_KEY_LENGTH = 3;
    public const MAX_KEY_LENGTH = 80;

    /**
     * Katalog skill bawaan — 7 langkah superpowers + corak MY.
     *
     * Setiap skill: key, name, category, description, system_prompt,
     * steps[], triggers[], tools[], source, license.
     */
    public const SKILL_BUILTIN = [
        'brainstorming' => [
            'name' => 'Brainstorming',
            'category' => 'coding',
            'description' => 'Fahami niat & kekangan sebelum menulis sebarang kod. Hasil: keperluan yang jelas + skop.',
            'system_prompt' => 'Anda pembantu analitik. Tanya soalan penetrating satu demi satu sehingga keperluan jelas. Jangan tulis kod. Hasil akhir mesti senarai keperluan, kekangan, dan kriteria terimaan yang boleh diukur.',
            'steps' => [
                ['tajuk' => 'Kenal pasti masalah', 'output' => 'Penyataan masalah satu ayat'],
                ['tajuk' => 'Kumpul kekangan', 'output' => 'Senarai kekangan teknikal & perniagaan'],
                ['tajuk' => 'Tentukan skop', 'output' => 'Skop masuk / skop keluar'],
                ['tajuk' => 'Kriteria terimaan', 'output' => 'Senarai kriteria yang boleh diuji'],
            ],
            'triggers' => ['fikircara', ' brainstorm', 'reka bentuk', 'design', 'how should i', 'bagaimana nak'],
            'tools' => [],
            'source' => 'builtin',
            'origin_project' => 'obra/superpowers',
        ],
        'writing-plans' => [
            'name' => 'Writing Plans',
            'category' => 'coding',
            'description' => 'Pecah kerja kepada tugas 2–5 minit dengan laluan fail yang tepat. Hasil: pelan bolehpiekara oleh ejen lain.',
            'system_prompt' => 'Anda perancang perisian. Hasilkan pelan bernombor. Setiap langkah: 2–5 minit, satu fail jelas, kriteria selesai yang boleh diukur. Jangan kod.',
            'steps' => [
                ['tajuk' => 'Pecah kepada langkah kecil', 'output' => 'Senarai langkah 2–5 minit'],
                ['tajuk' => 'Tentukan fail', 'output' => 'Laluan fail tepat setiap langkah'],
                ['tajuk' => 'Tentukan ujian', 'output' => 'Ujian untuk setiap langkah'],
            ],
            'triggers' => ['pelan', 'plan', 'rancang', 'langkah', 'roadmap'],
            'tools' => [],
            'source' => 'builtin',
            'origin_project' => 'obra/superpowers',
        ],
        'subagent-driven-development' => [
            'name' => 'Sub-Agent Driven Development',
            'category' => 'coding',
            'description' => 'Satu sub-ejen bagi satu tugas + semakan selepas setiap satu. Hasil: kerja teliti tanpa konflik fail.',
            'system_prompt' => 'Anda penyelaras pembangunan. Bagi setiap subtugas kepada sub-ejen khusus, kemudian semak output sebelum meneruskan. Jangan kod sendiri.',
            'steps' => [
                ['tajuk' => 'Pecah kepada subtugas', 'output' => 'Senarai subtugas bebas'],
                ['tajuk' => 'Pilih ejen', 'output' => 'Ejen bagi setiap subtugas'],
                ['tajuk' => 'Semak setiap output', 'output' => 'Catatan semakan setiap subtugas'],
            ],
            'triggers' => ['subagent', 'ejen', 'parallel', 'serentak', 'orchestrate'],
            'tools' => ['ade.coordinations.run'],
            'source' => 'builtin',
            'origin_project' => 'obra/superpowers',
        ],
        'test-driven-development' => [
            'name' => 'Test Driven Development',
            'category' => 'coding',
            'description' => 'RED → GREEN → REFACTOR wajib. Tulis ujian gagal dahulu, kemudian kod minimum.',
            'system_prompt' => 'Anda pembangun TDD. MULA dengan ujian yang gagal (RED). затем kod minimum (GREEN). kemudian refactor tanpa mengubah tingkah laku (REFACTOR). Jangan tulis kod sebelum ujian.',
            'steps' => [
                ['tajuk' => 'RED — tulis ujian gagal', 'output' => 'Ujian yang gagal'],
                ['tajuk' => 'GREEN — kod minimum', 'output' => 'Kod yang lulus ujian'],
                ['tajuk' => 'REFACTOR', 'output' => 'Kod bersih, ujian masih lulus'],
            ],
            'triggers' => ['tdd', 'ujian', 'test', 'red green', 'regression'],
            'tools' => ['ade.reviews.test'],
            'source' => 'builtin',
            'origin_project' => 'obra/superpowers',
        ],
        'requesting-code-review' => [
            'name' => 'Requesting Code Review',
            'category' => 'coding',
            'description' => 'Semakan mengikut tahap keseriusan. Kritikal menyekat kelulusan automatik.',
            'system_prompt' => 'Anda penemu kod. Laporkan penemuan mengikut tahap keseriusan (critical/high/medium/low). Setiap penemuan: fail, baris, masalah, cadangan pembetulan.',
            'steps' => [
                ['tajuk' => 'Semak diff', 'output' => 'Senarai fail berubah'],
                ['tajuk' => 'Klasifikasi penemuan', 'output' => 'Penemuan mengikut keseriusan'],
                ['tajuk' => 'Cadangan', 'output' => 'Cadangan pembetulan'],
            ],
            'triggers' => ['review', 'semak', 'code review', 'pepijat', 'bug'],
            'tools' => ['ade.reviews.create'],
            'source' => 'builtin',
            'origin_project' => 'obra/superpowers',
        ],
        'finishing-development-branch' => [
            'name' => 'Finishing Development Branch',
            'category' => 'coding',
            'description' => 'Sahkan ujian → merge / buka PR / buang. Tutup dengan bukti.',
            'system_prompt' => 'Anda penutup cabang. Sahkan semua ujian lulus, kemudian pilih merge, buka PR, atau buang. Jangan buat tindakan tanpa bukti ujian.',
            'steps' => [
                ['tajuk' => 'Sahkan ujian', 'output' => 'Bukti ujian lulus'],
                ['tajuk' => 'Tentukan laluan', 'output' => 'merge|pr|discard'],
                ['tajuk' => 'Tutup', 'output' => 'Status akhir cabang'],
            ],
            'triggers' => ['merge', 'gabung', 'pull request', 'pr', 'tutup cabang', 'ship'],
            'tools' => ['ade.sandboxes.merge'],
            'source' => 'builtin',
            'origin_project' => 'obra/superpowers',
        ],
        'using-git-worktrees' => [
            'name' => 'Using Git Worktrees',
            'category' => 'coding',
            'description' => 'Ruang kerja terasing + garis dasar ujian bersih sebelum mula.',
            'system_prompt' => 'Andaowell worktree. Setiap subtugas mesti ada worktree sendiri. Sahkan garis dasar ujian bersih terlebih dahulu sebelum mula.',
            'steps' => [
                ['tajuk' => 'Cipta worktree', 'output' => 'Laluan worktree'],
                ['tajuk' => 'Jalankan garis dasar', 'output' => 'Keputusan ujian asas'],
                ['tajuk' => 'Isolasi subtugas', 'output' => 'Worktree per subtugas'],
            ],
            'triggers' => ['worktree', 'sandbox', 'branch', 'cabor'],
            'tools' => ['ade.sandboxes.create'],
            'source' => 'builtin',
            'origin_project' => 'obra/superpowers',
        ],
        'content-brief' => [
            'name' => 'Content Brief',
            'category' => 'content',
            'description' => 'Ringkas idea menjadi brief penulisan: audiens, nada, bukti, CTA.',
            'system_prompt' => 'Anda editor kandungan. Hasilkan brief: audiens sasaran, pesan utama, bukti sokongan, nada, CTA, platform sasaran.',
            'steps' => [
                ['tajuk' => 'Tentukan audiens', 'output' => 'Profil audiens'],
                ['tajuk' => 'Pesan & bukti', 'output' => 'Pesan utama + bukti'],
                ['tajuk' => 'Format', 'output' => 'Spesifikasi platform'],
            ],
            'triggers' => ['caption', 'post', 'kandungan', 'content', 'tulisan', 'artikel'],
            'tools' => ['content.pieces.create'],
            'source' => 'builtin',
            'origin_project' => 'aibijan/content-studio',
        ],
        'market-research' => [
            'name' => 'Market Research',
            'category' => 'marketing',
            'description' => 'Kaji pasaran: trend, pesaing, produk trending, idea untuk jualan.',
            'system_prompt' => 'Anda penganalisis pasaran. Gunakan data sebenar syarikat. Hasil: trend, pesaing, produk{% opportunity, idea tersusun mengikut impact ÷ effort.',
            'steps' => [
                ['tajuk' => 'Kumpul data', 'output' => 'Data sebenar syarikat'],
                ['tajuk' => 'Analisis', 'output' => 'Trend + pesaing'],
                ['tajuk' => 'Idea', 'output' => 'Idea dengan impact/effort'],
            ],
            'triggers' => ['pesaing', 'competitor', 'pasar', 'market', 'trend', 'ide'],
            'tools' => ['business_intel.study', 'business_intel.ideas'],
            'source' => 'builtin',
            'origin_project' => 'aibijan/business-intel',
        ],
        'financial-review' => [
            'name' => 'Financial Review',
            'category' => 'finance',
            'description' => 'Semak kesihatan kewangan tenant: jualan, margin, hutang, perbelanjaan.',
            'system_prompt' => 'Anda penganalisis kewangan. Semak jualan, margin, penghutang, perbelanjaan, cashflow. Laporkan risiko dan cadangan. Jangan beri cadangan tanpa data.',
            'steps' => [
                ['tajuk' => 'Kumpul angka', 'output' => 'KPI kewangan'],
                ['tajuk' => 'Semak anomali', 'output' => 'Senarai anomali'],
                ['tajuk' => 'Cadangan', 'output' => 'Cadangan keutamaan'],
            ],
            'triggers' => ['keuangan', 'finance', 'margin', 'hutang', 'revenue', 'jualan'],
            'tools' => ['business_intel.health'],
            'source' => 'builtin',
            'origin_project' => 'aibijan/business-intel',
        ],
    ];

    /**
     * Pasang semua skill bawaan untuk satu tenant (idempoten).
     *
     * @return int bilangan skill dipasang
     */
    public function pasangBuiltin(int $tenantId): int
    {
        $dipasang = 0;

        foreach (self::SKILL_BUILTIN as $key => $def) {
            $sedia = AdeSkill::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('key', $key)
                ->first();

            if ($sedia !== null) {
                continue;
            }

            $skill = new AdeSkill();
            $skill->tenant_id = $tenantId;
            $skill->key = $key;
            $skill->name = $def['name'];
            $skill->category = $def['category'];
            $skill->description = $def['description'];
            $skill->system_prompt = $def['system_prompt'];
            $skill->steps = $def['steps'];
            $skill->triggers = $def['triggers'];
            $skill->tools = $def['tools'];
            $skill->source = $def['source'];
            $skill->origin_project = $def['origin_project'];
            $skill->license = 'MIT';
            $skill->is_enabled = true;
            $skill->is_public = false;
            $skill->save();

            $dipasang++;
        }

        return $dipasang;
    }

    /**
     * Jana skill tersuai untuk tenant.
     *
     * @param array<string,mixed> $data
     */
    public function janaSkill(int $tenantId, array $data): AdeSkill
    {
        $this->guardBilang($tenantId);

        $key = $this->normaliseKey((string) ($data['key'] ?? ''));
        $this->guardKey($key);

        if ($this->kunciWujud($tenantId, $key)) {
            throw new \InvalidArgumentException("Kunci skill '{$key}' sudah wujud untuk tenant ini.");
        }

        $skill = new AdeSkill();
        $skill->tenant_id = $tenantId;
        $skill->key = $key;
        $skill->name = (string) ($data['name'] ?? $key);
        $skill->category = (string) ($data['category'] ?? 'general');
        $skill->description = $data['description'] ?? null;
        $skill->system_prompt = $data['system_prompt'] ?? null;
        $skill->steps = $this->guardSteps($data['steps'] ?? []);
        $skill->triggers = $this->guardTriggers($data['triggers'] ?? []);
        $skill->tools = array_values(array_map('strval', (array) ($data['tools'] ?? [])));
        $skill->config = $data['config'] ?? [];
        $skill->source = (string) ($data['source'] ?? 'user');
        $skill->origin_project = (string) ($data['origin_project'] ?? 'user');
        $skill->license = (string) ($data['license'] ?? 'MIT');
        $skill->is_enabled = (bool) ($data['is_enabled'] ?? true);
        $skill->is_public = (bool) ($data['is_public'] ?? false);
        $skill->save();

        return $skill;
    }

    /**
     * Cari skill yang sesuai untuk teks input — pencetus automatik.
     *
     * @return array<int,AdeSkill>
     */
    public function cariPencetus(int $tenantId, string $teks, int $had = 5): array
    {
        if (trim($teks) === '') {
            return [];
        }

        $teksLower = mb_strtolower($teks);
        $skill = AdeSkill::where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->get();

        $padanan = [];

        foreach ($skill as $s) {
            $skor = 0;

            foreach ($s->pencetus() as $p) {
                $p = mb_strtolower(trim((string) $p));

                if ($p === '') {
                    continue;
                }

                if (str_contains($teksLower, $p)) {
                    // Pencetus lebih panjang = lebih spesifik = skor lebih tinggi.
                    $skor += mb_strlen($p);
                }
            }

            if ($skor > 0) {
                $padanan[] = ['skill' => $s, 'skor' => $skor];
            }
        }

        usort($padanan, static fn (array $a, array $b) => $b['skor'] <=> $a['skor']);

        return array_slice(
            array_map(static fn (array $p) => $p['skill'], $padanan),
            0,
            $had
        );
    }

    /**
     * Rekod pemanggilan skill — sumber data learning loop.
     */
    public function rekod(
        int $tenantId,
        int $skillId,
        bool $berjaya,
        ?string $inputRingkas = null,
        ?string $outputRingkas = null,
        ?float $skor = null,
        int $durationMs = 0,
        string $contextType = 'manual',
        ?string $contextRef = null,
        array $meta = []
    ): AdeSkillInvocation {
        $inv = new AdeSkillInvocation();
        $inv->tenant_id = $tenantId;
        $inv->ade_skill_id = $skillId;
        $inv->context_type = $contextType;
        $inv->context_ref = $contextRef;
        $inv->input_summary = $inputRingkas === null ? null : mb_substr($inputRingkas, 0, 2000);
        $inv->output_summary = $outputRingkas === null ? null : mb_substr($outputRingkas, 0, 2000);
        $inv->succeeded = $berjaya;
        $inv->score = $skor;
        $inv->duration_ms = $durationMs;
        $inv->meta = $meta;
        $inv->save();

        $this->kemasKiniStatistik($skillId);

        return $inv;
    }

    /**
     * Kemas kini statistik penggunaan & kadar kejayaan skill.
     */
    private function kemasKiniStatistik(int $skillId): void
    {
        try {
            $stat = AdeSkillInvocation::withoutGlobalScopes()
                ->where('ade_skill_id', $skillId)
                ->selectRaw('COUNT(*) as jumlah, SUM(CASE WHEN succeeded = 1 THEN 1 ELSE 0 END) as berjaya')
                ->first();

            if ($stat === null || (int) $stat->jumlah === 0) {
                return;
            }

            AdeSkill::withoutGlobalScopes()
                ->where('id', $skillId)
                ->update([
                    'usage_count' => (int) $stat->jumlah,
                    'success_rate' => round(((int) $stat->berjaya / (int) $stat->jumlah) * 100, 2),
                    'updated_at' => now(),
                ]);
        } catch (\Throwable $e) {
            Log::warning('ADE kemas kini statistik skill gagal: ' . $e->getMessage());
        }
    }

    /**
     * Pasang skill global (marketplace) ke tenant.
     */
    public function pasang(int $tenantId, int $skillId): AdeSkill
    {
        $global = AdeSkill::withoutGlobalScopes()->where('id', $skillId)->first();

        if ($global === null) {
            throw new \RuntimeException('Skill tidak dijumpai.');
        }

        if ($global->tenant_id !== null) {
            throw new \RuntimeException('Hanya skill global boleh dipasang ke tenant lain.');
        }

        if ($global->license !== 'MIT' && $global->license !== 'Apache-2.0' && $global->license !== 'BSD') {
            throw new \RuntimeException("Lesen '{$global->license}' tidak membenarkan pemasangan ulang.");
        }

        $sedia = AdeSkill::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('key', $global->key)
            ->first();

        if ($sedia !== null) {
            return $sedia;
        }
        $this->guardBilang($tenantId);

        $salinan = $global->replicate();
        $salinan->tenant_id = $tenantId;
        $salinan->source = 'marketplace';
        $salinan->install_count = 0;
        $salinan->usage_count = 0;
        $salinan->success_rate = 0.00;
        $salinan->is_public = false;
        $salinan->save();

        AdeSkill::withoutGlobalScopes()->where('id', $skillId)->increment('install_count');

        return $salinan;
    }

    /**
     * Senarai skill untuk tenant — skill sendiri + skill global.
     */
    public function senarai(int $tenantId, ?string $kategori = null, bool $hanyaAktif = true): array
    {
        $query = AdeSkill::where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
        });

        if ($kategori !== null && $kategori !== '') {
            $query->where('category', $kategori);
        }

        if ($hanyaAktif) {
            $query->where('is_enabled', true);
        }

        return $query->orderBy('name')->get()->toArray();
    }

    /**
     * Statistik skill untuk tenant.
     */
    public function statistik(int $tenantId): array
    {
        $tenant = AdeSkill::where('tenant_id', $tenantId)->get();
        $global = AdeSkill::whereNull('tenant_id')->count();

        $kategori = $tenant->groupBy('category')->map(fn ($g) => $g->count())->toArray();

        $palingDigunakan = $tenant->sortByDesc('usage_count')->take(5)->map(fn ($s) => [
            'key' => $s->key,
            'name' => $s->name,
            'usage_count' => $s->usage_count,
            'success_rate' => $s->success_rate,
        ])->values()->toArray();

        $kadarJaya = $tenant->count() > 0
            ? round($tenant->avg('success_rate'), 2)
            : 0.0;

        return [
            'jumlah_tenant' => $tenant->count(),
            'jumlah_global' => $global,
            'mengikut_kategori' => $kategori,
            'paling_digunakan' => $palingDigunakan,
            'kadar_jaya_purata' => $kadarJaya,
            'jumlah_penggunaan' => (int) $tenant->sum('usage_count'),
        ];
    }

    // ── Penjaga (guards) ──────────────────────────────────────────────────

    /**
     * Adakah kunci skill ini sudah wujud dalam skop tenant?
     *
     * ⚠️ Kenaikan ini dilakukan di PHP, bukan UNIQUE(tenant_id, key) sahaja,
     * kerana SQLite/MySQL anggap NULL sebagai nilai berbeza dalam indeks
     * komposit — skill global (tenant_id NULL) boleh berkongsi kunci dengan
     * salinan tenant tanpa menjejaskan pengekalan unik.
     */
    private function kunciWujud(int $tenantId, string $key): bool
    {
        return AdeSkill::withoutGlobalScopes()
            ->where('key', $key)
            ->where(function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id');
            })
            ->exists();
    }

    private function guardBilang(int $tenantId): void
    {
        $jumlah = AdeSkill::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();

        if ($jumlah >= self::HAD_SKILL_PER_TENANT) {
            throw new \RuntimeException('Had skill per tenant (' . self::HAD_SKILL_PER_TENANT . ') telah dicapai.');
        }
    }

    private function guardKey(string $key): void
    {
        $panjang = mb_strlen($key);

        if ($panjang < self::MIN_KEY_LENGTH || $panjang > self::MAX_KEY_LENGTH) {
            throw new \InvalidArgumentException(
                'Kunci skill mestilah ' . self::MIN_KEY_LENGTH . '-' . self::MAX_KEY_LENGTH . ' aksara.'
            );
        }

        if (! preg_match('/^[a-z0-9][a-z0-9._-]*$/', $key)) {
            throw new \InvalidArgumentException('Kunci skill hanya boleh huruf kecil, nombor, titik, garis bawah dan tanda hubung.');
        }
    }

    private function guardSteps(mixed $steps): array
    {
        $steps = is_array($steps) ? $steps : [];

        if (count($steps) > self::HAD_LANGKAH) {
            throw new \InvalidArgumentException('Maksimum ' . self::HAD_LANGKAH . ' langkah bagi satu skill.');
        }

        $normal = [];

        foreach ($steps as $s) {
            if (is_string($s)) {
                $normal[] = ['tajuk' => $s];
                continue;
            }

            if (! is_array($s) || ! isset($s['tajuk'])) {
                throw new \InvalidArgumentException('Setiap langkah mesti mempunyai medan "tajuk".');
            }

            $normal[] = [
                'tajuk' => (string) $s['tajuk'],
                'output' => isset($s['output']) ? (string) $s['output'] : null,
                'detail' => isset($s['detail']) ? (string) $s['detail'] : null,
            ];
        }

        return $normal;
    }

    private function guardTriggers(mixed $triggers): array
    {
        $triggers = is_array($triggers) ? $triggers : [];

        if (count($triggers) > self::HAD_PENCETUS) {
            throw new \InvalidArgumentException('Maksimum ' . self::HAD_PENCETUS . ' pencetus bagi satu skill.');
        }

        $normal = [];

        foreach ($triggers as $t) {
            $t = trim((string) $t);

            if ($t === '') {
                continue;
            }

            if (mb_strlen($t) < 2) {
                throw new \InvalidArgumentException('Pencetus mestilah sekurang-kurangnya 2 aksara.');
            }

            $normal[] = $t;
        }

        return $normal;
    }

    /**
     * Normalise kunci: huruf kecil, ruang/garis bergaris bawah.
     */
    private function normaliseKey(string $key): string
    {
        $key = mb_strtolower(trim($key));
        $key = preg_replace('/[\s_]+/', '-', $key) ?? $key;

        return $key;
    }
}
