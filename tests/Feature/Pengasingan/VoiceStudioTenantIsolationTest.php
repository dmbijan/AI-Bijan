<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\VoiceStudio\Models\VoiceProfile;
use App\Modules\VoiceStudio\Models\VoiceTrack;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul VoiceStudio (enjin suara / TTS).
 *
 * Ditambah 8 Nov 2026. Profil suara dan teks yang disintesis mungkin
 * mengandungi maklumat peribadi atau teks promosi pelanggan.
 */
class VoiceStudioTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'VoiceStudio';

    protected array $model = [
        VoiceProfile::class,
        VoiceTrack::class,
    ];

    protected ?string $laluanApi = '/api/v1/voice-studio/profiles';

    protected ?string $namaRahsia = 'Profil Suara Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === VoiceProfile::class) {
            return [
                'name' => 'Profil Suara Rahsia Tenant Satu '.$n,
                'slug' => 'profil-suara-rahsia-'.$n,
                'language' => 'ms',
                'engine' => 'elevenlabs',
                'status' => VoiceProfile::STATUS_ACTIVE,
            ];
        }

        $profil = VoiceProfile::create([
            'name' => 'Profil Suara Track '.$n,
            'slug' => 'profil-suara-track-'.$n,
            'language' => 'ms',
        ]);

        return [
            'uuid' => 'voice-uuid-'.$n,
            'voice_profile_id' => $profil->id,
            'engine' => 'elevenlabs',
            'text' => 'Teks Rahsia Tenant Satu '.$n,
            'text_length' => 25,
            'status' => VoiceTrack::STATUS_PENDING,
        ];
    }
}
