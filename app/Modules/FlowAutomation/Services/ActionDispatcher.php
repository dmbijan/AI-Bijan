<?php

namespace App\Modules\FlowAutomation\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Action Dispatcher (BAB 28.1)
 *
 * Memetakan jenis tindakan workflow kepada handler sebenar.
 */
class ActionDispatcher
{
    /**
     * Dispatch satu tindakan
     */
    public function dispatch(string $actionType, array $config, array $payload): array
    {
        $handler = $this->resolveHandler($actionType);

        if (!$handler) {
            return [
                'status' => 'failed',
                'error' => "Tiada handler untuk jenis tindakan: {$actionType}",
            ];
        }

        return $handler($config, $payload);
    }

    /**
     * Dapatkan handler untuk jenis tindakan
     */
    protected function resolveHandler(string $actionType): ?callable
    {
        return match ($actionType) {
            'internal_api' => fn ($c, $p) => $this->handleInternalApi($c, $p),
            'connector_api' => fn ($c, $p) => $this->handleConnectorApi($c, $p),
            'ai_task' => fn ($c, $p) => $this->handleAiTask($c, $p),
            'create_task' => fn ($c, $p) => $this->handleCreateTask($c, $p),
            'send_notification' => fn ($c, $p) => $this->handleSendNotification($c, $p),
            'update_record' => fn ($c, $p) => $this->handleUpdateRecord($c, $p),
            'request_approval' => fn ($c, $p) => $this->handleRequestApproval($c, $p),
            'webhook_out' => fn ($c, $p) => $this->handleWebhookOut($c, $p),
            default => null,
        };
    }

    // ===== Handlers =====

