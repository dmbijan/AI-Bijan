<?php

namespace App\Modules\DataImport\Services;

use App\Models\Item;
use App\Models\Warehouse;
use App\Modules\CRM\Models\Customer;
use App\Modules\DataImport\Models\ImportJob;
use App\Modules\DataImport\Models\ImportRow;
use App\Modules\Inventory\MovementService;
use App\Support\NumberSeries;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Import Pintar: CSV → padan lajur automatik (BM/EN & nama lajur lazim sistem lain) → semak setiap baris →
 * pratonton → sahkan → simpan dalam satu transaksi. Tiada apa disimpan ke data sebenar sebelum disahkan.
 */
class ImportService
{
    public const MAKS_BARIS = 20000;

    /** Medan bagi setiap jenis: [sinonim tajuk..., wajib?] */
    public const MEDAN = [
        'products' => [
            'sku' => [['sku', 'kod', 'kod produk', 'kod barang', 'item code', 'stock code', 'product code', 'itemcode', 'sku reference no.', 'parent sku', 'kod item'], true],
            'name' => [['nama', 'nama produk', 'nama barang', 'name', 'product name', 'item name', 'description', 'keterangan', 'produk'], true],
            'sell_price' => [['harga', 'harga jual', 'price', 'selling price', 'unit price', 'harga jualan', 'retail price', 'rrp'], false],
            'standard_cost' => [['kos', 'harga kos', 'cost', 'unit cost', 'cost price', 'purchase price', 'harga beli'], false],
            'category' => [['kategori', 'category', 'group', 'item group', 'item type', 'jenis'], false],
            'barcode' => [['barcode', 'kod bar', 'ean', 'upc', 'gtin'], false],
            'uom' => [['unit', 'uom', 'unit ukuran', 'base uom'], false],
            'reorder_level' => [['paras pesanan semula', 'reorder level', 'reorder point', 'min stock', 'stok minimum'], false],
        ],
        'customers' => [
            'name' => [['nama', 'nama pelanggan', 'name', 'customer name', 'company name', 'nama syarikat', 'debtor name', 'company'], true],
            'contact_phone' => [['telefon', 'no telefon', 'phone', 'mobile', 'tel', 'no. telefon', 'phone number', 'no hp', 'whatsapp'], false],
            'contact_email' => [['emel', 'e-mel', 'email', 'email address'], false],
            'contact_person' => [['orang hubungan', 'contact person', 'attention', 'contact', 'pic'], false],
            'customer_type' => [['jenis', 'type', 'customer type', 'jenis pelanggan'], false],
            'ssm_number' => [['ssm', 'no ssm', 'registration no', 'company reg no', 'brn', 'no pendaftaran'], false],
            'sst_number' => [['sst', 'no sst', 'sst no', 'sst registration no'], false],
            'tax_identification' => [['tin', 'no cukai', 'tax id', 'tax identification', 'no tin'], false],
            'address' => [['alamat', 'address', 'address 1', 'billing address'], false],
            'city' => [['bandar', 'city', 'town'], false],
            'state' => [['negeri', 'state'], false],
            'postcode' => [['poskod', 'postcode', 'postal code', 'zip'], false],
            'credit_limit' => [['had kredit', 'credit limit'], false],
        ],
        'opening_stock' => [
            'sku' => [['sku', 'kod', 'kod produk', 'kod barang', 'item code', 'stock code', 'product code'], true],
            'qty' => [['kuantiti', 'qty', 'quantity', 'stok', 'stock', 'baki', 'balance qty', 'on hand', 'kuantiti stok'], true],
            'unit_cost' => [['kos', 'kos seunit', 'unit cost', 'cost', 'harga kos', 'average cost', 'kos purata'], false],
            'warehouse' => [['gudang', 'warehouse', 'location', 'lokasi', 'outlet', 'kedai'], false],
        ],
    ];

