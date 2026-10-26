@extends('layouts.app')

@section('title', 'Analitik')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Analitik</h1>
        <p class="page-desc">Papan pemuka, metrik dan laporan.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="dashboards">Papan Pemuka</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="metrics">Metrik</button>
</div>

<section data-panel="dashboards">
    <div class="grid grid-3" id="dashGrid"><div class="skeleton" style="height:120px"></div></div>
</section>

<section data-panel="metrics" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Metrik</th><th>Kunci</th><th class="table-num">Nilai</th><th>Tarikh</th></tr></thead>
            <tbody id="metricTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadDashboards(), loadMetrics()]);
}

async function loadDashboards() {
    try {
        const r = await App.api.get('/api/v1/analytics/dashboards');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#dashGrid').innerHTML = rows.length ? rows.map(d => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(d.name)}</h3>
                        <p class="card-subtitle">${App.number((d.widgets ?? d.items ?? []).length)} widget</p>
                    </div>
                    ${d.is_default ? '<span class="badge badge-brand">Lalai</span>' : ''}
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada papan pemuka</div></div></div>';
    } catch (e) {
        App.$('#dashGrid').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`;
    }
}

async function loadMetrics() {
    try {
        const r = await App.api.get('/api/v1/analytics/metrics');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#metricTable').innerHTML = rows.length ? rows.map(m => `
            <tr>
                <td class="text-sm">${App.escapeHtml(m.name ?? '—')}</td>
                <td class="mono text-xs">${App.escapeHtml(m.key ?? '—')}</td>
                <td class="table-num">${App.number(m.value ?? 0)}</td>
                <td class="text-xs">${App.date(m.recorded_at ?? m.updated_at)}</td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada metrik.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
