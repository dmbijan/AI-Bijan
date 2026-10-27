@extends('layouts.app')

@section('title', 'POS')

@section('content')
<style>
    .pos-kassa { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(300px, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 1024px) { .pos-kassa { grid-template-columns: 1fr; } }
    .pos-products { display: grid; grid-template-columns: repeat(auto-fill, minmax(148px, 1fr)); gap: 10px; }
    .pos-prod { text-align: left; cursor: pointer; display: flex; flex-direction: column; gap: 2px; }
    .pos-prod-name { font-weight: 600; font-size: 13px; }
    .pos-prod-price { color: var(--brand); font-weight: 700; }
    .pos-cart-line { display: flex; justify-content: space-between; padding: 3px 0; font-size: 13px; }
    .pos-cart-line.grand { font-size: 18px; font-weight: 700; border-top: 1px solid var(--border); margin-top: 8px; padding-top: 10px; }
    .pos-qty { display: inline-flex; align-items: center; gap: 6px; }
    .pos-qty button { width: 26px; height: 26px; padding: 0; }
    .pos-kds { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 12px; }
    .pos-ticket { border-left: 4px solid var(--warning-500); }
    .pos-ticket[data-status="ready"] { border-left-color: var(--success-500); }
    .pos-ticket[data-status="served"] { border-left-color: var(--text-subtle); opacity: .72; }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">POS</h1>
        <p class="page-desc">Kasa pantas, kaunter, syif tunai, paparan dapur dan kelulusan diskaun.</p>
    </div>
    <div class="row">
        <select class="select" id="registerPicker" aria-label="Pilih daftar kasar" style="width:auto;min-width:180px" onchange="onRegisterChange()"></select>
        <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
    </div>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Hasil Hari Ini</div><div class="stat-value" id="sRevenue">—</div><div class="stat-delta" id="sRevenueSub">—</div></div>
    <div class="stat"><div class="stat-label">Transaksi</div><div class="stat-value" id="sCount">—</div><div class="stat-delta" id="sAvgSub">—</div></div>
    <div class="stat"><div class="stat-label">Diskaun</div><div class="stat-value" id="sDiscount">—</div><div class="stat-delta" id="sTaxSub">—</div></div>
    <div class="stat"><div class="stat-label">Syif Terbuka</div><div class="stat-value" id="sShift">—</div><div class="stat-delta" id="sShiftSub">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="kassa">Kassa</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="sales">Jualan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="shifts">Syif Tunai</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="registers">Kaunter</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="kitchen">Dapur</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="discounts">Diskaun</button>
</div>

<section data-panel="kassa">
    <div class="pos-kassa">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Katalog Produk</h2>
                    <p class="card-subtitle">Klik produk untuk masuk ke kasa</p>
                </div>
                <input class="input" id="prodSearch" type="search" placeholder="Cari nama / SKU / barkod..." aria-label="Cari produk" style="max-width:260px" oninput="debouncedProducts()">
            </div>
            <div class="pos-products" id="prodGrid">
                <div class="skeleton" style="height:70px"></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Kasa</h2>
                    <p class="card-subtitle" id="cartMeta">—</p>
                </div>
                <button class="btn btn-sm" onclick="clearCart()">Kosongkan</button>
            </div>

            <div class="table-wrap" style="max-height:300px">
                <table class="table">
                    <thead>
                        <tr><th>Item</th><th>Kuantiti</th><th class="table-num">Harga</th><th class="table-num">Jumlah</th><th></th></tr>
                    </thead>
                    <tbody id="cartBody">
                        <tr><td colspan="5"><div class="empty"><div class="empty-text">Kasa kosong. Pilih produk.</div></div></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <div class="pos-cart-line"><span class="text-muted">Subtotal</span><span id="tSubtotal">—</span></div>
                <div class="pos-cart-line"><span class="text-muted">Diskaun</span><span id="tDiscount">—</span></div>
                <div class="pos-cart-line"><span class="text-muted">Caj perkhidmatan</span><span id="tService">—</span></div>
                <div class="pos-cart-line grand"><span>Jumlah</span><span id="tTotal">—</span></div>
            </div>

            <div class="stack mt-4">
                <div class="field" style="margin:0">
                    <label class="label" for="saleDiscount">Diskaun keseluruhan (%)</label>
                    <input class="input" id="saleDiscount" type="number" min="0" max="100" step="0.5" value="0" oninput="recalcCart()">
                </div>
                <input class="input" id="saleNote" placeholder="Nota (pilihan)" aria-label="Nota jualan (pilihan)">
                <div class="row">
                    <button class="btn btn-block" id="draftBtn" onclick="saveDraft()">Simpan Draf</button>
                    <button class="btn btn-primary btn-block" id="payBtn" onclick="openPayDialog()">Bayar</button>
                </div>
                <p class="text-xs text-muted" id="shiftHint">—</p>
            </div>
        </div>
    </div>
</section>

<div class="card mb-4">
    <h2 class="card-title">Pencetak resit ESC/POS USB</h2>
    <p class="text-sm">Chrome/Edge desktop, HTTPS/localhost. Hanya pencetak USB kelas 7 dengan protokol ESC/POS. Sahkan model sebelum sambung; bukan semua pencetak USB serasi.</p>
    <div class="row">
        <button type="button" class="btn btn-sm" id="printerConnect">Sambung pencetak</button>
        <button type="button" class="btn btn-sm" id="printerDisconnect">Putus sambungan</button>
        <select class="select" id="receiptWidth" aria-label="Lebar resit"><option value="32">58 mm / 32 aksara</option><option value="48">80 mm / 48 aksara</option></select>
    </div>
    <p id="printerStatus" role="status" aria-live="polite">Pencetak belum disambung.</p>
</div>

<section data-panel="sales" hidden>
    <div class="card mb-4">
        <div class="row-between">
            <div class="row">
                <select class="select" id="filterStatus" aria-label="Tapis jualan mengikut status" style="width:auto" onchange="loadSales()">
                    <option value="">Semua status</option>
                    <option value="draft">Draf</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Batal</option>
                    <option value="partially_refunded">Dipulangkan sebahagian</option>
                </select>
                <select class="select" id="filterRegister" aria-label="Tapis jualan mengikut daftar kasar" style="width:auto" onchange="loadSales()"></select>
                <input class="input" id="filterSearch" type="search" placeholder="Cari no. jualan / pelanggan..." aria-label="Cari jualan" style="max-width:240px" oninput="debouncedSales()">
            </div>
            <button class="btn btn-sm" onclick="loadSales()">Segar Semula</button>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Jualan</th><th>Masa</th><th>Kaunter</th><th>Juruwang</th>
                    <th class="table-num">Jumlah</th><th>Bayaran</th><th>Status</th><th>Tindakan</th>
                </tr>
            </thead>
            <tbody id="saleTable">
                <tr><td colspan="8"><div class="empty"><div class="empty-text">Memuat...</div></div></td></tr>
            </tbody>
        </table>
    </div>
</section>

<section data-panel="shifts" hidden>
    <div class="grid grid-4 mb-5">
        <div class="stat"><div class="stat-label">Jumlah Syif</div><div class="stat-value" id="shTotal">—</div></div>
        <div class="stat"><div class="stat-label">Terbuka</div><div class="stat-value" id="shOpen">—</div></div>
        <div class="stat"><div class="stat-label">Ditutup</div><div class="stat-value" id="shClosed">—</div></div>
        <div class="stat"><div class="stat-label">Beza Tunai</div><div class="stat-value" id="shDiff">—</div></div>
    </div>

    <div class="grid grid-2 mb-5">
        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Buka Syif</h2><p class="card-subtitle">Kaunter mesti tiada syif terbuka</p></div>
            </div>
            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0">
                    <label class="label" for="openShiftRegister">Kaunter</label>
                    <select class="select" id="openShiftRegister"></select>
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="openShiftCash">Tunai Pembukaan</label>
                    <input class="input" id="openShiftCash" type="number" min="0" step="0.01" value="200">
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-primary" id="openShiftBtn" onclick="openShift()">Buka Syif</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Pergerakan Tunai</h2><p class="card-subtitle">Drop / pickup / perbelanjaan laci</p></div>
            </div>
            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0">
                    <label class="label" for="cmShift">Syif Terbuka</label>
                    <select class="select" id="cmShift"></select>
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="cmType">Jenis</label>
                    <select class="select" id="cmType">
                        <option value="drop">Drop (masuk laci)</option>
                        <option value="pickup">Pickup</option>
                        <option value="expense">Perbelanjaan</option>
                        <option value="adjustment">Pelarasan</option>
                    </select>
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="cmAmount">Jumlah</label>
                    <input class="input" id="cmAmount" type="number" min="0.01" step="0.01">
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="cmReason">Sebab</label>
                    <input class="input" id="cmReason" placeholder="Beli ais, dsb.">
                </div>
            </div>
            <div class="card-footer">
                <button class="btn" id="cmBtn" onclick="recordCashMovement()">Rekod Pergerakan</button>
            </div>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Syif</th><th>Tarikh</th><th>Kaunter</th><th>Juruwang</th><th class="table-num">Tunai Awal</th><th class="table-num">Dijangka</th><th class="table-num">Sebenar</th><th class="table-num">Beza</th><th>Status</th><th>Tindakan</th></tr>
            </thead>
            <tbody id="shiftTable">
                <tr><td colspan="10"><div class="empty"><div class="empty-text">Memuat...</div></div></td></tr>
            </tbody>
        </table>
    </div>
</section>


<section data-panel="registers" hidden>
    <div class="card mb-4">
        <div class="card-header">
            <div><h2 class="card-title">Tambah Kaunter</h2><p class="card-subtitle">Kaunter = titik jualan (POS terminal)</p></div>
        </div>
        <div class="grid grid-3" style="gap:12px">
            <div class="field" style="margin:0">
                <label class="label" for="regName">Nama Kaunter</label>
                <input class="input" id="regName" placeholder="Kaunter 1">
            </div>
            <div class="field" style="margin:0">
                <label class="label" for="regLocation">Lokasi</label>
                <input class="input" id="regLocation" placeholder="Cawangan Utama">
            </div>
            <div class="field" style="margin:0;display:flex;align-items:flex-end">
                <button class="btn btn-primary btn-block" id="regAddBtn" onclick="createRegister()">Tambah</button>
            </div>
        </div>
    </div>
    <div class="grid grid-3" id="regGrid">
        <div class="skeleton" style="height:120px"></div>
    </div>
</section>

<section data-panel="kitchen" hidden>
    <div class="card mb-4">
        <div class="row-between">
            <div>
                <h2 class="card-title">Paparan Dapur (KDS)</h2>
                <p class="card-subtitle" id="kdsMeta">Tiket aktif, paling lama dahulu</p>
            </div>
            <button class="btn btn-sm" onclick="loadKitchen()">Segar Semula</button>
        </div>
    </div>
    <div class="pos-kds" id="kdsGrid">
        <div class="skeleton" style="height:160px"></div>
    </div>
</section>

<section data-panel="discounts" hidden>
    <div class="grid grid-2 mb-5">
        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Mohon Kelulusan Diskaun</h2><p class="card-subtitle" id="discLimit">—</p></div>
            </div>
            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0">
                    <label class="label" for="discPercent">Diskaun (%)</label>
                    <input class="input" id="discPercent" type="number" min="0.01" max="100" step="0.5">
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="discSaleId">ID Jualan (pilihan)</label>
                    <input class="input" id="discSaleId" type="number" min="1">
                </div>
            </div>
            <div class="field mt-3">
                <label class="label" for="discReason">Sebab</label>
                <input class="input" id="discReason" placeholder="Pelanggan tetap, dsb.">
            </div>
            <div class="card-footer">
                <button class="btn btn-primary" id="discBtn" onclick="requestDiscount()">Hantar Permohonan</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Menunggu Kelulusan</h2><p class="card-subtitle">Sahkan atau tolak permohonan juruwang</p></div>
                <button class="btn btn-sm" onclick="loadDiscounts()">Segar</button>
            </div>
            <div id="discList">
                <div class="skeleton" style="height:60px"></div>
            </div>
        </div>
    </div>
</section>


<div class="dialog-backdrop" id="payDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="payDialogTitle">
        <div class="dialog-header">
            <h2 class="card-title" id="payDialogTitle">Bayar Jualan</h2>
            <button class="btn btn-ghost btn-sm" onclick="closePayDialog()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <div class="pos-cart-line grand mb-4"><span>Jumlah Perlu Bayar</span><span id="payDue">—</span></div>

            <div class="field">
                <label class="label" for="payMethod">Kaedah Bayaran</label>
                <select class="select" id="payMethod" onchange="onPayMethodChange()">
                    <option value="cash">Tunai</option>
                    <option value="card">Kad</option>
                    <option value="ewallet">E-Wallet</option>
                    <option value="tng">TNG</option>
                    <option value="credit">Kredit</option>
                    <option value="voucher">Voucher</option>
                </select>
            </div>

            <div class="field" id="cashReceivedField">
                <label class="label" for="payCashReceived">Tunai Diterima</label>
                <input class="input" id="payCashReceived" type="number" min="0" step="0.01" oninput="updateChangePreview()">
            </div>

            <div class="field">
                <label class="label" for="payReference">Rujukan (pilihan)</label>
                <input class="input" id="payReference" placeholder="No. kad / e-wallet ref">
            </div>

            <label class="checkbox mb-4">
                <input type="checkbox" id="paySplit" onchange="onSplitToggle()"> Bayaran berpisah (2 kaedah)
            </label>

            <div id="splitBlock" hidden>
                <div class="grid grid-2" style="gap:12px">
                    <div class="field" style="margin:0">
                        <label class="label" for="splitMethod2">Kaedah Ke-2</label>
                        <select class="select" id="splitMethod2">
                            <option value="card">Kad</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="tng">TNG</option>
                            <option value="voucher">Voucher</option>
                        </select>
                    </div>
                    <div class="field" style="margin:0">
                        <label class="label" for="splitAmount2">Jumlah Ke-2</label>
                        <input class="input" id="splitAmount2" type="number" min="0.01" step="0.01" oninput="updateChangePreview()">
                    </div>
                </div>
            </div>

            <div class="alert alert-info mt-3"><span>Baki dipulangkan:</span> <strong id="payChangePreview">—</strong></div>
            <div id="payError" class="alert alert-danger" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="closePayDialog()">Batal</button>
            <button class="btn btn-primary" id="payConfirmBtn" onclick="confirmPay()">Sahkan Bayaran</button>
        </div>
    </div>
</div>

<div class="dialog-backdrop" id="regDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="regDialogTitle">
        <div class="dialog-header">
            <h2 class="card-title" id="regDialogTitle">Edit Kaunter</h2>
            <button class="btn btn-ghost btn-sm" onclick="closeRegDialog()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <input type="hidden" id="editRegId">
            <div class="field">
                <label class="label" for="editRegName">Nama</label>
                <input class="input" id="editRegName">
            </div>
            <div class="field">
                <label class="label" for="editRegLocation">Lokasi</label>
                <input class="input" id="editRegLocation">
            </div>
            <div id="regError" class="alert alert-danger" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="closeRegDialog()">Batal</button>
            <button class="btn btn-primary" id="regSaveBtn" onclick="saveRegister()">Simpan</button>
        </div>
    </div>
</div>


<div class="dialog-backdrop" id="closeShiftDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="closeShiftTitle">
        <div class="dialog-header">
            <h2 class="card-title" id="closeShiftTitle">Tutup Syif</h2>
            <button class="btn btn-ghost btn-sm" onclick="hideCloseShift()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <input type="hidden" id="closeShiftId">
            <div id="closeShiftSummary" class="stack mb-4"><div class="skeleton" style="height:40px"></div></div>
            <div class="field">
                <label class="label" for="closeShiftCash">Tunai Sebenar Dikira</label>
                <input class="input" id="closeShiftCash" type="number" min="0" step="0.01">
            </div>
            <div class="field">
                <label class="label" for="closeShiftNotes">Nota</label>
                <input class="input" id="closeShiftNotes" placeholder="Pilihan">
            </div>
            <div id="closeShiftError" class="alert alert-danger" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="hideCloseShift()">Batal</button>
            <button class="btn btn-primary" id="closeShiftBtn" onclick="confirmCloseShift()">Tutup Syif</button>
        </div>
    </div>
</div>

<div class="dialog-backdrop" id="refundDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="refundTitle">
        <div class="dialog-header">
            <h2 class="card-title" id="refundTitle">Pulangan Jualan</h2>
            <button class="btn btn-ghost btn-sm" onclick="hideRefund()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <input type="hidden" id="refundSaleId">
            <div class="field">
                <label class="label" for="refundAmount">Jumlah Pulangan</label>
                <input class="input" id="refundAmount" type="number" min="0" step="0.01">
            </div>
            <div class="field">
                <label class="label" for="refundReason">Sebab</label>
                <input class="input" id="refundReason" placeholder="Barang rosak, dsb.">
            </div>
            <div id="refundError" class="alert alert-danger" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="hideRefund()">Batal</button>
            <button class="btn btn-danger" id="refundBtn" onclick="confirmRefund()">Proses Pulangan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const POS = {
    registers: [], products: [], cart: [], registerId: null, shift: null,
    saleDiscount: 0, payTarget: null, discountLimits: null,
};

function rows(r) {
    if (Array.isArray(r)) return r;
    if (r && Array.isArray(r.data)) return r.data;
    if (r && r.data && Array.isArray(r.data.data)) return r.data.data;
    return [];
}

document.querySelectorAll('[data-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', 'false'));
        tab.setAttribute('aria-selected', 'true');
        document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
        const name = tab.dataset.tab;
        if (name === 'sales') loadSales();
        if (name === 'shifts') { loadShiftSummary(); loadShifts(); }
        if (name === 'registers') renderRegisters();
        if (name === 'kitchen') loadKitchen();
        if (name === 'discounts') loadDiscounts();
    });
});

