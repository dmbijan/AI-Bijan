<?php

namespace App\Modules\SCM\Services;

use App\Models\Item;
use App\Models\StockBalance;
use App\Models\StockLedgerEntry;
use App\Models\StockMovement;
use App\Modules\Inventory\StockEngine;
use App\Modules\SCM\Models\LandedCost;
use App\Modules\SCM\Models\Supplier;
use App\Services\AuditLogger;
use App\Support\Dec;
use App\Support\NumberSeries;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * SUP-011 ramalan permintaan, SUP-014 pesanan semula, SUP-015 kos pendaratan, SUP-013 kelengkapan maklumat produk.
 * Ramalan guna kaedah ringkas yang boleh diterangkan (purata harian × trend × corak hari dalam minggu), bukan kotak hitam.
 */
class SupplyPlanningService
{
    public const HISTORY_DAYS = 56;

    public const OPEN_PO = ['draft', 'pending_approval', 'approved', 'sent', 'partially_received'];

    // ------------------------------------------------------------------ SUP-011

    /** @return array<int, array> ramalan setiap barang stok yang ada jualan */
    public function forecast(int $days = 30, ?Carbon $today = null, ?array $itemIds = null): array
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $from = $today->copy()->subDays(self::HISTORY_DAYS);
        $rows = StockLedgerEntry::where('source_type', 'sale')->where('qty', '<', 0)
            ->where('posted_at', '>=', $from)->where('posted_at', '<', $today)
            ->when($itemIds, fn ($q) => $q->whereIn('item_id', $itemIds))
            ->selectRaw('item_id, DATE(posted_at) d, SUM(-qty) q')->groupBy('item_id', DB::raw('DATE(posted_at)'))->get()->groupBy('item_id');

        $out = [];
        foreach ($rows as $itemId => $daily) {
            $byDate = $daily->mapWithKeys(fn ($r) => [substr((string) $r->d, 0, 10) => (float) $r->q]);
            $series = [];
            for ($i = self::HISTORY_DAYS; $i >= 1; $i--) {
                $d = $today->copy()->subDays($i);
                $series[] = ['date' => $d, 'q' => $byDate[$d->toDateString()] ?? 0.0];
            }
            $total = array_sum(array_column($series, 'q'));
            $avg = $total / self::HISTORY_DAYS;
            $half = intdiv(self::HISTORY_DAYS, 2);
            $old = array_sum(array_column(array_slice($series, 0, $half), 'q'));
            $new = array_sum(array_column(array_slice($series, $half), 'q'));
            $trend = $old > 0 ? max(0.5, min(2.0, $new / $old)) : 1.0;

            // Corak mingguan: purata hari tertentu ÷ purata semua hari (dilicinkan ke arah 1 supaya data nipis tak melampau).
            $wd = array_fill(0, 7, []);
            foreach ($series as $s) {
                $wd[$s['date']->dayOfWeek][] = $s['q'];
            }
            $factor = [];
            for ($k = 0; $k < 7; $k++) {
                $m = count($wd[$k]) ? array_sum($wd[$k]) / count($wd[$k]) : $avg;
                $factor[$k] = $avg > 0 ? 0.5 + 0.5 * ($m / $avg) : 1.0;
            }

            $base = ($avg + ($new / $half)) / 2; // separuh berat pada 4 minggu terkini
            $f = 0.0;
            $f7 = 0.0;
            for ($i = 0; $i < $days; $i++) {
                $v = $base * $factor[$today->copy()->addDays($i)->dayOfWeek];
                $f += $v;
                $f7 += $i < 7 ? $v : 0;
            }
            $out[$itemId] = [
                'item_id' => (int) $itemId,
                'history_qty' => round($total, 3),
                'avg_daily' => round($avg, 3),
                'trend' => round($trend, 2),
                'forecast_7' => round($f7, 2),
                'forecast' => round($f, 2),
                'days' => $days,
                'busiest_day' => array_search(max($factor), $factor, true),
                'weekly' => array_map(fn ($x) => round($x, 2), $factor),
            ];
        }

