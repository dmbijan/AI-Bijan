<?php

namespace App\Modules\AIAgents\Tools;

use App\Models\Item;

/** Ubah harga jual produk. Pagar owner #1 (ubah harga) → sentiasa melalui Peti Kelulusan. */
class ProdukUbahHarga extends AgentTool
{
    public function name(): string { return 'produk.ubah_harga'; }

    public function description(): string { return 'Ubah harga jual satu produk (perlu kelulusan).'; }

    public function rules(): array { return ['sku' => 'required|string|max:40', 'harga_baharu' => 'required|numeric|min:0|max:1000000']; }

    public function permission(): string { return 'inventory.manage'; }

    public function risk(): string { return self::TINGGI; }

    public function summary(array $p): string
    {
        $i = Item::where('sku', $p['sku'])->first();

        return $i ? sprintf('Ubah harga %s (%s) dari RM %.2f kepada RM %.2f', $i->name, $i->sku, $i->sell_price, $p['harga_baharu'])
            : "Ubah harga SKU {$p['sku']} kepada RM ".number_format((float) $p['harga_baharu'], 2);
    }

    public function handle(array $p): array
    {
        $i = Item::where('sku', $p['sku'])->first();
        if (! $i) {
            throw new \RuntimeException("Produk SKU {$p['sku']} tidak dijumpai.");
        }
        $lama = (float) $i->sell_price;
        $i->update(['sell_price' => $p['harga_baharu']]);

        return ['sku' => $i->sku, 'nama' => $i->name, 'harga_lama' => $lama, 'harga_baharu' => (float) $i->fresh()->sell_price];
    }

    public function verify(array $p, array $hasil): bool
    {
        return abs((float) Item::where('sku', $p['sku'])->value('sell_price') - (float) $p['harga_baharu']) < 0.005;
    }
}
