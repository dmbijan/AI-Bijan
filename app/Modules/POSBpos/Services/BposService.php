<?php

namespace App\Modules\POSBpos\Services;

use App\Modules\POSBpos\Models\PosBranchInvoice;
use App\Modules\POSBpos\Models\PosDeliverySync;
use App\Modules\POSBpos\Models\PosFoodCourt;
use App\Modules\POSBpos\Models\PosFoodCourtTable;
use App\Modules\POSBpos\Models\PosGiftCard;
use App\Modules\POSBpos\Models\PosOnlineStore;
use App\Modules\POSBpos\Models\PosQueueCounter;
use App\Modules\POSBpos\Models\PosStaffAttendance;
use App\Modules\POSBpos\Models\PosSubscriptionPayment;
use App\Modules\POSBpos\Models\PosTableReservation;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * BposService — ciri POS lanjutan yang diambil daripada B-POS.
 *
 * Melengkapkan modul `POS` sedia ada dengan:
 *   - kad hadiah (gift card) dengan ledger baki
 *   - tempahan meja
 *   - kehadiran & jadual syif staf
 *   - food court + meja boleh dikongsi
 *   - kaunter nombor giliran
 *   - baris gilir sync offline
 *   - kedai online
 *   - bil cawangan + bayaran langganan
 *
 * Semua wang disimpan sebagai integer sen (elak ralat titik terapung).
 */
class BposService
{
    public function __construct(private TenantContext $ctx) {}

    /**
     * ID tenant semasa.
     */
    public function tenantId(): int
    {
        return (int) $this->ctx->id();
    }

    // ===============================================================
    // KAD HADIAH
    // ===============================================================

    /**
     * Terbitkan kad hadiah baharu.
     */
    public function terbitKadHadiah(int $nilaiSen, array $data = []): PosGiftCard
    {
        return PosGiftCard::create(array_merge([
            'code' => $this->janaKod('GC'),
            'initial_balance_sen' => $nilaiSen,
            'balance_sen' => $nilaiSen,
            'status' => PosGiftCard::STATUS_ACTIVE,
            'expires_at' => $data['expires_at'] ?? now()->addYear(),
            'created_by' => auth()->id(),
        ], $data));
    }

    /**
     * Tebus kad hadiah — potong baki secara atomik.
     *
     * @return array{success: bool, dicaj_sen: int, baki_sen: int, mesej: string}
     */
    public function tebusKadHadiah(string $kod, int $jumlahSen): array
    {
        return DB::transaction(function () use ($kod, $jumlahSen) {
            $kad = PosGiftCard::query()
                ->where('code', $kod)
                ->lockForUpdate()
                ->first();

            if (! $kad) {
                return ['success' => false, 'dicaj_sen' => 0, 'baki_sen' => 0, 'mesej' => 'Kod kad hadiah tidak dijumpai.'];
            }

            if (! $kad->bolehTebus()) {
                return ['success' => false, 'dicaj_sen' => 0, 'baki_sen' => $kad->balance_sen, 'mesej' => 'Kad tidak aktif, tamat tempoh atau baki kosong.'];
            }

            $dicaj = min($jumlahSen, $kad->balance_sen);
            $baki = $kad->balance_sen - $dicaj;

            $kad->update([
                'balance_sen' => $baki,
                'status' => $baki === 0 ? PosGiftCard::STATUS_REDEEMED : PosGiftCard::STATUS_ACTIVE,
                'redeemed_at' => $baki === 0 ? now() : $kad->redeemed_at,
            ]);

            return [
                'success' => true,
                'dicaj_sen' => $dicaj,
                'baki_sen' => $baki,
                'mesej' => 'Kad hadiah berjaya ditebus.',
            ];
        });
    }

    // ===============================================================
    // TEMPAHAN MEJA
    // ===============================================================

    /**
     * Cipta tempahan meja.
     */
    public function tempahMeja(array $data): PosTableReservation
    {
        return PosTableReservation::create(array_merge([
            'reservation_no' => $this->janaKod('RSV'),
            'status' => PosTableReservation::STATUS_PENDING,
            'created_by' => auth()->id(),
        ], $data));
    }

