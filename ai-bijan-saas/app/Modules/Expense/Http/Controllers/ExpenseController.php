<?php

namespace App\Modules\Expense\Http\Controllers;

use App\Modules\Expense\Models\Expense;
use App\Modules\Expense\Models\ExpenseCategory;
use App\Modules\Expense\Models\ExpenseClaim;
use App\Modules\Expense\Models\ExpenseReceipt;
use App\Modules\Expense\Services\ExpenseService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;

/**
 * API B-Expense (BAB 89 P1).
 *
 * ⚠️ SEMUA laluan statik DIDAHULUKAN sebelum `/{id}` supaya
 * `summary`, `categories`, `pending-approvals` tidak ditangkap oleh `/{id}`.
 */
class ExpenseController extends Controller
{
    public function __construct(private ExpenseService $servis)
    {
    }

    private function tenantId(): int
    {
        return (int) app(TenantContext::class)->id();
    }

    /**
     * ID pengguna semasa ( Sanctum). 0 jika tiada (konteks job/scheduler).
     */
    private function userId(): int
    {
        return (int) (auth()->id() ?? 0);
    }

    // ---------------------------------------------------------------
    // STATIK (mesti di atas sekali)
    // ---------------------------------------------------------------

    public function summary(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->ringkasan(
                $this->tenantId(),
                $request->query('dari'),
                $request->query('hingga')
            ),
        ]);
    }

    public function categories(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->servis->senaraiKategori($this->tenantId()),
        ]);
    }

    public function pendingApprovals(): JsonResponse
    {
        $tenantId = $this->tenantId();

        return response()->json([
            'success' => true,
            'data' => [
                'perbelanjaan' => Expense::query()
                    ->where('tenant_id', $tenantId)
                    ->where('status', Expense::STATUS_SUBMITTED)
                    ->orderBy('expense_date')
                    ->get(),
                'tuntutan' => ExpenseClaim::query()
                    ->where('tenant_id', $tenantId)
                    ->where('status', ExpenseClaim::STATUS_SUBMITTED)
                    ->orderBy('claim_date')
                    ->get(),
            ],
        ]);
    }

    public function suggestCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'merchant' => ['nullable', 'string', 'max:180'],
            'ocr_text' => ['nullable', 'string'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->servis->cadangKategori(
                $this->tenantId(),
                $data['merchant'] ?? '',
                $data['ocr_text'] ?? null
            ),
        ]);
    }

    public function duplicateCheck(Request $request): JsonResponse
    {
        $data = $request->validate([
            'receipt_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->servis->cariPendua($this->tenantId(), (int) $data['receipt_id']),
        ]);
    }

    // ---------------------------------------------------------------
    // KATEGORI
    // ---------------------------------------------------------------

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'max:150'],
            'name_bm' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'chart_of_account_id' => ['nullable', 'integer'],
            'requires_receipt' => ['nullable', 'boolean'],
            'receipt_threshold' => ['nullable', 'numeric', 'min:0'],
            'requires_approval' => ['nullable', 'boolean'],
            'is_billable' => ['nullable', 'boolean'],
            'is_tax_deductible' => ['nullable', 'boolean'],
            'merchant_keywords' => ['nullable', 'array'],
        ]);

        $kategori = $this->servis->ciptaKategori($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $kategori], 201);
    }

    public function seedCategories(): JsonResponse
    {
        $jumlah = $this->servis->janaKategoriLalai($this->tenantId());

        return response()->json([
            'success' => true,
            'created' => $jumlah,
            'message' => $jumlah > 0
                ? "{$jumlah} kategori lalai dijana."
                : 'Semua kategori lalai telah wujud.',
        ]);
    }

    // ---------------------------------------------------------------
    // PERBELANJAAN
    // ---------------------------------------------------------------

    public function index(Request $request): JsonResponse
    {
        $query = Expense::query()->where('tenant_id', $this->tenantId());

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->query('category_id')) {
            $query->where('expense_category_id', (int) $categoryId);
        }

        if ($dari = $request->query('dari')) {
            $query->where('expense_date', '>=', $dari);
        }

        if ($hingga = $request->query('hingga')) {
            $query->where('expense_date', '<=', $hingga);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('expense_date')->limit(200)->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'expense_category_id' => ['nullable', 'integer'],
            'vendor_name' => ['nullable', 'string', 'max:180'],
            'vendor_invoice_no' => ['nullable', 'string', 'max:80'],
            'expense_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'currency' => ['nullable', 'string', 'size:3'],
            'exchange_rate' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_type' => ['nullable', 'string', 'max:20'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'is_billable' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:300'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:30'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.expense_category_id' => ['nullable', 'integer'],
        ]);

        $perbelanjaan = $this->servis->ciptaPerbelanjaan($this->tenantId(), $data);

        return response()->json(['success' => true, 'data' => $perbelanjaan], 201);
    }

    public function show(int $id): JsonResponse
    {
        $perbelanjaan = Expense::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $perbelanjaan,
            'category' => $perbelanjaan->category,
            'items' => $perbelanjaan->items()->get(),
            'receipts' => $perbelanjaan->receipts()->get(),
        ]);
    }

    public function submit(int $id): JsonResponse
    {
        try {
            $perbelanjaan = $this->servis->hantarUntukKelulusan($this->tenantId(), $id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $perbelanjaan]);
    }

    public function approve(Request $request, int $id): JsonResponse
    {
        $userId = $this->userId();

        try {
            $perbelanjaan = $this->servis->luluskan(
                $this->tenantId(),
                $id,
                $userId,
                $request->input('note')
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $perbelanjaan]);
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $userId = $this->userId();

        try {
            $perbelanjaan = $this->servis->tolak($this->tenantId(), $id, $userId, $data['reason']);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $perbelanjaan]);
    }

    public function post(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'bank_account_id' => ['required', 'integer'],
        ]);

        try {
            $perbelanjaan = $this->servis->postKeJurnal(
                $this->tenantId(),
                $id,
                (int) $data['bank_account_id']
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $perbelanjaan]);
    }

    public function markPaid(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $perbelanjaan = $this->servis->tandakanDibayar(
                $this->tenantId(),
                $id,
                (float) $data['amount']
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $perbelanjaan]);
    }

    // ---------------------------------------------------------------
    // RESIT
    // ---------------------------------------------------------------

    public function uploadReceipt(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'file_path' => ['required', 'string', 'max:400'],
            'file_mime' => ['nullable', 'string', 'max:100'],
            'file_size' => ['nullable', 'integer'],
            'file_hash' => ['nullable', 'string', 'max:64'],
            'document_id' => ['nullable', 'integer'],
            'merchant_name' => ['nullable', 'string', 'max:180'],
            'receipt_date' => ['nullable', 'date'],
            'receipt_total' => ['nullable', 'numeric'],
            'receipt_number' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $resit = $this->servis->muatResit($this->tenantId(), $data, $id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $resit], 201);
    }

    public function verifyReceipt(Request $request, int $id): JsonResponse
    {
        $userId = $this->userId();

        $resit = $this->servis->sahkanResit($this->tenantId(), $id, $userId);

        return response()->json(['success' => true, 'data' => $resit]);
    }

    public function receipts(Request $request): JsonResponse
    {
        $query = ExpenseReceipt::query()->where('tenant_id', $this->tenantId());

        if ($request->boolean('duplicates_only')) {
            $query->where('is_duplicate_suspected', true);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('id')->limit(200)->get(),
        ]);
    }

    // ---------------------------------------------------------------
    // TUNTUTAN
    // ---------------------------------------------------------------

    public function claims(Request $request): JsonResponse
    {
        $query = ExpenseClaim::query()->where('tenant_id', $this->tenantId());

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($claimantId = $request->query('claimant_user_id')) {
            $query->where('claimant_user_id', (int) $claimantId);
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('claim_date')->limit(200)->get(),
        ]);
    }

    public function storeClaim(Request $request): JsonResponse
    {
        $userId = $this->userId();

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'purpose' => ['nullable', 'string'],
            'claim_type' => ['nullable', Rule::in(ExpenseClaim::TYPES)],
            'claim_date' => ['required', 'date'],
            'period_start' => ['nullable', 'date'],
            'period_end' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'string', 'max:40'],
            'mileage_distance_km' => ['nullable', 'numeric', 'min:0'],
            'mileage_rate' => ['nullable', 'numeric', 'min:0'],
            'per_diem_days' => ['nullable', 'integer', 'min:0'],
            'per_diem_rate' => ['nullable', 'numeric', 'min:0'],
            'per_diem_location' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.item_date' => ['required_with:items', 'date'],
            'items.*.description' => ['required_with:items', 'string', 'max:300'],
            'items.*.vendor_name' => ['nullable', 'string', 'max:180'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.expense_category_id' => ['nullable', 'integer'],
            'items.*.receipt_required' => ['nullable', 'boolean'],
        ]);

        $tuntutan = $this->servis->ciptaTuntutan($this->tenantId(), $userId, $data);

        return response()->json(['success' => true, 'data' => $tuntutan], 201);
    }

    public function showClaim(int $id): JsonResponse
    {
        $tuntutan = ExpenseClaim::query()
            ->where('tenant_id', $this->tenantId())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $tuntutan,
            'items' => $tuntutan->items()->get(),
            'receipts' => $tuntutan->receipts()->get(),
            'approvals' => $tuntutan->approvals()->get(),
        ]);
    }

    public function submitClaim(int $id): JsonResponse
    {
        $userId = $this->userId();

        try {
            $tuntutan = $this->servis->hantarTuntutan($this->tenantId(), $id, $userId);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $tuntutan]);
    }

    public function approveClaim(Request $request, int $id): JsonResponse
    {
        $userId = $this->userId();

        try {
            $tuntutan = $this->servis->luluskanTuntutan(
                $this->tenantId(),
                $id,
                $userId,
                $request->input('comment')
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $tuntutan]);
    }

    public function rejectClaim(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $userId = $this->userId();

        try {
            $tuntutan = $this->servis->tolakTuntutan(
                $this->tenantId(),
                $id,
                $userId,
                $data['reason']
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $tuntutan]);
    }

    public function reimburseClaim(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $userId = $this->userId();

        try {
            $tuntutan = $this->servis->tandakanTuntutanDibayar(
                $this->tenantId(),
                $id,
                $userId,
                (float) $data['amount']
            );
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $tuntutan]);
    }

    public function uploadClaimReceipt(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'file_path' => ['required', 'string', 'max:400'],
            'file_mime' => ['nullable', 'string', 'max:100'],
            'file_size' => ['nullable', 'integer'],
            'file_hash' => ['nullable', 'string', 'max:64'],
            'document_id' => ['nullable', 'integer'],
            'merchant_name' => ['nullable', 'string', 'max:180'],
            'receipt_date' => ['nullable', 'date'],
            'receipt_total' => ['nullable', 'numeric'],
            'receipt_number' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            $resit = $this->servis->muatResit($this->tenantId(), $data, null, $id);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        return response()->json(['success' => true, 'data' => $resit], 201);
    }
}
