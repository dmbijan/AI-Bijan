<?php

namespace App\Modules\ADE\Services;

use App\Modules\ADE\Models\AdeCoordination;
use App\Modules\ADE\Models\AdeReview;
use App\Modules\ADE\Models\AdeSandbox;
use Illuminate\Support\Facades\Log;

/**
 * AdeReviewService — A4 Review Surface.
 *
 * Diff walkthrough + test runner + preview dalam satu permukaan.
 *
 * Corak superpowers `requesting-code-review`: semakan mengikut tahap
 * keseriusan; kritikal MENYEKAT kelulusan automatik.
 */
class AdeReviewService
{
    public const HAD_TEMUAN = 100;
    public const HAD_KOMEN = 200;
    public const HAD_BARIS_DIFF = 2000;

    /**
     * Buat review baharu.
     *
     * @param array<string,mixed> $data
     */
    public function cipta(int $tenantId, array $data): AdeReview
    {
        $tajuk = trim((string) ($data['title'] ?? ''));

        if ($tajuk === '') {
            throw new \InvalidArgumentException('Tajuk review wajib diisi.');
        }

        $coordId = $data['ade_coordination_id'] ?? null;

        if ($coordId !== null) {
            AdeCoordination::where('tenant_id', $tenantId)->where('id', $coordId)->firstOrFail();
        }

        $sandboxId = $data['ade_sandbox_id'] ?? null;

        if ($sandboxId !== null) {
            AdeSandbox::where('tenant_id', $tenantId)->where('id', $sandboxId)->firstOrFail();
        }

        $diff = $this->guardDiff($data['diff'] ?? []);
        $files = $this->guardFiles($data['files'] ?? []);
        $testResults = $this->guardTestResults($data['test_results'] ?? []);

        $review = new AdeReview();
        $review->tenant_id = $tenantId;
        $review->ade_coordination_id = $coordId;
        $review->ade_sandbox_id = $sandboxId;
        $review->title = $tajuk;
        $review->status = AdeReview::STATUS_OPEN;
        $review->severity = $this->guardSeverity($data['severity'] ?? 'medium');
        $review->diff = $diff;
        $review->files = $files;
        $review->test_results = $testResults;
        $review->summary = $data['summary'] ?? null;
        $review->findings = $this->guardFindings($data['findings'] ?? []);
        $review->comments = $this->guardComments($data['comments'] ?? []);
        $review->additions = (int) ($data['additions'] ?? 0);
        $review->deletions = (int) ($data['deletions'] ?? 0);
        $review->save();

        return $review;
    }

    /**
     * Tambah penemuan semakan.
     *
     * @param array<string,mixed> $penemuan
     */
    public function tambahTemuan(int $tenantId, int $reviewId, array $penemuan): AdeReview
    {
        $review = $this->cari($tenantId, $reviewId);

        $temuan = $review->findings ?? [];
        $temuan[] = $this->normaliseTemuan($penemuan);

        if (count($temuan) > self::HAD_TEMUAN) {
            throw new \RuntimeException('Had penemuan (' . self::HAD_TEMUAN . ') telah dicapai.');
        }

        $review->findings = $temuan;

        // Penemuan kritikal naikkan tahap keseriusan review.
        if ($this->adaKritikal($temuan)) {
            $review->severity = 'critical';
        }

        $review->save();

        return $review;
    }

    /**
     * Tambah komen pada baris tertentu.
     *
     * @param array<string,mixed> $komen
     */
    public function tambahKomen(int $tenantId, int $reviewId, array $komen): AdeReview
    {
        $review = $this->cari($tenantId, $reviewId);

        $list = $review->comments ?? [];

        if (count($list) >= self::HAD_KOMEN) {
            throw new \RuntimeException('Had komen (' . self::HAD_KOMEN . ') telah dicapai.');
        }

        $list[] = [
            'file' => $komen['file'] ?? null,
            'line' => isset($komen['line']) ? (int) $komen['line'] : null,
            'body' => mb_substr((string) ($komen['body'] ?? ''), 0, 2000),
            'author' => $komen['author'] ?? null,
            'created_at' => now()->toIso8601String(),
        ];

        $review->comments = $list;
        $review->save();

        return $review;
    }

    /**
     * Rekod keputusan test runner.
     *
     * @param array<string,mixed> $hasil
     */
    public function rekodUjian(int $tenantId, int $reviewId, array $hasil): AdeReview
    {
        $review = $this->cari($tenantId, $reviewId);
        $review->test_results = $this->guardTestResults($hasil);
        $review->save();

        return $review;
    }