    /**
     * Sahkan tempahan — semak konflik meja dahulu.
     *
     * @return array{success: bool, mesej: string, reservation: ?PosTableReservation}
     */
    public function sahkanTempahan(int $id): array
    {
        $tempahan = PosTableReservation::find($id);

        if (! $tempahan) {
            return ['success' => false, 'mesej' => 'Tempahan tidak dijumpai.', 'reservation' => null];
        }

        $mula = Carbon::parse($tempahan->reserved_at);
        $tamat = $mula->copy()->addMinutes($tempahan->duration_minutes);

        $konflik = PosTableReservation::query()
            ->where('dine_table_id', $tempahan->dine_table_id)
            ->whereKeyNot($tempahan->id)
            ->whereIn('status', [
                PosTableReservation::STATUS_CONFIRMED,
                PosTableReservation::STATUS_SEATED,
            ])
            ->whereBetween('reserved_at', [
                $mula->copy()->subMinutes($tempahan->duration_minutes),
                $tamat,
            ])
            ->exists();

        if ($konflik) {
            return ['success' => false, 'mesej' => 'Meja sudah ditempah pada waktu ini.', 'reservation' => $tempahan];
        }

        $tempahan->update([
            'status' => PosTableReservation::STATUS_CONFIRMED,
            'confirmed_at' => now(),
        ]);

        return ['success' => true, 'mesej' => 'Tempahan disahkan.', 'reservation' => $tempahan->fresh()];
    }

    // ===============================================================
    // KEHADIRAN & SYIF STAF
    // ===============================================================

    /**
     * Rekod masuk kerja.
     */
    public function masukKerja(int $userId, array $data = []): PosStaffAttendance
    {
        $hari = Carbon::today();
        $mulaSyarat = Carbon::parse($data['expected_start'] ?? '09:00');
        $masuk = now();
        $lewat = max(0, $mulaSyarat->diffInMinutes($masuk, false));

        return PosStaffAttendance::updateOrCreate(
            ['user_id' => $userId, 'attendance_date' => $hari->toDateString()],
            [
                'register_id' => $data['register_id'] ?? null,
                'clock_in_at' => $masuk,
                'status' => $lewat > 0 ? PosStaffAttendance::STATUS_LATE : PosStaffAttendance::STATUS_PRESENT,
                'minutes_late' => (int) $lewat,
                'created_by' => auth()->id(),
            ]
        );
    }

    /**
     * Rekod keluar kerja dan kira minit bekerja.
     */
    public function keluarKerja(int $userId): ?PosStaffAttendance
    {
        $rekod = PosStaffAttendance::query()
            ->where('user_id', $userId)
            ->whereDate('attendance_date', Carbon::today()->toDateString())
            ->first();

        if (! $rekod || ! $rekod->clock_in_at) {
            return $rekod;
        }

        $minit = $rekod->clock_in_at->diffInMinutes(now());

        $rekod->update([
            'clock_out_at' => now(),
            'minutes_worked' => (int) $minit,
        ]);

        return $rekod->fresh();
    }

    // ===============================================================
    // FOOD COURT & GILIRAN
    // ===============================================================

    /**
     * Cipta food court dengan meja.
     */
    public function ciptaFoodCourt(array $data, int $bilanganMeja = 0): PosFoodCourt
    {
        return DB::transaction(function () use ($data, $bilanganMeja) {
            $court = PosFoodCourt::create(array_merge($data, ['created_by' => auth()->id()]));

            for ($i = 1; $i <= $bilanganMeja; $i++) {
                PosFoodCourtTable::create([
                    'food_court_id' => $court->id,
                    'table_no' => $court->code.'-'.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'seats' => 4,
                    'status' => PosFoodCourtTable::STATUS_FREE,
                    'pairing_code' => Str::random(6),
                ]);
            }

            return $court->fresh();
        });
    }

    /**
     * Ambil nombor giliran seterusnya (atomik).
     */
    public function nomborGiliran(?int $foodCourtId = null, ?int $registerId = null, string $prefix = 'A'): string
    {
        return DB::transaction(function () use ($foodCourtId, $registerId, $prefix) {
            $kaunter = PosQueueCounter::query()
                ->where('counter_date', Carbon::today()->toDateString())
                ->where('prefix', $prefix)
                ->when($foodCourtId !== null, fn ($q) => $q->where('food_court_id', $foodCourtId))
                ->when($registerId !== null, fn ($q) => $q->where('register_id', $registerId))
                ->lockForUpdate()
                ->first();

            if (! $kaunter) {
                $kaunter = PosQueueCounter::create([
                    'food_court_id' => $foodCourtId,
                    'register_id' => $registerId,
                    'counter_date' => Carbon::today()->toDateString(),
                    'prefix' => $prefix,
                    'last_number' => 0,
                ]);
            }

            $seterusnya = $kaunter->last_number + 1;
            $kaunter->update(['last_number' => $seterusnya]);

            return $prefix.str_pad((string) $seterusnya, 3, '0', STR_PAD_LEFT);
        });
    }

    // ===============================================================
    // BIL CAWANGAN & LANGGANAN
    // ===============================================================

    /**
     * Cipta bil cawangan bulanan.
     */
    public function ciptaBilCawangan(array $data): PosBranchInvoice
    {
        return PosBranchInvoice::create(array_merge([
            'invoice_no' => $this->janaKod('BRN'),
            'billing_month' => Carbon::now()->startOfMonth(),
            'status' => PosBranchInvoice::STATUS_DRAFT,
            'due_at' => now()->addDays(14),
            'issued_by' => auth()->id(),
        ], $data));
    }

