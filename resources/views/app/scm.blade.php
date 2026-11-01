@extends('layouts.app')

@section('title', 'SCM')

@section('content')
<style>
    .tabs { scrollbar-width: none; }
    [data-panel] .stat { min-width: 0; }
    [data-panel] .stat-value { white-space: nowrap; font-size: clamp(1.05rem, 4.6vw, 1.6rem); font-variant-numeric: tabular-nums; }
    .list-row { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; padding: 12px 16px; border-bottom: 1px solid var(--border, #e5e5e5); }
    .list-row:last-child { border-bottom: 0; }
    .list-row .meta { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; }
    .list-row .act { margin-left: auto; display: flex; gap: 6px; flex-wrap: wrap; align-items: center; }
    .num { font-variant-numeric: tabular-nums; font-weight: 600; text-align: right; white-space: nowrap; }
    .bar { height: 6px; border-radius: 3px; background: var(--border, #e5e5e5); overflow: hidden; }
    .bar > i { display: block; height: 100%; background: var(--primary, #b7791f); }
    .qty { width: 84px; }
    .spark { display: flex; gap: 3px; align-items: flex-end; height: 22px; }
    .spark i { width: 7px; background: var(--primary, #b7791f); border-radius: 2px; opacity: .8; }
    @media (max-width: 640px) { [data-panel] .grid-4 { grid-template-columns: 1fr 1fr; } [data-panel] .grid-2 { grid-template-columns: 1fr; } }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">SCM</h1>
        <p class="page-desc">Pesanan semula, ramalan jualan, pesanan belian, kos pendaratan dan maklumat produk.</p>
    </div>
    <button class="btn btn-sm" onclick="muatSemula()">Muat semula</button>
</div>

<div class="tabs" role="tablist" style="overflow-x:auto">
    <button class="tab" role="tab" aria-selected="true" data-tab="reorder">Pesanan semula</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="forecast">Ramalan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="po">Pesanan belian</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="landed">Kos pendaratan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="pim">Maklumat produk</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="suppliers">Pembekal</button>
</div>

{{-- SUP-014 --}}
<section data-panel="reorder">
    <div class="grid grid-4 mb-4">
        <div class="stat"><div class="stat-label">Habis stok</div><div class="stat-value" id="rOut">—</div></div>
        <div class="stat"><div class="stat-label">Segera</div><div class="stat-value" id="rUrgent">—</div></div>
        <div class="stat"><div class="stat-label">Perlu dipesan</div><div class="stat-value" id="rCount">—</div></div>
        <div class="stat"><div class="stat-label">Anggaran nilai</div><div class="stat-value" id="rValue">—</div></div>
    </div>
    <div class="card" style="padding:0">
        <div class="row-between" style="padding:16px;flex-wrap:wrap;gap:8px">
            <p class="text-sm text-muted" style="margin:0;max-width:60ch">Dikira daripada baki stok, PO yang belum tiba, jualan 8 minggu dan masa tiba pembekal. PO dicipta sebagai <b>draf</b> dan perlu diluluskan sebelum dihantar.</p>
            <button class="btn btn-primary btn-sm" onclick="ciptaPo(this)">Cipta PO draf (yang ditanda)</button>
        </div>
        <div id="rList"></div>
    </div>
</section>

{{-- SUP-011 --}}
<section data-panel="forecast" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <div class="field" style="margin:0"><label class="label" for="fDays">Tempoh ramalan</label>
            <select class="select" id="fDays" onchange="loadForecast()"><option value="7">7 hari</option><option value="30" selected>30 hari</option><option value="60">60 hari</option></select></div>
        <p class="text-xs text-muted" id="fMethod" style="margin:0;max-width:50ch"></p>
    </div>
    <div class="card" style="padding:0"><div id="fList"></div></div>
</section>

<section data-panel="po" hidden>
    <div class="grid grid-4 mb-4">
        <div class="stat"><div class="stat-label">PO aktif</div><div class="stat-value" id="sPo">—</div></div>
        <div class="stat"><div class="stat-label">Nilai aktif</div><div class="stat-value" id="sValue">—</div></div>
        <div class="stat"><div class="stat-label">Menunggu kelulusan</div><div class="stat-value" id="sPending">—</div></div>
        <div class="stat"><div class="stat-label">Draf</div><div class="stat-value" id="sDraft">—</div></div>
    </div>
    <div class="card" style="padding:0"><div id="poList"></div></div>
</section>

{{-- SUP-015 --}}
<section data-panel="landed" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <p class="text-sm text-muted" style="margin:0;max-width:60ch">Tambah caj penghantaran, duti, insurans dan kastam kepada kos barang yang diterima. Bahagian untuk barang yang sudah terjual dicatat sebagai kos jualan.</p>
        <button class="btn btn-primary btn-sm" onclick="tambahKosPendaratan()">+ Kos pendaratan</button>
    </div>
    <div class="card" style="padding:0"><div id="lList"></div></div>
</section>

{{-- SUP-013 --}}
<section data-panel="pim" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <div class="row" style="gap:8px;flex-wrap:wrap;align-items:center">
            <input class="input" id="pCari" placeholder="Cari nama atau SKU" style="max-width:260px" oninput="clearTimeout(window._pc); window._pc = setTimeout(loadPim, 300)">
            <label class="text-sm row" style="gap:6px;align-items:center"><input type="checkbox" id="pIncomplete" onchange="loadPim()"> Belum lengkap sahaja</label>
        </div>
        <div class="text-sm">Purata kelengkapan: <b id="pAvg">—</b></div>
    </div>
    <div class="card" style="padding:0"><div id="pList"></div></div>
</section>

<section data-panel="suppliers" hidden>
    <div class="row mb-4" style="justify-content:flex-end"><button class="btn btn-primary btn-sm" onclick="tambahPembekal()">+ Pembekal</button></div>
    <div class="card" style="padding:0"><div id="supList"></div></div>
</section>

<dialog class="dialog" id="dlg">
    <form method="dialog">
        <div class="dialog-header"><h2 class="card-title" id="dlgTitle"></h2></div>
        <div class="dialog-body stack" id="dlgBody"></div>
        <div class="dialog-footer row" style="justify-content:flex-end;gap:8px">
            <button class="btn btn-ghost" value="cancel" formnovalidate>Batal</button>
            <button class="btn btn-primary" value="ok">Simpan</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
const RM = n => App.rupiah(n ?? 0);
const E = s => App.escapeHtml(String(s ?? ''));
const N = n => App.number(Math.round(Number(n ?? 0) * 100) / 100);
const tarikh = d => d ? new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(String(d).slice(0, 10) + 'T00:00:00')) : '—';
const kosong = t => `<div class="empty" style="padding:24px"><div class="empty-text">${E(t)}</div></div>`;
const HARI = ['Ahad', 'Isnin', 'Selasa', 'Rabu', 'Khamis', 'Jumaat', 'Sabtu'];
const STATUS_PO = { draft: ['Draf', 'neutral'], pending_approval: ['Menunggu lulus', 'warning'], approved: ['Lulus', 'success'], sent: ['Dihantar', 'info'], partially_received: ['Diterima separa', 'warning'], fully_received: ['Diterima', 'success'], cancelled: ['Batal', 'danger'] };
const lencana = (map, k) => { const [t, c] = map[k] ?? [k ?? '—', 'neutral']; return `<span class="badge badge-${c}">${E(t)}</span>`; };
const dimuat = {};
let PEMBEKAL = [], GUDANG = [];

const pemuat = { reorder: loadReorder, forecast: loadForecast, po: loadPo, landed: loadLanded, pim: loadPim, suppliers: loadSuppliers };
function bukaTab(n) {
    document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', String(t.dataset.tab === n)));
    document.querySelector(`[data-tab="${n}"]`)?.scrollIntoView({ inline: 'nearest', block: 'nearest' });
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== n);
    if (!dimuat[n]) { dimuat[n] = true; pemuat[n](); }
    history.replaceState(null, '', '#' + n);
}
document.querySelectorAll('[data-tab]').forEach(t => t.addEventListener('click', () => bukaTab(t.dataset.tab)));
window.addEventListener('hashchange', () => { const n = location.hash.slice(1); if (pemuat[n]) bukaTab(n); });
function muatSemula() { Object.keys(dimuat).forEach(k => delete dimuat[k]); asas().then(() => bukaTab(pemuat[location.hash.slice(1)] ? location.hash.slice(1) : 'reorder')); }

function borang(tajuk, medan) {
    const dlg = App.$('#dlg');
    App.$('#dlgTitle').textContent = tajuk;
    App.$('#dlgBody').innerHTML = medan.map(m => m.html ? m.html : `
        <div class="field"><label class="label" for="f_${m.n}">${E(m.l)}</label>
        ${m.opts ? `<select class="select" id="f_${m.n}">${m.opts.map(o => `<option value="${E(o[0])}" ${String(o[0]) === String(m.v ?? '') ? 'selected' : ''}>${E(o[1])}</option>`).join('')}</select>`
          : m.t === 'textarea' ? `<textarea class="input" id="f_${m.n}" rows="4" placeholder="${E(m.ph ?? '')}">${E(m.v ?? '')}</textarea>`
          : `<input class="input" id="f_${m.n}" type="${m.t || 'text'}" ${m.req ? 'required' : ''} ${m.t === 'number' ? 'step="any"' : ''} value="${E(m.v ?? '')}" placeholder="${E(m.ph ?? '')}">`}
        ${m.hint ? `<div class="field-hint">${E(m.hint)}</div>` : ''}</div>`).join('');
    dlg.showModal();
    return new Promise(res => {
        dlg.onclose = () => {
            if (dlg.returnValue !== 'ok') return res(null);
            const out = {};
            medan.filter(m => m.n).forEach(m => { const v = App.$('#f_' + m.n).value.trim(); if (v !== '') out[m.n] = m.t === 'number' ? Number(v) : v; });
            res(out);
        };
    });
}
async function cuba(fn, ok) {
    try { const r = await fn(); if (ok) App.toast(ok, 'success'); return r; }
    catch (e) { App.toast(e.message, 'error'); return null; }
}
async function asas() {
    const [s, w] = await Promise.all([cuba(() => App.api.get('/api/v1/scm/suppliers?per_page=200')), cuba(() => App.api.get('/api/v1/inventory/warehouses'))]);
    PEMBEKAL = s?.data ?? []; GUDANG = Array.isArray(w) ? w : (w?.data ?? []);
}

// ── Pesanan semula ──
let CADANGAN = [];
async function loadReorder() {
    const r = await cuba(() => App.api.get('/api/v1/scm/planning/reorder'));
    CADANGAN = r?.data ?? [];
    App.$('#rOut').textContent = CADANGAN.filter(x => x.urgency === 'out').length;
    App.$('#rUrgent').textContent = CADANGAN.filter(x => x.urgency === 'urgent').length;
    App.$('#rCount').textContent = CADANGAN.length;
    App.$('#rValue').textContent = RM(CADANGAN.reduce((s, x) => s + x.est_value, 0));
    const U = { out: ['Habis', 'danger'], urgent: ['Segera', 'warning'], soon: ['Tidak lama lagi', 'neutral'] };
    App.$('#rList').innerHTML = CADANGAN.length ? CADANGAN.map((x, i) => `<div class="list-row">
        <label class="text-sm row" style="gap:8px;align-items:flex-start"><input type="checkbox" class="rPilih" data-i="${i}" ${x.supplier_id ? 'checked' : ''} style="margin-top:3px">
            <span><b>${E(x.name)}</b> <span class="mono text-xs text-muted">${E(x.sku)}</span><br><span class="text-xs text-muted">${E(x.reason)}</span></span></label>
        <div class="num">${RM(x.est_value)}</div>
        <div class="meta">${lencana(U, x.urgency)}
            <span class="text-xs">Ada ${N(x.on_hand)} · dalam pesanan ${N(x.on_order)} · paras pesan ${N(x.reorder_point)}${x.days_left !== null ? ' · cukup ~' + x.days_left + ' hari' : ''}</span>
            <span class="text-xs text-muted">${x.supplier_name ? E(x.supplier_name) : '<span style="color:var(--danger,#c0392b)">tiada pembekal</span>'}</span>
            <span class="act"><input class="input qty" type="number" min="1" id="rQty${i}" value="${x.suggested_qty}" aria-label="Kuantiti"> <span class="text-xs">${E(x.uom)}</span>
            <button class="btn btn-sm btn-ghost" onclick="tetapanBarang(${x.item_id})">Tetapan</button></span></div>
    </div>`).join('') : kosong('Semua stok mencukupi. Tetapkan paras pesanan semula dan pembekal pada setiap barang supaya cadangan muncul di sini.');
}
async function tetapanBarang(id) {
    const x = CADANGAN.find(c => c.item_id === id) ?? {};
    const d = await borang('Tetapan pesanan semula', [
        { n: 'preferred_supplier_id', l: 'Pembekal utama', opts: [['', '— pilih —'], ...PEMBEKAL.map(s => [s.id, s.company_name])], v: x.supplier_id },
        { n: 'lead_time_days', l: 'Masa tiba (hari)', t: 'number', v: x.lead_time_days ?? 7 },
        { n: 'reorder_level', l: 'Paras pesanan semula minimum', t: 'number', hint: 'Kosongkan untuk guna ramalan jualan sahaja.' },
        { n: 'safety_stock', l: 'Stok keselamatan', t: 'number' },
        { n: 'reorder_qty', l: 'Kuantiti pesanan tetap', t: 'number', hint: 'Kosong = kira automatik (cukup untuk ~30 hari).' },
        { n: 'min_order_qty', l: 'Kuantiti minimum pembekal (MOQ)', t: 'number' }]);
    if (d && await cuba(() => App.api.put(`/api/v1/scm/planning/items/${id}`, d), 'Tetapan disimpan')) loadReorder();
}
async function ciptaPo(btn) {
    const pilih = [...document.querySelectorAll('.rPilih:checked')].map(c => { const i = +c.dataset.i; return { item_id: CADANGAN[i].item_id, qty: Number(App.$('#rQty' + i).value) }; });
    if (!pilih.length) return App.toast('Tanda sekurang-kurangnya satu barang.', 'error');
    const r = await App.withLoading(btn, () => cuba(() => App.api.post('/api/v1/scm/planning/reorder/draft-pos', { lines: pilih })));
    if (r) { App.toast(`${r.data.length} PO draf dicipta: ${r.data.map(p => p.po_number).join(', ')}`, 'success'); loadReorder(); delete dimuat.po; }
}

// ── Ramalan ──
async function loadForecast() {
    const r = await cuba(() => App.api.get('/api/v1/scm/planning/forecast?days=' + App.$('#fDays').value));
    if (!r) return;
    App.$('#fMethod').textContent = 'Kaedah: ' + r.meta.method + '. Ini anggaran, bukan pesanan.';
    App.$('#fList').innerHTML = r.data.length ? r.data.map(x => `<div class="list-row">
        <div class="text-sm"><b>${E(x.name)}</b> <span class="mono text-xs text-muted">${E(x.sku)}</span></div>
        <div class="num">${N(x.forecast)} ${E(x.uom)}</div>
        <div class="meta"><span class="text-xs">Purata ${N(x.avg_daily)}/hari · 7 hari: ${N(x.forecast_7)}</span>
            <span class="badge badge-${x.trend > 1.1 ? 'success' : x.trend < 0.9 ? 'warning' : 'neutral'}">${x.trend > 1.1 ? 'Naik' : x.trend < 0.9 ? 'Turun' : 'Stabil'} ×${x.trend}</span>
            <span class="text-xs text-muted">Paling laku hari ${HARI[x.busiest_day]}</span>
            <span class="act spark" title="Corak Ahad–Sabtu">${x.weekly.map(w => `<i style="height:${Math.max(3, w / Math.max(...x.weekly) * 22)}px"></i>`).join('')}</span></div>
    </div>`).join('') : kosong('Belum ada jualan dalam 8 minggu lepas. Ramalan muncul selepas jualan POS atau kedai online menolak stok.');
}

// ── Pesanan belian ──
async function loadPo() {
    const r = await cuba(() => App.api.get('/api/v1/scm/purchase-orders?per_page=50'));
    const rows = r?.data ?? [];
    const aktif = rows.filter(p => !['cancelled', 'fully_received'].includes(p.status));
    App.$('#sPo').textContent = aktif.length;
    App.$('#sValue').textContent = RM(aktif.reduce((s, p) => s + Number(p.total_amount ?? 0), 0));
    App.$('#sPending').textContent = rows.filter(p => p.status === 'pending_approval').length;
    App.$('#sDraft').textContent = rows.filter(p => p.status === 'draft').length;
    const BTN = { draft: [['submit', 'Hantar untuk lulus']], pending_approval: [['approve', 'Lulus']], approved: [['send', 'Hantar ke pembekal']], sent: [['receive', 'Terima barang']], partially_received: [['receive', 'Terima baki']] };
    App.$('#poList').innerHTML = rows.length ? rows.map(p => `<div class="list-row">
        <div class="text-sm"><span class="mono">${E(p.po_number)}</span> · ${E(p.supplier?.company_name ?? '—')}</div>
        <div class="num">${RM(p.total_amount)}</div>
        <div class="meta">${lencana(STATUS_PO, p.status)}<span class="text-xs text-muted">${tarikh(p.order_date)} · ${(p.lines ?? []).length} baris</span>
            <span class="act">${(BTN[p.status] ?? []).map(([a, t]) => `<button class="btn btn-sm ${a === 'approve' ? 'btn-primary' : ''}" onclick="tindakPo(${p.id}, '${a}')">${t}</button>`).join('')}
            ${['draft', 'pending_approval', 'approved', 'sent'].includes(p.status) ? `<button class="btn btn-sm btn-ghost" onclick="tindakPo(${p.id}, 'cancel')">Batal</button>` : ''}</span></div>
    </div>`).join('') : kosong('Tiada pesanan belian. Cipta dari tab Pesanan semula.');
}
async function tindakPo(id, a) {
    if (a === 'receive') return terimaPo(id);
    if (a === 'cancel' && !confirm('Batalkan PO ini?')) return;
    if (await cuba(() => App.api.post(`/api/v1/scm/purchase-orders/${id}/${a}`), 'Dikemas kini')) { loadPo(); delete dimuat.reorder; }
}
async function terimaPo(id) {
    const po = await cuba(() => App.api.get(`/api/v1/scm/purchase-orders/${id}`));
    if (!po) return;
    const lines = (po.lines ?? []).filter(l => l.quantity_ordered > l.quantity_received);
    const d = await borang('Terima barang ' + po.po_number, [
        { n: 'warehouse_id', l: 'Masuk ke gudang', opts: GUDANG.map(w => [w.id, w.code + ' ' + w.name]), hint: 'Stok & kos dikemas kini terus.' },
        ...lines.map(l => ({ n: 'l' + l.id, l: `${l.item_name} (baki ${l.quantity_ordered - l.quantity_received})`, t: 'number', v: l.quantity_ordered - l.quantity_received }))]);
    if (!d) return;
    const items = lines.map(l => ({ purchase_order_line_id: l.id, quantity_received: Math.max(0, Math.floor(d['l' + l.id] ?? 0)) }));
    const r = await cuba(() => App.api.post(`/api/v1/scm/purchase-orders/${id}/receive`, { warehouse_id: Number(d.warehouse_id), received_items: items }));
    if (r) { App.toast('Barang diterima' + (r.stock_movement ? ', stok masuk ' + r.stock_movement.number : ''), 'success'); loadPo(); delete dimuat.landed; delete dimuat.reorder; }
}

// ── Kos pendaratan ──
let TERIMAAN = [];
async function loadLanded() {
    const r = await cuba(() => App.api.get('/api/v1/scm/landed-costs'));
    if (!r) return;
    TERIMAAN = r.receipts;
    const JC = { freight: 'Penghantaran', duty: 'Duti', insurance: 'Insurans', clearance: 'Kastam/ejen', other: 'Lain' };
    App.$('#lList').innerHTML = r.data.length ? r.data.map(x => `<div class="list-row">
        <div class="text-sm"><span class="mono">${E(x.number)}</span> · terimaan ${E(x.movement_number)}</div><div class="num">${RM(x.total)}</div>
        <div class="meta">${lencana({ draft: ['Draf', 'neutral'], posted: ['Dipos', 'success'] }, x.status)}
            <span class="text-xs">${x.charges.map(c => JC[c.type] + ' ' + RM(c.amount)).join(' · ')}</span>
            ${x.status === 'posted' ? `<span class="text-xs text-muted">Masuk stok ${RM(x.capitalised)} · kos jualan ${RM(x.expensed)}</span>` : ''}
            <span class="act">${x.status === 'draft' ? `<button class="btn btn-sm btn-primary" onclick="posKos(${x.id})">Pos</button>` : ''}</span></div>
        <div class="meta text-xs text-muted">${x.lines.map(l => `${E(l.item_name)}: ${RM(l.allocated)}`).join(' · ')}</div>
    </div>`).join('') : kosong('Belum ada kos pendaratan.');
}
async function tambahKosPendaratan() {
    if (!TERIMAAN.length) return App.toast('Tiada terimaan stok yang dipos.', 'error');
    const d = await borang('Kos pendaratan', [
        { n: 'stock_movement_id', l: 'Terimaan', opts: TERIMAAN.map(t => [t.id, `${t.number}${t.reference ? ' · ' + t.reference : ''} · ${tarikh(t.posted_at)}`]) },
        { n: 'method', l: 'Agih ikut', opts: [['value', 'Nilai barang'], ['qty', 'Kuantiti'], ['weight', 'Berat']] },
        { n: 'freight', l: 'Penghantaran (RM)', t: 'number' }, { n: 'duty', l: 'Duti import (RM)', t: 'number' },
        { n: 'insurance', l: 'Insurans (RM)', t: 'number' }, { n: 'clearance', l: 'Kastam / ejen (RM)', t: 'number' }, { n: 'notes', l: 'Nota' }]);
    if (!d) return;
    const charges = ['freight', 'duty', 'insurance', 'clearance'].filter(k => d[k] > 0).map(k => ({ type: k, amount: d[k] }));
    if (await cuba(() => App.api.post('/api/v1/scm/landed-costs', { stock_movement_id: Number(d.stock_movement_id), method: d.method, notes: d.notes, charges }), 'Draf disimpan. Semak agihan, kemudian Pos.')) loadLanded();
}
async function posKos(id) {
    if (confirm('Pos kos pendaratan? Nilai stok dan kos jualan akan berubah, dan tidak boleh dibatalkan.') && await cuba(() => App.api.post(`/api/v1/scm/landed-costs/${id}/post`), 'Kos pendaratan dipos')) loadLanded();
}

// ── Maklumat produk ──
let PRODUK = [];
async function loadPim() {
    const q = App.$('#pCari').value.trim();
    const r = await cuba(() => App.api.get('/api/v1/scm/pim?' + new URLSearchParams({ search: q, incomplete: App.$('#pIncomplete').checked ? 1 : 0 })));
    if (!r) return;
    PRODUK = r.data;
    App.$('#pAvg').textContent = r.meta.average_score === null ? '—' : r.meta.average_score + '%';
    App.$('#pList').innerHTML = PRODUK.length ? PRODUK.map(x => `<div class="list-row">
        <div class="text-sm row" style="gap:10px;align-items:center">${x.images?.[0] ? `<img src="${E(x.images[0])}" alt="" width="40" height="40" style="border-radius:6px;object-fit:cover">` : ''}
            <span><b>${E(x.name)}</b> <span class="mono text-xs text-muted">${E(x.sku)}</span>${x.brand ? `<br><span class="text-xs text-muted">${E(x.brand)}</span>` : ''}</span></div>
        <div class="num">${x.score}%</div>
        <div class="meta" style="display:block"><div class="bar"><i style="width:${x.score}%"></i></div>
            <div class="row mt-2" style="gap:8px;flex-wrap:wrap;align-items:center">
                ${x.missing.length ? `<span class="text-xs text-muted">Belum ada: ${E(x.missing.join(', '))}</span>` : '<span class="text-xs">Lengkap</span>'}
                ${x.variants.length ? `<span class="badge badge-neutral">${x.variants.length} varian</span>` : ''}
                <span class="act"><button class="btn btn-sm" onclick="suntingProduk(${x.id})">Sunting</button><button class="btn btn-sm btn-ghost" onclick="janaVarian(${x.id})">Varian</button></span></div></div>
    </div>`).join('') : kosong(q ? 'Tiada padanan.' : 'Tiada produk.');
}
async function suntingProduk(id) {
    const x = PRODUK.find(p => p.id === id);
    const d = await borang('Maklumat produk', [{ n: 'name', l: 'Nama', v: x.name }, { n: 'brand', l: 'Jenama', v: x.brand }, { n: 'category', l: 'Kategori', v: x.category },
        { n: 'barcode', l: 'Kod bar', v: x.barcode }, { n: 'sell_price', l: 'Harga jual (RM)', t: 'number', v: x.sell_price }, { n: 'weight_g', l: 'Berat (gram)', t: 'number', v: x.weight_g },
        { n: 'description', l: 'Keterangan', t: 'textarea', v: x.description }, { n: 'images', l: 'Pautan gambar (satu baris satu)', t: 'textarea', v: (x.images ?? []).join('\n') },
        { n: 'attrs', l: 'Atribut (Nama: nilai, satu baris satu)', t: 'textarea', v: Object.entries(x.attributes ?? {}).map(([k, v]) => k + ': ' + v).join('\n'), ph: 'Warna: Merah' }]);
    if (!d) return;
    d.images = (d.images ?? '').split('\n').map(s => s.trim()).filter(Boolean);
    d.attributes = Object.fromEntries((d.attrs ?? '').split('\n').map(s => s.split(':')).filter(p => p.length > 1).map(([k, ...v]) => [k.trim(), v.join(':').trim()]));
    delete d.attrs;
    if (await cuba(() => App.api.put(`/api/v1/scm/pim/${id}`, d), 'Produk dikemas kini')) loadPim();
}
async function janaVarian(id) {
    const d = await borang('Jana varian', [{ n: 'a1', l: 'Atribut 1', ph: 'Saiz' }, { n: 'v1', l: 'Nilai (asingkan dengan koma)', ph: 'Kecil, Sederhana, Besar' },
        { n: 'a2', l: 'Atribut 2 (pilihan)', ph: 'Warna' }, { n: 'v2', l: 'Nilai', ph: 'Merah, Putih' }]);
    if (!d) return;
    const options = {};
    [['a1', 'v1'], ['a2', 'v2']].forEach(([a, v]) => { if (d[a] && d[v]) options[d[a]] = d[v].split(',').map(s => s.trim()).filter(Boolean); });
    if (!Object.keys(options).length) return App.toast('Isi sekurang-kurangnya satu atribut.', 'error');
    const r = await cuba(() => App.api.post(`/api/v1/scm/pim/${id}/variants`, { options }));
    if (r) { App.toast(r.meta.created ? `${r.meta.created} varian dicipta` : 'Semua varian sudah wujud', 'success'); loadPim(); }
}

// ── Pembekal ──
async function loadSuppliers() {
    await asas();
    App.$('#supList').innerHTML = PEMBEKAL.length ? PEMBEKAL.map(s => `<div class="list-row">
        <div class="text-sm"><b>${E(s.company_name)}</b>${s.ssm_number ? ` <span class="text-xs text-muted">SSM ${E(s.ssm_number)}</span>` : ''}</div>
        <div>${lencana({ active: ['Aktif', 'success'], inactive: ['Tidak aktif', 'neutral'], blacklisted: ['Disenarai hitam', 'danger'] }, s.status)}</div>
        <div class="meta text-xs text-muted">${E([s.contact_person, s.contact_phone, s.contact_email].filter(Boolean).join(' · ') || '—')}</div></div>`).join('') : kosong('Belum ada pembekal.');
}
async function tambahPembekal() {
    const d = await borang('Pembekal baharu', [{ n: 'company_name', l: 'Nama syarikat', req: true }, { n: 'ssm_number', l: 'No. SSM' },
        { n: 'contact_person', l: 'Orang dihubungi' }, { n: 'contact_phone', l: 'Telefon' }, { n: 'contact_email', l: 'Emel', t: 'email' }]);
    if (d && await cuba(() => App.api.post('/api/v1/scm/suppliers', d), 'Pembekal ditambah')) loadSuppliers();
}

(async () => {
    if (!(await App.loadSession())) { location.href = '/login'; return; }
    await asas();
    const n = location.hash.slice(1);
    bukaTab(pemuat[n] ? n : 'reorder');
})();
</script>
@endpush
