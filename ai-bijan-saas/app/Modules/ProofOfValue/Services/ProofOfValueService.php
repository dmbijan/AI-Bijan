<?php

namespace App\Modules\ProofOfValue\Services;

use App\Modules\ProofOfValue\Models\PovBaseline;
use App\Modules\ProofOfValue\Models\PovExport;
use App\Modules\ProofOfValue\Models\PovMetric;
use App\Modules\ProofOfValue\Models\PovReading;
use Illuminate\Support\Facades\Log;

/**
 * ProofOfValueService — metrik hasil per modul.
 *
 * ✅ IDEA #5 (BAB 84): ServiceTitan Pro Products/results boleh diukur.
 * AI BIJAN perlu corak sama: setiap modul mesti bolehventure
 * "berapa banyak nilai yang AI BIJAN hasilkan untuk saya?"
 *
 * Ini membezakan "produk bagus" daripada "platform yang boleh dibuktikan".
 */
class ProofOfValueService
{
    /**
     * Katalog standard metrik hasil mengikut modul.
     *
     * Setiap tenant boleh pilih mana yang relevan. Ini adalah cadangan
     * berdasarkan corak ServiceTitan + modul AI BIJAN yang sedia ada.
     *
     * @var array<string, array<int, array{key: string, name: string, unit: string, direction: string, description: string}>>
     */
    public const KATALOG = [
        'inventory' => [
            ['key' => 'stockout_reduction', 'name' => 'Pengurangan Kehabisan Stok', 'unit' => 'percent', 'direction' => 'down', 'description' => 'Bekalan stok berbanding sebelum'],
            ['key' => 'waste_reduction', 'name' => 'Pengurangan Pembaziran', 'unit' => 'percent', 'direction' => 'down', 'description' => 'Nilai barang rosak / kedaluwarsa'],
            ['key' => 'turnover_improvement', 'name' => 'Peningkatan Pusing Stok', 'unit' => 'percent', 'direction' => 'up', 'description' => 'Inventory turnover'],
        ],
        'sales' => [
            ['key' => 'average_ticket', 'name' => 'Purata Nilai Transaksi', 'unit' => 'rm', 'direction' => 'up', 'description' => 'Nilai purata satu jualan'],
            ['key' => 'conversion_rate', 'name' => 'Kadar Penukaran', 'unit' => 'percent', 'direction' => 'up', 'description' => 'Deal tertutup ÷ deal Spectral'],
            ['key' => 'revenue_growth', 'name' => 'Pertumbuhan Hasil', 'unit' => 'percent', 'direction' => 'up', 'description' => 'Perbandingan tempoh'],
        ],
        'marketing' => [
            ['key' => 'cost_per_acquisition', 'name' => 'Kos Perolehan Pelanggan', 'unit' => 'rm', 'direction' => 'down', 'description' => 'Kos marketing ÷ pelanggan baharu'],
            ['key' => 'open_rate', 'name' => 'Kadar Buka Blast', 'unit' => 'percent', 'direction' => 'up', 'description' => 'E-mel dibuka ÷ dihantar'],
            ['key' => 'unsubscribe_rate', 'name' => 'Kadar Langganan Berhenti', 'unit' => 'percent', 'direction' => 'down', 'description' => 'Unsubscribe ÷ dihantar'],
        ],
        'hr' => [
            ['key' => 'overtime_reduction', 'name' => 'Pengurangan Kerja Lepas Masa', 'unit' => 'percent', 'direction' => 'down', 'description' => 'Jam overtime ÷ jam kerja'],
            ['key' => 'time_to_hire', 'name' => 'Masa Tooka', 'unit' => 'days', 'direction' => 'down', 'description' => 'Hari dari advertisement ke tawaran'],
            ['key' => 'attendance_accuracy', 'name' => 'Ketepatan Kehadiran', 'unit' => 'percent', 'direction' => 'up', 'description' => 'Kehadiran direkod ÷ dijadualkan'],
        ],
        'finance' => [
            ['key' => 'invoice_collection_days', 'name' => 'Hari Kutipan Invois', 'unit' => 'days', 'direction' => 'down', 'description' => 'Purata hari dari invois ke bayaran'],
            ['key' => 'reconciliation_time', 'name' => 'Masa Rekonsiliasi', 'unit' => 'minutes', 'direction' => 'down', 'description' => 'Masa untuk saman bank'],
            ['key' => 'cost_saving', 'name' => 'Jimat Kos', 'unit' => 'rm', 'direction' => 'up', 'description' => 'Kos dielakkan'],
        ],
        'support' => [
            ['key' => 'first_response_time', 'name' => 'Masa Balasan Pertama', 'unit' => 'minutes', 'direction' => 'down', 'description' => 'Masa dari tiket ke balasan pertama'],
            ['key' => 'ai_resolution_rate', 'name' => 'Kadar Penyelesaian AI', 'unit' => 'percent', 'direction' => 'up', 'description' => '% tiket diselesaikan tanpa manusia'],
            ['key' => 'csat', 'name' => 'CSAT', 'unit' => 'percent', 'direction' => 'up', 'description' => 'Customer Satisfaction Score'],
        ],
        'logistics' => [
            ['key' => 'on_time_delivery', 'name' => 'Penghantaran Tepat Masa', 'unit' => 'percent', 'direction' => 'up', 'description' => 'Tepat masa ÷ jumlah penghantaran'],
            ['key' => 'fuel_cost_reduction', 'name' => 'Pengurangan Kos Bahan Api', 'unit' => 'percent', 'direction' => 'down', 'description' => 'Kos bahan api per penghantaran'],
            ['key' => 'accident_rate', 'name' => 'Kadar Kemalangan', 'unit' => 'percent', 'direction' => 'down', 'description' => 'Kemalangan ÷ perjalanan'],
        ],
        'cod' => [
            ['key' => 'cod_collection_rate', 'name' => 'Kadar Kutipan COD', 'unit' => 'percent', 'direction' => 'up', 'description' => 'Dikutip ÷ dijangka'],
            ['key' => 'cod_failure_rate', 'name' => 'Kadar Gagal COD', 'unit' => 'percent', 'direction' => 'down', 'description' => 'Gagal ÷ jumlah COD'],
        ],
    ];

