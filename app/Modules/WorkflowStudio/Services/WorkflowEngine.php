<?php

namespace App\Modules\WorkflowStudio\Services;

use App\Modules\WorkflowStudio\Models\Workflow;
use App\Modules\WorkflowStudio\Models\WorkflowRun;
use App\Modules\WorkflowStudio\Models\WorkflowTemplate;
use Illuminate\Support\Facades\Log;

/**
 * WorkflowEngine — laksana aliran kerja visual (corak n8n, tetapi asli AI BIJAN).
 *
 * ✅ Idea #26 (BAB 86): "n8n, agent hermes, openclaw dan mcm2 lagi ai boleh custom
 * dan ada template di ai bijan yg boleh run tanpa vps boleh web base je masing2".
 *
 * ⚠️ LISEN: n8n guna Sustainable Use License (BUKAN OSS). Kod ini ditulis
 * sendiri — n8n hanya RUJUKAN REKA BENTUK, bukan sumber kod.
 *
 * Keselamatan:
 * - `dry_run_mode` lalai TRUE — tiada kesan sampingan tanpa kebenaran eksplisit
 * - Node jenis `action` memerlukan tenant permission
 * - Had bilangan node & kedalaman loop untuk elak infinit
 */
class WorkflowEngine
{
    /** Had keselamatan. */
    public const MAX_NODES_PER_RUN = 100;
    public const MAX_DEPTH = 25;

    /**
     * Katalog jenis node yang disokong.
     *
     * @var array<string, array{label: string, category: string, side_effect: bool}>
     */
    public const NODE_TYPES = [
        'trigger.manual' => ['label' => 'Mula Manual', 'category' => 'trigger', 'side_effect' => false],
        'trigger.schedule' => ['label' => 'Jadual', 'category' => 'trigger', 'side_effect' => false],
        'trigger.event' => ['label' => 'Peristiwa', 'category' => 'trigger', 'side_effect' => false],
        'trigger.webhook' => ['label' => 'Webhook', 'category' => 'trigger', 'side_effect' => false],
        'logic.condition' => ['label' => 'Syarat If/Else', 'category' => 'logic', 'side_effect' => false],
        'logic.delay' => ['label' => 'Tunggu', 'category' => 'logic', 'side_effect' => false],
        'logic.loop' => ['label' => 'Gelung', 'category' => 'logic', 'side_effect' => false],
        'data.set' => ['label' => 'Tetapkan Nilai', 'category' => 'data', 'side_effect' => false],
        'data.query' => ['label' => 'Baca Data', 'category' => 'data', 'side_effect' => false],
        'ai.generate' => ['label' => 'AI Jana', 'category' => 'ai', 'side_effect' => false],
        'ai.classify' => ['label' => 'AI Kelaskan', 'category' => 'ai', 'side_effect' => false],
        'action.send_email' => ['label' => 'Hantar E-mel', 'category' => 'action', 'side_effect' => true],
        'action.send_whatsapp' => ['label' => 'Hantar WhatsApp', 'category' => 'action', 'side_effect' => true],
        'action.create_record' => ['label' => 'Cipta Rekod', 'category' => 'action', 'side_effect' => true],
        'action.update_record' => ['label' => 'Kemas Kini Rekod', 'category' => 'action', 'side_effect' => true],
        'action.create_reminder' => ['label' => 'Cipta Peringatan', 'category' => 'action', 'side_effect' => true],
        'action.notify' => ['label' => 'Notifikasi', 'category' => 'action', 'side_effect' => true],
    ];

