@extends('layouts.app')

@section('title', 'B-Link')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">B-Link</h1>
        <p class="page-desc">Halaman pautan dan kad digital.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Halaman Pautan</div><div class="stat-value" id="sPages">—</div></div>
    <div class="stat"><div class="stat-label">Kad Digital</div><div class="stat-value" id="sCards">—</div></div>
    <div class="stat"><div class="stat-label">Jumlah Klik</div><div class="stat-value" id="sClicks">—</div></div>
    <div class="stat"><div class="stat-label">Kadar Klik</div><div class="stat-value" id="sRate">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="cards">Kad Digital</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="analytics">Analitik</button>
</div>

<section data-panel="cards">
    <div class="grid grid-3" id="cardGrid"><div class="skeleton" style="height:110px"></div></div>
</section>

<section data-panel="analytics" hidden>
    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Prestasi Pautan</h2></div>
        </div>
        <div id="analyticsBox"><div class="empty"><div class="empty-text">Memuat…</div></div></div>
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
    await Promise.all([loadCards(), loadAnalytics()]);
}

async function loadCards() {
    try {
        const r = await App.api.get('/api/v1/link/cards');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sCards').textContent = rows.length;

        App.$('#cardGrid').innerHTML = rows.length ? rows.map(c => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(c.name ?? c.title ?? '—')}</h3>
                        <p class="card-subtitle">/${App.escapeHtml(c.slug ?? c.handle ?? '')}</p>
                    </div>
                    <span class="badge badge-${c.is_active !== false ? 'success' : 'neutral'}">${c.is_active !== false ? 'Aktif' : 'Tidak'}</span>
                </div>
                <div class="row text-xs text-muted">
                    <span>${App.number(c.views ?? 0)} lawatan</span>
                    <span>·</span>
                    <span>${App.number(c.clicks ?? 0)} klik</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada kad digital.</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadAnalytics() {
    try {
        const r = await App.api.get('/api/v1/link/analytics');
        const d = r.data?.data ?? r.data ?? {};
        App.$('#sClicks').textContent = App.number(d.total_clicks ?? 0);
        App.$('#sRate').textContent = (d.ctr ?? 0) + '%';
        App.$('#sPages').textContent = App.number(d.total_pages ?? d.pages ?? 0);

        App.$('#analyticsBox').innerHTML = `
            <pre class="mono" style="max-height:280px;overflow:auto">${App.escapeHtml(JSON.stringify(d, null, 2))}</pre>
        `;
    } catch {
        App.$('#analyticsBox').innerHTML = '<div class="empty"><div class="empty-text">Tiada data analitik.</div></div>';
    }
}

loadAll();
</script>
@endpush
