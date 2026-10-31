<?php

namespace App\Modules\AIAgents\Tools;

use App\Models\ApprovalRequest;
use App\Services\AuditLogger;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\Validator;

/**
 * Jalankan alat ejen dengan pagar: kebenaran pengguna, validasi, kelulusan untuk risiko tinggi,
 * pengesahan hasil, dan audit. Pulangan sentiasa jujur: `berjaya` hanya bila hasil disahkan.
 */
class AgentToolRunner
{
    public function __construct(private AgentToolRegistry $registry) {}

    public function jalan(string $agent, string $nama, array $p, ?int $userId): array
    {
        $alat = $this->registry->get($nama);
        if (! $alat) {
            return ['status' => 'tiada_alat', 'berjaya' => false, 'mesej' => "Alat '{$nama}' tiada."];
        }
        if (! app(TenantContext::class)->can($alat->permission())) {
            return ['status' => 'ditolak', 'berjaya' => false, 'mesej' => "Tiada kebenaran {$alat->permission()}."];
        }
        $v = Validator::make($p, $alat->rules());
        if ($v->fails()) {
            return ['status' => 'input_tidak_sah', 'berjaya' => false, 'mesej' => $v->errors()->first(), 'ralat' => $v->errors()->toArray()];
        }
        $p = $v->validated();

        if ($alat->risk() === AgentTool::TINGGI) {
            $a = ApprovalRequest::create([
                'type' => 'ai.tool',
                'payload' => ['agent' => $agent, 'tool' => $nama, 'params' => $p, 'ringkasan' => $alat->summary($p), 'diminta_oleh' => $userId],
                'status' => 'pending',
                'requested_by' => null, // diminta oleh ejen, bukan pengguna → pemilik boleh meluluskan
            ]);
            app(AuditLogger::class)->log('ai.tool.approval_requested', $a, null, ['agent' => $agent, 'tool' => $nama]);

            return ['status' => 'menunggu_kelulusan', 'berjaya' => false, 'approval_id' => $a->id, 'mesej' => 'Menunggu kelulusan: '.$alat->summary($p)];
        }

        return $this->laksana($agent, $alat, $p);
    }

    /** Dipanggil bila permintaan `ai.tool` diluluskan dalam Peti Kelulusan. */
    public function laksanaKelulusan(ApprovalRequest $a): array
    {
        $alat = $this->registry->get($a->payload['tool'] ?? '');
        if (! $alat) {
            return ['status' => 'tiada_alat', 'berjaya' => false];
        }
        if (! app(TenantContext::class)->can($alat->permission())) {
            return ['status' => 'ditolak', 'berjaya' => false, 'mesej' => "Pelulus tiada kebenaran {$alat->permission()}."];
        }

        return $this->laksana($a->payload['agent'] ?? 'ejen', $alat, $a->payload['params'] ?? []);
    }

    private function laksana(string $agent, AgentTool $alat, array $p): array
    {
        try {
            $hasil = $alat->handle($p);
        } catch (\Throwable $e) {
            app(AuditLogger::class)->log('ai.tool.failed', null, null, ['agent' => $agent, 'tool' => $alat->name(), 'error' => mb_substr($e->getMessage(), 0, 250)]);

            return ['status' => 'gagal', 'berjaya' => false, 'mesej' => $e->getMessage()];
        }
        $sah = $alat->verify($p, $hasil);
        app(AuditLogger::class)->log('ai.tool.executed', null, null, ['agent' => $agent, 'tool' => $alat->name(), 'disahkan' => $sah]);

        return $sah
            ? ['status' => 'berjaya', 'berjaya' => true, 'hasil' => $hasil]
            : ['status' => 'tidak_disahkan', 'berjaya' => false, 'hasil' => $hasil, 'mesej' => 'Tindakan dijalankan tetapi hasil tidak dapat disahkan.'];
    }
}
