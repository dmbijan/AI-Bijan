<?php

namespace App\Modules\HRMS\Models;

use App\Services\AuditLogger;
use App\Models\Concerns\BelongsToTenant;
use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/** Consent biometrik individu pekerja (BAB 56C.6A). */
class BiometricConsent extends Model
{
    use BelongsToTenant;

    protected $table = 'biometric_consents';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'consent_version',
        'signed_at',
        'withdrawn_at',
        'document_path',
        'ip_address',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /** Consent aktif: sudah ditandatangan dan belum ditarik balik. */
    public function skopAktif($q)
    {
        return $q->whereNull('withdrawn_at');
    }

    public static function aktifUntuk(int $userId): ?self
    {
        return static::where('user_id', $userId)->whereNull('withdrawn_at')->latest('signed_at')->first();
    }

    public static function adaConsentAktif(int $userId): bool
    {
        return static::aktifUntuk($userId) !== null;
    }

    /**
     * Tarik balik consent dan padam embedding dalam transaksi yang sama.
     * Rekod kehadiran (tarikh/masa) TIDAK dipadam — ia diperlukan oleh Akta Kerja.
     */
    public function tarikBalik(): void
    {
        DB::transaction(function () {
            $this->forceFill(['withdrawn_at' => now()])->save();

            $dipadam = BiometricEmbedding::where('user_id', $this->user_id)->delete();

            DB::table('biometric_access_logs')->insert([
                'tenant_id' => app(TenantContext::class)->id(),
                'user_id' => $this->user_id,
                'purpose' => 'consent_withdrawn',
                'matched' => false,
                'outcome' => 'ok',
                'created_at' => now(),
            ]);

            app(AuditLogger::class)->log('biometric.consent_withdrawn', $this, null, [
                'embeddings_purged' => $dipadam,
            ]);
        });
    }
}
