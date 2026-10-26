<?php

namespace App\Modules\Inventory;

use App\Models\Batch;
use App\Models\Item;
use App\Models\Setting;
use App\Models\StockBalance;
use App\Models\StockLayer;
use App\Models\StockLedgerEntry;
use App\Support\Dec;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Enjin stok deterministik. Semua kemas kini stok MESTI melalui kelas ini.
 * - Kaedah kos tenant: 'fifo' (lalai) atau 'wac' (purata wajaran). LIFO sengaja tidak disokong (V3.0 §6.1).
 * - Barang berbatch tanpa batch dinyatakan: keluar ikut FEFO (tarikh luput terawal), kemudian FIFO.
 * - Kunci baris barang (SELECT … FOR UPDATE) mensirikan operasi serentak → tiada stok negatif / jual lebih.
 * - Lejar tambah sahaja; baki & lapisan kos dikemas kini dalam transaksi yang sama.
 */
class StockEngine
{
    public function method(): string
    {
        $m = Setting::where('key', 'inventory.valuation')->value('value');

        return in_array($m, ['fifo', 'wac'], true) ? $m : 'fifo';
    }

    public function batchFor(Item $item, ?string $batchNo, ?string $expires = null): ?Batch
    {
        if (! $item->has_batch) {
            return null;
        }
        if (! $batchNo) {
            throw ValidationException::withMessages(['batch_no' => "Barang {$item->sku} memerlukan no. batch."]);
        }
        if ($item->has_expiry && ! $expires && ! Batch::where('item_id', $item->id)->where('batch_no', $batchNo)->exists()) {
            throw ValidationException::withMessages(['expires_on' => "Barang {$item->sku} memerlukan tarikh luput."]);
        }

        return Batch::firstOrCreate(['item_id' => $item->id, 'batch_no' => $batchNo], ['expires_on' => $expires]);
    }

    private function lock(int $itemId): Item
    {
        $item = Item::lockForUpdate()->findOrFail($itemId);
        if (! $item->is_stock) {
            throw ValidationException::withMessages(['item' => "Barang {$item->sku} bukan barang stok."]);
        }

        return $item;
    }

    private function balance(int $itemId, int $whId, ?int $batchId): StockBalance
    {
        return StockBalance::where(['item_id' => $itemId, 'warehouse_id' => $whId, 'batch_id' => $batchId])->lockForUpdate()->first()
            ?? StockBalance::create(['item_id' => $itemId, 'warehouse_id' => $whId, 'batch_id' => $batchId, 'qty' => 0, 'value' => 0]);
    }

    private function ledger(int $itemId, int $whId, ?int $batchId, string $qty, string $unitCost, string $value, StockBalance $b, string $srcType, int $srcId, ?string $reason): void
    {
        StockLedgerEntry::create(['item_id' => $itemId, 'warehouse_id' => $whId, 'batch_id' => $batchId, 'qty' => $qty, 'unit_cost' => $unitCost, 'value' => $value,
            'balance_qty' => $b->qty, 'balance_value' => $b->value, 'source_type' => $srcType, 'source_id' => $srcId, 'reason' => $reason, 'user_id' => auth()->id(), 'posted_at' => now()]);
    }

    /** Stok masuk. Pulangkan nilai kos. */
    public function receive(int $itemId, int $whId, ?int $batchId, $qty, $unitCost, string $srcType, int $srcId, ?string $reason = null): string
    {
        $qty = Dec::n($qty);
        $unitCost = Dec::n($unitCost);
        if (Dec::cmp($qty, 0) <= 0) {
            throw ValidationException::withMessages(['qty' => 'Kuantiti mesti lebih dari 0.']);
        }
        if (Dec::cmp($unitCost, 0) < 0) {
            throw ValidationException::withMessages(['unit_cost' => 'Kos tidak boleh negatif.']);
        }

        return DB::transaction(function () use ($itemId, $whId, $batchId, $qty, $unitCost, $srcType, $srcId, $reason) {
            $this->lock($itemId);
            $b = $this->balance($itemId, $whId, $batchId);
            $value = Dec::mul($qty, $unitCost);
            $b->qty = Dec::add($b->qty, $qty);
            $b->value = Dec::add($b->value, $value);
            $b->save();
            if ($this->method() === 'fifo') {
                StockLayer::create(['item_id' => $itemId, 'warehouse_id' => $whId, 'batch_id' => $batchId, 'qty_remaining' => $qty, 'unit_cost' => $unitCost, 'received_at' => now()]);
            }
            $this->ledger($itemId, $whId, $batchId, $qty, $unitCost, $value, $b, $srcType, $srcId, $reason);

            return $value;
        });
    }