async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await loadRegisters();
    await Promise.all([loadSummary(), loadProducts(), loadShiftSummary(), loadSales(), loadLimits()]);
    await loadCurrentShift();
}

async function loadSummary() {
    try {
        const d = await App.api.get('/api/v1/pos/sales/summary');
        App.$('#sRevenue').textContent = App.rupiah(d.total_revenue ?? 0);
        App.$('#sRevenueSub').textContent = App.number(d.total_sales ?? 0) + ' jualan selesai';
        App.$('#sCount').textContent = App.number(d.total_sales ?? 0);
        App.$('#sAvgSub').textContent = 'Purata ' + App.rupiah(d.avg_transaction ?? 0);
        App.$('#sDiscount').textContent = App.rupiah(d.total_discounts ?? 0);
        App.$('#sTaxSub').textContent = 'Cukai ' + App.rupiah(d.total_tax ?? 0);
    } catch (e) { App.toast(e.message, 'error'); }
}

async function loadShiftSummary() {
    try {
        const d = await App.api.get('/api/v1/pos/shifts/summary');
        App.$('#sShift').textContent = App.number(d.open_shifts ?? 0);
        App.$('#sShiftSub').textContent = App.number(d.total_shifts ?? 0) + ' jumlah syif';
        App.$('#shTotal').textContent = App.number(d.total_shifts ?? 0);
        App.$('#shOpen').textContent = App.number(d.open_shifts ?? 0);
        App.$('#shClosed').textContent = App.number(d.closed_shifts ?? 0);
        App.$('#shDiff').textContent = App.rupiah(d.total_discrepancy ?? 0);
    } catch { /* abaikan */ }
}

