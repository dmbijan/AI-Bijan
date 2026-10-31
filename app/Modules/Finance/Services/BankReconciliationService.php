<?php

namespace App\Modules\Finance\Services;

use App\Modules\Billing\Models\PaymentLink;
use App\Modules\Finance\Models\BankAccount;
use App\Modules\Finance\Models\BankStatementLine;
use App\Support\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * FIN-017 Rekonsiliasi Bank (+ AUT-013 import penyata CSV).
 *
 * Padanan automatik hanya bila yakin: (1) rujukan rekod dalaman muncul dalam keterangan bank DAN jumlah sama,
 * atau (2) satu-satunya calon dengan jumlah sama dalam ±3 hari. Selain itu dibiarkan untuk padanan manual.
 * Bayaran CHIP yang dibayar keluar secara pukal (tolak yuran) tidak akan sepadan 1:1 — padan manual.
 */
class BankReconciliationService
{
    public const HARI_TOLERANSI = 3;

    private const KOLUM = [
        'date' => ['tarikh', 'date', 'transaction date', 'posting date', 'value date', 'trn date', 'tarikh transaksi'],
        'description' => ['keterangan', 'description', 'details', 'transaction description', 'particulars', 'butiran', 'transaction details'],
        'reference' => ['rujukan', 'reference', 'ref', 'ref no', 'reference no', 'cheque no', 'no rujukan'],
        'debit' => ['debit', 'withdrawal', 'withdrawals', 'pengeluaran', 'keluar', 'debit amount'],
        'credit' => ['credit', 'kredit', 'deposit', 'deposits', 'masuk', 'credit amount'],
        'amount' => ['amount', 'jumlah', 'amaun', 'transaction amount'],
        'balance' => ['balance', 'baki', 'running balance', 'ledger balance'],
    ];

