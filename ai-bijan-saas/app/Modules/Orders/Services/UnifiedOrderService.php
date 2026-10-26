<?php

namespace App\Modules\Orders\Services;

use App\Modules\Orders\Models\OrderChannelBinding;
use App\Modules\Orders\Models\OrderStockReservation;
use App\Modules\Orders\Models\OrderSyncLog;
use App\Modules\Orders\Models\OrderTimelineEvent;
use App\Modules\Orders\Models\UnifiedOrder;
use App\Modules\Orders\Models\UnifiedOrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * UnifiedOrderService — satu tempat untuk semua pesanan.
 *
 * ✅ Idea #19 (BAB 86): tenant tidak patut melihat 7 tempat untuk satu jawapan.
 *
 * Tanggungjawab:
 * 1. **Ingest** — terima pesanan dari mana-mana sumber (marketplace/POS/site/booking/COD)
 * 2. **Kanonikalisasi** — petakan status platform → status kanonikal AI BIJAN
 * 3. **Stok sync** — rizab stok bila pesanan dibuat, commit bila dihantar, release bila batal
 * 4. **Timeline** — jejak audit setiap perubahan
 */
class UnifiedOrderService
{
    /**
     * Peta status platform → status kanonikal (lalai).
     *
     * @var array<string, array<string, string>>
     */
    public const PETA_STATUS = [
        'shopee' => [
            'READY_TO_SHIP' => UnifiedOrder::STATUS_PACKED,
            'SHIPPED' => UnifiedOrder::STATUS_SHIPPED,
            'COMPLETED' => UnifiedOrder::STATUS_DELIVERED,
            'CANCELLED' => UnifiedOrder::STATUS_CANCELLED,
            'PENDING' => UnifiedOrder::STATUS_PENDING,
            'TO_SHIP' => UnifiedOrder::STATUS_CONFIRMED,
        ],
        'lazada' => [
            'Ready to ship' => UnifiedOrder::STATUS_PACKED,
            'Shipped' => UnifiedOrder::STATUS_SHIPPED,
            'Delivered' => UnifiedOrder::STATUS_DELIVERED,
            'Cancelled' => UnifiedOrder::STATUS_CANCELLED,
            'Pending' => UnifiedOrder::STATUS_PENDING,
        ],
        'tiktok' => [
            'Awaiting shipment' => UnifiedOrder::STATUS_PACKED,
            'Shipped' => UnifiedOrder::STATUS_SHIPPED,
            'Delivered' => UnifiedOrder::STATUS_DELIVERED,
            'Cancelled' => UnifiedOrder::STATUS_CANCELLED,
            'Unpaid' => UnifiedOrder::STATUS_PENDING,
        ],
        'amazon' => [
            'Shipped' => UnifiedOrder::STATUS_SHIPPED,
            'Delivered' => UnifiedOrder::STATUS_DELIVERED,
            'Pending' => UnifiedOrder::STATUS_PENDING,
        ],
        'pos' => [
            'paid' => UnifiedOrder::STATUS_PAID,
            'completed' => UnifiedOrder::STATUS_DELIVERED,
            'refunded' => UnifiedOrder::STATUS_REFUNDED,
        ],
        'site' => [
            'processing' => UnifiedOrder::STATUS_PROCESSING,
            'shipped' => UnifiedOrder::STATUS_SHIPPED,
            'delivered' => UnifiedOrder::STATUS_DELIVERED,
        ],
        'booking' => [
            'confirmed' => UnifiedOrder::STATUS_CONFIRMED,
            'completed' => UnifiedOrder::STATUS_DELIVERED,
            'cancelled' => UnifiedOrder::STATUS_CANCELLED,
        ],
    ];

