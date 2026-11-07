<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\ScreenRecorder\Models\RecordingComment;
use App\Modules\ScreenRecorder\Models\ScreenRecording;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul ScreenRecorder (rakaman skrin).
 *
 * Ditambah 8 Nov 2026. Rakaman skrin boleh mengandungi maklumat sistem
 * dalaman yang tidak patut bocor kepada tenant lain.
 */
class ScreenRecorderTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'ScreenRecorder';

    protected array $model = [
        ScreenRecording::class,
        RecordingComment::class,
    ];

    protected ?string $laluanApi = '/api/v1/screen-recorder/recordings';

    protected ?string $namaRahsia = 'Rakaman Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === ScreenRecording::class) {
            return [
                'uuid' => 'rec-uuid-'.$n,
                'title' => 'Rakaman Rahsia Tenant Satu '.$n,
                'description' => 'Rakaman Rahsia Tenant Satu '.$n,
                'share_token' => 'token-rahasia-'.$n,
                'status' => ScreenRecording::STATUS_READY,
                'privacy' => ScreenRecording::PRIVACY_PRIVATE,
                'duration_seconds' => 30.5,
            ];
        }

        $recording = ScreenRecording::create([
            'uuid' => 'rec-uuid-komen-'.$n,
            'title' => 'Rakaman Komen '.$n,
            'share_token' => 'token-komen-'.$n,
            'status' => ScreenRecording::STATUS_READY,
            'privacy' => ScreenRecording::PRIVACY_PRIVATE,
        ]);

        return [
            'recording_id' => $recording->id,
            'body' => 'Komen Rahsia Tenant Satu '.$n,
            'timestamp_seconds' => 12.5,
        ];
    }
}
