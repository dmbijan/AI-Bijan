<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\Logistics\Models\Shipment;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul Logistics (penghantaran).
 *
 * Ditambah 27 Okt 2026 (P1-1d). Penghantaran menyimpan nama, telefon dan
 * alamat penerima — data peribadi di bawah PDPA; kebocoran mendedahkan
 * alamat pelanggan pesaing.
 */
class LogisticsTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'Logistics';

    protected array $model = [
        Shipment::class,
    ];

    protected ?string $laluanApi = '/api/v1/logistics/shipments';

    protected ?string $namaRahsia = 'Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        return [
            'shipment_number' => 'SHP-' . str_pad((string) $n, 6, '0', STR_PAD_LEFT),
            'order_type' => 'sales',
            'order_id' => $n,
            'recipient_name' => 'Rahsia Tenant Satu ' . $n,
            'recipient_phone' => '6012345' . str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'shipping_address' => 'No 1, Jalan Rahsia',
            'shipping_city' => 'Kuala Lumpur',
            'shipping_state' => 'WP Kuala Lumpur',
            'shipping_postal_code' => '50000',
        ];
    }
}
