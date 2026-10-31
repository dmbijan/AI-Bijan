@extends('layouts.app')

@section('title', 'Meja & Pesanan')

@section('content')
<style>
.pm-board{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
.pm-order{border:1px solid var(--border);background:var(--surface);border-radius:14px;padding:14px;display:flex;flex-direction:column;gap:8px}
.pm-order[data-s=submitted]{border-color:var(--brand);box-shadow:0 0 0 1px var(--brand)}
.pm-order-head{display:flex;justify-content:space-between;align-items:baseline;gap:8px}
.pm-table{font-weight:800;font-size:18px}
.pm-time{font-size:12px;color:var(--text-muted)}
.pm-line{font-size:14px}
.pm-line b{display:inline-block;min-width:28px}
.pm-note{font-size:12px;color:var(--text-muted);margin-left:28px}
.pm-actions{display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:auto}
.pm-qr{display:flex;justify-content:center;padding:10px;background:#fff;border-radius:10px;margin:8px 0}
.pm-qr svg{width:150px;height:150px}
.pm-url{font-size:11px;word-break:break-all;color:var(--text-muted)}
@media print{
  body *{visibility:hidden}
  .pm-print, .pm-print *{visibility:visible}
  .pm-print{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;font-family:sans-serif;color:#000}
  .pm-print svg{width:260px;height:260px}
}
</style>
<div class="page-head">
    <div>
        <h1 class="page-title">Meja &amp; Pesanan</h1>
        <p class="page-desc">Pelanggan imbas QR di meja dan pesan sendiri. Terima pesanan untuk jadikan jualan, kemudian bayar di POS.</p>
    </div>
    <button class="btn btn-sm" onclick="muatPesanan()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="pesanan">Pesanan <span class="badge badge-warning" id="cntBaru" hidden></span></button>
    <button class="tab" role="tab" aria-selected="false" data-tab="meja">Meja &amp; QR</button>
</div>

<section data-panel="pesanan">
    <div class="pm-board" id="board"><div class="skeleton" style="height:160px"></div></div>
</section>

<section data-panel="meja" hidden>
    <div class="card mb-4">
        <div class="card-header"><div><h2 class="card-title">Tambah Meja</h2><p class="card-subtitle">Setiap meja dapat QR sendiri. Cetak dan letak atas meja.</p></div></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
            <div class="field" style="margin:0"><label class="label" for="areaSel">Kawasan</label><select class="select" id="areaSel" style="min-width:160px"></select></div>
            <div class="field" style="margin:0"><label class="label" for="tblName">Nama meja</label><input class="input" id="tblName" placeholder="Meja 1" style="max-width:200px"></div>
            <button class="btn btn-primary" onclick="tambahMeja()">Tambah Meja</button>
            <span style="flex:1"></span>
            <div class="field" style="margin:0"><label class="label" for="areaName">Kawasan baharu</label><input class="input" id="areaName" placeholder="Dalam / Luar" style="max-width:180px"></div>
            <button class="btn" onclick="tambahKawasan()">Tambah Kawasan</button>
        </div>
    </div>
    <div class="grid grid-4" id="tblGrid"><div class="skeleton" style="height:220px"></div></div>
</section>
@endsection


@push('scripts')
<script src="{{ asset('assets/lib/qrcode-generator-1.4.4.min.js') }}"></script>
<script>
const LABEL = { submitted: 'Baharu', accepted: 'Diterima' };
let dahNampak = new Set();
let pertama = true;

document.querySelectorAll('[data-tab]').forEach(tab => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-tab]').forEach(x => x.setAttribute('aria-selected', 'false'));
    tab.setAttribute('aria-selected', 'true');
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
}));

function senarai(r) { return Array.isArray(r) ? r : (r?.data ?? []); }

