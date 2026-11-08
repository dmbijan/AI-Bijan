<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\OmniOrder\Models\OmniChannelConnection;
use App\Modules\OmniOrder\Models\OmniCodPayout;
use App\Modules\OmniOrder\Models\OmniCourier;
use App\Modules\OmniOrder\Models\OmniOrder;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul OmniOrder (pesanan omnichannel, asal: Luxana).
 *
 * Ditambah 9 Nov 2026. Pesanan mendedahkan nama, telefon dan alamat
 * pelanggan — data peribadi yang paling sensitif dalam platform.
 */
class OmniOrderTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'OmniOrder';

    protected array $model = [
        OmniOrder::class,
        OmniCourier::class,
        OmniChannelConnection::class,
        OmniCodPayout::class,
    ];

    protected ?string $laluanApi = '/api/v1/omni-order/orders';

    protected ?string $namaRahsia = 'Pesanan Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        switch ($this->rekodSemasa) {
            case OmniOrder::class:
                return [
                    'order_no' => 'OM-RAHSIA-'.$n,
                    'channel' => OmniOrder::CHANNEL_TIKTOK,
                    'status' => OmniOrder::STATUS_NEW,
                    'customer_name' => 'Pelanggan Rahsia Tenant Satu '.$n,
                    'customer_phone' => '6012345678'.($n % 10),
                    'customer_address' => 'Alamat Rahsia Tenant Satu '.$n,
                    'total_sen' => 12800,
                    'payment_method' => OmniOrder::PAYMENT_COD,
                ];

            case OmniCourier::class:
                return [
                    'code' => 'KUR'.$n,
                    'name' => 'Kurier Rahsia Tenant Satu '.$n,
                    'supports_cod' => true,
                    'is_active' => true,
                ];

            case OmniChannelConnection::class:
                return [
                    'channel' => 'shopee_'.$n,
                    'name' => 'Shopee Rahsia Tenant Satu '.$n,
                    'shop_id' => 'shop-'.$n,
                    'status' => OmniChannelConnection::STATUS_CONNECTED,
                ];

            default:
                $pesanan = OmniOrder::create([
                    'order_no' => 'OM-COD-'.$n,
                    'channel' => OmniOrder::CHANNEL_SHOPEE,
                    'status' => OmniOrder::STATUS_SHIPPED,
                    'customer_name' => 'Pelanggan COD '.$n,
                    'customer_phone' => '6098765432',
                    'total_sen' => 5600,
                    'payment_method' => OmniOrder::PAYMENT_COD,
                ]);

                return [
                    'omni_order_id' => $pesanan->id,
                    'expected_sen' => 5600,
                    'status' => OmniCodPayout::STATUS_IN_TRANSIT,
                    'expected_at' => now()->addDays(7),
                ];
        }
    }
}
