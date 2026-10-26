<?php

namespace App\Modules\McpStudio\Services;

use App\Modules\McpStudio\Models\McpExecution;
use App\Modules\McpStudio\Models\McpPrompt;
use App\Modules\McpStudio\Models\McpResource;
use App\Modules\McpStudio\Models\McpSession;
use App\Modules\McpStudio\Models\McpTool;
use Illuminate\Support\Facades\Log;

/**
 * McpServerService — pelayan Model Context Protocol.
 *
 * Menyediakan handshake MCP (initialize, tools/list, tools/call,
 * resources/list, resources/read, prompts/list, prompts/get) supaya
 * mana-mana AI luar (Claude Desktop, Cursor, Windsurf, n8n, Zapier)
 * boleh bercakap dengan AI BIJAN.
 *
 * IDEA #1 (BAB 84): MCP Studio — "connect any AI to AI BIJAN".
 */
class McpServerService
{
    public const PROTOCOL_VERSION = '2025-06-18';

    public function __construct(private McpToolRegistry $registry) {}

    /**
     * Handshake — initialize.
     */
    public function initialize(int $tenantId, string $clientName, ?string $clientVersion = null, string $transport = 'http'): array
    {
        $session = McpSession::create([
            'tenant_id' => $tenantId,
            'client_name' => $clientName,
            'client_version' => $clientVersion,
            'transport' => $transport,
            'status' => 'active',
            'capabilities' => [
                'tools' => ['listChanged' => false],
                'resources' => ['subscribe' => false, 'listChanged' => false],
                'prompts' => ['listChanged' => false],
            ],
            'last_seen_at' => now(),
        ]);

        Log::info('MCP Studio: sesi diinisialisasi', [
            'tenant_id' => $tenantId,
            'client' => $clientName,
            'session_id' => $session->id,
        ]);

        return [
            'protocolVersion' => self::PROTOCOL_VERSION,
            'serverInfo' => [
                'name' => 'AI BIJAN',
                'version' => config('aibijan.version', '8.8'),
            ],
            'capabilities' => $session->capabilities,
            'sessionId' => $session->id,
        ];
    }

    /**
     * tools/list
     */
    public function listTools(int $tenantId, ?string $module = null): array
    {
        return [
            'tools' => $this->registry->senaraiAlat($tenantId, $module),
        ];
    }

    /**
     * tools/call
     */
    public function callTool(int $tenantId, string $toolName, array $arguments, ?int $sessionId = null): array
    {
        $execution = $this->registry->panggil($tenantId, $toolName, $arguments, $sessionId);

        if ($execution->menungguKelulusan()) {
            return [
                'executionId' => $execution->id,
                'status' => $execution->status,
                'approval_status' => $execution->approval_status,
                'content' => [[
                    'type' => 'text',
                    'text' => "Alat '{$toolName}' memerlukan kelulusan manusia sebelum dilaksanakan.",
                ]],
                'isError' => false,
            ];
        }

        return [
            'executionId' => $execution->id,
            'status' => $execution->status,
            'content' => [[
                'type' => 'text',
                'text' => json_encode($execution->result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]],
            'isError' => $execution->status === McpExecution::STATUS_FAILED,
        ];
    }

    /**
     * resources/list
     */
    public function listResources(int $tenantId): array
    {
        $resources = McpResource::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn ($r) => [
                'uri' => $r->uri,
                'name' => $r->name,
                'description' => $r->name,
                'mimeType' => $r->mime_type,
            ])
            ->toArray();

        return ['resources' => $resources];
    }

    /**
     * resources/read
     */
    public function readResource(int $tenantId, string $uri): array
    {
        $data = $this->registry->bacaResource($tenantId, $uri);

        return [
            'contents' => [[
                'uri' => $data['uri'],
                'mimeType' => $data['mimeType'],
                'text' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            ]],
        ];
    }

    /**
     * prompts/list
     */
    public function listPrompts(int $tenantId): array
    {
        $prompts = McpPrompt::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn ($p) => [
                'name' => $p->name,
                'title' => $p->title,
                'description' => $p->description,
                'arguments' => collect($p->arguments_schema['properties'] ?? [])
                    ->map(fn ($def, $kunci) => [
                        'name' => $kunci,
                        'description' => $def['description'] ?? '',
                        'required' => in_array($kunci, $p->argumentWajib(), true),
                    ])
                    ->values()
                    ->toArray(),
            ])
            ->toArray();

        return ['prompts' => $prompts];
    }

    /**
     * prompts/get
     */
    public function getPrompt(int $tenantId, string $name, array $arguments = []): array
    {
        $prompt = McpPrompt::query()
            ->where('tenant_id', $tenantId)
            ->where('name', $name)
            ->where('status', 'active')
            ->first();

        if (! $prompt) {
            throw new \InvalidArgumentException("Prompt '{$name}' tidak wujud");
        }

        $prompt->increment('use_count');

        $this->registry->audit($tenantId, null, 'prompt_used', ['prompt' => $name]);

        return [
            'description' => $prompt->description,
            'messages' => [[
                'role' => 'user',
                'content' => [
                    'type' => 'text',
                    'text' => $prompt->render($arguments),
                ],
            ]],
        ];
    }

    /**
     * Konfigurasi pelayan MCP untuk klien (contoh Claude Desktop).
     */
    public function konfigurasiKlien(int $tenantId, string $apiUrl, ?string $apiKey = null): array
    {
        return [
            'mcpServers' => [
                'ai-bijan' => [
                    'url' => rtrim($apiUrl, '/') . '/api/v1/mcp/rpc',
                    'headers' => array_filter([
                        'Authorization' => $apiKey ? 'Bearer ' . $apiKey : null,
                        'X-Tenant-Id' => (string) $tenantId,
                    ]),
                ],
            ],
        ];
    }

    /**
     * Ringkasan aktiviti MCP.
     */
    public function statistik(int $tenantId): array
    {
        $alat = McpTool::query()->where('tenant_id', $tenantId)->count();
        $sesi = McpSession::query()->where('tenant_id', $tenantId)->where('status', 'active')->count();
        $menunggu = McpExecution::query()
            ->where('tenant_id', $tenantId)
            ->where('approval_status', McpExecution::APPROVAL_PENDING)
            ->count();

        $jumlahPanggilan = (int) McpExecution::query()->where('tenant_id', $tenantId)->count();
        $berjaya = (int) McpExecution::query()
            ->where('tenant_id', $tenantId)
            ->where('status', McpExecution::STATUS_EXECUTED)
            ->count();
        $gagal = (int) McpExecution::query()
            ->where('tenant_id', $tenantId)
            ->where('status', McpExecution::STATUS_FAILED)
            ->count();

        return [
            'jumlah_alat' => $alat,
            'sesi_aktif' => $sesi,
            'menunggu_kelulusan' => $menunggu,
            'jumlah_panggilan' => $jumlahPanggilan,
            'berjaya' => $berjaya,
            'gagal' => $gagal,
            'kadar_keputusan' => $jumlahPanggilan > 0 ? round(($berjaya / $jumlahPanggilan) * 100, 1) : 0.0,
        ];
    }
}
