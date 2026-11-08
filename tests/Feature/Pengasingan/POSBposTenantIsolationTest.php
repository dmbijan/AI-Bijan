<?php

namespace Tests\Feature\Pengasingan;

use App\Modules\POSBpos\Models\PosBranchInvoice;
use App\Modules\POSBpos\Models\PosDeliverySync;
use App\Modules\POSBpos\Models\PosFoodCourt;
use App\Modules\POSBpos\Models\PosGiftCard;
use App\Modules\POSBpos\Models\PosOnlineStore;
use App\Modules\POSBpos\Models\PosStaffAttendance;
use App\Modules\POSBpos\Models\PosTableReservation;
use Tests\Support\UjianPengasingan;

/**
 * Pengasingan tenant — modul POSBpos (ciri POS lanjutan daripada B-POS).
 *
 * Ditambah 9 Nov 2026. Kad hadiah menyimpan nilai prabayar pelanggan;
 * bil cawangan dan bayaran langganan adalah data kewangan.
 */
class POSBposTenantIsolationTest extends UjianPengasingan
{
    protected string $modul = 'POSBpos';

    protected array $model = [
        PosGiftCard::class,
        PosTableReservation::class,
        PosStaffAttendance::class,
        PosFoodCourt::class,
        PosOnlineStore::class,
        PosBranchInvoice::class,
        PosDeliverySync::class,
    ];

    protected ?string $laluanApi = '/api/v1/pos-bpos/gift-cards';

    protected ?string $namaRahsia = 'Kad Hadiah Rahsia Tenant Satu';

    protected function isi(): array
    {
        static $n = 0;
        $n++;

        switch ($this->rekodSemasa) {
            case PosGiftCard::class:
                return [
                    'code' => 'GC-RAHSIA-'.$n,
                    'initial_balance_sen' => 5000,
                    'balance_sen' => 5000,
                    'status' => PosGiftCard::STATUS_ACTIVE,
                    'note' => 'Kad Hadiah Rahsia Tenant Satu '.$n,
                ];

            case PosTableReservation::class:
                return [
                    'reservation_no' => 'RSV-RAHSIA-'.$n,
                    'guest_name' => 'Tetamu Rahsia Tenant Satu '.$n,
                    'party_size' => 4,
                    'reserved_at' => now()->addDay(),
                    'status' => PosTableReservation::STATUS_PENDING,
                ];

            case PosStaffAttendance::class:
                return [
                    'user_id' => $n + 1000,
                    'attendance_date' => now()->toDateString(),
                    'status' => PosStaffAttendance::STATUS_PRESENT,
                ];

            case PosFoodCourt::class:
                return [
                    'name' => 'Food Court Rahsia Tenant Satu '.$n,
                    'code' => 'FC'.$n,
                ];

            case PosOnlineStore::class:
                return [
                    'name' => 'Kedai Online Rahsia Tenant Satu '.$n,
                    'slug' => 'kedai-rahsia-'.$n,
                ];

            case PosBranchInvoice::class:
                return [
                    'invoice_no' => 'BRN-RAHSIA-'.$n,
                    'branch_name' => 'Cawangan Rahsia Tenant Satu '.$n,
                    'billing_month' => now()->startOfMonth(),
                    'amount_sen' => 1800,
                ];

            default:
                return [
                    'client_uuid' => 'sync-rahsia-'.$n,
                    'payload' => ['sale' => 'Rahsia Tenant Satu '.$n],
                    'status' => PosDeliverySync::STATUS_PENDING,
                ];
        }
    }
}