// ---- pesanan ----
async function muatPesanan() {
    let rows;
    try {
        const [baru, terima] = await Promise.all([
            App.api.get('/api/v1/pos/dine/orders?status=submitted'),
            App.api.get('/api/v1/pos/dine/orders?status=accepted'),
        ]);
        rows = [...senarai(baru), ...senarai(terima)];
    } catch (e) { App.$('#board').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`; return; }

    const baharu = rows.filter(o => o.status === 'submitted' && !dahNampak.has(o.id));
    rows.forEach(o => dahNampak.add(o.id));
    if (!pertama && baharu.length) App.toast(`${baharu.length} pesanan meja baharu`, 'success');
    pertama = false;

    const n = rows.filter(o => o.status === 'submitted').length;
    App.$('#cntBaru').hidden = !n;
    App.$('#cntBaru').textContent = n;
    App.$('#board').innerHTML = rows.length ? rows.map(o => `
        <article class="pm-order" data-s="${o.status}">
            <div class="pm-order-head">
                <span class="pm-table">${App.escapeHtml(o.table?.name ?? '—')}</span>
                <span class="badge badge-${o.status === 'submitted' ? 'warning' : 'success'}">${LABEL[o.status] ?? o.status}</span>
            </div>
            <div class="pm-time">${App.escapeHtml(o.order_no ?? '')} · ${new Date(o.submitted_at).toLocaleTimeString('ms-MY', { hour: '2-digit', minute: '2-digit' })}</div>
            <div>${(o.items ?? []).map(it => `<div class="pm-line"><b>${Number(it.quantity)}×</b> ${App.escapeHtml(it.item_name)}</div>${it.notes ? `<div class="pm-note">» ${App.escapeHtml(it.notes)}</div>` : ''}`).join('')}</div>
            ${o.notes ? `<div class="text-sm" style="padding:8px;border-radius:8px;background:var(--bg-subtle)">${App.escapeHtml(o.notes)}</div>` : ''}
            <div class="text-sm" style="text-align:right;font-weight:700">${App.rupiah(o.subtotal)}</div>
            <div class="pm-actions">
                ${o.status === 'submitted'
                    ? `<button class="btn btn-sm btn-primary" onclick="terima(${o.id})">Terima</button><button class="btn btn-sm btn-ghost" onclick="tolak(${o.id})">Tolak</button>`
                    : `<a class="btn btn-sm btn-primary" style="grid-column:1/-1" href="/app/pos">Bayar di POS → tab Jualan</a>`}
            </div>
        </article>`).join('') : '<div class="card" style="grid-column:1/-1"><div class="empty"><div class="empty-title">Tiada pesanan terbuka</div><div class="empty-text">Pesanan dari QR meja akan muncul di sini secara automatik.</div></div></div>';
}

async function terima(id) {
    try {
        const r = await App.api.post(`/api/v1/pos/dine/orders/${id}/accept`, {});
        App.toast(`Diterima: jualan draf ${r.sale_no} (${App.rupiah(r.total_amount)}). Bayar di POS → Jualan.`, 'success');
        muatPesanan();
    } catch (e) { App.toast(e.message, 'error'); }
}
async function tolak(id) {
    const sebab = prompt('Sebab tolak (pilihan):') ;
    if (sebab === null) return;
    try { await App.api.post(`/api/v1/pos/dine/orders/${id}/reject`, { reason: sebab || null }); muatPesanan(); }
    catch (e) { App.toast(e.message, 'error'); }
}

// ---- meja & QR ----
function urlMeja(token) { return `${location.origin}/meja/${token}`; }
function svgQr(text) { const q = qrcode(0, 'M'); q.addData(text); q.make(); return q.createSvgTag({ cellSize: 4, margin: 2, scalable: true }); }

async function muatMeja() {
    try {
        const [areas, tables] = await Promise.all([App.api.get('/api/v1/pos/dine/areas'), App.api.get('/api/v1/pos/dine/tables')]);
        const a = senarai(areas);
        window._areas = a;
        const pilih = App.$('#areaSel').value;
        App.$('#areaSel').innerHTML = a.length ? a.map(x => `<option value="${x.id}">${App.escapeHtml(x.name)}</option>`).join('') : '<option value="">(Tambah kawasan dahulu)</option>';
        if (pilih && a.some(x => String(x.id) === pilih)) App.$('#areaSel').value = pilih;
        const rows = senarai(tables);
        window._tables = rows;
        App.$('#tblGrid').innerHTML = rows.length ? rows.map((m, i) => `
            <div class="card">
                <div class="card-header">
                    <div><h3 class="card-title">${App.escapeHtml(m.name)}</h3><p class="card-subtitle">${App.escapeHtml(m.area?.name ?? '')} · ${m.open_orders ? m.open_orders + ' pesanan terbuka' : 'Tiada pesanan'}</p></div>
                    <span class="badge badge-${m.is_active ? 'success' : 'neutral'}">${m.is_active ? 'Aktif' : 'Tutup'}</span>
                </div>
                ${m.qr_token ? `<div class="pm-qr">${svgQr(urlMeja(m.qr_token))}</div><div class="pm-url">${App.escapeHtml(urlMeja(m.qr_token))}</div>` : '<p class="text-sm text-muted">Tiada QR. Tekan "Jana QR".</p>'}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:10px">
                    <button class="btn btn-sm btn-primary" onclick="cetakQr(${i})" ${m.qr_token ? '' : 'disabled'}>Cetak QR</button>
                    <button class="btn btn-sm" onclick="tokenBaharu(${m.id}, ${m.qr_token ? 'true' : 'false'})">${m.qr_token ? 'Tukar QR' : 'Jana QR'}</button>
                </div>
            </div>`).join('') : '<div class="card" style="grid-column:1/-1"><div class="empty"><div class="empty-text">Belum ada meja. Tambah kawasan, kemudian meja.</div></div></div>';
    } catch (e) { App.$('#tblGrid').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`; }
}

