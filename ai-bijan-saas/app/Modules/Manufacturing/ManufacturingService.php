<?php

namespace App\Modules\Manufacturing;

use App\Models\Bom;
use App\Models\IdempotencyKey;
use App\Models\Item;
use App\Models\WorkOrder;
use App\Modules\Inventory\StockEngine;
use App\Services\AuditLogger;
use App\Support\Dec;
use App\Support\NumberSeries;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ManufacturingService
{
    public function __construct(private StockEngine $engine) {}

    /** BOM baru = versi baru (BOM lama tidak diubah supaya sejarah kos kekal). */
    public function createBom(array $d): Bom
    {
        return DB::transaction(function () use ($d) {
            $item = Item::findOrFail($d['item_id']);
            foreach ($d['lines'] as $l) {
                if ((int) $l['item_id'] === $item->id) {
                    throw ValidationException::withMessages(['lines' => 'Barang tidak boleh menjadi bahan kepada dirinya sendiri.']);
                }
                $this->semakKitaran($item->id, (int) $l['item_id']);
            }
            $versi = (int) Bom::where('item_id', $item->id)->max('version') + 1;
            $default = $d['is_default'] ?? ! Bom::where('item_id', $item->id)->where('is_default', true)->exists();
            if ($default) {
                Bom::where('item_id', $item->id)->update(['is_default' => false]);
            }
            $bom = Bom::create(['item_id' => $item->id, 'version' => $versi, 'type' => $d['type'], 'quantity' => Dec::n($d['quantity'] ?? 1), 'labour_cost' => Dec::n($d['labour_cost'] ?? 0),
                'overhead_cost' => Dec::n($d['overhead_cost'] ?? 0), 'is_active' => true, 'is_default' => $default, 'notes' => $d['notes'] ?? null, 'created_by' => auth()->id()]);
            foreach ($d['lines'] as $l) {
                $bom->lines()->create(['item_id' => $l['item_id'], 'qty' => Dec::n($l['qty']), 'wastage_pct' => Dec::n($l['wastage_pct'] ?? 0)]);
            }
            app(AuditLogger::class)->log('manufacturing.bom_created', $bom, null, ['item' => $item->sku, 'version' => $versi, 'type' => $bom->type]);

            return $bom->load('lines');
        });
    }

    /** Elak kitaran (A guna B, B guna A). */
    private function semakKitaran(int $produk, int $bahan, int $dalam = 0): void
    {
        if ($dalam > 10) {
            throw ValidationException::withMessages(['lines' => 'BOM terlalu bersarang.']);
        }
        $bom = Bom::where('item_id', $bahan)->where('is_default', true)->where('is_active', true)->with('lines')->first();
        foreach ($bom?->lines ?? [] as $l) {
            if ($l->item_id === $produk) {
                throw ValidationException::withMessages(['lines' => 'BOM membentuk kitaran.']);
            }
            $this->semakKitaran($produk, $l->item_id, $dalam + 1);
        }
    }

    /** Keperluan bahan bagi $qty produk (termasuk pembaziran). */
    public function explode(Bom $bom, $qty): array
    {
        $skala = Dec::div($qty, $bom->quantity);

        return $bom->lines->map(fn ($l) => ['item_id' => $l->item_id, 'qty' => Dec::mul(Dec::mul($l->qty, $skala), Dec::add(1, Dec::div($l->wastage_pct, 100)))])->all();
    }

    /** Kos standard seunit = (bahan pada kos purata semasa + buruh + overhead) ÷ hasil. */
    public function standardCost(Bom $bom): array
    {
        $bahan = '0';
        $pecah = [];
        foreach ($bom->lines()->with('item')->get() as $l) {
            $q = Dec::mul($l->qty, Dec::add(1, Dec::div($l->wastage_pct, 100)));
            $kos = Dec::mul($q, $this->engine->averageCost($l->item_id));
            $bahan = Dec::add($bahan, $kos);
            $pecah[] = ['sku' => $l->item->sku, 'name' => $l->item->name, 'qty' => $q, 'cost' => Dec::round($kos, 4)];
        }
        $jumlah = Dec::add($bahan, $bom->labour_cost, $bom->overhead_cost);

        return ['material' => Dec::round($bahan, 4), 'labour' => Dec::round($bom->labour_cost, 4), 'overhead' => Dec::round($bom->overhead_cost, 4),
            'total' => Dec::round($jumlah, 4), 'unit' => Dec::round(Dec::div($jumlah, $bom->quantity), 4), 'lines' => $pecah];
    }

    public function createWorkOrder(array $d): WorkOrder
    {
        return DB::transaction(function () use ($d) {
            $bom = Bom::with('lines')->findOrFail($d['bom_id']);
            if ($bom->type !== 'manufacture' || ! $bom->is_active) {
                throw ValidationException::withMessages(['bom_id' => 'BOM mesti jenis pengeluaran dan aktif.']);
            }
            $wo = WorkOrder::create(['number' => NumberSeries::next('work_order', 'WO'), 'bom_id' => $bom->id, 'item_id' => $bom->item_id, 'qty_planned' => Dec::n($d['qty']),
                'source_warehouse_id' => $d['source_warehouse_id'], 'target_warehouse_id' => $d['target_warehouse_id'], 'status' => 'draft', 'created_by' => auth()->id(),
                'standard_unit_cost' => $this->standardCost($bom)['unit']]);
            foreach ($this->explode($bom, $d['qty']) as $m) {
                $wo->materials()->create(['item_id' => $m['item_id'], 'qty_required' => $m['qty']]);
            }
            app(AuditLogger::class)->log('manufacturing.wo_created', $wo, null, ['number' => $wo->number, 'qty' => $wo->qty_planned]);

            return $wo->load('materials');
        });
    }

    /**
     * Siapkan work order: tolak bahan (guna sebenar jika diberi), masukkan barang siap pada kos sebenar.
     * Kos seunit = (bahan + buruh×skala + overhead×skala) ÷ kuantiti baik. Reject/scrap diserap oleh unit baik.
     */
    public function complete(WorkOrder $wo, $qtyGood, $qtyReject = 0, array $consumed = []): WorkOrder
    {
        return DB::transaction(function () use ($wo, $qtyGood, $qtyReject, $consumed) {
            $wo = WorkOrder::lockForUpdate()->with('materials', 'bom')->find($wo->id);
            if (! in_array($wo->status, ['draft', 'released'], true)) {
                throw ValidationException::withMessages(['status' => 'Work order sudah selesai atau dibatalkan.']);
            }
            if (Dec::cmp($qtyGood, 0) <= 0) {
                throw ValidationException::withMessages(['qty_good' => 'Kuantiti baik mesti lebih dari 0.']);
            }
            $bahan = '0';
            foreach ($wo->materials as $m) {
                $guna = isset($consumed[$m->item_id]) ? Dec::n($consumed[$m->item_id]) : Dec::n($m->qty_required);
                $kos = Dec::cmp($guna, 0) > 0 ? $this->engine->issue($m->item_id, $wo->source_warehouse_id, null, $guna, 'work_order', $wo->id, 'Bahan '.$wo->number)[0] : '0';
                $m->update(['qty_consumed' => $guna, 'cost' => $kos]);
                $bahan = Dec::add($bahan, $kos);
            }
            $skala = Dec::div($wo->qty_planned, $wo->bom->quantity);
            $buruh = Dec::mul($wo->bom->labour_cost, $skala);
            $overhead = Dec::mul($wo->bom->overhead_cost, $skala);
            $unit = Dec::div(Dec::add($bahan, $buruh, $overhead), $qtyGood);
            $this->engine->receive($wo->item_id, $wo->target_warehouse_id, null, $qtyGood, $unit, 'work_order', $wo->id, 'Hasil '.$wo->number);
            $wo->update(['status' => 'completed', 'qty_good' => Dec::n($qtyGood), 'qty_reject' => Dec::n($qtyReject), 'material_cost' => $bahan, 'labour_cost' => $buruh,
                'overhead_cost' => $overhead, 'unit_cost' => $unit, 'variance' => Dec::mul(Dec::sub($unit, $wo->standard_unit_cost), $qtyGood), 'completed_at' => now()]);
            app(AuditLogger::class)->log('manufacturing.wo_completed', $wo, null, ['good' => $qtyGood, 'reject' => $qtyReject, 'unit_cost' => Dec::round($unit, 4)]);

            return $wo->fresh('materials');
        });
    }

    /**
     * Tolak stok untuk satu jualan (POS / kedai online). Idempoten ikut $key: panggilan berulang tidak tolak dua kali.
     * Barang dengan BOM kit lalai → tolak bahan ikut resipi; barang stok biasa → tolak barang itu; bukan stok → tiada.
     * Pulangkan COGS & butiran.
     */
    public function deductForSale(int $itemId, $qty, int $warehouseId, string $key): array
    {
        $ada = IdempotencyKey::where(['scope' => 'sale_deduct', 'key' => $key])->first();
        if ($ada) {
            return $ada->result + ['replayed' => true];
        }
        try {
            return DB::transaction(function () use ($itemId, $qty, $warehouseId, $key) {
                $rekod = IdempotencyKey::create(['scope' => 'sale_deduct', 'key' => $key]); // unik → panggilan serentak kedua gagal di sini
                $item = Item::findOrFail($itemId);
                $kit = Bom::where(['item_id' => $item->id, 'type' => 'kit', 'is_default' => true, 'is_active' => true])->with('lines')->first();
                $baris = [];
                $cogs = '0';
                if ($kit) {
                    foreach ($this->explode($kit, $qty) as $m) {
                        $k = $this->engine->issue($m['item_id'], $warehouseId, null, $m['qty'], 'sale', $rekod->id, 'Jualan '.$item->sku)[0];
                        $baris[] = ['item_id' => $m['item_id'], 'qty' => $m['qty'], 'cost' => $k];
                        $cogs = Dec::add($cogs, $k);
                    }
                } elseif ($item->is_stock) {
                    $cogs = $this->engine->issue($item->id, $warehouseId, null, $qty, 'sale', $rekod->id, 'Jualan')[0];
                    $baris[] = ['item_id' => $item->id, 'qty' => Dec::n($qty), 'cost' => $cogs];
                }
                $hasil = ['cogs' => Dec::round($cogs, 4), 'lines' => $baris, 'kit' => (bool) $kit];
                $rekod->update(['result' => $hasil]);

                return $hasil + ['replayed' => false];
            });
        } catch (QueryException $e) {
            // Perlumbaan pada kunci idempotensi: pulangkan hasil yang sudah direkod.
            $ada = IdempotencyKey::where(['scope' => 'sale_deduct', 'key' => $key])->first();
            if ($ada) {
                return ($ada->result ?? []) + ['replayed' => true];
            }
            throw $e;
        }
    }
}
