@extends('layouts.app')

@section('title', 'Aset')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Aset</h1>
        <p class="page-desc">Aset fizikal, susut nilai dan penyelenggaraan.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Aset</div><div class="stat-value" id="sTotal">—</div></div>
    <div class="stat"><div class="stat-label">Nilai Buku</div><div class="stat-value" id="sValue">—</div></div>
    <div class="stat"><div class="stat-label">Penyelenggaraan</div><div class="stat-value" id="sMaint">—</div></div>
    <div class="stat"><div class="stat-label">Akan Luput</div><div class="stat-value" id="sExpiring">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="assets">Aset</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="categories">Kategori</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="maintenance">Penyelenggaraan</button>
</div>

<section data-panel="assets">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kod</th><th>Nama</th><th>Kategori</th><th class="table-num">Nilai Buku</th><th>Status</th></tr></thead>
            <tbody id="assetTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="categories" hidden>
    <div class="grid grid-3" id="catGrid"><div class="skeleton" style="height:100px"></div></div>
</section>

<section data-panel="maintenance" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Aset</th><th>Jenis</th><th>Tarikh</th><th>Status</th></tr></thead>
            <tbody id="maintTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadAssets(), loadCategories(), loadMaintenance()]);
}

async function loadAssets() {
    try {
        const r = await App.api.get('/api/v1/assets?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sTotal').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sValue').textContent = App.rupiah(rows.reduce((s, a) => s + Number(a.book_value ?? a.current_value ?? 0), 0));

        App.$('#assetTable').innerHTML = rows.length ? rows.map(a => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(a.code ?? a.asset_code ?? '—')}</td>
                <td class="text-sm">${App.escapeHtml(a.name ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(a.category?.name ?? a.category ?? '—')}</span></td>
                <td class="table-num">${App.rupiah(a.book_value ?? a.current_value ?? 0)}</td>
                <td><span class="badge badge-${a.status === 'active' ? 'success' : a.status === 'disposed' ? 'danger' : 'warning'}">${App.escapeHtml(a.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada aset</div></div></td></tr>';
    } catch (e) {
        App.$('#assetTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadCategories() {
    try {
        const r = await App.api.get('/api/v1/assets/categories');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#catGrid').innerHTML = rows.length ? rows.map(c => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(c.name)}</h3>
                        <p class="card-subtitle">${App.number(c.asset_count ?? 0)} aset</p>
                    </div>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada kategori.</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadMaintenance() {
    try {
        const r = await App.api.get('/api/v1/assets/maintenance');
        const rows = r.data?.data ?? r.data ?? [];
        const upcoming = rows.filter(m => ['scheduled', 'pending'].includes(m.status)).length;
        App.$('#sMaint').textContent = upcoming;

        App.$('#maintTable').innerHTML = rows.length ? rows.map(m => `
            <tr>
                <td class="text-sm">${App.escapeHtml(m.asset?.name ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(m.type ?? m.maintenance_type ?? '—')}</span></td>
                <td class="text-xs">${App.date(m.scheduled_date ?? m.created_at)}</td>
                <td><span class="badge badge-${m.status === 'completed' ? 'success' : m.status === 'overdue' ? 'danger' : 'warning'}">${App.escapeHtml(m.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada rekod penyelenggaraan.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
