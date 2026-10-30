@extends('layouts.app')

@section('title', 'Meja & Dapur')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Meja &amp; Dapur</h1>
        <p class="page-desc">Pelanggan imbas QR di meja, pesan sendiri, pesanan masuk dapur, bayar di kaunter.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <label class="text-sm" style="display:flex;gap:6px;align-items:center"><input type="checkbox" id="autoTiket"> Cetak tiket dapur automatik</label>
        <button class="btn btn-sm" onclick="muatPesanan()">Muat Semula</button>
    </div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="dapur">Pesanan <span class="badge badge-warning" id="cntOpen" hidden></span></button>
    <button class="tab" role="tab" aria-selected="false" data-tab="meja">Meja &amp; QR</button>
</div>

<section data-panel="dapur">
    <div class="pm-board" id="board"><div class="skeleton" style="height:160px"></div></div>
</section>

<section data-panel="meja" hidden>
    <div class="card mb-4">
        <div class="card-header"><div><h2 class="card-title">Tambah Meja</h2><p class="card-subtitle">Setiap meja dapat QR sendiri. Cetak dan letak atas meja.</p></div></div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <input class="input" id="tblName" placeholder="Meja 1" style="max-width:240px">
            <button class="btn btn-primary" onclick="tambahMeja()">Tambah</button>
        </div>
    </div>
    <div class="grid grid-4" id="tblGrid"><div class="skeleton" style="height:220px"></div></div>
</section>
@endsection

@push('styles')
<style>
.pm-board{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px}
.pm-order{border:1px solid var(--border);background:var(--surface);border-radius:14px;padding:14px;display:flex;flex-direction:column;gap:8px}
.pm-order[data-s=baru]{border-color:var(--brand);box-shadow:0 0 0 1px var(--brand)}
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
@endpush

@push('scripts')
<script src="{{ asset('assets/vendor/qrcode-generator-1.4.4.min.js') }}"></script>
<script src="{{ asset('assets/pos-kaunter.js') }}?v={{ @filemtime(public_path('assets/pos-kaunter.js')) }}"></script>
<script>
const LABEL = { baru: 'Baru', dimasak: 'Dimasak', siap: 'Siap', dihantar: 'Dihantar' };
const SETERUSNYA = { baru: ['dimasak', 'Mula masak'], dimasak: ['siap', 'Siap'], siap: ['dihantar', 'Dah hantar'] };
let dahNampak = new Set();
let pertama = true;

document.querySelectorAll('[data-tab]').forEach(tab => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-tab]').forEach(x => x.setAttribute('aria-selected', 'false'));
    tab.setAttribute('aria-selected', 'true');
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
}));

const K_AUTO = 'aib-dapur-auto';
App.$('#autoTiket').checked = (() => { try { return localStorage.getItem(K_AUTO) === '1'; } catch { return false; } })();
App.$('#autoTiket').addEventListener('change', e => { try { localStorage.setItem(K_AUTO, e.target.checked ? '1' : '0'); } catch {} });

