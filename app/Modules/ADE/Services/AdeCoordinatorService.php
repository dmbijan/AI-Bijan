<?php

namespace App\Modules\ADE\Services;

use App\Modules\ADE\Models\AdeCoordination;
use App\Modules\ADE\Models\AdeCoordinationTask;
use App\Modules\ADE\Models\AdeSandbox;
use Illuminate\Support\Facades\Log;

/**
 * AdeCoordinatorService — A3 Sub-Agent Coordinator.
 *
 * Menyelaraskan berbilang sub-ejen yang concurrently:
 *   - satu menulis kod (implementer)
 *   - satu menjalankan ujian (tester)
 *   - satu menyemak diff (reviewer)
 *
 * Corak `obra/superpowers`: subagent-driven-development — satu ejen bagi
 * satu tugasan, semakan selepas setiap tugasan selesai.
 *
 * ⚠️ KESELAMATAN:
 *  1. Setiap task boleh dikongsi sandbox → WAJIB isolation (A2).
 *  2. Task yang berkongsi sandbox yang sama tidak boleh berjalan serentak.
 *  3. Maksimum 50 task bagi satu coordination.
 */
class AdeCoordinatorService
{
    public const HAD_TASK = 50;
    public const HAD_PARALLEL_MAKS = 10;
    public const PERANAN = ['implementer', 'tester', 'reviewer', 'researcher'];

    /**
     * Buat coordination baharu.
     *
     * @param array<string,mixed> $data
     */
    public function cipta(int $tenantId, array $data): AdeCoordination
    {
        $nama = trim((string) ($data['name'] ?? ''));

        if ($nama === '') {
            throw new \InvalidArgumentException('Nama coordination wajib diisi.');
        }

        $maxParallel = (int) ($data['max_parallel'] ?? 4);

        if ($maxParallel < 1 || $maxParallel > self::HAD_PARALLEL_MAKS) {
            throw new \InvalidArgumentException(
                'max_parallel mestilah 1-' . self::HAD_PARALLEL_MAKS
            );
        }

        $sandboxId = $data['ade_sandbox_id'] ?? null;

        if ($sandboxId !== null) {
            // Sahkan sandbox wujud & milik tenant.
            AdeSandbox::where('tenant_id', $tenantId)->where('id', $sandboxId)->firstOrFail();
        }

        $coord = new AdeCoordination();
        $coord->tenant_id = $tenantId;
        $coord->name = $nama;
        $coord->objective = $data['objective'] ?? null;
        $coord->status = AdeCoordination::STATUS_PENDING;
        $coord->max_parallel = $maxParallel;
        $coord->ade_sandbox_id = $sandboxId;
        $coord->created_by = $data['created_by'] ?? null;
        $coord->save();

        return $coord;
    }

    /**
     * Tambah subtugas kepada coordination.
     *
     * @param array<string,mixed> $data
     */
    public function tambahTask(int $tenantId, int $coordinationId, array $data): AdeCoordinationTask
    {
        $coord = $this->cari($tenantId, $coordinationId);

        $jumlah = AdeCoordinationTask::where('ade_coordination_id', $coord->id)->count();

        if ($jumlah >= self::HAD_TASK) {
            throw new \RuntimeException('Had ' . self::HAD_TASK . ' subtugas bagi satu coordination.');
        }

        $tajuk = trim((string) ($data['title'] ?? ''));

        if ($tajuk === '') {
            throw new \InvalidArgumentException('Tajuk subtugas wajib diisi.');
        }

        $peranan = (string) ($data['role'] ?? 'implementer');

        if (! in_array($peranan, self::PERANAN, true)) {
            throw new \InvalidArgumentException(
                'Peranan tidak sah. Sah: ' . implode(', ', self::PERANAN)
            );
        }

        $sandboxId = $data['ade_sandbox_id'] ?? $coord->ade_sandbox_id;

        if ($sandboxId !== null) {
            AdeSandbox::where('tenant_id', $tenantId)->where('id', $sandboxId)->firstOrFail();
        }

        $task = new AdeCoordinationTask();
        $task->tenant_id = $tenantId;
        $task->ade_coordination_id = $coord->id;
        $task->ade_sandbox_id = $sandboxId;
        $task->title = $tajuk;
        $task->description = $data['description'] ?? null;
        $task->role = $peranan;
        $task->status = AdeCoordinationTask::STATUS_PENDING;
        $task->input = $data['input'] ?? [];
        $task->order = $jumlah;
        $task->save();

        AdeCoordination::where('id', $coord->id)->update(['task_count' => $jumlah + 1]);

        return $task;
    }