    /**
     * Jana katalog metrik standard untuk tenant (idempotent).
     *
     * @return int bilangan metrik dicipta
     */
    public function janaKatalog(int $tenantId, ?string $module = null): int
    {
        $dicipta = 0;

        foreach (self::KATALOG as $modul => $metrik) {
            if ($module && $modul !== $module) {
                continue;
            }

            foreach ($metrik as $definisi) {
                $ada = PovMetric::query()
                    ->where('tenant_id', $tenantId)
                    ->where('module', $modul)
                    ->where('key', $definisi['key'])
                    ->exists();

                if ($ada) {
                    continue;
                }

                PovMetric::create([
                    'tenant_id' => $tenantId,
                    'module' => $modul,
                    'key' => $definisi['key'],
                    'name' => $definisi['name'],
                    'description' => $definisi['description'],
                    'unit' => $definisi['unit'],
                    'direction' => $definisi['direction'],
                    'is_active' => true,
                    'is_verified' => false,
                ]);

                $dicipta++;
            }
        }

        return $dicipta;
    }

    /**
     * Rekod bacaan metrik.
     */
    public function rekodBacaan(int $tenantId, int $metricId, float $value, ?string $tarikh = null, ?string $source = null): PovReading
    {
        $metric = PovMetric::query()
            ->where('tenant_id', $tenantId)
            ->find($metricId);

        if (! $metric) {
            throw new \InvalidArgumentException('Metrik tidak dijumpai');
        }

        $tarikh = $tarikh ?? now()->toDateString();

        // Kira perubahan berbanding bacaan sebelum
        $sebelum = PovReading::query()
            ->where('metric_id', $metricId)
            ->where('reading_date', '<', $tarikh)
            ->orderByDesc('reading_date')
            ->first();

        $change = null;
        if ($sebelum && (float) $sebelum->value != 0.0) {
            $change = round((($value - (float) $sebelum->value) / abs((float) $sebelum->value)) * 100, 2);
        }

        $reading = PovReading::updateOrCreate(
            ['tenant_id' => $tenantId, 'metric_id' => $metricId, 'reading_date' => $tarikh],
            [
                'value' => $value,
                'change_percent' => $change,
                'source' => $source,
            ]
        );

        return $reading;
    }