    /**
     * Aliran status kanonikal yang dibenarkan.
     *
     * @var array<string, array<int, string>>
     */
    public const ALIRAN = [
        UnifiedOrder::STATUS_DRAFT => [
            UnifiedOrder::STATUS_PENDING,
            UnifiedOrder::STATUS_CANCELLED,
        ],
        UnifiedOrder::STATUS_PENDING => [
            UnifiedOrder::STATUS_CONFIRMED,
            UnifiedOrder::STATUS_PAID,
            UnifiedOrder::STATUS_ON_HOLD,
            UnifiedOrder::STATUS_CANCELLED,
            UnifiedOrder::STATUS_FAILED,
        ],
        UnifiedOrder::STATUS_CONFIRMED => [
            UnifiedOrder::STATUS_PAID,
            UnifiedOrder::STATUS_PROCESSING,
            UnifiedOrder::STATUS_PACKED,
            UnifiedOrder::STATUS_CANCELLED,
        ],
        UnifiedOrder::STATUS_PAID => [
            UnifiedOrder::STATUS_PROCESSING,
            UnifiedOrder::STATUS_PACKED,
            UnifiedOrder::STATUS_CANCELLED,
            UnifiedOrder::STATUS_REFUNDED,
        ],
        UnifiedOrder::STATUS_PROCESSING => [
            UnifiedOrder::STATUS_PACKED,
            UnifiedOrder::STATUS_SHIPPED,
            UnifiedOrder::STATUS_CANCELLED,
        ],
        UnifiedOrder::STATUS_PACKED => [
            UnifiedOrder::STATUS_SHIPPED,
            UnifiedOrder::STATUS_CANCELLED,
        ],
        UnifiedOrder::STATUS_SHIPPED => [
            UnifiedOrder::STATUS_OUT_FOR_DELIVERY,
            UnifiedOrder::STATUS_DELIVERED,
            UnifiedOrder::STATUS_ON_HOLD,
        ],
        UnifiedOrder::STATUS_OUT_FOR_DELIVERY => [
            UnifiedOrder::STATUS_DELIVERED,
            UnifiedOrder::STATUS_ON_HOLD,
            UnifiedOrder::STATUS_FAILED,
        ],
        UnifiedOrder::STATUS_ON_HOLD => [
            UnifiedOrder::STATUS_CONFIRMED,
            UnifiedOrder::STATUS_PROCESSING,
            UnifiedOrder::STATUS_SHIPPED,
            UnifiedOrder::STATUS_CANCELLED,
        ],
        UnifiedOrder::STATUS_FAILED => [
            UnifiedOrder::STATUS_CONFIRMED,
            UnifiedOrder::STATUS_CANCELLED,
        ],
        UnifiedOrder::STATUS_DELIVERED => [
            UnifiedOrder::STATUS_PARTIALLY_REFUNDED,
            UnifiedOrder::STATUS_REFUNDED,
        ],
        UnifiedOrder::STATUS_PARTIALLY_REFUNDED => [
            UnifiedOrder::STATUS_REFUNDED,
        ],
        UnifiedOrder::STATUS_CANCELLED => [],
        UnifiedOrder::STATUS_REFUNDED => [],
    ];

    /**
     * Jana nombor pesanan unified yang unik.
     */
    public function janaOrderNo(int $tenantId): string
    {
        $tahun = now()->format('Y');
        $count = UnifiedOrder::where('tenant_id', $tenantId)->count() + 1;

        do {
            $no = sprintf('AO-%s-%05d', $tahun, $count);
            $ada = UnifiedOrder::where('tenant_id', $tenantId)->where('order_no', $no)->exists();
            $count++;
        } while ($ada);

        return $no;
    }

