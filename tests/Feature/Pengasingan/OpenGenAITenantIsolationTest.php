<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\OpenGenAI\Models\GenAiJob;
use App\Modules\OpenGenAI\Models\GenAiProvider;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul OpenGenAI (enjin generatif pelbagai provider).
 *
 * Ditambah 8 Nov 2026. Provider menyimpan kunci API dan kunci model;
 * job menyimpan prompt pengguna — data peribadi + rahsia perniagaan.
 */
class OpenGenAITenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'OpenGenAI';

    protected array $model = [
        GenAiProvider::class,
        GenAiJob::class,
    ];

    protected ?string $laluanApi = '/api/v1/open-gen-ai/providers';

    protected ?string $namaRahsia = 'Provider Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === GenAiProvider::class) {
            return [
                'name' => 'Provider Rahsia Tenant Satu '.$n,
                'slug' => 'provider-rahsia-'.$n,
                'kind' => GenAiProvider::KIND_OPENAI,
                'api_key' => 'sk-rahasia-'.$n,
                'default_model' => 'gpt-4o-mini',
                'capabilities' => ['text'],
                'is_active' => true,
            ];
        }

        $provider = GenAiProvider::create([
            'name' => 'Provider Job Tenant Satu '.$n,
            'slug' => 'provider-job-'.$n,
            'kind' => GenAiProvider::KIND_OPENAI,
            'api_key' => 'sk-job-'.$n,
            'capabilities' => ['text'],
        ]);

        return [
            'uuid' => 'job-uuid-'.$n,
            'provider_id' => $provider->id,
            'model' => 'gpt-4o-mini',
            'capability' => GenAiJob::KEMAHRIRAN_TEXT,
            'status' => GenAiJob::STATUS_PENDING,
            'prompt' => 'Prompt Rahsia Tenant Satu '.$n,
        ];
    }
}
