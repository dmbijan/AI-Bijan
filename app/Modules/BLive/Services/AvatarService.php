<?php

namespace App\Modules\BLive\Services;

use App\Modules\BLive\Models\Avatar;
use App\Modules\BLive\Models\ConsentRecord;
use App\Modules\BLive\Models\HostPersonality;
use App\Modules\BLive\Models\RenderJob;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * AvatarService - modul B-Avatar Creator.
 *
 * Mengurus kitaran hidup avatar:
 *   draft -> generating -> ready -> archived
 *
 * Setiap avatar ialah aset beriversi. Perubahan identiti menaikkan
 * `asset_version` supaya render manifest kekal jejak.
 */
class AvatarService
{
    public function __construct(
        private TenantContext $ctx,
        private AvatarEngineService $engine,
        private ConsentService $consent,
    ) {}

    /**
     * Senarai avatar.
     *
     * @param  array<string, mixed>  $filter
     * @return Collection<int, Avatar>
     */
    public function senarai(array $filter = [])
    {
        $query = Avatar::query()->orderBy('name');

        if (! empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (! empty($filter['avatar_type'])) {
            $query->where('avatar_type', $filter['avatar_type']);
        }

        if (! empty($filter['consent_status'])) {
            $query->where('consent_status', $filter['consent_status']);
        }

        return $query->limit((int) ($filter['limit'] ?? 100))->get();
    }

    /**
     * Cipta avatar baharu.
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, avatar: ?Avatar, error: ?string}
     */
    public function cipta(array $data): array
    {
        $had = (int) config('blive.limits.max_avatar_per_tenant', 25);

        if (Avatar::count() >= $had) {
            return [
                'success' => false,
                'avatar' => null,
                'error' => 'Had avatar untuk syarikat anda telah tercapai ('.$had.').',
            ];
        }

        $jenis = $data['avatar_type'] ?? Avatar::TYPE_AI_GENERATED;

        if (! in_array($jenis, [Avatar::TYPE_AI_GENERATED, Avatar::TYPE_PHOTO, Avatar::TYPE_DIGITAL_TWIN], true)) {
            return ['success' => false, 'avatar' => null, 'error' => 'Jenis avatar tidak sah: '.$jenis];
        }

        // Identiti sebenar mesti ada kebenaran sebelum avatar boleh ditetapkan.
        $rekodKebenaran = null;

        if ($jenis !== Avatar::TYPE_AI_GENERATED) {
            $rekodKebenaran = $this->consent->cipta([
                'consent_type' => $jenis === Avatar::TYPE_DIGITAL_TWIN
                    ? ConsentRecord::TYPE_INDIVIDUAL_RELEASE
                    : ConsentRecord::TYPE_LICENSED,
                'subject_name' => $data['subject_name'] ?? ($data['name'] ?? null),
                'subject_reference' => $data['subject_reference'] ?? null,
                'notes' => $data['consent_notes'] ?? null,
            ]);
        }

        $avatar = Avatar::create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']).'-'.Str::random(6),
            'description' => $data['description'] ?? null,
            'avatar_type' => $jenis,
            'engine' => $data['engine'] ?? config('blive.avatar_engine', 'simulated'),
            'voice_profile_id' => $data['voice_profile_id'] ?? null,
            'host_personality_id' => $data['host_personality_id'] ?? null,
            'render_quality' => $data['render_quality'] ?? 'high',
            'appearance' => $data['appearance'] ?? [],
            'body_traits' => $data['body_traits'] ?? [],
            'consent_record_id' => $rekodKebenaran?->id,
            'consent_status' => $rekodKebenaran?->status ?? Avatar::CONSENT_GRANTED,
            'is_ai_disclosed' => (bool) ($data['is_ai_disclosed'] ?? true),
            'status' => Avatar::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        if ($rekodKebenaran) {
            $rekodKebenaran->update(['avatar_id' => $avatar->id]);
        }

        return ['success' => true, 'avatar' => $avatar, 'error' => null];
    }

    /**
     * Jana avatar melalui enjin render.
     *
     * @param  array<string, mixed>  $specification
     * @return array{success: bool, avatar: ?Avatar, job: ?RenderJob, error: ?string}
     */
    public function jana(Avatar $avatar, array $specification = []): array
    {
        $hasil = $this->engine->jana($avatar, $specification);

        if (! $hasil['success']) {
            return [
                'success' => false,
                'avatar' => $avatar->fresh(),
                'job' => $hasil['job'],
                'error' => $hasil['error'],
            ];
        }

        $avatar->refresh();

        // Naikkan versi aset selepas penjanaan berjaya.
        $avatar->update(['asset_version' => $avatar->asset_version + 1]);

        return ['success' => true, 'avatar' => $avatar->fresh(), 'job' => $hasil['job'], 'error' => null];
    }

    /**
     * Kemas kini avatar.
     *
     * @param  array<string, mixed>  $data
     */
    public function kemasKini(Avatar $avatar, array $data): Avatar
    {
        $bolehUbah = ['name', 'description', 'render_quality', 'appearance', 'body_traits',
            'voice_profile_id', 'host_personality_id', 'engine', 'is_ai_disclosed'];

        $payload = array_intersect_key($data, array_flip($bolehUbah));

        if ($payload !== []) {
            $avatar->update($payload);
        }

        return $avatar->fresh();
    }

    /**
     * Arkib avatar.
     */
    public function arkib(Avatar $avatar): Avatar
    {
        $avatar->update(['status' => Avatar::STATUS_ARCHIVED]);

        return $avatar->fresh();
    }

    /**
     * Semak kelayakan avatar untuk live.
     *
     * @return array{ok: bool, reason: ?string, code: ?string}
     */
    public function semakKelayakan(Avatar $avatar): array
    {
        return $this->consent->semakAvatar($avatar);
    }

    /**
     * Senarai personaliti aktif yang boleh digunakan.
     */
    public function personaliti(): Collection
    {
        return HostPersonality::query()->where('is_active', true)->orderBy('name')->get();
    }
}
