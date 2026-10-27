<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\AgentStudio\Models\AgentBlueprint;
use App\Modules\McpStudio\Models\McpPrompt;
use App\Modules\MediaEditor\Models\MediaProject;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul AgentStudio (blueprint ejen), McpStudio (prompt
 * MCP) + MediaEditor (projek media).
 *
 * Ditambah 27 Okt 2026 (P1-1d).
 *
 * • AgentStudio/McpStudio: `system_prompt` dan templat prompt ialah harta
 *   intelektual (cara tenant mengautomasi kerja). Kebocoran = kelebihan
 *   daya saing hilang.
 * • MediaEditor: projek reka bentuk beserta aset jenama (brand kit).
 */
class StudioBlueprintIsolationTest extends UjianPengasingan
{
    protected string $modul = 'AgentStudio';

    protected array $model = [
        AgentBlueprint::class,
        McpPrompt::class,
        MediaProject::class,
    ];

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected string $sebabTiadaApi = 'laluan studio memerlukan entitlement khas';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        if ($this->rekodSemasa === McpPrompt::class) {
            return [
                'name' => 'prompt_rahsia_' . $n,
                'title' => 'Prompt Rahsia Tenant Satu ' . $n,
                'template' => 'Rahsia Tenant Satu',
            ];
        }

        if ($this->rekodSemasa === MediaProject::class) {
            return [
                'name' => 'Projek Rahsia Tenant Satu ' . $n,
                'project_type' => 'image',
                'status' => 'draft',
                'canvas' => json_encode(['width' => 1080, 'height' => 1080]),
            ];
        }

        return [
            'key' => 'ejen_rahsia_' . $n,
            'name' => 'Ejen Rahsia Tenant Satu ' . $n,
            'category' => 'general',
            'status' => 'draft',
        ];
    }
}
