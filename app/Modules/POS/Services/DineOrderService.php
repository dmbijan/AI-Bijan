<?php

namespace App\Modules\POS\Services;

use App\Models\Item;
use App\Modules\POS\Models\POSDineOrder;
use App\Modules\POS\Models\POSDineTable;
use App\Modules\POS\Models\POSRegister;
use App\Modules\POS\Models\POSSale;
use App\Modules\POS\Models\POSSaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Pesanan dine-in (BAB 88 / T-76).
 *
 * Dipindah daripada B-POS `DineOrderService`. Aliran:
 *
 *   pesanan dibuat (submitted)
 *     → juruwang TERIMA  → jadi POSSale (draf, order_type = dine_in)
 *     → juruwang TOLAK   → status rejected + sebab
 *
 * ⚠️ Perbezaan penting daripada B-POS: B-POS menolak pesanan melainkan
 * juruwang ada syif tunai TERBUKA (kerana ia memotong stok gudang syif).
 * Di sini (a) pesanan boleh diterima tanpa syif — jualan draf tidak memotong
 * stok dan tidak menyentuh tunai — tetapi (b) syif terbuka DILEKATKAN jika ada,
 * supaya laporan syif kekal tepat.
 */
class DineOrderService
{
    public function __construct(
        private ModifierService $modifier,
        private ServiceChargeService $serviceCharge,
        private KitchenService $kitchen,
    ) {
    }

    /**
     * Cipta pesanan dine-in untuk satu meja.
     *
     * @param  array{items: array<int, array<string, mixed>>, notes?: string|null, customer_id?: int|null}  $data
     */
    public function cipta(int $tenantId, POSDineTable $meja, array $data): POSDineOrder
    {
        if (! $meja->is_active) {
            throw ValidationException::withMessages(['dine_table_id' => 'Meja ini tidak aktif.']);
        }

        $baris = $data['items'] ?? [];

        if ($baris === []) {
            throw ValidationException::withMessages(['items' => 'Pesanan mesti ada sekurang-kurangnya satu item.']);
        }

        return DB::transaction(function () use ($tenantId, $meja, $data, $baris) {
            $order = POSDineOrder::create([
                'tenant_id' => $tenantId,
                'dine_table_id' => $meja->id,
                'customer_id' => $data['customer_id'] ?? null,
                'order_no' => $this->nomborPesanan($tenantId),
                'status' => POSDineOrder::STATUS_SUBMITTED,
                'payment_option' => POSDineOrder::PAY_AT_COUNTER,
                'notes' => $data['notes'] ?? null,
                'submitted_at' => now(),
            ]);

            $subtotal = 0.0;

            foreach ($baris as $i => $b) {
                $hasil = $this->baris($tenantId, $b, $i);
                $subtotal += $hasil['line_total'];

                $order->items()->create($hasil + ['tenant_id' => $tenantId]);
            }

            $order->update([
                'subtotal' => round($subtotal, 2),
                'item_count' => (int) $order->items()->sum('quantity'),
            ]);

            $meja->update(['status' => POSDineTable::STATUS_OCCUPIED]);

            return $order->fresh(['items']);
        });
    }

    /**
     * Bangun satu baris pesanan (snapshot nama + harga + modifier).
     *
     * @param  array<string, mixed>  $b
     * @return array<string, mixed>
     */
    private function baris(int $tenantId, array $b, int $i): array
    {
        $kuantiti = (int) ($b['quantity'] ?? 0);

        if ($kuantiti < 1) {
            throw ValidationException::withMessages(["items.{$i}.quantity" => 'Kuantiti mesti sekurang-kurangnya 1.']);
        }

        $item = null;

        if (! empty($b['item_id'])) {
            $item = Item::where('tenant_id', $tenantId)->find($b['item_id']);

            if (! $item) {
                throw ValidationException::withMessages(["items.{$i}.item_id" => 'Item tidak dijumpai.']);
            }
        }

        $nama = $item?->name ?? (string) ($b['item_name'] ?? '');
        $unitPrice = (float) ($b['unit_price'] ?? $item?->sell_price ?? 0);

        if ($nama === '') {
            throw ValidationException::withMessages(["items.{$i}.item_name" => 'Nama item diperlukan.']);
        }

        $modifier = ['modifiers' => [], 'total' => 0.0];

        if ($item && ! empty($b['modifier_options'])) {
            $modifier = $this->modifier->resolve($item, $b['modifier_options']);
        }

        $hargaBaris = $unitPrice + $modifier['total'];

        return [
            'item_id' => $item?->id,
            'item_name' => $nama,
            'unit_price' => round($unitPrice, 2),
            'quantity' => $kuantiti,
            'modifiers' => $modifier['modifiers'] ?: null,
            'modifier_total' => round($modifier['total'], 2),
            'line_total' => round($hargaBaris * $kuantiti, 2),
            'notes' => $b['notes'] ?? null,
        ];
    }

