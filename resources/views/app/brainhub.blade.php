@extends('layouts.app')

@section('title', 'Brain Hub')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Brain Hub</h1>
        <p class="page-desc">Insight, ramalan dan cadangan AI.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="insights">Insight</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="forecast">Ramalan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="recs">Cadangan</button>
</div>

<section data-panel="insights">
    <div class="grid grid-2" id="insightGrid"><div class="skeleton" style="height:120px"></div></div>
</section>

<section data-panel="forecast" hidden>
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Jana Ramalan</h2>
                <p class="card-subtitle">Masukkan ID metrik</p>
            </div>
        </div>
        <div class="row">
            <input class="input" id="metricId" type="number" placeholder="ID metrik" style="max-width:200px">
            <button class="btn btn-primary" onclick="generateForecast()">Jana</button>
        </div>
        <div id="forecastOut" class="mt-4"></div>
    </div>
</section>

<section data-panel="recs" hidden>
    <div class="grid grid-2" id="recGrid"><div class="skeleton" style="height:120px"></div></div>
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
    await Promise.all([loadInsights(), loadRecommendations()]);
}

async function loadInsights() {
    try {
        const r = await App.api.get('/api/v1/brainhub/insights');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#insightGrid').innerHTML = rows.length ? rows.map(i => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(i.title ?? i.insight_type ?? '—')}</h3>
                        <p class="card-subtitle">${App.escapeHtml(i.category ?? '')}</p>
                    </div>
                    <span class="badge badge-${i.impact_level === 'high' || i.impact_level === 'critical' ? 'danger' : 'info'}">${App.escapeHtml(i.impact_level ?? 'info')}</span>
                </div>
                <p class="card-body text-sm">${App.escapeHtml((i.description ?? '').slice(0, 160))}</p>
                <div class="card-footer">
                    <span class="badge badge-neutral">Kepastian: ${(Number(i.confidence_score ?? 0) * 100).toFixed(0)}%</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada insight</div><div class="empty-text">AI akan menjana insight daripada data anda.</div></div></div>';
    } catch (e) {
        App.$('#insightGrid').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`;
    }
}

async function loadRecommendations() {
    try {
        const r = await App.api.get('/api/v1/brainhub/recommendations');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#recGrid').innerHTML = rows.length ? rows.map(x => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(x.title ?? '—')}</h3>
                        <p class="card-subtitle">${App.escapeHtml(x.category ?? '')}</p>
                    </div>
                </div>
                <p class="card-body text-sm">${App.escapeHtml((x.description ?? x.action ?? '').slice(0, 160))}</p>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada cadangan.</div></div></div>';
    } catch { /* abaikan */ }
}

async function generateForecast() {
    const id = App.$('#metricId').value;
    if (!id) { App.toast('ID metrik diperlukan', 'error'); return; }

    try {
        const r = await App.api.post(`/api/v1/brainhub/forecasts/metric/${id}/generate`);
        App.$('#forecastOut').innerHTML = `<pre class="mono" style="max-height:300px;overflow:auto">${App.escapeHtml(JSON.stringify(r.data, null, 2))}</pre>`;
        App.toast('Ramalan dijana', 'success');
    } catch (e) {
        App.$('#forecastOut').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}

loadAll();
</script>
@endpush
