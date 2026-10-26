@extends('layouts.app')

@section('title', 'CRM')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">CRM</h1>
        <p class="page-desc">Pelanggan, leads, peluang jualan dan pipeline.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Lead Baru</div><div class="stat-value" id="sLeads">—</div></div>
    <div class="stat"><div class="stat-label">Peluang Aktif</div><div class="stat-value" id="sOpps">—</div></div>
    <div class="stat"><div class="stat-label">Nilai Pipeline</div><div class="stat-value" id="sValue">—</div></div>
    <div class="stat"><div class="stat-label">Kadar Tukar</div><div class="stat-value" id="sConv">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="leads">Leads</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="opps">Peluang</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="customers">Pelanggan</button>
</div>

<section data-panel="leads">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>Syarikat</th><th>Sumber</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody id="leadTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="opps" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Tajuk</th><th>Pelanggan</th><th>Peringkat</th><th class="table-num">Nilai</th></tr></thead>
            <tbody id="oppTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="customers" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>E-mel</th><th>Telefon</th><th class="table-num">Jumlah Belian</th></tr></thead>
            <tbody id="custTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadStats(), loadLeads(), loadOpps(), loadCustomers()]);
}

async function loadStats() {
    try {
        const s = await App.api.get('/api/v1/crm/statistics/pipeline');
        const d = s.data ?? {};
        App.$('#sOpps').textContent = App.number(d.count ?? 0);
        App.$('#sValue').textContent = App.rupiah(d.total_value ?? 0);
        App.$('#sConv').textContent = (d.conversion_rate ?? 0) + '%';
    } catch { /* abaikan */ }

    try {
        const l = await App.api.get('/api/v1/crm/statistics/leads');
        App.$('#sLeads').textContent = App.number(l.data?.total ?? l.data?.count ?? 0);
    } catch { /* abaikan */ }
}

async function loadLeads() {
    try {
        const r = await App.api.get('/api/v1/crm/leads?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#leadTable').innerHTML = rows.length ? rows.map(l => `
            <tr>
                <td class="text-sm">${App.escapeHtml(l.name ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(l.company ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(l.source ?? '—')}</span></td>
                <td><span class="badge badge-${l.status === 'qualified' ? 'success' : l.status === 'lost' ? 'danger' : 'warning'}">${App.escapeHtml(l.status ?? '—')}</span></td>
                <td>${l.status !== 'converted' ? `<button class="btn btn-sm" onclick="convertLead(${l.id})">Tukar</button>` : '<span class="text-xs text-muted">Selesai</span>'}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada lead</div></div></td></tr>';
    } catch (e) {
        App.$('#leadTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadOpps() {
    try {
        const r = await App.api.get('/api/v1/crm/opportunities?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#oppTable').innerHTML = rows.length ? rows.map(o => `
            <tr>
                <td class="text-sm">${App.escapeHtml(o.title ?? o.name ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(o.customer?.name ?? '—')}</td>
                <td><span class="badge badge-brand">${App.escapeHtml(o.stage ?? '—')}</span></td>
                <td class="table-num">${App.rupiah(o.value ?? o.amount ?? 0)}</td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada peluang.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function loadCustomers() {
    try {
        const r = await App.api.get('/api/v1/crm/customers?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#custTable').innerHTML = rows.length ? rows.map(c => `
            <tr>
                <td class="text-sm">${App.escapeHtml(c.name ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(c.email ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(c.phone ?? '—')}</td>
                <td class="table-num">${App.rupiah(c.total_purchases ?? c.lifetime_value ?? 0)}</td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada pelanggan.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function convertLead(id) {
    try {
        await App.api.post(`/api/v1/crm/leads/${id}/convert`);
        App.toast('Lead ditukar kepada pelanggan', 'success');
        loadLeads(); loadCustomers(); loadStats();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
