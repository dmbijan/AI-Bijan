<?php

namespace App\Modules\Expense\Services;

use App\Modules\DocumentAI\Models\Document;
use App\Modules\DocumentAI\Services\DocumentExtractionService;
use App\Modules\DocumentAI\Services\DocumentOCRService;
use App\Modules\Expense\Models\Expense;
use App\Modules\Expense\Models\ExpenseCategory;
use App\Modules\Expense\Models\ExpenseClaim;
use App\Modules\Expense\Models\ExpenseClaimApproval;
use App\Modules\Expense\Models\ExpenseClaimItem;
use App\Modules\Expense\Models\ExpenseItem;
use App\Modules\Expense\Models\ExpenseReceipt;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Services\JournalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * ExpenseService — enjin pengurusan perbelanjaan (BAB 89 P1).
 *
 * ⚠️ PERINGKATAN KESELAMATAN (WAJIB):
 *  1. Staff upload resit → DRAF sahaja. Tiada auto-posting ke jurnal.
 *  2. Kelulusan manusia WAJIB sebelum perbelanjaan jadi "approved".
 *  3. Staff TIDAK BOLEH meluluskan tuntutan sendiri.
 *  4. AI HANYA mengesyorkan kategori — tidak pernah menetapkan `approved_amount`.
 *  5. Modul ini TIDAK menyimpan wang tunai — pembayaran kekal Finance/Invoicing.
 *  6. Posting ke jurnal HANYA melalui Finance\JournalService.
 */
class ExpenseService
{
    /** Had keselamatan. */
    public const MAX_ITEMS_PER_EXPENSE = 200;
    public const MAX_RECEIPTS_PER_EXPENSE = 20;

    public function __construct(
        private JournalService $journalService,
        private DocumentOCRService $ocrService,
        private DocumentExtractionService $extractionService,
    ) {
    }

    // ===============================================================
    // KATEGORI
    // ===============================================================

    /**
     * Jana katalog kategori lalai (idempoten).
     */
    public function janaKategoriLalai(int $tenantId): int
    {
        $katalog = config('expense.default_categories', []);

        $dicipta = 0;

        foreach ($katalog as $data) {
            $sedia = ExpenseCategory::query()
                ->where('tenant_id', $tenantId)
                ->where('code', $data['code'])
                ->first();

            if ($sedia) {
                continue;
            }

            ExpenseCategory::create(array_merge([
                'tenant_id' => $tenantId,
                'requires_receipt' => true,
                'receipt_threshold' => 50.00,
                'requires_approval' => true,
                'is_billable' => false,
                'is_tax_deductible' => true,
                'is_active' => true,
                'is_system' => true,
                'sort_order' => 0,
            ], $data, ['tenant_id' => $tenantId]));

            $dicipta++;
        }

        return $dicipta;
    }

    public function ciptaKategori(int $tenantId, array $data): ExpenseCategory
    {
        return ExpenseCategory::create(array_merge([
            'tenant_id' => $tenantId,
            'requires_receipt' => true,
            'receipt_threshold' => 50.00,
            'requires_approval' => true,
            'is_billable' => false,
            'is_tax_deductible' => true,
            'is_active' => true,
            'is_system' => false,
            'sort_order' => 0,
        ], $data, ['tenant_id' => $tenantId]));
    }

