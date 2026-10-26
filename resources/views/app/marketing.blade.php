@extends('layouts.app')

@section('title', 'Kempen')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Kempen</h1>
        <p class="page-desc">Kempen pemasaran, segmen dan automasi.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="campaigns">Kempen</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="segments">Segmen</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="ads">Iklan</button>
</div>

<section data-panel="campaigns">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kempen</th><th>Jenis</th><th>Status</th><th class="table-num">Dihantar</th><th class="table-num">Buka</th></tr></thead>
            <tbody id="campTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="segments" hidden>
    <div class="grid grid-3" id="segGrid"><div class="skeleton" style="height:100px"></div></div>
</section>

<section data-panel="ads" hidden>
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header"><div><h2 class="card-title">Google Ads</h2><p class="card-subtitle">Prestasi kempen iklan</p></div></div>
            <div id="googleAds"><div class="empty"><div class="empty-text">Tiada data.</div></div></div>
        </div>
        <div class="card">
            <div class="card-header"><div><h2 class="card-title">Meta Ads</h2><p class="card-subtitle">Facebook &amp; Instagram</p></div></div>
            <div id="metaAds"><div class="empty"><div class="empty-text">Tiada data.</div></div></div>
        </div>
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
    await Promise.all([loadCampaigns(), loadSegments()]);
}

async function loadCampaigns() {
    try {
        const r = await App.api.get('/api/v1/marketing/campaigns?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#campTable').innerHTML = rows.length ? rows.map(c => `
            <tr>
                <td class="text-sm">${App.escapeHtml(c.name ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(c.type ?? c.channel ?? '—')}</span></td>
                <td><span class="badge badge-${c.status === 'sent' || c.status === 'active' ? 'success' : c.status === 'draft' ? 'neutral' : 'warning'}">${App.escapeHtml(c.status ?? '—')}</span></td>
                <td class="table-num">${App.number(c.sent_count ?? 0)}</td>
                <td class="table-num">${App.number(c.open_count ?? 0)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada kempen</div><div class="empty-text">Cipta kempen pertama anda.</div></div></td></tr>';
    } catch (e) {
        App.$('#campTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadSegments() {
    try {
        const r = await App.api.get('/api/v1/marketing/segments');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#segGrid').innerHTML = rows.length ? rows.map(s => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(s.name)}</h3>
                        <p class="card-subtitle">${App.number(s.contact_count ?? 0)} kenalan</p>
                    </div>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada segmen.</div></div></div>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
