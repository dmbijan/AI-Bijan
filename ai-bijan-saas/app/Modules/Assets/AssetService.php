<?php

namespace App\Modules\Assets;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\AssetMaintenance;
use App\Models\AssetMovement;
use App\Services\AuditLogger;
use App\Support\Dec;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Susut nilai bulanan. Kaedah ikut polisi syarikat (bukan elaun modal cukai — itu pengiraan LHDN berasingan).
 * - Garis lurus: (kos − nilai sisa) ÷ hayat bulan; bulan terakhir dilaras supaya tepat sampai nilai sisa.
 * - Baki berkurangan (dua kali ganda): nilai buku × (2 ÷ hayat bulan), tidak melepasi nilai sisa.
 */
class AssetService
{
    public function buildSchedule(Asset $a): void
    {
        DB::transaction(function () use ($a) {
            AssetDepreciation::where('asset_id', $a->id)->where('posted', false)->delete();
            $dipos = AssetDepreciation::where('asset_id', $a->id)->where('posted', true)->orderBy('period')->get();
            $boleh = Dec::sub($a->cost, $a->residual_value);
            $terkumpul = Dec::n($dipos->sum('amount'));
            $mula = Carbon::parse($a->depreciation_start)->startOfMonth()->addMonths($dipos->count());
            $baki = $a->useful_life_months - $dipos->count();
            $bulanan = Dec::div($boleh, $a->useful_life_months);
            for ($i = 0; $i < $baki; $i++) {
                $buku = Dec::sub($a->cost, $terkumpul);
                $amaun = $a->method === 'garis_lurus' ? Dec::round($bulanan) : Dec::round(Dec::div(Dec::mul($buku, 2), $a->useful_life_months)); // darab dahulu, baru bahagi (elak hilang ketepatan)
                $tinggal = Dec::sub($buku, $a->residual_value);
                if ($i === $baki - 1 || Dec::cmp($amaun, $tinggal) > 0) {
                    $amaun = Dec::round($tinggal); // bulan terakhir / jangan bawah nilai sisa
                }
                $terkumpul = Dec::add($terkumpul, $amaun);
                AssetDepreciation::create(['asset_id' => $a->id, 'period' => $mula->copy()->addMonths($i)->format('Y-m'), 'amount' => $amaun,
                    'accumulated' => Dec::round($terkumpul), 'book_value' => Dec::round(Dec::sub($a->cost, $terkumpul)), 'posted' => false]);
                if (Dec::cmp(Dec::sub($a->cost, $terkumpul), $a->residual_value) <= 0) {
                    break;
                }
            }
        });
    }

    /** Pos susut nilai hingga tempoh (YYYY-MM) untuk semua aset aktif. Pulangkan jumlah (untuk jurnal kewangan). */
    public function postUntil(string $period): array
    {
        return DB::transaction(function () use ($period) {
            $rows = AssetDepreciation::where('posted', false)->where('period', '<=', $period)->whereIn('asset_id', Asset::where('status', '!=', 'dilupus')->pluck('id'))->lockForUpdate()->get();
            foreach ($rows as $r) {
                $r->update(['posted' => true, 'posted_at' => now()]);
            }
            $jum = Dec::round($rows->sum('amount'));
            app(AuditLogger::class)->log('assets.depreciation_posted', null, null, ['period' => $period, 'rows' => $rows->count(), 'total' => $jum]);

            return ['period' => $period, 'rows' => $rows->count(), 'total' => $jum];
        });
    }

    public function bookValue(Asset $a, ?string $period = null): string
    {
        $q = AssetDepreciation::where('asset_id', $a->id)->where('posted', true)->when($period, fn ($q) => $q->where('period', '<=', $period));

        return Dec::round(Dec::sub($a->cost, $q->sum('amount')));
    }

    public function move(Asset $a, array $d): AssetMovement
    {
        if ($a->status === 'dilupus') {
            throw ValidationException::withMessages(['asset' => 'Aset telah dilupus.']);
        }
        $m = AssetMovement::create(['asset_id' => $a->id, 'from_location' => $a->location, 'to_location' => $d['to_location'] ?? $a->location, 'from_custodian_id' => $a->custodian_id,
            'to_custodian_id' => $d['to_custodian_id'] ?? $a->custodian_id, 'moved_on' => $d['moved_on'] ?? now()->toDateString(), 'note' => $d['note'] ?? null, 'user_id' => auth()->id()]);
        $a->update(['location' => $m->to_location, 'custodian_id' => $m->to_custodian_id]);
        app(AuditLogger::class)->log('assets.moved', $a, ['location' => $m->from_location, 'custodian' => $m->from_custodian_id], ['location' => $m->to_location, 'custodian' => $m->to_custodian_id]);

        return $m;
    }

    /** Pelupusan: nilai buku pada tarikh (susut nilai dipos), untung/rugi = hasil − nilai buku; jadual masa depan dibuang. */
    public function dispose(Asset $a, string $date, $proceeds, string $reason): Asset
    {
        if ($a->status === 'dilupus') {
            throw ValidationException::withMessages(['asset' => 'Aset sudah dilupus.']);
        }

        return DB::transaction(function () use ($a, $date, $proceeds, $reason) {
            $tempoh = Carbon::parse($date)->format('Y-m');
            $this->postUntilForAsset($a, $tempoh);
            AssetDepreciation::where('asset_id', $a->id)->where('posted', false)->delete();
            $buku = $this->bookValue($a);
            $a->update(['status' => 'dilupus', 'disposed_on' => $date, 'disposal_proceeds' => Dec::round($proceeds), 'disposal_gain_loss' => Dec::round(Dec::sub($proceeds, $buku)), 'disposal_reason' => $reason]);
            app(AuditLogger::class)->log('assets.disposed', $a, null, ['book_value' => $buku, 'proceeds' => $proceeds, 'gain_loss' => $a->disposal_gain_loss]);

            return $a;
        });
    }

    private function postUntilForAsset(Asset $a, string $period): void
    {
        AssetDepreciation::where('asset_id', $a->id)->where('posted', false)->where('period', '<=', $period)->update(['posted' => true, 'posted_at' => now()]);
    }

    /** Selesai penyelenggaraan; jika berulang, jadualkan seterusnya. */
    public function completeMaintenance(AssetMaintenance $m, array $d): AssetMaintenance
    {
        if ($m->status !== 'dijadual') {
            throw ValidationException::withMessages(['status' => 'Penyelenggaraan ini bukan dalam jadual.']);
        }
        $m->update(['status' => 'selesai', 'done_on' => $d['done_on'] ?? now()->toDateString(), 'cost' => $d['cost'] ?? null, 'notes' => $d['notes'] ?? $m->notes]);
        if ($m->repeat_months) {
            AssetMaintenance::create(['asset_id' => $m->asset_id, 'type' => $m->type, 'scheduled_on' => Carbon::parse($m->done_on)->addMonths($m->repeat_months)->toDateString(),
                'vendor' => $m->vendor, 'repeat_months' => $m->repeat_months, 'status' => 'dijadual']);
        }
        app(AuditLogger::class)->log('assets.maintenance_done', $m, null, ['cost' => $m->cost]);

        return $m;
    }
}
