<?php

namespace App\Modules\AIAgents\Tools;

use App\Models\Item;
use App\Models\Warehouse;
use App\Modules\Inventory\StockEngine;
use App\Modules\POS\Models\POSRegister;
use App\Modules\POS\Services\RegisterService;
use App\Support\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Ejen Penyediaan (Fasa 1.2): kedai siap berniaga dalam satu langkah.
 * Gudang outlet + kaunter (dipaut ke gudang) + produk daripada teks menu biasa + stok awal pilihan.
 * Idempoten secara praktikal: produk dengan nama sama tidak dicipta dua kali; kaunter/gudang guna semula jika wujud.
 */
class PenyediaanKedai extends AgentTool
{
    public function name(): string { return 'penyediaan.sediakan_kedai'; }

    public function description(): string { return 'Sediakan kedai: gudang, kaunter dan produk daripada teks menu (satu baris = "Nama Harga [Stok]").'; }

    public function rules(): array
    {
        return [
            'nama_kedai' => 'required|string|max:80',
            'menu' => 'required|string|max:20000',
            'kategori' => 'nullable|string|max:80',
            'pratonton' => 'nullable|boolean',
        ];
    }

    public function permission(): string { return 'inventory.manage'; }

    /** "Nasi Lemak Ayam 8.50" | "Teh Tarik - RM3" | "Kopi O, 2.80, 50" → [nama, harga, stok?] */
    public static function huraiMenu(string $menu): array
    {
        $out = [];
        foreach (preg_split('/\R/u', $menu) as $baris) {
            // Buang penanda senarai ("1.", "2)", "-", "*", "•") tanpa menyentuh nama seperti "7Up".
            $baris = trim(preg_replace('/^\s*(?:[\-\*•]+|\d+[\.\)])\s+/u', '', trim($baris)));
            if ($baris === '') {
                continue;
            }
            if (! preg_match('/^(.*?)[\s,;:\-–]*(?:RM\s*)?(\d+(?:[.,]\d{1,2})?)(?:\s*[,;x×]?\s*(\d+))?\s*$/iu', $baris, $m)) {
                continue;
            }
            $nama = trim($m[1], " \t,;:-–");
            if ($nama === '' || mb_strlen($nama) > 150) {
                continue;
            }
            $out[] = ['nama' => $nama, 'harga' => (float) str_replace(',', '.', $m[2]), 'stok' => isset($m[3]) && $m[3] !== '' ? (int) $m[3] : null];
        }

        return $out;
    }

    public function handle(array $p): array
    {
        $produk = self::huraiMenu($p['menu']);
        if (! $produk) {
            throw new \RuntimeException('Tiada produk dikenal pasti. Tulis satu produk satu baris, contoh: "Nasi Lemak 8.50".');
        }
        if (! empty($p['pratonton'])) {
            return ['pratonton' => true, 'produk' => $produk, 'sedia_ada' => Item::whereIn('name', array_column($produk, 'nama'))->pluck('name')->all()];
        }

        return DB::transaction(function () use ($p, $produk) {
            $wh = Warehouse::where('type', 'outlet')->orderBy('id')->first()
                ?? Warehouse::create(['code' => 'KDI', 'name' => $p['nama_kedai'], 'type' => 'outlet']);

            $reg = POSRegister::where('is_active', true)->orderBy('id')->first()
                ?? app(RegisterService::class)->createRegister(['name' => 'Kaunter 1', 'location' => $p['nama_kedai']], (int) app(TenantContext::class)->id(), (int) auth()->id());
            if (! $reg->warehouse_id) {
                $reg->update(['warehouse_id' => $wh->id]);
            }

            $dicipta = $sedia = 0;
            foreach ($produk as $i => $pr) {
                $item = Item::where('name', $pr['nama'])->first();
                if ($item) {
                    $sedia++;

                    continue;
                }
                $item = Item::create([
                    'sku' => $this->sku($pr['nama']), 'name' => $pr['nama'], 'uom' => 'unit', 'category' => $p['kategori'] ?? null,
                    'sell_price' => $pr['harga'], 'is_stock' => $pr['stok'] !== null, 'is_active' => true,
                ]);
                if ($pr['stok']) {
                    app(StockEngine::class)->receive($item->id, $wh->id, null, $pr['stok'], 0, 'adjustment', $item->id, 'Stok awal (Ejen Penyediaan)');
                }
                $dicipta++;
            }

            return ['gudang' => $wh->name, 'kaunter' => $reg->name, 'produk_dicipta' => $dicipta, 'produk_sedia_ada' => $sedia, 'dibaca' => count($produk)];
        });
    }

    public function verify(array $p, array $hasil): bool
    {
        if (! empty($p['pratonton'])) {
            return true;
        }
        $nama = array_column(self::huraiMenu($p['menu']), 'nama');

        return Item::whereIn('name', $nama)->count() === count(array_unique($nama));
    }

    private function sku(string $nama): string
    {
        $asas = Str::upper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', Str::ascii($nama)), 0, 4)) ?: 'ITEM';
        do {
            $sku = $asas.random_int(100, 999);
        } while (Item::where('sku', $sku)->exists());

        return $sku;
    }
}
