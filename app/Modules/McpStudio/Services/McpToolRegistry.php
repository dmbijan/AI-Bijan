<?php

namespace App\Modules\McpStudio\Services;

use App\Modules\McpStudio\Models\McpAuditLog;
use App\Modules\McpStudio\Models\McpExecution;
use App\Modules\McpStudio\Models\McpResource;
use App\Modules\McpStudio\Models\McpSession;
use App\Modules\McpStudio\Models\McpTool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * McpToolRegistry — pendaftaran & pelaksanaan alat MCP.
 *
 * IDEA #1 (BAB 84): "AI MCP Studio" — membolehkan mana-mana AI luar
 * (Claude Desktop, Cursor, n8n, Make) bercakap dengan AI BIJAN
 * melalui protokol MCP (Model Context Protocol).
 *
 * Keselamatan:
 * - ALLOWLIST hanya — tiada Tool calling secara bebas
 * - Tool destructive mesti kelulusan manusia (human-in-the-loop)
 * - Semua panggilan direkod dalam mcp_executions + mcp_audit_logs
 */
class McpToolRegistry
{
    /**
     * Senarai putih modul & tindakan yang dibenarkan.
     *
     * Kunci = modul AI BIJAN, nilai = senarai tindakan yang selamat.
     * Tindakan di luar senarai ini akan ditolak.
     */
    public const ALLOWLIST = [
        'crm' => ['list', 'show', 'search', 'count', 'create', 'update'],
        'invoicing' => ['list', 'show', 'count', 'export'],
        'logistics' => ['list', 'show', 'count'],
        'marketing' => ['list', 'show', 'count'],
        'prospecting' => ['list', 'show', 'search', 'count', 'create', 'update'],
        'cod' => ['list', 'show', 'count'],
        'fleet' => ['list', 'show', 'count'],
        'booking' => ['list', 'show', 'create'],
        'pos' => ['list', 'show'],
        'marketplace' => ['list', 'show', 'count'],
        'loyalty' => ['list', 'show', 'count'],
        'hrms' => ['list', 'show', 'count'],
        'documents' => ['list', 'show'],
        'media' => ['list', 'show', 'count'],
        'analytics' => ['list', 'show', 'run'],
    ];

    /**
     * Tindakan yang mengubah data (memerlukan kelulusan jika destructive).
     */
    public const TINDAKAN_MUBAH = ['create', 'update', 'delete', 'execute', 'approve'];

    /**
     * Daftarkan alat baharu.
     */
    public function daftar(
        int $tenantId,
        string $name,
        string $title,
        ?string $description = null,
        ?string $sourceModule = null,
        ?string $action = null,
        ?string $method = null,
        array $parameterSchema = [],
        bool $requiresApproval = true,
        bool $isDestructive = false
    ): McpTool {
        $tool = McpTool::updateOrCreate(
            ['tenant_id' => $tenantId, 'name' => $name],
            [
                'title' => $title,
                'description' => $description,
                'source_module' => $sourceModule,
                'action' => $action,
                'method' => $method,
                'parameters_schema' => $parameterSchema ?: null,
                'requires_approval' => $requiresApproval,
                'is_destructive' => $isDestructive,
                'status' => 'active',
                'permission_scope' => $sourceModule ? [$sourceModule . '.' . ($action ?? 'view')] : null,
            ]
        );

        Log::info('MCP Studio: alat didaftar', ['tenant_id' => $tenantId, 'tool' => $name]);

        return $tool;
    }

    /**
     * Senarai alat aktif.
     */
    public function senaraiAlat(int $tenantId, ?string $module = null): array
    {
        $query = McpTool::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->orderBy('source_module')
            ->orderBy('name');

        if ($module) {
            $query->where('source_module', $module);
        }

        return $query->get()->map(fn ($t) => [
            'name' => $t->name,
            'title' => $t->title,
            'description' => $t->description,
            'source_module' => $t->source_module,
            'action' => $t->action,
            'input_schema' => $t->skemaParameter(),
            'requires_approval' => $t->requires_approval,
            'is_destructive' => $t->is_destructive,
            'call_count' => $t->call_count,
        ])->toArray();
    }

