<?php

namespace App\Modules\POSBpos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\POSBpos\Models\PosBranchInvoice;
use App\Modules\POSBpos\Models\PosDeliverySync;
use App\Modules\POSBpos\Models\PosFoodCourt;
use App\Modules\POSBpos\Models\PosFoodCourtTable;
use App\Modules\POSBpos\Models\PosGiftCard;
use App\Modules\POSBpos\Models\PosOnlineStore;
use App\Modules\POSBpos\Models\PosStaffAttendance;
use App\Modules\POSBpos\Models\PosStaffRosterShift;
use App\Modules\POSBpos\Models\PosSubscriptionPayment;
use App\Modules\POSBpos\Models\PosTableReservation;
use App\Modules\POSBpos\Services\BposService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class POSBposController extends Controller
{
    public function __construct(private BposService $bpos) {}

    // ===============================================================
    // KAD HADIAH
    // ===============================================================

    public function giftCards(Request $request): JsonResponse
    {
        $query = PosGiftCard::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    public function storeGiftCard(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'amount_sen' => ['required', 'integer', 'min:'.config('pos_bpos.gift_card.min_issue_sen', 1000)],
            'issued_to_customer_id' => ['nullable', 'integer'],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $kad = $this->bpos->terbitKadHadiah($data['amount_sen'], $data);

        return response()->json([
            'success' => true,
            'message' => 'Kad hadiah berjaya diterbitkan.',
            'data' => $kad,
        ], 201);
    }

    public function redeemGiftCard(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'code' => ['required', 'string', 'max:60'],
            'amount_sen' => ['required', 'integer', 'min:1'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $hasil = $this->bpos->tebusKadHadiah(
            $v->validated()['code'],
            $v->validated()['amount_sen']
        );

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => [
                'dicaj_sen' => $hasil['dicaj_sen'],
                'baki_sen' => $hasil['baki_sen'],
            ],
        ], $hasil['success'] ? 200 : 422);
    }

    // ===============================================================
    // TEMPAHAN MEJA
    // ===============================================================

    public function reservations(Request $request): JsonResponse
    {
        $query = PosTableReservation::query()->orderByDesc('reserved_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            $query->whereDate('reserved_at', $request->input('date'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    public function storeReservation(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'dine_table_id' => ['nullable', 'integer'],
            'customer_id' => ['nullable', 'integer'],
            'guest_name' => ['required', 'string', 'max:150'],
            'guest_phone' => ['nullable', 'string', 'max:40'],
            'guest_email' => ['nullable', 'email', 'max:180'],
            'party_size' => ['required', 'integer', 'min:1', 'max:'.config('pos_bpos.reservation.max_party_size', 40)],
            'reserved_at' => ['required', 'date', 'after:now'],
            'duration_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $tempahan = $this->bpos->tempahMeja($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Tempahan berjaya dicipta.',
            'data' => $tempahan,
        ], 201);
    }

    public function confirmReservation(int $id): JsonResponse
    {
        $hasil = $this->bpos->sahkanTempahan($id);

        return response()->json([
            'success' => $hasil['success'],
            'message' => $hasil['mesej'],
            'data' => $hasil['reservation'],
        ], $hasil['success'] ? 200 : 422);
    }

    public function cancelReservation(Request $request, int $id): JsonResponse
    {
        $tempahan = PosTableReservation::findOrFail($id);

        $tempahan->update([
            'status' => PosTableReservation::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancel_reason' => $request->input('reason'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tempahan dibatalkan.',
            'data' => $tempahan->fresh(),
        ]);
    }

    // ===============================================================
    // KEHADIRAN & SYIF
    // ===============================================================

    public function attendance(Request $request): JsonResponse
    {
        $query = PosStaffAttendance::query()->orderByDesc('attendance_date');

        if ($request->filled('date')) {
            $query->whereDate('attendance_date', $request->input('date'));
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
        ]);
    }

    public function clockIn(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'user_id' => ['required', 'integer'],
            'register_id' => ['nullable', 'integer'],
            'expected_start' => ['nullable', 'string', 'max:5'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $rekod = $this->bpos->masukKerja((int) $data['user_id'], $data);

        return response()->json([
            'success' => true,
            'message' => 'Masuk kerja direkod.',
            'data' => $rekod,
        ], 201);
    }

    public function clockOut(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'user_id' => ['required', 'integer'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $rekod = $this->bpos->keluarKerja((int) $v->validated()['user_id']);

        if (! $rekod) {
            return response()->json([
                'success' => false,
                'message' => 'Tiada rekod masuk kerja hari ini.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Keluar kerja direkod.',
            'data' => $rekod,
        ]);
    }

    public function roster(Request $request): JsonResponse
    {
        $query = PosStaffRosterShift::query()->orderBy('shift_date');

        if ($request->filled('from')) {
            $query->whereDate('shift_date', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('shift_date', '<=', $request->input('to'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 200))->get(),
        ]);
    }

    public function storeRoster(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'user_id' => ['required', 'integer'],
            'register_id' => ['nullable', 'integer'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required', 'string', 'max:5'],
            'end_time' => ['required', 'string', 'max:5'],
            'role' => ['nullable', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $syif = PosStaffRosterShift::create(array_merge($v->validated(), [
            'created_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Syif berjaya dijadualkan.',
            'data' => $syif,
        ], 201);
    }

    // ===============================================================
    // FOOD COURT & GILIRAN
    // ===============================================================

    public function foodCourts(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => PosFoodCourt::query()->with('meja')->orderBy('name')->get(),
        ]);
    }

    public function storeFoodCourt(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'code' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:1000'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'tables' => ['nullable', 'integer', 'min:0', 'max:200'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $bilangan = (int) ($data['tables'] ?? config('pos_bpos.food_court.default_tables', 10));
        unset($data['tables']);

        $court = $this->bpos->ciptaFoodCourt($data, $bilangan);

        return response()->json([
            'success' => true,
            'message' => 'Food court berjaya dicipta.',
            'data' => $court->load('meja'),
        ], 201);
    }

    public function tables(int $id): JsonResponse
    {
        PosFoodCourt::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => PosFoodCourtTable::query()
                ->where('food_court_id', $id)
                ->orderBy('table_no')
                ->get(),
        ]);
    }

    public function issueQueueNumber(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'food_court_id' => ['nullable', 'integer'],
            'register_id' => ['nullable', 'integer'],
            'prefix' => ['nullable', 'string', 'max:3'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();

        $nombor = $this->bpos->nomborGiliran(
            isset($data['food_court_id']) ? (int) $data['food_court_id'] : null,
            isset($data['register_id']) ? (int) $data['register_id'] : null,
            $data['prefix'] ?? config('pos_bpos.food_court.queue_prefix', 'A')
        );

        return response()->json([
            'success' => true,
            'message' => 'Nombor giliran dikeluarkan.',
            'data' => ['queue_number' => $nombor],
        ], 201);
    }

    // ===============================================================
    // SYNC OFFLINE
    // ===============================================================

    public function syncQueue(Request $request): JsonResponse
    {
        $query = PosDeliverySync::query()->orderBy('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 100))->get(),
            'meta' => ['statistik' => $this->bpos->statistikSync()],
        ]);
    }

    public function pushSync(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'client_uuid' => ['required', 'string', 'max:64'],
            'payload' => ['required', 'array'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $data = $v->validated();
        $baris = $this->bpos->masukBarisGilirSync($data['client_uuid'], $data['payload']);

        return response()->json([
            'success' => true,
            'message' => 'Baris gilir sync diterima.',
            'data' => $baris,
        ], 201);
    }

    public function markSynced(int $id): JsonResponse
    {
        $baris = $this->bpos->syncBerjaya($id);

        if (! $baris) {
            return response()->json(['success' => false, 'message' => 'Baris gilir tidak dijumpai.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Baris gilir ditanda berjaya diselaraskan.',
            'data' => $baris,
        ]);
    }

    public function markFailed(Request $request, int $id): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'error' => ['required', 'string', 'max:1000'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $baris = $this->bpos->syncGagal($id, $v->validated()['error']);

        if (! $baris) {
            return response()->json(['success' => false, 'message' => 'Baris gilir tidak dijumpai.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kegagalan sync direkod.',
            'data' => $baris,
        ]);
    }

    // ===============================================================
    // KEDAI ONLINE
    // ===============================================================

    public function onlineStores(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => PosOnlineStore::query()->orderByDesc('created_at')->get(),
        ]);
    }

    public function storeOnlineStore(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:150'],
            'register_id' => ['nullable', 'integer'],
            'description' => ['nullable', 'string', 'max:2000'],
            'allow_pickup' => ['nullable', 'boolean'],
            'allow_delivery' => ['nullable', 'boolean'],
            'minimum_order_sen' => ['nullable', 'integer', 'min:0'],
            'delivery_fee_sen' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $kedai = $this->bpos->ciptaKedaiOnline($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Kedai online berjaya dicipta.',
            'data' => $kedai,
        ], 201);
    }

    public function publishStore(int $id): JsonResponse
    {
        $kedai = $this->bpos->terbitKedai($id);

        if (! $kedai) {
            return response()->json(['success' => false, 'message' => 'Kedai tidak dijumpai.'], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kedai online diterbitkan.',
            'data' => $kedai,
        ]);
    }

    // ===============================================================
    // BIL CAWANGAN & LANGGANAN
    // ===============================================================

    public function branchInvoices(Request $request): JsonResponse
    {
        $query = PosBranchInvoice::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    public function storeBranchInvoice(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'branch_name' => ['required', 'string', 'max:150'],
            'register_id' => ['nullable', 'integer'],
            'amount_sen' => ['required', 'integer', 'min:0'],
            'billing_month' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $invois = $this->bpos->ciptaBilCawangan($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bil cawangan berjaya dicipta.',
            'data' => $invois,
        ], 201);
    }

    public function subscriptionPayments(Request $request): JsonResponse
    {
        $query = PosSubscriptionPayment::query()->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return response()->json([
            'success' => true,
            'data' => $query->limit((int) $request->input('limit', 50))->get(),
        ]);
    }

    public function storeSubscriptionPayment(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'pos_branch_invoice_id' => ['required', 'integer'],
            'amount_sen' => ['required', 'integer', 'min:1'],
            'client_uuid' => ['nullable', 'string', 'max:64'],
            'gateway_reference' => ['nullable', 'string', 'max:120'],
        ]);

        if ($v->fails()) {
            return $this->ralatValidasi($v);
        }

        $bayaran = $this->bpos->rekodBayaranLangganan($v->validated());

        return response()->json([
            'success' => true,
            'message' => 'Bayaran langganan direkod.',
            'data' => $bayaran,
        ], 201);
    }

    public function confirmSubscriptionPayment(Request $request, int $id): JsonResponse
    {
        $bayaran = $this->bpos->sahkanBayaran($id, $request->input('gateway_reference'));

        return response()->json([
            'success' => true,
            'message' => 'Bayaran disahkan.',
            'data' => $bayaran,
        ]);
    }

    /**
     * @param  \Illuminate\Contracts\Validation\Validator  $v
     */
    protected function ralatValidasi($v): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Input tidak sah.',
            'errors' => $v->errors(),
        ], 422);
    }
}
