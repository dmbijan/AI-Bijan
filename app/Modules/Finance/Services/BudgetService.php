<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Budget;
use App\Modules\Finance\Models\BudgetLine;
use App\Modules\Finance\Models\ChartOfAccount;
use App\Modules\Finance\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * FIN-005 FP&A: bajet vs sebenar.
 * "Sebenar" = jurnal diposting. Hasil dikira kredit - debit; kos/belanja/aset debit - kredit,
 * supaya kedua-dua bajet dan sebenar positif dalam arah biasa akaun.
 */
class BudgetService
{
    /** Amaran bila belanja melebihi bajet lebih daripada peratus ini, atau hasil kurang daripada bajet. */
    public const AMBANG_PERATUS = 10;

    /**
     * Tetapkan baris bajet. Setiap item: account_id + (months[12] ATAU annual dibahagi sama rata, baki sen ke Disember).
     */
    public function tetapkanBaris(Budget $b, array $items): Budget
    {
        if ($b->status === 'approved') {
            throw new RuntimeException('Bajet yang diluluskan tidak boleh diubah. Buat versi baharu.');
        }

        DB::transaction(function () use ($b, $items) {
            foreach ($items as $it) {
                $akaun = ChartOfAccount::find($it['account_id']);
                if (! $akaun) {
                    throw new RuntimeException("Akaun {$it['account_id']} tidak dijumpai.");
                }
                if (isset($it['months'])) {
                    if (count($it['months']) !== 12) {
                        throw new RuntimeException("Akaun {$akaun->code}: perlu tepat 12 nilai bulanan.");
                    }
                    $bulan = array_map(fn ($v) => round((float) $v, 2), array_values($it['months']));
                } else {
                    $sen = (int) round((float) ($it['annual'] ?? 0) * 100);
                    $asas = intdiv($sen, 12);
                    $bulan = array_fill(0, 12, $asas / 100);
                    $bulan[11] = ($sen - $asas * 11) / 100;
                }
                foreach ($bulan as $i => $amaun) {
                    BudgetLine::updateOrCreate(
                        ['budget_id' => $b->id, 'account_id' => $akaun->id, 'month' => $i + 1],
                        ['amount' => $amaun]
                    );
                }
            }
        });

        return $b->refresh();
    }

    public function lulus(Budget $b, int $userId): Budget
    {
        if ($b->status === 'approved') {
            throw new RuntimeException('Bajet sudah diluluskan.');
        }
        if (! $b->lines()->exists()) {
            throw new RuntimeException('Bajet kosong tidak boleh diluluskan.');
        }
        $b->update(['status' => 'approved', 'approved_by' => $userId, 'approved_at' => now()]);

        return $b;
    }

    /** Laporan bajet vs sebenar bagi bulan $dari..$hingga (1-12) tahun bajet. */
    public function laporan(Budget $b, int $dari = 1, int $hingga = 12): array
    {
        if ($dari < 1 || $hingga > 12 || $dari > $hingga) {
            throw new RuntimeException('Julat bulan tidak sah.');
        }
        $tahun = $b->fiscal_year;
        $mula = sprintf('%04d-%02d-01', $tahun, $dari);
        $tamat = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $tahun, $hingga)));

        $bajet = BudgetLine::where('budget_id', $b->id)->whereBetween('month', [$dari, $hingga])
            ->selectRaw('account_id, SUM(amount) as amt')->groupBy('account_id')->pluck('amt', 'account_id');

        $sebenar = JournalEntryLine::query()
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->whereBetween('journal_entries.entry_date', [$mula, $tamat.' 23:59:59'])
            ->selectRaw('journal_entry_lines.account_id as account_id, SUM(journal_entry_lines.debit - journal_entry_lines.credit) as net')
            ->groupBy('journal_entry_lines.account_id')->pluck('net', 'account_id');

        $ids = collect($bajet->keys())->merge($sebenar->keys())->unique()->values();
        $akaun = ChartOfAccount::whereIn('id', $ids)->get()->keyBy('id');

        $baris = [];
        foreach ($ids as $id) {
            $a = $akaun[$id] ?? null;
            if (! $a || ! in_array($a->type, ['revenue', 'cost_of_goods', 'expense'], true)) {
                continue; // aset/liabiliti tidak dibajet dalam P&L
            }
            $hasil = $a->type === 'revenue';
            $sb = round((float) ($sebenar[$id] ?? 0) * ($hasil ? -1 : 1), 2);
            $bj = round((float) ($bajet[$id] ?? 0), 2);
            $beza = round($sb - $bj, 2);
            $peratus = $bj != 0.0 ? round($beza / $bj * 100, 1) : null;
            // Baik: hasil melebihi bajet / belanja di bawah bajet.
            $buruk = $hasil ? $beza < 0 : $beza > 0;
            $amaran = $buruk && ($peratus === null ? abs($beza) > 0 : abs($peratus) > self::AMBANG_PERATUS);
            $baris[] = [
                'account_id' => $a->id, 'code' => $a->code, 'name' => $a->name_bm ?: $a->name, 'type' => $a->type,
                'budget' => $bj, 'actual' => $sb, 'variance' => $beza, 'variance_pct' => $peratus,
                'favourable' => ! $buruk, 'alert' => $amaran,
            ];
        }
        usort($baris, fn ($x, $y) => [$x['type'] !== 'revenue', $x['code']] <=> [$y['type'] !== 'revenue', $y['code']]);

        $jum = fn (string $medan, array $jenis) => round(array_sum(array_column(array_filter($baris, fn ($r) => in_array($r['type'], $jenis, true)), $medan)), 2);
        $untung = fn (string $m) => round($jum($m, ['revenue']) - $jum($m, ['cost_of_goods', 'expense']), 2);

        return [
            'budget' => ['id' => $b->id, 'name' => $b->name, 'fiscal_year' => $tahun, 'status' => $b->status],
            'period' => ['from' => $mula, 'to' => $tamat],
            'lines' => $baris,
            'totals' => [
                'revenue' => ['budget' => $jum('budget', ['revenue']), 'actual' => $jum('actual', ['revenue'])],
                'costs' => ['budget' => $jum('budget', ['cost_of_goods', 'expense']), 'actual' => $jum('actual', ['cost_of_goods', 'expense'])],
                'profit' => ['budget' => $untung('budget'), 'actual' => $untung('actual'), 'variance' => round($untung('actual') - $untung('budget'), 2)],
            ],
            'alerts' => count(array_filter($baris, fn ($r) => $r['alert'])),
        ];
    }
}
