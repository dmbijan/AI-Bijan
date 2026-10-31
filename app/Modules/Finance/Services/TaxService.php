<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\TaxCode;
use App\Modules\Finance\Models\TaxSetting;
use App\Support\Tenancy\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * FIN-010 Cukai SST Malaysia.
 *
 * Kadar lalai di bawah ialah titik mula untuk setiap tenant dan boleh disunting. Ia BUKAN nasihat cukai:
 * skop & kadar SST berubah (cth. cukai perkhidmatan 8% dari 1/3/2024 dengan pengecualian kekal 6%,
 * peluasan skop 1/7/2025). Pengguna mesti sahkan dengan JKDM/MySST.
 */
class TaxService
{
    public const LALAI = [
        ['code' => 'ST10', 'name' => 'Cukai jualan 10%', 'type' => 'sales', 'rate' => 10, 'effective_from' => '2018-09-01'],
        ['code' => 'ST5', 'name' => 'Cukai jualan 5%', 'type' => 'sales', 'rate' => 5, 'effective_from' => '2018-09-01'],
        ['code' => 'SV6', 'name' => 'Cukai perkhidmatan 6% (F&B, logistik, dll.)', 'type' => 'service', 'rate' => 6, 'effective_from' => '2018-09-01'],
        ['code' => 'SV8', 'name' => 'Cukai perkhidmatan 8%', 'type' => 'service', 'rate' => 8, 'effective_from' => '2024-03-01'],
        ['code' => 'EX', 'name' => 'Dikecualikan', 'type' => 'exempt', 'rate' => 0, 'effective_from' => '2018-09-01'],
        ['code' => 'NT', 'name' => 'Tidak bercukai / luar skop', 'type' => 'none', 'rate' => 0, 'effective_from' => '2018-09-01'],
    ];

    /** Semai kod lalai sekali bagi tenant yang belum ada kod. */
    public function pastikanLalai(): void
    {
        if (TaxCode::exists()) {
            return;
        }
        foreach (self::LALAI as $k) {
            TaxCode::create($k);
        }
    }

    public function tetapan(): TaxSetting
    {
        return TaxSetting::firstOrCreate([], ['period_start_month' => 1]);
    }

    /** Kod yang berkuat kuasa pada tarikh tertentu. */
    public function kod(string $kod, ?string $tarikh = null): TaxCode
    {
        $this->pastikanLalai();
        $t = $tarikh ? CarbonImmutable::parse($tarikh)->toDateString() : now()->toDateString();
        $k = TaxCode::where('code', strtoupper($kod))->where('is_active', true)
            ->where('effective_from', '<=', $t)
            ->where(fn ($q) => $q->whereNull('effective_to')->orWhere('effective_to', '>=', $t))
            ->orderByDesc('effective_from')->first();
        if (! $k) {
            throw new RuntimeException("Kod cukai {$kod} tidak sah atau tidak berkuat kuasa pada {$t}.");
        }

        return $k;
    }

    /**
     * Kira cukai bagi satu amaun. $termasuk = true jika harga sudah termasuk cukai.
     *
     * @return array{code:string, rate:float, taxable:float, tax:float, gross:float}
     */
    public function kira(float $amaun, string $kod, ?string $tarikh = null, bool $termasuk = false): array
    {
        $k = $this->kod($kod, $tarikh);
        $kadar = (float) $k->rate;
        if ($termasuk) {
            $kasar = round($amaun, 2);
            $bercukai = round($kasar / (1 + $kadar / 100), 2);
            $cukai = round($kasar - $bercukai, 2);
        } else {
            $bercukai = round($amaun, 2);
            $cukai = round($bercukai * $kadar / 100, 2);
            $kasar = round($bercukai + $cukai, 2);
        }

        return ['code' => $k->code, 'rate' => $kadar, 'taxable' => $bercukai, 'tax' => $cukai, 'gross' => $kasar];
    }

    /** Tempoh bercukai 2 bulan yang mengandungi $tarikh, ikut bulan mula kitaran tenant. */
    public function tempoh(?string $tarikh = null): array
    {
        $d = CarbonImmutable::parse($tarikh ?? now())->startOfMonth();
        $mula = $this->tetapan()->period_start_month ?: 1;
        $offset = (($d->month - $mula) % 2 + 2) % 2;
        $dari = $d->subMonths($offset);
        $hingga = $dari->addMonth()->endOfMonth();

        return [
            'from' => $dari->toDateString(),
            'to' => $hingga->toDateString(),
            // SST-02 dan bayaran: akhir bulan berikutan tamat tempoh.
            'due' => $hingga->addDay()->endOfMonth()->toDateString(),
        ];
    }