    /**
     * Ingest pesanan dari mana-mana sumber.
     *
     * @param array $data Data pesanan (sumber, platform, item, party, nilai…)
     */
    public function ingest(int $tenantId, array $data): UnifiedOrder
    {
        $platform = $data['source_platform'] ?? null;
        $source = $data['source'] ?? UnifiedOrder::SOURCE_MANUAL;

        // Status PLATFORM (cth "READY_TO_SHIP") → status KANONIKAL
        $statusPlatform = $data['status'] ?? null;
        $status = $this->kanonikalkanStatus($platform, $statusPlatform)
            ?? UnifiedOrder::STATUS_PENDING;

        // `items` bukan kolum — keluarkan sebelum mass-assignment
        $items = $data['items'] ?? [];
        unset($data['items']);

        // Status platform asal kekal di source_status
        $data['source_status'] = $statusPlatform;

        // Cari pesanan sedia ada (idempoten) hanya jika ada source_id
        $order = null;

        if (! empty($data['source_id'])) {
            $order = UnifiedOrder::query()
                ->where('tenant_id', $tenantId)
                ->where('source', $source)
                ->where('source_platform', $platform)
                ->where('source_id', $data['source_id'])
                ->first();
        }

        if ($order === null) {
            $order = UnifiedOrder::create(array_merge([
                'order_no' => $data['order_no'] ?? $this->janaOrderNo($tenantId),
                'source_order_no' => $data['source_order_no'] ?? null,
                'payment_method' => $data['payment_method'] ?? null,
                'payment_status' => $data['payment_status'] ?? UnifiedOrder::PAYMENT_UNPAID,
                'fulfillment_method' => $data['fulfillment_method'] ?? 'shipping',
                'currency' => $data['currency'] ?? 'MYR',
                'customer_name' => $data['customer_name'] ?? null,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'shipping_address' => $data['shipping_address'] ?? null,
                'billing_address' => $data['billing_address'] ?? null,
                'meta' => $data['meta'] ?? null,
                'placed_at' => $data['placed_at'] ?? now(),
                'source_synced_at' => now(),
            ], $data, [
                'tenant_id' => $tenantId,
                'status' => $status,
                'source' => $source,
                'source_platform' => $platform,
                'source_id' => $data['source_id'] ?? null,
            ]));
        } else {
            $order->update(array_merge($data, [
                'status' => $status,
                'source_synced_at' => now(),
            ]));
        }

        // Item pesanan — satu pesanan boleh ada berbilang line item
        if (! empty($items)) {
            // Buang item lama supaya re-ingest tidak melilinasi
            UnifiedOrderItem::where('tenant_id', $tenantId)
                ->where('unified_order_id', $order->id)
                ->delete();

            foreach ($items as $itemData) {
                $this->tambahItem($tenantId, $order, $itemData);
            }
        }

        $this->kiraJumlah($tenantId, $order);

        // Rizab stok
        $this->rizabStok($tenantId, $order);

        $this->catat($tenantId, $order->id, OrderTimelineEvent::EVENT_CREATED, null, $status, [
            'source' => $source,
            'platform' => $platform,
        ]);

        return $order->fresh();
    }
    /**
     * Tambah item pada pesanan.
     */
    public function tambahItem(int $tenantId, UnifiedOrder $order, array $items): UnifiedOrderItem
    {
        $item = UnifiedOrderItem::create(array_merge([
            'tenant_id' => $tenantId,
            'unified_order_id' => $order->id,
            'name' => 'Item',
            'quantity' => 1,
            'unit_price' => 0,
        ], $items));

        $item->kiraLineTotal();
        $item->save();

        $this->kiraJumlah($tenantId, $order);

        return $item;
    }

    /**
     * Kira semula jumlah pesanan daripada item.
     */
    public function kiraJumlah(int $tenantId, UnifiedOrder $order): UnifiedOrder
    {
        $items = UnifiedOrderItem::where('tenant_id', $tenantId)
            ->where('unified_order_id', $order->id)
            ->get();

        $subtotal = (float) $items->sum('line_total');
        $discount = (float) $items->sum('discount_amount');
        $tax = (float) $items->sum('tax_amount');
        $cost = (float) $items->sum(fn ($i) => $i->jumlahKos());

        $order->update([
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => round($tax, 2),
            'total_amount' => round($subtotal + (float) $order->shipping_amount, 2),
            'cost_amount' => round($cost, 2),
            'line_count' => $items->count(),
        ]);

        return $order->fresh();
    }

