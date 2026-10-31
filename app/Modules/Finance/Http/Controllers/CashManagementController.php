<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\CashBox;
use App\Modules\Finance\Models\CashCount;
use App\Modules\Finance\Services\CashManagementService;
use App\Modules\POS\Models\CashShift;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** FIN-018 Pengurusan Tunai. */
class CashManagementController extends Controller
{
    public function __construct(private CashManagementService $svc) {}

    public function boxes(): JsonResponse
    {
        return response()->json(['data' => CashBox::orderBy('name')->get()->map(fn ($b) => $b->toArray() + ['balance' => $b->baki()])]);
    }

    public function storeBox(Request $r): JsonResponse
    {
        $d = $r->validate(['name' => 'required|string|max:100', 'type' => 'required|in:petty,safe', 'float_amount' => 'nullable|numeric|min:0|max:1000000']);
        $b = CashBox::create($d + ['float_amount' => 0]);
        app(AuditLogger::class)->log('finance.cash_box_created', $b, null, ['name' => $b->name, 'type' => $b->type]);

        return response()->json(['data' => $b->toArray() + ['balance' => 0.0]], 201);
    }

    public function report(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['from' => 'required|date', 'to' => 'required|date|after_or_equal:from']);

        return response()->json(['data' => $this->svc->laporan(CashBox::findOrFail($id), $d['from'], $d['to'])]);
    }

    public function entry(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'type' => 'required|in:in,out', 'amount' => 'required|numeric|min:0.01|max:1000000',
            'category' => 'nullable|string|max:60', 'description' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:60', 'entry_date' => 'nullable|date|before_or_equal:today',
        ]);
        $peti = CashBox::findOrFail($id);

        return $this->cuba(fn () => $this->svc->rekod($peti, $d['type'], (float) $d['amount'], $d, $r->user()->id), 'finance.cash_entry', 201);
    }

    public function topUp(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['reference' => 'nullable|string|max:60']);
        $peti = CashBox::findOrFail($id);

        return $this->cuba(fn () => $this->svc->tambahFloat($peti, $d['reference'] ?? null, $r->user()->id), 'finance.cash_topup', 201);
    }

    public function deposit(Request $r, int $id): JsonResponse
    {
        $d = $r->validate([
            'bank_account_id' => 'required|integer', 'amount' => 'required|numeric|min:0.01|max:10000000',
            'reference' => 'nullable|string|max:60', 'entry_date' => 'nullable|date|before_or_equal:today',
        ]);
        $peti = CashBox::findOrFail($id);
        $bank = BankAccount::findOrFail($d['bank_account_id']);

        return $this->cuba(fn () => $this->svc->deposit($peti, $bank, (float) $d['amount'], $d['reference'] ?? null, $d['entry_date'] ?? null, $r->user()->id), 'finance.cash_deposit', 201);
    }

    public function receiveDrop(Request $r, int $id): JsonResponse
    {
        $d = $r->validate(['cash_shift_id' => 'required|integer', 'amount' => 'required|numeric|min:0.01']);
        $peti = CashBox::findOrFail($id);
        $syif = CashShift::findOrFail($d['cash_shift_id']);

        return $this->cuba(fn () => $this->svc->terimaDropSyif($peti, $syif, (float) $d['amount'], $r->user()->id), 'finance.cash_drop_received', 201);
    }

    public function count(Request $r): JsonResponse
    {
        $d = $r->validate([
            'cash_box_id' => 'nullable|integer|required_without:cash_shift_id', 'cash_shift_id' => 'nullable|integer',
            'denominations' => 'required|array', 'note' => 'nullable|string|max:255',
        ]);
        $peti = isset($d['cash_box_id']) ? CashBox::findOrFail($d['cash_box_id']) : null;
        $syif = isset($d['cash_shift_id']) ? CashShift::findOrFail($d['cash_shift_id']) : null;

        return $this->cuba(fn () => $this->svc->kira($peti, $syif, $d['denominations'], $d['note'] ?? null, $r->user()->id), 'finance.cash_counted', 201);
    }

    public function adjust(Request $r, int $countId): JsonResponse
    {
        $kiraan = CashCount::findOrFail($countId);

        return $this->cuba(fn () => $this->svc->laraskanIkutKiraan($kiraan, $r->user()->id), 'finance.cash_adjusted', 201);
    }

    public function denominations(): JsonResponse
    {
        return response()->json(['data' => CashManagementService::DENOMINASI]);
    }

    private function cuba(callable $f, string $peristiwa, int $kod = 200): JsonResponse
    {
        try {
            /** @var Model $hasil */
            $hasil = $f();
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        app(AuditLogger::class)->log($peristiwa, $hasil, null, $hasil->only(['type', 'amount', 'variance', 'counted_total', 'cash_box_id', 'cash_shift_id']));

        return response()->json(['data' => $hasil], $kod);
    }
}