    /**
     * Draf SST-02: nilai bercukai & cukai ikut kod, daripada jualan POS selesai dan invois dikeluarkan.
     * Baris tanpa kod tetapi ada cukai dilaporkan berasingan supaya pengguna betulkan dahulu.
     */
    public function drafSst02(string $dari, string $hingga): array
    {
        $tenant = app(TenantContext::class)->id();
        $this->pastikanLalai();

        $pos = DB::table('pos_sale_items as i')->join('pos_sales as s', 's.id', '=', 'i.sale_id')
            ->where('s.tenant_id', $tenant)->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$dari, $hingga.' 23:59:59'])
            ->selectRaw('i.tax_code as code, SUM(i.line_total - i.tax_amount) as taxable, SUM(i.tax_amount) as tax, COUNT(*) as n')
            ->groupBy('i.tax_code')->get();

        $inv = DB::table('invoice_items as i')->join('invoices as v', 'v.id', '=', 'i.invoice_id')
            ->where('v.tenant_id', $tenant)->whereNotIn('v.status', ['draft', 'cancelled'])
            ->whereBetween('v.issue_date', [$dari, $hingga.' 23:59:59'])
            ->selectRaw('i.tax_code as code, SUM(i.line_total - i.tax_amount) as taxable, SUM(i.tax_amount) as tax, COUNT(*) as n')
            ->groupBy('i.tax_code')->get();

        $kod = TaxCode::get()->keyBy('code');
        $baris = [];
        $tanpaKod = ['taxable' => 0.0, 'tax' => 0.0, 'lines' => 0];
        foreach ([['pos', $pos], ['invoice', $inv]] as [$sumber, $rows]) {
            foreach ($rows as $r) {
                if (! $r->code) {
                    if ((float) $r->tax > 0) {
                        $tanpaKod['taxable'] += (float) $r->taxable;
                        $tanpaKod['tax'] += (float) $r->tax;
                        $tanpaKod['lines'] += (int) $r->n;
                    }
                    continue;
                }
                $b = &$baris[$r->code];
                $b ??= ['code' => $r->code, 'name' => $kod[$r->code]->name ?? $r->code, 'type' => $kod[$r->code]->type ?? '?',
                    'rate' => (float) ($kod[$r->code]->rate ?? 0), 'taxable' => 0.0, 'tax' => 0.0, 'pos_lines' => 0, 'invoice_lines' => 0];
                $b['taxable'] += (float) $r->taxable;
                $b['tax'] += (float) $r->tax;
                $b[$sumber.'_lines'] += (int) $r->n;
                unset($b);
            }
        }
        $baris = array_values(array_map(fn ($b) => ['taxable' => round($b['taxable'], 2), 'tax' => round($b['tax'], 2)] + $b, $baris));
        usort($baris, fn ($a, $b) => [$a['type'], $b['rate']] <=> [$b['type'], $a['rate']]);

        $jumlah = fn (string $jenis) => round(array_sum(array_column(array_filter($baris, fn ($b) => $b['type'] === $jenis), 'tax')), 2);
        $t = $this->tetapan();

        return [
            'registration_no' => $t->sst_registration_no,
            'period' => ['from' => $dari, 'to' => $hingga, 'due' => CarbonImmutable::parse($hingga)->addDay()->endOfMonth()->toDateString()],
            'lines' => $baris,
            'sales_tax' => $jumlah('sales'),
            'service_tax' => $jumlah('service'),
            'total_tax_payable' => round($jumlah('sales') + $jumlah('service'), 2),
            'untagged' => ['taxable' => round($tanpaKod['taxable'], 2), 'tax' => round($tanpaKod['tax'], 2), 'lines' => $tanpaKod['lines']],
            'warnings' => array_values(array_filter([
                $t->sst_registration_no ? null : 'No. pendaftaran SST belum diisi.',
                $tanpaKod['lines'] > 0 ? "{$tanpaKod['lines']} baris ada cukai tanpa kod: tetapkan kod sebelum hantar." : null,
            ])),
            'disclaimer' => 'Draf untuk semakan. Sahkan angka dan kadar sebelum dihantar melalui MySST.',
        ];
    }
}
