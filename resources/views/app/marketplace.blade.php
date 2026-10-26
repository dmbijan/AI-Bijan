@extends('layouts.app')

@section('title', 'Marketplace')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Marketplace</h1>
        <p class="page-desc">Produk, vendor dan pesanan marketplace.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Produk</div><div class="stat-value" id="sProducts">—</div></div>
    <div class="stat"><div class="stat-label">Vendor</div><div class="stat-value" id="sVendors">—</div></div>
    <div class="stat"><div class="stat-label">Pesanan</div><div class="stat-value" id="sOrders">—</div></div>
    <div class="stat"><div class="stat-label">Hasil</div><div class="stat-value" id="sRevenue">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="products">Produk</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="vendors">Vendor</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="orders">Pesanan</button>
</div>

<section data-panel="products">
    <div class="grid grid-3" id="productGrid"><div class="skeleton" style="height:120px"></div></div>
</section>

<section data-panel="vendors" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>E-mel</th><th class="table-num">Komisen</th><th>Status</th></tr></thead>
            <tbody id="vendorTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="orders" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No. Pesanan</th><th>Pelanggan</th><th class="table-num">Jumlah</th><th>Status</th><th>Tarikh</th></tr></thead>
            <tbody id="orderTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>
@endsection

@push('scripts')
<script>
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
    await Promise.all([loadProducts(), loadVendors(), loadOrders()]);
}

async function loadProducts() {
    try {
        const r = await App.api.get('/api/v1/marketplace/products?per_page=24');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sProducts').textContent = App.number(r.data?.total ?? rows.length);

        App.$('#productGrid').innerHTML = rows.length ? rows.map(p => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(p.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(p.sku ?? '')}</p>
                    </div>
                    <span class="badge badge-brand">${App.rupiah(p.price ?? 0)}</span>
                </div>
                <div class="row text-xs text-muted">
                    <span>Stok: ${App.number(p.stock_quantity ?? 0)}</span>
                    <span>·</span>
                    <span>${App.number(p.sales_count ?? 0)} terjual</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada produk.</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadVendors() {
    try {
        const r = await App.api.get('/api/v1/marketplace/vendors?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sVendors').textContent = App.number(r.data?.total ?? rows.length);

        App.$('#vendorTable').innerHTML = rows.length ? rows.map(v => `
            <tr>
                <td class="text-sm">${App.escapeHtml(v.name)}</td>
                <td class="text-sm text-muted">${App.escapeHtml(v.contact_email ?? '—')}</td>
                <td class="table-num">${((Number(v.commission_rate ?? 0.05)) * 100).toFixed(1)}%</td>
                <td><span class="badge badge-${v.is_active ? 'success' : 'neutral'}">${v.is_active ? 'Aktif' : 'Tidak'}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada vendor.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function loadOrders() {
    try {
        const r = await App.api.get('/api/v1/marketplace/orders?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sOrders').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sRevenue').textContent = App.rupiah(rows.reduce((s, o) => s + Number(o.total ?? 0), 0));

        App.$('#orderTable').innerHTML = rows.length ? rows.map(o => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(o.order_no ?? '—')}</td>
                <td class="text-sm">${App.escapeHtml(o.customer?.name ?? '—')}</td>
                <td class="table-num">${App.rupiah(o.total ?? 0)}</td>
                <td><span class="badge badge-${o.status === 'completed' ? 'success' : o.status === 'cancelled' ? 'danger' : 'warning'}">${App.escapeHtml(o.status ?? '—')}</span></td>
                <td class="text-xs">${App.date(o.created_at)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada pesanan.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
