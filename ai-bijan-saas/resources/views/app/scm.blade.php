@extends('layouts.app')

@section('title', 'SCM')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">SCM</h1>
        <p class="page-desc">Pembekal dan pesanan belian.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Pembekal</div><div class="stat-value" id="sSuppliers">—</div></div>
    <div class="stat"><div class="stat-label">PO Aktif</div><div class="stat-value" id="sPo">—</div></div>
    <div class="stat"><div class="stat-label">Nilai PO</div><div class="stat-value" id="sValue">—</div></div>
    <div class="stat"><div class="stat-label">Menunggu Kelulusan</div><div class="stat-value" id="sPending">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="po">Pesanan Belian</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="suppliers">Pembekal</button>
</div>

<section data-panel="po">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No. PO</th><th>Pembekal</th><th class="table-num">Jumlah</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody id="poTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="suppliers" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>E-mel</th><th>Telefon</th><th>Status</th></tr></thead>
            <tbody id="supTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadPo(), loadSuppliers()]);
}

async function loadPo() {
    try {
        const r = await App.api.get('/api/v1/scm/purchase-orders?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sPo').textContent = rows.filter(p => !['cancelled', 'received'].includes(p.status)).length;
        App.$('#sValue').textContent = App.rupiah(rows.reduce((s, p) => s + Number(p.total ?? 0), 0));
        App.$('#sPending').textContent = rows.filter(p => p.status === 'pending' || p.status === 'submitted').length;

        App.$('#poTable').innerHTML = rows.length ? rows.map(p => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(p.po_number ?? '—')}</td>
                <td class="text-sm">${App.escapeHtml(p.supplier?.name ?? '—')}</td>
                <td class="table-num">${App.rupiah(p.total ?? 0)}</td>
                <td><span class="badge badge-${p.status === 'approved' || p.status === 'received' ? 'success' : p.status === 'cancelled' ? 'danger' : 'warning'}">${App.escapeHtml(p.status ?? '—')}</span></td>
                <td>${p.status === 'draft' || p.status === 'pending'
                    ? `<button class="btn btn-sm" onclick="submitPo(${p.id})">Hantar</button>` : '—'}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada PO</div></div></td></tr>';
    } catch (e) {
        App.$('#poTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadSuppliers() {
    try {
        const r = await App.api.get('/api/v1/scm/suppliers');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sSuppliers').textContent = rows.length;

        App.$('#supTable').innerHTML = rows.length ? rows.map(s => `
            <tr>
                <td class="text-sm">${App.escapeHtml(s.name)}</td>
                <td class="text-sm text-muted">${App.escapeHtml(s.email ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(s.phone ?? '—')}</td>
                <td><span class="badge badge-${s.status === 'active' ? 'success' : 'neutral'}">${App.escapeHtml(s.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada pembekal.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function submitPo(id) {
    try {
        await App.api.post(`/api/v1/scm/purchase-orders/${id}/submit`);
        App.toast('PO dihantar', 'success');
        loadPo();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