        return $out;
    }

    // ------------------------------------------------------------------ SUP-014

    public function suggestions(?Carbon $today = null): array
    {
        $items = Item::where('is_stock', true)->where('is_active', true)->get();
        $fc = $this->forecast(30, $today);
        $onHand = StockBalance::selectRaw('item_id, SUM(qty - reserved) q')->groupBy('item_id')->pluck('q', 'item_id');
        $onOrder = DB::table('purchase_order_lines')->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_lines.purchase_order_id')
            ->where('purchase_orders.tenant_id', app(TenantContext::class)->id())->whereIn('purchase_orders.status', self::OPEN_PO)->whereNotNull('purchase_order_lines.item_id')
            ->selectRaw('purchase_order_lines.item_id, SUM(purchase_order_lines.quantity_ordered - purchase_order_lines.quantity_received) q')
            ->groupBy('purchase_order_lines.item_id')->pluck('q', 'item_id');
        $lastCost = StockLedgerEntry::where('qty', '>', 0)->where('unit_cost', '>', 0)->orderByDesc('id')->get(['item_id', 'unit_cost'])->unique('item_id')->pluck('unit_cost', 'item_id');
        $suppliers = Supplier::pluck('company_name', 'id');

        $out = [];
        foreach ($items as $it) {
            $f = $fc[$it->id] ?? null;
            $daily = $f ? $f['forecast'] / 30 : 0;
            $lead = max(1, (int) $it->lead_time_days);
            $leadDemand = $daily * $lead;
            $rop = max((float) $it->reorder_level, $leadDemand + (float) $it->safety_stock);
            if ($rop <= 0) {
                continue;
            }
            $have = (float) ($onHand[$it->id] ?? 0);
            $coming = (float) ($onOrder[$it->id] ?? 0);
            $projected = $have + $coming;
            if ($projected > $rop) {
                continue;
            }
            $qty = (float) $it->reorder_qty > 0 ? (float) $it->reorder_qty : ($rop + $daily * 30) - $projected;
            $qty = (int) ceil(max($qty, (float) $it->min_order_qty, 1));
            $cost = (float) ($lastCost[$it->id] ?? $it->standard_cost);
            $out[] = [
                'item_id' => $it->id, 'sku' => $it->sku, 'name' => $it->name, 'uom' => $it->uom,
                'on_hand' => round($have, 3), 'on_order' => round($coming, 3), 'reorder_point' => round($rop, 2),
                'avg_daily' => round($daily, 3), 'lead_time_days' => $lead,
                'days_left' => $daily > 0 ? round($have / $daily, 1) : null,
                'suggested_qty' => $qty, 'unit_cost' => round($cost, 2), 'est_value' => round($qty * $cost, 2),
                'supplier_id' => $it->preferred_supplier_id, 'supplier_name' => $it->preferred_supplier_id ? ($suppliers[$it->preferred_supplier_id] ?? null) : null,
                'urgency' => $have <= 0 ? 'out' : ($daily > 0 && $have / $daily < $lead ? 'urgent' : 'soon'),
                'reason' => $f ? 'Jualan purata '.round($daily, 1).'/hari, masa tiba '.$lead.' hari' : 'Di bawah paras pesanan semula',
            ];
        }
        $rank = ['out' => 0, 'urgent' => 1, 'soon' => 2];
        usort($out, fn ($a, $b) => [$rank[$a['urgency']], $a['days_left'] ?? 0] <=> [$rank[$b['urgency']], $b['days_left'] ?? 0]);

        return $out;
    }

    /**
     * Cipta PO DRAF ikut pembekal. PO tidak dihantar: ia melalui aliran hantar → lulus → hantar sedia ada.
     *
     * @param  array<int, array{item_id:int, qty?:int, supplier_id?:int, unit_cost?:float}>  $lines
     */
    public function createDraftPos(array $lines): array
    {
        $sugg = collect($this->suggestions())->keyBy('item_id');
        $items = Item::whereIn('id', array_column($lines, 'item_id'))->get()->keyBy('id');
        $groups = [];
        foreach ($lines as $l) {
            $it = $items[$l['item_id']] ?? null;
            if (! $it) {
                throw ValidationException::withMessages(['lines' => 'Barang tidak ditemui.']);
            }
            $sup = $l['supplier_id'] ?? $it->preferred_supplier_id;
            if (! $sup || ! Supplier::whereKey($sup)->exists()) {
                throw ValidationException::withMessages(['lines' => "Pilih pembekal untuk {$it->sku}."]);
            }
            $qty = (int) ($l['qty'] ?? ($sugg[$it->id]['suggested_qty'] ?? 0));
            if ($qty <= 0) {
                throw ValidationException::withMessages(['lines' => "Kuantiti untuk {$it->sku} mesti lebih dari 0."]);
            }
            $groups[$sup][] = ['item_id' => $it->id, 'item_code' => $it->sku, 'item_name' => $it->name, 'uom' => $it->uom,
                'quantity_ordered' => $qty, 'unit_price' => round((float) ($l['unit_cost'] ?? ($sugg[$it->id]['unit_cost'] ?? $it->standard_cost)), 2)];
        }
        $svc = app(PurchaseOrderService::class);
        $pos = [];
        DB::transaction(function () use ($groups, $svc, &$pos) {
            foreach ($groups as $sup => $ls) {
                $po = $svc->createPurchaseOrder(['tenant_id' => app(TenantContext::class)->id(), 'supplier_id' => $sup, 'lines' => $ls,
                    'internal_notes' => 'Dicadang oleh Pesanan Semula Automatik. Perlu kelulusan sebelum dihantar.']);
                app(AuditLogger::class)->log('scm.reorder_po_drafted', $po, null, ['po' => $po->po_number, 'lines' => count($ls)]);
                $pos[] = ['id' => $po->id, 'po_number' => $po->po_number, 'supplier_id' => $sup, 'total' => $po->total_amount, 'lines' => count($ls)];
            }
        });

        return $pos;
    }

    // ------------------------------------------------------------------ SUP-015

    public function createLandedCost(int $movementId, array $charges, string $method = 'value', ?string $notes = null): LandedCost
    {
        $m = StockMovement::with('lines.item')->findOrFail($movementId);
        if ($m->type !== 'receipt' || $m->status !== 'posted') {
            throw ValidationException::withMessages(['stock_movement_id' => 'Kos pendaratan hanya untuk dokumen terimaan yang sudah dipos.']);
        }
        if (LandedCost::where('stock_movement_id', $m->id)->where('status', 'draft')->exists()) {
            throw ValidationException::withMessages(['stock_movement_id' => 'Sudah ada draf kos pendaratan untuk terimaan ini.']);
        }
        $total = round(array_sum(array_column($charges, 'amount')), 2);
        if ($total <= 0) {
            throw ValidationException::withMessages(['charges' => 'Jumlah caj mesti lebih dari 0.']);
        }

        return DB::transaction(function () use ($m, $charges, $method, $notes, $total) {
            $lc = LandedCost::create(['number' => NumberSeries::next('landed_cost', 'LC'), 'stock_movement_id' => $m->id, 'method' => $method, 'status' => 'draft', 'total' => $total, 'notes' => $notes]);
            foreach ($charges as $c) {
                $lc->charges()->create(['type' => $c['type'], 'description' => $c['description'] ?? null, 'amount' => round($c['amount'], 2)]);
            }
            $basis = [];
            foreach ($m->lines as $l) {
                $val = $l->cost_value !== null ? (float) $l->cost_value : (float) $l->qty * (float) $l->unit_cost;
                $basis[] = [$l, $val, match ($method) {
                    'qty' => (float) $l->qty,
                    'weight' => (float) $l->qty * (float) ($l->item->weight_g ?? 0),
                    default => $val,
                }];
            }
            $sum = array_sum(array_column($basis, 2));
            if ($sum <= 0) {
                throw ValidationException::withMessages(['method' => $method === 'weight' ? 'Barang belum ada berat. Isi berat atau pilih kaedah lain.' : 'Tiada asas untuk agihan.']);
            }
            $baki = $total;
            foreach (array_values($basis) as $i => [$l, $val, $w]) {
                $a = $i === count($basis) - 1 ? round($baki, 6) : round($total * $w / $sum, 6);
                $baki -= $a;
                $lc->lines()->create(['item_id' => $l->item_id, 'warehouse_id' => $m->to_warehouse_id, 'qty' => $l->qty, 'base_value' => $val, 'allocated' => $a]);
            }

            return $lc->load('charges', 'lines');
        });
    }

    /** Pos: bahagian untuk unit yang masih ada → nilai stok; bahagian unit yang sudah keluar → kos jualan (dilaporkan). */
    public function postLandedCost(LandedCost $lc): LandedCost
    {
        if ($lc->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Kos pendaratan sudah dipos.']);
        }
        $engine = app(StockEngine::class);

        return DB::transaction(function () use ($lc, $engine) {
            $cap = 0.0;
            $exp = 0.0;
            foreach ($lc->lines as $l) {
                $have = (float) $engine->onHand($l->item_id, $l->warehouse_id, null, true);
                $share = $l->qty > 0 ? min(1, max(0, $have / (float) $l->qty)) : 0;
                $c = round((float) $l->allocated * $share, 6);
                $in = (float) $engine->revalue($l->item_id, $l->warehouse_id, $c, Dec::n(min($have, (float) $l->qty)), 'landed_cost', $lc->id, 'Kos pendaratan '.$lc->number);
                $e = round((float) $l->allocated - $in, 6);
                $l->update(['capitalised' => $in, 'expensed' => $e]);
                $cap += $in;
                $exp += $e;
            }
            $lc->update(['status' => 'posted', 'capitalised' => round($cap, 2), 'expensed' => round($exp, 2), 'posted_by' => auth()->id(), 'posted_at' => now()]);
            app(AuditLogger::class)->log('scm.landed_cost_posted', $lc, ['status' => 'draft'], ['total' => $lc->total, 'capitalised' => $lc->capitalised, 'expensed' => $lc->expensed]);

            return $lc->fresh(['charges', 'lines']);
        });
    }

    // ------------------------------------------------------------------ SUP-013

    public const PIM_FIELDS = [
        'barcode' => 'Kod bar', 'category' => 'Kategori', 'brand' => 'Jenama', 'description' => 'Keterangan (50+ aksara)',
        'images' => 'Gambar', 'weight_g' => 'Berat', 'sell_price' => 'Harga jual',
    ];

    public function completeness(Item $it): array
    {
        $missing = [];
        foreach (self::PIM_FIELDS as $f => $label) {
            $ok = match ($f) {
                'description' => mb_strlen((string) $it->description) >= 50,
                'images' => ! empty($it->images),
                'sell_price' => (float) $it->sell_price > 0,
                default => ! empty($it->{$f}),
            };
            if (! $ok) {
                $missing[] = $label;
            }
        }

        return ['score' => (int) round((count(self::PIM_FIELDS) - count($missing)) / count(self::PIM_FIELDS) * 100), 'missing' => $missing];
    }

    /** Jana varian daripada gabungan atribut, cth. {"Saiz":["S","M"],"Warna":["Merah"]} → 2 varian. */
    public function generateVariants(Item $parent, array $options): array
    {
        if ($parent->parent_id) {
            throw ValidationException::withMessages(['options' => 'Varian tidak boleh ada varian sendiri.']);
        }
        $combos = [[]];
        foreach ($options as $attr => $vals) {
            $next = [];
            foreach ($combos as $c) {
                foreach (array_unique(array_filter(array_map('trim', (array) $vals))) as $v) {
                    $next[] = $c + [$attr => $v];
                }
            }
            $combos = $next;
        }
        if (count($combos) > 100) {
            throw ValidationException::withMessages(['options' => 'Terlalu banyak gabungan (maks 100).']);
        }
        $made = [];
        foreach ($combos as $c) {
            if (! $c) {
                continue;
            }
            $suffix = strtoupper(implode('-', array_map(fn ($v) => preg_replace('/[^A-Za-z0-9]/', '', mb_substr($v, 0, 6)), $c)));
            $sku = mb_substr($parent->sku.'-'.$suffix, 0, 40);
            if (Item::where('sku', $sku)->exists()) {
                continue;
            }
            $made[] = Item::create([
                'sku' => $sku, 'name' => $parent->name.' ('.implode(', ', $c).')', 'category' => $parent->category, 'uom' => $parent->uom,
                'is_stock' => $parent->is_stock, 'has_batch' => $parent->has_batch, 'has_expiry' => $parent->has_expiry,
                'standard_cost' => $parent->standard_cost, 'sell_price' => $parent->sell_price, 'brand' => $parent->brand, 'description' => $parent->description,
                'images' => $parent->images, 'weight_g' => $parent->weight_g, 'parent_id' => $parent->id, 'attributes' => $c,
                'lead_time_days' => $parent->lead_time_days, 'preferred_supplier_id' => $parent->preferred_supplier_id,
            ]);
        }
        app(AuditLogger::class)->log('inventory.variants_generated', $parent, null, ['count' => count($made)]);

        return $made;
    }
}