    /** Cipta kerja pratonton daripada CSV. */
    public function pratonton(string $jenis, string $csv, ?string $namaFail, ?array $peta, int $userId): ImportJob
    {
        $baris = $this->bacaCsv($csv);
        if (count($baris) < 2) {
            throw new RuntimeException('Fail kosong atau hanya ada tajuk.');
        }
        if (count($baris) - 1 > self::MAKS_BARIS) {
            throw new RuntimeException('Terlalu banyak baris (maksimum '.self::MAKS_BARIS.'). Pecahkan fail.');
        }
        $tajuk = array_shift($baris);
        $peta = $peta ? $this->sahkanPeta($jenis, $peta, count($tajuk)) : $this->padanTajuk($jenis, $tajuk);
        $kurang = array_keys(array_filter(self::MEDAN[$jenis], fn ($m, $k) => $m[1] && ! isset($peta[$k]), ARRAY_FILTER_USE_BOTH));
        if ($kurang) {
            throw new RuntimeException('Lajur wajib tidak dijumpai: '.implode(', ', $kurang).'. Tetapkan padanan lajur secara manual.');
        }

        return DB::transaction(function () use ($jenis, $baris, $tajuk, $peta, $namaFail, $userId) {
            $job = ImportJob::create(['type' => $jenis, 'status' => 'preview', 'filename' => $namaFail ? mb_substr($namaFail, 0, 190) : null,
                'mapping' => $peta, 'options' => ['headers' => $tajuk], 'created_by' => $userId]);

            $konteks = $this->konteks($jenis);
            $sah = 0;
            $ralat = 0;
            foreach ($baris as $i => $b) {
                if (count(array_filter($b, fn ($v) => trim((string) $v) !== '')) === 0) {
                    continue;
                }
                $data = [];
                foreach ($peta as $medan => $kol) {
                    $data[$medan] = trim((string) ($b[$kol] ?? ''));
                }
                [$data, $err, $tindakan, $sasaran] = $this->semak($jenis, $data, $konteks, $i + 2);
                ImportRow::create(['import_job_id' => $job->id, 'row_no' => $i + 2, 'data' => $data, 'errors' => $err ?: null,
                    'action' => $err ? 'skip' : $tindakan, 'target_id' => $sasaran]);
                $err ? $ralat++ : $sah++;
            }
            $job->update(['total_rows' => $sah + $ralat, 'valid_rows' => $sah, 'error_rows' => $ralat]);

            return $job;
        });
    }

    /** Simpan baris sah ke data sebenar. Sekali sahaja (idempoten pada status). */
    public function sahkan(ImportJob $job, int $userId): ImportJob
    {
        return DB::transaction(function () use ($job, $userId) {
            $job = ImportJob::lockForUpdate()->find($job->id);
            if ($job->status !== 'preview') {
                throw new RuntimeException('Import ini sudah '.($job->status === 'committed' ? 'disimpan' : 'dibatalkan').'.');
            }
            if ($job->valid_rows === 0) {
                throw new RuntimeException('Tiada baris sah untuk disimpan.');
            }
            $rows = $job->rows()->where('action', '!=', 'skip')->orderBy('row_no')->get();
            $hasil = match ($job->type) {
                'products' => $this->simpanProduk($rows),
                'customers' => $this->simpanPelanggan($rows),
                'opening_stock' => $this->simpanStokAwal($rows, $job),
            };
            $job->update(['status' => 'committed', 'committed_by' => $userId, 'committed_at' => now(), 'result' => $hasil]);

            return $job;
        });
    }

    public function batal(ImportJob $job): ImportJob
    {
        if ($job->status !== 'preview') {
            throw new RuntimeException('Hanya import pratonton boleh dibatalkan.');
        }
        $job->update(['status' => 'cancelled']);

        return $job;
    }

    public const TEMPLAT = [
        'products' => ['SKU', 'Nama', 'Harga jual', 'Kos', 'Kategori', 'Barcode', 'Unit', 'Paras pesanan semula'],
        'customers' => ['Nama', 'Telefon', 'Emel', 'Orang hubungan', 'Jenis', 'SSM', 'SST', 'TIN', 'Alamat', 'Bandar', 'Negeri', 'Poskod', 'Had kredit'],
        'opening_stock' => ['SKU', 'Kuantiti', 'Kos seunit', 'Gudang'],
    ];

