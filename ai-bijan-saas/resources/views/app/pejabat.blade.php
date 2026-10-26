@extends('layouts.app')

@section('title', 'Pejabat Maya')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Pejabat Maya</h1>
        <p class="page-desc">Ejen, tugasan, papan kerja dan jadual.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Ejen</div><div class="stat-value" id="sAgents">—</div></div>
    <div class="stat"><div class="stat-label">Tugasan</div><div class="stat-value" id="sTasks">—</div></div>
    <div class="stat"><div class="stat-label">Selesai</div><div class="stat-value" id="sDone">—</div></div>
    <div class="stat"><div class="stat-label">Lewat</div><div class="stat-value" id="sLate">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="agents">Ejen</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="tasks">Tugasan</button>
</div>

<section data-panel="agents">
    <div class="grid grid-3" id="agentGrid"><div class="skeleton" style="height:110px"></div></div>
</section>

<section data-panel="tasks" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Tajuk</th><th>Ejen</th><th>Keutamaan</th><th>Status</th><th>Tarikh Akhir</th></tr></thead>
            <tbody id="taskTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadAgents(), loadTasks()]);
}

async function loadAgents() {
    try {
        const r = await App.api.get('/api/v1/virtual-office/agents');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sAgents').textContent = rows.length;

        App.$('#agentGrid').innerHTML = rows.length ? rows.map(a => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(a.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(a.role ?? a.type ?? '')}</p>
                    </div>
                    <span class="badge badge-${a.status === 'active' ? 'success' : 'neutral'}">${App.escapeHtml(a.status ?? '—')}</span>
                </div>
                <div class="row text-xs text-muted">
                    <span>${App.number(a.task_count ?? 0)} tugasan</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada ejen</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadTasks() {
    try {
        const r = await App.api.get('/api/v1/virtual-office/tasks?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        const now = new Date();
        App.$('#sTasks').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sDone').textContent = rows.filter(t => t.status === 'completed').length;
        App.$('#sLate').textContent = rows.filter(t =>
            t.due_date && new Date(t.due_date) < now && t.status !== 'completed'
        ).length;

        App.$('#taskTable').innerHTML = rows.length ? rows.map(t => `
            <tr>
                <td class="text-sm">${App.escapeHtml(t.title ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(t.agent?.name ?? t.agent_name ?? '—')}</td>
                <td><span class="badge badge-${t.priority === 'high' || t.priority === 'urgent' ? 'danger' : 'neutral'}">${App.escapeHtml(t.priority ?? '—')}</span></td>
                <td><span class="badge badge-${t.status === 'completed' ? 'success' : ['in_progress','doing'].includes(t.status) ? 'info' : 'warning'}">${App.escapeHtml(t.status ?? '—')}</span></td>
                <td class="text-xs">${App.date(t.due_date)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada tugasan.</div></div></td></tr>';
    } catch (e) {
        App.$('#taskTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

loadAll();
</script>
@endpush
