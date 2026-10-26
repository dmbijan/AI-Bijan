@extends('layouts.app')

@section('title', 'B-Site')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">B-Site</h1>
        <p class="page-desc">Laman web, domain dan halaman.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Laman</div><div class="stat-value" id="sSites">—</div></div>
    <div class="stat"><div class="stat-label">Domain</div><div class="stat-value" id="sDomains">—</div></div>
    <div class="stat"><div class="stat-label">Halaman</div><div class="stat-value" id="sPages">—</div></div>
    <div class="stat"><div class="stat-label">Lawatan</div><div class="stat-value" id="sViews">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="sites">Laman</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="domains">Domain</button>
</div>

<section data-panel="sites">
    <div class="grid grid-3" id="siteGrid"><div class="skeleton" style="height:120px"></div></div>
</section>

<section data-panel="domains" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Domain</th><th>Laman</th><th>SSL</th><th>Status</th></tr></thead>
            <tbody id="domainTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadSites(), loadDomains()]);
}

async function loadSites() {
    try {
        const r = await App.api.get('/api/v1/site/sites');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sSites').textContent = rows.length;
        App.$('#sViews').textContent = App.number(rows.reduce((s, x) => s + Number(x.views ?? 0), 0));

        App.$('#siteGrid').innerHTML = rows.length ? rows.map(s => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(s.name ?? '—')}</h3>
                        <p class="card-subtitle">${App.escapeHtml(s.domain ?? s.primary_domain ?? '')}</p>
                    </div>
                    <span class="badge badge-${s.is_published ? 'success' : 'neutral'}">${s.is_published ? 'Terbit' : 'Draf'}</span>
                </div>
                <div class="row text-xs text-muted">
                    <span>${App.number(s.views ?? 0)} lawatan</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada laman.</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadDomains() {
    try {
        const r = await App.api.get('/api/v1/site/domains');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sDomains').textContent = rows.length;
        App.$('#sPages').textContent = App.number(rows.reduce((s, x) => s + Number(x.page_count ?? 0), 0));

        App.$('#domainTable').innerHTML = rows.length ? rows.map(d => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(d.domain ?? d.name ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(d.site?.name ?? '—')}</td>
                <td><span class="badge badge-${d.ssl_status === 'active' ? 'success' : 'warning'}">${App.escapeHtml(d.ssl_status ?? '—')}</span></td>
                <td><span class="badge badge-${d.status === 'active' || d.is_verified ? 'success' : 'neutral'}">${App.escapeHtml(d.status ?? (d.is_verified ? 'disahkan' : 'belum'))}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada domain.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
