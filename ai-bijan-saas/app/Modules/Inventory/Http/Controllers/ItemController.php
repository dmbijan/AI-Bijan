<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemUom;
use App\Models\StockBalance;
use App\Models\StockLedgerEntry;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Katalog barang & unit ukuran.
 *
 * Pergerakan stok TIDAK diuruskan di sini — ia mesti melalui MovementService
 * supaya lejar dan lapisan kos kekal konsisten.
 */
class ItemController extends Controller
{
    public function index(Request $r)
    {
        $q = Item::query()
            ->when($r->query('search'), fn ($q, $s) => $q->where(fn ($w) => $w->where('sku', 'like', "%{$s}%")->orWhere('name', 'like', "%{$s}%")->orWhere('barcode', $s)))
            ->when($r->query('category'), fn ($q, $c) => $q->where('category', $c))
            ->when($r->has('is_active'), fn ($q) => $q->where('is_active', $r->boolean('is_active')))
            ->orderBy('sku');

        return response()->json($q->paginate((int) $r->query('per_page', 50)));
    }

    public function show(int $id)
    {
        $item = Item::with('uoms')->findOrFail($id);

        return response()->json([
            'item' => $item,
            'balances' => StockBalance::where('item_id', $item->id)->get(),
        ]);
    }

    public function store(Request $r)
    {
        $d = $this->validasi($r);
        $item = Item::create($d + ['is_active' => $d['is_active'] ?? true]);

        $this->simpanUom($item, $r->input('uoms', []));
        app(AuditLogger::class)->log('inventory.item_created', $item, null, ['sku' => $item->sku]);

        return response()->json($item->load('uoms'), 201);
    }

    public function update(Request $r, int $id)
    {
        $item = Item::findOrFail($id);
        $sebelum = $item->toArray();
        $d = $this->validasi($r, $item->id);

        $item->fill($d)->save();
        if ($r->has('uoms')) {
            $this->simpanUom($item, $r->input('uoms', []), true);
        }

        app(AuditLogger::class)->log('inventory.item_updated', $item, $sebelum, $d);

        return response()->json($item->load('uoms'));
    }

    /** Nyahaktif, bukan padam — lejar mesti kekal merujuk barang. */
    public function destroy(int $id)
    {
        $item = Item::findOrFail($id);

        abort_if(StockLedgerEntry::where('item_id', $item->id)->exists(), 422, 'Barang ada pergerakan stok — nyahaktifkan sahaja.');

        $item->update(['is_active' => false]);
        app(AuditLogger::class)->log('inventory.item_deactivated', $item);

        return response()->json(['message' => 'Barang dinyahaktifkan.']);
    }

    /** Lejar pergerakan barang (append-only). */
    public function ledger(Request $r, int $id)
    {
        Item::findOrFail($id);

        $q = StockLedgerEntry::where('item_id', $id)
            ->when($r->query('warehouse_id'), fn ($q, $w) => $q->where('warehouse_id', $w))
            ->orderByDesc('id');

        return response()->json($q->paginate((int) $r->query('per_page', 100)));
    }

    /** Baki stok semua gudang untuk satu barang. */
    public function stock(int $id)
    {
        $item = Item::findOrFail($id);

        $baki = StockBalance::where('item_id', $item->id)
            ->join('warehouses', 'warehouses.id', '=', 'stock_balances.warehouse_id')
            ->selectRaw('warehouses.name as warehouse, stock_balances.warehouse_id, stock_balances.batch_id, stock_balances.qty, stock_balances.value')
            ->get();

        return response()->json(['item_id' => $item->id, 'sku' => $item->sku, 'balances' => $baki]);
    }

    /** Barang bawah paras pesanan semula. */
    public function reorder()
    {
        $bawah = StockBalance::query()
            ->join('items', 'items.id', '=', 'stock_balances.item_id')
            ->whereNotNull('items.reorder_level')
            ->where('items.reorder_level', '>', 0)
            ->groupBy('items.id', 'items.sku', 'items.name', 'items.reorder_level', 'items.uom')
            ->havingRaw('SUM(stock_balances.qty) <= items.reorder_level')
            ->selectRaw('items.id as item_id, items.sku, items.name, items.uom, items.reorder_level,
                         SUM(stock_balances.qty) as on_hand,
                         (items.reorder_level - SUM(stock_balances.qty)) as suggested_qty')
            ->get();

        return response()->json($bawah);
    }

    private function validasi(Request $r, ?int $abaikanId = null): array
    {
        return $r->validate([
            'sku' => ['required', 'string', 'max:50', Rule::unique('items', 'sku')->ignore($abaikanId)],
            'name' => 'required|string|max:200',
            'barcode' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'uom' => 'required|string|max:15',
            'is_stock' => 'sometimes|boolean',
            'has_batch' => 'sometimes|boolean',
            'has_expiry' => 'sometimes|boolean',
            'reorder_level' => 'nullable|numeric|min:0',
            'safety_stock' => 'nullable|numeric|min:0',
            'standard_cost' => 'nullable|numeric|min:0',
            'sell_price' => 'nullable|numeric|min:0',
            'is_active' => 'sometimes|boolean',
            'uoms' => 'nullable|array',
            'uoms.*.uom' => 'required_with:uoms|string|max:15',
            'uoms.*.factor' => 'required_with:uoms|numeric|gt:0',
        ]);
    }

    private function simpanUom(Item $item, array $senarai, bool $ganti = false): void
    {
        if ($ganti) {
            $item->uoms()->delete();
        }
        foreach ($senarai as $u) {
            ItemUom::create([
                'item_id' => $item->id,
                'uom' => $u['uom'],
                'factor' => $u['factor'],
                'price' => $u['price'] ?? null,
            ]);
        }
    }
}