    /** @return array{batch:string, imported:int, duplicates:int, errors:list<string>} */
    public function importCsv(BankAccount $akaun, string $csv): array
    {
        $baris = $this->bacaCsv($csv);
        [$indeksTajuk, $peta] = $this->cariTajuk($baris);
        if ($indeksTajuk === null) {
            throw new RuntimeException('Tajuk lajur tidak dikenali. Perlu sekurang-kurangnya Tarikh, Keterangan dan Jumlah (atau Debit/Kredit).');
        }

        $batch = (string) Str::ulid();
        $diimport = 0;
        $berganda = 0;
        $ralat = [];
        $kiraanHash = [];

        foreach (array_slice($baris, $indeksTajuk + 1, null, true) as $i => $b) {
            if (count(array_filter($b, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }
            $noBaris = $i + 1;
            $tarikh = $this->tarikh($b[$peta['date']] ?? '');
            if (! $tarikh) {
                $ralat[] = "Baris {$noBaris}: tarikh tidak sah";
                continue;
            }
            $jumlah = $this->jumlahBaris($b, $peta);
            if ($jumlah === null || abs($jumlah) < 0.005) {
                $ralat[] = "Baris {$noBaris}: jumlah tiada";
                continue;
            }
            $ket = isset($peta['description']) ? trim((string) ($b[$peta['description']] ?? '')) : '';
            $ruj = isset($peta['reference']) ? trim((string) ($b[$peta['reference']] ?? '')) : '';
            $baki = isset($peta['balance']) ? $this->nombor($b[$peta['balance']] ?? '') : null;

            // Dua transaksi sama (tarikh/jumlah/keterangan) pada hari sama adalah sah: bezakan dengan bilangan kejadian.
            $asas = $tarikh->toDateString().'|'.number_format($jumlah, 2, '.', '').'|'.mb_strtolower($ket).'|'.mb_strtolower($ruj).'|'.($baki ?? '');
            $kiraanHash[$asas] = ($kiraanHash[$asas] ?? 0) + 1;
            $hash = hash('sha256', $asas.'#'.$kiraanHash[$asas]);

            if (BankStatementLine::where('bank_account_id', $akaun->id)->where('line_hash', $hash)->exists()) {
                $berganda++;
                continue;
            }
            BankStatementLine::create([
                'bank_account_id' => $akaun->id, 'txn_date' => $tarikh, 'description' => mb_substr($ket, 0, 500),
                'reference' => $ruj !== '' ? mb_substr($ruj, 0, 120) : null, 'amount' => round($jumlah, 2), 'balance' => $baki,
                'line_hash' => $hash, 'import_batch' => $batch, 'status' => BankStatementLine::UNMATCHED,
            ]);
            $diimport++;
        }

        return ['batch' => $batch, 'imported' => $diimport, 'duplicates' => $berganda, 'errors' => array_slice($ralat, 0, 50)];
    }

    /** Padankan semua baris belum padan bagi satu akaun. Pulangkan bilangan yang dipadankan. */
    public function autoMatch(BankAccount $akaun): int
    {
        $n = 0;
        $baris = BankStatementLine::where('bank_account_id', $akaun->id)->where('status', BankStatementLine::UNMATCHED)->orderBy('txn_date')->get();
        foreach ($baris as $l) {
            $calon = $this->calon($l);
            $ikutRujukan = array_values(array_filter($calon, fn ($c) => $this->rujukanAda($l, $c['refs'])));
            if (count($ikutRujukan) === 1) {
                $this->padan($l, $ikutRujukan[0]['type'], $ikutRujukan[0]['id'], 'reference', null);
                $n++;
                continue;
            }
            if (count($ikutRujukan) === 0 && count($calon) === 1) {
                $this->padan($l, $calon[0]['type'], $calon[0]['id'], 'amount_date', null);
                $n++;
            }
        }

        return $n;
    }

    /**
     * Calon padanan untuk satu baris bank: jumlah sama, belum direkonsiliasi, dalam ±3 hari.
     *
     * @return list<array{type:string,id:int,date:string,amount:float,label:string,refs:list<string>}>
     */
    public function calon(BankStatementLine $l): array
    {
        $dari = $l->txn_date->copy()->subDays(self::HARI_TOLERANSI)->toDateString();
        $hingga = $l->txn_date->copy()->addDays(self::HARI_TOLERANSI)->toDateString();
        $amaun = abs((float) $l->amount);
        $jenis = (float) $l->amount > 0 ? 'receivable' : 'payable';
        $out = [];

        $bayaran = DB::table('payments')->where('tenant_id', app(TenantContext::class)->id())
            ->where('payment_type', $jenis)->where('is_reconciled', false)
            ->whereBetween('payment_date', [$dari, $hingga])->where('amount', $amaun)->get();
        foreach ($bayaran as $p) {
            $out[] = ['type' => 'payment', 'id' => (int) $p->id, 'date' => (string) $p->payment_date, 'amount' => (float) $p->amount,
                'label' => $p->payment_reference.($p->reference_number ? ' · '.$p->reference_number : ''),
                'refs' => array_values(array_filter([$p->payment_reference, $p->reference_number]))];
        }

        if ($jenis === 'receivable') {
            $pautan = PaymentLink::where('status', 'paid')->where('is_reconciled', false)->where('amount', $amaun)
                ->whereBetween('paid_at', [$dari.' 00:00:00', $hingga.' 23:59:59'])->get();
            foreach ($pautan as $p) {
                $out[] = ['type' => 'payment_link', 'id' => $p->id, 'date' => $p->paid_at->toDateString(), 'amount' => (float) $p->amount,
                    'label' => strtoupper($p->provider).' '.$p->provider_ref, 'refs' => array_values(array_filter([$p->provider_ref]))];
            }
        }

        // Rekod yang sudah dipadankan oleh baris bank lain tidak lagi menjadi calon.
        return array_values(array_filter($out, fn ($c) => ! BankStatementLine::where('matched_type', $c['type'])->where('matched_id', $c['id'])
            ->where('status', BankStatementLine::MATCHED)->exists()));
    }

    public function padan(BankStatementLine $l, string $jenis, int $id, string $peraturan, ?int $userId): BankStatementLine
    {
        if ($l->status === BankStatementLine::MATCHED) {
            throw new RuntimeException('Baris bank ini sudah dipadankan. Nyahpadan dahulu.');
        }
        $sasaran = $this->sasaran($jenis, $id);
        if (abs(abs((float) $l->amount) - (float) $sasaran['amount']) > 0.004) {
            throw new RuntimeException('Jumlah tidak sama: bank RM'.number_format(abs((float) $l->amount), 2).' vs rekod RM'.number_format((float) $sasaran['amount'], 2).'.');
        }
        if ($sasaran['reconciled']) {
            throw new RuntimeException('Rekod ini sudah direkonsiliasi dengan baris bank lain.');
        }

        DB::transaction(function () use ($l, $jenis, $id, $peraturan, $userId) {
            $this->tandaSasaran($jenis, $id, true, $l->txn_date->toDateString());
            $l->update(['status' => BankStatementLine::MATCHED, 'matched_type' => $jenis, 'matched_id' => $id,
                'match_rule' => $peraturan, 'matched_by' => $userId, 'matched_at' => now()]);
        });

        return $l->refresh();
    }

    public function nyahpadan(BankStatementLine $l): BankStatementLine
    {
        if ($l->status === BankStatementLine::MATCHED) {
            DB::transaction(function () use ($l) {
                $this->tandaSasaran($l->matched_type, (int) $l->matched_id, false, null);
                $l->update(['status' => BankStatementLine::UNMATCHED, 'matched_type' => null, 'matched_id' => null,
                    'match_rule' => null, 'matched_by' => null, 'matched_at' => null]);
            });
        } elseif ($l->status === BankStatementLine::IGNORED) {
            $l->update(['status' => BankStatementLine::UNMATCHED]);
        }

        return $l->refresh();
    }

    /** Caj bank, faedah, pindahan dalaman: tiada rekod dalaman untuk dipadankan. */
    public function abaikan(BankStatementLine $l, ?int $userId): BankStatementLine
    {
        if ($l->status === BankStatementLine::MATCHED) {
            throw new RuntimeException('Nyahpadan dahulu sebelum mengabaikan.');
        }
        $l->update(['status' => BankStatementLine::IGNORED, 'matched_by' => $userId, 'matched_at' => now()]);

        return $l->refresh();
    }

    /** Ringkasan untuk satu akaun (pilihan julat tarikh). */
    public function ringkasan(BankAccount $akaun, ?string $dari = null, ?string $hingga = null): array
    {
        $q = fn () => BankStatementLine::where('bank_account_id', $akaun->id)
            ->when($dari, fn ($q) => $q->where('txn_date', '>=', $dari))
            ->when($hingga, fn ($q) => $q->where('txn_date', '<=', $hingga));
        $ikut = fn (string $s) => ['count' => $q()->where('status', $s)->count(), 'amount' => round((float) $q()->where('status', $s)->sum('amount'), 2)];
        $akhir = $q()->whereNotNull('balance')->orderByDesc('txn_date')->orderByDesc('id')->first();

        return [
            'matched' => $ikut(BankStatementLine::MATCHED),
            'unmatched' => $ikut(BankStatementLine::UNMATCHED),
            'ignored' => $ikut(BankStatementLine::IGNORED),
            'money_in' => round((float) $q()->where('amount', '>', 0)->sum('amount'), 2),
            'money_out' => round((float) $q()->where('amount', '<', 0)->sum('amount'), 2),
            'closing_balance' => $akhir ? (float) $akhir->balance : null,
            'closing_date' => $akhir?->txn_date?->toDateString(),
        ];
    }

    // ── dalaman ─────────────────────────────────────────────

    private function sasaran(string $jenis, int $id): array
    {
        if ($jenis === 'payment') {
            $p = DB::table('payments')->where('tenant_id', app(TenantContext::class)->id())->where('id', $id)->first();
            if (! $p) {
                throw new RuntimeException('Rekod bayaran tidak dijumpai.');
            }

            return ['amount' => (float) $p->amount, 'reconciled' => (bool) $p->is_reconciled];
        }
        if ($jenis === 'payment_link') {
            $p = PaymentLink::find($id);
            if (! $p || $p->status !== 'paid') {
                throw new RuntimeException('Pautan bayar tidak dijumpai atau belum dibayar.');
            }

            return ['amount' => (float) $p->amount, 'reconciled' => (bool) $p->is_reconciled];
        }
        throw new RuntimeException('Jenis padanan tidak disokong.');
    }

    private function tandaSasaran(string $jenis, int $id, bool $nilai, ?string $tarikh): void
    {
        if ($jenis === 'payment') {
            DB::table('payments')->where('tenant_id', app(TenantContext::class)->id())->where('id', $id)
                ->update(['is_reconciled' => $nilai, 'reconciled_at' => $tarikh, 'updated_at' => now()]);
        } elseif ($jenis === 'payment_link') {
            PaymentLink::whereKey($id)->update(['is_reconciled' => $nilai]);
        }
    }

    private function rujukanAda(BankStatementLine $l, array $refs): bool
    {
        $teks = mb_strtolower(($l->description ?? '').' '.($l->reference ?? ''));
        foreach ($refs as $r) {
            $r = mb_strtolower(trim((string) $r));
            if (mb_strlen($r) >= 4 && str_contains($teks, $r)) {
                return true;
            }
        }

        return false;
    }

    /** @return list<list<string>> */
    private function bacaCsv(string $csv): array
    {
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
        $baris = preg_split('/\r\n|\n|\r/', trim($csv));
        $pemisah = substr_count($baris[0] ?? '', ';') > substr_count($baris[0] ?? '', ',') ? ';' : ',';

        return array_map(fn ($b) => str_getcsv($b, $pemisah, '"', '\\'), $baris);
    }

    /** Cari baris tajuk dalam 15 baris pertama (penyata bank selalunya ada maklumat akaun di atas). */
    private function cariTajuk(array $baris): array
    {
        foreach (array_slice($baris, 0, 15, true) as $i => $b) {
            $peta = [];
            foreach ($b as $j => $sel) {
                $s = mb_strtolower(trim(preg_replace('/\s*\(.*?\)|\s*rm$/i', '', (string) $sel)));
                foreach (self::KOLUM as $kunci => $nama) {
                    if (! isset($peta[$kunci]) && in_array($s, $nama, true)) {
                        $peta[$kunci] = $j;
                        break;
                    }
                }
            }
            if (isset($peta['date']) && (isset($peta['amount']) || isset($peta['debit']) || isset($peta['credit']))) {
                return [$i, $peta];
            }
        }

        return [null, []];
    }

    private function jumlahBaris(array $b, array $peta): ?float
    {
        if (isset($peta['debit']) || isset($peta['credit'])) {
            $dr = isset($peta['debit']) ? $this->nombor($b[$peta['debit']] ?? '') : null;
            $cr = isset($peta['credit']) ? $this->nombor($b[$peta['credit']] ?? '') : null;
            if ($dr === null && $cr === null) {
                return isset($peta['amount']) ? $this->nombor($b[$peta['amount']] ?? '') : null;
            }

            return round(abs($cr ?? 0) - abs($dr ?? 0), 2);
        }

        return $this->nombor($b[$peta['amount']] ?? '');
    }

    /** "RM1,234.50", "(12.00)", "12.00 DR", "-5", "1.234,50" → float. */
    public function nombor(string $s): ?float
    {
        $s = trim(str_ireplace(['RM', 'MYR', ' '], '', $s));
        if ($s === '' || $s === '-') {
            return null;
        }
        $negatif = false;
        if (preg_match('/^\((.*)\)$/', $s, $m)) {
            $negatif = true;
            $s = $m[1];
        }
        if (preg_match('/^(.*?)(DR|CR|-)$/i', $s, $m)) {
            $negatif = $negatif || strtoupper($m[2]) !== 'CR';
            $s = $m[1];
        }
        if (str_starts_with($s, '-')) {
            $negatif = true;
            $s = substr($s, 1);
        } elseif (str_starts_with($s, '+')) {
            $s = substr($s, 1);
        }
        if (preg_match('/^\d{1,3}(\.\d{3})+,\d{1,2}$/', $s)) {
            $s = str_replace(['.', ','], ['', '.'], $s);
        } else {
            $s = str_replace(',', '', $s);
        }
        if (! is_numeric($s)) {
            return null;
        }

        return $negatif ? -(float) $s : (float) $s;
    }

    public function tarikh(string $s): ?Carbon
    {
        $s = trim($s);
        foreach (['d/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'Y-m-d', 'd.m.Y', 'd/m/y', 'j/n/y', 'd-m-y', 'd M Y', 'j M Y', 'd-M-Y', 'd M y', 'd-M-y', 'Y/m/d'] as $f) {
            try {
                $d = Carbon::createFromFormat('!'.$f, $s);
            } catch (\Throwable) {
                continue;
            }
            if ($d && strcasecmp($d->format($f), $s) === 0) {
                return $d;
            }
        }

        return null;
    }
}
