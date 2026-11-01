<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\CashBox;
use App\Modules\Finance\Models\CashBoxEntry;
use App\Modules\Finance\Models\CashCount;
use App\Modules\POS\Models\CashShift;
use App\Modules\POS\Models\POSShiftCashMovement;
use App\Modules\POS\Services\CashShiftService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * FIN-018 Pengurusan Tunai: petty cash (imprest), peti besi, kiraan denominasi, deposit ke bank.
 * Semua amaun disimpan bertanda dalam cash_box_entries; baki = jumlah entri (tiada medan baki yang boleh lari).
 */
class CashManagementService
{
    /** Denominasi Ringgit yang beredar. */
    public const DENOMINASI = ['100', '50', '20', '10', '5', '1', '0.50', '0.20', '0.10', '0.05'];

    /** @return array{total: float, denominations: array<string,int>} */
    public function kiraDenominasi(array $input): array
    {
        $bersih = [];
        $jumlahSen = 0;
        foreach ($input as $nilai => $bil) {
            $kunci = $this->kunciDenominasi((string) $nilai);
            if ($kunci === null) {
                throw new RuntimeException("Denominasi tidak sah: {$nilai}");
            }
            if (! is_numeric($bil) || (int) $bil != $bil || (int) $bil < 0) {
                throw new RuntimeException("Bilangan untuk RM{$kunci} mesti nombor bulat ≥ 0.");
            }
            if ((int) $bil === 0) {
                continue;
            }
            $bersih[$kunci] = ($bersih[$kunci] ?? 0) + (int) $bil;
            $jumlahSen += (int) round((float) $kunci * 100) * (int) $bil;
        }

        return ['total' => $jumlahSen / 100, 'denominations' => $bersih];
    }

    public function rekod(CashBox $peti, string $jenis, float $amaun, array $d, ?int $userId): CashBoxEntry
    {
        if (! in_array($jenis, ['in', 'out'], true)) {
            throw new RuntimeException('Jenis entri mesti in atau out.');
        }
        $this->amaunPositif($amaun);

        return DB::transaction(function () use ($peti, $jenis, $amaun, $d, $userId) {
            $this->kunci($peti);
            if ($jenis === 'out' && $amaun > $peti->baki() + 0.004) {
                throw new RuntimeException('Tunai tidak mencukupi: baki RM'.number_format($peti->baki(), 2).'.');
            }

            return $this->entri($peti, $jenis, $jenis === 'out' ? -$amaun : $amaun, $d, $userId);
        });
    }

    /** Tambah petty cash kembali ke paras imprest. */
    public function tambahFloat(CashBox $peti, ?string $ruj, ?int $userId): CashBoxEntry
    {
        return DB::transaction(function () use ($peti, $ruj, $userId) {
            $this->kunci($peti);
            $kurang = round((float) $peti->float_amount - $peti->baki(), 2);
            if ($kurang <= 0) {
                throw new RuntimeException('Peti sudah pada atau melebihi paras float.');
            }

            return $this->entri($peti, 'in', $kurang, ['category' => 'Tambah float', 'reference' => $ruj], $userId);
        });
    }

    /** Deposit tunai dari peti ke akaun bank. Muncul sebagai calon dalam rekonsiliasi bank. */
    public function deposit(CashBox $peti, BankAccount $bank, float $amaun, ?string $ruj, ?string $tarikh, ?int $userId): CashBoxEntry
    {
        $this->amaunPositif($amaun);

        return DB::transaction(function () use ($peti, $bank, $amaun, $ruj, $tarikh, $userId) {
            $this->kunci($peti);
            if ($amaun > $peti->baki() + 0.004) {
                throw new RuntimeException('Tunai tidak mencukupi untuk deposit: baki RM'.number_format($peti->baki(), 2).'.');
            }

            return $this->entri($peti, 'deposit', -$amaun, [
                'category' => 'Deposit bank', 'description' => 'Deposit ke '.$bank->name, 'reference' => $ruj,
                'bank_account_id' => $bank->id, 'entry_date' => $tarikh,
            ], $userId);
        });
    }

    /** Terima tunai yang dikeluarkan dari laci POS (drop) ke peti besi. Tidak boleh melebihi jumlah drop syif. */
    public function terimaDropSyif(CashBox $peti, CashShift $syif, float $amaun, ?int $userId): CashBoxEntry
    {
        $this->amaunPositif($amaun);

        return DB::transaction(function () use ($peti, $syif, $amaun, $userId) {
            $this->kunci($peti);
            $drop = (float) POSShiftCashMovement::where('cash_shift_id', $syif->id)->where('type', POSShiftCashMovement::TYPE_DROP)->sum('amount');
            $sudah = (float) CashBoxEntry::where('cash_shift_id', $syif->id)->where('type', 'in')->sum('amount');
            if ($amaun > round(abs($drop) - $sudah, 2) + 0.004) {
                throw new RuntimeException('Melebihi tunai yang di-drop dari syif ini (baki boleh terima RM'.number_format(max(0, abs($drop) - $sudah), 2).').');
            }

            return $this->entri($peti, 'in', $amaun, ['category' => 'Drop laci POS', 'description' => 'Syif #'.$syif->id, 'cash_shift_id' => $syif->id], $userId);
        });
    }

