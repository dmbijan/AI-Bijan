<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Setting;
use App\Models\StockBalance;
use App\Models\StockLedgerEntry;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Modules\Inventory\MovementService;
use App\Modules\Inventory\StockEngine;
use App\Services\AuditLogger;
use App\Support\Dec;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    private function ctx(): TenantContext
    {
        return app(TenantContext::class);
    }

    private function log(string $a, $m, $b = null, $af = null): void
    {
        app(AuditLogger::class)->log($a, $m, $b, $af);
    }

    // ---------- Gudang
    public function warehouses()
    {
        return response()->json(Warehouse::orderBy('code')->get());
    }

    public function saveWarehouse(Request $r, ?int $id = null)
    {
        $tid = $this->ctx()->id();
        $d = $r->validate(['code' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9_-]+$/i', Rule::unique('warehouses')->where('tenant_id', $tid)->ignore($id)],
            'name' => 'required|string|max:120', 'type' => 'required|in:gudang,outlet,dropship,transit', 'address' => 'nullable|string|max:255', 'is_active' => 'boolean']);
        $w = $id ? Warehouse::findOrFail($id) : new Warehouse;
        $before = $w->exists ? $w->toArray() : null;
        $w->fill($d)->save();
        $this->log($id ? 'inventory.warehouse_updated' : 'inventory.warehouse_created', $w, $before, $d);

        return response()->json($w, $id ? 200 : 201);
    }

    // ---------- Barang
    public function items(Request $r)
    {
        $q = Item::query()->when($r->query('q'), fn ($q, $s) => $q->where(fn ($w) => $w->where('sku', 'like', "%$s%")->orWhere('name', 'like', "%$s%")->orWhere('barcode', $s)))
            ->when($r->query('category'), fn ($q, $c) => $q->where('category', $c))->orderBy('name');

        return response()->json($q->paginate(min((int) $r->query('per_page', 50), 200)));
    }

    public function saveItem(Request $r, ?int $id = null)
    {
        $tid = $this->ctx()->id();
        $d = $r->validate(['sku' => ['required', 'string', 'max:40', Rule::unique('items')->where('tenant_id', $tid)->ignore($id)], 'name' => 'required|string|max:150',
            'barcode' => 'nullable|string|max:64', 'category' => 'nullable|string|max:80', 'uom' => 'required|string|max:15', 'is_stock' => 'boolean', 'has_batch' => 'boolean', 'has_expiry' => 'boolean',
            'reorder_level' => 'nullable|numeric|min:0', 'safety_stock' => 'nullable|numeric|min:0', 'standard_cost' => 'nullable|numeric|min:0', 'sell_price' => 'nullable|numeric|min:0', 'is_active' => 'boolean',
            'uoms' => 'nullable|array', 'uoms.*.uom' => 'required|string|max:15', 'uoms.*.factor' => 'required|numeric|gt:0']);
        if (! empty($d['has_expiry'])) {
            $d['has_batch'] = true; // tarikh luput dijejak melalui batch
        }
        $item = $id ? Item::findOrFail($id) : new Item;
        if ($item->exists && StockLedgerEntry::where('item_id', $item->id)->exists()) {
            foreach (['uom', 'has_batch', 'is_stock'] as $k) {
                abort_if(array_key_exists($k, $d) && $d[$k] != $item->$k, 422, "Medan $k tidak boleh diubah selepas ada transaksi stok.");
            }
        }
        $before = $item->exists ? $item->toArray() : null;
        DB::transaction(function () use ($item, $d) {
            $item->fill(collect($d)->except('uoms')->all())->save();
            if (isset($d['uoms'])) {
                $item->uoms()->delete();
                foreach ($d['uoms'] as $u) {
                    $item->uoms()->create(['uom' => $u['uom'], 'factor' => Dec::n($u['factor'])]);
                }
            }
        });
        $this->log($id ? 'inventory.item_updated' : 'inventory.item_created', $item, $before, collect($d)->except('uoms')->all());

        return response()->json($item->load('uoms'), $id ? 200 : 201);
    }

    // ---------- Baki & lejar
    public function balances(Request $r)
    {
        $q = StockBalance::query()->join('items', 'items.id', '=', 'stock_balances.item_id')->join('warehouses', 'warehouses.id', '=', 'stock_balances.warehouse_id')
            ->leftJoin('batches', 'batches.id', '=', 'stock_balances.batch_id')->where('stock_balances.qty', '!=', 0)
            ->when($r->query('item_id'), fn ($q, $i) => $q->where('stock_balances.item_id', $i))->when($r->query('warehouse_id'), fn ($q, $w) => $q->where('stock_balances.warehouse_id', $w))
            ->select('stock_balances.item_id', 'items.sku', 'items.name', 'items.uom', 'stock_balances.warehouse_id', 'warehouses.code as warehouse', 'batches.batch_no', 'batches.expires_on', 'stock_balances.qty', 'stock_balances.value')
            ->orderBy('items.name');

        return response()->json($q->get());
    }

    public function ledger(Request $r, int $itemId)
    {
        Item::findOrFail($itemId);

        return response()->json(StockLedgerEntry::where('item_id', $itemId)->when($r->query('warehouse_id'), fn ($q, $w) => $q->where('warehouse_id', $w))->orderByDesc('id')->paginate(100));
    }

    // ---------- Pergerakan
    public function movements(Request $r)
    {
        return response()->json(StockMovement::with('lines')->when($r->query('type'), fn ($q, $t) => $q->where('type', $t))->when($r->query('status'), fn ($q, $s) => $q->where('status', $s))->orderByDesc('id')->paginate(50));
    }

    public function createMovement(Request $r, MovementService $svc)
    {
        $tid = $this->ctx()->id();
        $wh = Rule::exists('warehouses', 'id')->where('tenant_id', $tid);
        $d = $r->validate(['type' => 'required|in:receipt,issue,transfer,adjustment,count', 'from_warehouse_id' => ['nullable', 'integer', $wh], 'to_warehouse_id' => ['nullable', 'integer', $wh],
            'reason' => 'nullable|string|max:255', 'reference' => 'nullable|string|max:60', 'freight_in' => 'nullable|numeric|min:0',
            'lines' => 'required|array|min:1|max:500', 'lines.*.item_id' => ['required', 'integer', Rule::exists('items', 'id')->where('tenant_id', $tid)], 'lines.*.qty' => 'required|numeric',
            'lines.*.uom' => 'nullable|string|max:15', 'lines.*.unit_cost' => 'nullable|numeric|min:0', 'lines.*.batch_no' => 'nullable|string|max:40', 'lines.*.expires_on' => 'nullable|date']);
        $perlu = ['receipt' => ['to_warehouse_id'], 'issue' => ['from_warehouse_id'], 'transfer' => ['from_warehouse_id', 'to_warehouse_id'], 'adjustment' => ['to_warehouse_id'], 'count' => ['to_warehouse_id']][$d['type']];
        foreach ($perlu as $k) {
            abort_if(empty($d[$k]), 422, "Medan $k diperlukan untuk {$d['type']}.");
        }
        foreach ($d['lines'] as $i => $l) {
            // Tukar unit (cth kotak → unit asas) ikut faktor barang.
            if (! empty($l['uom'])) {
                $item = Item::with('uoms')->find($l['item_id']);
                if ($l['uom'] !== $item->uom) {
                    $f = $item->uoms->firstWhere('uom', $l['uom']) ?? abort(422, "Unit {$l['uom']} tidak ditetapkan untuk {$item->sku}.");
                    $d['lines'][$i]['qty'] = Dec::mul($l['qty'], $f->factor);
                    if (isset($l['unit_cost'])) {
                        $d['lines'][$i]['unit_cost'] = Dec::div($l['unit_cost'], $f->factor);
                    }
                }
            }
            $q = $d['lines'][$i]['qty'];
            $sah = match ($d['type']) {
                'count' => Dec::cmp($q, 0) >= 0,          // kiraan fizikal boleh 0
                'adjustment' => Dec::cmp($q, 0) !== 0,    // bertanda: + masuk, − keluar
                default => Dec::cmp($q, 0) > 0,
            };
            abort_unless($sah, 422, 'Kuantiti tidak sah untuk jenis '.$d['type'].'.');
        }

        return response()->json($svc->create($d)->load('lines'), 201);
    }

    /** Pos. Pelarasan & kira stok perlu kebenaran lulus; pencipta tidak boleh lulus sendiri (kecuali pemilik). */
    public function post(int $id, MovementService $svc)
    {
        $m = StockMovement::findOrFail($id);
        if (in_array($m->type, ['adjustment', 'count'], true)) {
            abort_unless($this->ctx()->can('inventory.approve'), 403, 'Pelarasan stok perlu kelulusan pengurus.');
            abort_if($m->created_by === auth()->id() && ! $this->ctx()->can('roles.manage'), 403, 'Pelarasan tidak boleh diluluskan oleh pencipta sendiri.');
        }

        return response()->json($svc->post($m));
    }

    public function receive(int $id, MovementService $svc)
    {
        return response()->json($svc->receiveTransfer(StockMovement::findOrFail($id)));
    }

    public function cancel(int $id, MovementService $svc)
    {
        $svc->cancel(StockMovement::findOrFail($id));

        return response()->json(['ok' => true]);
    }

    // ---------- Laporan
    public function reports(Request $r, string $report, StockEngine $engine)
    {
        return response()->json(match ($report) {
            'valuation' => ['method' => $engine->method(), 'total' => Dec::round(StockBalance::sum('value')),
                'by_warehouse' => StockBalance::join('warehouses', 'warehouses.id', '=', 'stock_balances.warehouse_id')->groupBy('warehouses.code')->selectRaw('warehouses.code, SUM(stock_balances.value) as value, SUM(stock_balances.qty) as qty')->get()],
            'low-stock' => Item::where('is_stock', true)->where('is_active', true)->get()->map(fn ($i) => ['id' => $i->id, 'sku' => $i->sku, 'name' => $i->name, 'qty' => Dec::n(StockBalance::where('item_id', $i->id)->sum('qty')), 'reorder_level' => $i->reorder_level, 'safety_stock' => $i->safety_stock])
                ->filter(fn ($x) => Dec::cmp($x['qty'], $x['reorder_level']) <= 0 && Dec::cmp($x['reorder_level'], 0) > 0)->values(),
            'expiring' => StockBalance::join('batches', 'batches.id', '=', 'stock_balances.batch_id')->join('items', 'items.id', '=', 'stock_balances.item_id')->where('stock_balances.qty', '>', 0)
                ->whereNotNull('batches.expires_on')->where('batches.expires_on', '<=', now()->addDays((int) $r->query('days', 30))->toDateString())
                ->orderBy('batches.expires_on')->get(['items.sku', 'items.name', 'batches.batch_no', 'batches.expires_on', 'stock_balances.qty', 'stock_balances.value', 'stock_balances.warehouse_id']),
            'non-moving' => Item::where('is_stock', true)->get()->filter(function ($i) use ($r) {
                $ada = Dec::cmp(StockBalance::where('item_id', $i->id)->sum('qty'), 0) > 0;
                $keluar = StockLedgerEntry::where('item_id', $i->id)->where('qty', '<', 0)->where('posted_at', '>=', now()->subDays((int) $r->query('days', 90)))->exists();

                return $ada && ! $keluar;
            })->map(fn ($i) => ['sku' => $i->sku, 'name' => $i->name])->values(),
            default => abort(404),
        });
    }

    /** Kaedah kos hanya boleh ditukar sebelum ada sebarang transaksi stok (elak ubah sejarah secara senyap). */
    public function setValuation(Request $r)
    {
        $d = $r->validate(['method' => 'required|in:fifo,wac']);
        abort_if(StockLedgerEntry::exists(), 422, 'Kaedah kos tidak boleh ditukar selepas ada transaksi stok. Rujuk akauntan.');
        $s = Setting::firstOrNew(['key' => 'inventory.valuation']);
        $before = ['method' => $s->value];
        $s->value = $d['method'];
        $s->save();
        $this->log('inventory.valuation_set', $s, $before, $d);

        return response()->json($d);
    }
}
