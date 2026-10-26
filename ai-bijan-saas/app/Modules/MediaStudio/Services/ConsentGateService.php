<?php

namespace App\Modules\MediaStudio\Services;

use App\Modules\HRMS\Models\BiometricConsent;
use App\Modules\HRMS\Services\BiometricGateService;
use App\Modules\MediaStudio\Models\MediaPersona;
use Illuminate\Support\Facades\Log;

/**
 * ConsentGateService — pagar consent untuk pemprosesan media.
 *
 * ⚠️ Bab 82.9:
 * - Biometrik dari video/imej → WAJIB consent biometrik aktif (BAB 56C.6A).
 * - Persona video blast → WAJIB pengesahan identiti.
 * - Tiada Inferens biometrik tanpa pagar.
 */
class ConsentGateService
{
    public function __construct(private BiometricGateService $biometricGate) {}

    /**
     * Bolehkah tenant membuat inferens biometrik daripada media ini?
     *
     * @param array $purposes Tujuan pemprosesan (untuk audit)
     */
    public function bolehInferBiometrik(int $tenantId, ?int $consentId, array $purposes = []): bool
    {
        if (! $consentId) {
            Log::warning('MediaStudio: inferens biometrik tanpa consent ID ditolak', [
                'tenant_id' => $tenantId,
            ]);

            return false;
        }

        $consent = BiometricConsent::query()
            ->where('tenant_id', $tenantId)
            ->where('id', $consentId)
            ->first();

        if (! $consent) {
            return false;
        }

        // Consent yang telah ditarik balik tidak boleh digunakan
        if ($consent->withdrawn_at !== null) {
            Log::warning('MediaStudio: consent biometrik telah ditarik balik', [
                'tenant_id' => $tenantId,
                'consent_id' => $consentId,
            ]);

            return false;
        }

        // Rujuk pagar biometrik yang sedia ada
        $result = $this->biometricGate->bolehPadan($consent->user_id);

        return (bool) ($result['boleh'] ?? false);
    }

    /**
     * Bolehkah persona video ini digunakan untuk menjana/blast?
     */
    public function bolehGunaPersona(?MediaPersona $persona): bool
    {
        if (! $persona) {
            return false;
        }

        if (! $persona->bolehGuna()) {
            Log::warning('MediaStudio: persona tidak layak digunakan', [
                'persona_id' => $persona->id,
                'verification_status' => $persona->verification_status,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Semak kelayakan persona dan pulangkan sebab jika gagal.
     *
     * @return array{ok: bool, reason: ?string}
     */
    public function semakPersona(?MediaPersona $persona): array
    {
        if (! $persona) {
            return ['ok' => false, 'reason' => 'persona_not_found'];
        }

        if (! $persona->is_active) {
            return ['ok' => false, 'reason' => 'persona_not_active'];
        }

        if ($persona->verification_status !== 'verified' || ! $persona->verified_at) {
            return ['ok' => false, 'reason' => 'persona_not_verified'];
        }

        if (! $persona->avatar_ref) {
            return ['ok' => false, 'reason' => 'persona_missing_avatar'];
        }

        return ['ok' => true, 'reason' => null];
    }
}
