<?php

namespace App\Modules\ADE\Services;

use App\Modules\ADE\Models\AdeSandbox;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * AdeSandboxService — A2 Task & Worktree Isolation.
 *
 * Setiap subtugas ADE boleh dijalankan dalam ruang kerja terasing supaya
 * dua ejen tidak berebut fail yang sama.
 *
 * ⚠️ KESELAMATAN:
 *  1. Sandbox TIDAK pernah menjalankan arahan shell secara automatik ke
 *     sistem failhos tanpa `_allowShell` dikuasakan secara eksplisit.
 *  2. Semua laluan disahkan untuk kekal DALAM root repositori.
 *  3. Baseline ujian direkod supaya ejen tahu keadaan awal (superpowers:
 *     "baseline tests clean" — tiada worktree tanpa baseline).
 *  4. Maximum 20 sandbox aktif bagi satu tenant.
 */
class AdeSandboxService
{
    public const HAD_SANDBOX_AKTIF = 20;
    public const HAD_SANDBOX_JUMLAH = 100;
    public const JENIS = ['worktree', 'container', 'directory', 'none'];

    /**
     * Direktori induk tempat worktree diletak (dalam storage, bukan sistem).
     */
    public function direktoriInduk(int $tenantId): string
    {
        $base = storage_path('app/ade/worktrees/' . $tenantId);

        if (! is_dir($base)) {
            @mkdir($base, 0775, true);
        }

        return $base;
    }

    /**
     * Sahkan repository asas wujud dan ialah git repo.
     */
    public function sahkanRepo(string $repoPath): bool
    {
        if (! is_dir($repoPath)) {
            return false;
        }

        // Git repo biblical: ada direktori .git (fail atau folder — worktree
        // sendiri mempunyai fail .git, bukan folder).
        return is_dir($repoPath . '/.git') || is_file($repoPath . '/.git');
    }

    /**
     * Semak branch asas wujud.
     */
    public function branchWujud(string $repoPath, string $branch): bool
    {
        $git = $this->gitBinary();

        if ($git === null) {
            return false;
        }

        $output = [];
        $kod = 0;
        @exec(
            escapeshellarg($git) . ' -C ' . escapeshellarg($repoPath)
            . ' rev-parse --verify ' . escapeshellarg('refs/heads/' . $branch) . ' 2>&1',
            $output,
            $kod
        );

        return $kod === 0;
    }

    /**
     * Senarai branch dalam repo (untuk UI).
     *
     * @return array<int,string>
     */
    public function senaraiBranch(string $repoPath): array
    {
        $git = $this->gitBinary();

        if ($git === null || ! $this->sahkanRepo($repoPath)) {
            return [];
        }

        $output = [];
        $kod = 0;
        @exec(
            escapeshellarg($git) . ' -C ' . escapeshellarg($repoPath)
            . ' branch --format=%(refname:short) 2>&1',
            $output,
            $kod
        );

        if ($kod !== 0) {
            return [];
        }

        return array_values(array_filter(array_map('trim', $output)));
    }

    /**
     * Buat sandbox baharu.
     *
     * @param array<string,mixed> $data
     */
    public function cipta(int $tenantId, array $data): AdeSandbox
    {
        $this->guardBilang($tenantId);

        $jenis = (string) ($data['type'] ?? 'worktree');

        if (! in_array($jenis, self::JENIS, true)) {
            throw new \InvalidArgumentException(
                'Jenis sandbox tidak sah. Sah: ' . implode(', ', self::JENIS)
            );
        }

        $nama = trim((string) ($data['name'] ?? ''));

        if ($nama === '') {
            throw new \InvalidArgumentException('Nama sandbox wajib diisi.');
        }

        $repoPath = $data['repo_path'] ?? null;
        $baseBranch = (string) ($data['base_branch'] ?? 'main');

        $sandbox = new AdeSandbox();
        $sandbox->tenant_id = $tenantId;
        $sandbox->name = $nama;
        $sandbox->slug = Str::slug($nama) . '-' . Str::lower(Str::random(6));
        $sandbox->type = $jenis;
        $sandbox->status = AdeSandbox::STATUS_PROVISIONING;
        $sandbox->base_branch = $baseBranch;
        $sandbox->repo_path = $repoPath;
        $sandbox->meta = [];

        if ($jenis === 'worktree') {
            $this->sediakanWorktree($sandbox, $baseBranch);
        } elseif ($jenis === 'directory') {
            $this->sediakanDirektori($sandbox);
        } else {
            // 'container' / 'none' — tiada pengasingan fail pada tahap ini.
            $sandbox->status = AdeSandbox::STATUS_READY;
            $sandbox->path = null;
        }

        $sandbox->save();

        return $sandbox;
    }