    /**
     * Terima pesanan → cipta POSSale (draf).
     *
     * Stok TIDAK dipotong di sini (jualan masih draf); potongan berlaku semasa
     * jualan diselesaikan, sama seperti penjualan POS biasa.
     */
    public function terima(POSDineOrder $order, int $tenantId, int $userId, ?int $registerId = null): POSSale
    {
        if ($order->status !== POSDineOrder::STATUS_SUBMITTED) {
            throw ValidationException::withMessages(['status' => 'Hanya pesanan baharu boleh diterima.']);
        }

        return DB::transaction(function () use ($order, $tenantId, $userId, $registerId) {
            $order = POSDineOrder::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($order->status !== POSDineOrder::STATUS_SUBMITTED) {
                throw ValidationException::withMessages(['status' => 'Pesanan ini sudah diproses.']);
            }

            $registerId = $registerId ?? $this->daftarLalai($tenantId, $userId);

            $caj = $this->serviceCharge->kira((float) $order->subtotal, 'dine_in');
            $jumlah = round((float) $order->subtotal + $caj['amount'], 2);

            $sale = POSSale::create([
                'tenant_id' => $tenantId,
                'register_id' => $registerId,
                'cash_shift_id' => $this->syifTerbuka($tenantId, $registerId),
                'customer_id' => $order->customer_id,
                'user_id' => $userId,
                'sale_no' => POSSale::generateSaleNo(),
                'status' => 'draft',
                'subtotal' => (float) $order->subtotal,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $jumlah,
                'amount_paid' => 0,
                'change_amount' => 0,
                'service_charge_amount' => $caj['amount'],
                'service_charge_rate' => $caj['rate'],
                'order_type' => 'dine_in',
                'dine_table_id' => $order->dine_table_id,
                'notes' => $order->notes,
            ]);

            foreach ($order->items as $baris) {
                POSSaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $baris->item_id,
                    'product_name' => $baris->item_name,
                    'quantity' => $baris->quantity,
                    'unit_price' => $baris->unit_price,
                    'discount_percentage' => 0,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'line_total' => $baris->line_total,
                    'modifiers' => $baris->modifiers,
                    'modifier_total' => $baris->modifier_total,
                ]);
            }

            $this->kitchen->enqueue($sale);

            $order->update([
                'status' => POSDineOrder::STATUS_ACCEPTED,
                'sale_id' => $sale->id,
                'cashier_id' => $userId,
            ]);

            return $sale;
        });
    }

    /** Tolak pesanan — meja dilepaskan semula. */
    public function tolak(POSDineOrder $order, ?string $sebab = null): POSDineOrder
    {
        if (! $order->menunggu()) {
            throw ValidationException::withMessages(['status' => 'Hanya pesanan baharu boleh ditolak.']);
        }

        return DB::transaction(function () use ($order, $sebab) {
            $order->update([
                'status' => POSDineOrder::STATUS_REJECTED,
                'rejection_reason' => $sebab,
            ]);

            POSDineTable::whereKey($order->dine_table_id)
                ->update(['status' => POSDineTable::STATUS_FREE]);

            return $order;
        });
    }

    /** Melepaskan meja selepas jualan selesai/dibatalkan. */
    public function lepasMeja(POSDineOrder $order): void
    {
        POSDineTable::whereKey($order->dine_table_id)
            ->update(['status' => POSDineTable::STATUS_FREE]);
    }

    /** Nombor pesanan berturutan per tenant (DIN-YYYYMMDD-NNNN). */
    private function nomborPesanan(int $tenantId): string
    {
        $awalan = 'DIN-' . now()->format('Ymd') . '-';
        $bilangan = POSDineOrder::forTenant($tenantId)
            ->where('order_no', 'like', $awalan . '%')
            ->count();

        return $awalan . str_pad((string) ($bilangan + 1), 4, '0', STR_PAD_LEFT);
    }

    /** Daftar lalai jika juruwang tidak menyatakan (daftar aktif pertama). */
    private function daftarLalai(int $tenantId, int $userId): int
    {
        $daftar = POSRegister::forTenant($tenantId)->active()->orderBy('id')->first();

        if ($daftar) {
            return $daftar->id;
        }

        return POSRegister::create([
            'tenant_id' => $tenantId,
            'name' => 'Daftar Utama',
            'code' => 'POS-REG-001',
            'created_by' => $userId,
            'is_active' => true,
        ])->id;
    }

    private function syifTerbuka(int $tenantId, int $registerId): ?int
    {
        return \App\Modules\POS\Models\CashShift::forTenant($tenantId)
            ->where('register_id', $registerId)
            ->where('status', 'open')
            ->orderByDesc('opened_at')
            ->value('id');
    }
}
