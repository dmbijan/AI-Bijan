<?php

namespace App\Modules\POS\Services;

use App\Modules\POS\Models\POSDiscountApproval;
use App\Modules\POS\Models\POSSale;
use Illuminate\Validation\ValidationException;

/**
 * Kelulusan diskaun (BAB 88 / T-83) — dipindah daripada B-POS
 * `DiscountApprovalLog`.
 *
 * Masalah yang diselesaikan: tanpa had, juruwang boleh memberi diskaun 90%
 * kepada rakan sendiri. Peraturan:
 *
 *   • diskaun ≤ `requires_approval_above` (lalai 20%) → terus lulus,
 *   • diskaun > had → perlu KELULUSAN pengurus sebelum jualan boleh selesai,
 *   • diskaun > `max_percent` (lalai 50%) → DITOLAK sepenuhnya.
 *
 * ⚠️ Kelulusan mesti datang daripada pengguna BERBEZA (tidak boleh lulus
 * permintaan sendiri) — jika tidak kawalan ini tidak bermakna.
 */
class DiscountApprovalService
{
    public function hadKelulusan(): float
    {
        return (float) config('pos.discount_approval.requires_approval_above', 20.0);
    }

    public function hadMutlak(): float
    {
        return (float) config('pos.discount_approval.max_percent', 50.0);
    }

    /** Adakah diskaun ini memerlukan kelulusan pengurus? */
    public function perluKelulusan(float $peratus): bool
    {
        return $peratus > $this->hadKelulusan();
    }

    /**
     * Minta kelulusan. Rekod `pending` sehingga pengurus memutuskan.
     */
    public function minta(
        int $tenantId,
        ?int $saleId,
        float $peratus,
        float $amaun,
        ?int $pemintaId,
        ?string $sebab = null
    ): POSDiscountApproval {
        if ($peratus <= 0) {
            throw ValidationException::withMessages([
                'discount_percent' => 'Peratus diskaun mesti melebihi sifar.',
            ]);
        }

        if ($peratus > $this->hadMutlak()) {
            throw ValidationException::withMessages([
                'discount_percent' => sprintf(
                    'Diskaun %s%% melebihi had mutlak %s%%.',
                    number_format($peratus, 2, '.', ''),
                    number_format($this->hadMutlak(), 2, '.', '')
                ),
            ]);
        }

        return POSDiscountApproval::create([
            'tenant_id' => $tenantId,
            'sale_id' => $saleId,
            'requested_by' => $pemintaId,
            'discount_percent' => round($peratus, 2),
            'discount_amount' => round($amaun, 2),
            'status' => POSDiscountApproval::STATUS_PENDING,
            'reason' => $sebab,
        ]);
    }

    /**
     * Pengurus meluluskan. Pengguna mesti BERBEZA daripada peminta.
     */
    public function luluskan(POSDiscountApproval $permohonan, int $pengurusId): POSDiscountApproval
    {
        $this->pastikanBolehDiputuskan($permohonan);

        if ($permohonan->requested_by !== null && (int) $permohonan->requested_by === $pengurusId) {
            throw ValidationException::withMessages([
                'approved_by' => 'Tidak boleh meluluskan diskaun sendiri.',
            ]);
        }

        $permohonan->update([
            'status' => POSDiscountApproval::STATUS_APPROVED,
            'approved_by' => $pengurusId,
            'decided_at' => now(),
        ]);

        if ($permohonan->sale_id) {
            POSSale::whereKey($permohonan->sale_id)->update([
                'discount_approved_by' => $pengurusId,
                'discount_approved_at' => now(),
            ]);
        }

        return $permohonan;
    }

    public function tolak(POSDiscountApproval $permohonan, int $pengurusId, ?string $sebab = null): POSDiscountApproval
    {
        $this->pastikanBolehDiputuskan($permohonan);

        $permohonan->update([
            'status' => POSDiscountApproval::STATUS_REJECTED,
            'approved_by' => $pengurusId,
            'decided_at' => now(),
            'reason' => $sebab ?? $permohonan->reason,
        ]);

        return $permohonan;
    }

    /**
     * Gerbang: jualan hanya boleh diselesaikan jika diskaunnya diluluskan.
     */
    public function pastikanDibenarkan(POSSale $jualan): void
    {
        $asas = max(0.0, (float) $jualan->subtotal);
        $diskaun = (float) $jualan->discount_amount;

        if ($asas <= 0 || $diskaun <= 0) {
            return;
        }

        $peratus = $diskaun / $asas * 100;

        if ($peratus > $this->hadMutlak()) {
            throw ValidationException::withMessages([
                'discount_amount' => 'Diskaun melebihi had mutlak — tidak dibenarkan.',
            ]);
        }

        if ($this->perluKelulusan($peratus) && ! $jualan->discount_approved_at) {
            throw ValidationException::withMessages([
                'discount_amount' => sprintf(
                    'Diskaun %s%% memerlukan kelulusan pengurus sebelum jualan boleh diselesaikan.',
                    number_format($peratus, 2, '.', '')
                ),
            ]);
        }
    }

    private function pastikanBolehDiputuskan(POSDiscountApproval $permohonan): void
    {
        if ($permohonan->status !== POSDiscountApproval::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Permohonan ini sudah diputuskan.',
            ]);
        }
    }
}