    /**
     * Semak sama ada tindakan dibenarkan.
     */
    public function dibenarkan(?string $module, ?string $action): bool
    {
        if (! $module || ! $action) {
            return false;
        }

        return in_array($action, self::ALLOWLIST[$module] ?? [], true);
    }

    /**
     * Panggil alat MCP.
     *
     * Jika alat memerlukan kelulusan dan belum diluluskan,
     * cipta execution berstatus awaiting_approval.
     */
    public function panggil(
        int $tenantId,
        string $toolName,
        array $argumen,
        ?int $sessionId = null
    ): McpExecution {
        $tool = McpTool::query()
            ->where('tenant_id', $tenantId)
            ->where('name', $toolName)
            ->first();

        if (! $tool) {
            throw new \InvalidArgumentException("Alat MCP '{$toolName}' tidak wujud");
        }

        if (! $tool->isActive()) {
            throw new \RuntimeException("Alat MCP '{$toolName}' tidak aktif");
        }

        // Semak allowlist
        if (! $this->dibenarkan($tool->source_module, $tool->action)) {
            throw new \RuntimeException(
                "Tindakan '{$tool->action}' pada modul '{$tool->source_module}' tidak dibenarkan oleh dasar keselamatan"
            );
        }

        // Validasi argumen
        $validasi = $tool->validasiArgumen($argumen);
        if (! $validasi['valid']) {
            throw new \InvalidArgumentException('Argumen tidak sah: ' . implode('; ', $validasi['errors']));
        }

        $perluKelulusan = $tool->requiresApproval() && $tool->isDestructive();

        $execution = McpExecution::create([
            'tenant_id' => $tenantId,
            'mcp_tool_id' => $tool->id,
            'mcp_session_id' => $sessionId,
            'tool_name' => $toolName,
            'arguments' => $argumen,
            'status' => $perluKelulusan ? McpExecution::STATUS_AWAITING_APPROVAL : McpExecution::STATUS_PENDING,
            'approval_status' => $perluKelulusan ? McpExecution::APPROVAL_PENDING : McpExecution::APPROVAL_NOT_REQUIRED,
        ]);

        $this->audit($tenantId, $execution->id, McpAuditLog::ACTION_TOOL_CALLED, [
            'tool' => $toolName,
            'module' => $tool->source_module,
            'action' => $tool->action,
            'requires_approval' => $perluKelulusan,
        ]);

        if (! $perluKelulusan) {
            $this->lancarkan($tenantId, $execution);
        }

        return $execution->fresh();
    }

    /**
     * Luluskan pelaksanaan (human-in-the-loop).
     */
    public function luluskan(int $tenantId, int $executionId, ?int $approvedBy = null): McpExecution
    {
        $execution = McpExecution::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($executionId);

        if (! $execution->menungguKelulusan()) {
            throw new \RuntimeException('Pelaksanaan ini tidak menunggu kelulusan');
        }

        $execution->update([
            'approval_status' => McpExecution::APPROVAL_APPROVED,
            'approved_by' => $approvedBy ?? auth()->id(),
            'approved_at' => now(),
            'status' => McpExecution::STATUS_APPROVED,
        ]);

        $this->audit($tenantId, $execution->id, McpAuditLog::ACTION_TOOL_APPROVED, [
            'approved_by' => $approvedBy ?? auth()->id(),
        ]);

        $this->lancarkan($tenantId, $execution);

        return $execution->fresh();
    }

    /**
     * Tolak pelaksanaan.
     */
    public function tolak(int $tenantId, int $executionId, ?string $sebab = null, ?int $rejectedBy = null): McpExecution
    {
        $execution = McpExecution::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($executionId);

        $execution->update([
            'approval_status' => McpExecution::APPROVAL_REJECTED,
            'approved_by' => $rejectedBy ?? auth()->id(),
            'approved_at' => now(),
            'status' => McpExecution::STATUS_REJECTED,
            'rejection_reason' => $sebab,
        ]);

        $this->audit($tenantId, $execution->id, McpAuditLog::ACTION_TOOL_REJECTED, [
            'reason' => $sebab,
            'rejected_by' => $rejectedBy ?? auth()->id(),
        ]);

        return $execution->fresh();
    }