    /**
     * Rizab stok untuk item pesanan.
     *
     * Rizab dibuat bila pesanan dibuat supaya stok tidak boleh "dihantar dua kali"
     * bila ada 2 platform selling produk sama.
     */
    public function rizabStok(int $tenantId, UnifiedOrder $order): void
    {
        // Buang rizab lama (dalam status released/cancelled)
        OrderStockReservation::where('tenant_id', $tenantId)
            ->where('unified_order_id', $order->id)
            ->whereIn('status', [OrderStockReservation::STATUS_RELEASED, OrderStockReservation::STATUS_CANCELLED])
            ->delete();

        $items = UnifiedOrderItem::where('tenant_id', $tenantId)
            ->where('unified_order_id', $order->id)
            ->get();

        foreach ($items as $item) {
            if ($item->product_id === null) {
                continue;
            }

            $sedia = OrderStockReservation::where('tenant_id', $tenantId)
                ->where('unified_order_id', $order->id)
                ->where('product_id', $item->product_id)
                ->where('status', OrderStockReservation::STATUS_RESERVED)
                ->exists();

            if ($sedia) {
                continue;
            }

            OrderStockReservation::create([
                'tenant_id' => $tenantId,
                'unified_order_id' => $order->id,
                'product_id' => $item->product_id,
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'reserved_quantity' => $item->quantity,
                'status' => OrderStockReservation::STATUS_RESERVED,
                'expires_at' => now()->addDays((int) config('orders.reservation_ttl_days', 7)),
            ]);
        }
    }

    /**
     * Tukar status pesanan + urus stok mengikut status.
     */
    public function tukarStatus(int $tenantId, int $orderId, string $status, ?string $note = null, string $actorType = 'user', ?int $actorId = null): UnifiedOrder
    {
        $order = UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($orderId);

        $dari = $order->status;

        if ($dari === $status) {
            return $order;
        }

        $dibenarkan = self::ALIRAN[$dari] ?? [];

        if (! in_array($status, $dibenarkan, true)) {
            Log::warning('Orders: perpindahan status tidak sah', [
                'order_no' => $order->order_no,
                'from' => $dari,
                'to' => $status,
            ]);

            return $order;
        }

        $order->status = $status;

        match ($status) {
            UnifiedOrder::STATUS_CONFIRMED => $order->confirmed_at = now(),
            UnifiedOrder::STATUS_PAID => $order->paid_at = now(),
            UnifiedOrder::STATUS_SHIPPED => $order->shipped_at = now(),
            UnifiedOrder::STATUS_DELIVERED => $order->delivered_at = now(),
            UnifiedOrder::STATUS_CANCELLED => $order->cancelled_at = now(),
            UnifiedOrder::STATUS_REFUNDED => $order->refunded_at = now(),
            default => null,
        };

        $order->save();

        // Urus stok ikut status
        $this->urusStokUntukStatus($tenantId, $order, $status);

        $this->catat(
            $tenantId,
            $order->id,
            OrderTimelineEvent::EVENT_STATUS_CHANGED,
            $dari,
            $status,
            array_filter(['note' => $note]),
            $actorType,
            $actorId
        );

        return $order->fresh();
    }

    /**
     * Urus rizab stok mengikut status baharu.
     */
    private function urusStokUntukStatus(int $tenantId, UnifiedOrder $order, string $status): void
    {
        $reservations = OrderStockReservation::where('tenant_id', $tenantId)
            ->where('unified_order_id', $order->id)
            ->where('status', OrderStockReservation::STATUS_RESERVED)
            ->get();

        if (in_array($status, UnifiedOrder::STATUS_STOCK_COMMITTED, true)) {
            foreach ($reservations as $r) {
                $r->update([
                    'status' => OrderStockReservation::STATUS_COMMITTED,
                    'committed_at' => now(),
                ]);
            }

            return;
        }

        if (in_array($status, UnifiedOrder::STATUS_STOCK_RELEASED, true)) {
            foreach ($reservations as $r) {
                $r->update([
                    'status' => OrderStockReservation::STATUS_RELEASED,
                    'released_at' => now(),
                ]);
            }
        }
    }

