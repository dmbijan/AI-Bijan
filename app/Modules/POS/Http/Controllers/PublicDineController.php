<?php

namespace App\Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Tenant;
use App\Modules\POS\Models\POSDineOrder;
use App\Modules\POS\Models\POSDineTable;
use App\Modules\POS\Services\DineOrderService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Menu meja awam: pelanggan imbas QR, tiada log masuk. Token meja = kebenaran.
 * Pesanan masuk sebagai `submitted`; staf terima (jadi jualan draf) atau tolak.
 */
class PublicDineController extends Controller
{
    private function meja(string $token): POSDineTable
    {
        $meja = POSDineTable::withoutGlobalScope('tenant')->where('qr_token', $token)->where('is_active', true)->first();
        abort_unless($meja, 404, 'Meja tidak dijumpai.');
        $tenant = Tenant::find($meja->tenant_id);
        abort_unless($tenant && $tenant->isUsable(), 404);
        app(TenantContext::class)->set($tenant);

        return $meja;
    }

    public function show(string $token)
    {
        $meja = $this->meja($token);

        return response()->json([
            'meja' => $meja->name,
            'syarikat' => app(TenantContext::class)->tenant()->name,
            'menu' => Item::where('is_active', true)->orderBy('category')->orderBy('name')->get(['id', 'name', 'category', 'sell_price']),
        ]);
    }

    public function store(Request $request, string $token, DineOrderService $svc)
    {
        $meja = $this->meja($token);
        $d = $request->validate([
            'customer_name' => 'nullable|string|max:80',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1|max:50',
            'items.*.item_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1|max:99',
            'items.*.notes' => 'nullable|string|max:200',
        ]);

        // Hanya item_id/kuantiti/nota dari pelanggan: harga & nama sentiasa dari katalog.
        $items = array_map(fn ($b) => ['item_id' => $b['item_id'], 'quantity' => $b['quantity'], 'notes' => $b['notes'] ?? null], $d['items']);
        $nota = trim((! empty($d['customer_name']) ? 'Nama: '.$d['customer_name'].'. ' : '').($d['notes'] ?? ''));

        $order = $svc->cipta($meja->tenant_id, $meja, ['items' => $items, 'notes' => $nota ?: null]);

        $akses = Str::random(40);
        $order->forceFill(['access_token_hash' => hash('sha256', $akses), 'access_expires_at' => now()->addHours(12)])->save();

        return response()->json($this->awam($order) + ['token' => $akses], 201);
    }

    public function order(string $token, string $akses)
    {
        $meja = $this->meja($token);
        $order = POSDineOrder::with('items')->where('dine_table_id', $meja->id)
            ->where('access_token_hash', hash('sha256', $akses))->where('access_expires_at', '>', now())->firstOrFail();

        return response()->json($this->awam($order));
    }

    private function awam(POSDineOrder $o): array
    {
        return [
            'no' => $o->order_no,
            'status' => $o->status,
            'jumlah' => $o->subtotal,
            'masa' => $o->submitted_at,
            'items' => $o->items->map(fn ($i) => ['nama' => $i->item_name, 'qty' => (int) $i->quantity, 'harga' => $i->unit_price, 'nota' => $i->notes]),
        ];
    }
}