    /**
     * Rekod pembayaran langganan (idempoten melalui client_uuid).
     */
    public function rekodBayaranLangganan(array $data): PosSubscriptionPayment
    {
        return PosSubscriptionPayment::updateOrCreate(
            ['client_uuid' => $data['client_uuid'] ?? (string) Str::uuid()],
            array_merge(['status' => PosSubscriptionPayment::STATUS_PENDING], $data)
        );
    }

    /**
     * Sahkan pembayaran dan kemas kini bil.
     */
    public function sahkanBayaran(int $pembayaranId, ?string $rujukan = null): PosSubscriptionPayment
    {
        return DB::transaction(function () use ($pembayaranId, $rujukan) {
            $bayaran = PosSubscriptionPayment::lockForUpdate()->findOrFail($pembayaranId);

            $bayaran->update([
                'status' => PosSubscriptionPayment::STATUS_SUCCEEDED,
                'gateway_reference' => $rujukan ?? $bayaran->gateway_reference,
                'paid_at' => now(),
            ]);

            $invois = $bayaran->invois;

            if ($invois) {
                $dibayar = $invois->amount_paid_sen + $bayaran->amount_sen;

                $invois->update([
                    'amount_paid_sen' => $dibayar,
                    'status' => $dibayar >= $invois->amount_sen
                        ? PosBranchInvoice::STATUS_PAID
                        : $invois->status,
                    'paid_at' => $dibayar >= $invois->amount_sen ? now() : $invois->paid_at,
                ]);
            }

            return $bayaran->fresh();
        });
    }

    // ===============================================================
    // SYNC OFFLINE
    // ===============================================================

    /**
     * Masukkan baris gilir sync.
     */
    public function masukBarisGilirSync(string $clientUuid, array $payload): PosDeliverySync
    {
        return PosDeliverySync::updateOrCreate(
            ['client_uuid' => $clientUuid],
            [
                'payload' => $payload,
                'status' => PosDeliverySync::STATUS_PENDING,
                'attempts' => 0,
                'last_error' => null,
            ]
        );
    }

    /**
     * Tanda baris gilir sebagai berjaya.
     */
    public function syncBerjaya(int $id): ?PosDeliverySync
    {
        $baris = PosDeliverySync::find($id);

        if (! $baris) {
            return null;
        }

        $baris->update([
            'status' => PosDeliverySync::STATUS_SYNCED,
            'synced_at' => now(),
        ]);

        return $baris->fresh();
    }

    /**
     * Tanda baris gilir gagal dengan sebab.
     */
    public function syncGagal(int $id, string $mesej): ?PosDeliverySync
    {
        $baris = PosDeliverySync::find($id);

        if (! $baris) {
            return null;
        }

        $baris->update([
            'status' => PosDeliverySync::STATUS_FAILED,
            'attempts' => $baris->attempts + 1,
            'last_error' => $mesej,
        ]);

        return $baris->fresh();
    }

    /**
     * Statistik baris gilir sync.
     *
     * @return array<string, int>
     */
    public function statistikSync(): array
    {
        return [
            'pending' => PosDeliverySync::query()->where('status', PosDeliverySync::STATUS_PENDING)->count(),
            'synced' => PosDeliverySync::query()->where('status', PosDeliverySync::STATUS_SYNCED)->count(),
            'failed' => PosDeliverySync::query()->where('status', PosDeliverySync::STATUS_FAILED)->count(),
            'conflict' => PosDeliverySync::query()->where('status', PosDeliverySync::STATUS_CONFLICT)->count(),
        ];
    }

    // ===============================================================
    // KEDAI ONLINE
    // ===============================================================

    /**
     * Cipta kedai online.
     */
    public function ciptaKedaiOnline(array $data): PosOnlineStore
    {
        $nama = $data['name'] ?? 'Kedai Online';

        return PosOnlineStore::create(array_merge([
            'name' => $nama,
            'slug' => Str::slug($nama).'-'.Str::random(5),
            'status' => PosOnlineStore::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ], $data));
    }

    /**
     * Terbit kedai online.
     */
    public function terbitKedai(int $id): ?PosOnlineStore
    {
        $kedai = PosOnlineStore::find($id);

        if (! $kedai) {
            return null;
        }

        $kedai->update([
            'status' => PosOnlineStore::STATUS_LIVE,
            'published_at' => now(),
        ]);

        return $kedai->fresh();
    }

    // ===============================================================
    // BANTUAN
    // ===============================================================

    /**
     * Jana kod unik dengan awalan.
     */
    protected function janaKod(string $awalan): string
    {
        return $awalan.'-'.strtoupper(Str::random(10));
    }
}
