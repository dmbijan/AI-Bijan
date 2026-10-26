@extends('layouts.app')

@section('title', 'POS')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">POS</h1>
        <p class="page-desc">Kaunter jualan, syif tunai dan ringkasan harian.</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <select class="input" id="regSel" style="min-width:160px" aria-label="Pilih kaunter"></select>
        <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
    </div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="counter">Kaunter</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="sales">Jualan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="registers">Tetapan Kaunter</button>
</div>

{{-- ============ KAUNTER (skrin jual sebenar) ============ --}}
<section data-panel="counter">
    <div class="pos-wrap">
        {{-- Kiri: cari + grid produk --}}
        <div class="pos-catalog">
            <div class="pos-search">
                <input class="input" id="prodSearch" placeholder="Cari nama / SKU / imbas barkod…" autocomplete="off">
            </div>
            <div class="pos-grid" id="prodGrid">
                <div class="skeleton" style="height:84px"></div>
                <div class="skeleton" style="height:84px"></div>
                <div class="skeleton" style="height:84px"></div>
            </div>
        </div>

        {{-- Kanan: troli --}}
        <aside class="pos-cart" aria-label="Troli jualan">
            <div class="pos-cart-head">
                <h2 class="card-title">Troli</h2>
                <button class="btn btn-sm btn-ghost" onclick="clearCart()" id="btnClear" hidden>Kosongkan</button>
            </div>
            <div class="pos-cart-lines" id="cartLines">
                <div class="empty"><div class="empty-text">Tekan produk untuk tambah ke troli.</div></div>
            </div>
            <div class="pos-cart-foot">
                <div class="pos-total-row"><span>Jumlah item</span><span id="cartQty">0</span></div>
                <div class="pos-total-row pos-total-grand"><span>Jumlah</span><span id="cartTotal">RM 0.00</span></div>
                <button class="btn btn-primary btn-block btn-lg" id="btnPay" onclick="openPay()" disabled>Bayar</button>
            </div>
        </aside>
    </div>
</section>

{{-- ============ JUALAN (sejarah) ============ --}}
<section data-panel="sales" hidden>
    <div class="grid grid-4 mb-5">
        <div class="stat"><div class="stat-label">Jualan Hari Ini</div><div class="stat-value" id="sSales">—</div></div>
        <div class="stat"><div class="stat-label">Transaksi</div><div class="stat-value" id="sCount">—</div></div>
        <div class="stat"><div class="stat-label">Purata Tiket</div><div class="stat-value" id="sAvg">—</div></div>
        <div class="stat"><div class="stat-label">Kaunter Aktif</div><div class="stat-value" id="sRegisters">—</div></div>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>No. Jualan</th><th>Masa</th><th>Kaunter</th><th class="table-num">Jumlah</th><th>Status</th></tr>
            </thead>
            <tbody id="saleTable">
                <tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr>
            </tbody>
        </table>
    </div>
</section>

{{-- ============ TETAPAN KAUNTER ============ --}}
<section data-panel="registers" hidden>
    <div class="card mb-4">
        <div class="card-header"><div><h2 class="card-title">Tambah Kaunter</h2></div></div>
        <div class="grid grid-3" style="gap:12px">
            <div class="field" style="margin:0">
                <label class="label">Nama Kaunter</label>
                <input class="input" id="regName" placeholder="Kaunter 1">
            </div>
            <div class="field" style="margin:0">
                <label class="label">Lokasi</label>
                <input class="input" id="regLocation" placeholder="Cawangan Utama">
            </div>
            <div class="field" style="margin:0;display:flex;align-items:flex-end">
                <button class="btn btn-primary btn-block" onclick="createRegister()">Tambah</button>
            </div>
        </div>
    </div>
    <div class="grid grid-3" id="regGrid">
        <div class="skeleton" style="height:120px"></div>
    </div>
</section>

