@extends('layouts.app')

@section('title', 'Omnichannel')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Omnichannel</h1>
        <p class="page-desc">Saluran pelanggan dan perbualan bersatu.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Saluran</div><div class="stat-value" id="sChannels">—</div></div>
    <div class="stat"><div class="stat-label">Perbualan Aktif</div><div class="stat-value" id="sConvos">—</div></div>
    <div class="stat"><div class="stat-label">Belum Dibaca</div><div class="stat-value" id="sUnread">—</div></div>
    <div class="stat"><div class="stat-label">Diselesaikan</div><div class="stat-value" id="sResolved">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="conversations">Perbualan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="channels">Saluran</button>
</div>

<section data-panel="conversations">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Pelanggan</th><th>Saluran</th><th>Mesej Terakhir</th><th>Status</th><th>Masa</th></tr></thead>
            <tbody id="convTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="channels" hidden>
    <div class="grid grid-3" id="channelGrid">
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
    await Promise.all([loadChannels(), loadConversations()]);
}

async function loadChannels() {
    try {
        const r = await App.api.get('/api/v1/omnichannel/channels');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sChannels').textContent = rows.length;

        App.$('#channelGrid').innerHTML = rows.length ? rows.map(c => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(c.name ?? c.type ?? '—')}</h3>
                        <p class="card-subtitle">${App.escapeHtml(c.type ?? '')}</p>
                    </div>
                    <span class="badge badge-${c.status === 'connected' || c.is_active ? 'success' : 'neutral'}">${c.status ?? (c.is_active ? 'aktif' : 'tidak aktif')}</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada saluran disambungkan.</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadConversations() {
    try {
        const r = await App.api.get('/api/v1/omnichannel/conversations?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sConvos').textContent = rows.filter(c => c.status === 'open').length;
        App.$('#sUnread').textContent = rows.reduce((s, c) => s + Number(c.unread_count ?? 0), 0);
        App.$('#sResolved').textContent = rows.filter(c => c.status === 'resolved' || c.status === 'closed').length;

        App.$('#convTable').innerHTML = rows.length ? rows.map(c => `
            <tr>
                <td class="text-sm">${App.escapeHtml(c.customer?.name ?? c.contact_name ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(c.channel?.name ?? c.channel_type ?? '—')}</span></td>
                <td class="text-sm text-muted">${App.escapeHtml((c.last_message ?? c.preview ?? '—').toString().slice(0, 60))}</td>
                <td><span class="badge badge-${c.status === 'open' ? 'warning' : c.status === 'resolved' ? 'success' : 'neutral'}">${App.escapeHtml(c.status ?? '—')}</span></td>
                <td class="text-xs">${App.date(c.last_message_at ?? c.updated_at)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada perbualan</div></div></td></tr>';
    } catch (e) {
        App.$('#convTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

loadAll();
</script>
@endpush
