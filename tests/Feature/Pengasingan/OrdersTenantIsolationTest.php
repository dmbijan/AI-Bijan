<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Orders\Models\UnifiedOrder;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Orders (pesanan bersatu).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Pesanan bersatu menggabungkan semua saluran
 * jualan; kebocoran bermakna tenant lain boleh membaca jualan, pelanggan dan
 * alamat penghantaran pesaing.
 */
class OrdersTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Orders';

    protected array $model = [
        UnifiedOrder::class,
    ];

    protected ?string $laluanApi = '/api/v1/orders';

    protected ?string $namaRahsia = 'Tanpa Nama Rahsia';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'order_no' => 'ORD-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'source' => 'manual',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'subtotal' => 100,
            'total_amount' => 100,
            'customer_name' => 'Tanpa Nama Rahsia ' . $n,
        ];
    }
}