// ---------- Kaunter ----------
async function loadRegisters() {
    try {
        const r = await App.api.get('/api/v1/pos/registers');
        POS.registers = rows(r);
        fillRegisterSelects();
        renderRegisters();
    } catch (e) {
        App.toast('Gagal muat kaunter: ' + e.message, 'error');
    }
}

function fillRegisterSelects() {
    const opts = POS.registers.map(g =>
        `<option value="${g.id}">${App.escapeHtml(g.name)}${g.location ? ' — ' + App.escapeHtml(g.location) : ''}</option>`
    ).join('');
    App.$('#registerPicker').innerHTML = '<option value="">Pilih kaunter…</option>' + opts;
    App.$('#filterRegister').innerHTML = '<option value="">Semua kaunter</option>' + opts;
    App.$('#openShiftRegister').innerHTML = opts || '<option value="">Tiada kaunter</option>';
    if (POS.registerId) App.$('#registerPicker').value = String(POS.registerId);
    else if (POS.registers.length) { POS.registerId = POS.registers[0].id; App.$('#registerPicker').value = String(POS.registerId); }
}

function onRegisterChange() {
    const v = App.$('#registerPicker').value;
    POS.registerId = v ? Number(v) : null;
    loadCurrentShift();
}

async function loadCurrentShift() {
    if (!POS.registerId) { POS.shift = null; updateShiftHint(); return; }
    try {
        const s = await App.api.get(`/api/v1/pos/shifts/open/${POS.registerId}`);
        POS.shift = s && s.id ? s : null;
    } catch { POS.shift = null; }
    updateShiftHint();
}

