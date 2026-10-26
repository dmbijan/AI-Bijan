@extends('layouts.app')

@section('title', 'Invois')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Invois</h1>
        <p class="page-desc">Invois, nota kredit dan bil berulang.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Invois</div><div class="stat-value" id="sTotal">—</div></div>
    <div class="stat"><div class="stat-label">Telah Dibayar</div><div class="stat-value" id="sPaid">—</div></div>
    <div class="stat"><div class="stat-label">Tertunggak</div><div class="stat-value" id="sOverdue">—</div></div>
    <div class="stat"><div class="stat-label">Draf</div><div class="stat-value" id="sDraft">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="invoices">Invois</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="credit">Nota Kredit</button>
</div>

<section data-panel="invoices">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No. Invois</th><th>Pelanggan</th><th>Tarikh</th><th>Matang</th><th class="table-num">Jumlah</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody id="invTable"><tr><td colspan="7"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="credit" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No. Nota</th><th>Invois Asal</th><th class="table-num">Jumlah</th><th>Status</th></tr></thead>
            <tbody id="cnTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadInvoices(), loadCreditNotes()]);
}

async function loadInvoices() {
    try {
        const r = await App.api.get('/api/v1/invoicing/invoices?per_page=40');
        const rows = r.data?.data ?? r.data ?? [];

        let paid = 0, overdue = 0, draft = 0, total = 0;
        rows.forEach(i => {
            total += Number(i.total ?? i.grand_total ?? 0);
            if (i.status === 'paid') paid += Number(i.total ?? 0);
            if (i.status === 'overdue') overdue += Number(i.total ?? 0);
            if (i.status === 'draft') draft++;
        });

        App.$('#sTotal').textContent = App.rupiah(total);
        App.$('#sPaid').textContent = App.rupiah(paid);
        App.$('#sOverdue').textContent = App.rupiah(overdue);
        App.$('#sDraft').textContent = draft;

        App.$('#invTable').innerHTML = rows.length ? rows.map(i => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(i.invoice_number ?? '—')}</td>
                <td class="text-sm">${App.escapeHtml(i.customer?.name ?? i.customer_name ?? '—')}</td>
                <td class="text-xs">${App.date(i.issue_date)}</td>
                <td class="text-xs">${App.date(i.due_date)}</td>
                <td class="table-num">${App.rupiah(i.total ?? i.grand_total ?? 0)}</td>
                <td><span class="badge badge-${i.status === 'paid' ? 'success' : i.status === 'overdue' ? 'danger' : i.status === 'draft' ? 'neutral' : 'warning'}">${App.escapeHtml(i.status ?? '—')}</span></td>
                <td>
                    ${i.status === 'draft' ? `<button class="btn btn-sm" onclick="issueInvoice(${i.id})">Hantar</button>` : ''}
                    ${i.status === 'issued' || i.status === 'sent' ? `<button class="btn btn-sm" onclick="markPaid(${i.id})">Tanda Dibayar</button>` : ''}
                </td>
            </tr>
        `).join('') : '<tr><td colspan="7"><div class="empty"><div class="empty-title">Tiada invois</div><div class="empty-text">Invois akan muncul di sini.</div></div></td></tr>';
    } catch (e) {
        App.$('#invTable').innerHTML = `<tr><td colspan="7"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadCreditNotes() {
    try {
        const r = await App.api.get('/api/v1/invoicing/credit-notes?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#cnTable').innerHTML = rows.length ? rows.map(n => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(n.credit_note_number ?? '—')}</td>
                <td class="mono text-xs text-muted">${App.escapeHtml(n.invoice?.invoice_number ?? n.invoice_number ?? '—')}</td>
                <td class="table-num">${App.rupiah(n.total ?? 0)}</td>
                <td><span class="badge badge-${n.status === 'applied' ? 'success' : 'neutral'}">${App.escapeHtml(n.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada nota kredit.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function issueInvoice(id) {
    try {
        await App.api.post(`/api/v1/invoicing/invoices/${id}/issue`);
        App.toast('Invois dihantar', 'success');
        loadInvoices();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function markPaid(id) {
    try {
        await App.api.post(`/api/v1/invoicing/invoices/${id}/mark-paid`);
        App.toast('Ditanda dibayar', 'success');
        loadInvoices();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
