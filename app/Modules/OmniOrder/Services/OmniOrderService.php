<?php

namespace App\Modules\OmniOrder\Services;

use App\Modules\OmniOrder\Models\OmniCodPayout;
use App\Modules\OmniOrder\Models\OmniOrder;
use App\Modules\Orders\Services\UnifiedOrderService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * OmniOrderService — pesanan omnichannel & perlindungan hasil.
 *
 * Berasal daripada Luxana (luxana.com.my):
 *   - satu baris gilir untuk 5+ saluran jualan
 *   - kelulusan pukal, cetak AWB, push ke kurier
 *   - 5 alat perlindungan hasil: COD Tracker, Duplicate Alert,
 *     Delivery Recovery, High-Risk Flag, High-Value Track
 */
class OmniOrderService
{
    public function __construct(private TenantContext $ctx) {}

    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    /**
     * Terima pesanan daripada saluran luaran.
     *
     * @param  array<string, mixed>  $data
     */
    public function terimaPesanan(array $data): OmniOrder
    {
        $pesanan = OmniOrder::create([
            'order_no' => $data['order_no'] ?? $this->janaNomborPesanan(),
            'external_order_id' => $data['external_order_id'] ?? null,
            'channel' => $data['channel'] ?? OmniOrder::CHANNEL_MANUAL,
            'status' => OmniOrder::STATUS_NEW,
            'customer_name' => $data['customer_name'] ?? null,
            'customer_phone' => $data['customer_phone'] ?? null,
            'customer_address' => $data['customer_address'] ?? null,
            'customer_city' => $data['customer_city'] ?? null,
            'customer_state' => $data['customer_state'] ?? null,
            'customer_postcode' => $data['customer_postcode'] ?? null,
            'total_sen' => (int) ($data['total_sen'] ?? 0),
            'payment_method' => $data['payment_method'] ?? OmniOrder::PAYMENT_PREPAID,
            'payment_status' => $data['payment_status'] ?? 'unpaid',
            'courier' => $data['courier'] ?? null,
            'items' => $data['items'] ?? [],
            'payload' => $data['payload'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Alat perlindungan hasil dijalankan automatik.
        $this->kesanPendua($pesanan);
        $this->nilaikanRisiko($pesanan);
        $this->tandaNilaiTinggi($pesanan);

        return $pesanan->fresh();
    }

    /**
     * Kesan pesanan pendua — telefon + jumlah sama dalam 24 jam.
     */
    public function kesanPendua(OmniOrder $pesanan): bool
    {
        $pendua = OmniOrder::query()
            ->whereKeyNot($pesanan->id)
            ->where('customer_phone', $pesanan->customer_phone)
            ->where('total_sen', $pesanan->total_sen)
            ->where('created_at', '>=', $pesanan->created_at->copy()->subHours(24))
            ->whereNotIn('status', [OmniOrder::STATUS_CANCELLED, OmniOrder::STATUS_REJECTED])
            ->first();

        if ($pendua) {
            $pesanan->update([
                'is_duplicate' => true,
                'duplicate_of_id' => $pendua->id,
            ]);

            return true;
        }

        return false;
    }

    /**
     * Nilaikan risiko pesanan — tanda pembeli berisiko sebelum hantar.
     *
     * Skor 0-100: +40 nombor baru (tiada sejarah), +30 alamat tidak lengkap,
     * +30 lebih 2 pesanan ditolak dalam 90 hari.
     */
    public function nilaikanRisiko(OmniOrder $pesanan): int
    {
        $skor = 0;
        $sebab = [];

        $sejarah = OmniOrder::query()
            ->where('customer_phone', $pesanan->customer_phone)
            ->whereKeyNot($pesanan->id)
            ->count();

        if ($sejarah === 0) {
            $skor += 40;
            $sebab[] = 'nombor baru';
        }

        if (empty($pesanan->customer_address) || empty($pesanan->customer_postcode)) {
            $skor += 30;
            $sebab[] = 'alamat tidak lengkap';
        }

        $ditolak = OmniOrder::query()
            ->where('customer_phone', $pesanan->customer_phone)
            ->whereIn('status', [OmniOrder::STATUS_REJECTED, OmniOrder::STATUS_RETURNED])
            ->where('created_at', '>=', now()->subDays(90))
            ->count();

        if ($ditolak >= 2) {
            $skor += 30;
            $sebab[] = $ditolak.' pesanan ditolak';
        }

        $pesanan->update([
            'risk_score' => min(100, $skor),
            'is_high_risk' => $skor >= 50,
            'risk_reason' => $sebab === [] ? null : implode(', ', $sebab),
        ]);

        return $skor;
    }

    /**
     * Tanda pembeli bernilai tinggi.
     */
    public function tandaNilaiTinggi(OmniOrder $pesanan, ?float $ambangRM = null): bool
    {
        $ambangSen = (int) (($ambangRM ?? config('omni_order.high_value_threshold_rm', 500)) * 100);

        $jumlah = OmniOrder::query()
            ->where('customer_phone', $pesanan->customer_phone)
            ->whereIn('status', [
                OmniOrder::STATUS_DELIVERED,
                OmniOrder::STATUS_SHIPPED,
                OmniOrder::STATUS_PACKED,
                OmniOrder::STATUS_APPROVED,
            ])
            ->sum('total_sen');

        $nilaiTinggi = $jumlah >= $ambangSen;

        if ($nilaiTinggi !== $pesanan->is_high_value) {
            $pesanan->update(['is_high_value' => $nilaiTinggi]);
        }

        return $nilaiTinggi;
    }

    /**
     * Luluskan pesanan secara pukal (bulk approve).
     *
     * @param  array<int, int>  $ids
     * @return array{lulus: int, gagal: int, pendua: int}
     */
    public function lulusPukal(array $ids, bool $langkauPendua = true): array
    {
        $lulus = 0;
        $gagal = 0;
        $pendua = 0;

        DB::transaction(function () use ($ids, $langkauPendua, &$lulus, &$gagal, &$pendua) {
            foreach ($ids as $id) {
                $pesanan = OmniOrder::lockForUpdate()->find($id);

                if (! $pesanan || $pesanan->status !== OmniOrder::STATUS_NEW) {
                    $gagal++;

                    continue;
                }

                if ($langkauPendua && $pesanan->is_duplicate) {
                    $pendua++;

                    continue;
                }

                $pesanan->update([
                    'status' => OmniOrder::STATUS_APPROVED,
                    'approved_at' => now(),
                ]);
                $lulus++;
            }
        });

        return ['lulus' => $lulus, 'gagal' => $gagal, 'pendua' => $pendua];
    }

    /**
     * Cetak AWB (label penghantaran) untuk satu pesanan.
     *
     * @return array{success: bool, awb_url: ?string, tracking_no: ?string, mesej: string}
     */
    public function cetakAwb(OmniOrder $pesanan): array
    {
        if (! $pesanan->bolehHantar()) {
            return [
                'success' => false,
                'awb_url' => null,
                'tracking_no' => null,
                'mesej' => 'Status pesanan tidak membenarkan cetak AWB.',
            ];
        }

        if (! $pesanan->courier) {
            return [
                'success' => false,
                'awb_url' => null,
                'tracking_no' => null,
                'mesej' => 'Kurier belum dipilih.',
            ];
        }

        // Penjanaan AWB sebenar memerlukan API kurier.
        // Tanpa kredensial, hasilkan nombor tracking tempatan yang konsisten.
        $tracking = $pesanan->tracking_no
            ?? strtoupper(substr(str_replace('-', '', $pesanan->courier), 0, 3)).$pesanan->id.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        $pesanan->update([
            'tracking_no' => $tracking,
            'awb_url' => url('/api/v1/omni-order/orders/'.$pesanan->id.'/awb.pdf'),
            'status' => OmniOrder::STATUS_PACKED,
            'packed_at' => now(),
        ]);

        return [
            'success' => true,
            'awb_url' => $pesanan->fresh()->awb_url,
            'tracking_no' => $tracking,
            'mesej' => 'AWB dijana.',
        ];
    }

    /**
     * Push pesanan ke kurier (serahan).
     */
    public function pushKeKurier(OmniOrder $pesanan): OmniOrder
    {
        $pesanan->update([
            'status' => OmniOrder::STATUS_SHIPPED,
            'shipped_at' => now(),
        ]);

        if ($pesanan->cod()) {
            OmniCodPayout::create([
                'omni_order_id' => $pesanan->id,
                'expected_sen' => $pesanan->total_sen,
                'status' => OmniCodPayout::STATUS_IN_TRANSIT,
                'expected_at' => now()->addDays(7),
            ]);
        }

        return $pesanan->fresh();
    }

    /**
     * Pulihkan penghantaran gagal — cadangan tindakan.
     *
     * @return array<string, mixed>
     */
    public function pulihkanPenghantaran(OmniOrder $pesanan, string $sebab): array
    {
        $pesanan->update([
            'delivery_failure_reason' => $sebab,
            'status' => OmniOrder::STATUS_NEW,
        ]);

        $cadangan = match (true) {
            str_contains(strtolower($sebab), 'alamat') => 'Hubungi pelanggan untuk alamat lengkap, kemudian cetak AWB baharu.',
            str_contains(strtolower($sebab), 'tiada orang') => 'Jadualkan semula penghantaran atau tawarkan pickup di cawangan.',
            str_contains(strtolower($sebab), 'tolak') => 'Tawarkan harga semula atau baucar diskaun untuk mengelak pembatalan.',
            default => 'Hubungi kurier untuk siasatan lanjut.',
        };

        return [
            'order_id' => $pesanan->id,
            'sebab' => $sebab,
            'cadangan' => $cadangan,
            'boleh_hantar_semula' => true,
        ];
    }

    /**
     * Teruskan pesanan ke UnifiedOrder (modul Orders).
     *
     * Ini menyambungkan OmniOrder ke ORDER CENTER platform — pesanan dari
     * mana-mana saluran jualan boleh dipusatkan, dirizabkan, dan dijejak
     * bersama marketplace/POS/service.
     *
     * @return array{success: bool, order_id: mixed, mesej: string}
     */
    public function hantarKeUnifiedOrder(OmniOrder $pesanan): array
    {
        try {
            $item = collect($pesanan->items ?? [])->map(fn ($i) => [
                'sku' => $i['sku'] ?? null,
                'name' => $i['name'] ?? $i['title'] ?? 'Item',
                'quantity' => (float) ($i['quantity'] ?? 1),
                'price' => (float) ($i['price'] ?? 0),
                'total' => (float) ($i['total'] ?? (($i['quantity'] ?? 1) * ($i['price'] ?? 0))),
            ])->values()->all();

            $order = app(UnifiedOrderService::class)->ingest(
                (int) $pesanan->tenant_id,
                [
                    'source_platform' => $pesanan->channel,
                    'source' => 'omnichannel',
                    'external_order_id' => $pesanan->external_order_id,
                    'order_no' => $pesanan->order_no,
                    'status' => $pesanan->status,
                    'party' => [
                        'name' => $pesanan->customer_name,
                        'phone' => $pesanan->customer_phone,
                        'address' => $pesanan->customer_address,
                        'city' => $pesanan->customer_city,
                        'state' => $pesanan->customer_state,
                        'postcode' => $pesanan->customer_postcode,
                    ],
                    'totals' => [
                        'subtotal' => $pesanan->total_sen / 100,
                        'grand_total' => $pesanan->total_sen / 100,
                    ],
                    'payment' => [
                        'method' => $pesanan->payment_method,
                        'status' => $pesanan->payment_status,
                    ],
                    'items' => $item,
                    'notes' => 'Dari OmniOrder ('.$pesanan->order_no.')',
                ]
            );

            $pesanan->update([
                'payload' => array_merge($pesanan->payload ?? [], [
                    'unified_order_id' => $order->id ?? null,
                ]),
            ]);

            return [
                'success' => true,
                'order_id' => $order->id ?? null,
                'mesej' => 'Pesanan dihantar ke UnifiedOrder.',
            ];
        } catch (\Throwable $e) {
            Log::warning('OmniOrder: hantar ke UnifiedOrder gagal', [
                'order_id' => $pesanan->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'order_id' => null,
                'mesej' => $e->getMessage(),
            ];
        }
    }

    /**
     * Ringkasan papan pemuka.
     *
     * @return array<string, mixed>
     */
    public function ringkasan(): array
    {
        $semua = OmniOrder::query();

        return [
            'jumlah_pesanan' => (clone $semua)->count(),
            'baru' => (clone $semua)->where('status', OmniOrder::STATUS_NEW)->count(),
            'perlu_lulus' => (clone $semua)->where('status', OmniOrder::STATUS_NEW)->where('is_duplicate', false)->count(),
            'pendua' => (clone $semua)->where('is_duplicate', true)->count(),
            'berisiko' => (clone $semua)->where('is_high_risk', true)->count(),
            'nilai_tinggi' => (clone $semua)->where('is_high_value', true)->count(),
            'dalam_perjalanan' => (clone $semua)->where('status', OmniOrder::STATUS_SHIPPED)->count(),
            'dihantar' => (clone $semua)->where('status', OmniOrder::STATUS_DELIVERED)->count(),
            'nilai_sen' => (int) (clone $semua)->sum('total_sen'),
            'mengikut_saluran' => (clone $semua)->selectRaw('channel, COUNT(*) as jumlah')
                ->groupBy('channel')->pluck('jumlah', 'channel'),
            'cod_tertunggak' => OmniCodPayout::query()
                ->whereIn('status', [OmniCodPayout::STATUS_PENDING, OmniCodPayout::STATUS_IN_TRANSIT])
                ->whereNotNull('expected_at')
                ->where('expected_at', '<', now())
                ->count(),
        ];
    }

    /**
     * Jana nombor pesanan.
     */
    protected function janaNomborPesanan(): string
    {
        return 'OM'.Carbon::now()->format('ymd').str_pad((string) (OmniOrder::count() + 1), 5, '0', STR_PAD_LEFT);
    }
}
