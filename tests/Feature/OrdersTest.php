<?php

namespace Tests\Feature;

use App\Modules\Orders\Models\OrderChannelBinding;
use App\Modules\Orders\Models\OrderStockReservation;
use App\Modules\Orders\Models\OrderSyncLog;
use App\Modules\Orders\Models\OrderTimelineEvent;
use App\Modules\Orders\Models\UnifiedOrder;
use App\Modules\Orders\Models\UnifiedOrderItem;
use App\Modules\Orders\Services\UnifiedOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AksesTenant;
use Tests\TestCase;

/**
 * Unified Order Management (BAB 86 / Idea #19).
 *
 * Satu tempat untuk semua pesanan — marketplace, POS, site, booking, COD.
 */
class OrdersTest extends TestCase
{
    use RefreshDatabase, AksesTenant;

    protected $seed = true;

    private $u;
    private $t;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAkses();
        [$this->u, $this->t] = $this->daftar('pemilik@orders.my', 'Orders Sdn Bhd');
        $this->dalamTenant($this->t);
    }

    private function item(array $ganti = []): array
    {
        return array_merge([
            'name' => 'Barang Uji',
            'quantity' => 2,
            'unit_price' => 50.00,
        ], $ganti);
    }

    // -------------------------------------------------------------------
    // Ingest
    // -------------------------------------------------------------------

    public function test_ingest_mencipta_pesanan(): void
    {
        $s = app(UnifiedOrderService::class);

        $order = $s->ingest($this->t->id, [
            'source' => UnifiedOrder::SOURCE_MARKETPLACE,
            'source_platform' => 'shopee',
            'source_id' => 'SHP-001',
            'customer_name' => 'Ahmad Ali',
            'items' => [$this->item()],
        ]);

        $this->assertNotNull($order->id);
        $this->assertStringStartsWith('AO-', $order->order_no);
        $this->assertSame('shopee', $order->source_platform);
        $this->assertSame(1, $order->line_count);
    }

    public function test_ingest_dengan_pil_item(): void
    {
        $s = app(UnifiedOrderService::class);

        $order = $s->ingest($this->t->id, [
            'source' => UnifiedOrder::SOURCE_MANUAL,
            'items' => [
                $this->item(['name' => 'Item A', 'unit_price' => 10]),
                $this->item(['name' => 'Item B', 'unit_price' => 20]),
            ],
        ]);

        $this->assertSame(2, $order->line_count);
        $this->assertSame(60.0, (float) $order->total_amount);
    }

    public function test_ingest_idempotent_untuk_source_id_sama(): void
    {
        $s = app(UnifiedOrderService::class);

        $data = [
            'source' => UnifiedOrder::SOURCE_MARKETPLACE,
            'source_platform' => 'shopee',
            'source_id' => 'SHP-DUP',
            'items' => [$this->item()],
        ];

        $a = $s->ingest($this->t->id, $data);
        $b = $s->ingest($this->t->id, $data);

        $this->assertSame($a->id, $b->id);
        $this->assertSame(1, UnifiedOrder::where('tenant_id', $this->t->id)->count());
    }

    public function test_kanonikalkan_status_platform(): void
    {
        $s = app(UnifiedOrderService::class);

        $this->assertSame(
            UnifiedOrder::STATUS_SHIPPED,
            $s->kanonikalkanStatus('shopee', 'SHIPPED')
        );
        $this->assertSame(
            UnifiedOrder::STATUS_DELIVERED,
            $s->kanonikalkanStatus('lazada', 'Delivered')
        );
        $this->assertSame(
            UnifiedOrder::STATUS_CANCELLED,
            $s->kanonikalkanStatus('tiktok', 'Cancelled')
        );
    }

    public function test_kanonikalkan_status_tak_dikenali_null(): void
    {
        $s = app(UnifiedOrderService::class);

        $this->assertNull($s->kanonikalkanStatus('shopee', 'StatusTidakDikenali'));
    }

    // -------------------------------------------------------------------
    // Jumlah & margin
    // -------------------------------------------------------------------

    public function test_kira_jumlah_dan_margin(): void
    {
        $s = app(UnifiedOrderService::class);

        $order = $s->ingest($this->t->id, [
            'items' => [$this->item(['quantity' => 3, 'unit_price' => 40, 'unit_cost' => 25])],
        ]);

        $this->assertSame(120.0, (float) $order->total_amount);
        $this->assertSame(75.0, (float) $order->cost_amount);
        $this->assertSame(45.0, (float) $order->margin());
        $this->assertSame(37.5, (float) $order->marginPercent());
    }

    public function test_baki_bayaran(): void
    {
        $s = app(UnifiedOrderService::class);

        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        $this->assertSame(100.0, $order->bakiBayaran());
    }

    // -------------------------------------------------------------------
    // Status
    // -------------------------------------------------------------------

    public function test_tukar_status_valid(): void
    {
        $s = app(UnifiedOrderService::class);
        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        $selesai = $s->tukarStatus($this->t->id, $order->id, UnifiedOrder::STATUS_CONFIRMED);

        $this->assertSame(UnifiedOrder::STATUS_CONFIRMED, $selesai->status);
        $this->assertNotNull($selesai->confirmed_at);
    }

    public function test_tukar_status_tidak_sah_ditolak(): void
    {
        $s = app(UnifiedOrderService::class);
        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        // pending → delivered tidak dibenarkan
        $s->tukarStatus($this->t->id, $order->id, UnifiedOrder::STATUS_DELIVERED);

        $this->assertSame(UnifiedOrder::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_tukar_status_merekod_timeline(): void
    {
        $s = app(UnifiedOrderService::class);
        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        $s->tukarStatus($this->t->id, $order->id, UnifiedOrder::STATUS_CONFIRMED);

        $events = OrderTimelineEvent::where('unified_order_id', $order->id)->get();
        $this->assertGreaterThanOrEqual(2, $events->count());
        $this->assertTrue($events->contains('event_type', OrderTimelineEvent::EVENT_STATUS_CHANGED));
    }

    // -------------------------------------------------------------------
    // Stok
    // -------------------------------------------------------------------

    public function test_rizab_stok_dicipta(): void
    {
        $s = app(UnifiedOrderService::class);
        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        // Tiada product_id → tiada rizab (item digital / luar sistem)
        $this->assertSame(0, OrderStockReservation::where('unified_order_id', $order->id)->count());
    }

    public function test_rizab_stok_dengan_product_id(): void
    {
        $s = app(UnifiedOrderService::class);
        $product = \App\Models\Item::query()->first();

        if (! $product) {
            $this->markTestSkipped('Tiada Item dalam pangkalan data');
        }

        $order = $s->ingest($this->t->id, [
            'items' => [$this->item(['product_id' => $product->id])],
        ]);

        $rizab = OrderStockReservation::where('unified_order_id', $order->id)->first();

        $this->assertNotNull($rizab);
        $this->assertSame(OrderStockReservation::STATUS_RESERVED, $rizab->status);
    }

    // -------------------------------------------------------------------
    // Bayaran
    // -------------------------------------------------------------------

    public function test_rekod_bayaran_sepenuh(): void
    {
        $s = app(UnifiedOrderService::class);
        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        $selesai = $s->rekodBayaran($this->t->id, $order->id, 100.00);

        $this->assertSame(UnifiedOrder::PAYMENT_PAID, $selesai->payment_status);
        $this->assertSame(100.0, (float) $selesai->paid_amount);
        $this->assertTrue($selesai->isPaid());
    }

    public function test_rekod_bayaran_separa(): void
    {
        $s = app(UnifiedOrderService::class);
        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        $selesai = $s->rekodBayaran($this->t->id, $order->id, 40.00);

        $this->assertSame(UnifiedOrder::PAYMENT_PARTIAL, $selesai->payment_status);
        $this->assertSame(60.0, $selesai->bakiBayaran());
    }

    // -------------------------------------------------------------------
    // Ringkasan
    // -------------------------------------------------------------------

    public function test_ringkasan_jualan(): void
    {
        $s = app(UnifiedOrderService::class);

        $s->ingest($this->t->id, [
            'source_platform' => 'shopee',
            'items' => [$this->item(['quantity' => 1, 'unit_price' => 100])],
        ]);

        $s->ingest($this->t->id, [
            'source_platform' => 'lazada',
            'items' => [$this->item(['quantity' => 2, 'unit_price' => 50])],
        ]);

        $ringkasan = $s->ringkasan($this->t->id);

        $this->assertSame(2, $ringkasan['jumlah_pesanan']);
        $this->assertSame(200.0, $ringkasan['jumlah_jualan']);
        $this->assertCount(2, $ringkasan['ikut_platform']);
    }

    public function test_ringkasan_kosong(): void
    {
        $s = app(UnifiedOrderService::class);

        $ringkasan = $s->ringkasan($this->t->id);

        $this->assertSame(0, $ringkasan['jumlah_pesanan']);
        $this->assertSame(0.0, $ringkasan['jumlah_jualan']);
    }

    // -------------------------------------------------------------------
    // Channel binding & sync
    // -------------------------------------------------------------------

    public function test_binding_petakan_status(): void
    {
        $binding = OrderChannelBinding::create([
            'tenant_id' => $this->t->id,
            'platform' => 'shopee',
            'status_map' => ['READY' => UnifiedOrder::STATUS_PACKED],
        ]);

        $this->assertSame(UnifiedOrder::STATUS_PACKED, $binding->petakanStatus('READY'));
        $this->assertNull($binding->petakanStatus('TIDAK_DIKETAHUI'));
    }

    public function test_binding_mapping_balikan(): void
    {
        $binding = OrderChannelBinding::create([
            'tenant_id' => $this->t->id,
            'platform' => 'shopee',
            'status_map' => ['READY' => UnifiedOrder::STATUS_PACKED],
        ]);

        $this->assertSame('READY', $binding->statusPlatform(UnifiedOrder::STATUS_PACKED));
    }

    public function test_sync_platform_tiada_binding_dilewati(): void
    {
        $s = app(UnifiedOrderService::class);

        $hasil = $s->sinkronPlatform($this->t->id, 'shopee', []);

        $this->assertSame(OrderSyncLog::STATUS_SKIPPED, $hasil['status']);
    }

    public function test_sync_platform_dengan_binding(): void
    {
        $s = app(UnifiedOrderService::class);

        OrderChannelBinding::create([
            'tenant_id' => $this->t->id,
            'platform' => 'shopee',
            'is_active' => true,
        ]);

        $hasil = $s->sinkronPlatform($this->t->id, 'shopee', [[
            'source_id' => 'SHP-9',
            'status' => 'READY_TO_SHIP',
            'items' => [$this->item()],
        ]]);

        $this->assertSame(OrderSyncLog::STATUS_SUCCESS, $hasil['status']);
        $this->assertSame(1, $hasil['synced']);
        $this->assertSame(1, OrderSyncLog::where('tenant_id', $this->t->id)->count());
    }

    // -------------------------------------------------------------------
    // Tenant isolation
    // -------------------------------------------------------------------

    public function test_pesanan_tenant_lain_tidak_nampak(): void
    {
        $s = app(UnifiedOrderService::class);
        $order = $s->ingest($this->t->id, ['items' => [$this->item()]]);

        [$u2, $t2] = $this->daftar('lawan@orders.my', 'Lawan Sdn Bhd');
        $this->dalamTenant($t2);

        $this->assertNull(UnifiedOrder::find($order->id));
        $this->assertSame(0, UnifiedOrderItem::where('unified_order_id', $order->id)->count());
    }

    // -------------------------------------------------------------------
    // API
    // -------------------------------------------------------------------

    public function test_api_senarai_pesanan(): void
    {
        app(UnifiedOrderService::class)->ingest($this->t->id, ['items' => [$this->item()]]);

        $res = $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/orders')
            ->assertOk();

        $this->assertCount(1, $res->json('data'));
    }

    public function test_api_cipta_pesanan(): void
    {
        $res = $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/orders', [
                'customer_name' => 'Siti Aminah',
                'items' => [$this->item()],
            ])
            ->assertCreated();

        $this->assertNotEmpty($res->json('data.order_no'));
    }

    public function test_api_ringkasan(): void
    {
        app(UnifiedOrderService::class)->ingest($this->t->id, ['items' => [$this->item()]]);

        $this->sebagai($this->u, $this->t)
            ->getJson('/api/v1/orders/summary')
            ->assertOk();
    }

    public function test_api_tukar_status(): void
    {
        $order = app(UnifiedOrderService::class)->ingest($this->t->id, ['items' => [$this->item()]]);

        $this->sebagai($this->u, $this->t)
            ->postJson("/api/v1/orders/{$order->id}/status", [
                'status' => UnifiedOrder::STATUS_CONFIRMED,
            ])
            ->assertOk();

        $this->assertSame(UnifiedOrder::STATUS_CONFIRMED, $order->fresh()->status);
    }

    public function test_api_channel_binding(): void
    {
        $this->sebagai($this->u, $this->t)
            ->postJson('/api/v1/orders/channels', [
                'platform' => 'shopee',
                'display_name' => 'Shopee MY',
                'stock_sync_enabled' => true,
            ])
            ->assertCreated();

        $this->dalamTenant($this->t);
        $this->assertSame(1, OrderChannelBinding::where('tenant_id', $this->t->id)->count());
    }

    public function test_api_timeline(): void
    {
        $order = app(UnifiedOrderService::class)->ingest($this->t->id, ['items' => [$this->item()]]);

        $this->sebagai($this->u, $this->t)
            ->getJson("/api/v1/orders/{$order->id}/timeline")
            ->assertOk();
    }
}