    /** Templat CSV kosong (tajuk BM) — setiap tajuk dikenali oleh padanTajuk(). */
    public function templat(string $jenis): string
    {
        return implode(',', self::TEMPLAT[$jenis])."\n";
    }

    // ── semakan setiap jenis ───────────────────────────────

    private function konteks(string $jenis): array
    {
        return match ($jenis) {
            'products' => ['sku' => Item::pluck('id', 'sku')->mapWithKeys(fn ($id, $s) => [mb_strtolower($s) => $id])->all(), 'dalam_fail' => []],
            'customers' => [
                'emel' => Customer::whereNotNull('contact_email')->pluck('id', 'contact_email')->mapWithKeys(fn ($id, $e) => [mb_strtolower($e) => $id])->all(),
                'telefon' => Customer::whereNotNull('contact_phone')->pluck('id', 'contact_phone')->mapWithKeys(fn ($id, $p) => [$this->telefon($p) ?? $p => $id])->all(),
                'dalam_fail' => [],
            ],
            'opening_stock' => [
                'sku' => Item::pluck('id', 'sku')->mapWithKeys(fn ($id, $s) => [mb_strtolower($s) => $id])->all(),
                'gudang' => Warehouse::where('is_active', true)->get()->flatMap(fn ($w) => [mb_strtolower($w->code) => $w->id, mb_strtolower($w->name) => $w->id])->all(),
                'gudang_lalai' => Warehouse::where('is_active', true)->orderBy('id')->value('id'),
                'berstok' => DB::table('stock_balances')->where('tenant_id', app(TenantContext::class)->id())->where('qty', '!=', 0)->distinct()->pluck('item_id')->flip()->all(),
                'dalam_fail' => [],
            ],
        };
    }

