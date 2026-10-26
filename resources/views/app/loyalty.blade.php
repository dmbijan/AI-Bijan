@extends('layouts.app')

@section('title', 'Kesetiaan')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Kesetiaan</h1>
        <p class="page-desc">Ahli, mata, ganjaran dan tier.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Ahli</div><div class="stat-value" id="sMembers">—</div></div>
    <div class="stat"><div class="stat-label">Mata Beredar</div><div class="stat-value" id="sPoints">—</div></div>
    <div class="stat"><div class="stat-label">Ganjaran Aktif</div><div class="stat-value" id="sRewards">—</div></div>
    <div class="stat"><div class="stat-label">Liabiliti</div><div class="stat-value" id="sLiability">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="members">Ahli</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="rewards">Ganjaran</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="tiers">Tier</button>
</div>

<section data-panel="members">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>E-mel</th><th>Tier</th><th class="table-num">Mata</th><th>Status</th></tr></thead>
            <tbody id="memberTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="rewards" hidden>
    <div class="grid grid-3" id="rewardGrid"><div class="skeleton" style="height:100px"></div></div>
</section>

<section data-panel="tiers" hidden>
    <div class="grid grid-3" id="tierGrid"><div class="skeleton" style="height:100px"></div></div>
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
    await Promise.all([loadMembers(), loadRewards(), loadTiers(), loadAnalytics()]);
}

async function loadAnalytics() {
    try {
        const r = await App.api.get('/api/v1/loyalty/analytics/liability');
        const d = r.data?.data ?? r.data ?? {};
        App.$('#sLiability').textContent = App.rupiah(d.total_liability ?? d.liability ?? 0);
    } catch { /* abaikan */ }
}

async function loadMembers() {
    try {
        const r = await App.api.get('/api/v1/loyalty/members?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sMembers').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sPoints').textContent = App.number(rows.reduce((s, m) => s + Number(m.points ?? m.points_balance ?? 0), 0));

        App.$('#memberTable').innerHTML = rows.length ? rows.map(m => `
            <tr>
                <td class="text-sm">${App.escapeHtml(m.name ?? m.customer?.name ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(m.email ?? '—')}</td>
                <td><span class="badge badge-brand">${App.escapeHtml(m.tier?.name ?? m.tier ?? '—')}</span></td>
                <td class="table-num">${App.number(m.points ?? m.points_balance ?? 0)}</td>
                <td><span class="badge badge-${m.status === 'active' ? 'success' : 'neutral'}">${App.escapeHtml(m.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada ahli</div></div></td></tr>';
    } catch (e) {
        App.$('#memberTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadRewards() {
    try {
        const r = await App.api.get('/api/v1/loyalty/rewards');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sRewards').textContent = rows.filter(x => x.is_active !== false).length;

        App.$('#rewardGrid').innerHTML = rows.length ? rows.map(x => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(x.name)}</h3>
                        <p class="card-subtitle">${App.number(x.points_cost ?? x.points_required ?? 0)} mata</p>
                    </div>
                    <span class="badge badge-${x.is_active === false ? 'neutral' : 'success'}">${x.is_active === false ? 'Tidak Aktif' : 'Aktif'}</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada ganjaran.</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadTiers() {
    try {
        const r = await App.api.get('/api/v1/loyalty/tiers');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#tierGrid').innerHTML = rows.length ? rows.map(t => `
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(t.name)}</h3>
                        <p class="card-subtitle">Min ${App.number(t.min_points ?? 0)} mata</p>
                    </div>
                    <span class="badge badge-brand">${t.multiplier ?? 1}×</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada tier.</div></div></div>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