async function tambahKawasan() {
    const name = App.$('#areaName').value.trim();
    if (!name) { App.toast('Nama kawasan diperlukan', 'error'); return; }
    try { await App.api.post('/api/v1/pos/dine/areas', { name }); App.$('#areaName').value = ''; App.toast('Kawasan ditambah', 'success'); muatMeja(); }
    catch (e) { App.toast(e.message, 'error'); }
}
async function tambahMeja() {
    const name = App.$('#tblName').value.trim();
    const area = Number(App.$('#areaSel').value) || (window._areas?.[0]?.id ?? 0);
    if (!area) { App.toast('Tambah kawasan dahulu', 'error'); return; }
    if (!name) { App.toast('Nama meja diperlukan', 'error'); return; }
    try { await App.api.post('/api/v1/pos/dine/tables', { name, dine_area_id: area }); App.$('#tblName').value = ''; App.toast('Meja ditambah', 'success'); muatMeja(); }
    catch (e) { App.toast(e.message, 'error'); }
}
async function tokenBaharu(id, ada) {
    if (ada && !confirm('QR lama di meja akan berhenti berfungsi. Teruskan?')) return;
    try { await App.api.post(`/api/v1/pos/dine/tables/${id}/qr`); App.toast('QR baharu dijana. Cetak semula.', 'success'); muatMeja(); }
    catch (e) { App.toast(e.message, 'error'); }
}
function cetakQr(i) {
    const m = window._tables[i];
    const el = document.createElement('div');
    el.className = 'pm-print';
    el.innerHTML = `<div style="font-size:28px;font-weight:800">${App.escapeHtml(m.name)}</div>${svgQr(urlMeja(m.qr_token))}<div style="font-size:16px">Imbas untuk lihat menu &amp; pesan</div><div style="font-size:13px">Scan to view menu &amp; order</div>`;
    document.body.appendChild(el);
    window.print();
    el.remove();
}

(async () => {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await Promise.all([muatPesanan(), muatMeja()]);
    setInterval(muatPesanan, 15000);
})();
</script>
@endpush
