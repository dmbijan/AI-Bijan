<?php

namespace App\Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StockBalance;
use App\Models\StockLedgerEntry;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Gudang & lokasi simpanan. */
class WarehouseController extends Controller
{
    public function index()
    {
        return response()->json(Warehouse::orderBy('name')->get());
    }

    public function show(int $id)
    {
        $w = Warehouse::findOrFail($id);

        return response()->json([
            'warehouse' => $w,
            'summary' => [
                'items' => StockBalance::where('warehouse_id', $w->id)->distinct('item_id')->count('item_id'),
                'qty' => (float) StockBalance::where('warehouse_id', $w->id)->sum('qty'),
                'value' => (float) StockBalance::where('warehouse_id', $w->id)->sum('value'),
            ],
        ]);
    }

    public function store(Request $r)
    {
        $d = $this->validasi($r);
        $d['code'] ??= 'WH-'.strtoupper(bin2hex(random_bytes(3)));
        $w = Warehouse::create($d);
        app(AuditLogger::class)->log('inventory.warehouse_created', $w, null, $d);

        return response()->json($w, 201);
    }

    public function update(Request $r, int $id)
    {
        $w = Warehouse::findOrFail($id);
        $sebelum = $w->toArray();
        $d = $this->validasi($r, $w->id);
        $w->fill($d)->save();
        app(AuditLogger::class)->log('inventory.warehouse_updated', $w, $sebelum, $d);

        return response()->json($w);
    }

    /** Gudang tidak boleh dipadam jika masih ada baki atau pergerakan. */
    public function destroy(int $id)
    {
        $w = Warehouse::findOrFail($id);

        abort_if(
            StockBalance::where('warehouse_id', $w->id)->where('qty', '!=', 0)->exists(),
            422,
            'Gudang masih mempunyai baki stok.'
        );
        abort_if(StockLedgerEntry::where('warehouse_id', $w->id)->exists(), 422, 'Gudang ada sejarah pergerakan.');

        $w->delete();
        app(AuditLogger::class)->log('inventory.warehouse_deleted', $w, null, ['id' => $id]);

        return response()->json(['message' => 'Gudang dipadam.']);
    }

    private function validasi(Request $r, ?int $abaikanId = null): array
    {
        return $r->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('warehouses', 'name')->ignore($abaikanId)],
            'code' => ['nullable', 'string', 'max:30', Rule::unique('warehouses', 'code')->ignore($abaikanId)],
            'address' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);
    }
}