    /**
     * Rekod baseline.
     */
    public function rekodBaseline(int $tenantId, int $metricId, float $value, ?string $tarikh = null, ?string $source = null, ?string $note = null): PovBaseline
    {
        return PovBaseline::create([
            'tenant_id' => $tenantId,
            'metric_id' => $metricId,
            'baseline_date' => $tarikh ?? now()->toDateString(),
            'baseline_value' => $value,
            'source' => $source,
            'note' => $note,
            'recorded_by' => auth()->id(),
        ]);
    }

    /**
     * Ringkasan bukti nilai untuk semua modul.
     *
     * @return array{modules: array, ringkasan: array}
     */
    public function ringkasan(int $tenantId): array
    {
        $metrics = PovMetric::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $perModule = [];

        foreach ($metrics as $metric) {
            $perModule[$metric->module][] = [
                'id' => $metric->id,
                'key' => $metric->key,
                'name' => $metric->name,
                'unit' => $metric->unit,
                'direction' => $metric->direction,
                'target_value' => $metric->target_value !== null ? (float) $metric->target_value : null,
                'current_value' => $metric->bacaanTerkini()?->value,
                'baseline_value' => $metric->baselineTerkini()?->baseline_value,
                'change_percent' => $metric->perubahan(),
                'target_met' => $metric->sasaranTercapai(),
                'is_verified' => $metric->is_verified,
            ];
        }

        $verifikasi = $metrics->where('is_verified', true)->count();
        $denganBaseline = $metrics->filter(fn ($m) => $m->baselineTerkini() !== null)->count();
        $sasaranTercapai = $metrics->filter(fn ($m) => $m->sasaranTercapai())->count();

        return [
            'modules' => $perModule,
            'ringkasan' => [
                'jumlah_metrik' => $metrics->count(),
                'jumlah_modul' => count($perModule),
                'metrik_dengan_baseline' => $denganBaseline,
                'metrik_terverifikasi' => $verifikasi,
                'sasaran_tercapai' => $sasaranTercapai,
                'kadar_verifikasi' => $metrics->count() > 0 ? round(($verifikasi / $metrics->count()) * 100, 1) : 0.0,
                'generated_at' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Jana eksport bukti jualan.
     */
    public function janaEksport(int $tenantId, string $nama, string $dari, string $hingga): PovExport
    {
        $ringkasan = $this->ringkasan($tenantId);

        return PovExport::create([
            'tenant_id' => $tenantId,
            'name' => $nama,
            'period_start' => $dari,
            'period_end' => $hingga,
            'metrics' => $ringkasan,
            'status' => 'draft',
            'generated_by' => auth()->id(),
        ]);
    }

    /**
     * Sahkan metrik (tandakan data disahkan).
     */
    public function sahkanMetrik(int $tenantId, int $metricId, bool $disahkan, ?string $nota = null): PovMetric
    {
        $metric = PovMetric::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($metricId);

        $metric->update([
            'is_verified' => $disahkan,
            'verification_note' => $nota,
        ]);

        Log::info('ProofOfValue: metrik disahkan', [
            'tenant_id' => $tenantId,
            'metric_id' => $metricId,
            'verified' => $disahkan,
        ]);

        return $metric->fresh();
    }

    /**
     * Senarai modul yang ada metrik (untuk UI).
     */
    public function modulDenganMetrik(int $tenantId): array
    {
        return PovMetric::query()
            ->where('tenant_id', $tenantId)
            ->distinct()
            ->pluck('module')
            ->sort()
            ->values()
            ->toArray();
    }
}
