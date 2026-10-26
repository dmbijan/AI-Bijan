<?php

namespace App\Modules\ADE\Services;

use App\Modules\ADE\Models\AdeCoordination;
use App\Modules\ADE\Models\AdeReview;
use App\Modules\ADE\Models\AdeSandbox;
use App\Modules\ADE\Models\AdeSkill;

/**
 * AdeOrchestrator — facade ADE.
 *
 * Menyatukan 4 fasa ADE kepada satu permukaan:
 *   A1 Skills Engine        → AdeSkillService
 *   A2 Worktree Isolation   → AdeSandboxService
 *   A3 Sub-Agent Coordinator → AdeCoordinatorService
 *   A4 Review Surface       → AdeReviewService
 *
 * Aliran lengkap (ala superpowers):
 *   1. Pasang skills untuk tenant
 *   2. Cari skill yang terpicu daripada input
 *   3. Cipta sandbox (isolation)
 *   4. Rekod baseline ujian
 *   5. coordination → subtugas (implementer/tester/reviewer)
 *   6. Jalankan coordination
 *   7. Buka review + walkthrough
 *   8. Sahkan review (kritikal menyekat)
 */
class AdeOrchestrator
{
    public function __construct(
        private AdeSkillService $skills,
        private AdeSandboxService $sandboxes,
        private AdeCoordinatorService $coordinator,
        private AdeReviewService $reviews,
    ) {
    }

    public function skills(): AdeSkillService
    {
        return $this->skills;
    }

    public function sandboxes(): AdeSandboxService
    {
        return $this->sandboxes;
    }

    public function coordinator(): AdeCoordinatorService
    {
        return $this->coordinator;
    }

    public function reviews(): AdeReviewService
    {
        return $this->reviews;
    }

    /**
     * Aliran penuh: dari input mentah kepada review yang boleh disahkan.
     *
     * @param array<string,mixed> $data
     */
    public function jalankanAliran(int $tenantId, array $data): array
    {
        $input = (string) ($data['input'] ?? '');
        $nama = (string) ($data['name'] ?? 'Aliran ADE');

        // 1. Pastikan skill ada
        $this->skills->pasangBuiltin($tenantId);

        // 2. Cari skill yang terpicu
        $terpicu = $this->skills->cariPencetus($tenantId, $input);
        $skillKeys = array_map(fn ($s) => $s->key, $terpicu);

        // 3. Sandbox untuk pengasingan
        $sandbox = null;

        if (($data['sandbox'] ?? null) !== null) {
            $sandbox = $this->sandboxes->cipta($tenantId, (array) $data['sandbox']);
        }

        // 4. Coordination
        $coord = $this->coordinator->cipta($tenantId, [
            'name' => $nama,
            'objective' => $data['objective'] ?? $input,
            'max_parallel' => $data['max_parallel'] ?? 4,
            'ade_sandbox_id' => $sandbox?->id,
        ]);

        // 5. Subtugas
        $subtugas = (array) ($data['tasks'] ?? []);

        foreach ($subtugas as $t) {
            $this->coordinator->tambahTask($tenantId, $coord->id, (array) $t);
        }

        // 6. Jalankan
        $coord = $this->coordinator->jalankan($tenantId, $coord->id, $data['executor'] ?? null);

        // 7. Review
        $review = null;

        if (($data['review'] ?? null) !== null) {
            $reviewData = (array) $data['review'];
            $reviewData['ade_coordination_id'] = $coord->id;

            if ($sandbox !== null) {
                $reviewData['ade_sandbox_id'] = $sandbox->id;
            }

            $review = $this->reviews->cipta($tenantId, $reviewData);
        }

        // 8. Rekod pemanggilan skill
        foreach ($terpicu as $s) {
            $this->skills->rekod(
                $tenantId,
                $s->id,
                true,
                $input,
                'Aliran ADE: ' . $nama,
                null,
                0,
                'automatic',
                'coordination:' . $coord->id
            );
        }

        return [
            'skills_terpicu' => $skillKeys,
            'sandbox' => $sandbox?->toArray(),
            'coordination' => $coord->toArray(),
            'review' => $review?->toArray(),
        ];
    }

    /**
     * Ringkasan menyeluruh ADE untuk satu tenant — dashboard.
     */
    public function ringkasan(int $tenantId): array
    {
        $skillStat = $this->skills->statistik($tenantId);
        $sandboxStat = $this->sandboxes->statistik($tenantId);
        $coordStat = $this->coordinator->statistik($tenantId);
        $reviewStat = $this->reviews->statistik($tenantId);

        // Health ADE — 4 komponen, skor 0–100.
        $komponen = [
            'skills' => $this->skor($skillStat['jumlah_tenant'], 10, 25),
            'isolation' => $this->skor($sandboxStat['dengan_baseline'], 1, 25),
            'orchestration' => $this->skor($coordStat['task_selesai'], 1, 25),
            'review' => $this->skor($reviewStat['boleh_lulus_auto'], 1, 25),
        ];

        $jumlah = array_sum($komponen);

        return [
            'skills' => $skillStat,
            'sandboxes' => $sandboxStat,
            'coordination' => $coordStat,
            'reviews' => $reviewStat,
            'komponen_kesihatan' => $komponen,
            'skor_kesihatan' => $jumlah,
            'gred' => $this->gred($jumlah),
        ];
    }

    /**
     * Skor komponen 0–25.
     */
    private function skor(int $nilai, int $minimum, int $maksimum): int
    {
        if ($nilai <= 0) {
            return 0;
        }

        $bahagian = min($nilai, $minimum) / $minimum;

        return (int) round($bahagian * $maksimum);
    }

    /**
     * Gred A–E.
     */
    private function gred(int $skor): string
    {
        return match (true) {
            $skor >= 85 => 'A',
            $skor >= 70 => 'B',
            $skor >= 55 => 'C',
            $skor >= 40 => 'D',
            default => 'E',
        };
    }
}
