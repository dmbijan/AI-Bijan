@extends('layouts.app')

@section('title', 'Sign & Docs')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Sign &amp; Docs</h1>
        <p class="page-desc">Dokumen, templat dan tandatangan digital.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Dokumen</div><div class="stat-value" id="sDocs">—</div></div>
    <div class="stat"><div class="stat-label">Menunggu Tandatangan</div><div class="stat-value" id="sPending">—</div></div>
    <div class="stat"><div class="stat-label">Ditandatangani</div><div class="stat-value" id="sSigned">—</div></div>
    <div class="stat"><div class="stat-label">Templat</div><div class="stat-value" id="sTemplates">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="docs">Dokumen</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="templates">Templat</button>
</div>

<section data-panel="docs">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Tajuk</th><th>Jenis</th><th>Status</th><th>Dicipta</th></tr></thead>
            <tbody id="docTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="templates" hidden>
    <div class="grid grid-3" id="tplGrid"><div class="skeleton" style="height:100px"></div></div>
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
    await Promise.all([loadDocs(), loadTemplates()]);
}

async function loadDocs() {
    try {
        const r = await App.api.get('/api/v1/sign-docs/documents?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sDocs').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sPending').textContent = rows.filter(d => ['pending', 'awaiting_signature'].includes(d.status)).length;
        App.$('#sSigned').textContent = rows.filter(d => d.status === 'signed' || d.status === 'completed').length;

        App.$('#docTable').innerHTML = rows.length ? rows.map(d => `
            <tr>
                <td class="text-sm">${App.escapeHtml(d.title ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(d.type ?? d.document_type ?? '—')}</span></td>
                <td><span class="badge badge-${['signed','completed'].includes(d.status) ? 'success' : ['pending','awaiting_signature'].includes(d.status) ? 'warning' : 'neutral'}">${App.escapeHtml(d.status ?? '—')}</span></td>
                <td class="text-xs">${App.date(d.created_at)}</td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-title">Tiada dokumen</div></div></td></tr>';
    } catch (e) {
        App.$('#docTable').innerHTML = `<tr><td colspan="4"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadTemplates() {
    try {
        const r = await App.api.get('/api/v1/sign-docs/templates');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sTemplates').textContent = rows.length;

        App.$('#tplGrid').innerHTML = rows.length ? rows.map(t => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(t.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(t.type ?? '')}</p>
                    </div>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada templat.</div></div></div>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