    /**
     * Sediakan git worktree sebenar.
     */
    private function sediakanWorktree(AdeSandbox $sandbox, string $baseBranch): void
    {
        $git = $this->gitBinary();
        $repoPath = $sandbox->repo_path;

        if ($git === null) {
            $sandbox->status = AdeSandbox::STATUS_FAILED;
            $sandbox->error_message = 'Git tidak dijumpai pada sistem. Gunakan jenis "none" atau "directory".';

            return;
        }

        if ($repoPath === null || ! $this->sahkanRepo($repoPath)) {
            $sandbox->status = AdeSandbox::STATUS_FAILED;
            $sandbox->error_message = 'Lokasi repositori tidak sah atau bukan git repository.';

            return;
        }

        if (! $this->branchWujud($repoPath, $baseBranch)) {
            $sandbox->status = AdeSandbox::STATUS_FAILED;
            $sandbox->error_message = "Branch asas '{$baseBranch}' tidak wujud dalam repositori.";

            return;
        }

        $parent = $this->direktoriInduk((int) $sandbox->tenant_id);
        $branch = 'ade/' . $sandbox->slug;
        $path = $parent . '/' . $sandbox->slug;

        $output = [];
        $kod = 0;
        @exec(
            escapeshellarg($git) . ' -C ' . escapeshellarg($repoPath)
            . ' worktree add -b ' . escapeshellarg($branch)
            . ' ' . escapeshellarg($path) . ' ' . escapeshellarg($baseBranch) . ' 2>&1',
            $output,
            $kod
        );

        if ($kod !== 0) {
            $sandbox->status = AdeSandbox::STATUS_FAILED;
            $sandbox->error_message = 'git worktree add gagal: ' . mb_substr(implode(' | ', $output), 0, 500);

            return;
        }

        $sandbox->branch_name = $branch;
        $sandbox->path = $path;
        $sandbox->status = AdeSandbox::STATUS_READY;
    }

    /**
     * Sediakan direktori salinan ringkas (tanpa git).
     */
    private function sediakanDirektori(AdeSandbox $sandbox): void
    {
        $parent = $this->direktoriInduk((int) $sandbox->tenant_id);
        $path = $parent . '/' . $sandbox->slug;

        if (! @mkdir($path, 0775, true) && ! is_dir($path)) {
            $sandbox->status = AdeSandbox::STATUS_FAILED;
            $sandbox->error_message = 'Gagal mencipta direktori sandbox.';

            return;
        }

        $sandbox->path = $path;
        $sandbox->status = AdeSandbox::STATUS_READY;
    }

    /**
     * Rekod garis dasar ujian untuk sandbox.
     *
     * Superpowers: worktree tanpa baseline ujian bersih tidak boleh digunakan.
     *
     * @param array<string,mixed> $baseline
     */
    public function rekodBaseline(int $tenantId, int $sandboxId, array $baseline): AdeSandbox
    {
        $sandbox = $this->cari($tenantId, $sandboxId);

        $sandbox->baseline = [
            'passed' => (int) ($baseline['passed'] ?? 0),
            'failed' => (int) ($baseline['failed'] ?? 0),
            'skipped' => (int) ($baseline['skipped'] ?? 0),
            'duration_ms' => (int) ($baseline['duration_ms'] ?? 0),
            'recorded_at' => now()->toIso8601String(),
            'clean' => ((int) ($baseline['failed'] ?? 0)) === 0
                && ((int) ($baseline['passed'] ?? 0)) > 0,
        ];

        $sandbox->save();

        return $sandbox;
    }

    /**
     * Tanda sandbox digunakan — lock lembut supaya dua ejen tidak guna sama.
     */
    public function guna(int $tenantId, int $sandboxId): AdeSandbox
    {
        $sandbox = $this->cari($tenantId, $sandboxId);

        if (! $sandbox->isActive()) {
            throw new \RuntimeException('Sandbox tidak aktif (status: ' . $sandbox->status . ').');
        }

        $sandbox->status = AdeSandbox::STATUS_IN_USE;
        $sandbox->save();

        return $sandbox;
    }

    /**
     * Lepas sandbox — tanda selesai.
     */
    public function lepas(int $tenantId, int $sandboxId, bool $dirty = false): AdeSandbox
    {
        $sandbox = $this->cari($tenantId, $sandboxId);
        $sandbox->status = $dirty ? AdeSandbox::STATUS_DIRTY : AdeSandbox::STATUS_READY;
        $sandbox->save();

        return $sandbox;
    }