    /**
     * Panggilan API dalaman
     */
    protected function handleInternalApi(array $config, array $payload): array
    {
        $route = $config['route'] ?? null;

        if (!$route) {
            return ['status' => 'failed', 'error' => 'Route tidak ditetapkan.'];
        }

        // Placeholder — implementasi sebenar akan dispatch internal request
        return [
            'status' => 'completed',
            'note' => "Panggilan API dalaman ke '{$route}' (placeholder).",
            'requested_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Panggilan connector luaran
     */
    protected function handleConnectorApi(array $config, array $payload): array
    {
        $url = $config['url'] ?? null;
        $method = strtoupper($config['method'] ?? 'POST');

        if (!$url) {
            return ['status' => 'failed', 'error' => 'URL connector tidak ditetapkan.'];
        }

        try {
            $request = Http::timeout($config['timeout'] ?? 30)
                ->withHeaders($config['headers'] ?? []);

            $response = match ($method) {
                'GET' => $request->get($url, $config['query'] ?? []),
                'PUT' => $request->put($url, $payload),
                'DELETE' => $request->delete($url, $payload),
                default => $request->post($url, $config['body'] ?? $payload),
            };

            $success = $response->successful();

            return [
                'status' => $success ? 'completed' : 'failed',
                'http_status' => $response->status(),
                'response' => $success ? $response->json() : null,
                'error' => $success ? null : "HTTP {$response->status()}",
            ];
        } catch (\Throwable $e) {
            Log::error('Connector API call failed', [
                'url' => $url,
                'method' => $method,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Tugas AI
     */
    protected function handleAiTask(array $config, array $payload): array
    {
        $agentKey = $config['agent_key'] ?? null;
        $objective = $config['objective'] ?? 'Tugas automasi';

        if (!$agentKey) {
            return ['status' => 'failed', 'error' => 'agent_key tidak ditetapkan.'];
        }

        // Placeholder — akan sambung ke modul AIAgents bila tersedia
        return [
            'status' => 'completed',
            'note' => "Tugas AI untuk ejen '{$agentKey}' (placeholder).",
            'objective' => $objective,
        ];
    }

    /**
     * Cipta tugasan
     */
    protected function handleCreateTask(array $config, array $payload): array
    {
        $table = $config['table'] ?? 'tasks';

        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return ['status' => 'failed', 'error' => "Jadual '{$table}' tidak wujud."];
        }

        try {
            $data = $this->interpolate($config['data'] ?? [], $payload);
            $data['tenant_id'] = $data['tenant_id'] ?? ($payload['tenant_id'] ?? null);

            $id = DB::table($table)->insertGetId($data);

            return ['status' => 'completed', 'record_id' => $id];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * Hantar notifikasi
     */
    protected function handleSendNotification(array $config, array $payload): array
    {
        $channel = $config['channel'] ?? 'in_app';
        $recipient = $config['recipient'] ?? null;
        $message = $this->interpolateString($config['message'] ?? '', $payload);

        if (!$recipient) {
            return ['status' => 'failed', 'error' => 'Penerima tidak ditetapkan.'];
        }

        // Placeholder — akan sambung ke modul Notifications bila tersedia
        Log::info('Workflow notification', [
            'channel' => $channel,
            'recipient' => $recipient,
            'message' => $message,
        ]);

        return [
            'status' => 'completed',
            'channel' => $channel,
            'recipient' => $recipient,
            'note' => 'Notifikasi direkod (placeholder).',
        ];
    }

    /**
     * Kemas kini rekod
     */
    protected function handleUpdateRecord(array $config, array $payload): array
    {
        $table = $config['table'] ?? null;
        $key = $config['key'] ?? 'id';
        $keyValue = $this->interpolateValue($config['key_value'] ?? null, $payload);

        if (!$table || !$keyValue) {
            return ['status' => 'failed', 'error' => 'table/key_value tidak ditetapkan.'];
        }

        if (!DB::getSchemaBuilder()->hasTable($table)) {
            return ['status' => 'failed', 'error' => "Jadual '{$table}' tidak wujud."];
        }

        try {
            $data = $this->interpolate($config['data'] ?? [], $payload);
            $affected = DB::table($table)->where($key, $keyValue)->update($data);

            return ['status' => 'completed', 'affected_rows' => $affected];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    /**
     * Mohon kelulusan
     */
    protected function handleRequestApproval(array $config, array $payload): array
    {
        $approverRole = $config['approver_role'] ?? 'tenant_owner';

        return [
            'status' => 'awaiting_approval',
            'approver_role' => $approverRole,
            'reason' => $config['reason'] ?? 'Kelulusan diperlukan untuk tindakan ini.',
        ];
    }

    /**
     * Hantar webhook keluar
     */
    protected function handleWebhookOut(array $config, array $payload): array
    {
        $url = $config['url'] ?? null;

        if (!$url) {
            return ['status' => 'failed', 'error' => 'URL webhook tidak ditetapkan.'];
        }

        try {
            $secret = $config['secret'] ?? null;
            $body = $config['body'] ?? $payload;
            $headers = $config['headers'] ?? [];

            if ($secret) {
                $headers['X-Signature'] = hash_hmac('sha256', json_encode($body), $secret);
            }
            $headers['X-Timestamp'] = now()->timestamp;

            $response = Http::timeout(15)->withHeaders($headers)->post($url, $body);

            return [
                'status' => $response->successful() ? 'completed' : 'failed',
                'http_status' => $response->status(),
                'error' => $response->successful() ? null : "HTTP {$response->status()}",
            ];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'error' => $e->getMessage()];
        }
    }

    // ===== Interpolation Helpers =====

    /**
     * Ganti placeholder {{key}} dalam array dengan nilai payload
     */
    protected function interpolate(array $data, array $payload): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $result[$key] = is_array($value)
                ? $this->interpolate($value, $payload)
                : $this->interpolateValue($value, $payload);
        }
        return $result;
    }

    protected function interpolateValue($value, array $payload)
    {
        if (is_string($value) && preg_match('/^\{\{(.+)\}\}$/', $value, $matches)) {
            return data_get($payload, trim($matches[1]), $value);
        }
        return is_string($value) ? $this->interpolateString($value, $payload) : $value;
    }

    protected function interpolateString(string $template, array $payload): string
    {
        return preg_replace_callback('/\{\{\s*([^}]+)\s*\}\}/', function ($matches) use ($payload) {
            $value = data_get($payload, trim($matches[1]));
            return is_scalar($value) ? (string) $value : $matches[0];
        }, $template);
    }
}
