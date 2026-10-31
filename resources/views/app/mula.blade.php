@extends('layouts.app')

@section('title', 'Mula Berniaga')

@section('content')
<style>
    .mb-wrap { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 900px) { .mb-wrap { grid-template-columns: 1fr; } }
    .mb-step { display: flex; gap: 10px; align-items: center; font-weight: 700; margin-bottom: 8px; }
    .mb-step b { width: 26px; height: 26px; border-radius: 50%; display: inline-grid; place-items: center; background: var(--brand); color: #1a1205; font-size: 13px; }
    .mb-row { display: flex; justify-content: space-between; gap: 8px; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 14px; }
    .mb-done { text-align: center; padding: 24px 8px; }
    .mb-done .big { font-size: 40px; }
    #menu { min-height: 220px; font-family: inherit; }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">Mula Berniaga</h1>
        <p class="page-desc">Tampal menu atau senarai harga anda. Ejen Penyediaan akan sediakan gudang, kaunter dan produk, dan kedai terus boleh menjual.</p>
    </div>
</div>

<div class="mb-wrap">
    <div class="card">
        <div class="mb-step"><b>1</b> Maklumat kedai</div>
        <div class="field"><label class="label" for="namaKedai">Nama kedai</label><input class="input" id="namaKedai" placeholder="Kedai Bunga Sari"></div>
        <div class="field"><label class="label" for="kategori">Kategori produk (pilihan)</label><input class="input" id="kategori" placeholder="Makanan / Bunga / Pakaian"></div>
        <div class="mb-step mt-4"><b>2</b> Tampal menu / senarai harga</div>
        <div class="field">
            <textarea class="input" id="menu" placeholder="Satu produk satu baris, contoh:&#10;Nasi Lemak Ayam 8.50&#10;Teh Tarik RM3&#10;Jambangan Ros 25, 10   ← nombor ketiga = stok awal"></textarea>
            <p class="text-xs text-muted mt-2">Boleh salin terus dari WhatsApp, Excel atau gambar menu yang ditaip. Baris tanpa harga diabaikan.</p>
        </div>
    </div>

    <div class="card">
        <div class="mb-step"><b>3</b> Semak sebelum siapkan</div>
        <div id="pratonton"><div class="empty"><div class="empty-text">Senarai produk akan muncul di sini semasa anda menaip.</div></div></div>
        <div id="ralat" class="alert alert-danger mt-4" hidden></div>
        <button class="btn btn-primary btn-block btn-lg mt-4" id="btnSiap" onclick="siapkan()" disabled>Siapkan kedai</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
let pemasa = null, bilangan = 0;

function badan(pratonton) {
    return { tool: 'penyediaan.sediakan_kedai', agent: 'ejen_penyediaan', params: {
        nama_kedai: App.$('#namaKedai').value.trim() || 'Kedai Saya',
        kategori: App.$('#kategori').value.trim() || null,
        menu: App.$('#menu').value, pratonton,
    } };
}

async function pratonton() {
    const ralat = App.$('#ralat');
    ralat.hidden = true;
    if (!App.$('#menu').value.trim()) { App.$('#btnSiap').disabled = true; return; }
    try {
        const r = await App.api.post('/api/v1/ai-agents/tools/run', badan(true));
        const sedia = new Set(r.hasil.sedia_ada ?? []);
        bilangan = r.hasil.produk.length;
        App.$('#pratonton').innerHTML = `<p class="text-sm mb-2"><b>${bilangan}</b> produk dikenal pasti:</p>` + r.hasil.produk.map(p => `
            <div class="mb-row"><span>${App.escapeHtml(p.nama)}${sedia.has(p.nama) ? ' <span class="badge badge-neutral">sudah ada</span>' : ''}</span>
            <span>${App.rupiah(p.harga)}${p.stok !== null ? ` · stok ${p.stok}` : ''}</span></div>`).join('');
        App.$('#btnSiap').disabled = !bilangan;
    } catch (e) {
        App.$('#pratonton').innerHTML = '';
        ralat.textContent = e.data?.mesej ?? e.message;
        ralat.hidden = false;
        App.$('#btnSiap').disabled = true;
    }
}
App.$('#menu').addEventListener('input', () => { clearTimeout(pemasa); pemasa = setTimeout(pratonton, 500); });

async function siapkan() {
    await App.withLoading(App.$('#btnSiap'), async () => {
        try {
            const r = await App.api.post('/api/v1/ai-agents/tools/run', badan(false));
            const h = r.hasil;
            App.$('#pratonton').innerHTML = `<div class="mb-done"><div class="big">✅</div>
                <h2 class="card-title">Kedai siap berniaga</h2>
                <p class="text-sm">${h.produk_dicipta} produk baharu${h.produk_sedia_ada ? `, ${h.produk_sedia_ada} sudah ada` : ''} · kaunter <b>${App.escapeHtml(h.kaunter)}</b> · gudang <b>${App.escapeHtml(h.gudang)}</b></p>
                <a class="btn btn-primary btn-lg mt-4" href="/app/pos">Buka kaunter & mula jual</a></div>`;
            App.$('#btnSiap').hidden = true;
        } catch (e) {
            const ralat = App.$('#ralat');
            ralat.textContent = e.data?.mesej ?? e.message;
            ralat.hidden = false;
        }
    });
}

(async () => {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    App.$('#namaKedai').value = App.state.tenant ? (App.state.tenants.find(t => t.ulid === App.state.tenant)?.name ?? '') : '';
})();
</script>
@endpush
