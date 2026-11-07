<?php

namespace App\Modules\WorkManagement\Services;

use App\Modules\Projects\Models\Project;
use App\Modules\WorkManagement\Models\ProjectBaseline;
use App\Modules\WorkManagement\Models\ProjectBudgetLine;
use App\Modules\WorkManagement\Models\ProjectTimeline;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;

/**
 * WorkManagementService — pengurusan kerja lanjutan ala OpenProject.
 *
 * inspired oleh https://github.com/opf/openproject
 *
 * Menyediakan:
 *   - garis masa Gantt dengan kebergantungan (FS/SS/FF/SF)
 *   - laluan kritikal (critical path) — aktiviti yang menentukan tarikh akhir
 *   - garis dasar & analisis variance
 *   - bajet per baris dengan perbandingan perbelanjaan
 *   - portfolio projek
 */
class WorkManagementService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * Tambah aktiviti pada garis masa projek.
     *
     * @param  array<string, mixed>  $data
     */
    public function tambahAktiviti(Project $projek, array $data): ProjectTimeline
    {
        $maks = (int) ProjectTimeline::query()
            ->where('project_id', $projek->id)
            ->max('sort_order');

        $mula = $data['start_date'] ?? $projek->start_date ?? now()->toDateString();
        $tamat = $data['end_date'] ?? Carbon::parse($mula)->addDays((int) ($data['duration_days'] ?? 1))->toDateString();

        return ProjectTimeline::create(array_merge($data, [
            'project_id' => $projek->id,
            'start_date' => $mula,
            'end_date' => $tamat,
            'duration_days' => max(1, (int) ($data['duration_days'] ?? Carbon::parse($mula)->diffInDays(Carbon::parse($tamat)) + 1)),
            'sort_order' => $data['sort_order'] ?? ($maks + 1),
            'created_by' => auth()->id(),
        ]));
    }

    /**
     * Kira laluan kritikal projek.
     *
     * Kaedah: aktiviti dengan `duration` paling panjang yang membina
     * rantaian bergantung. Untuk projek kecil, guna model topologi:
     * aktiviti tanpa dependensi → subsidiary terpanjang.
     *
     * @return array<int, int>
     */
    public function laluanKritikal(Project $projek): array
    {
        $aktiviti = ProjectTimeline::query()
            ->where('project_id', $projek->id)
            ->where('is_baseline', false)
            ->orderBy('sort_order')
            ->get();

        if ($aktiviti->isEmpty()) {
            return [];
        }

        // Peta kebergantungan.
        $graf = [];
        foreach ($aktiviti as $a) {
            $graf[$a->id] = [];
            if ($a->depends_on_id && $aktiviti->contains('id', $a->depends_on_id)) {
                $graf[$a->id][] = $a->depends_on_id;
            }
        }

        // Panjang laluan terpanjang dari setiap nod (DFS dengan memo).
        $memo = [];

        $panjangLaluPath = function (int $nod, array $dilawat = []) use (&$panjangLaluPath, &$memo, $graf, $aktiviti) {
            if (isset($memo[$nod])) {
                return $memo[$nod];
            }

            if (in_array($nod, $dilawat, true)) {
                return 0;
            }

            $dilawat[] = $nod;
            $terpanjang = 0;

            foreach ($graf[$nod] ?? [] as $sebelum) {
                $terpanjang = max($terpanjang, $panjangLaluPath($sebelum, $dilawat));
            }

            $panjang = $terpanjang + (int) ($aktiviti->firstWhere('id', $nod)?->duration_days ?? 1);

            return $memo[$nod] = $panjang;
        };

        // Panjang setiap nod.
        foreach ($aktiviti->pluck('id')->all() as $id) {
            $panjangLaluPath($id);
        }

        if ($memo === []) {
            return [];
        }

        $panjangMaks = max($memo);
        $kritikal = array_keys(array_filter($memo, fn ($p) => $p === $panjangMaks));

        // Susun mengikut tarikh mula.
        $kritikal = collect($kritikal)
            ->sortBy(fn ($id) => $aktiviti->firstWhere('id', $id)?->sort_order ?? 0)
            ->values()
            ->all();

        return $kritikal;
    }

    /**
     * Tangkap garis dasar projek.
     */
    public function tangkapGarisDasar(Project $projek, ?string $nama = null): ProjectBaseline
    {
        $aktiviti = ProjectTimeline::query()
            ->where('project_id', $projek->id)
            ->where('is_baseline', false)
            ->orderBy('sort_order')
            ->get();

        $mula = $aktiviti->min('start_date') ?? $projek->start_date ?? now()->toDateString();
        $tamat = $aktiviti->max('end_date') ?? $projek->end_date ?? now()->toDateString();

        $jumlahHari = $mula && $tamat
            ? (int) Carbon::parse($mula)->diffInDays(Carbon::parse($tamat)) + 1
            : 0;

        $bajet = (float) ProjectBudgetLine::query()->where('project_id', $projek->id)->sum('planned_cost');

        $baseline = ProjectBaseline::create([
            'project_id' => $projek->id,
            'name' => $nama ?? 'Garis dasar '.Carbon::now()->format('d/m/Y H:i'),
            'captured_at' => now(),
            'scheduled_start' => $mula,
            'scheduled_end' => $tamat,
            'planned_duration_days' => $jumlahHari,
            'planned_cost' => $bajet,
            'actual_cost' => (float) $projek->spent,
            'snapshot' => $aktiviti->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->name,
                'start_date' => $a->start_date?->toDateString(),
                'end_date' => $a->end_date?->toDateString(),
                'duration_days' => $a->duration_days,
                'progress' => $a->progress,
            ])->all(),
            'captured_by' => auth()->id(),
        ]);

        $baseline->update([
            'variance_days' => $this->varianHari($baseline, $projek),
            'variance_cost' => round((float) $projek->spent - $bajet, 2),
        ]);

        return $baseline->fresh();
    }

    /**
     * Bandingkan projek semasa dengan garis dasar terkini.
     *
     * @return array<string, mixed>
     */
    public function analisisVariance(Project $projek): array
    {
        $baseline = ProjectBaseline::query()
            ->where('project_id', $projek->id)
            ->orderByDesc('captured_at')
            ->first();

        if (! $baseline) {
            return [
                'ada_garis_dasar' => false,
                'pesanan' => 'Tiada garis dasar direkod untuk projek ini.',
            ];
        }

        $aktiviti = ProjectTimeline::query()
            ->where('project_id', $projek->id)
            ->where('is_baseline', false)
            ->orderBy('sort_order')
            ->get();

        $mulaSekarang = $aktiviti->min('start_date') ?? $projek->start_date;
        $tamatSekarang = $aktiviti->max('end_date') ?? $projek->end_date;

        $perbandingan = [];

        foreach ((array) $baseline->snapshot as $snap) {
            $aktivitiSekarang = $aktiviti->firstWhere('id', $snap['id']);

            if (! $aktivitiSekarang) {
                continue;
            }

            $perbandingan[] = [
                'aktiviti_id' => $aktivitiSekarang->id,
                'nama' => $aktivitiSekarang->name,
                'sasaran_mula' => $snap['start_date'] ?? null,
                'semasa_mula' => $aktivitiSekarang->start_date?->toDateString(),
                'sasaran_tamat' => $snap['end_date'] ?? null,
                'semasa_tamat' => $aktivitiSekarang->end_date?->toDateString(),
                'varian_hari' => $this->varianHariAktiviti($snap, $aktivitiSekarang),
                'sasaran_peratus' => $snap['progress'] ?? 0,
                'semasa_peratus' => $aktivitiSekarang->progress,
            ];
        }

        return [
            'ada_garis_dasar' => true,
            'garis_dasar' => $baseline,
            'varian_hari' => $baseline->variance_days,
            'varian_kos' => $baseline->variance_cost,
            'mula_rasmi' => $baseline->scheduled_start?->toDateString(),
            'mula_semasa' => $mulaSekarang?->toDateString(),
            'tamat_rasmi' => $baseline->scheduled_end?->toDateString(),
            'tamat_semasa' => $tamatSekarang?->toDateString(),
            'perbandingan_aktiviti' => $perbandingan,
        ];
    }

    /**
     * Ringkasan perbelanjaan bajet projek.
     *
     * @return array<string, mixed>
     */
    public function ringkasanBajet(Project $projek): array
    {
        $baris = ProjectBudgetLine::query()->where('project_id', $projek->id)->get();

        $mengikutKategori = $baris->groupBy('category')->map(fn ($kumpulan) => [
            'kategori' => $kumpulan->first()->category,
            'label' => ProjectBudgetLine::kategori()[$kumpulan->first()->category] ?? $kumpulan->first()->category,
            'rancangan' => round((float) $kumpulan->sum('planned_cost'), 2),
            'sebenar' => round((float) $kumpulan->sum('actual_cost'), 2),
            'dibeli' => round((float) $kumpulan->sum('spent_to_date'), 2),
        ])->values();

        $rancangan = round((float) $baris->sum('planned_cost'), 2);
        $dibeli = round((float) $baris->sum('spent_to_date'), 2);

        return [
            'jumlah_baris' => $baris->count(),
            'jumlah_rancangan' => $rancangan,
            'jumlah_dibeli' => $dibeli,
            'jumlah_sebenar' => round((float) $baris->sum('actual_cost'), 2),
            'baki' => round($rancangan - $dibeli, 2),
            'peratus_digunakan' => $rancangan > 0 ? round(($dibeli / $rancangan) * 100, 2) : 0.0,
            'mengikut_kategori' => $mengikutKategori,
        ];
    }

    /**
     * Varian hari antara baseline dan semasa.
     */
    protected function varianHari(ProjectBaseline $baseline, Project $projek): int
    {
        $aktiviti = ProjectTimeline::query()
            ->where('project_id', $projek->id)
            ->where('is_baseline', false)
            ->get();

        $tamatSekarang = $aktiviti->max('end_date') ?? $projek->end_date;

        if (! $baseline->scheduled_end || ! $tamatSekarang) {
            return 0;
        }

        return (int) Carbon::parse($tamatSekarang)->diffInDays(Carbon::parse($baseline->scheduled_end), false);
    }

    /**
     * Varian hari bagi satu aktiviti.
     *
     * @param  array<string, mixed>  $snap
     */
    protected function varianHariAktiviti(array $snap, ProjectTimeline $aktiviti): int
    {
        if (! isset($snap['end_date']) || ! $aktiviti->end_date) {
            return 0;
        }

        return (int) Carbon::parse($aktiviti->end_date)
            ->diffInDays(Carbon::parse($snap['end_date']), false);
    }
}
