<?php

namespace App\Modules\HRMS\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * Pagar consent biometrik per tenant (BAB 56C.6A).
 *
 * Semua medan di bawah MESTI lengkap sebelum face recognition boleh digunakan.
 */
class BiometricSetting extends Model
{
    use BelongsToTenant;

    protected $table = 'biometric_settings';

    protected $fillable = [
        'tenant_id',
        'enabled',
        'alternative_method',
        'dpo_name',
        'dpo_email',
        'retention_days',
        'consent_version',
        'legal_review_date',
        'privacy_notice',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'retention_days' => 'integer',
        'legal_review_date' => 'date',
    ];

    /** Kaedah alternatif yang diterima (mesti ada sebelum wajah dibenarkan). */
    public const ALTERNATIF = ['rfid', 'pin', 'qr', 'manual'];

    /**
     * Senarai pagar yang belum dipenuhi.
     *
     * @return array<int, string>
     */
    public function pagarBelumLengkap(): array
    {
        $gagal = [];

        if (! in_array($this->alternative_method, self::ALTERNATIF, true)) {
            $gagal[] = 'alternative_method';
        }
        if (blank($this->dpo_name)) {
            $gagal[] = 'dpo_name';
        }
        if (blank($this->dpo_email) || ! filter_var($this->dpo_email, FILTER_VALIDATE_EMAIL)) {
            $gagal[] = 'dpo_email';
        }
        if (blank($this->privacy_notice)) {
            $gagal[] = 'privacy_notice';
        }
        if (blank($this->consent_version)) {
            $gagal[] = 'consent_version';
        }
        if (($this->retention_days ?? 0) < 1 || $this->retention_days > 365) {
            $gagal[] = 'retention_days';
        }
        if (! $this->legal_review_date) {
            $gagal[] = 'legal_review_date';
        } elseif ($this->legal_review_date->lt(now()->subYear())) {
            $gagal[] = 'legal_review_expired';
        }

        return $gagal;
    }

    public function bolehAktif(): bool
    {
        return $this->pagarBelumLengkap() === [];
    }

    /** Semakan penuh: pagar tenant lengkap DAN ciri dihidupkan. */
    public static function aktifUntukTenant(): bool
    {
        $s = static::first();

        return $s !== null && $s->enabled && $s->bolehAktif();
    }
}