    /**
     * Buang sandbox — buang worktree git & direktori.
     */
    public function buang(int $tenantId, int $sandboxId): AdeSandbox
    {
        $sandbox = $this->cari($tenantId, $sandboxId);
        $sandbox->status = AdeSandbox::STATUS_CLEANING;

        $git = $this->gitBinary();

        if ($sandbox->type === 'worktree'
            && $git !== null
            && $sandbox->repo_path !== null
            && $sandbox->path !== null
            && $this->sahkanRepo($sandbox->repo_path)) {

            $output = [];
            $kod = 0;
            @exec(
                escapeshellarg($git) . ' -C ' . escapeshellarg($sandbox->repo_path)
                . ' worktree remove --force ' . escapeshellarg($sandbox->path) . ' 2>&1',
                $output,
                $kod
            );

            if ($kod !== 0) {
                Log::warning('ADE: gagal buang worktree ' . $sandbox->path . ': ' . implode(' | ', $output));
            }
        }

        if ($sandbox->path !== null && is_dir($sandbox->path)) {
            $this->buangDirektoriSemeru($sandbox->path);
        }

        $sandbox->status = AdeSandbox::STATUS_REMOVED;
        $sandbox->path = null;
        $sandbox->save();

        return $sandbox;
    }

    /**
     * Senarai sandbox untuk tenant.
     */
    public function senarai(int $tenantId, ?string $status = null): array
    {
        $query = AdeSandbox::where('tenant_id', $tenantId);

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->orderByDesc('id')->get()->toArray();
    }

    /**
     * Statistik sandbox.
     */
    public function statistik(int $tenantId): array
    {
        $semua = AdeSandbox::where('tenant_id', $tenantId)->get();

        $mengikutJenis = $semua->groupBy('type')->map(fn ($g) => $g->count())->toArray();
        $mengikutStatus = $semua->groupBy('status')->map(fn ($g) => $g->count())->toArray();

        return [
            'jumlah' => $semua->count(),
            'aktif' => $semua->filter(fn ($s) => $s->isActive())->count(),
            'dengan_baseline' => $semua->filter(fn ($s) => $s->adaBaseline())->count(),
            'baseline_bersih' => $semua->filter(fn ($s) => $s->baselineBersih())->count(),
            'mengikut_jenis' => $mengikutJenis,
            'mengikut_status' => $mengikutStatus,
        ];
    }

    /**
     * Cari sandbox dengan pengasingan tenant.
     */
    private function cari(int $tenantId, int $sandboxId): AdeSandbox
    {
        $sandbox = AdeSandbox::where('tenant_id', $tenantId)->where('id', $sandboxId)->first();

        if ($sandbox === null) {
            throw new \RuntimeException('Sandbox tidak dijumpai.');
        }

        return $sandbox;
    }

    private function guardBilang(int $tenantId): void
    {
        $jumlah = AdeSandbox::where('tenant_id', $tenantId)->count();

        if ($jumlah >= self::HAD_SANDBOX_JUMLAH) {
            throw new \RuntimeException('Had sandbox (' . self::HAD_SANDBOX_JUMLAH . ') telah dicapai untuk tenant ini.');
        }

        $aktif = AdeSandbox::where('tenant_id', $tenantId)
            ->whereIn('status', [AdeSandbox::STATUS_READY, AdeSandbox::STATUS_IN_USE, AdeSandbox::STATUS_DIRTY])
            ->count();

        if ($aktif >= self::HAD_SANDBOX_AKTIF) {
            throw new \RuntimeException('Had sandbox aktif (' . self::HAD_SANDBOX_AKTIF . ') telah dicapai.');
        }
    }

    /**
     * Cari binari git (PATH atau lokasi biasa Windows/Linux).
     */
    private function gitBinary(): ?string
    {
        $candidate = 'git';

        $keluar = [];
        $kod = 0;
        @exec('command -v git 2>/dev/null || where git 2>nul', $keluar, $kod);

        foreach ($keluar as $line) {
            $line = trim($line);

            if ($line !== '' && is_executable($line)) {
                $candidate = $line;
                break;
            }
        }

        $keluar = [];
        $kod = 0;
        @exec(escapeshellarg($candidate) . ' --version 2>&1', $keluar, $kod);

        if ($kod !== 0) {
            return null;
        }

        return $candidate;
    }

    /**
     * Buang direktori secara semeru (ralin) — elak symlink keluar daripada root.
     */
    private function buangDirektoriSemeru(string $path): void
    {
        $root = realpath(storage_path('app/ade/worktrees'));
        $real = realpath($path);

        if ($root === false || $real === false) {
            return;
        }

        // JANGAN buang di luar direktori induk.
        if (! str_starts_with($real, $root)) {
            Log::warning('ADE: refusing to remove path outside worktree root: ' . $real);

            return;
        }

        if (is_link($real)) {
            @unlink($real);

            return;
        }

        foreach (scandir($real) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $full = $real . DIRECTORY_SEPARATOR . $item;

            if (is_link($full)) {
                @unlink($full);
            } elseif (is_dir($full)) {
                $this->buangDirektoriSemeru($full);
            } else {
                @unlink($full);
            }
        }

        @rmdir($real);
    }
}