    /**
     * LANCARKAN — jalankan tindakan sebenar pada modul.
     */
    private function lancarkan(int $tenantId, McpExecution $execution): void
    {
        $mula = microtime(true);

        try {
            // Guna sifat (bukan method) supaya dapat objek model, bukan objek relation
            $tool = $execution->tool;

            if (! $tool) {
                throw new \RuntimeException('Alat tidak dijumpai');
            }

            $hasil = $this->jalankanTindakan($tool, $execution->arguments ?? []);

            $execution->update([
                'status' => McpExecution::STATUS_EXECUTED,
                'result' => $hasil,
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
            ]);

            $tool->increment('call_count');

            $this->audit($tenantId, $execution->id, McpAuditLog::ACTION_TOOL_EXECUTED, [
                'duration_ms' => $execution->duration_ms,
            ]);
        } catch (\Throwable $e) {
            $execution->update([
                'status' => McpExecution::STATUS_FAILED,
                'error_message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
            ]);

            Log::error('MCP Studio: pelaksanaan gagal', [
                'tenant_id' => $tenantId,
                'tool' => $execution->tool_name,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * mappedkan tindakan ke query sebenar.
     *
     * Pendekatan selamat: gunakan allowlist + query builder Eloquent
     * dengan had (limit) supaya AI tidak boleh "tarik" keseluruhan pangkalan data.
     */
    private function jalankanTindakan(McpTool $tool, array $argumen): array
    {
        $modul = $tool->source_module;
        $tindakan = $tool->action;
        $modelClass = $this->selesaikanModel($modul);

        if (! $modelClass) {
            throw new \RuntimeException("Modul '{$modul}' tidak disokong");
        }

        $had = (int) ($argumen['limit'] ?? 25);
        $had = max(1, min($had, 100));

        $query = $modelClass::query();

        // Tenant isolation automatik melalui trait BelongsToTenant

        switch ($tindakan) {
            case 'count':
                return ['count' => $query->count()];

            case 'list':
            case 'search':
                if (! empty($argumen['q']) && in_array('search', $tindakan === 'search' ? [$tindakan] : [])) {
                    $query->where(function ($q) use ($argumen) {
                        foreach (['name', 'title', 'email', 'code', 'order_no'] as $medan) {
                            $q->orWhere($medan, 'like', '%' . $argumen['q'] . '%');
                        }
                    });
                }

                $baris = $query->limit($had)->get();

                return [
                    'count' => $baris->count(),
                    'rows' => $baris->map(function ($b) {
                        $array = $b->toArray();
                        unset($array['tenant_id']);

                        return $array;
                    })->all(),
                ];

            case 'show':
                $id = $argumen['id'] ?? null;
                if ($id === null) {
                    throw new \InvalidArgumentException("Parameter 'id' diperlukan untuk tindakan show");
                }

                $model = $modelClass::query()->find($id);
                if (! $model) {
                    return ['found' => false, 'id' => $id];
                }

                $array = $model->toArray();
                unset($array['tenant_id']);

                return ['found' => true, 'row' => $array];

            case 'export':
                $baris = $query->limit($had)->get();

                return [
                    'count' => $baris->count(),
                    'rows' => $baris->map(fn ($b) => $b->toArray())->all(),
                ];

            case 'run':
                return ['status' => 'accepted', 'note' => 'Tindakan run dicetuskan'];

            case 'create':
                $medan = $this->medanBolehTulis($modul);
                $data = array_intersect_key($argumen, array_flip($medan));
                $data['tenant_id'] = $tool->tenant_id;

                $model = $modelClass::create($data);

                return ['created' => true, 'id' => $model->getKey()];

            case 'update':
                $id = $argumen['id'] ?? null;
                if ($id === null) {
                    throw new \InvalidArgumentException("Parameter 'id' diperlukan untuk tindakan update");
                }

                $medan = $this->medanBolehTulis($modul);
                $data = array_intersect_key($argumen, array_flip($medan));
                unset($data['id']);

                $model = $modelClass::query()->findOrFail($id);
                $model->update($data);

                return ['updated' => true, 'id' => $model->getKey()];

            default:
                throw new \RuntimeException("Tindakan '{$tindakan}' tidak disokong");
        }
    }

    /**
     * Resolusi modul ke kelas Eloquent.
     *
     * Hanya kelas yang benar-benar wujud — MCP registry tidak boleh
     * menunjuk kepada model yang tidak wujud (akan pecah pada runtime).
     */
    private function selesaikanModel(string $modul): ?string
    {
        $peta = [
            'crm' => \App\Modules\Crm\Models\Customer::class,
            'invoicing' => \App\Modules\Invoicing\Models\Invoice::class,
            'logistics' => \App\Modules\Logistics\Models\Shipment::class,
            'marketing' => \App\Modules\Marketing\Models\Campaign::class,
            'prospecting' => \App\Modules\Prospecting\Models\Prospect::class,
            'cod' => \App\Modules\Cod\Models\CodOrder::class,
            'fleet' => \App\Modules\Fleet\Models\FleetVehicle::class,
            'booking' => \App\Modules\Booking\Models\Appointment::class,
            'pos' => \App\Modules\POS\Models\POSSale::class,
            'marketplace' => \App\Modules\Marketplace\Models\Order::class,
            'loyalty' => \App\Modules\Loyalty\Models\Member::class,
            'hrms' => \App\Modules\HRMS\Models\Employee::class,
            'documents' => \App\Documents\Models\Document::class,
            'media' => \App\Modules\MediaStudio\Models\MediaAsset::class,
        ];

        $class = $peta[$modul] ?? null;

        return ($class && class_exists($class)) ? $class : null;
    }

    /**
     * Medan yang boleh ditulis oleh AI (kekangan keselamatan).
     */
    private function medanBolehTulis(string $modul): array
    {
        $peta = [
            'crm' => ['name', 'email', 'phone', 'company', 'address', 'notes'],
            'prospecting' => ['first_name', 'last_name', 'email', 'phone', 'company', 'job_title', 'notes'],
            'booking' => ['customer_name', 'service_id', 'scheduled_at', 'notes'],
        ];

        return $peta[$modul] ?? [];
    }

    /**
     * Baca sumber MCP.
     */
    public function bacaResource(int $tenantId, string $uri): array
    {
        $resource = McpResource::query()
            ->where('tenant_id', $tenantId)
            ->where('uri', $uri)
            ->first();

        if (! $resource) {
            throw new \InvalidArgumentException("Sumber MCP '{$uri}' tidak wujud");
        }

        if (! $resource->isActive()) {
            throw new \RuntimeException("Sumber MCP '{$uri}' tidak aktif");
        }

        $resource->increment('access_count');

        $this->audit($tenantId, null, McpAuditLog::ACTION_RESOURCE_READ, ['uri' => $uri]);

        return [
            'uri' => $resource->uri,
            'mimeType' => $resource->mime_type,
            'name' => $resource->name,
            'source_table' => $resource->source_table,
        ];
    }

    /**
     * Rekod audit.
     */
    public function audit(int $tenantId, ?int $executionId, string $action, array $context = []): McpAuditLog
    {
        return McpAuditLog::create([
            'tenant_id' => $tenantId,
            'mcp_execution_id' => $executionId,
            'action' => $action,
            'actor_type' => auth()->check() ? 'human' : 'ai',
            'actor_id' => auth()->id(),
            'context' => $context ?: null,
            'ip_address' => request()->ip(),
        ]);
    }

    /**
     * Senarai pelaksanaan yang menunggu kelulusan.
     */
    public function senaraiMenungguKelulusan(int $tenantId): array
    {
        return McpExecution::query()
            ->where('tenant_id', $tenantId)
            ->where('approval_status', McpExecution::APPROVAL_PENDING)
            ->orderBy('created_at')
            ->get()
            ->toArray();
    }
}