    /**
     * Stok keluar. Pulangkan [kos keseluruhan, [[batch_id, qty, kos]...]].
     * $batchId null untuk barang berbatch → peruntukan FEFO automatik merentas batch.
     */
    public function issue(int $itemId, int $whId, ?int $batchId, $qty, string $srcType, int $srcId, ?string $reason = null): array
    {
        $qty = Dec::n($qty);
        if (Dec::cmp($qty, 0) <= 0) {
            throw ValidationException::withMessages(['qty' => 'Kuantiti mesti lebih dari 0.']);
        }

        return DB::transaction(function () use ($itemId, $whId, $batchId, $qty, $srcType, $srcId, $reason) {
            $item = $this->lock($itemId);
            $peruntukan = [];
            if ($item->has_batch && $batchId === null) {
                $baki = StockBalance::where(['stock_balances.item_id' => $itemId, 'stock_balances.warehouse_id' => $whId])->whereNotNull('stock_balances.batch_id')->where('stock_balances.qty', '>', 0)
                    ->join('batches', 'batches.id', '=', 'stock_balances.batch_id')
                    ->orderByRaw('batches.expires_on IS NULL, batches.expires_on, batches.id')->select('stock_balances.batch_id', 'stock_balances.qty')->get();
                $perlu = $qty;
                foreach ($baki as $r) {
                    if (Dec::cmp($perlu, 0) <= 0) {
                        break;
                    }
                    $ambil = Dec::min($perlu, $r->qty);
                    $peruntukan[] = [$r->batch_id, $ambil];
                    $perlu = Dec::sub($perlu, $ambil);
                }
                if (Dec::cmp($perlu, 0) > 0) {
                    throw $this->tidakCukup($item, $qty);
                }
            } else {
                $peruntukan[] = [$batchId, $qty];
            }
            $jumlah = '0';
            $hasil = [];
            foreach ($peruntukan as [$bid, $q]) {
                $b = $this->balance($itemId, $whId, $bid);
                if (Dec::cmp($b->qty, $q) < 0) {
                    throw $this->tidakCukup($item, $q, $b->qty);
                }
                $kos = $this->method() === 'fifo' ? $this->ambilLapisan($itemId, $whId, $bid, $q) : Dec::mul($q, Dec::div($b->value, $b->qty));
                // Keluar semua → nilai baki tepat sifar (elak sisa pembundaran).
                if (Dec::cmp($b->qty, $q) === 0) {
                    $kos = Dec::n($b->value);
                }
                $b->qty = Dec::sub($b->qty, $q);
                $b->value = Dec::sub($b->value, $kos);
                $b->save();
                $this->ledger($itemId, $whId, $bid, Dec::neg($q), Dec::div($kos, $q), Dec::neg($kos), $b, $srcType, $srcId, $reason);
                $jumlah = Dec::add($jumlah, $kos);
                $hasil[] = [$bid, $q, $kos];
            }

            return [$jumlah, $hasil];
        });
    }

    private function ambilLapisan(int $itemId, int $whId, ?int $batchId, string $qty): string
    {
        $lapisan = StockLayer::where(['item_id' => $itemId, 'warehouse_id' => $whId, 'batch_id' => $batchId])->where('qty_remaining', '>', 0)
            ->orderBy('received_at')->orderBy('id')->lockForUpdate()->get();
        $perlu = $qty;
        $kos = '0';
        foreach ($lapisan as $l) {
            if (Dec::cmp($perlu, 0) <= 0) {
                break;
            }
            $ambil = Dec::min($perlu, $l->qty_remaining);
            $kos = Dec::add($kos, Dec::mul($ambil, $l->unit_cost));
            $l->qty_remaining = Dec::sub($l->qty_remaining, $ambil);
            $l->save();
            $perlu = Dec::sub($perlu, $ambil);
        }
        if (Dec::cmp($perlu, 0) > 0) {
            throw new \RuntimeException('Lapisan kos FIFO tidak sepadan dengan baki stok.');
        }

        return $kos;
    }

    private function tidakCukup(Item $item, $perlu, $ada = null): ValidationException
    {
        return ValidationException::withMessages(['qty' => "Stok {$item->sku} tidak mencukupi (perlu ".rtrim(rtrim(Dec::n($perlu), '0'), '.').($ada !== null ? ', ada '.rtrim(rtrim(Dec::n($ada), '0'), '.') : '').').']);
    }

    /** Kos purata semasa (untuk pelarasan masuk tanpa kos). */
    public function averageCost(int $itemId, ?int $whId = null): string
    {
        $q = StockBalance::where('item_id', $itemId)->when($whId, fn ($q) => $q->where('warehouse_id', $whId));
        $qty = Dec::n($q->sum('qty'));

        return Dec::cmp($qty, 0) > 0 ? Dec::div((clone $q)->sum('value'), $qty) : Dec::n(Item::find($itemId)?->standard_cost);
    }

    public function onHand(int $itemId, int $whId, ?int $batchId = null, bool $semuaBatch = false): string
    {
        $q = StockBalance::where(['item_id' => $itemId, 'warehouse_id' => $whId]);
        if (! $semuaBatch) {
            $q->where('batch_id', $batchId);
        }

        return Dec::n($q->sum('qty'));
    }
}
