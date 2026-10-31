<?php

namespace App\Modules\POS\Services;

use App\Models\Warehouse;
use App\Modules\Manufacturing\ManufacturingService;
use App\Modules\POS\Models\POSRegister;
use App\Modules\POS\Models\POSSale;
use Illuminate\Validation\ValidationException;

/**
 * Potong stok untuk jualan POS yang selesai (dokumen Master Inventory §7.1: POS → IMS).
 *
 * Polisi owner (31/10/2026): stok tak cukup TIDAK menyekat jualan — pelanggan dah bayar.
 * Baris itu ditanda `insufficient` untuk disemak dan stok dibetulkan kemudian.
 * Idempoten per baris (`pos-sale-item-{id}`), jadi segerak luar talian berulang tak potong dua kali.
 */
class SaleStockService
{
    public function __construct(private ManufacturingService $mfg) {}

    public function potongStok(POSSale $sale): void
    {
        $gudang = $this->gudang($sale);

        foreach ($sale->items()->get() as $item) {
            if (! $item->product_id || $item->stock_status) {
                continue; // barang bebas (tiada item katalog) atau sudah diproses
            }
            if (! $gudang) {
                $item->forceFill(['stock_status' => 'no_warehouse', 'stock_note' => 'Tiada gudang untuk kaunter ini.'])->save();

                continue;
            }
            try {
                $hasil = $this->mfg->deductForSale($item->product_id, $item->quantity, $gudang, 'pos-sale-item-'.$item->id);
                $item->forceFill([
                    'stock_status' => $hasil['lines'] === [] ? 'not_stock' : 'deducted',
                    'stock_note' => null,
                    'cogs' => $hasil['cogs'] ?? null,
                ])->save();
            } catch (ValidationException $e) {
                $item->forceFill(['stock_status' => 'insufficient', 'stock_note' => mb_substr(collect($e->errors())->flatten()->first() ?? $e->getMessage(), 0, 250)])->save();
            }
        }
    }

    /** Gudang kaunter; jika tiada, gudang aktif pertama tenant (outlet diutamakan). */
    private function gudang(POSSale $sale): ?int
    {
        $wh = POSRegister::whereKey($sale->register_id)->value('warehouse_id');

        return $wh ?: Warehouse::where('is_active', true)->orderByRaw("type = 'outlet' desc")->orderBy('id')->value('id');
    }
}