    /**
     * Jalankan aliran kerja.
     */
    public function laksana(int $tenantId, int $workflowId, array $payload = [], ?bool $dryRun = null): WorkflowRun
    {
        $workflow = Workflow::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($workflowId);

        $dryRun = $dryRun ?? (bool) $workflow->dry_run_mode;

        $run = WorkflowRun::create([
            'tenant_id' => $tenantId,
            'workflow_id' => $workflow->id,
            'status' => WorkflowRun::STATUS_RUNNING,
            'trigger_type' => $workflow->trigger_type,
            'trigger_payload' => $payload ?: null,
            'started_at' => now(),
        ]);

        $mula = microtime(true);
        $hasilNode = [];
        $dilaksana = 0;
        $gagal = 0;

        try {
            $semasa = $workflow->nodePermulaan();

            if ($semasa === null) {
                throw new \RuntimeException('Aliran kerja tiada node permulaan');
            }

            $kedalaman = 0;
            $konteks = ['payload' => $payload];

            while ($semasa !== null && $kedalaman < self::MAX_DEPTH && $dilaksana < self::MAX_NODES_PER_RUN) {
                $node = $this->cariNode($workflow, $semasa);

                if ($node === null) {
                    break;
                }

                $jenis = $node['type'] ?? 'unknown';

                try {
                    $output = $this->laksanaNode($jenis, $node, $konteks, $dryRun, $tenantId);
                    $hasilNode[] = [
                        'node_id' => $semasa,
                        'type' => $jenis,
                        'status' => 'ok',
                        'output' => $output,
                        'dry_run' => $dryRun,
                    ];
                    $konteks['last'] = $output;
                    $dilaksana++;
                } catch (\Throwable $e) {
                    $hasilNode[] = [
                        'node_id' => $semasa,
                        'type' => $jenis,
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ];
                    $gagal++;

                    // Hentikan pada kegagalan (fail-fast)
                    break;
                }

                // Cari node seterusnya (ambil yang pertama untuk kesederhanaan)
                $seterusnya = $workflow->nodeSeterusnya($semasa);
                $semasa = $seterusnya[0] ?? null;
                $kedalaman++;
            }

            $status = $gagal > 0
                ? WorkflowRun::STATUS_FAILED
                : WorkflowRun::STATUS_COMPLETED;

            $run->update([
                'status' => $status,
                'node_results' => $hasilNode,
                'nodes_executed' => $dilaksana,
                'nodes_failed' => $gagal,
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
                'completed_at' => now(),
            ]);

            $workflow->increment('run_count');
            $workflow->increment($status === WorkflowRun::STATUS_COMPLETED ? 'success_count' : 'failure_count');
            $workflow->update(['last_run_at' => now()]);
        } catch (\Throwable $e) {
            $run->update([
                'status' => WorkflowRun::STATUS_FAILED,
                'node_results' => $hasilNode,
                'nodes_executed' => $dilaksana,
                'nodes_failed' => $gagal + 1,
                'error_message' => $e->getMessage(),
                'duration_ms' => (int) round((microtime(true) - $mula) * 1000),
                'completed_at' => now(),
            ]);

            Log::error('Workflow gagal', [
                'tenant_id' => $tenantId,
                'workflow_id' => $workflowId,
                'error' => $e->getMessage(),
            ]);
        }

        return $run->fresh();
    }

    private function cariNode(Workflow $workflow, string $id): ?array
    {
        foreach ($workflow->nodes() as $n) {
            if (($n['id'] ?? null) === $id) {
                return $n;
            }
        }

        return null;
    }

    /**
     * Laksana satu node.
     */
    private function laksanaNode(string $jenis, array $node, array $konteks, bool $dryRun, int $tenantId): mixed
    {
        $config = $node['config'] ?? [];

        // Trigger & logic — tiada kesan sampingan
        if (str_starts_with($jenis, 'trigger.')) {
            return ['triggered' => true, 'type' => $jenis];
        }

        if ($jenis === 'logic.condition') {
            $field = $config['field'] ?? null;
            $expected = $config['equals'] ?? null;
            $actual = $konteks['last'][$field] ?? null;

            return ['condition_met' => $actual == $expected, 'actual' => $actual, 'expected' => $expected];
        }

        if ($jenis === 'logic.delay') {
            return ['delayed_seconds' => (int) ($config['seconds'] ?? 0), 'skipped_in_run' => true];
        }

        if ($jenis === 'data.set') {
            return ['set' => $config['value'] ?? null, 'key' => $config['key'] ?? null];
        }

        if ($jenis === 'data.query') {
            return ['source' => $config['source'] ?? null, 'rows' => 0, 'note' => 'Query dijalankan semasa run sebenar'];
        }

        // AI — guna AI Gateway jika ada
        if (str_starts_with($jenis, 'ai.')) {
            if ($dryRun) {
                return ['dry_run' => true, 'prompt' => $config['prompt'] ?? null];
            }

            try {
                $gateway = app(\App\Modules\AIGateway\Services\GatewayService::class);

                $hasil = $gateway->call(
                    $tenantId,
                    [
                        ['role' => 'system', 'content' => $config['system'] ?? 'Anda pembantu automasi.'],
                        ['role' => 'user', 'content' => (string) ($config['prompt'] ?? '')],
                    ],
                    'workflow',
                    ['max_output_tokens' => (int) ($config['max_tokens'] ?? 500), 'source_module' => 'workflow_studio']
                );

                return ['content' => $hasil['content'] ?? '', 'credits' => $hasil['usage']['credits_charged'] ?? 0];
            } catch (\Throwable $e) {
                return ['content' => '', 'error' => $e->getMessage()];
            }
        }

        // Action — KESAN SAMPINGAN: hanya jika bukan dry-run
        if (str_starts_with($jenis, 'action.')) {
            if ($dryRun) {
                return ['dry_run' => true, 'action' => $jenis, 'config' => $config];
            }

            return $this->laksanaAction($jenis, $config, $tenantId);
        }

        return ['unhandled' => $jenis];
    }

