<?php

namespace App\Modules\AIAgents\Tools;

use App\Modules\POS\Services\SaleService;
use App\Support\Tenancy\TenantContext;

class PosRingkasanHari extends AgentTool
{
    public function name(): string { return 'pos.ringkasan_hari'; }

    public function description(): string { return 'Ringkasan jualan POS untuk satu tarikh (lalai: hari ini).'; }

    public function rules(): array { return ['tarikh' => 'nullable|date']; }

    public function permission(): string { return 'pos.view'; }

    public function handle(array $p): array
    {
        $hari = ($p['tarikh'] ?? null) ? \Illuminate\Support\Carbon::parse($p['tarikh'])->format('Y-m-d') : now()->format('Y-m-d');
        $r = app(SaleService::class)->getSummary((int) app(TenantContext::class)->id(), $hari, $hari);

        return [
            'tarikh' => $hari,
            'bilangan_jualan' => $r['total_sales'],
            'jumlah' => round((float) $r['total_revenue'], 2),
            'purata' => round((float) $r['avg_transaction'], 2),
            'ikut_kaedah' => collect($r['by_payment_method'])->map(fn ($v) => round((float) $v, 2))->all(),
        ];
    }
}