    /**
     * Katalog kategori aktif.
     */
    public function senaraiKategori(int $tenantId): array
    {
        return ExpenseCategory::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'code' => $k->code,
                'name' => $k->name,
                'name_bm' => $k->name_bm,
                'requires_receipt' => (bool) $k->requires_receipt,
                'receipt_threshold' => (float) $k->receipt_threshold,
                'requires_approval' => (bool) $k->requires_approval,
                'is_billable' => (bool) $k->is_billable,
                'chart_of_account_id' => $k->chart_of_account_id,
            ])
            ->all();
    }

    // ===============================================================
    // PERBELANJAAN
    // ===============================================================

    /**
     * Cipta perbelanjaan (status draf).
     */
    public function ciptaPerbelanjaan(int $tenantId, array $data): Expense
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        if (count($items) > self::MAX_ITEMS_PER_EXPENSE) {
            throw new \RuntimeException('Had baris perbelanjaan dicapai (' . self::MAX_ITEMS_PER_EXPENSE . ').');
        }

        return DB::transaction(function () use ($tenantId, $data, $items) {
            $perbelanjaan = Expense::create(array_merge([
                'tenant_id' => $tenantId,
                'status' => Expense::STATUS_DRAFT,
                'payment_status' => Expense::PAYMENT_UNPAID,
                'currency' => 'MYR',
                'exchange_rate' => 1,
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'amount_myr' => 0,
                'tax_claimable' => true,
                'is_billable' => false,
                'is_flagged' => false,
            ], $data, [
                'tenant_id' => $tenantId,
                'status' => Expense::STATUS_DRAFT,
                'expense_no' => $this->janaNoPerbelanjaan($tenantId),
            ]));

            $this->tambahBaris($tenantId, $perbelanjaan, $items);
            $this->kiraSemulaJumlah($tenantId, $perbelanjaan);

            return $perbelanjaan->fresh();
        });
    }

    /**
     * Tambah/ubah baris perbelanjaan.
     */
    public function tambahBaris(int $tenantId, Expense $perbelanjaan, array $items): void
    {
        $bilangan = $perbelanjaan->items()->count();

        foreach ($items as $data) {
            $bilangan++;

            ExpenseItem::create(array_merge([
                'tenant_id' => $tenantId,
                'expense_id' => $perbelanjaan->id,
                'line_number' => $bilangan,
                'quantity' => 1,
                'unit_price' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ], $data, [
                'tenant_id' => $tenantId,
                'expense_id' => $perbelanjaan->id,
                'line_number' => $bilangan,
            ]));
        }
    }

    /**
     * Kira semula subtotal / cukai / jumlah perbelanjaan.
     */
    public function kiraSemulaJumlah(int $tenantId, Expense $perbelanjaan): Expense
    {
        $baris = $perbelanjaan->items()->get();

        $subtotal = 0.0;
        $cukai = 0.0;

        foreach ($baris as $b) {
            $kasar = (float) $b->quantity * (float) $b->unit_price;
            $subtotal += $kasar;
            $cukai += (float) $b->tax_amount;

            $b->update([
                'total_amount' => round($kasar - (float) $b->discount_amount + (float) $b->tax_amount, 2),
            ]);
        }

        $diskaun = (float) $perbelanjaan->discount_amount;
        $jumlah = round($subtotal - $diskaun + $cukai, 2);
        $kadar = (float) ($perbelanjaan->exchange_rate ?? 1);
        $amountMyr = round($jumlah * ($kadar ?: 1), 2);

        $perbelanjaan->update([
            'subtotal' => round($subtotal, 2),
            'tax_amount' => round($cukai, 2),
            'total_amount' => $jumlah,
            'amount_myr' => $amountMyr,
        ]);

        return $perbelanjaan->fresh();
    }

    /**
     * Hantar perbelanjaan untuk kelulusan.
     *
     * @throws \RuntimeException jika resit wajib tiada
     */
    public function hantarUntukKelulusan(int $tenantId, int $expenseId): Expense
    {
        $perbelanjaan = Expense::query()->where('tenant_id', $tenantId)->findOrFail($expenseId);

        if (! $perbelanjaan->bolehTukarKe(Expense::STATUS_SUBMITTED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$perbelanjaan->status} → " . Expense::STATUS_SUBMITTED . '.'
            );
        }

        $kategori = $perbelanjaan->category;

        // Resit wajib jika jumlah >= ambang kategori
        if ($kategori && $kategori->resitWajib((float) $perbelanjaan->total_amount)) {
            $adaResit = $perbelanjaan->receipts()->where('is_primary', true)->exists();

            if (! $adaResit) {
                throw new \RuntimeException(
                    "Resit wajib untuk perbelanjaan RM" . number_format((float) $perbelanjaan->total_amount, 2) . '.'
                );
            }
        }

        $perbelanjaan->update(['status' => Expense::STATUS_SUBMITTED]);

        return $perbelanjaan->fresh();
    }

    /**
     * Luluskan perbelanjaan.
     *
     * @throws \RuntimeException jika pemohon cuba lulus sendiri
     */
    public function luluskan(int $tenantId, int $expenseId, int $approverUserId, ?string $note = null): Expense
    {
        $perbelanjaan = Expense::query()->where('tenant_id', $tenantId)->findOrFail($expenseId);

        if ((int) $perbelanjaan->created_by_user_id === $approverUserId) {
            throw new \RuntimeException('Staff tidak boleh meluluskan perbelanjaan sendiri.');
        }

        if (! $perbelanjaan->bolehTukarKe(Expense::STATUS_APPROVED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$perbelanjaan->status} → " . Expense::STATUS_APPROVED . '.'
            );
        }

        $perbelanjaan->update([
            'status' => Expense::STATUS_APPROVED,
            'approved_by_user_id' => $approverUserId,
            'approved_at' => now(),
            'internal_notes' => $note ?? $perbelanjaan->internal_notes,
        ]);

        return $perbelanjaan->fresh();
    }

    /**
     * Tolak perbelanjaan.
     */
    public function tolak(int $tenantId, int $expenseId, int $approverUserId, string $sebab): Expense
    {
        $perbelanjaan = Expense::query()->where('tenant_id', $tenantId)->findOrFail($expenseId);

        if ((int) $perbelanjaan->created_by_user_id === $approverUserId) {
            throw new \RuntimeException('Staff tidak boleh menolak perbelanjaan sendiri.');
        }

        if (! $perbelanjaan->bolehTukarKe(Expense::STATUS_REJECTED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$perbelanjaan->status} → " . Expense::STATUS_REJECTED . '.'
            );
        }

        $perbelanjaan->update([
            'status' => Expense::STATUS_REJECTED,
            'approved_by_user_id' => $approverUserId,
            'internal_notes' => $sebab,
        ]);

        return $perbelanjaan->fresh();
    }

    /**
     * Post perbelanjaan ke jurnal kewangan.
     *
     * Memerlukan: status = approved, dan akaun perbelanjaan + akaun bank.
     *
     * @throws \RuntimeException jika prerequisite tidak dipenuhi
     */
    public function postKeJurnal(int $tenantId, int $expenseId, int $bankAccountId): Expense
    {
        $perbelanjaan = Expense::query()->where('tenant_id', $tenantId)->findOrFail($expenseId);

        if (! $perbelanjaan->bolehTukarKe(Expense::STATUS_POSTED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$perbelanjaan->status} → " . Expense::STATUS_POSTED . '.'
            );
        }

        $kategori = $perbelanjaan->category;

        if (! $kategori || ! $kategori->chart_of_account_id) {
            throw new \RuntimeException('Kategori perbelanjaan tiada akaun COA — tidak boleh post ke jurnal.');
        }

        $akaunBank = ChartOfAccount::query()
            ->where('tenant_id', $tenantId)
            ->findOrFail($bankAccountId);

        $jumlah = (float) $perbelanjaan->total_amount;

        $entri = $this->journalService->createJournalEntry([
            'tenant_id' => $tenantId,
            'entry_date' => $perbelanjaan->expense_date,
            'reference_number' => $perbelanjaan->expense_no,
            'description' => "Perbelanjaan: {$perbelanjaan->vendor_name}",
            'narration_bm' => "Perbelanjaan {$perbelanjaan->expense_no} — {$perbelanjaan->vendor_name}",
            'source_module' => 'expense',
            'source_id' => $perbelanjaan->id,
            'lines' => [
                [
                    'account_id' => $kategori->chart_of_account_id,
                    'debit' => $jumlah,
                    'credit' => 0,
                    'description' => $kategori->name,
                ],
                [
                    'account_id' => $akaunBank->id,
                    'debit' => 0,
                    'credit' => $jumlah,
                    'description' => "Bayar: {$perbelanjaan->vendor_name}",
                ],
            ],
        ]);

        $this->journalService->postJournalEntry($entri);

        $perbelanjaan->update([
            'status' => Expense::STATUS_POSTED,
            'custom_fields' => array_merge((array) ($perbelanjaan->custom_fields ?? []), [
                'journal_entry_id' => $entri->id,
                'journal_entry_number' => $entri->entry_number,
            ]),
        ]);

        return $perbelanjaan->fresh();
    }

    /**
     * Tanda perbelanjaan sebagai dibayar.
     */
    public function tandakanDibayar(int $tenantId, int $expenseId, float $jumlah): Expense
    {
        $perbelanjaan = Expense::query()->where('tenant_id', $tenantId)->findOrFail($expenseId);

        if ($perbelanjaan->status !== Expense::STATUS_POSTED) {
            throw new \RuntimeException('Hanya perbelanjaan yang telah dipost boleh ditanda dibayar.');
        }

        $dibayar = round((float) $perbelanjaan->paid_amount + $jumlah, 2);

        $kemasKini = [
            'paid_amount' => $dibayar,
            'payment_status' => $dibayar >= (float) $perbelanjaan->total_amount
                ? Expense::PAYMENT_PAID
                : Expense::PAYMENT_PARTIAL,
        ];

        if ($dibayar >= (float) $perbelanjaan->total_amount) {
            $kemasKini['status'] = Expense::STATUS_PAID;
            $kemasKini['paid_at'] = now();
        }

        $perbelanjaan->update($kemasKini);

        return $perbelanjaan->fresh();
    }

    // ===============================================================
    // RESIT
    // ===============================================================

    /**
     * Muat naik resit dan jalankan OCR + ekstrak.
     *
     * @throws \RuntimeException jika jumlah resit melebihi had
     */
    public function muatResit(int $tenantId, array $data, ?int $expenseId = null, ?int $claimId = null): ExpenseReceipt
    {
        if ($expenseId) {
            $bilangan = ExpenseReceipt::query()
                ->where('tenant_id', $tenantId)
                ->where('expense_id', $expenseId)
                ->count();

            if ($bilangan >= self::MAX_RECEIPTS_PER_EXPENSE) {
                throw new \RuntimeException('Had resit per perbelanjaan dicapai (' . self::MAX_RECEIPTS_PER_EXPENSE . ').');
            }
        }

        $hash = $data['file_hash'] ?? null;

        // Kesan resit pendua
        $pendua = null;
        if ($hash) {
            $pendua = ExpenseReceipt::query()
                ->where('tenant_id', $tenantId)
                ->where('file_hash', $hash)
                ->first();
        }

        $resit = ExpenseReceipt::create(array_merge([
            'tenant_id' => $tenantId,
            'expense_id' => $expenseId,
            'expense_claim_id' => $claimId,
            'file_path' => $data['file_path'],
            'file_mime' => $data['file_mime'] ?? null,
            'file_size' => $data['file_size'] ?? null,
            'file_hash' => $hash,
            'ocr_processed' => false,
            'is_verified' => false,
            'is_primary' => true,
            'is_duplicate_suspected' => $pendua !== null,
            'duplicate_of_receipt_id' => $pendua?->id,
        ], $data, [
            'tenant_id' => $tenantId,
            'expense_id' => $expenseId,
            'expense_claim_id' => $claimId,
        ]));

        // Jalankan OCR + ekstrak (jika ada dokumen DocumentAI)
        if (! empty($data['document_id'])) {
            $this->prosesResit($tenantId, $resit);
        }

        return $resit->fresh();
    }

    /**
     * Jalankan OCR + ekstrak pada resit.
     */
    public function prosesResit(int $tenantId, ExpenseReceipt $resit): ExpenseReceipt
    {
        $document = $resit->document;

        if (! $document) {
            return $resit;
        }

        try {
            $ocr = $this->ocrService->processOCR($document, $resit->file_path);
            $resit->update([
                'ocr_text' => $ocr['text'] ?? null,
                'ocr_confidence' => $ocr['confidence'] ?? null,
                'ocr_processed' => true,
                'ocr_processed_at' => now(),
            ]);

            $ekstrak = $this->extractionService->extractData($document, 'receipt');
            $fields = $ekstrak['data'] ?? $ekstrak['fields'] ?? [];

            $resit->update([
                'extracted_fields' => $fields,
                'merchant_name' => $fields['merchant'] ?? $fields['vendor'] ?? null,
                'receipt_date' => $fields['date'] ?? null,
                'receipt_total' => $fields['total'] ?? null,
                'receipt_number' => $fields['receipt_number'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Expense: OCR resit gagal', [
                'tenant_id' => $tenantId,
                'receipt_id' => $resit->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $resit->fresh();
    }

    /**
     * Sahkan resit oleh manusia.
     */
    public function sahkanResit(int $tenantId, int $receiptId, int $userId, array $data = []): ExpenseReceipt
    {
        $resit = ExpenseReceipt::query()->where('tenant_id', $tenantId)->findOrFail($receiptId);

        $resit->update(array_merge([
            'is_verified' => true,
            'verified_by_user_id' => $userId,
            'verified_at' => now(),
        ], $data));

        // Kemas kini baris tuntutan berkenaan
        if ($resit->expense_claim_id) {
            $baris = ExpenseClaimItem::query()
                ->where('tenant_id', $tenantId)
                ->where('expense_claim_id', $resit->expense_claim_id)
                ->where('has_receipt', false)
                ->get();

            foreach ($baris as $b) {
                $b->update(['has_receipt' => true]);
            }
        }

        return $resit->fresh();
    }

    /**
     * Cadang kategori berdasarkan nama merchant / teks OCR.
     *
     * @return array{category_id: ?int, confidence: float, source: string, candidates: array}
     */
    public function cadangKategori(int $tenantId, string $merchant, ?string $teksOcr = null): array
    {
        $kategori = ExpenseCategory::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $padanan = [];

        foreach ($kategori as $k) {
            $skor = 0.0;

            foreach ((array) ($k->merchant_keywords ?? []) as $kata) {
                $kata = mb_strtolower(trim((string) $kata));

                if ($kata === '') {
                    continue;
                }

                if ($merchant !== '' && str_contains(mb_strtolower($merchant), $kata)) {
                    $skor = max($skor, 0.85);
                } elseif ($teksOcr !== null && str_contains(mb_strtolower($teksOcr), $kata)) {
                    $skor = max($skor, 0.65);
                }
            }

            if ($skor > 0) {
                $padanan[] = [
                    'category_id' => $k->id,
                    'code' => $k->code,
                    'name' => $k->name,
                    'confidence' => $skor,
                ];
            }
        }

        // Isih ikut keyakinan
        usort($padanan, fn ($a, $b) => $b['confidence'] <=> $a['confidence']);

        if ($padanan === []) {
            return [
                'category_id' => null,
                'confidence' => 0.0,
                'source' => 'heuristic',
                'candidates' => [],
            ];
        }

        return [
            'category_id' => $padanan[0]['category_id'],
            'confidence' => $padanan[0]['confidence'],
            'source' => 'heuristic',
            'candidates' => array_slice($padanan, 0, 5),
        ];
    }

    /**
     * Kesan resit pendua dalam tenant.
     */
    public function cariPendua(int $tenantId, int $receiptId): array
    {
        $resit = ExpenseReceipt::query()->where('tenant_id', $tenantId)->findOrFail($receiptId);

        $kandar = [];

        if ($resit->file_hash) {
            $kandar[] = ['file_hash' => $resit->file_hash, 'label' => 'Hash fail sama'];
        }

        if ($resit->merchant_name && $resit->receipt_total) {
            $kandar[] = [
                'merchant_name' => $resit->merchant_name,
                'receipt_total' => (float) $resit->receipt_total,
                'label' => 'Merchant + jumlah sama',
            ];
        }

        $ditemui = [];

        foreach ($kandar as $kriteria) {
            $query = ExpenseReceipt::query()
                ->where('tenant_id', $tenantId)
                ->where('id', '!=', $resit->id);

            if (isset($kriteria['file_hash'])) {
                $query->where('file_hash', $kriteria['file_hash']);
            } else {
                $query->where('merchant_name', $kriteria['merchant_name'])
                    ->where('receipt_total', $kriteria['receipt_total']);
            }

            foreach ($query->get() as $lain) {
                $ditemui[] = [
                    'receipt_id' => $lain->id,
                    'label' => $kriteria['label'],
                    'expense_id' => $lain->expense_id,
                    'claim_id' => $lain->expense_claim_id,
                    'merchant_name' => $lain->merchant_name,
                    'receipt_total' => (float) $lain->receipt_total,
                ];
            }
        }

        $resit->update([
            'is_duplicate_suspected' => $ditemui !== [],
            'duplicate_of_receipt_id' => $ditemui !== [] ? (string) $ditemui[0]['receipt_id'] : null,
        ]);

        return $ditemui;
    }

    // ===============================================================
    // TUNTUTAN
    // ===============================================================

    /**
     * Cipta tuntutan staff (status draf).
     */
    public function ciptaTuntutan(int $tenantId, int $claimantUserId, array $data): ExpenseClaim
    {
        $items = $data['items'] ?? [];
        unset($data['items']);

        $tuntutan = DB::transaction(function () use ($tenantId, $claimantUserId, $data, $items) {
            $tuntutan = ExpenseClaim::create(array_merge([
                'tenant_id' => $tenantId,
                'status' => ExpenseClaim::STATUS_DRAFT,
                'claim_type' => ExpenseClaim::TYPE_REIMBURSEMENT,
                'currency' => 'MYR',
                'total_amount' => 0,
                'approved_amount' => 0,
                'reimbursed_amount' => 0,
                'is_flagged' => false,
            ], $data, [
                'tenant_id' => $tenantId,
                'claimant_user_id' => $claimantUserId,
                'status' => ExpenseClaim::STATUS_DRAFT,
                'claim_no' => $this->janaNoTuntutan($tenantId),
            ]));

            $this->tambahBarisTuntutan($tenantId, $tuntutan, $items);
            $this->kiraSemulaJumlahTuntutan($tenantId, $tuntutan);

            return $tuntutan->fresh();
        });

        // Auto-kira perbatuan / per diem
        $this->kiraSemulaJumlahTuntutan($tenantId, $tuntutan->fresh());

        return $tuntutan->fresh();
    }

    public function tambahBarisTuntutan(int $tenantId, ExpenseClaim $tuntutan, array $items): void
    {
        $bilangan = $tuntutan->items()->count();

        foreach ($items as $data) {
            $bilangan++;

            ExpenseClaimItem::create(array_merge([
                'tenant_id' => $tenantId,
                'expense_claim_id' => $tuntutan->id,
                'line_number' => $bilangan,
                'quantity' => 1,
                'unit_price' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'approved_amount' => 0,
                'has_receipt' => false,
                'receipt_required' => true,
                'is_billable' => false,
            ], $data, [
                'tenant_id' => $tenantId,
                'expense_claim_id' => $tuntutan->id,
                'line_number' => $bilangan,
            ]));
        }
    }

    /**
     * Kira semula jumlah tuntutan (termasuk mileage + per diem).
     */
    public function kiraSemulaJumlahTuntutan(int $tenantId, ExpenseClaim $tuntutan): ExpenseClaim
    {
        $baris = $tuntutan->items()->get();

        $jumlah = 0.0;

        foreach ($baris as $b) {
            $kasar = (float) $b->quantity * (float) $b->unit_price;
            $total = round($kasar + (float) $b->tax_amount, 2);

            $b->update([
                'total_amount' => $total,
                'approved_amount' => $total,
            ]);

            $jumlah += $total;
        }

        // Mileage
        $kilometer = (float) ($tuntutan->mileage_distance_km ?? 0);
        if ($kilometer > 0) {
            $kadar = (float) ($tuntutan->mileage_rate ?? config('expense.mileage.default_rate', 0.60));
            $tuntutan->update([
                'mileage_rate' => $kadar,
                'mileage_amount' => round($kilometer * $kadar, 2),
            ]);
            $jumlah += round($kilometer * $kadar, 2);
        }

        // Per diem
        $hari = (int) ($tuntutan->per_diem_days ?? 0);
        if ($hari > 0) {
            $kadar = (float) ($tuntutan->per_diem_rate ?? config('expense.per_diem.default_rate', 50));
            $tuntutan->update([
                'per_diem_rate' => $kadar,
                'per_diem_amount' => round($hari * $kadar, 2),
            ]);
            $jumlah += round($hari * $kadar, 2);
        }

        $tuntutan->update(['total_amount' => round($jumlah, 2)]);

        return $tuntutan->fresh();
    }

    /**
     * Hantar tuntutan untuk kelulusan.
     */
    public function hantarTuntutan(int $tenantId, int $claimId, int $claimantUserId): ExpenseClaim
    {
        $tuntutan = ExpenseClaim::query()->where('tenant_id', $tenantId)->findOrFail($claimId);

        if ((int) $tuntutan->claimant_user_id !== $claimantUserId) {
            throw new \RuntimeException('Hanya pemohon yang boleh menghantar tuntutan ini.');
        }

        if (! $tuntutan->bolehTukarKe(ExpenseClaim::STATUS_SUBMITTED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$tuntutan->status} → " . ExpenseClaim::STATUS_SUBMITTED . '.'
            );
        }

        if ((float) $tuntutan->total_amount <= 0) {
            throw new \RuntimeException('Tuntutan tiada jumlah — tidak boleh dihantar.');
        }

        $tuntutan->update([
            'status' => ExpenseClaim::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        ExpenseClaimApproval::create([
            'tenant_id' => $tenantId,
            'expense_claim_id' => $tuntutan->id,
            'approver_user_id' => $claimantUserId,
            'step' => 1,
            'action' => ExpenseClaimApproval::ACTION_SUBMIT,
            'previous_status' => ExpenseClaim::STATUS_DRAFT,
            'new_status' => ExpenseClaim::STATUS_SUBMITTED,
            'amount_before' => $tuntutan->total_amount,
            'amount_after' => $tuntutan->total_amount,
        ]);

        return $tuntutan->fresh();
    }

    /**
     * Luluskan tuntutan.
     *
     * @throws \RuntimeException jika pemohon cuba lulus sendiri
     */
    public function luluskanTuntutan(int $tenantId, int $claimId, int $approverUserId, ?string $komen = null): ExpenseClaim
    {
        $tuntutan = ExpenseClaim::query()->where('tenant_id', $tenantId)->findOrFail($claimId);

        if ((int) $tuntutan->claimant_user_id === $approverUserId) {
            throw new \RuntimeException('Staff tidak boleh meluluskan tuntutan sendiri.');
        }

        if (! $tuntutan->bolehTukarKe(ExpenseClaim::STATUS_APPROVED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$tuntutan->status} → " . ExpenseClaim::STATUS_APPROVED . '.'
            );
        }

        $kemasKini = [
            'status' => ExpenseClaim::STATUS_APPROVED,
            'approver_user_id' => $approverUserId,
            'approved_at' => now(),
        ];

        $tuntutan->update($kemasKini);

        ExpenseClaimApproval::create([
            'tenant_id' => $tenantId,
            'expense_claim_id' => $tuntutan->id,
            'approver_user_id' => $approverUserId,
            'step' => (ExpenseClaimApproval::where('tenant_id', $tenantId)->where('expense_claim_id', $tuntutan->id)->count() + 1),
            'action' => ExpenseClaimApproval::ACTION_APPROVE,
            'previous_status' => ExpenseClaim::STATUS_SUBMITTED,
            'new_status' => ExpenseClaim::STATUS_APPROVED,
            'amount_before' => $tuntutan->total_amount,
            'amount_after' => $tuntutan->total_amount,
            'comment' => $komen,
        ]);

        return $tuntutan->fresh();
    }

    /**
     * Tolak tuntutan.
     */
    public function tolakTuntutan(int $tenantId, int $claimId, int $approverUserId, string $sebab): ExpenseClaim
    {
        $tuntutan = ExpenseClaim::query()->where('tenant_id', $tenantId)->findOrFail($claimId);

        if ((int) $tuntutan->claimant_user_id === $approverUserId) {
            throw new \RuntimeException('Staff tidak boleh menolak tuntutan sendiri.');
        }

        if (! $tuntutan->bolehTukarKe(ExpenseClaim::STATUS_REJECTED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$tuntutan->status} → " . ExpenseClaim::STATUS_REJECTED . '.'
            );
        }

        $tuntutan->update([
            'status' => ExpenseClaim::STATUS_REJECTED,
            'approver_user_id' => $approverUserId,
            'rejected_at' => now(),
            'rejection_reason' => $sebab,
        ]);

        ExpenseClaimApproval::create([
            'tenant_id' => $tenantId,
            'expense_claim_id' => $tuntutan->id,
            'approver_user_id' => $approverUserId,
            'step' => (ExpenseClaimApproval::where('tenant_id', $tenantId)->where('expense_claim_id', $tuntutan->id)->count() + 1),
            'action' => ExpenseClaimApproval::ACTION_REJECT,
            'previous_status' => ExpenseClaim::STATUS_SUBMITTED,
            'new_status' => ExpenseClaim::STATUS_REJECTED,
            'amount_before' => $tuntutan->total_amount,
            'amount_after' => 0,
            'comment' => $sebab,
        ]);

        return $tuntutan->fresh();
    }

    /**
     * Tanda tuntutan sebagai dibayar balik.
     */
    public function tandakanTuntutanDibayar(int $tenantId, int $claimId, int $approverUserId, float $jumlah): ExpenseClaim
    {
        $tuntutan = ExpenseClaim::query()->where('tenant_id', $tenantId)->findOrFail($claimId);

        if (! $tuntutan->bolehTukarKe(ExpenseClaim::STATUS_REIMBURSED)) {
            throw new \RuntimeException(
                "Peralihan status tidak sah: {$tuntutan->status} → " . ExpenseClaim::STATUS_REIMBURSED . '.'
            );
        }

        $tuntutan->update([
            'status' => ExpenseClaim::STATUS_REIMBURSED,
            'reimbursed_amount' => round((float) $tuntutan->reimbursed_amount + $jumlah, 2),
            'reimbursed_at' => now(),
        ]);

        ExpenseClaimApproval::create([
            'tenant_id' => $tenantId,
            'expense_claim_id' => $tuntutan->id,
            'approver_user_id' => $approverUserId,
            'step' => (ExpenseClaimApproval::where('tenant_id', $tenantId)->where('expense_claim_id', $tuntutan->id)->count() + 1),
            'action' => ExpenseClaimApproval::ACTION_REIMBURSE,
            'previous_status' => ExpenseClaim::STATUS_APPROVED,
            'new_status' => ExpenseClaim::STATUS_REIMBURSED,
            'amount_before' => $tuntutan->approved_amount,
            'amount_after' => $tuntutan->reimbursed_amount,
        ]);

        return $tuntutan->fresh();
    }

    // ===============================================================
    // RINGKASAN
    // ===============================================================

    /**
     * Ringkasan perbelanjaan untuk dashboard.
     */
    public function ringkasan(int $tenantId, ?string $dari = null, ?string $hingga = null): array
    {
        $dari = $dari ?: now()->startOfMonth()->toDateString();
        $hingga = $hingga ?: now()->endOfMonth()->toDateString();

        $perbelanjaan = Expense::query()
            ->where('tenant_id', $tenantId)
            ->whereBetween('expense_date', [$dari, $hingga]);

        $total = (clone $perbelanjaan)->whereIn('status', [
            Expense::STATUS_APPROVED,
            Expense::STATUS_POSTED,
            Expense::STATUS_PAID,
        ])->sum('amount_myr');

        $tunggakan = Expense::query()
            ->where('tenant_id', $tenantId)
            ->where('payment_status', '!=', Expense::PAYMENT_PAID)
            ->whereIn('status', [Expense::STATUS_APPROVED, Expense::STATUS_POSTED])
            ->sum('amount_myr');

        $ikutKategori = Expense::withoutGlobalScopes()
            ->where('expenses.tenant_id', $tenantId)
            ->whereNull('expenses.deleted_at')
            ->where('expense_categories.tenant_id', $tenantId)
            ->whereBetween('expenses.expense_date', [$dari, $hingga])
            ->whereIn('expenses.status', [Expense::STATUS_APPROVED, Expense::STATUS_POSTED, Expense::STATUS_PAID])
            ->join('expense_categories', 'expenses.expense_category_id', '=', 'expense_categories.id')
            ->selectRaw('expense_categories.name as nama, SUM(expenses.amount_myr) as jumlah, COUNT(expenses.id) as bilangan')
            ->groupBy('expense_categories.name')
            ->orderByDesc('jumlah')
            ->get()
            ->map(fn ($r) => [
                'nama' => $r->nama,
                'jumlah' => round((float) $r->jumlah, 2),
                'bilangan' => (int) $r->bilangan,
            ])
            ->all();

        return [
            'tempoh' => ['dari' => $dari, 'hingga' => $hingga],
            'jumlah_perbelanjaan' => round((float) $total, 2),
            'baki_tertunggak' => round((float) $tunggakan, 2),
            'bilangan_perbelanjaan' => (clone $perbelanjaan)->count(),
            'mengikut_kategori' => $ikutKategori,
            'tuntutan_menunggu' => ExpenseClaim::query()
                ->where('tenant_id', $tenantId)
                ->where('status', ExpenseClaim::STATUS_SUBMITTED)
                ->count(),
            'perbelanjaan_menunggu_kelulusan' => (clone $perbelanjaan)
                ->where('status', Expense::STATUS_SUBMITTED)
                ->count(),
            'resit_pendua' => ExpenseReceipt::query()
                ->where('tenant_id', $tenantId)
                ->where('is_duplicate_suspected', true)
                ->count(),
            'kategori_aktif' => ExpenseCategory::query()
                ->where('tenant_id', $tenantId)
                ->where('is_active', true)
                ->count(),
        ];
    }

    // ===============================================================
    // PENJANA NOMBOR
    // ===============================================================

    public function janaNoPerbelanjaan(int $tenantId): string
    {
        $bil = Expense::withTrashed()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('EXP-%s-%04d', now()->format('Y'), $bil);
    }

    public function janaNoTuntutan(int $tenantId): string
    {
        $bil = ExpenseClaim::withTrashed()
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', now()->year)
            ->count() + 1;

        return sprintf('CLM-%s-%04d', now()->format('Y'), $bil);
    }
}
