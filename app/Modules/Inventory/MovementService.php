<?php

namespace App\Modules\Inventory;

use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use App\Support\Dec;
use App\Support\NumberSeries;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Dokumen pergerakan stok: draf → pos. Setiap transaksi stok ada dokumen sumber, pengguna, masa, kuantiti, kos & sebab. */
class MovementService
{
    public const AWALAN = ['receipt' => 'TRM', 'issue' => 'KLR', 'transfer' => 'PND', 'adjustment' => 'LRS', 'count' => 'KRS'];

    public function __construct(private StockEngine $engine) {}

    public function create(array $d): StockMovement
    {
        return DB::transaction(function () use ($d) {
            $m = StockMovement::create(['number' => NumberSeries::next('stok_'.$d['type'], self::AWALAN[$d['type']]), 'type' => $d['type'], 'status' => 'draft',
                'from_warehouse_id' => $d['from_warehouse_id'] ?? null, 'to_warehouse_id' => $d['to_warehouse_id'] ?? null, 'reason' => $d['reason'] ?? null,
                'reference' => $d['reference'] ?? null, 'freight_in' => Dec::n($d['freight_in'] ?? 0), 'created_by' => auth()->id()]);
            foreach ($d['lines'] as $l) {
                $m->lines()->create(['item_id' => $l['item_id'], 'batch_no' => $l['batch_no'] ?? null, 'expires_on' => $l['expires_on'] ?? null,
                    'qty' => Dec::n($l['qty']), 'unit_cost' => isset($l['unit_cost']) ? Dec::n($l['unit_cost']) : null]);
            }
            app(AuditLogger::class)->log('inventory.movement_created', $m, null, ['number' => $m->number, 'type' => $m->type, 'lines' => count($d['lines'])]);

            return $m;
        });
    }

    private function wh(?int $id, string $label): Warehouse
    {
        $w = $id ? Warehouse::find($id) : null;
        if (! $w || ! $w->is_active) {
            throw ValidationException::withMessages([$label => 'Gudang tidak sah atau tidak aktif.']);
        }

        return $w;
    }