    /**
     * Rekod bayaran.
     */
    public function rekodBayaran(int $tenantId, int $orderId, float $amount, string $method = 'bank_transfer', ?string $reference = null): UnifiedOrder
    {
        $order = UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($orderId);

        $order->paid_amount = round((float) $order->paid_amount + $amount, 2);

        if ((float) $order->paid_amount >= (float) $order->total_amount) {
            $order->payment_status = UnifiedOrder::PAYMENT_PAID;
            $order->payment_method = $order->payment_method ?? $method;
            $order->paid_at = now();
        } elseif ((float) $order->paid_amount > 0) {
            $order->payment_status = UnifiedOrder::PAYMENT_PARTIAL;
        }

        $order->save();

        $this->catat($tenantId, $order->id, OrderTimelineEvent::EVENT_PAYMENT, null, $order->payment_status, [
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
        ]);

        return $order->fresh();
    }

    /**
     * Petakan status platform → status kanonikal.
     */
    public function kanonikalkanStatus(?string $platform, ?string $status): ?string
    {
        if ($status === null) {
            return null;
        }

        //(binding per-tenant menang atas peta lalai)
        if ($platform) {
            $binding = OrderChannelBinding::query()
                ->where('tenant_id', app(\App\Support\Tenancy\TenantContext::class)->id())
                ->where('platform', $platform)
                ->first();

            if ($binding) {
                $mapped = $binding->petakanStatus($status);
                if ($mapped) {
                    return $mapped;
                }
            }
        }

        if ($platform && isset(self::PETA_STATUS[$platform][$status])) {
            return self::PETA_STATUS[$platform][$status];
        }

        // Status yang sudah kanonikal
        if (array_key_exists($status, self::ALIRAN)) {
            return $status;
        }

        return null;
    }

    /**
     * Catat peristiwa timeline.
     */
    public function catat(int $tenantId, int $orderId, string $eventType, ?string $from = null, ?string $to = null, array $context = [], string $actorType = 'system', ?int $actorId = null): OrderTimelineEvent
    {
        return OrderTimelineEvent::create([
            'tenant_id' => $tenantId,
            'unified_order_id' => $orderId,
            'event_type' => $eventType,
            'from_value' => $from,
            'to_value' => $to,
            'description' => $context['note'] ?? null,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'context' => $context ?: null,
        ]);
    }

    /**
     * Rekod keputusan sinkronisasi platform.
     */
    public function catatSync(int $tenantId, string $platform, string $direction, string $status, int $synced = 0, int $failed = 0, ?string $error = null, ?int $orderId = null, ?int $durationMs = null): OrderSyncLog
    {
        return OrderSyncLog::create([
            'tenant_id' => $tenantId,
            'unified_order_id' => $orderId,
            'platform' => $platform,
            'direction' => $direction,
            'status' => $status,
            'records_synced' => $synced,
            'records_failed' => $failed,
            'error_message' => $error,
            'duration_ms' => $durationMs,
        ]);
    }

    /**
     * Ringkasan jualan merentas semua platform — jawapan kepada
     * "berapa jualan saya hari ini, dari mana?"
     */
    public function ringkasan(int $tenantId, ?string $dari = null, ?string $hingga = null): array
    {
        $query = UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', [UnifiedOrder::STATUS_CANCELLED, UnifiedOrder::STATUS_DRAFT]);

        if ($dari) {
            $query->whereDate('placed_at', '>=', $dari);
        }

        if ($hingga) {
            $query->whereDate('placed_at', '<=', $hingga);
        }

        $orders = $query->get();

        $total = (float) $orders->sum('total_amount');
        $kos = (float) $orders->sum('cost_amount');
        $dibayar = (float) $orders->sum('paid_amount');
        $belumBayar = (float) $orders->sum(fn ($o) => $o->bakiBayaran());

        $ikutPlatform = $orders
            ->groupBy(fn ($o) => $o->source_platform ?: $o->source)
            ->map(fn ($group, $platform) => [
                'platform' => $platform,
                'pesanan' => $group->count(),
                'jumlah' => round((float) $group->sum('total_amount'), 2),
            ])
            ->sortByDesc('jumlah')
            ->values()
            ->toArray();

        $ikutStatus = $orders
            ->groupBy('status')
            ->map(fn ($group, $status) => [
                'status' => $status,
                'pesanan' => $group->count(),
            ])
            ->toArray();

        return [
            'jumlah_pesanan' => $orders->count(),
            'jumlah_jualan' => round($total, 2),
            'jumlah_kos' => round($kos, 2),
            'margin' => round($total - $kos, 2),
            'margin_percent' => $total > 0 ? round(($total - $kos) / $total * 100, 2) : 0.0,
            'sudah_bayar' => round($dibayar, 2),
            'belum_bayar' => round($belumBayar, 2),
            'ikut_platform' => $ikutPlatform,
            'ikut_status' => $ikutStatus,
            'purata_pesanan' => $orders->count() > 0 ? round($total / $orders->count(), 2) : 0.0,
            'periode' => ['dari' => $dari, 'hingga' => $hingga],
        ];
    }

