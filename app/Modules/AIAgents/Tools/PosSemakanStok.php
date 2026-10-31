<?php

namespace App\Modules\AIAgents\Tools;

use App\Modules\POS\Models\POSSaleItem;
use App\Support\Tenancy\TenantContext;

class PosSemakanStok extends AgentTool
{
    public function name(): string { return 'pos.semakan_stok'; }

    public function description(): string { return 'Jualan POS yang stoknya tak dapat dipotong (stok tak cukup / tiada gudang).'; }

    public function rules(): array { return []; }

    public function permission(): string { return 'pos.view'; }

    public function handle(array $p): array
    {
        $rows = POSSaleItem::query()->join('pos_sales', 'pos_sales.id', '=', 'pos_sale_items.sale_id')
            ->where('pos_sales.tenant_id', app(TenantContext::class)->id())
            ->whereIn('pos_sale_items.stock_status', ['insufficient', 'no_warehouse'])
            ->orderByDesc('pos_sale_items.id')->limit(50)
            ->get(['pos_sales.sale_no', 'pos_sale_items.product_name', 'pos_sale_items.quantity', 'pos_sale_items.stock_status']);

        return ['bilangan' => $rows->count(), 'senarai' => $rows->toArray()];
    }
}