    /**
     * Mulakan semakan — status in_review.
     */
    public function mulaSemakan(int $tenantId, int $reviewId): AdeReview
    {
        $review = $this->cari($tenantId, $reviewId);

        if ($review->status !== AdeReview::STATUS_OPEN) {
            throw new \RuntimeException('Review tidak berstatus open.');
        }

        $review->status = AdeReview::STATUS_IN_REVIEW;
        $review->save();

        return $review;
    }

    /**
     * Sahkan review — sama ada lulus / perlu perubahan / ditolak.
     *
     * ⚠️ Penemuan kritikal MENYEKAT kelulusan automatik — sentiasa rujuk manusia.
     */
    public function sahkan(int $tenantId, int $reviewId, string $keputusan, ?int $reviewerId = null): AdeReview
    {
        $review = $this->cari($tenantId, $reviewId);

        $keputusan = mb_strtolower(trim($keputusan));

        $benar = ['approved', 'changes_requested', 'rejected', 'merged'];

        if (! in_array($keputusan, $benar, true)) {
            throw new \InvalidArgumentException(
                'Keputusan tidak sah. Sah: ' . implode(', ', $benar)
            );
        }

        if ($keputusan === 'approved' && $review->adaKritikal()) {
            throw new \RuntimeException(
                'Review mengandungi penemuan kritikal — kelulusan automatik disekat. Selesaikan dahulu.'
            );
        }

        if ($keputusan === 'approved' && ! $review->ujianLulus()) {
            throw new \RuntimeException(
                'Ujian belum lulus — kelulusan disekat. Rekod keputusan ujian dahulu.'
            );
        }

        $review->status = $keputusan;
        $review->reviewed_by = $reviewerId;
        $review->save();

        return $review;
    }

    /**
     * Minta perubahan — semakan kembali ke open.
     */
    public function mintaPerubahan(int $tenantId, int $reviewId, ?int $reviewerId = null): AdeReview
    {
        $review = $this->cari($tenantId, $reviewId);
        $review->status = AdeReview::STATUS_CHANGES_REQUESTED;
        $review->reviewed_by = $reviewerId;
        $review->save();

        return $review;
    }

    /**
     * Diff walkthrough — ringkasan fail berubah.
     */
    public function walkthrough(int $tenantId, int $reviewId): array
    {
        $review = $this->cari($tenantId, $reviewId);

        $fail = $review->diff ?? [];
        $files = $review->files ?? [];

        $ikutFail = [];

        foreach ($files as $f) {
            $path = is_array($f) ? ($f['path'] ?? '') : (string) $f;
            $baris = 0;
            $tambah = 0;
            $buang = 0;

            foreach ($fail as $b) {
                $bFile = is_array($b) ? ($b['file'] ?? null) : null;

                if ($bFile !== $path) {
                    continue;
                }

                $baris++;
                $jenis = is_array($b) ? ($b['type'] ?? '') : '';

                if ($jenis === 'add') {
                    $tambah++;
                } elseif ($jenis === 'remove') {
                    $buang++;
                }
            }

            $ikutFail[] = [
                'path' => $path,
                'lines_changed' => $baris,
                'additions' => $tambah,
                'deletions' => $buang,
            ];
        }

        return [
            'review_id' => $review->id,
            'title' => $review->title,
            'status' => $review->status,
            'severity' => $review->severity,
            'files' => $ikutFail,
            'total_lines' => count($fail),
            'additions' => $review->additions,
            'deletions' => $review->deletions,
            'tests_passed' => $review->ujianLulus(),
            'critical_findings' => $this->adaKritikal($review->findings ?? []),
            'can_auto_approve' => $review->bolehLulusAuto(),
        ];
    }

    /**
     * Senarai review untuk tenant.
     */
    public function senarai(int $tenantId, ?string $status = null): array
    {
        $query = AdeReview::where('tenant_id', $tenantId);

        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        return $query->orderByDesc('id')->get()->toArray();
    }

    /**
     * Statistik review.
     */
    public function statistik(int $tenantId): array
    {
        $semua = AdeReview::where('tenant_id', $tenantId)->get();

        $mengikutStatus = $semua->groupBy('status')->map(fn ($g) => $g->count())->toArray();
        $mengikutSeverity = $semua->groupBy('severity')->map(fn ($g) => $g->count())->toArray();

        $kritikal = $semua->filter(fn ($r) => $r->adaKritikal())->count();
        $lulusOtom = $semua->filter(fn ($r) => $r->bolehLulusAuto())->count();

        return [
            'jumlah' => $semua->count(),
            'mengikut_status' => $mengikutStatus,
            'mengikut_severity' => $mengikutSeverity,
            'dengan_kritikal' => $kritikal,
            'boleh_lulus_auto' => $lulusOtom,
            'total_temuan' => $semua->sum(fn ($r) => count($r->findings ?? [])),
        ];
    }