    /** @return array{0: array, 1: list<string>, 2: string, 3: ?int} */
    private function semak(string $jenis, array $d, array &$k, int $noBaris): array
    {
        $err = [];
        $tindakan = 'create';
        $sasaran = null;

        if ($jenis === 'products') {
            $d['sku'] = mb_strtoupper(preg_replace('/\s+/', '', $d['sku'] ?? ''));
            if ($d['sku'] === '' || mb_strlen($d['sku']) > 40) {
                $err[] = 'SKU kosong atau melebihi 40 aksara';
            }
            if (($d['name'] ?? '') === '') {
                $err[] = 'Nama kosong';
            }
            foreach (['sell_price', 'standard_cost', 'reorder_level'] as $m) {
                if (isset($d[$m]) && $d[$m] !== '') {
                    $n = $this->nombor($d[$m]);
                    $n === null || $n < 0 ? $err[] = "{$m} bukan nombor sah" : $d[$m] = $n;
                } else {
                    unset($d[$m]);
                }
            }
            $kunci = mb_strtolower($d['sku']);
            if ($kunci !== '' && isset($k['dalam_fail'][$kunci])) {
                $err[] = "SKU berganda dalam fail (baris {$k['dalam_fail'][$kunci]})";
            }
            $k['dalam_fail'][$kunci] ??= $noBaris;
            if (isset($k['sku'][$kunci])) {
                $tindakan = 'update';
                $sasaran = $k['sku'][$kunci];
            }
        }

        if ($jenis === 'customers') {
            if (($d['name'] ?? '') === '') {
                $err[] = 'Nama kosong';
            }
            if (($d['contact_email'] ?? '') !== '') {
                $d['contact_email'] = mb_strtolower($d['contact_email']);
                if (! filter_var($d['contact_email'], FILTER_VALIDATE_EMAIL)) {
                    $err[] = 'Emel tidak sah';
                }
            }
            if (($d['contact_phone'] ?? '') !== '') {
                $t = $this->telefon($d['contact_phone']);
                $t === null ? $err[] = 'Nombor telefon tidak sah' : $d['contact_phone'] = $t;
            }
            $jenisP = mb_strtolower($d['customer_type'] ?? '');
            $d['customer_type'] = in_array($jenisP, ['individu', 'individual', 'perseorangan', 'personal'], true) ? 'individual'
                : (($d['ssm_number'] ?? '') === '' && $jenisP === '' ? 'individual' : 'business');
            if (($d['credit_limit'] ?? '') !== '') {
                $n = $this->nombor($d['credit_limit']);
                $n === null || $n < 0 ? $err[] = 'Had kredit tidak sah' : $d['credit_limit'] = $n;
            } else {
                unset($d['credit_limit']);
            }
            // Pendua: emel atau telefon sama dengan pelanggan sedia ada → kemas kini; dalam fail → ralat.
            foreach (['contact_email' => 'emel', 'contact_phone' => 'telefon'] as $m => $idx) {
                $v = $d[$m] ?? '';
                if ($v === '' || $err) {
                    continue;
                }
                if (isset($k['dalam_fail'][$m.$v])) {
                    $err[] = "Pendua dalam fail ({$idx} sama dengan baris {$k['dalam_fail'][$m.$v]})";
                }
                $k['dalam_fail'][$m.$v] ??= $noBaris;
                if (! $sasaran && isset($k[$idx][$v])) {
                    $tindakan = 'update';
                    $sasaran = $k[$idx][$v];
                }
            }
            $d = array_filter($d, fn ($v) => $v !== '');
        }

        if ($jenis === 'opening_stock') {
            $kunci = mb_strtolower(preg_replace('/\s+/', '', $d['sku'] ?? ''));
            $sasaran = $k['sku'][$kunci] ?? null;
            if (! $sasaran) {
                $err[] = 'SKU tidak wujud (import produk dahulu)';
            } elseif (isset($k['berstok'][$sasaran])) {
                $err[] = 'Barang ini sudah ada stok: guna pelarasan stok, bukan stok awal';
            }
            $q = $this->nombor($d['qty'] ?? '');
            $q === null || $q <= 0 ? $err[] = 'Kuantiti mesti lebih daripada 0' : $d['qty'] = $q;
            if (($d['unit_cost'] ?? '') === '') {
                $err[] = 'Kos seunit diperlukan untuk nilai stok';
            } else {
                $c = $this->nombor($d['unit_cost']);
                $c === null || $c < 0 ? $err[] = 'Kos seunit tidak sah' : $d['unit_cost'] = $c;
            }
            $g = ($d['warehouse'] ?? '') !== '' ? ($k['gudang'][mb_strtolower($d['warehouse'])] ?? null) : $k['gudang_lalai'];
            $g ? $d['warehouse_id'] = $g : $err[] = (($d['warehouse'] ?? '') !== '' ? 'Gudang tidak dijumpai' : 'Tiada gudang aktif: cipta gudang dahulu');
            $kk = $kunci.'|'.($d['warehouse_id'] ?? '');
            if (isset($k['dalam_fail'][$kk])) {
                $err[] = "SKU & gudang sama dengan baris {$k['dalam_fail'][$kk]}";
            }
            $k['dalam_fail'][$kk] ??= $noBaris;
        }

        return [$d, $err, $tindakan, $sasaran];
    }

    // ── simpan ─────────────────────────────────────────────

    private function simpanProduk($rows): array
    {
        $baru = 0;
        $kemas = 0;
        foreach ($rows as $r) {
            $d = array_intersect_key($r->data, array_flip(['sku', 'name', 'sell_price', 'standard_cost', 'category', 'barcode', 'uom', 'reorder_level']));
            $d = array_filter($d, fn ($v) => $v !== '' && $v !== null);
            if ($r->action === 'update' && ($item = Item::find($r->target_id))) {
                $item->update($d);
                $kemas++;
            } else {
                $item = Item::create($d + ['uom' => 'unit', 'is_stock' => true]);
                $r->update(['target_id' => $item->id]);
                $baru++;
            }
        }

        return ['created' => $baru, 'updated' => $kemas];
    }

