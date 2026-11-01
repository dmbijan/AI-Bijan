<?php

namespace App\Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\BankStatementLine;
use App\Modules\Finance\Services\BankReconciliationService;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** FIN-017 Rekonsiliasi Bank. */
class BankReconciliationController extends Controller
{
    public function __construct(private BankReconciliationService $svc) {}

    public function accounts(): JsonResponse
    {
        return response()->json(['data' => BankAccount::orderBy('name')->get()]);
    }

    public function storeAccount(Request $r): JsonResponse
    {
        $d = $r->validate([
            'name' => 'required|string|max:100',
            'bank_name' => 'nullable|string|max:60',
            'account_last4' => 'nullable|digits:4',
            'gl_account_id' => 'nullable|integer|exists:chart_of_accounts,id',
            'currency' => 'nullable|string|size:3',
        ]);
        $a = BankAccount::create($d + ['currency' => 'MYR']);
        app(AuditLogger::class)->log('finance.bank_account_created', $a, null, ['name' => $a->name]);

        return response()->json(['data' => $a], 201);
    }

    public function import(Request $r, int $id): JsonResponse
    {
        $akaun = BankAccount::findOrFail($id);
        $r->validate(['file' => 'required_without:csv|file|mimes:csv,txt|max:5120', 'csv' => 'required_without:file|string|max:5000000']);
        $csv = $r->hasFile('file') ? (string) file_get_contents($r->file('file')->getRealPath()) : (string) $r->input('csv');
        try {
            $hasil = $this->svc->importCsv($akaun, $csv);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        $hasil['auto_matched'] = $this->svc->autoMatch($akaun);
        app(AuditLogger::class)->log('finance.bank_statement_imported', $akaun, null, $hasil);

        return response()->json(['data' => $hasil], 201);
    }

    public function lines(Request $r, int $id): JsonResponse
    {
        $akaun = BankAccount::findOrFail($id);
        $r->validate(['status' => 'nullable|in:unmatched,matched,ignored', 'from' => 'nullable|date', 'to' => 'nullable|date']);
        $q = BankStatementLine::where('bank_account_id', $akaun->id)
            ->when($r->status, fn ($q, $s) => $q->where('status', $s))
            ->when($r->from, fn ($q, $s) => $q->where('txn_date', '>=', $s))
            ->when($r->to, fn ($q, $s) => $q->where('txn_date', '<=', $s))
            ->orderByDesc('txn_date')->orderByDesc('id');

        return response()->json($q->paginate(min(200, (int) $r->input('per_page', 50))));
    }

    public function summary(Request $r, int $id): JsonResponse
    {
        $akaun = BankAccount::findOrFail($id);

        return response()->json(['data' => $this->svc->ringkasan($akaun, $r->input('from'), $r->input('to'))]);
    }

    public function autoMatch(int $id): JsonResponse
    {
        return response()->json(['data' => ['auto_matched' => $this->svc->autoMatch(BankAccount::findOrFail($id))]]);
    }

    public function candidates(int $lineId): JsonResponse
    {
        return response()->json(['data' => $this->svc->calon(BankStatementLine::findOrFail($lineId))]);
    }

    public function match(Request $r, int $lineId): JsonResponse
    {
        $d = $r->validate(['type' => 'required|in:payment,payment_link,cash_deposit', 'id' => 'required|integer']);
        $l = BankStatementLine::findOrFail($lineId);

        return $this->cuba(fn () => $this->svc->padan($l, $d['type'], (int) $d['id'], 'manual', $r->user()->id), 'finance.bank_line_matched', $l);
    }

    public function unmatch(int $lineId): JsonResponse
    {
        $l = BankStatementLine::findOrFail($lineId);

        return $this->cuba(fn () => $this->svc->nyahpadan($l), 'finance.bank_line_unmatched', $l);
    }

    public function ignore(Request $r, int $lineId): JsonResponse
    {
        $l = BankStatementLine::findOrFail($lineId);

        return $this->cuba(fn () => $this->svc->abaikan($l, $r->user()->id), 'finance.bank_line_ignored', $l);
    }

    private function cuba(callable $f, string $peristiwa, BankStatementLine $l): JsonResponse
    {
        try {
            $hasil = $f();
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        app(AuditLogger::class)->log($peristiwa, $l, null, ['matched_type' => $hasil->matched_type, 'matched_id' => $hasil->matched_id, 'amount' => (string) $hasil->amount]);

        return response()->json(['data' => $hasil]);
    }
}
