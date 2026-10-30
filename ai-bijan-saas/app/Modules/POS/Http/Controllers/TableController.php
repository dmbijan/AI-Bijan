<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POS\Models\POSTable;
use App\Modules\POS\Models\POSTableOrder;
use App\Modules\POS\Services\TableOrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Meja QR & pesanan dapur (staf). */
class TableController extends Controller
{
    public function __construct(protected TableOrderService $orders) {}

    public function index()
    {
        return response()->json(POSTable::withCount(['orders as open_orders' => fn ($q) => $q->whereIn('status', POSTableOrder::TERBUKA)])
            ->orderBy('name')->get()->makeVisible('token'));
    }

    public function store(Request $request)
    {
        $d = $request->validate(['name' => 'required|string|max:60']);

        return response()->json(POSTable::create($d)->makeVisible('token'), 201);
    }

    public function update(Request $request, int $id)
    {
        $table = POSTable::findOrFail($id);
        $table->update($request->validate(['name' => 'sometimes|string|max:60', 'is_active' => 'sometimes|boolean']));

        return response()->json($table);
    }

    /** Jana token QR baharu (QR lama terus tak boleh dipakai). */
    public function regenerateToken(int $id)
    {
        $table = POSTable::findOrFail($id);
        $table->update(['token' => POSTable::tokenBaharu()]);

        return response()->json($table);
    }

    public function orders(Request $request)
    {
        $status = $request->input('status') ? explode(',', $request->input('status')) : POSTableOrder::TERBUKA;

        return response()->json(POSTableOrder::with(['items', 'table:id,name'])->whereIn('status', $status)->orderBy('id')->limit(200)->get());
    }

    public function setStatus(Request $request, int $id)
    {
        $d = $request->validate(['status' => ['required', Rule::in(POSTableOrder::STATUS)]]);

        return response()->json($this->orders->setStatus(POSTableOrder::findOrFail($id), $d['status']));
    }

    public function close(Request $request, int $id)
    {
        $d = $request->validate(['sale_id' => 'nullable|integer|exists:pos_sales,id']);

        return response()->json($this->orders->close(POSTableOrder::findOrFail($id), $d['sale_id'] ?? null));
    }
}
