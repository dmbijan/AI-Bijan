@extends('layouts.app')

@section('title', 'Perkilangan')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Perkilangan</h1>
        <p class="page-desc">BOM, pesanan kerja dan pengeluaran.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">BOM</div><div class="stat-value" id="sBom">—</div></div>
    <div class="stat"><div class="stat-label">Work Order</div><div class="stat-value" id="sWo">—</div></div>
    <div class="stat"><div class="stat-label">Dalam Proses</div><div class="stat-value" id="sWip">—</div></div>
    <div class="stat"><div class="stat-label">Selesai</div><div class="stat-value" id="sDone">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="bom">BOM</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="wo">Work Order</button>
</div>

<section data-panel="bom">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Produk</th><th>Versi</th><th class="table-num">Komponen</th><th>Status</th></tr></thead>
            <tbody id="bomTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="wo" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No. WO</th><th>Produk</th><th class="table-num">Kuantiti</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody id="woTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadBom(), loadWo()]);
}

async function loadBom() {
    try {
        const r = await App.api.get('/api/v1/manufacturing/boms');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sBom').textContent = rows.length;

        App.$('#bomTable').innerHTML = rows.length ? rows.map(b => `
            <tr>
                <td class="text-sm">${App.escapeHtml(b.product?.name ?? b.product_name ?? '—')}</td>
                <td class="mono text-xs">v${App.escapeHtml(b.version ?? 1)}</td>
                <td class="table-num">${App.number((b.components ?? b.items ?? []).length)}</td>
                <td><span class="badge badge-${b.is_active !== false ? 'success' : 'neutral'}">${b.is_active !== false ? 'Aktif' : 'Tidak'}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-title">Tiada BOM</div></div></td></tr>';
    } catch (e) {
        App.$('#bomTable').innerHTML = `<tr><td colspan="4"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadWo() {
    try {
        const r = await App.api.get('/api/v1/manufacturing/work-orders?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sWo').textContent = rows.length;
        App.$('#sWip').textContent = rows.filter(w => ['in_progress', 'started'].includes(w.status)).length;
        App.$('#sDone').textContent = rows.filter(w => w.status === 'completed').length;

        App.$('#woTable').innerHTML = rows.length ? rows.map(w => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(w.wo_number ?? w.order_number ?? '—')}</td>
                <td class="text-sm">${App.escapeHtml(w.product?.name ?? '—')}</td>
                <td class="table-num">${App.number(w.quantity ?? 0)}</td>
                <td><span class="badge badge-${w.status === 'completed' ? 'success' : w.status === 'cancelled' ? 'danger' : 'warning'}">${App.escapeHtml(w.status ?? '—')}</span></td>
                <td>${['planned', 'draft'].includes(w.status)
                    ? `<button class="btn btn-sm" onclick="startWo(${w.id})">Mula</button>` : '—'}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada work order.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function startWo(id) {
    try {
        await App.api.post(`/api/v1/manufacturing/work-orders/${id}/start`);
        App.toast('Work order dimulakan', 'success');
        loadWo();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
