<?php

namespace App\Modules\AIAgents\Tools;

use App\Models\Item;
use App\Models\StockBalance;

class StokRendah extends AgentTool
{
    public function name(): string { return 'stok.rendah'; }

    public function description(): string { return 'Barang stok yang baki semasa sama atau bawah paras pesanan semula.'; }

    public function rules(): array { return ['had' => 'nullable|integer|min:1|max:100']; }

    public function permission(): string { return 'inventory.view'; }

    public function handle(array $p): array
    {
        $baki = StockBalance::query()->selectRaw('item_id, sum(qty) as qty')->groupBy('item_id')->pluck('qty', 'item_id');
        $rows = Item::where('is_stock', true)->where('is_active', true)->get(['id', 'sku', 'name', 'reorder_level'])
            ->map(fn ($i) => ['sku' => $i->sku, 'nama' => $i->name, 'baki' => (float) ($baki[$i->id] ?? 0), 'paras_pesan_semula' => (float) $i->reorder_level])
            ->filter(fn ($r) => $r['baki'] <= $r['paras_pesan_semula'])
            ->sortBy('baki')->take((int) ($p['had'] ?? 20))->values();

        return ['bilangan' => $rows->count(), 'senarai' => $rows->all()];
    }
}