function updateShiftHint() {
    const el = App.$('#shiftHint');
    if (!POS.registerId) { el.textContent = 'Pilih kaunter untuk mula jualan.'; return; }
    if (POS.shift) el.textContent = 'Syif terbuka #' + POS.shift.id + ' · tunai awal ' + App.rupiah(POS.shift.opening_cash);
    else el.textContent = 'Tiada syif terbuka — jualan tetap dibenarkan, syif dijejak jika dibuka.';
}

function renderRegisters() {
    const grid = App.$('#regGrid');
    if (!grid) return;
    grid.innerHTML = POS.registers.length ? POS.registers.map(g => `
        <div class="card card-hover">
            <div class="card-header">
                <div>
                    <h3 class="card-title">${App.escapeHtml(g.name)}</h3>
                    <p class="card-subtitle">${App.escapeHtml(g.location ?? '')}${g.code ? ' · ' + App.escapeHtml(g.code) : ''}</p>
                </div>
                <span class="badge badge-${g.is_active ? 'success' : 'neutral'}">${g.is_active ? 'Aktif' : 'Tutup'}</span>
            </div>
            <div class="card-footer">
                <button class="btn btn-sm" onclick="toggleRegister(${g.id})">${g.is_active ? 'Tutup' : 'Buka'}</button>
                <button class="btn btn-sm" onclick="openRegDialog(${g.id})">Edit</button>
                <button class="btn btn-sm btn-danger" onclick="deleteRegister(${g.id})">Hapus</button>
            </div>
        </div>
    `).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada kaunter</div><div class="empty-text">Tambah kaunter pertama anda.</div></div></div>';
}

// ---------- Katalog produk ----------
let prodTimer = null;
function debouncedProducts() { clearTimeout(prodTimer); prodTimer = setTimeout(loadProducts, 250); }

async function loadProducts() {
    const q = App.$('#prodSearch') ? App.$('#prodSearch').value.trim() : '';
    try {
        const r = await App.api.get('/api/v1/inventory/items?per_page=60' + (q ? `&search=${encodeURIComponent(q)}` : ''));
        POS.products = rows(r);
        renderProducts();
    } catch (e) {
        App.$('#prodGrid').innerHTML = `<div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div>`;
    }
}

function productPrice(p) { return Number(p.sell_price ?? p.price ?? 0) || 0; }

function renderProducts() {
    const grid = App.$('#prodGrid');
    grid.innerHTML = POS.products.length ? POS.products.map(p => `
        <button type="button" class="card card-hover pos-prod" onclick="addToCart(${p.id})">
            <span class="pos-prod-name">${App.escapeHtml(p.name)}</span>
            <span class="text-xs text-muted mono">${App.escapeHtml(p.sku ?? '')}</span>
            <span class="pos-prod-price">${App.rupiah(productPrice(p))}</span>
        </button>
    `).join('') : '<div class="empty"><div class="empty-title">Tiada produk</div><div class="empty-text">Tambah item dalam modul Inventori.</div></div>';
}

// ---------- Kasa ----------
function addToCart(productId) {
    const p = POS.products.find(x => x.id === productId);
    if (!p) return;
    const line = POS.cart.find(l => l.product_id === productId);
    if (line) line.quantity += 1;
    else POS.cart.push({
        product_id: p.id,
        product_name: p.name,
        product_sku: p.sku ?? null,
        unit_price: productPrice(p),
        quantity: 1,
    });
    recalcCart();
}

function changeQty(idx, delta) {
    const line = POS.cart[idx];
    if (!line) return;
    line.quantity += delta;
    if (line.quantity <= 0) POS.cart.splice(idx, 1);
    recalcCart();
}

function setQty(idx, val) {
    const line = POS.cart[idx];
    if (!line) return;
    line.quantity = Math.max(1, Number(val) || 1);
    recalcCart();
}

function removeLine(idx) { POS.cart.splice(idx, 1); recalcCart(); }
function clearCart() { POS.cart = []; recalcCart(); }

function cartTotals() {
    const subtotal = POS.cart.reduce((s, l) => s + l.unit_price * l.quantity, 0);
    const disc = subtotal * (POS.saleDiscount / 100);
    return { subtotal, disc, total: subtotal - disc };
}