    /**
     * Pesanan yang memerlukan tindakan (tidak dijawab / lama).
     */
    public function perlukanTindakan(int $tenantId, int $limit = 50): array
    {
        return UnifiedOrder::query()
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', UnifiedOrder::STATUS_TERMINAL)
            ->where(function ($q) {
                $q->where('status', UnifiedOrder::STATUS_PENDING)
                    ->orWhere('status', UnifiedOrder::STATUS_ON_HOLD);
            })
            ->orderBy('placed_at')
            ->limit($limit)
            ->get()
            ->filter(fn ($o) => $o->perluTindakan())
            ->map(fn ($o) => [
                'id' => $o->id,
                'order_no' => $o->order_no,
                'status' => $o->status,
                'platform' => $o->source_platform,
                'jumlah' => (float) $o->total_amount,
                'lama' => $o->placed_at?->diffForHumans(),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Bersihkan rizab stok yang luput (scheduler).
     */
    public function bersihkanRizabLuput(int $tenantId): int
    {
        $luput = OrderStockReservation::query()
            ->where('tenant_id', $tenantId)
            ->where('status', OrderStockReservation::STATUS_RESERVED)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($luput as $r) {
            $r->update([
                'status' => OrderStockReservation::STATUS_RELEASED,
                'released_at' => now(),
            ]);
        }

        return $luput->count();
    }

    /**
     * Sinkronisasi pesanan dari platform (stub untuk adapter sebenar).
     *
     * @return array{status: string, synced: int, failed: int}
     */
    public function sinkronPlatform(int $tenantId, string $platform, array $pesanan = []): array
    {
        $mula = microtime(true);
        $synced = 0;
        $failed = 0;

        $binding = OrderChannelBinding::query()
            ->where('tenant_id', $tenantId)
            ->where('platform', $platform)
            ->where('is_active', true)
            ->first();

        if (! $binding) {
            $this->catatSync($tenantId, $platform, OrderSyncLog::DIRECTION_PULL, OrderSyncLog::STATUS_SKIPPED, 0, 0, 'Tiada binding aktif untuk platform ini');

            return ['status' => OrderSyncLog::STATUS_SKIPPED, 'synced' => 0, 'failed' => 0];
        }

        foreach ($pesanan as $data) {
            try {
                $this->ingest($tenantId, array_merge($data, [
                    'source_platform' => $platform,
                    'source' => $data['source'] ?? UnifiedOrder::SOURCE_MARKETPLACE,
                ]));
                $synced++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Orders: sync gagal', [
                    'tenant_id' => $tenantId,
                    'platform' => $platform,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $status = $failed === 0
            ? OrderSyncLog::STATUS_SUCCESS
            : ($synced > 0 ? OrderSyncLog::STATUS_PARTIAL : OrderSyncLog::STATUS_FAILED);

        $this->catatSync(
            $tenantId,
            $platform,
            OrderSyncLog::DIRECTION_PULL,
            $status,
            $synced,
            $failed,
            null,
            null,
            (int) round((microtime(true) - $mula) * 1000)
        );

        $binding->update(['last_pull_at' => now()]);

        return ['status' => $status, 'synced' => $synced, 'failed' => $failed];
    }
}
