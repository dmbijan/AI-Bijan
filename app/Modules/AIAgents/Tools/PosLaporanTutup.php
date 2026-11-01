<?php

namespace App\Modules\AIAgents\Tools;

use App\Modules\POS\Models\POSSaleItem;
use App\Support\Tenancy\TenantContext;

/**
 * Ejen Kaunter (Fasa 1.2): laporan tutup kedai dalam BM.
 * Gabung alat lain (ringkasan jualan, semakan stok, invois lewat) + produk terlaris.
 * Teks sedia untuk dihantar ke WhatsApp owner (Fasa 1.5).
 */
class PosLaporanTutup extends AgentTool
{
    public function name(): string { return 'pos.laporan_tutup_kedai'; }

    public function description(): string { return 'Laporan tutup kedai: jualan, kaedah bayaran, produk terlaris, isu stok, invois lewat.'; }

    public function rules(): array { return ['tarikh' => 'nullable|date']; }

    public function permission(): string { return 'pos.view'; }

    public function handle(array $p): array
    {
        $ctx = app(TenantContext::class);
        $jualan = app(PosRingkasanHari::class)->handle($p);
        $hari = $jualan['tarikh'];

        $terlaris = POSSaleItem::query()->join('pos_sales', 'pos_sales.id', '=', 'pos_sale_items.sale_id')
            ->where('pos_sales.tenant_id', $ctx->id())->where('pos_sales.status', 'completed')->whereDate('pos_sales.sale_date', $hari)
            ->selectRaw('pos_sale_items.product_name as nama, sum(pos_sale_items.quantity) as qty, sum(pos_sale_items.line_total) as jumlah')
            ->groupBy('pos_sale_items.product_name')->orderByDesc('jumlah')->limit(5)->get()
            ->map(fn ($r) => ['nama' => $r->nama, 'qty' => (float) $r->qty, 'jumlah' => round((float) $r->jumlah, 2)])->all();

        $stok = app(PosSemakanStok::class)->handle([]);
        $invois = $ctx->can('invoicing.view') ? app(InvoisBelumBayar::class)->handle(['had' => 5]) : null;

        $rm = fn ($n) => 'RM '.number_format((float) $n, 2);
        $baris = ["*Tutup kedai — {$hari}*", "Jualan: {$rm($jualan['jumlah'])} ({$jualan['bilangan_jualan']} transaksi, purata {$rm($jualan['purata'])})"];
        foreach ((array) $jualan['ikut_kaedah'] as $kaedah => $amaun) {
            $baris[] = "• {$kaedah}: {$rm($amaun)}";
        }
        if ($terlaris) {
            $baris[] = 'Terlaris: '.implode(', ', array_map(fn ($t) => "{$t['nama']} ×".rtrim(rtrim(number_format($t['qty'], 3, '.', ''), '0'), '.'), $terlaris));
        }
        $baris[] = $stok['bilangan'] ? "⚠️ {$stok['bilangan']} jualan stoknya tak dapat dipotong — semak POS → Kaunter → Semakan Stok." : 'Stok: tiada isu.';
        if ($invois && $invois['bilangan']) {
            $baris[] = "💰 {$invois['bilangan']} invois lewat bayar, jumlah {$rm($invois['jumlah_tertunggak'])}.";
        }
        if ($jualan['bilangan_jualan'] === 0) {
            $baris[] = 'Tiada jualan direkod hari ini.';
        }

        return ['tarikh' => $hari, 'jualan' => $jualan, 'terlaris' => $terlaris, 'isu_stok' => $stok['bilangan'],
            'invois_lewat' => $invois['bilangan'] ?? null, 'teks' => implode("\n", $baris)];
    }
}
