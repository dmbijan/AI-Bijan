@extends('layouts.app')

@section('title', 'Helpdesk')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Helpdesk</h1>
        <p class="page-desc">Tiket sokongan, SLA dan pangkalan pengetahuan.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Tiket</div><div class="stat-value" id="sTotal">—</div></div>
    <div class="stat"><div class="stat-label">Terbuka</div><div class="stat-value" id="sOpen">—</div></div>
    <div class="stat"><div class="stat-label">Selesai</div><div class="stat-value" id="sClosed">—</div></div>
    <div class="stat"><div class="stat-label">Artikel KB</div><div class="stat-value" id="sArticles">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="tickets">Tiket</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="articles">Artikel</button>
</div>

<section data-panel="tickets">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Subjek</th><th>Pelanggan</th><th>Keutamaan</th><th>Status</th><th>Dicipta</th></tr></thead>
            <tbody id="ticketTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="articles" hidden>
    <div class="grid grid-2" id="articleGrid">
        <div class="skeleton" style="height:100px"></div>
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
    await Promise.all([loadTickets(), loadArticles()]);
}

async function loadTickets() {
    try {
        const r = await App.api.get('/api/v1/helpdesk/tickets?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sTotal').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sOpen').textContent = rows.filter(t => ['open', 'pending', 'in_progress'].includes(t.status)).length;
        App.$('#sClosed').textContent = rows.filter(t => ['resolved', 'closed'].includes(t.status)).length;

        App.$('#ticketTable').innerHTML = rows.length ? rows.map(t => `
            <tr>
                <td class="text-sm">${App.escapeHtml(t.subject ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(t.customer?.name ?? t.customer_name ?? '—')}</td>
                <td><span class="badge badge-${t.priority === 'urgent' || t.priority === 'high' ? 'danger' : t.priority === 'low' ? 'neutral' : 'warning'}">${App.escapeHtml(t.priority ?? '—')}</span></td>
                <td><span class="badge badge-${['resolved','closed'].includes(t.status) ? 'success' : ['open','pending'].includes(t.status) ? 'warning' : 'info'}">${App.escapeHtml(t.status ?? '—')}</span></td>
                <td class="text-xs">${App.date(t.created_at)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada tiket</div><div class="empty-text">Tiket sokongan akan muncul di sini.</div></div></td></tr>';
    } catch (e) {
        App.$('#ticketTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadArticles() {
    try {
        const r = await App.api.get('/api/v1/helpdesk/articles');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sArticles').textContent = rows.length;

        App.$('#articleGrid').innerHTML = rows.length ? rows.map(a => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(a.title ?? '—')}</h3>
                        <p class="card-subtitle">${App.escapeHtml(a.category?.name ?? a.category ?? '')}</p>
                    </div>
                </div>
                <p class="card-body text-sm">${App.escapeHtml((a.excerpt ?? a.content ?? '').slice(0, 120))}</p>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada artikel.</div></div></div>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
