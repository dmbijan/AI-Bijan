<?php

namespace App\Modules\HRMS\Services;

use App\Modules\HRMS\Models\BiometricAccessLog;
use App\Modules\HRMS\Models\BiometricConsent;
use App\Modules\HRMS\Models\BiometricSetting;
use App\Services\AuditLogger;
use Illuminate\Validation\ValidationException;

/**
 * Penguatkuasa pagar consent biometrik (BAB 56C.6A).
 *
 * Semua padanan wajah MESTI melalui kelas ini — tiada laluan lain.
 */
class BiometricGateService
{
    /** Konfigurasi pagar tenant semasa. */
    public function tetapan(): ?BiometricSetting
    {
        return BiometricSetting::query()->first();
    }

    /**
     * Simpan konfigurasi pagar.
     *
     * @throws ValidationException jika cuba hidupkan tanpa pagar lengkap
     */
    public function simpanTetapan(array $data): BiometricSetting
    {
        $s = $this->tetapan() ?? new BiometricSetting;

        $s->fill([
            'alternative_method' => $data['alternative_method'] ?? $s->alternative_method,
            'dpo_name' => $data['dpo_name'] ?? $s->dpo_name,
            'dpo_email' => $data['dpo_email'] ?? $s->dpo_email,
            'retention_days' => $data['retention_days'] ?? $s->retention_days,
            'consent_version' => $data['consent_version'] ?? $s->consent_version,
            'legal_review_date' => $data['legal_review_date'] ?? $s->legal_review_date,
            'privacy_notice' => $data['privacy_notice'] ?? $s->privacy_notice,
        ]);

        $mahuAktif = (bool) ($data['enabled'] ?? $s->enabled ?? false);
        $gagal = $s->pagarBelumLengkap();

        if ($mahuAktif && $gagal !== []) {
            throw ValidationException::withMessages([
                'enabled' => ['Tidak boleh diaktifkan. Pagar belum lengkap: '.implode(', ', $gagal)],
            ]);
        }

        $s->enabled = $mahuAktif;
        $s->save();

        app(AuditLogger::class)->log('biometric.settings_saved', $s, null, [
            'enabled' => $s->enabled,
            'pending_gates' => $gagal,
        ]);

        return $s;
    }

    /**
     * Semak sama ada padanan wajah dibenarkan untuk pengguna ini.
     *
     * @return array{ok: bool, sebab: ?string}
     */
    public function bolehPadan(int $userId): array
    {
        $s = $this->tetapan();

        if (! $s || ! $s->enabled) {
            return ['ok' => false, 'sebab' => 'Ciri biometrik belum diaktifkan untuk syarikat ini.'];
        }

        if ($s->pagarBelumLengkap() !== []) {
            return ['ok' => false, 'sebab' => 'Pagar consent tidak lagi lengkap.'];
        }

        if (! BiometricConsent::adaConsentAktif($userId)) {
            return ['ok' => false, 'sebab' => 'Pekerja ini tiada consent biometrik yang sah.'];
        }

        return ['ok' => true, 'sebab' => null];
    }

    /** Rekod setiap cubaan padanan (berjaya atau ditolak). */
    public function logPadanan(?int $userId, ?string $cameraRef, bool $matched, ?float $confidence, string $outcome, string $purpose = 'attendance'): BiometricAccessLog
    {
        return BiometricAccessLog::create([
            'user_id' => $userId,
            'camera_ref' => $cameraRef,
            'matched' => $matched,
            'confidence' => $confidence,
            'purpose' => $purpose,
            'outcome' => $outcome,
        ]);
    }

    /**
     * Tujuan yang dibenarkan (BAB 56C.6A "Had Penggunaan").
     *
     * @return array<int, string>
     */
    public static function tujuanDibenarkan(): array
    {
        return ['attendance', 'restricted_zone', 'door_identity'];
    }

    public static function tujuanDilarang(): array
    {
        return ['productivity', 'performance', 'movement_tracking', 'customer_recognition'];
    }
}