    // ── Penjaga ──────────────────────────────────────────────────────────

    private function cari(int $tenantId, int $reviewId): AdeReview
    {
        $review = AdeReview::where('tenant_id', $tenantId)->where('id', $reviewId)->first();

        if ($review === null) {
            throw new \RuntimeException('Review tidak dijumpai.');
        }

        return $review;
    }

    private function guardSeverity(mixed $severity): string
    {
        $severity = (string) $severity;

        if (! in_array($severity, AdeReview::SEVERITY, true)) {
            throw new \InvalidArgumentException(
                'Tahap keseriusan tidak sah. Sah: ' . implode(', ', AdeReview::SEVERITY)
            );
        }

        return $severity;
    }

    private function guardDiff(mixed $diff): array
    {
        $diff = is_array($diff) ? $diff : [];

        if (count($diff) > self::HAD_BARIS_DIFF) {
            throw new \RuntimeException('Diff terlalu besar (' . self::HAD_BARIS_DIFF . ' baris).');
        }

        $normal = [];

        foreach ($diff as $b) {
            if (! is_array($b)) {
                continue;
            }

            $jenis = (string) ($b['type'] ?? '');

            if (! in_array($jenis, ['add', 'remove', 'context'], true)) {
                throw new \InvalidArgumentException('Jenis baris diff tidak sah: ' . $jenis);
            }

            $normal[] = [
                'file' => $b['file'] ?? null,
                'type' => $jenis,
                'content' => mb_substr((string) ($b['content'] ?? ''), 0, 2000),
                'line' => isset($b['line']) ? (int) $b['line'] : null,
            ];
        }

        return $normal;
    }

    private function guardFiles(mixed $files): array
    {
        $files = is_array($files) ? $files : [];
        $normal = [];

        foreach ($files as $f) {
            if (is_string($f)) {
                $normal[] = ['path' => $f];
                continue;
            }

            if (is_array($f) && isset($f['path'])) {
                $normal[] = [
                    'path' => (string) $f['path'],
                    'status' => (string) ($f['status'] ?? 'modified'),
                    'additions' => (int) ($f['additions'] ?? 0),
                    'deletions' => (int) ($f['deletions'] ?? 0),
                ];
            }
        }

        return $normal;
    }

    private function guardTestResults(mixed $hasil): array
    {
        $hasil = is_array($hasil) ? $hasil : [];

        return [
            'passed' => (int) ($hasil['passed'] ?? 0),
            'failed' => (int) ($hasil['failed'] ?? 0),
            'skipped' => (int) ($hasil['skipped'] ?? 0),
            'duration_ms' => (int) ($hasil['duration_ms'] ?? 0),
            'suite' => $hasil['suite'] ?? null,
            'recorded_at' => now()->toIso8601String(),
        ];
    }

    private function guardFindings(mixed $findings): array
    {
        $findings = is_array($findings) ? $findings : [];
        $normal = [];

        foreach ($findings as $f) {
            $normal[] = $this->normaliseTemuan(is_array($f) ? $f : ['message' => (string) $f]);
        }

        return $normal;
    }

    private function guardComments(mixed $comments): array
    {
        $comments = is_array($comments) ? $comments : [];
        $normal = [];

        foreach ($comments as $c) {
            if (! is_array($c)) {
                continue;
            }

            $normal[] = [
                'file' => $c['file'] ?? null,
                'line' => isset($c['line']) ? (int) $c['line'] : null,
                'body' => mb_substr((string) ($c['body'] ?? ''), 0, 2000),
                'author' => $c['author'] ?? null,
                'created_at' => now()->toIso8601String(),
            ];
        }

        return $normal;
    }

    private function normaliseTemuan(array $penemuan): array
    {
        $severity = (string) ($penemuan['severity'] ?? 'medium');

        if (! in_array($severity, AdeReview::SEVERITY, true)) {
            $severity = 'medium';
        }

        return [
            'severity' => $severity,
            'title' => mb_substr((string) ($penemuan['title'] ?? 'Penemuan'), 0, 200),
            'message' => mb_substr((string) ($penemuan['message'] ?? ''), 0, 2000),
            'file' => $penemuan['file'] ?? null,
            'line' => isset($penemuan['line']) ? (int) $penemuan['line'] : null,
            'suggestion' => isset($penemuan['suggestion'])
                ? mb_substr((string) $penemuan['suggestion'], 0, 1000)
                : null,
        ];
    }

    private function adaKritikal(mixed $findings): bool
    {
        if (! is_array($findings)) {
            return false;
        }

        foreach ($findings as $f) {
            $sev = is_array($f) ? ($f['severity'] ?? null) : null;

            if ($sev === 'critical') {
                return true;
            }
        }

        return false;
    }
}
