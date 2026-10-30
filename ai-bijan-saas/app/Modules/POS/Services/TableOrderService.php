<?php

namespace App\Modules\POS\Services;

use App\Models\Item;
use App\Modules\POS\Models\POSTable;
use App\Modules\POS\Models\POSTableOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TableOrderService
{
    /**
     * Pesanan dari pelanggan di meja. Harga diambil dari katalog pelayan, bukan dari pelanggan.
     */
    public function place(POSTable $table, array $data): POSTableOrder
    {
        $items = Item::whereIn('id', collect($data['items'])->pluck('item_id'))->where('is_active', true)->get()->keyBy('id');

        return DB::transaction(function () use ($table, $data, $items) {
            $order = POSTableOrder::create([
                'table_id' => $table->id,
                'customer_name' => $data['customer_name'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => 'baru',
            ]);

            $total = 0;
            foreach ($data['items'] as $line) {
                $item = $items->get($line['item_id']);
                if (! $item) {
                    throw ValidationException::withMessages(['items' => ['Menu ini tiada lagi. Muat semula halaman.']]);
                }
                $order->items()->create([
                    'item_id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $line['quantity'],
                    'unit_price' => $item->sell_price,
                    'note' => $line['note'] ?? null,
                ]);
                $total += $item->sell_price * $line['quantity'];
            }

            $order->update(['total_amount' => $total]);

            return $order->load('items', 'table');
        });
    }

    public function setStatus(POSTableOrder $order, string $status): POSTableOrder
    {
        if (in_array($order->status, ['selesai', 'batal'], true)) {
            throw ValidationException::withMessages(['status' => ['Pesanan ini sudah ditutup.']]);
        }
        $order->update(['status' => $status]);

        return $order;
    }

    /** Tutup pesanan selepas dibayar di kaunter. */
    public function close(POSTableOrder $order, ?int $saleId): POSTableOrder
    {
        $order->update(['status' => 'selesai', 'sale_id' => $saleId]);

        return $order;
    }
}