    /** Pos dokumen draf. Pindahan: keluar dari gudang asal → status in_transit (diterima kemudian). */
    public function post(StockMovement $m): StockMovement
    {
        if ($m->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Hanya draf boleh dipos.']);
        }

        return DB::transaction(function () use ($m) {
            $m = StockMovement::lockForUpdate()->find($m->id);
            if ($m->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'Dokumen sudah dipos.']);
            }
            $lines = $m->lines()->with('item')->get();
            $src = 'stock_movement';
            match ($m->type) {
                'receipt' => $this->terima($m, $lines),
                'issue' => $lines->each(fn ($l) => $l->update(['cost_value' => $this->keluar($m, $l, $this->wh($m->from_warehouse_id, 'from_warehouse_id'))])),
                'transfer' => $this->pindahKeluar($m, $lines),
                'adjustment' => $this->laras($m, $lines),
                'count' => $this->kira($m, $lines),
            };
            $m->update(['status' => $m->type === 'transfer' ? 'in_transit' : 'posted', 'posted_by' => auth()->id(), 'posted_at' => now()]);
            app(AuditLogger::class)->log('inventory.movement_posted', $m, ['status' => 'draft'], ['status' => $m->status, 'number' => $m->number]);

            return $m->fresh('lines');
        });
    }

    private function terima(StockMovement $m, $lines): void
    {
        $wh = $this->wh($m->to_warehouse_id, 'to_warehouse_id');
        // Agih freight-in ikut nilai baris (kos yang layak dimasukkan ke kos barang).
        $jumlahNilai = Dec::add(...$lines->map(fn ($l) => Dec::mul($l->qty, $l->unit_cost ?? 0))->all());
        foreach ($lines as $l) {
            if ($l->unit_cost === null) {
                throw ValidationException::withMessages(['unit_cost' => "Kos seunit diperlukan untuk {$l->item->sku}."]);
            }
            $nilai = Dec::mul($l->qty, $l->unit_cost);
            $freight = Dec::cmp($jumlahNilai, 0) > 0 ? Dec::mul($m->freight_in, Dec::div($nilai, $jumlahNilai)) : '0';
            $kos = Dec::div(Dec::add($nilai, $freight), $l->qty);
            $batch = $this->engine->batchFor($l->item, $l->batch_no, $l->expires_on);
            $l->update(['cost_value' => $this->engine->receive($l->item_id, $wh->id, $batch?->id, $l->qty, $kos, 'stock_movement', $m->id, $m->reason)]);
        }
    }

    private function keluar(StockMovement $m, $l, Warehouse $wh): string
    {
        $batch = $l->batch_no ? $this->engine->batchFor($l->item, $l->batch_no) : null;

        return $this->engine->issue($l->item_id, $wh->id, $batch?->id, $l->qty, 'stock_movement', $m->id, $m->reason)[0];
    }

    private function pindahKeluar(StockMovement $m, $lines): void
    {
        $dari = $this->wh($m->from_warehouse_id, 'from_warehouse_id');
        $ke = $this->wh($m->to_warehouse_id, 'to_warehouse_id');
        if ($dari->id === $ke->id) {
            throw ValidationException::withMessages(['to_warehouse_id' => 'Gudang asal dan destinasi sama.']);
        }
        foreach ($lines as $l) {
            $batch = $l->batch_no ? $this->engine->batchFor($l->item, $l->batch_no) : null;
            [$kos, $per] = $this->engine->issue($l->item_id, $dari->id, $batch?->id, $l->qty, 'stock_movement', $m->id, 'Pindah ke '.$ke->code);
            if ($l->item->has_batch && ! $l->batch_no) {
                // Batch diperuntuk FEFO: pecah baris ikut batch supaya penerimaan masuk ke batch & kos yang sama.
                $l->delete();
                foreach ($per as [$bid, $q, $k]) {
                    $m->lines()->create(['item_id' => $l->item_id, 'batch_no' => Batch::find($bid)->batch_no, 'qty' => $q, 'unit_cost' => Dec::div($k, $q), 'cost_value' => $k]);
                }
            } else {
                $l->update(['cost_value' => $kos, 'unit_cost' => Dec::div($kos, $l->qty)]);
            }
        }
    }

    /** Terima pindahan di destinasi pada kos yang sama (kos tidak berubah semasa pindah). */
    public function receiveTransfer(StockMovement $m): StockMovement
    {
        return DB::transaction(function () use ($m) {
            $m = StockMovement::lockForUpdate()->find($m->id);
            if ($m->type !== 'transfer' || $m->status !== 'in_transit') {
                throw ValidationException::withMessages(['status' => 'Pindahan ini tidak dalam transit.']);
            }
            $ke = $this->wh($m->to_warehouse_id, 'to_warehouse_id');
            foreach ($m->lines()->with('item')->get() as $l) {
                $batch = $l->batch_no ? $this->engine->batchFor($l->item, $l->batch_no) : null;
                $this->engine->receive($l->item_id, $ke->id, $batch?->id, $l->qty, $l->unit_cost, 'stock_movement', $m->id, 'Terima pindahan '.$m->number);
            }
            $m->update(['status' => 'received', 'received_by' => auth()->id(), 'received_at' => now()]);
            app(AuditLogger::class)->log('inventory.transfer_received', $m, ['status' => 'in_transit'], ['status' => 'received']);

            return $m;
        });
    }

    /** Pelarasan: qty bertanda (+ masuk, − keluar). Sebab wajib. */
    private function laras(StockMovement $m, $lines): void
    {
        if (! $m->reason) {
            throw ValidationException::withMessages(['reason' => 'Sebab pelarasan wajib.']);
        }
        $wh = $this->wh($m->to_warehouse_id ?? $m->from_warehouse_id, 'to_warehouse_id');
        foreach ($lines as $l) {
            $batch = $this->engine->batchFor($l->item, $l->batch_no, $l->expires_on);
            if (Dec::cmp($l->qty, 0) > 0) {
                $kos = $l->unit_cost ?? $this->engine->averageCost($l->item_id, $wh->id);
                $l->update(['unit_cost' => $kos, 'cost_value' => $this->engine->receive($l->item_id, $wh->id, $batch?->id, $l->qty, $kos, 'stock_movement', $m->id, $m->reason)]);
            } elseif (Dec::cmp($l->qty, 0) < 0) {
                $l->update(['cost_value' => Dec::neg($this->engine->issue($l->item_id, $wh->id, $batch?->id, Dec::neg($l->qty), 'stock_movement', $m->id, $m->reason)[0])]);
            }
        }
    }

    /** Kira stok: qty = kiraan fizikal; beza dengan baki sistem dipos sebagai pelarasan. */
    private function kira(StockMovement $m, $lines): void
    {
        $wh = $this->wh($m->to_warehouse_id ?? $m->from_warehouse_id, 'to_warehouse_id');
        foreach ($lines as $l) {
            $batch = $l->batch_no ? $this->engine->batchFor($l->item, $l->batch_no, $l->expires_on) : null;
            $sistem = $this->engine->onHand($l->item_id, $wh->id, $batch?->id);
            $beza = Dec::sub($l->qty, $sistem);
            $nilai = '0';
            if (Dec::cmp($beza, 0) > 0) {
                $nilai = $this->engine->receive($l->item_id, $wh->id, $batch?->id, $beza, $l->unit_cost ?? $this->engine->averageCost($l->item_id, $wh->id), 'stock_movement', $m->id, 'Kira stok '.$m->number);
            } elseif (Dec::cmp($beza, 0) < 0) {
                $nilai = Dec::neg($this->engine->issue($l->item_id, $wh->id, $batch?->id, Dec::neg($beza), 'stock_movement', $m->id, 'Kira stok '.$m->number)[0]);
            }
            $l->update(['system_qty' => $sistem, 'cost_value' => $nilai]);
        }
    }

    public function cancel(StockMovement $m): void
    {
        if ($m->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'Hanya draf boleh dibatalkan. Dokumen dipos diperbetulkan dengan pelarasan.']);
        }
        $m->update(['status' => 'cancelled']);
        app(AuditLogger::class)->log('inventory.movement_cancelled', $m);
    }
}