{{-- ============ OVERLAY BAYAR ============ --}}
<div class="pos-overlay" id="payOverlay" hidden>
    <div class="pos-dialog" role="dialog" aria-modal="true" aria-label="Bayaran">
        <div class="pos-dialog-head">
            <h2 class="card-title">Bayaran</h2>
            <button class="btn btn-sm btn-ghost" onclick="closePay()" aria-label="Tutup">✕</button>
        </div>
        <div class="pos-pay-amount">
            <span class="stat-label">Jumlah perlu dibayar</span>
            <div class="pos-pay-grand" id="payGrand">RM 0.00</div>
        </div>
        <div class="field">
            <label class="label">Kaedah bayaran</label>
            <div class="pos-methods" id="payMethods">
                <button type="button" class="pos-method is-active" data-method="cash">Tunai</button>
                <button type="button" class="pos-method" data-method="card">Kad</button>
                <button type="button" class="pos-method" data-method="ewallet">E-Wallet</button>
                <button type="button" class="pos-method" data-method="credit">Kredit</button>
            </div>
        </div>
        <div class="field" id="tenderField">
            <label class="label">Tunai diterima</label>
            <input class="input" id="payTender" inputmode="decimal" placeholder="0.00">
            <div class="pos-quick" id="quickCash"></div>
        </div>
        <div class="pos-total-row pos-total-grand" id="changeRow" hidden><span>Baki</span><span id="payChange">RM 0.00</span></div>
        <button class="btn btn-primary btn-block btn-lg" id="btnConfirmPay" onclick="confirmPay()">Sahkan &amp; Resit</button>
    </div>
</div>

{{-- ============ RESIT ============ --}}
<div class="pos-overlay" id="rcptOverlay" hidden>
    <div class="pos-dialog" role="dialog" aria-modal="true" aria-label="Resit">
        <div class="pos-receipt" id="rcptBody"></div>
        <div style="display:flex;gap:8px;margin-top:14px">
            <button class="btn btn-block" onclick="printReceipt()">Cetak</button>
            <button class="btn btn-primary btn-block" onclick="closeReceipt()">Jualan Baharu</button>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.pos-wrap{display:grid;grid-template-columns:1fr 360px;gap:16px;align-items:start}
