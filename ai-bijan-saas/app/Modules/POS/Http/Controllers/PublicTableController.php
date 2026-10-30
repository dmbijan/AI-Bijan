<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Tenant;
use App\Modules\POS\Models\POSTable;
use App\Modules\POS\Models\POSTableOrder;
use App\Modules\POS\Services\TableOrderService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;

/** Menu meja awam: pelanggan imbas QR, tiada log masuk. Token meja = kebenaran. */
class PublicTableController extends Controller
{
    private function meja(string $token): POSTable
    {
        $table = POSTable::withoutGlobalScope('tenant')->where('token', $token)->where('is_active', true)->first();
        abort_unless($table, 404, 'Meja tidak dijumpai.');
        $tenant = Tenant::find($table->tenant_id);
        abort_unless($tenant && $tenant->isUsable(), 404);
        app(TenantContext::class)->set($tenant);

        return $table;
    }

    public function show(string $token)
    {
        $table = $this->meja($token);

        return response()->json([
            'meja' => $table->name,
            'syarikat' => app(TenantContext::class)->tenant()?->name,
            'menu' => Item::where('is_active', true)->orderBy('category')->orderBy('name')
                ->get(['id', 'name', 'category', 'sell_price']),
        ]);
    }

    public function store(Request $request, string $token, TableOrderService $svc)
    {
        $table = $this->meja($token);
        $d = $request->validate([
            'customer_name' => 'nullable|string|max:80',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1|max:50',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.note' => 'nullable|string|max:200',
        ]);

        $order = $svc->place($table, $d);

        return response()->json($this->awam($order), 201);
    }

    public function order(string $token, string $publicId)
    {
        $table = $this->meja($token);

        return response()->json($this->awam(POSTableOrder::with('items')->where('table_id', $table->id)->where('public_id', $publicId)->firstOrFail()));
    }

    private function awam(POSTableOrder $o): array
    {
        return [
            'id' => $o->public_id,
            'status' => $o->status,
            'jumlah' => $o->total_amount,
            'masa' => $o->created_at,
            'items' => $o->items->map(fn ($i) => ['nama' => $i->name, 'qty' => (float) $i->quantity, 'harga' => $i->unit_price, 'nota' => $i->note]),
        ];
    }
}
