<?php

namespace App\Modules\BusinessIntel\Services;

use App\Modules\BusinessIntel\Models\BiCompetitor;
use App\Modules\BusinessIntel\Models\BiCompetitorSnapshot;
use App\Modules\BusinessIntel\Models\BiHealthSnapshot;
use App\Modules\BusinessIntel\Models\BiIdea;
use App\Modules\BusinessIntel\Models\BiLearning;
use App\Modules\BusinessIntel\Models\BiMarketSignal;
use App\Modules\BusinessIntel\Models\BiStudy;
use Illuminate\Support\Facades\Log;

/**
 * BusinessIntelService — AI belajar perniagaan tenant dan cadang penambahbaikan.
 *
 * ✅ Idea #23, #24, #25 (BAB 86):
 * - "full ai automation dan dijaga, di update oleh ai"
 * - "kajian bisnes untuk tambah baik, beri cadangan, beri idea untuk market"
 * - "ai self-improve business"
 * - "ai auto kaji pesaing business dan bagikan idea kepada tenant"
 *
 * Corak diambil daripada ReStructure AI ("data jadi aset, aset buat kerja")
 * dan Hermes Agent ("closed learning loop").
 */
class BusinessIntelService
{
    /**
     * Pemberat dimensi skor kesihatan.
     *
     * @var array<string, float>
     */
    public const PEMBERAT = [
        'sales' => 0.25,
        'cashflow' => 0.25,
        'operations' => 0.15,
        'customer' => 0.15,
        'marketing' => 0.10,
        'compliance' => 0.10,
    ];

    /**
     * Kira skor kesihatan perniagaan daripada data sebenar.
     *
     * Semua nilai 0–100. Metrik tidak tersedia = neutral (50).
     */
    public function kiraKesihatan(int $tenantId, array $metrik = []): array
    {
        $sales = $this->skorSales($tenantId, $metrik);
        $cashflow = $this->skorCashflow($tenantId, $metrik);
        $operations = $this->skorOperations($tenantId, $metrik);
        $customer = $this->skorCustomer($tenantId, $metrik);
        $marketing = $this->skorMarketing($tenantId, $metrik);
        $compliance = $this->skorCompliance($tenantId, $metrik);

        $skor = [
            'sales' => $sales,
            'cashflow' => $cashflow,
            'operations' => $operations,
            'customer' => $customer,
            'marketing' => $marketing,
            'compliance' => $compliance,
        ];

        $keseluruhan = 0.0;
        foreach (self::PEMBERAT as $dimensi => $berat) {
            $keseluruhan += $skor[$dimensi]['skor'] * $berat;
        }

        $keseluruhan = round($keseluruhan, 2);

        return [
            'overall_score' => $keseluruhan,
            'grade' => BiHealthSnapshot::gredDaripada($keseluruhan),
            'dimensions' => $skor,
            'weakest' => collect($skor)->sortBy('skor')->take(2)->keys()->all(),
        ];
    }

    /**
     * Simpan snapshot kesihatan harian.
     */
    public function simpanKesihatan(int $tenantId, ?array $metrik = null): BiHealthSnapshot
    {
        $hasil = $this->kiraKesihatan($tenantId, $metrik ?? []);
        $tarikh = now()->toDateString();

        // Cari snapshot hari ini (elak isu perbandingan tarikh dalam updateOrCreate)
        $snapshot = BiHealthSnapshot::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('snapshot_date', $tarikh)
            ->first();

        $data = [
            'overall_score' => $hasil['overall_score'],
            'grade' => $hasil['grade'],
            'sales_score' => $hasil['dimensions']['sales']['skor'],
            'cashflow_score' => $hasil['dimensions']['cashflow']['skor'],
            'operations_score' => $hasil['dimensions']['operations']['skor'],
            'customer_score' => $hasil['dimensions']['customer']['skor'],
            'marketing_score' => $hasil['dimensions']['marketing']['skor'],
            'compliance_score' => $hasil['dimensions']['compliance']['skor'],
            'metrics' => $hasil['dimensions'],
            'insights' => $hasil['weakest'],
        ];

        if ($snapshot) {
            $snapshot->update($data);

            return $snapshot->fresh();
        }

        return BiHealthSnapshot::create(array_merge($data, [
            'tenant_id' => $tenantId,
            'snapshot_date' => $tarikh,
        ]));
    }