.pos-catalog{min-width:0}
.pos-search{margin-bottom:12px}
.pos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px}
.pos-tile{text-align:left;border:1px solid var(--border,#2a2a2a);background:var(--surface,#161616);border-radius:12px;padding:12px;cursor:pointer;transition:.12s;min-height:84px;display:flex;flex-direction:column;justify-content:space-between}
.pos-tile:hover{border-color:var(--accent,#d08b3f);transform:translateY(-1px)}
.pos-tile:disabled{opacity:.45;cursor:not-allowed}
.pos-tile-name{font-weight:600;font-size:13px;line-height:1.3;margin-bottom:6px}
.pos-tile-sku{font-size:11px;opacity:.6}
.pos-tile-price{font-weight:700;font-size:14px;color:var(--accent,#d08b3f)}
.pos-cart{position:sticky;top:12px;border:1px solid var(--border,#2a2a2a);background:var(--surface,#161616);border-radius:14px;display:flex;flex-direction:column;max-height:calc(100vh - 160px)}
.pos-cart-head{display:flex;justify-content:space-between;align-items:center;padding:14px 14px 8px}
.pos-cart-lines{flex:1;overflow-y:auto;padding:0 14px;min-height:120px}
.pos-line{display:grid;grid-template-columns:1fr auto;gap:4px 10px;padding:10px 0;border-bottom:1px solid var(--border,#242424)}
.pos-line-name{font-weight:600;font-size:13px}
.pos-line-price{font-size:12px;opacity:.7}
.pos-line-total{font-weight:700;font-size:13px;text-align:right}
.pos-qty{display:flex;align-items:center;gap:6px;margin-top:4px}
.pos-qty button{width:26px;height:26px;border-radius:7px;border:1px solid var(--border,#2a2a2a);background:transparent;color:inherit;cursor:pointer;font-size:15px;line-height:1}
.pos-qty button:hover{border-color:var(--accent,#d08b3f)}
.pos-qty span{min-width:22px;text-align:center;font-weight:600}
.pos-line-rm{background:none;border:none;color:var(--danger,#e06c6c);cursor:pointer;font-size:12px;padding:0}
.pos-cart-foot{padding:14px;border-top:1px solid var(--border,#2a2a2a)}
.pos-total-row{display:flex;justify-content:space-between;font-size:13px;margin-bottom:8px;opacity:.85}
.pos-total-grand{font-size:18px;font-weight:800;opacity:1;margin:6px 0 12px}
.pos-overlay{position:fixed;inset:0;background:rgba(0,0,0,.55);display:flex;align-items:center;justify-content:center;z-index:60;padding:16px}
.pos-dialog{background:var(--surface,#161616);border:1px solid var(--border,#2a2a2a);border-radius:16px;padding:18px;width:100%;max-width:420px;max-height:90vh;overflow-y:auto}
.pos-dialog-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:12px}
.pos-pay-amount{text-align:center;margin-bottom:16px}
.pos-pay-grand{font-size:34px;font-weight:800;color:var(--accent,#d08b3f)}
.pos-methods{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
.pos-method{padding:12px;border-radius:10px;border:1px solid var(--border,#2a2a2a);background:transparent;color:inherit;cursor:pointer;font-weight:600}
.pos-method.is-active{border-color:var(--accent,#d08b3f);background:color-mix(in srgb,var(--accent,#d08b3f) 14%,transparent)}
.pos-quick{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.pos-quick button{padding:7px 12px;border-radius:8px;border:1px solid var(--border,#2a2a2a);background:transparent;color:inherit;cursor:pointer;font-size:13px}
.pos-receipt{font-family:ui-monospace,monospace;font-size:13px;white-space:pre-wrap;line-height:1.55}
@media(max-width:820px){
  .pos-wrap{grid-template-columns:1fr}
  .pos-cart{position:static;max-height:none}
}
</style>
@endpush

@push('scripts')
<script>
// ---- state ----
const cart = [];          // {item_id, name, sku, price, qty}
let registers = [];
let payMethod = 'cash';

// ---- tab switch ----
document.querySelectorAll('[data-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', 'false'));
        tab.setAttribute('aria-selected', 'true');
        document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
    });
});

async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await loadRegisters();
    await Promise.all([loadCatalog(), loadSummary(), loadSales()]);
}

// ---- katalog produk ----
let searchTimer;
App.$('#prodSearch').addEventListener('input', e => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadCatalog(e.target.value.trim()), 250);
});
App.$('#prodSearch').addEventListener('keydown', e => {
    // Enter + satu hasil → terus tambah (aliran imbas barkod)
    if (e.key === 'Enter' && window._catalog && window._catalog.length === 1) addToCart(window._catalog[0]);
});

async function loadCatalog(search = '') {
    try {
        const qs = new URLSearchParams({ per_page: '60', is_active: '1' });
        if (search) qs.set('search', search);
        const r = await App.api.get('/api/v1/inventory/catalog?' + qs.toString());
        const rows = r.data?.data ?? r.data ?? [];
        window._catalog = rows.map(it => ({
            item_id: it.id,
            name: it.name,
            sku: it.sku,
            price: Number(it.sell_price ?? it.price ?? it.unit_price ?? 0),
        }));
        renderCatalog();
    } catch (e) {
        App.$('#prodGrid').innerHTML = `<div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div>`;
    }
}

function renderCatalog() {
    const rows = window._catalog ?? [];
    App.$('#prodGrid').innerHTML = rows.length ? rows.map((p, i) => `
        <button class="pos-tile" onclick="addToCart(window._catalog[${i}])">
            <div>
                <div class="pos-tile-name">${App.escapeHtml(p.name)}</div>
                <div class="pos-tile-sku">${App.escapeHtml(p.sku ?? '')}</div>
            </div>
            <div class="pos-tile-price">${App.rupiah(p.price)}</div>
        </button>
    `).join('') : '<div class="empty"><div class="empty-title">Tiada produk</div><div class="empty-text">Tambah produk di Inventori dahulu.</div></div>';
}

// ---- troli ----
function addToCart(p) {
    const line = cart.find(l => l.item_id === p.item_id);
    if (line) line.qty++;
    else cart.push({ ...p, qty: 1 });
    renderCart();
}
function changeQty(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    renderCart();
}
function removeLine(idx) { cart.splice(idx, 1); renderCart(); }
function clearCart() { cart.length = 0; renderCart(); }

function cartTotal() { return cart.reduce((s, l) => s + l.price * l.qty, 0); }
function cartCount() { return cart.reduce((s, l) => s + l.qty, 0); }

function renderCart() {
    const box = App.$('#cartLines');
    if (!cart.length) {
        box.innerHTML = '<div class="empty"><div class="empty-text">Tekan produk untuk tambah ke troli.</div></div>';
    } else {
        box.innerHTML = cart.map((l, i) => `
            <div class="pos-line">
                <div>
                    <div class="pos-line-name">${App.escapeHtml(l.name)}</div>
                    <div class="pos-line-price">${App.rupiah(l.price)} seunit</div>
                    <div class="pos-qty">
                        <button onclick="changeQty(${i},-1)" aria-label="Kurang">−</button>
                        <span>${l.qty}</span>
                        <button onclick="changeQty(${i},1)" aria-label="Tambah">+</button>
                        <button class="pos-line-rm" onclick="removeLine(${i})">Buang</button>
                    </div>
                </div>
                <div class="pos-line-total">${App.rupiah(l.price * l.qty)}</div>
            </div>
        `).join('');
    }
    App.$('#cartQty').textContent = cartCount();
    App.$('#cartTotal').textContent = App.rupiah(cartTotal());
    App.$('#btnPay').disabled = cart.length === 0;
    App.$('#btnClear').hidden = cart.length === 0;
}

// ---- kaunter ----
async function loadRegisters() {
    try {
        const r = await App.api.get('/api/v1/pos/registers');
        registers = r.data?.data ?? r.data ?? [];
    } catch { registers = []; }

    const sel = App.$('#regSel');
    const active = registers.filter(g => g.is_active);
    sel.innerHTML = active.length
        ? active.map(g => `<option value="${g.id}">${App.escapeHtml(g.name)}</option>`).join('')
        : '<option value="">(Tiada kaunter aktif)</option>';
    App.$('#sRegisters') && (App.$('#sRegisters').textContent = active.length);

    // grid tetapan
    const grid = App.$('#regGrid');
    if (grid) grid.innerHTML = registers.length ? registers.map(g => `
        <div class="card card-hover">
            <div class="card-header">
                <div><h3 class="card-title">${App.escapeHtml(g.name)}</h3><p class="card-subtitle">${App.escapeHtml(g.location ?? '')}</p></div>
                <span class="badge badge-${g.is_active ? 'success' : 'neutral'}">${g.is_active ? 'Aktif' : 'Tutup'}</span>
            </div>
            <div class="card-footer"><button class="btn btn-sm" onclick="toggleRegister(${g.id})">${g.is_active ? 'Tutup' : 'Buka'}</button></div>
        </div>
    `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada kaunter. Tambah satu di atas.</div></div></div>';
}

async function createRegister() {
    const name = App.$('#regName').value.trim();
    if (!name) { App.toast('Nama diperlukan', 'error'); return; }
    try {
        await App.api.post('/api/v1/pos/registers', { name, location: App.$('#regLocation').value.trim() || null });
        App.$('#regName').value = ''; App.$('#regLocation').value = '';
        App.toast('Kaunter ditambah', 'success');
        loadRegisters();
    } catch (e) { App.toast(e.message, 'error'); }
}
async function toggleRegister(id) {
    try { await App.api.post(`/api/v1/pos/registers/${id}/toggle`); App.toast('Dikemas kini', 'success'); loadRegisters(); }
    catch (e) { App.toast(e.message, 'error'); }
}

// ---- bayaran ----
function openPay() {
    if (!cart.length) return;
    if (!App.$('#regSel').value) { App.toast('Pilih / buka kaunter dahulu (tab Tetapan Kaunter)', 'error'); return; }
    App.$('#payGrand').textContent = App.rupiah(cartTotal());
    setMethod('cash');
    App.$('#payTender').value = '';
    renderQuickCash();
    updateChange();
    App.$('#payOverlay').hidden = false;
}
function closePay() { App.$('#payOverlay').hidden = true; }

document.querySelectorAll('#payMethods .pos-method').forEach(b =>
    b.addEventListener('click', () => setMethod(b.dataset.method)));
function setMethod(m) {
    payMethod = m;
    document.querySelectorAll('#payMethods .pos-method').forEach(b =>
        b.classList.toggle('is-active', b.dataset.method === m));
    App.$('#tenderField').hidden = m !== 'cash';
    updateChange();
}
function renderQuickCash() {
    const total = cartTotal();
    const notes = [total, Math.ceil(total / 5) * 5, Math.ceil(total / 10) * 10, Math.ceil(total / 50) * 50];
    const uniq = [...new Set(notes)].filter(n => n > 0);
    App.$('#quickCash').innerHTML = uniq.map(n => `<button type="button" onclick="setTender(${n})">${App.rupiah(n)}</button>`).join('');
}
function setTender(n) { App.$('#payTender').value = Number(n).toFixed(2); updateChange(); }
App.$('#payTender').addEventListener('input', updateChange);
function updateChange() {
    const row = App.$('#changeRow');
    if (payMethod !== 'cash') { row.hidden = true; return; }
    const tender = parseFloat(App.$('#payTender').value || '0');
    const change = tender - cartTotal();
    row.hidden = !(tender > 0);
    App.$('#payChange').textContent = App.rupiah(change >= 0 ? change : 0);
}

async function confirmPay() {
    const total = cartTotal();
    if (payMethod === 'cash') {
        const tender = parseFloat(App.$('#payTender').value || '0');
        if (tender < total) { App.toast('Tunai tidak mencukupi', 'error'); return; }
    }
    const btn = App.$('#btnConfirmPay');
    btn.disabled = true; btn.textContent = 'Memproses…';
    try {
        const regId = Number(App.$('#regSel').value);
        // 1) draf
        const draft = await App.api.post('/api/v1/pos/sales/draft', { register_id: regId });
        const saleId = draft.data?.id ?? draft.id;
        // 2) item satu demi satu
        for (const l of cart) {
            await App.api.post(`/api/v1/pos/sales/${saleId}/items`, {
                product_name: l.name, product_sku: l.sku ?? null,
                quantity: l.qty, unit_price: l.price,
            });
        }
        // 3) selesai + bayaran
        const done = await App.api.post(`/api/v1/pos/sales/${saleId}/complete`, {
            payments: [{ method: payMethod, amount: total }],
        });
        showReceipt(done.data ?? done, total);
        closePay();
        clearCart();
        loadSummary(); loadSales();
        App.toast('Jualan selesai', 'success');
    } catch (e) {
        App.toast(e.message || 'Gagal memproses jualan', 'error');
    } finally {
        btn.disabled = false; btn.textContent = 'Sahkan & Resit';
    }
}

// ---- resit ----
let _lastReceipt = '';
function showReceipt(sale, total) {
    const no = sale.sale_number ?? sale.number ?? ('#' + (sale.id ?? ''));
    const regName = App.$('#regSel').selectedOptions[0]?.textContent ?? '';
    const lines = cart.map(l => `${l.qty} x ${l.name}\n   ${App.rupiah(l.price)}  =  ${App.rupiah(l.price * l.qty)}`).join('\n');
    const tender = parseFloat(App.$('#payTender').value || '0');
    _lastReceipt =
`        AI BIJAN POS
${regName}
No: ${no}
${new Date().toLocaleString('ms-MY')}
------------------------------
${lines}
------------------------------
JUMLAH        ${App.rupiah(total)}
Bayaran       ${payMethod.toUpperCase()}` +
(payMethod === 'cash' ? `\nTunai         ${App.rupiah(tender)}\nBaki          ${App.rupiah(Math.max(0, tender - total))}` : '') +
`\n------------------------------
   Terima kasih! Datang lagi.`;
    App.$('#rcptBody').textContent = _lastReceipt;
    App.$('#rcptOverlay').hidden = false;
}
function closeReceipt() { App.$('#rcptOverlay').hidden = true; }
function printReceipt() {
    const w = window.open('', '_blank', 'width=320,height=600');
    w.document.write('<pre style="font:13px ui-monospace,monospace;white-space:pre-wrap">' +
        _lastReceipt.replace(/</g, '&lt;') + '</pre>');
    w.document.close(); w.focus(); w.print();
}

// ---- ringkasan + sejarah ----
async function loadSummary() {
    try {
        const r = await App.api.get('/api/v1/pos/sales/summary');
        const d = r.data ?? {};
        App.$('#sSales').textContent = App.rupiah(d.total_amount ?? 0);
        App.$('#sCount').textContent = App.number(d.count ?? 0);
        App.$('#sAvg').textContent = App.rupiah(d.average ?? 0);
    } catch { App.$('#sSales').textContent = '—'; }
}
async function loadSales() {
    try {
        const r = await App.api.get('/api/v1/pos/sales?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#saleTable').innerHTML = rows.length ? rows.map(s => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(s.sale_number ?? s.number ?? '—')}</td>
                <td class="text-xs">${App.date(s.created_at)}</td>
                <td class="text-xs">${App.escapeHtml(s.register?.name ?? '—')}</td>
                <td class="table-num">${App.rupiah(s.total ?? s.grand_total ?? 0)}</td>
                <td><span class="badge badge-${s.status === 'completed' ? 'success' : s.status === 'cancelled' ? 'danger' : 'warning'}">${App.escapeHtml(s.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada jualan</div><div class="empty-text">Jualan akan muncul di sini.</div></div></td></tr>';
    } catch (e) {
        App.$('#saleTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

loadAll();
</script>
@endpush