// ---- pesanan dapur ----
async function muatPesanan() {
    let rows;
    try { rows = await App.api.get('/api/v1/pos/table-orders'); }
    catch (e) { App.$('#board').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`; return; }

    const baru = rows.filter(o => !dahNampak.has(o.id));
    rows.forEach(o => dahNampak.add(o.id));
    if (!pertama && baru.length) {
        App.toast(`${baru.length} pesanan baharu`, 'success');
        if (App.$('#autoTiket').checked) for (const o of baru) await cetakTiket(o, true);
    }
    pertama = false;
    window._orders = rows;

    App.$('#cntOpen').hidden = !rows.length;
    App.$('#cntOpen').textContent = rows.length;
    App.$('#board').innerHTML = rows.length ? rows.map((o, i) => `
        <article class="pm-order" data-s="${o.status}">
            <div class="pm-order-head">
                <span class="pm-table">${App.escapeHtml(o.table?.name ?? '—')}</span>
                <span class="badge badge-${o.status === 'baru' ? 'warning' : o.status === 'dimasak' ? 'info' : 'success'}">${LABEL[o.status] ?? o.status}</span>
            </div>
            <div class="pm-time">${new Date(o.created_at).toLocaleTimeString('ms-MY', { hour: '2-digit', minute: '2-digit' })}${o.customer_name ? ' · ' + App.escapeHtml(o.customer_name) : ''}</div>
            <div>${o.items.map(it => `<div class="pm-line"><b>${Number(it.quantity)}×</b> ${App.escapeHtml(it.name)}</div>${it.note ? `<div class="pm-note">» ${App.escapeHtml(it.note)}</div>` : ''}`).join('')}</div>
            ${o.notes ? `<div class="text-sm" style="padding:8px;border-radius:8px;background:var(--bg-subtle)">${App.escapeHtml(o.notes)}</div>` : ''}
            <div class="text-sm" style="text-align:right;font-weight:700">${App.rupiah(o.total_amount)}</div>
            <div class="pm-actions">
                ${SETERUSNYA[o.status] ? `<button class="btn btn-sm btn-primary" onclick="setStatus(${o.id},'${SETERUSNYA[o.status][0]}')">${SETERUSNYA[o.status][1]}</button>` : '<span></span>'}
                <button class="btn btn-sm" onclick="cetakTiket(window._orders[${i}])">Tiket Dapur</button>
                <button class="btn btn-sm" onclick="bayar(${i})">Bayar di Kaunter</button>
                <button class="btn btn-sm btn-ghost" onclick="setStatus(${o.id},'batal')">Batal</button>
            </div>
        </article>`).join('') : '<div class="card" style="grid-column:1/-1"><div class="empty"><div class="empty-title">Tiada pesanan terbuka</div><div class="empty-text">Pesanan dari QR meja akan muncul di sini secara automatik.</div></div></div>';
}

async function setStatus(id, status) {
    if (status === 'batal' && !confirm('Batalkan pesanan ini?')) return;
    try { await App.api.post(`/api/v1/pos/table-orders/${id}/status`, { status }); muatPesanan(); }
    catch (e) { App.toast(e.message, 'error'); }
}

async function cetakTiket(o, auto = false) {
    const t = PosPrinter.tetapan();
    try {
        await PosPrinter.cetak(PosPrinter.binaTiketDapur({
            meja: o.table?.name ?? '', masa: new Date(o.created_at).toLocaleTimeString('ms-MY', { hour: '2-digit', minute: '2-digit' }),
            pelanggan: o.customer_name, nota: o.notes,
            items: o.items.map(it => ({ nama: it.name, qty: Number(it.quantity), nota: it.note })),
        }, { saiz: t.saiz }));
    } catch (e) { if (!auto) App.toast(e.message + ' (sambung printer di POS → Tetapan Kaunter)', 'error'); }
}

// Hantar pesanan ke troli kaunter POS; jualan selesai akan tutup pesanan ini.
function bayar(i) {
    const o = window._orders[i];
    try {
        sessionStorage.setItem('aib-pos-pesanan-meja', JSON.stringify({
            order_id: o.id, meja: o.table?.name,
            items: o.items.map(it => ({ item_id: it.item_id, name: it.name, sku: null, price: Number(it.unit_price), qty: Number(it.quantity) })),
        }));
    } catch {}
    location.href = '/app/pos';
}

// ---- meja & QR ----
function urlMeja(token) { return `${location.origin}/meja/${token}`; }
function svgQr(text) { const q = qrcode(0, 'M'); q.addData(text); q.make(); return q.createSvgTag({ cellSize: 4, margin: 2, scalable: true }); }

async function muatMeja() {
    try {
        const rows = await App.api.get('/api/v1/pos/tables');
        window._tables = rows;
        App.$('#tblGrid').innerHTML = rows.length ? rows.map((m, i) => `
            <div class="card">
                <div class="card-header">
                    <div><h3 class="card-title">${App.escapeHtml(m.name)}</h3><p class="card-subtitle">${m.open_orders ? m.open_orders + ' pesanan terbuka' : 'Tiada pesanan'}</p></div>
                    <span class="badge badge-${m.is_active ? 'success' : 'neutral'}">${m.is_active ? 'Aktif' : 'Tutup'}</span>
                </div>
                <div class="pm-qr">${svgQr(urlMeja(m.token))}</div>
                <div class="pm-url">${App.escapeHtml(urlMeja(m.token))}</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-top:10px">
                    <button class="btn btn-sm btn-primary" onclick="cetakQr(${i})">Cetak QR</button>
                    <button class="btn btn-sm" onclick="togolMeja(${i})">${m.is_active ? 'Tutup' : 'Buka'}</button>
                    <button class="btn btn-sm btn-ghost" style="grid-column:1/-1" onclick="tokenBaharu(${m.id})">Tukar QR (QR lama mati)</button>
                </div>
            </div>`).join('') : '<div class="card" style="grid-column:1/-1"><div class="empty"><div class="empty-text">Belum ada meja. Tambah satu di atas.</div></div></div>';
    } catch (e) { App.$('#tblGrid').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`; }
}

async function tambahMeja() {
    const name = App.$('#tblName').value.trim();
    if (!name) { App.toast('Nama meja diperlukan', 'error'); return; }
    try { await App.api.post('/api/v1/pos/tables', { name }); App.$('#tblName').value = ''; App.toast('Meja ditambah', 'success'); muatMeja(); }
    catch (e) { App.toast(e.message, 'error'); }
}
async function togolMeja(i) {
    const m = window._tables[i];
    try { await App.api.put(`/api/v1/pos/tables/${m.id}`, { is_active: !m.is_active }); muatMeja(); }
    catch (e) { App.toast(e.message, 'error'); }
}
async function tokenBaharu(id) {
    if (!confirm('QR lama di meja akan berhenti berfungsi. Teruskan?')) return;
    try { await App.api.post(`/api/v1/pos/tables/${id}/token`); App.toast('QR baharu dijana. Cetak semula.', 'success'); muatMeja(); }
    catch (e) { App.toast(e.message, 'error'); }
}
function cetakQr(i) {
    const m = window._tables[i];
    const el = document.createElement('div');
    el.className = 'pm-print';
    el.innerHTML = `<div style="font-size:28px;font-weight:800">${App.escapeHtml(m.name)}</div>${svgQr(urlMeja(m.token))}<div style="font-size:16px">Imbas untuk lihat menu &amp; pesan</div><div style="font-size:13px">Scan to view menu &amp; order</div>`;
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
