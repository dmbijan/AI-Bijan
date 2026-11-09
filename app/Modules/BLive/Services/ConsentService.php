<?php

namespace App\Modules\BLive\Services;

use App\Modules\BLive\Models\Avatar;
use App\Modules\BLive\Models\ConsentRecord;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Str;

/**
 * ConsentService - pagar keselamatan identiti & suara.
 *
 * Aturan (blueprint bahagian 14.1 - Deepfake dan identiti):
 *   1. Avatar `ai_generated` boleh diluluskan automatik - tiada identiti sebenar.
 *   2. Avatar `photo_to_avatar` / `custom_digital_twin` MESTI ada rekod
 *      kebenaran yang `granted` dan belum tamat tempoh.
 *   3. Menarik balik kebenaran menyebabkan avatar terus lose kelayakan.
 *   4. Hos digital WAJIB didedahkan sebagai AI.
 */
class ConsentService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * Rekod kebenaran baharu.
     *
     * @param  array<string, mixed>  $data
     */
    public function cipta(array $data): ConsentRecord
    {
        $jenis = $data['consent_type'] ?? ConsentRecord::TYPE_AI_GENERATED;

        if (! in_array($jenis, (array) config('blive.consent.allowed_consent_types', []), true)) {
            throw new \InvalidArgumentException('Jenis kebenaran tidak disokong: '.$jenis);
        }

        $rekod = ConsentRecord::create(array_merge([
            'uuid' => (string) Str::uuid(),
            'consent_type' => $jenis,
            'status' => ConsentRecord::STATUS_PENDING,
            'created_by' => auth()->id(),
        ], $data));

        // Avatar AI-generated tidak memerlukan bukti manual.
        if ($jenis === ConsentRecord::TYPE_AI_GENERATED) {
            $this->luluskan($rekod);
        }

        return $rekod->fresh();
    }

    /**
     * Cari avatar yang terikat pada rekod kebenaran ini.
     *
     * Pautan mesti berfungsi dari kedua-dua arah: avatar boleh dicipta
     * dahulu (lalu `consent_record_id` diisi), atau rekod dahulu
     * (lalu `avatar_id` diisi).
     */
    protected function avatarTerikat(?ConsentRecord $rekod): ?Avatar
    {
        if ($rekod === null) {
            return null;
        }

        if ($rekod->avatar_id) {
            $avatar = $rekod->avatar()->first();

            if ($avatar) {
                return $avatar;
            }
        }

        if ($rekod->id) {
            return Avatar::query()->where('consent_record_id', $rekod->id)->first();
        }

        return null;
    }

    /**
     * Luluskan kebenaran.
     */
    public function luluskan(ConsentRecord $rekod): ConsentRecord
    {
        $rekod->update([
            'status' => ConsentRecord::STATUS_GRANTED,
            'granted_at' => now(),
            'revoked_at' => null,
        ]);

        $this->avatarTerikat($rekod)?->update(['consent_status' => Avatar::CONSENT_GRANTED]);

        return $rekod->fresh();
    }

    /**
     * Tarik balik kebenaran.
     */
    public function tarikBalik(ConsentRecord $rekod, ?string $nota = null): ConsentRecord
    {
        $rekod->update([
            'status' => ConsentRecord::STATUS_REVOKED,
            'revoked_at' => now(),
            'notes' => $nota ?? $rekod->notes,
        ]);

        $this->avatarTerikat($rekod)?->update(['consent_status' => Avatar::CONSENT_REVOKED]);

        return $rekod->fresh();
    }

    /**
     * Semak sama ada avatar layak untuk diletakkan live.
     *
     * @return array{ok: bool, reason: ?string, code: ?string}
     */
    public function semakAvatar(Avatar $avatar): array
    {
        if ($avatar->status === Avatar::STATUS_ARCHIVED) {
            return ['ok' => false, 'reason' => 'Avatar telah diarkib.', 'code' => 'avatar_archived'];
        }

        if ($avatar->status !== Avatar::STATUS_READY) {
            return ['ok' => false, 'reason' => 'Avatar belum siap dijana.', 'code' => 'avatar_not_ready'];
        }

        if (config('blive.safety.require_ai_disclosure', true) && ! $avatar->is_ai_disclosed) {
            return [
                'ok' => false,
                'reason' => 'Hos digital mesti didedahkan sebagai AI presenter.',
                'code' => 'ai_disclosure_required',
            ];
        }

        if (config('blive.consent.require_consent_record', true)) {
            $rekod = $avatar->consentRecord;

            if ($rekod === null) {
                // Avatar AI-generated: belum ada rekod diluluskan secara automatik.
                if ($avatar->avatar_type === Avatar::TYPE_AI_GENERATED) {
                    return ['ok' => true, 'reason' => null, 'code' => null];
                }

                return [
                    'ok' => false,
                    'reason' => 'Avatar memerlukan rekod kebenaran yang sah.',
                    'code' => 'consent_record_missing',
                ];
            }

            if (! $rekod->sah()) {
                return [
                    'ok' => false,
                    'reason' => 'Kebenaran avatar tidak sah: '.$rekod->status,
                    'code' => 'consent_invalid',
                ];
            }
        }

        if ($avatar->consent_status === Avatar::CONSENT_REVOKED) {
            return [
                'ok' => false,
                'reason' => 'Kebenaran avatar telah ditarik balik.',
                'code' => 'consent_revoked',
            ];
        }

        return ['ok' => true, 'reason' => null, 'code' => null];
    }
}
