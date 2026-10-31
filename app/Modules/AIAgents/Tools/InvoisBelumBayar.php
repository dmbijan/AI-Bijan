<?php

namespace App\Modules\AIAgents\Tools;

use App\Modules\Invoicing\Models\Invoice;

class InvoisBelumBayar extends AgentTool
{
    public function name(): string { return 'invois.lewat_bayar'; }

    public function description(): string { return 'Invois yang sudah lepas tarikh akhir dan masih ada baki.'; }

    public function rules(): array { return ['had' => 'nullable|integer|min:1|max:100']; }

    public function permission(): string { return 'invoicing.view'; }

    public function handle(array $p): array
    {
        $rows = Invoice::overdue()->orderBy('due_date')->limit((int) ($p['had'] ?? 20))
            ->get(['invoice_number', 'customer_name', 'due_date', 'grand_total', 'amount_outstanding']);

        return [
            'bilangan' => $rows->count(),
            'jumlah_tertunggak' => round((float) $rows->sum('amount_outstanding'), 2),
            'senarai' => $rows->map(fn ($i) => [
                'no' => $i->invoice_number, 'pelanggan' => $i->customer_name,
                'tarikh_akhir' => optional($i->due_date)->format('Y-m-d'), 'baki' => (float) $i->amount_outstanding,
                'hari_lewat' => $i->due_date ? (int) $i->due_date->diffInDays(now()) : null,
            ])->all(),
        ];
    }
}