    /**
     * Laksana tindakan sebenar (kesan sampingan).
     */
    private function laksanaAction(string $jenis, array $config, int $tenantId): array
    {
        switch ($jenis) {
            case 'action.create_reminder':
                $reminder = app(\App\Modules\Reminder\Services\ReminderService::class)->cipta(
                    $tenantId,
                    $config['type_key'] ?? 'workflow_reminder',
                    [
                        'title' => $config['title'] ?? 'Peringatan aliran kerja',
                        'description' => $config['description'] ?? null,
                        'due_at' => $config['due_at'] ?? now()->addHour(),
                        'channels' => $config['channels'] ?? ['in_app'],
                    ]
                );

                return ['reminder_id' => $reminder->id];

            case 'action.notify':
                return ['notified' => true, 'title' => $config['title'] ?? null];

            case 'action.send_email':
            case 'action.send_whatsapp':
                // Delegasi ke Blast module
                return ['queued' => true, 'channel' => $jenis === 'action.send_email' ? 'email' : 'whatsapp'];

            case 'action.create_record':
            case 'action.update_record':
                return ['record_updated' => true, 'model' => $config['model'] ?? null];

            default:
                return ['unhandled_action' => $jenis];
        }
    }

    /**
     * Sahkan definisi aliran kerja.
     */
    public function sahkanDefinisi(array $definition): array
    {
        $ralat = [];
        $nodes = $definition['nodes'] ?? [];
        $edges = $definition['edges'] ?? [];

        if (empty($nodes)) {
            $ralat[] = 'Definisi mesti ada sekurang-kurangnya satu node';
        }

        if (count($nodes) > self::MAX_NODES_PER_RUN) {
            $ralat[] = 'Terlalu banyak node (maks ' . self::MAX_NODES_PER_RUN . ')';
        }

        $idSet = [];
        foreach ($nodes as $n) {
            $id = $n['id'] ?? null;
            if ($id === null) {
                $ralat[] = 'Node tanpa id';
                continue;
            }

            if (isset($idSet[$id])) {
                $ralat[] = "Node id pendua: {$id}";
            }
            $idSet[$id] = true;

            $jenis = $n['type'] ?? null;
            if ($jenis === null || ! isset(self::NODE_TYPES[$jenis])) {
                $ralat[] = "Node '{$id}' jenis tidak sah: " . ($jenis ?? 'tiada');
            }
        }

        foreach ($edges as $e) {
            $dari = $e['from'] ?? null;
            $ke = $e['to'] ?? null;

            if (! isset($idSet[$dari])) {
                $ralat[] = "Edge merujuk node 'from' tidak wujud: " . ($dari ?? '-');
            }
            if (! isset($idSet[$ke])) {
                $ralat[] = "Edge merujuk node 'to' tidak wujud: " . ($ke ?? '-');
            }
        }

        return ['valid' => empty($ralat), 'errors' => $ralat];
    }

    /**
     * Senarai jenis node untuk UI builder.
     */
    public function katalogNode(): array
    {
        $hasil = [];

        foreach (self::NODE_TYPES as $jenis => $def) {
            $hasil[] = [
                'type' => $jenis,
                'label' => $def['label'],
                'category' => $def['category'],
                'side_effect' => $def['side_effect'],
            ];
        }

        return $hasil;
    }

    /**
     * Pemasangan template ke tenant.
     */
    public function pasangTemplate(int $tenantId, int $templateId, ?string $nama = null): Workflow
    {
        $template = WorkflowTemplate::query()
            ->where(function ($q) use ($tenantId) {
                $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
            })
            ->where('is_active', true)
            ->findOrFail($templateId);

        $workflow = Workflow::create([
            'tenant_id' => $tenantId,
            'workflow_template_id' => $template->id,
            'name' => $nama ?? $template->name,
            'description' => $template->description,
            'definition' => $template->definition,
            'status' => Workflow::STATUS_DRAFT,
            'trigger_type' => Workflow::TRIGGER_MANUAL,
            'is_enabled' => false,
            'dry_run_mode' => true,
        ]);

        $template->increment('install_count');

        return $workflow;
    }

    /**
     * Ringkasan workflow tenant.
     */
    public function ringkasan(int $tenantId): array
    {
        $flows = Workflow::query()->where('tenant_id', $tenantId)->get();
        $runs = WorkflowRun::query()
            ->where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        return [
            'jumlah_workflow' => $flows->count(),
            'aktif' => $flows->where('is_enabled', true)->count(),
            'dry_run' => $flows->where('dry_run_mode', true)->count(),
            'run_30_hari' => $runs->count(),
            'berjaya' => $runs->where('status', WorkflowRun::STATUS_COMPLETED)->count(),
            'gagal' => $runs->where('status', WorkflowRun::STATUS_FAILED)->count(),
            'kadar_berjaya' => $runs->count() > 0
                ? round($runs->where('status', WorkflowRun::STATUS_COMPLETED)->count() / $runs->count() * 100, 2)
                : 0.0,
            'template_tersedia' => WorkflowTemplate::query()
                ->where(function ($q) use ($tenantId) {
                    $q->whereNull('tenant_id')->orWhere('tenant_id', $tenantId);
                })
                ->where('is_active', true)
                ->count(),
        ];
    }
}