    // ---------------------------------------------------------------
    // Skor setiap dimensi
    // ---------------------------------------------------------------

    private function skorSales(int $tenantId, array $m): array
    {
        // Pertumbuhan jualan (bulan ini vs bulan lepas)
        $bulanIni = $this->jumlahJualan($tenantId, now()->startOfMonth(), now());
        $bulanLepas = $this->jumlahJualan(
            $tenantId,
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        );

        $pertumbuhan = $bulanLepas > 0
            ? (($bulanIni - $bulanLepas) / $bulanLepas) * 100
            : ($bulanIni > 0 ? 100 : 0);

        // Skor: 0% pertumbuhan = 50, +20% = 100, -20% = 0
        $skor = 50 + ($pertumbuhan * 2.5);
        $skor = max(0, min(100, $skor));

        return [
            'skor' => round($skor, 2),
            'nilai' => round($bulanIni, 2),
            'pertumbuhan_percent' => round($pertumbuhan, 2),
            'sebab' => 'Pertumbuhan jualan bulan ini vs bulan lepas',
        ];
    }

    private function skorCashflow(int $tenantId, array $m): array
    {
        // Nisbah belum bayar vs jualan bulan ini
        $jualan = $this->jumlahJualan($tenantId, now()->startOfMonth(), now());
        $belumBayar = $this->jumlahBelumBayar($tenantId);

        if ($jualan <= 0) {
            return ['skor' => 50.0, 'nilai' => 0, 'belum_bayar' => round($belumBayar, 2), 'sebab' => 'Tiada data jualan'];
        }

        $nisbah = $belumBayar / max(1, $jualan);

        // 0% belum bayar = 100, 50%+ = 0
        $skor = max(0, min(100, 100 - ($nisbah * 200)));

        return [
            'skor' => round($skor, 2),
            'nilai' => round($jualan, 2),
            'belum_bayar' => round($belumBayar, 2),
            'nisbah' => round($nisbah, 4),
            'sebab' => 'Nisbah belum bayar berbanding jualan',
        ];
    }

