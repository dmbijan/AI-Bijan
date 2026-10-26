@extends('layouts.app')

@section('title', 'Logistik')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Logistik</h1>
        <p class="page-desc">Penghantaran, kurier dan gudang.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Penghantaran</div><div class="stat-value" id="sShip">—</div></div>
    <div class="stat"><div class="stat-label">Dalam Transit</div><div class="stat-value" id="sTransit">—</div></div>
    <div class="stat"><div class="stat-label">Dihantar</div><div class="stat-value" id="sDelivered">—</div></div>
    <div class="stat"><div class="stat-label">Gudang</div><div class="stat-value" id="sWarehouses">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="shipments">Penghantaran</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="warehouses">Gudang</button>
</div>

<section data-panel="shipments">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No. Jejak</th><th>Kurier</th><th>Penerima</th><th>Status</th><th>Tarikh</th></tr></thead>
            <tbody id="shipTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="warehouses" hidden>
    <div class="grid grid-3" id="whGrid"><div class="skeleton" style="height:100px"></div></div>
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
    await Promise.all([loadShipments(), loadWarehouses()]);
}

async function loadShipments() {
    try {
        const r = await App.api.get('/api/v1/logistics/shipments?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sShip').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sTransit').textContent = rows.filter(s => ['in_transit', 'shipped', 'picked_up'].includes(s.status)).length;
        App.$('#sDelivered').textContent = rows.filter(s => s.status === 'delivered').length;

        App.$('#shipTable').innerHTML = rows.length ? rows.map(s => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(s.tracking_number ?? s.awb ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(s.courier ?? s.carrier ?? '—')}</span></td>
                <td class="text-sm">${App.escapeHtml(s.recipient_name ?? s.customer?.name ?? '—')}</td>
                <td><span class="badge badge-${s.status === 'delivered' ? 'success' : s.status === 'failed' ? 'danger' : 'warning'}">${App.escapeHtml(s.status ?? '—')}</span></td>
                <td class="text-xs">${App.date(s.created_at)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada penghantaran</div></div></td></tr>';
    } catch (e) {
        App.$('#shipTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadWarehouses() {
    try {
        const r = await App.api.get('/api/v1/logistics/warehouses');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sWarehouses').textContent = rows.length;

        App.$('#whGrid').innerHTML = rows.length ? rows.map(w => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(w.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(w.address ?? w.location ?? '')}</p>
                    </div>
                    <span class="badge badge-${w.is_active !== false ? 'success' : 'neutral'}">${w.is_active !== false ? 'Aktif' : 'Tidak'}</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada gudang.</div></div></div>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