    /** Kiraan tunai fizikal ikut denominasi untuk peti ATAU syif POS. */
    public function kira(?CashBox $peti, ?CashShift $syif, array $denominasi, ?string $nota, ?int $userId): CashCount
    {
        if (($peti === null) === ($syif === null)) {
            throw new RuntimeException('Pilih sama ada peti tunai atau syif POS.');
        }
        $k = $this->kiraDenominasi($denominasi);
        $jangka = $peti ? $peti->baki() : app(CashShiftService::class)->calculateExpectedCash($syif->load('cashMovements'));

        return CashCount::create([
            'cash_box_id' => $peti?->id, 'cash_shift_id' => $syif?->id, 'denominations' => $k['denominations'],
            'counted_total' => $k['total'], 'expected_total' => $jangka, 'variance' => round($k['total'] - $jangka, 2),
            'note' => $nota, 'counted_by' => $userId,
        ]);
    }

    /** Laporan buku tunai satu peti untuk julat tarikh. */
    public function laporan(CashBox $peti, string $dari, string $hingga): array
    {
        $buka = (float) $peti->entries()->where('entry_date', '<', $dari)->sum('amount');
        $entri = $peti->entries()->whereBetween('entry_date', [$dari, $hingga])->orderBy('entry_date')->orderBy('id')->get();
        $keluarIkutKategori = $entri->where('type', 'out')->groupBy(fn ($e) => $e->category ?: 'Lain-lain')
            ->map(fn ($g) => round(abs((float) $g->sum('amount')), 2))->sortDesc();

        return [
            'opening' => round($buka, 2),
            'in' => round((float) $entri->where('amount', '>', 0)->sum('amount'), 2),
            'out' => round(abs((float) $entri->where('type', 'out')->sum('amount')), 2),
            'deposits' => round(abs((float) $entri->where('type', 'deposit')->sum('amount')), 2),
            'adjustments' => round((float) $entri->where('type', 'adjust')->sum('amount'), 2),
            'closing' => round($buka + (float) $entri->sum('amount'), 2),
            'out_by_category' => $keluarIkutKategori,
            'entries' => $entri->values(),
        ];
    }

    /** Pelarasan selepas kiraan (perlu kebenaran lulus): jadikan baki buku = tunai dikira. */
    public function laraskanIkutKiraan(CashCount $kiraan, ?int $userId): CashBoxEntry
    {
        if (! $kiraan->cash_box_id) {
            throw new RuntimeException('Varians syif POS ditutup melalui tutup syif, bukan pelarasan peti.');
        }
        $peti = CashBox::findOrFail($kiraan->cash_box_id);

        return DB::transaction(function () use ($peti, $kiraan, $userId) {
            $this->kunci($peti);
            $beza = round((float) $kiraan->counted_total - $peti->baki(), 2);
            if (abs($beza) < 0.005) {
                throw new RuntimeException('Tiada varians untuk dilaraskan.');
            }

            return $this->entri($peti, 'adjust', $beza, ['category' => 'Pelarasan kiraan', 'description' => 'Kiraan #'.$kiraan->id], $userId);
        });
    }

    // ── dalaman ─────────────────────────────────────────────

    private function entri(CashBox $peti, string $jenis, float $amaun, array $d, ?int $userId): CashBoxEntry
    {
        return CashBoxEntry::create([
            'cash_box_id' => $peti->id, 'entry_date' => $d['entry_date'] ?? now()->toDateString(), 'type' => $jenis,
            'amount' => round($amaun, 2), 'category' => $d['category'] ?? null, 'description' => $d['description'] ?? null,
            'reference' => $d['reference'] ?? null, 'bank_account_id' => $d['bank_account_id'] ?? null,
            'cash_shift_id' => $d['cash_shift_id'] ?? null, 'created_by' => $userId,
        ]);
    }

    /** Kunci baris peti supaya dua pengeluaran serentak tidak melepasi baki. */
    private function kunci(CashBox $peti): void
    {
        CashBox::whereKey($peti->id)->lockForUpdate()->first();
    }

    private function amaunPositif(float $amaun): void
    {
        if ($amaun <= 0 || round($amaun, 2) != $amaun) {
            throw new RuntimeException('Amaun mesti lebih daripada 0 dengan maksimum 2 tempat perpuluhan.');
        }
    }

    private function kunciDenominasi(string $n): ?string
    {
        foreach (self::DENOMINASI as $d) {
            if (is_numeric($n) && abs((float) $n - (float) $d) < 0.0001) {
                return $d;
            }
        }

        return null;
    }
}