    private function simpanPelanggan($rows): array
    {
        $baru = 0;
        $kemas = 0;
        $tid = app(TenantContext::class)->id();
        foreach ($rows as $r) {
            $d = array_intersect_key($r->data, array_flip((new Customer)->getFillable()));
            if ($r->action === 'update' && ($c = Customer::find($r->target_id))) {
                $c->update($d);
                $kemas++;
            } else {
                // customer_code unik secara global: sertakan id tenant supaya tiada pertembungan antara syarikat.
                $c = Customer::create($d + ['customer_code' => NumberSeries::next('customer', 'C'.$tid), 'status' => 'active']);
                $r->update(['target_id' => $c->id]);
                $baru++;
            }
        }

        return ['created' => $baru, 'updated' => $kemas];
    }

    private function simpanStokAwal($rows, ImportJob $job): array
    {
        $svc = app(MovementService::class);
        $dok = [];
        foreach ($rows->groupBy(fn ($r) => $r->data['warehouse_id']) as $gudang => $kump) {
            try {
                $m = $svc->create(['type' => 'receipt', 'to_warehouse_id' => (int) $gudang, 'reason' => 'Stok awal (import #'.$job->id.')',
                    'reference' => 'IMPORT-'.$job->id,
                    'lines' => $kump->map(fn ($r) => ['item_id' => $r->target_id, 'qty' => $r->data['qty'], 'unit_cost' => $r->data['unit_cost']])->values()->all()]);
                $svc->post($m);
            } catch (ValidationException $e) {
                throw new RuntimeException('Stok awal gagal: '.collect($e->errors())->flatten()->first());
            }
            $dok[] = $m->number;
        }

        return ['movements' => $dok, 'lines' => $rows->count(),
            'value' => round($rows->sum(fn ($r) => (float) $r->data['qty'] * (float) $r->data['unit_cost']), 2)];
    }

    // ── pembantu ───────────────────────────────────────────

    public function padanTajuk(string $jenis, array $tajuk): array
    {
        $norm = array_map(fn ($h) => mb_strtolower(trim(preg_replace('/[\s_]+/', ' ', preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), " *:")), $tajuk);
        $peta = [];
        foreach (self::MEDAN[$jenis] as $medan => [$sinonim]) {
            foreach ($norm as $i => $h) {
                if (in_array($h, $sinonim, true) && ! in_array($i, $peta, true)) {
                    $peta[$medan] = $i;
                    break;
                }
            }
        }

        return $peta;
    }

    private function sahkanPeta(string $jenis, array $peta, int $bilKol): array
    {
        $bersih = [];
        foreach ($peta as $medan => $kol) {
            if (! isset(self::MEDAN[$jenis][$medan])) {
                throw new RuntimeException("Medan tidak dikenali: {$medan}");
            }
            if ($kol === null || $kol === '') {
                continue;
            }
            if (! is_numeric($kol) || (int) $kol < 0 || (int) $kol >= $bilKol) {
                throw new RuntimeException("Lajur untuk {$medan} di luar julat.");
            }
            $bersih[$medan] = (int) $kol;
        }

        return $bersih;
    }

    /** Nombor telefon Malaysia → format 60XXXXXXXXX. */
    public function telefon(string $p): ?string
    {
        $d = preg_replace('/\D+/', '', $p);
        if (str_starts_with($d, '0')) {
            $d = '6'.$d;
        } elseif (str_starts_with($d, '1') && strlen($d) >= 9 && strlen($d) <= 10) {
            $d = '60'.$d;
        }

        return preg_match('/^60\d{8,10}$/', $d) ? $d : (preg_match('/^\d{8,15}$/', $d) && ! str_starts_with($d, '60') ? $d : null);
    }

    private function nombor(string $s): ?float
    {
        $s = str_replace([',', 'RM', 'rm', ' '], '', trim($s));

        return is_numeric($s) ? (float) $s : null;
    }

    private function bacaCsv(string $csv): array
    {
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);
        $baris = preg_split('/\r\n|\n|\r/', rtrim($csv));
        $pemisah = substr_count($baris[0] ?? '', ';') > substr_count($baris[0] ?? '', ',') ? ';'
            : (substr_count($baris[0] ?? '', "\t") > substr_count($baris[0] ?? '', ',') ? "\t" : ',');

        return array_map(fn ($b) => str_getcsv($b, $pemisah, '"', '\\'), $baris);
    }
}