function recalcCart() {
    if (!App.$('#saleDiscount')) return;
    POS.saleDiscount = Math.min(100, Math.max(0, Number(App.$('#saleDiscount').value) || 0));
    const body = App.$('#cartBody');
    body.innerHTML = POS.cart.length ? POS.cart.map((l, i) => `
        <tr>
            <td class="text-sm">${App.escapeHtml(l.product_name)}</td>
            <td>
                <span class="pos-qty">
                    <button class="btn btn-sm" aria-label="Kurang kuantiti ${App.escapeHtml(l.product_name)}" onclick="changeQty(${i},-1)">−</button>
                    <input class="input" style="width:56px;text-align:center" type="number" min="1" value="${l.quantity}" aria-label="Kuantiti ${App.escapeHtml(l.product_name)}" onchange="setQty(${i}, this.value)">
                    <button class="btn btn-sm" aria-label="Tambah kuantiti ${App.escapeHtml(l.product_name)}" onclick="changeQty(${i},1)">+</button>
                </span>
            </td>
            <td class="table-num">${App.rupiah(l.unit_price)}</td>
            <td class="table-num">${App.rupiah(l.unit_price * l.quantity)}</td>
            <td><button class="btn btn-sm btn-danger" aria-label="Buang ${App.escapeHtml(l.product_name)} daripada troli" onclick="removeLine(${i})">✕</button></td>
        </tr>
    `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Kasa kosong. Pilih produk.</div></div></td></tr>';

    const t = cartTotals();
    App.$('#tSubtotal').textContent = App.rupiah(t.subtotal);
    App.$('#tDiscount').textContent = '− ' + App.rupiah(t.disc);
    App.$('#tTotal').textContent = App.rupiah(t.total);
    App.$('#cartMeta').textContent = POS.cart.length + ' item · ' + POS.cart.reduce((s, l) => s + l.quantity, 0) + ' unit';
}

// ---------- Commit draf ----------
async function commitCart() {
    if (!POS.registerId) { throw new Error('Pilih kaunter dahulu.'); }
    const sale = await App.api.post('/api/v1/pos/sales/draft', {
        register_id: POS.registerId,
        notes: App.$('#saleNote').value.trim() || null,
    });
    for (const l of POS.cart) {
        await App.api.post(`/api/v1/pos/sales/${sale.id}/items`, {
            product_id: l.product_id,
            product_name: l.product_name,
            product_sku: l.product_sku,
            quantity: l.quantity,
            unit_price: l.unit_price,
            discount_percentage: POS.saleDiscount || 0,
        });
    }
    return App.api.get(`/api/v1/pos/sales/${sale.id}`);
}

async function saveDraft() {
    if (!POS.cart.length) { App.toast('Kasa kosong.', 'error'); return; }
    if (!POS.registerId) { App.toast('Pilih kaunter dahulu.', 'error'); return; }
    await App.withLoading(App.$('#draftBtn'), async () => {
        try {
            const sale = await commitCart();
            App.toast('Draf disimpan: ' + (sale.sale_no ?? ''), 'success');
            clearCart();
            loadSales(); loadSummary();
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

// ---------- Bayar ----------
function round2(n) { return Math.round((Number(n) || 0) * 100) / 100; }

function openPayDialog() {
    if (!POS.cart.length) { App.toast('Kasa kosong.', 'error'); return; }
    if (!POS.registerId) { App.toast('Pilih kaunter dahulu.', 'error'); return; }
    POS.payTarget = { mode: 'cart' };
    POS.payDue = cartTotals().total;
    preparePayDialog();
}

function openPayForSale(id, total) {
    POS.payTarget = { mode: 'sale', id: id };
    POS.payDue = Number(total) || 0;
    preparePayDialog();
}

function preparePayDialog() {
    App.$('#payDue').textContent = App.rupiah(POS.payDue);
    App.$('#payError').hidden = true;
    App.$('#paySplit').checked = false;
    App.$('#splitBlock').hidden = true;
    App.$('#payMethod').disabled = false;
    App.$('#payMethod').value = 'cash';
    App.$('#payCashReceived').value = '';
    App.$('#payReference').value = '';
    App.$('#splitAmount2').value = '';
    onPayMethodChange();
    App.$('#payDialog').hidden = false;
}

function closePayDialog() { App.$('#payDialog').hidden = true; POS.payTarget = null; }
function showPayError(msg) { const el = App.$('#payError'); el.textContent = msg; el.hidden = false; }

function onPayMethodChange() {
    App.$('#cashReceivedField').hidden = App.$('#payMethod').value !== 'cash';
    updateChangePreview();
}

function onSplitToggle() {
    const split = App.$('#paySplit').checked;
    App.$('#splitBlock').hidden = !split;
    if (split) { App.$('#payMethod').value = 'cash'; App.$('#payMethod').disabled = true; }
    else App.$('#payMethod').disabled = false;
    onPayMethodChange();
}

function updateChangePreview() {
    const due = POS.payDue || 0;
    let tendered;
    if (App.$('#paySplit').checked) {
        tendered = (Number(App.$('#payCashReceived').value) || 0) + (Number(App.$('#splitAmount2').value) || 0);
    } else if (App.$('#payMethod').value === 'cash') {
        tendered = Number(App.$('#payCashReceived').value) || 0;
    } else {
        tendered = due;
    }
    App.$('#payChangePreview').textContent = App.rupiah(Math.max(0, round2(tendered - due)));
}

function buildTenders(due) {
    const ref = App.$('#payReference').value.trim() || null;
    if (!App.$('#paySplit').checked) {
        const m = App.$('#payMethod').value;
        if (m === 'cash') {
            const rec = Number(App.$('#payCashReceived').value) || due;
            if (rec + 0.005 < due) return { error: 'Tunai diterima kurang daripada jumlah.' };
            return { tenders: [{ method: 'cash', amount: round2(due), cash_received: round2(rec), reference: ref }] };
        }
        return { tenders: [{ method: m, amount: round2(due), reference: ref }] };
    }
    const a2 = round2(App.$('#splitAmount2').value);
    const a1 = round2(due - a2);
    if (a1 <= 0 || a2 <= 0) return { error: 'Amaun berpisah tidak sah (mesti kurang daripada jumlah).' };
    const rec = Number(App.$('#payCashReceived').value) || a1;
    if (rec + 0.005 < a1) return { error: 'Tunai diterima kurang daripada bahagian tunai.' };
    return { tenders: [
        { method: 'cash', amount: a1, cash_received: round2(rec), reference: ref },
        { method: App.$('#splitMethod2').value, amount: a2 },
    ] };
}

async function confirmPay() {
    if (!POS.payTarget) return;
    const isCart = POS.payTarget.mode === 'cart';
    await App.withLoading(App.$('#payConfirmBtn'), async () => {
        try {
            let due = POS.payDue;
            let targetId = POS.payTarget.id;
            if (isCart) {
                const sale = await commitCart();
                targetId = sale.id;
                due = Number(sale.total_amount) || POS.payDue;
            } else {
                const fresh = await App.api.get(`/api/v1/pos/sales/${targetId}`);
                due = Number(fresh.total_amount) || due;
            }
            const built = buildTenders(due);
            if (built.error) { showPayError(built.error); return; }

            let uuid = null;
            try { uuid = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : null; } catch { uuid = null; }

            const res = await App.api.post(`/api/v1/pos/sales/${targetId}/pay`, { tenders: built.tenders, client_uuid: uuid });
            const d = res.data ?? {};
            App.toast('Bayaran berjaya — baki ' + App.rupiah(d.change_amount ?? 0), 'success');
            closePayDialog();
            clearCart();
            loadSales(); loadSummary(); loadShiftSummary();
        } catch (e) {
            showPayError(e.message);
        }
    });
}

// ---------- Jualan ----------
let salesTimer = null;
function debouncedSales() { clearTimeout(salesTimer); salesTimer = setTimeout(loadSales, 250); }

function statusBadge(s) {
    const map = { draft: 'warning', completed: 'success', cancelled: 'danger', refunded: 'info', partially_refunded: 'info' };
    return `<span class="badge badge-${map[s] ?? 'neutral'}">${App.escapeHtml(s ?? '—')}</span>`;
}

async function loadSales() {
    const params = new URLSearchParams();
    params.set('per_page', '50');
    const st = App.$('#filterStatus').value; if (st) params.set('status', st);
    const rg = App.$('#filterRegister').value; if (rg) params.set('register_id', rg);
    const q = App.$('#filterSearch').value.trim(); if (q) params.set('search', q);
    try {
        const r = await App.api.get('/api/v1/pos/sales?' + params.toString());
        const list = rows(r);
        App.$('#saleTable').innerHTML = list.length ? list.map(s => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(s.sale_no ?? '—')}</td>
                <td class="text-xs">${App.date(s.created_at)}</td>
                <td class="text-xs">${App.escapeHtml(s.register?.name ?? '—')}</td>
                <td class="text-xs">${App.escapeHtml(s.cashier?.name ?? '—')}</td>
                <td class="table-num">${App.rupiah(s.total_amount ?? 0)}</td>
                <td class="text-xs">${App.escapeHtml(s.payment_method ?? '—')}</td>
                <td>${statusBadge(s.status)}</td>
                <td>${saleActions(s)}</td>
            </tr>
        `).join('') : '<tr><td colspan="8"><div class="empty"><div class="empty-title">Tiada jualan</div><div class="empty-text">Jualan akan muncul di sini.</div></div></td></tr>';
    } catch (e) {
        App.$('#saleTable').innerHTML = `<tr><td colspan="8"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

function saleActions(s) {
    if (s.status === 'draft') {
        return `<button class="btn btn-sm btn-primary" onclick="openPayForSale(${s.id}, ${Number(s.total_amount) || 0})">Bayar</button> ` +
               `<button class="btn btn-sm" onclick="cancelSale(${s.id})">Batal</button>`;
    }
    if (s.status === 'completed') {
        return `<button class="btn btn-sm" onclick="printPosReceipt(${s.id})">Cetak resit USB</button> ` +
               `<button class="btn btn-sm btn-danger" onclick="openRefund(${s.id}, ${Number(s.total_amount) || 0})">Pulangan</button>`;
    }
    return '—';
}

async function cancelSale(id) {
    if (!confirm('Batal jualan ini?')) return;
    try {
        await App.api.post(`/api/v1/pos/sales/${id}/cancel`);
        App.toast('Jualan dibatalkan', 'success');
        loadSales(); loadSummary();
    } catch (e) { App.toast(e.message, 'error'); }
}

// ---------- Pulangan ----------
function openRefund(id, total) {
    App.$('#refundSaleId').value = id;
    App.$('#refundAmount').value = total;
    App.$('#refundReason').value = '';
    App.$('#refundError').hidden = true;
    App.$('#refundDialog').hidden = false;
}
function hideRefund() { App.$('#refundDialog').hidden = true; }
async function confirmRefund() {
    const id = App.$('#refundSaleId').value;
    const amount = Number(App.$('#refundAmount').value) || 0;
    const reason = App.$('#refundReason').value.trim();
    if (!reason) { const el = App.$('#refundError'); el.textContent = 'Sebab diperlukan.'; el.hidden = false; return; }
    await App.withLoading(App.$('#refundBtn'), async () => {
        try {
            await App.api.post(`/api/v1/pos/sales/${id}/refund`, { amount, reason });
            App.toast('Pulangan diproses', 'success');
            hideRefund(); loadSales(); loadSummary();
        } catch (e) { const el = App.$('#refundError'); el.textContent = e.message; el.hidden = false; }
    });
}

// ---------- Syif Tunai ----------
function shiftBadge(s) {
    const map = { open: 'success', closed: 'neutral', locked: 'info' };
    return `<span class="badge badge-${map[s] ?? 'neutral'}">${App.escapeHtml(s ?? '—')}</span>`;
}

async function loadShifts() {
    try {
        const r = await App.api.get('/api/v1/pos/shifts');
        const list = rows(r);
        const open = list.filter(x => x.status === 'open');
        App.$('#cmShift').innerHTML = open.length
            ? open.map(x => `<option value="${x.id}">#${x.id} · ${App.escapeHtml(x.register?.name ?? '')}</option>`).join('')
            : '<option value="">Tiada syif terbuka</option>';

        App.$('#shiftTable').innerHTML = list.length ? list.map(x => `
            <tr>
                <td class="mono text-xs">#${x.id}</td>
                <td class="text-xs">${App.escapeHtml(x.shift_date ?? '—')}</td>
                <td class="text-xs">${App.escapeHtml(x.register?.name ?? '—')}</td>
                <td class="text-xs">${App.escapeHtml(x.cashier?.name ?? x.opener?.name ?? '—')}</td>
                <td class="table-num">${App.rupiah(x.opening_cash ?? 0)}</td>
                <td class="table-num">${App.rupiah(x.expected_cash ?? 0)}</td>
                <td class="table-num">${(x.actual_cash ?? null) !== null ? App.rupiah(x.actual_cash) : '—'}</td>
                <td class="table-num">${(x.cash_discrepancy ?? null) !== null ? App.rupiah(x.cash_discrepancy) : '—'}</td>
                <td>${shiftBadge(x.status)}</td>
                <td>${shiftActions(x)}</td>
            </tr>
        `).join('') : '<tr><td colspan="10"><div class="empty"><div class="empty-title">Tiada syif</div><div class="empty-text">Buka syif untuk mula menjejak tunai.</div></div></td></tr>';
    } catch (e) {
        App.$('#shiftTable').innerHTML = `<tr><td colspan="10"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

function shiftActions(x) {
    if (x.status === 'open') return `<button class="btn btn-sm btn-primary" onclick="openCloseShift(${x.id})">Tutup</button>`;
    if (x.status === 'closed') return `<button class="btn btn-sm" onclick="lockShift(${x.id})">Kunci</button>`;
    return '—';
}

async function openShift() {
    const reg = App.$('#openShiftRegister').value;
    if (!reg) { App.toast('Pilih kaunter.', 'error'); return; }
    await App.withLoading(App.$('#openShiftBtn'), async () => {
        try {
            await App.api.post('/api/v1/pos/shifts/open', { register_id: Number(reg), opening_cash: Number(App.$('#openShiftCash').value) || 0 });
            App.toast('Syif dibuka', 'success');
            loadShifts(); loadShiftSummary(); loadCurrentShift();
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

async function recordCashMovement() {
    const shift = App.$('#cmShift').value;
    if (!shift) { App.toast('Tiada syif terbuka.', 'error'); return; }
    const amount = Number(App.$('#cmAmount').value) || 0;
    if (amount <= 0) { App.toast('Jumlah tidak sah.', 'error'); return; }
    await App.withLoading(App.$('#cmBtn'), async () => {
        try {
            await App.api.post(`/api/v1/pos/shifts/${shift}/cash`, {
                type: App.$('#cmType').value,
                amount,
                reason: App.$('#cmReason').value.trim() || null,
            });
            App.toast('Pergerakan tunai direkod', 'success');
            App.$('#cmAmount').value = ''; App.$('#cmReason').value = '';
            loadShifts();
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

async function lockShift(id) {
    try { await App.api.post(`/api/v1/pos/shifts/${id}/lock`); App.toast('Syif dikunci', 'success'); loadShifts(); }
    catch (e) { App.toast(e.message, 'error'); }
}

function openCloseShift(id) {
    App.$('#closeShiftId').value = id;
    App.$('#closeShiftCash').value = '';
    App.$('#closeShiftNotes').value = '';
    App.$('#closeShiftError').hidden = true;
    App.$('#closeShiftSummary').innerHTML = '<div class="skeleton" style="height:40px"></div>';
    App.$('#closeShiftDialog').hidden = false;
    loadCloseShiftSummary(id);
}
function hideCloseShift() { App.$('#closeShiftDialog').hidden = true; }

async function loadCloseShiftSummary(id) {
    try {
        const r = await App.api.get(`/api/v1/pos/shifts/${id}/cash-summary`);
        const d = r.data ?? r;
        App.$('#closeShiftSummary').innerHTML = `
            <div class="pos-cart-line"><span class="text-muted">Tunai awal</span><span>${App.rupiah(d.opening_cash ?? 0)}</span></div>
            <div class="pos-cart-line"><span class="text-muted">Jualan tunai</span><span>${App.rupiah(d.cash_sales_total ?? 0)}</span></div>
            <div class="pos-cart-line"><span class="text-muted">Jualan bukan tunai</span><span>${App.rupiah(d.non_cash_sales_total ?? 0)}</span></div>
            <div class="pos-cart-line"><span class="text-muted">Tunai masuk</span><span>${App.rupiah(d.cash_in ?? 0)}</span></div>
            <div class="pos-cart-line"><span class="text-muted">Tunai keluar</span><span>− ${App.rupiah(d.cash_out ?? 0)}</span></div>
            <div class="pos-cart-line grand"><span>Tunai dijangka</span><span>${App.rupiah(d.expected_cash ?? 0)}</span></div>`;
        App.$('#closeShiftCash').value = d.expected_cash ?? '';
    } catch (e) {
        App.$('#closeShiftSummary').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}

async function confirmCloseShift() {
    const id = App.$('#closeShiftId').value;
    const actual = Number(App.$('#closeShiftCash').value) || 0;
    await App.withLoading(App.$('#closeShiftBtn'), async () => {
        try {
            await App.api.post(`/api/v1/pos/shifts/${id}/close-with-summary`, { actual_cash: actual, notes: App.$('#closeShiftNotes').value.trim() || null });
            App.toast('Syif ditutup', 'success');
            hideCloseShift(); loadShifts(); loadShiftSummary(); loadCurrentShift();
        } catch (e) { const el = App.$('#closeShiftError'); el.textContent = e.message; el.hidden = false; }
    });
}

// ---------- Kaunter CRUD ----------
async function createRegister() {
    const name = App.$('#regName').value.trim();
    if (!name) { App.toast('Nama diperlukan.', 'error'); return; }
    await App.withLoading(App.$('#regAddBtn'), async () => {
        try {
            await App.api.post('/api/v1/pos/registers', { name, location: App.$('#regLocation').value.trim() || null });
            App.$('#regName').value = ''; App.$('#regLocation').value = '';
            App.toast('Kaunter ditambah', 'success');
            loadRegisters();
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

async function toggleRegister(id) {
    try { await App.api.post(`/api/v1/pos/registers/${id}/toggle`); loadRegisters(); }
    catch (e) { App.toast(e.message, 'error'); }
}

async function deleteRegister(id) {
    if (!confirm('Hapus kaunter ini?')) return;
    try { await App.api.del(`/api/v1/pos/registers/${id}`); App.toast('Kaunter dihapus', 'success'); loadRegisters(); }
    catch (e) { App.toast(e.message, 'error'); }
}

function openRegDialog(id) {
    const g = POS.registers.find(x => x.id === id);
    if (!g) return;
    App.$('#editRegId').value = id;
    App.$('#editRegName').value = g.name ?? '';
    App.$('#editRegLocation').value = g.location ?? '';
    App.$('#regError').hidden = true;
    App.$('#regDialog').hidden = false;
}
function closeRegDialog() { App.$('#regDialog').hidden = true; }

async function saveRegister() {
    const id = App.$('#editRegId').value;
    const name = App.$('#editRegName').value.trim();
    if (!name) { const el = App.$('#regError'); el.textContent = 'Nama diperlukan.'; el.hidden = false; return; }
    await App.withLoading(App.$('#regSaveBtn'), async () => {
        try {
            await App.api.put(`/api/v1/pos/registers/${id}`, { name, location: App.$('#editRegLocation').value.trim() || null });
            App.toast('Kaunter dikemas kini', 'success');
            closeRegDialog(); loadRegisters();
        } catch (e) { const el = App.$('#regError'); el.textContent = e.message; el.hidden = false; }
    });
}

// ---------- Paparan Dapur (KDS) ----------
const KDS_FLOW = ['new', 'preparing', 'ready', 'served'];

async function loadKitchen() {
    try {
        const r = await App.api.get('/api/v1/pos/kitchen');
        const tickets = r.data ?? [];
        App.$('#kdsMeta').textContent = (r.enabled ? '' : 'Paparan dapur DIMATIKAN (config) · ') + tickets.length + ' tiket aktif';
        App.$('#kdsGrid').innerHTML = tickets.length ? tickets.map(t => `
            <div class="card pos-ticket" data-status="${App.escapeHtml(t.kitchen_status ?? '')}">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(t.sale_no ?? ('#' + t.id))}</h3>
                        <p class="card-subtitle">${App.escapeHtml(t.order_type ?? '')}${t.table ? ' · Meja ' + App.escapeHtml(t.table) : ''} · ${t.waiting_minutes ?? 0} min</p>
                    </div>
                    <span class="badge badge-brand">${App.escapeHtml(t.kitchen_status ?? '—')}</span>
                </div>
                <div class="stack">
                    ${(t.items ?? []).map(i => `<div class="text-sm">${i.quantity}× ${App.escapeHtml(i.name)}</div>`).join('') || '<div class="text-xs text-muted">Tiada item</div>'}
                </div>
                <div class="card-footer">${kdsActions(t)}</div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada tiket aktif</div><div class="empty-text">Pesanan akan muncul selepas pembayaran (jika paparan dapur diaktifkan).</div></div></div>';
    } catch (e) {
        App.$('#kdsGrid').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`;
    }
}

function kdsActions(t) {
    const idx = KDS_FLOW.indexOf(t.kitchen_status);
    const next = idx >= 0 && idx < KDS_FLOW.length - 1 ? KDS_FLOW[idx + 1] : null;
    return next ? `<button class="btn btn-sm btn-primary" onclick="advanceKitchen(${t.id}, '${next}')">→ ${next}</button>` : '—';
}

async function advanceKitchen(id, status) {
    try { await App.api.post(`/api/v1/pos/kitchen/${id}/status`, { status }); loadKitchen(); }
    catch (e) { App.toast(e.message, 'error'); }
}

// ---------- Kelulusan Diskaun ----------
async function loadLimits() {
    try {
        const d = await App.api.get('/api/v1/pos/discounts/limits');
        POS.discountLimits = d;
        App.$('#discLimit').textContent = `Perlu kelulusan > ${d.requires_approval_above ?? 0}% · had mutlak ${d.max_percent ?? 0}%`;
    } catch { /* abaikan */ }
}

async function loadDiscounts() {
    try {
        const r = await App.api.get('/api/v1/pos/discounts/pending');
        const list = rows(r);
        App.$('#discList').innerHTML = list.length ? list.map(x => `
            <div class="card card-hover mb-4">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(String(x.discount_percent))}%${x.discount_amount ? ' · ' + App.rupiah(x.discount_amount) : ''}</h3>
                        <p class="card-subtitle">Jualan #${App.escapeHtml(String(x.sale_id ?? '—'))} · ${App.escapeHtml(x.reason ?? '')}</p>
                    </div>
                    <span class="badge badge-warning">menunggu</span>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm btn-primary" onclick="approveDiscount(${x.id})">Lulus</button>
                    <button class="btn btn-sm btn-danger" onclick="rejectDiscount(${x.id})">Tolak</button>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada permohonan menunggu.</div></div></div>';
    } catch (e) {
        App.$('#discList').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}

async function requestDiscount() {
    const percent = Number(App.$('#discPercent').value) || 0;
    if (percent <= 0) { App.toast('Peratus tidak sah.', 'error'); return; }
    await App.withLoading(App.$('#discBtn'), async () => {
        try {
            await App.api.post('/api/v1/pos/discounts', {
                sale_id: Number(App.$('#discSaleId').value) || null,
                discount_percent: percent,
                reason: App.$('#discReason').value.trim() || null,
            });
            App.toast('Permohonan dihantar', 'success');
            App.$('#discPercent').value = ''; App.$('#discSaleId').value = ''; App.$('#discReason').value = '';
            loadDiscounts();
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

async function approveDiscount(id) {
    try { await App.api.post(`/api/v1/pos/discounts/${id}/approve`); App.toast('Diskaun diluluskan', 'success'); loadDiscounts(); }
    catch (e) { App.toast(e.message, 'error'); }
}

async function rejectDiscount(id) {
    try { await App.api.post(`/api/v1/pos/discounts/${id}/reject`, {}); App.toast('Diskaun ditolak', 'success'); loadDiscounts(); }
    catch (e) { App.toast(e.message, 'error'); }
}

// ---------- Init ----------
loadAll();
</script>
<script type="module">
import { ThermalPrinter } from '{{ asset('js/pos-printer.js') }}';
const printer = new ThermalPrinter();
const status = document.getElementById('printerStatus');
const connect = document.getElementById('printerConnect');
const disconnect = document.getElementById('printerDisconnect');
let printing = false;
const report = message => { status.textContent = message; };
connect.disabled = !printer.supported;
if (!printer.supported) report('WebUSB tidak tersedia. Gunakan Chrome/Edge desktop melalui HTTPS atau localhost.');
connect.addEventListener('click', async () => {
    connect.disabled = true;
    try { await printer.connect(); report('Pencetak disambung. Cetak melalui tab Jualan.'); }
    catch (e) { report('Tidak disambung: ' + e.message + ' Semak izin USB, pemacu OS dan keserasian ESC/POS.'); }
    finally { connect.disabled = !printer.supported; }
});
disconnect.addEventListener('click', async () => {
    if (printer.busy || printing) return;
    await printer.disconnect(); report('Pencetak diputuskan.');
});
navigator.usb?.addEventListener('disconnect', () => { if (!printer.device) report('Pencetak diputuskan. Sambung semula untuk mencetak.'); });
window.printPosReceipt = async id => {
    if (printing || printer.busy) return;
    if (!printer.device?.opened) { report('Sambungkan pencetak dahulu.'); return; }
    printing = true;
    connect.disabled = disconnect.disabled = true;
    try {
        const sale = await App.api.get(`/api/v1/pos/sales/${id}`);
        await printer.print(sale, Number(document.getElementById('receiptWidth').value));
        report('Data dihantar ke USB. Semak resit fizikal; penghantaran bukan pengesahan cetakan.');
    } catch (e) { report(e.message); }
    finally { printing = false; connect.disabled = !printer.supported; disconnect.disabled = false; }
};
</script>







@endpush

