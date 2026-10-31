<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\BankStatementLine;
use App\Modules\Finance\Models\CashBox;
use App\Support\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * FIN-006 Treasury: kedudukan tunai semasa dan ramalan aliran tunai harian.
 *
 * Sumber: baki penyata bank terakhir yang diimport, baki peti tunai, invois pelanggan belum bayar (Invoicing)
 * ikut tarikh matang, bil pembekal (accounts_payable) ikut tarikh matang, dan anggaran SST tempoh semasa.
 * Ramalan konservatif: hutang pelanggan yang sudah lewat TIDAK dianggap masuk kecuali diminta.
 */
class TreasuryService
{
    public function kedudukan(): array
    {
        $bank = BankAccount::where('is_active', true)->orderBy('name')->get()->map(function (BankAccount $a) {
            $akhir = BankStatementLine::where('bank_account_id', $a->id)->whereNotNull('balance')
                ->orderByDesc('txn_date')->orderByDesc('id')->first();

            return [
                'id' => $a->id, 'name' => $a->name, 'balance' => $akhir ? (float) $akhir->balance : null,
                'as_of' => $akhir?->txn_date?->toDateString(),
                'stale' => ! $akhir || $akhir->txn_date->lt(now()->subDays(7)->startOfDay()),
            ];
        })->values();
        $tunai = CashBox::where('is_active', true)->orderBy('name')->get()
            ->map(fn (CashBox $b) => ['id' => $b->id, 'name' => $b->name, 'type' => $b->type, 'balance' => $b->baki()])->values();

        $jumlahBank = round((float) $bank->sum(fn ($b) => $b['balance'] ?? 0), 2);
        $jumlahTunai = round((float) $tunai->sum('balance'), 2);

        return [
            'bank_accounts' => $bank,
            'cash_boxes' => $tunai,
            'bank_total' => $jumlahBank,
            'cash_total' => $jumlahTunai,
            'total' => round($jumlahBank + $jumlahTunai, 2),
            'warnings' => array_values(array_filter([
                $bank->contains(fn ($b) => $b['balance'] === null) ? 'Ada akaun bank tanpa penyata dengan baki: import penyata dahulu.' : null,
                $bank->contains(fn ($b) => $b['stale'] && $b['balance'] !== null) ? 'Ada akaun bank dengan penyata lebih 7 hari: baki mungkin lapuk.' : null,
            ])),
        ];
    }

    /** Ramalan harian $hari ke hadapan. */
    public function ramalan(int $hari = 30, bool $kiraLewat = false, float $paras = 0): array
    {
        $tenant = app(TenantContext::class)->id();
        $hariIni = CarbonImmutable::today();
        $akhir = $hariIni->addDays($hari - 1);
        $pos = $this->kedudukan();

        $ar = DB::table('invoices')->where('tenant_id', $tenant)->whereIn('status', ['issued', 'partial', 'overdue'])
            ->where('amount_outstanding', '>', 0)->whereNotNull('due_date')
            ->selectRaw('DATE(due_date) as d, SUM(amount_outstanding) as amt, COUNT(*) as n')->groupByRaw('DATE(due_date)')->get();
        $ap = DB::table('accounts_payable')->where('tenant_id', $tenant)->whereIn('status', ['pending', 'partial_paid', 'overdue'])
            ->where('outstanding_amount', '>', 0)
            ->selectRaw('DATE(due_date) as d, SUM(outstanding_amount) as amt, COUNT(*) as n')->groupByRaw('DATE(due_date)')->get();

        $masuk = [];
        $keluar = [];
        $arLewat = 0.0;
        $apLewat = 0.0;
        foreach ($ar as $r) {
            if ($r->d < $hariIni->toDateString()) {
                $arLewat += (float) $r->amt;
            } elseif ($r->d <= $akhir->toDateString()) {
                $masuk[$r->d][] = ['source' => 'invoices', 'amount' => (float) $r->amt, 'count' => (int) $r->n];
            }
        }
        foreach ($ap as $r) {
            // Bil pembekal lewat dianggap mesti dibayar hari ini (konservatif).
            $d = $r->d < $hariIni->toDateString() ? $hariIni->toDateString() : $r->d;
            if ($r->d < $hariIni->toDateString()) {
                $apLewat += (float) $r->amt;
            }
            if ($d <= $akhir->toDateString()) {
                $keluar[$d][] = ['source' => 'supplier_bills', 'amount' => (float) $r->amt, 'count' => (int) $r->n];
            }
        }
        if ($kiraLewat && $arLewat > 0) {
            $masuk[$hariIni->toDateString()][] = ['source' => 'overdue_invoices', 'amount' => $arLewat, 'count' => 0];
        }

        // Anggaran SST tempoh semasa dibayar pada tarikh akhir hantar.
        $cukai = app(TaxService::class);
        $tempoh = $cukai->tempoh($hariIni->toDateString());
        if ($cukai->tetapan()->sst_registration_no && $tempoh['due'] <= $akhir->toDateString()) {
            $sst = $cukai->drafSst02($tempoh['from'], $tempoh['to'])['total_tax_payable'];
            if ($sst > 0) {
                $keluar[$tempoh['due']][] = ['source' => 'sst', 'amount' => $sst, 'count' => 1];
            }
        }

        $baki = $pos['total'];
        $hariHari = [];
        $terendah = ['date' => $hariIni->toDateString(), 'balance' => $baki];
        $bawahParas = [];
        for ($d = $hariIni; $d->lte($akhir); $d = $d->addDay()) {
            $k = $d->toDateString();
            $in = round(array_sum(array_column($masuk[$k] ?? [], 'amount')), 2);
            $out = round(array_sum(array_column($keluar[$k] ?? [], 'amount')), 2);
            $baki = round($baki + $in - $out, 2);
            $hariHari[] = ['date' => $k, 'in' => $in, 'out' => $out, 'balance' => $baki, 'in_items' => $masuk[$k] ?? [], 'out_items' => $keluar[$k] ?? []];
            if ($baki < $terendah['balance']) {
                $terendah = ['date' => $k, 'balance' => $baki];
            }
            if ($baki < $paras) {
                $bawahParas[] = $k;
            }
        }

        return [
            'opening' => $pos['total'],
            'from' => $hariIni->toDateString(),
            'to' => $akhir->toDateString(),
            'total_in' => round(array_sum(array_column($hariHari, 'in')), 2),
            'total_out' => round(array_sum(array_column($hariHari, 'out')), 2),
            'closing' => $baki,
            'lowest' => $terendah,
            'threshold' => $paras,
            'days_below_threshold' => $bawahParas,
            'overdue_receivables' => round($arLewat, 2),
            'overdue_payables' => round($apLewat, 2),
            'overdue_receivables_included' => $kiraLewat,
            'days' => $hariHari,
            'warnings' => $pos['warnings'],
        ];
    }
}