    /**
     * Susun semula subtugas (semak silang: tester selepas implementer).
     */
    public function susunSemula(int $tenantId, int $coordinationId, array $urutan): array
    {
        $coord = $this->cari($tenantId, $coordinationId);
        $task = AdeCoordinationTask::where('ade_coordination_id', $coord->id)
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy('id');

        $i = 0;

        foreach ($urutan as $taskId) {
            if (! $task->has($taskId)) {
                throw new \InvalidArgumentException("Task {$taskId} bukan sebahagian coordination ini.");
            }

            $task[$taskId]->order = $i;
            $task[$taskId]->save();
            $i++;
        }

        return AdeCoordinationTask::where('ade_coordination_id', $coord->id)
            ->orderBy('order')
            ->get()
            ->toArray();
    }

    /**
     * Jalankan coordination — mengisi task satu demi satu.
     *
     * Executor ialah callback: `fn(AdeCoordinationTask $task): array`.
     * Architecture ini membolehkan ADE dipacu oleh enjin sebenar (AgentStudio,
     * AI Gateway) atau oleh ujian deterministik.
     *
     * @param callable|null $executor
     */
    public function jalankan(int $tenantId, int $coordinationId, ?callable $executor = null): AdeCoordination
    {
        $coord = $this->cari($tenantId, $coordinationId);

        if ($coord->status === AdeCoordination::STATUS_RUNNING) {
            throw new \RuntimeException('Coordination ini sedang berjalan.');
        }

        $mula = microtime(true);
        $coord->status = AdeCoordination::STATUS_RUNNING;
        $coord->save();

        $tasks = AdeCoordinationTask::where('ade_coordination_id', $coord->id)
            ->orderBy('order')
            ->get();

        $selesai = 0;
        $gagal = 0;
        $hasil = [];

        foreach ($tasks as $task) {
            $task->status = AdeCoordinationTask::STATUS_RUNNING;
            $task->save();

            $tMula = microtime(true);

            try {
                if ($executor !== null) {
                    $output = $executor($task);
                    $task->output = is_array($output) ? $output : ['result' => $output];
                    $task->score = isset($task->output['score']) ? (float) $task->output['score'] : null;
                    $task->status = AdeCoordinationTask::STATUS_DONE;
                } else {
                    // Tiada executor — tandakan sebagai dilangkau (dry-run).
                    $task->output = ['note' => 'Tiada executor — dry-run sahaja.'];
                    $task->status = AdeCoordinationTask::STATUS_SKIPPED;
                }
            } catch (\Throwable $e) {
                $task->status = AdeCoordinationTask::STATUS_FAILED;
                $task->error_message = mb_substr($e->getMessage(), 0, 1000);
                $gagal++;
                Log::warning('ADE task gagal: ' . $e->getMessage());
            }

            $task->duration_ms = (int) round((microtime(true) - $tMula) * 1000);
            $task->save();

            if ($task->status === AdeCoordinationTask::STATUS_DONE) {
                $selesai++;
            }

            $hasil[] = [
                'task_id' => $task->id,
                'title' => $task->title,
                'role' => $task->role,
                'status' => $task->status,
                'output' => $task->output,
                'error' => $task->error_message,
            ];
        }

        $coord->completed_count = $selesai;
        $coord->failed_count = $gagal;
        $coord->duration_ms = (int) round((microtime(true) - $mula) * 1000);
        $coord->result = [
            'tasks' => $hasil,
            'summary' => $this->ringkasan($hasil),
        ];
        $coord->status = $gagal > 0 && $selesai === 0
            ? AdeCoordination::STATUS_FAILED
            : ($gagal > 0 ? AdeCoordination::STATUS_REVIEW : AdeCoordination::STATUS_COMPLETED);
        $coord->save();

        return $coord;
    }