    private function skorOperations(int $tenantId, array $m): array
    {
        $jumlah = \App\Modules\Orders\Models\UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        if ($jumlah === 0) {
            return ['skor' => 50.0, 'sebab' => 'Tiada pesanan dalam 30 hari'];
        }

        // Pesanan lewat (> 24 jam belum dihantar)
        $lewat = \App\Modules\Orders\Models\UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'confirmed', 'processing', 'packed'])
            ->where('placed_at', '<', now()->subDay())
            ->count();

        $nisbah = $lewat / $jumlah;
        $skor = max(0, min(100, 100 - ($nisbah * 300)));

        return [
            'skor' => round($skor, 2),
            'jumlah_pesanan' => $jumlah,
            'pesanan_lewat' => $lewat,
            'sebab' => 'Nisbah pesanan lewat diproses',
        ];
    }

    private function skorCustomer(int $tenantId, array $m): array
    {
        // Komen negatif vs jumlah komen
        $komen = \App\Modules\ContentStudio\Models\ContentComment::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        if ($komen->isEmpty()) {
            return ['skor' => 50.0, 'sebab' => 'Tiada data komen/sentimen'];
        }

        $negatif = $komen->filter(fn ($k) => in_array($k->sentiment, ['negative', 'toxic'], true))->count();
        $nisbah = $negatif / $komen->count();
        $skor = max(0, min(100, 100 - ($nisbah * 250)));

        return [
            'skor' => round($skor, 2),
            'jumlah_komen' => $komen->count(),
            'komen_negatif' => $negatif,
            'sebab' => 'Nisbah komen negatif',
        ];
    }

    private function skorMarketing(int $tenantId, array $m): array
    {
        // Kandungan diterbitkan + engagement
        $pieces = \App\Modules\ContentStudio\Models\ContentPiece::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        if ($pieces->isEmpty()) {
            return ['skor' => 40.0, 'sebab' => 'Tiada kandungan dijana dalam 30 hari'];
        }

        $diterbit = $pieces->where('status', 'published')->count();
        $nisbah = $pieces->count() > 0 ? $diterbit / $pieces->count() : 0;
        $skor = 40 + ($nisbah * 60);

        return [
            'skor' => round(min(100, $skor), 2),
            'dijana' => $pieces->count(),
            'diterbit' => $diterbit,
            'sebab' => 'Nisbah kandungan dijana → diterbitkan',
        ];
    }

    private function skorCompliance(int $tenantId, array $m): array
    {
        // Dari pakej pematuhan jika ada; default neutral
        $isu = 0;
        $kelas = '\\App\\Modules\\Compliance\\Models\\ComplianceItem';

        if (class_exists($kelas)) {
            $isu = $kelas::query()
                ->where('tenant_id', $tenantId)
                ->where('status', 'overdue')
                ->count();
        }

        $skor = max(0, 100 - ($isu * 15));

        return [
            'skor' => round($skor, 2),
            'isu_lewat' => $isu,
            'sebab' => 'Item pematuhan lewat',
        ];
    }

    // ---------------------------------------------------------------
    // Kajian bisnes
    // ---------------------------------------------------------------

    /**
     * Jalankan kajian bisnes dengan AI.
     */
    public function jalankanKajian(int $tenantId, array $input): BiStudy
    {
        $study = BiStudy::create([
            'tenant_id' => $tenantId,
            'title' => $input['title'] ?? 'Kajian Bisnes',
            'study_type' => $input['study_type'] ?? 'general',
            'status' => BiStudy::STATUS_RUNNING,
            'question' => $input['question'] ?? null,
            'created_by' => auth()->id(),
        ]);

        try {
            $data = $this->kumpulData($tenantId);
            $kesihatan = $this->kiraKesihatan($tenantId);

            $prompt = $this->binaPromptKajian($study, $data, $kesihatan);

            $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

            $hasil = $gateway->call(
                $tenantId,
                [
                    ['role' => 'system', 'content' => 'Anda perunding perniagaan yang analitikal. Jawab dalam JSON sahaja.'],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'business_study',
                ['max_output_tokens' => 1500, 'source_module' => 'business_intel']
            );

            $kandungan = (string) ($hasil['content'] ?? '');
            $json = $this->ekstrakJson($kandungan);

            // Jika AI tidak pulangkan JSON yang boleh digunakan (cth mod simulasi),
            // guna analisis heuristik daripada data sebenar supaya output berguna.
            if (empty($json['recommendations']) && empty($json['findings'])) {
                $study->update([
                    'status' => BiStudy::STATUS_COMPLETED,
                    'data_snapshot' => $data,
                    'summary' => $this->ringkasanHeuristik($kesihatan, $data),
                    'findings' => $this->penemuanHeuristik($kesihatan, $data),
                    'recommendations' => $this->cadanganHeuristik($kesihatan, $data),
                    'risks' => [],
                    'opportunities' => [],
                    'confidence' => 55,
                    'credits_used' => (int) ($hasil['usage']['credits_charged'] ?? 0),
                    'completed_at' => now(),
                ]);

                $this->janaIdeaDaripadaKajian($tenantId, $study->fresh());

                return $study->fresh();
            }

            $study->update([
                'status' => BiStudy::STATUS_COMPLETED,
                'data_snapshot' => $data,
                'summary' => $json['summary'] ?? $kandungan,
                'findings' => $json['findings'] ?? [],
                'recommendations' => $json['recommendations'] ?? [],
                'risks' => $json['risks'] ?? [],
                'opportunities' => $json['opportunities'] ?? [],
                'confidence' => (float) ($json['confidence'] ?? 60),
                'credits_used' => (int) ($hasil['usage']['credits_charged'] ?? 0),
                'completed_at' => now(),
            ]);

            // Jana idea daripada cadangan
            $this->janaIdeaDaripadaKajian($tenantId, $study);

            return $study->fresh();
        } catch (\Throwable $e) {
            Log::warning('BusinessIntel: kajian gagal', ['error' => $e->getMessage()]);

            // Fallback: kajian heuristik daripada data sebenar
            $data = $this->kumpulData($tenantId);
            $kesihatan = $this->kiraKesihatan($tenantId);

            $study->update([
                'status' => BiStudy::STATUS_COMPLETED,
                'data_snapshot' => $data,
                'summary' => $this->ringkasanHeuristik($kesihatan, $data),
                'findings' => $this->penemuanHeuristik($kesihatan, $data),
                'recommendations' => $this->cadanganHeuristik($kesihatan, $data),
                'risks' => [],
                'opportunities' => [],
                'confidence' => 50,
                'completed_at' => now(),
            ]);

            $this->janaIdeaDaripadaKajian($tenantId, $study);

            return $study->fresh();
        }
    }

    /**
     * Kumpul data perniagaan sebenar untuk kajian.
     */
    public function kumpulData(int $tenantId): array
    {
        $dari30 = now()->subDays(30);
        $dari90 = now()->subDays(90);

        $orders = \App\Modules\Orders\Models\UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $dari90)
            ->get();

        $jualan30 = (float) $orders->where('created_at', '>=', $dari30)->sum('total_amount');
        $kos30 = (float) $orders->where('created_at', '>=', $dari30)->sum('cost_amount');
        $pesanan30 = $orders->where('created_at', '>=', $dari30)->count();

        $ikutPlatform = $orders->where('created_at', '>=', $dari30)
            ->groupBy(fn ($o) => $o->source_platform ?: $o->source)
            ->map(fn ($g) => [
                'pesanan' => $g->count(),
                'jualan' => round((float) $g->sum('total_amount'), 2),
            ])
            ->toArray();

        $produkTeratas = \App\Modules\Orders\Models\UnifiedOrderItem::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', $dari30)
            ->selectRaw('name, SUM(quantity) as jumlah, SUM(line_total) as jualan')
            ->groupBy('name')
            ->orderByDesc('jualan')
            ->limit(10)
            ->get()
            ->toArray();

        return [
            'periode_hari' => 30,
            'jumlah_jualan_30' => round($jualan30, 2),
            'jumlah_kos_30' => round($kos30, 2),
            'margin_30' => round($jualan30 - $kos30, 2),
            'margin_percent' => $jualan30 > 0 ? round(($jualan30 - $kos30) / $jualan30 * 100, 2) : 0,
            'jumlah_pesanan_30' => $pesanan30,
            'purata_pesanan' => $pesanan30 > 0 ? round($jualan30 / $pesanan30, 2) : 0,
            'ikut_platform' => $ikutPlatform,
            'produk_teratas' => $produkTeratas,
            'pelanggan_unik' => (int) $orders->where('created_at', '>=', $dari30)->pluck('customer_email')->filter()->unique()->count(),
        ];
    }

    /**
     * Bina prompt kajian.
     */
    private function binaPromptKajian(BiStudy $study, array $data, array $kesihatan): string
    {
        $baris = [
            "Kajian: {$study->title}",
            "Jenis: {$study->study_type}",
        ];

        if ($study->question) {
            $baris[] = "Soalan: {$study->question}";
        }

        $baris[] = '';
        $baris[] = 'DATA PERNIAGAAN (30 hari):';
        $baris[] = '- Jualan: RM' . number_format($data['jumlah_jualan_30'], 2);
        $baris[] = '- Kos: RM' . number_format($data['jumlah_kos_30'], 2);
        $baris[] = '- Margin: RM' . number_format($data['margin_30'], 2) . " ({$data['margin_percent']}%)";
        $baris[] = "- Pesanan: {$data['jumlah_pesanan_30']} (purata RM" . number_format($data['purata_pesanan'], 2) . ')';
        $baris[] = "- Pelanggan unik: {$data['pelanggan_unik']}";

        if (! empty($data['ikut_platform'])) {
            $baris[] = '- Saluran: ' . implode(', ', array_map(
                fn ($k, $v) => "{$k} ({$v['pesanan']} pesanan, RM{$v['jualan']})",
                array_keys($data['ikut_platform']),
                $data['ikut_platform']
            ));
        }

        if (! empty($data['produk_teratas'])) {
            $baris[] = '- Produk teratas: ' . implode(', ', array_map(
                fn ($p) => "{$p['name']} (RM{$p['jualan']})",
                array_slice($data['produk_teratas'], 0, 5)
            ));
        }

        $baris[] = '';
        $baris[] = 'SKOR KESIHATAN PERNIAGAAN:';
        $baris[] = "- Keseluruhan: {$kesihatan['overall_score']}/100 (gred {$kesihatan['grade']})";

        foreach ($kesihatan['dimensions'] as $nama => $d) {
            $baris[] = "- {$nama}: {$d['skor']}/100 — {$d['sebab']}";
        }

        $baris[] = '';
        $baris[] = 'Berikan analisis dalam JSON dengan kunci:';
        $baris[] = 'summary (string), findings (array string), recommendations (array string),';
        $baris[] = 'risks (array string), opportunities (array string), confidence (0-100).';
        $baris[] = 'HANYA JSON, tiada teks lain. Guna Bahasa Malaysia.';

        return implode("\n", $baris);
    }

    private function ringkasanHeuristik(array $kesihatan, array $data): string
    {
        return sprintf(
            'Perniagaan menunjukkan skor kesihatan %s/100 (gred %s). '
            . 'Jualan 30 hari RM%s dengan margin %s%%. '
            . 'Dimensi paling lemah: %s.',
            number_format($kesihatan['overall_score'], 1),
            $kesihatan['grade'],
            number_format($data['jumlah_jualan_30'], 2),
            $data['margin_percent'],
            implode(' dan ', $kesihatan['weakest'])
        );
    }

    private function penemuanHeuristik(array $kesihatan, array $data): array
    {
        $penemuan = [];

        if ($data['margin_percent'] < 20) {
            $penemuan[] = "Margin {$data['margin_percent']}% di bawah sasaran 20% — kos perlu ditinjau.";
        }

        if ($data['purata_pesanan'] > 0 && $data['purata_pesanan'] < 100) {
            $penemuan[] = 'Purata nilai pesanan RM' . number_format($data['purata_pesanan'], 2) . ' — peluang upsell.';
        }

        if (count($data['ikut_platform']) > 1) {
            $penemuan[] = 'Jualan tersebar merentas ' . count($data['ikut_platform']) . ' saluran.';
        }

        foreach ($kesihatan['weakest'] as $dimensi) {
            $penemuan[] = "Dimensi '{$dimensi}' memerlukan perhatian.";
        }

        return $penemuan ?: ['Tiada penemuan ketara — data belum mencukupi.'];
    }

    private function cadanganHeuristik(array $kesihatan, array $data): array
    {
        $cadangan = [];

        foreach ($kesihatan['weakest'] as $dimensi) {
            $cadangan[] = match ($dimensi) {
                'sales' => 'Tingkatkan promosi dan follow-up prospek untuk menaikkan jualan.',
                'cashflow' => 'Kejar bayaran tertunggak dan tetapkan polisi deposit 50%.',
                'operations' => 'Kurangkan masa pemprosesan pesanan — tetapkan SLA 24 jam.',
                'customer' => 'Balas komen negatif dalam 24 jam dan kaji punca aduan berulang.',
                'marketing' => 'Jana dan terbitkan lebih banyak kandungan secara konsisten.',
                'compliance' => 'Selesaikan item pematuhan lewat segera.',
                default => "Tambah baik dimensi '{$dimensi}'.",
            };
        }

        if ($data['margin_percent'] < 20) {
            $cadangan[] = 'Semak kos pembekal dan pertimbangkan naik harga 5–10%.';
        }

        return $cadangan ?: ['Teruskan operasi sedia ada dan kumpul lebih banyak data.'];
    }

    /**
     * Jana idea daripada cadangan kajian.
     */
    public function janaIdeaDaripadaKajian(int $tenantId, BiStudy $study): array
    {
        $cadangan = $study->recommendations ?? [];
        $idea = [];

        foreach (array_slice($cadangan, 0, 10) as $cadanganTeks) {
            $teks = is_string($cadanganTeks) ? $cadanganTeks : json_encode($cadanganTeks);

            $idea[] = BiIdea::create([
                'tenant_id' => $tenantId,
                'bi_study_id' => $study->id,
                'title' => mb_substr($teks, 0, 120),
                'description' => $teks,
                'category' => $study->study_type === 'general' ? 'growth' : $study->study_type,
                'source' => 'ai',
                'impact' => BiIdea::IMPACT_MEDIUM,
                'effort' => BiIdea::IMPACT_MEDIUM,
                'impact_score' => (float) $study->confidence,
                'confidence' => (float) $study->confidence,
                'evidence' => $study->summary,
            ]);
        }

        return $idea;
    }

    // ---------------------------------------------------------------
    // Idea feed
    // ---------------------------------------------------------------

    /**
     * Idea feed — keutamaan ikut nisbah impak/effort.
     */
    public function ideaFeed(int $tenantId, ?string $status = null, int $limit = 50): array
    {
        $query = BiIdea::query()->where('tenant_id', $tenantId);

        if ($status) {
            $query->where('status', $status);
        } else {
            $query->whereIn('status', [BiIdea::STATUS_NEW, BiIdea::STATUS_ACCEPTED, BiIdea::STATUS_IN_PROGRESS]);
        }

        $ideas = $query->get()
            ->sortByDesc(fn ($i) => $i->nisbahKeutamaan())
            ->take($limit)
            ->values();

        return [
            'ideas' => $ideas->toArray(),
            'jumlah' => $ideas->count(),
            'ikut_kategori' => $ideas->groupBy('category')->map(fn ($g) => $g->count())->toArray(),
            'ikut_impak' => $ideas->groupBy('impact')->map(fn ($g) => $g->count())->toArray(),
        ];
    }

    /**
     * Terima idea.
     */
    public function terimaIdea(int $tenantId, int $ideaId): BiIdea
    {
        $idea = BiIdea::query()->where('tenant_id', $tenantId)->findOrFail($ideaId);

        $idea->update([
            'status' => BiIdea::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);

        // Rekod pembelajaran: cadangan ini diterima
        $this->rekodPembelajaran($tenantId, [
            'learning_type' => BiLearning::TYPE_PREFERENCE,
            'subject_type' => BiIdea::class,
            'subject_id' => $idea->id,
            'insight' => "Tenant menerima idea kategori '{$idea->category}': {$idea->title}",
            'confidence' => 70,
        ]);

        return $idea->fresh();
    }

    /**
     * Tolak idea — AI belajar apa yang tidak berguna.
     */
    public function tolakIdea(int $tenantId, int $ideaId, ?string $sebab = null): BiIdea
    {
        $idea = BiIdea::query()->where('tenant_id', $tenantId)->findOrFail($ideaId);

        $idea->update(['status' => BiIdea::STATUS_REJECTED]);

        $this->rekodPembelajaran($tenantId, [
            'learning_type' => BiLearning::TYPE_CORRECTION,
            'subject_type' => BiIdea::class,
            'subject_id' => $idea->id,
            'insight' => "Tenant menolak idea kategori '{$idea->category}'" . ($sebab ? ": {$sebab}" : ''),
            'confidence' => 60,
            'evidence' => $sebab ? ['reason' => $sebab] : null,
        ]);

        return $idea->fresh();
    }

    /**
     * Lengkapkan idea dengan hasil sebenar.
     */
    public function lengkapkanIdea(int $tenantId, int $ideaId, ?float $hasilSebenar = null): BiIdea
    {
        $idea = BiIdea::query()->where('tenant_id', $tenantId)->findOrFail($ideaId);

        $idea->update([
            'status' => BiIdea::STATUS_DONE,
            'done_at' => now(),
            'actual_outcome' => $hasilSebenar,
        ]);

        $this->rekodPembelajaran($tenantId, [
            'learning_type' => BiLearning::TYPE_OUTCOME,
            'subject_type' => BiIdea::class,
            'subject_id' => $idea->id,
            'insight' => 'Idea selesai'
                . ($hasilSebenar !== null ? " dengan hasil RM" . number_format($hasilSebenar, 2) : ''),
            'confidence' => 80,
            'evidence' => ['actual_outcome' => $hasilSebenar],
        ]);

        return $idea->fresh();
    }

    // ---------------------------------------------------------------
    // Competitor watch
    // ---------------------------------------------------------------

    /**
     * Rekod snapshot pesaing + analisis AI.
     */
    public function rekodSnapshotPesaing(int $tenantId, int $competitorId, array $data): BiCompetitorSnapshot
    {
        $pesaing = BiCompetitor::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($competitorId);

        // Banding dengan snapshot terakhir untuk kesan perubahan
        $terakhir = BiCompetitorSnapshot::query()
            ->where('tenant_id', $tenantId)
            ->where('bi_competitor_id', $competitorId)
            ->orderByDesc('snapshot_date')
            ->first();

        $perubahan = [];
        if ($terakhir && ! empty($data['observed_data'])) {
            $perubahan = $this->bandingData(
                $terakhir->observed_data ?? [],
                $data['observed_data']
            );
        }

        $snapshot = BiCompetitorSnapshot::create([
            'tenant_id' => $tenantId,
            'bi_competitor_id' => $competitorId,
            'snapshot_date' => now()->toDateString(),
            'observed_data' => $data['observed_data'] ?? null,
            'changes' => $perubahan ?: null,
            'ai_analysis' => $data['ai_analysis'] ?? null,
            'threats' => $data['threats'] ?? null,
            'opportunities' => $data['opportunities'] ?? null,
            'source_type' => $data['source_type'] ?? 'manual',
            'source_url' => $data['source_url'] ?? null,
        ]);

        // Jana idea jika ada ancaman atau peluang
        if (! empty($data['opportunities'])) {
            foreach ((array) $data['opportunities'] as $peluang) {
                BiIdea::create([
                    'tenant_id' => $tenantId,
                    'title' => 'Peluang vs ' . $pesaing->name . ': ' . mb_substr((string) $peluang, 0, 100),
                    'description' => (string) $peluang,
                    'category' => 'growth',
                    'source' => 'competitor',
                    'impact' => BiIdea::IMPACT_HIGH,
                    'effort' => BiIdea::IMPACT_MEDIUM,
                    'impact_score' => 75,
                    'confidence' => 65,
                    'evidence' => 'Analisis pesaing: ' . $pesaing->name,
                ]);
            }
        }

        return $snapshot;
    }

    private function bandingData(array $lama, array $baru): array
    {
        $perubahan = [];

        foreach ($baru as $kunci => $nilai) {
            $sebelum = $lama[$kunci] ?? null;

            if ($sebelum === $nilai) {
                continue;
            }

            $perubahan[] = [
                'field' => $kunci,
                'from' => $sebelum,
                'to' => $nilai,
            ];
        }

        return $perubahan;
    }

    /**
     * Ringkasan persaingan.
     */
    public function ringkasanPersaingan(int $tenantId): array
    {
        $pesaing = BiCompetitor::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $snapshots = BiCompetitorSnapshot::query()
            ->where('tenant_id', $tenantId)
            ->where('snapshot_date', '>=', now()->subDays(90)->toDateString())
            ->get();

        return [
            'jumlah_pesaing' => $pesaing->count(),
            'ikut_ancaman' => $pesaing->groupBy('threat_level')->map(fn ($g) => $g->count())->toArray(),
            'snapshot_90_hari' => $snapshots->count(),
            'perubahan_dikesan' => $snapshots->filter(fn ($s) => ! empty($s->changes))->count(),
            'ancaman_tinggi' => $pesaing->where('threat_level', BiCompetitor::THREAT_HIGH)
                ->map(fn ($p) => ['name' => $p->name, 'weaknesses' => $p->weaknesses])
                ->values()
                ->toArray(),
        ];
    }

    // ---------------------------------------------------------------
    // Sinyal pasaran
    // ---------------------------------------------------------------

    /**
     * Jana sinyal pasaran daripada data sendiri.
     */
    public function janaSinyalPasaran(int $tenantId): array
    {
        $data = $this->kumpulData($tenantId);
        $sinyal = [];

        // Trend produk teratas
        foreach (array_slice($data['produk_teratas'], 0, 5) as $produk) {
            $sinyal[] = BiMarketSignal::create([
                'tenant_id' => $tenantId,
                'signal_type' => BiMarketSignal::TYPE_TREND,
                'subject' => $produk['name'],
                'direction' => 'up',
                'strength' => min(100, (float) ($produk['jualan'] / max(1, $data['jumlah_jualan_30']) * 100)),
                'summary' => "Produk terlaris: RM{$produk['jualan']} dalam 30 hari",
                'evidence' => $produk,
                'source' => 'analytics',
                'observed_at' => now()->toDateString(),
            ]);
        }

        // Saluran terbaik
        $saluranTerbaik = collect($data['ikut_platform'])->sortByDesc('jualan')->first();

        if ($saluranTerbaik) {
            $sinyal[] = BiMarketSignal::create([
                'tenant_id' => $tenantId,
                'signal_type' => BiMarketSignal::TYPE_CATEGORY,
                'subject' => array_search($saluranTerbaik, $data['ikut_platform'], true) ?: 'saluran',
                'direction' => 'up',
                'strength' => 70,
                'summary' => 'Saluran jualan terbaik bulan ini',
                'evidence' => $saluranTerbaik,
                'source' => 'analytics',
                'observed_at' => now()->toDateString(),
            ]);
        }

        return $sinyal;
    }

    // ---------------------------------------------------------------
    // Learning loop
    // ---------------------------------------------------------------

    /**
     * Rekod pembelajaran AI.
     */
    public function rekodPembelajaran(int $tenantId, array $data): BiLearning
    {
        return BiLearning::create(array_merge([
            'tenant_id' => $tenantId,
            'learning_type' => BiLearning::TYPE_PATTERN,
            'confidence' => 50,
            'is_active' => true,
        ], $data));
    }

    /**
     * Senarai pembelajaran aktif.
     */
    public function pembelajaranAktif(int $tenantId, int $limit = 30): array
    {
        return BiLearning::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('confidence')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    // ---------------------------------------------------------------
    // Trend kesihatan
    // ---------------------------------------------------------------

    /**
     * Trend kesihatan 90 hari.
     */
    public function trendKesihatan(int $tenantId, int $hari = 90): array
    {
        $snapshots = BiHealthSnapshot::query()
            ->where('tenant_id', $tenantId)
            ->where('snapshot_date', '>=', now()->subDays($hari)->toDateString())
            ->orderBy('snapshot_date')
            ->get();

        if ($snapshots->isEmpty()) {
            return ['snapshots' => [], 'trend' => 'stable', 'perubahan' => 0.0];
        }

        $pertama = (float) $snapshots->first()->overall_score;
        $terakhir = (float) $snapshots->last()->overall_score;
        $perubahan = round($terakhir - $pertama, 2);

        return [
            'snapshots' => $snapshots->map(fn ($s) => [
                'date' => $s->snapshot_date->toDateString(),
                'score' => (float) $s->overall_score,
                'grade' => $s->grade,
            ])->toArray(),
            'trend' => $perubahan > 2 ? 'up' : ($perubahan < -2 ? 'down' : 'stable'),
            'perubahan' => $perubahan,
            'terkini' => $terakhir,
            'gred' => $snapshots->last()->grade,
        ];
    }

    // ---------------------------------------------------------------
    // Utiliti
    // ---------------------------------------------------------------

    private function jumlahJualan(int $tenantId, $dari, $hingga): float
    {
        return (float) \App\Modules\Orders\Models\UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'draft'])
            ->whereBetween('created_at', [$dari, $hingga])
            ->sum('total_amount');
    }

    private function jumlahBelumBayar(int $tenantId): float
    {
        return (float) \App\Modules\Orders\Models\UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'draft', 'refunded'])
            ->where('payment_status', '!=', 'paid')
            ->selectRaw('SUM(total_amount - paid_amount) as baki')
            ->value('baki') ?? 0.0;
    }

    private function ekstrakJson(string $teks): array
    {
        $decoded = json_decode($teks, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $teks, $m)) {
            $decoded = json_decode($m[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }
}