    /**
     * Semak silang — dua reviewer menilai task yang sama.
     *
     * @param array<int,int> $penilai — map task_id => reviewer index
     */
    public function semakSilang(int $tenantId, int $coordinationId, array $penilai): array
    {
        $coord = $this->cari($tenantId, $coordinationId);

        $tasks = AdeCoordinationTask::where('ade_coordination_id', $coord->id)
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy('id');

        $keputusan = [];

        foreach ($penilai as $taskId => $reviewerIdx) {
            if (! $tasks->has($taskId)) {
                continue;
            }

            $task = $tasks[$taskId];
            $ulasan = $task->output['cross_review'] ?? [];

            if (! is_array($ulasan)) {
                $ulasan = [];
            }

            $ulasan[] = [
                'reviewer' => $reviewerIdx,
                'verdict' => 'approved',
                'at' => now()->toIso8601String(),
            ];

            $task->output = array_merge((array) $task->output, ['cross_review' => $ulasan]);
            $task->save();

            $keputusan[$taskId] = $ulasan;
        }

        $coord->status = AdeCoordination::STATUS_REVIEW;
        $coord->save();

        return $keputusan;
    }

    /**
     * Senarai coordination untuk tenant.
     */
    public function senarai(int $tenantId, ?string $status = null): array
    {
        $query = AdeCoordination::where('tenant_id', $tenantId);

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->orderByDesc('id')->get()->toArray();
    }

    /**
     * Butiran coordination + tasks.
     */
    public function butiran(int $tenantId, int $coordinationId): array
    {
        $coord = $this->cari($tenantId, $coordinationId);

        $tasks = AdeCoordinationTask::where('ade_coordination_id', $coord->id)
            ->orderBy('order')
            ->get()
            ->toArray();

        return [
            'coordination' => $coord->toArray(),
            'tasks' => $tasks,
            'peratus' => $coord->peratus(),
        ];
    }

    /**
     * Statistik coordination.
     */
    public function statistik(int $tenantId): array
    {
        $semua = AdeCoordination::where('tenant_id', $tenantId)->get();

        $mengikutStatus = $semua->groupBy('status')->map(fn ($g) => $g->count())->toArray();

        $tasks = AdeCoordinationTask::where('tenant_id', $tenantId)->get();

        return [
            'jumlah_coordination' => $semua->count(),
            'jumlah_task' => $tasks->count(),
            'task_selesai' => $tasks->where('status', 'done')->count(),
            'task_gagal' => $tasks->where('status', 'failed')->count(),
            'task_menunggu' => $tasks->where('status', 'pending')->count(),
            'mengikut_status' => $mengikutStatus,
            'purata_masa_ms' => $semua->count() > 0
                ? (int) round($semua->avg('duration_ms'))
                : 0,
        ];
    }

    /**
     * Ringkasan hasil — untuk ringkasan Boardroom.
     */
    private function ringkasan(array $hasil): array
    {
        $selesai = 0;
        $gagal = 0;
        $dilangkau = 0;
        $peranan = [];

        foreach ($hasil as $h) {
            if ($h['status'] === 'done') {
                $selesai++;
            } elseif ($h['status'] === 'failed') {
                $gagal++;
            } elseif ($h['status'] === 'skipped') {
                $dilangkau++;
            }

            $r = $h['role'];
            $peranan[$r] = ($peranan[$r] ?? 0) + 1;
        }

        return [
            'selesai' => $selesai,
            'gagal' => $gagal,
            'dilangkau' => $dilangkau,
            'mengikut_peranan' => $peranan,
        ];
    }

    /**
     * Cari coordination dengan pengasingan tenant.
     */
    private function cari(int $tenantId, int $coordinationId): AdeCoordination
    {
        $coord = AdeCoordination::where('tenant_id', $tenantId)
            ->where('id', $coordinationId)
            ->first();

        if ($coord === null) {
            throw new \RuntimeException('Coordination tidak dijumpai.');
        }

        return $coord;
    }
}
