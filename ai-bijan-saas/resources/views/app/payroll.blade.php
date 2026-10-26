@extends('layouts.app')

@section('title', 'Payroll')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Payroll</h1>
        <p class="page-desc">Penggajian, statutori EPF/SOCSO/EIS/PCB dan slip gaji.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Pekerja</div><div class="stat-value" id="sEmployees">—</div></div>
    <div class="stat"><div class="stat-label">Jumlah Kasar</div><div class="stat-value" id="sGross">—</div></div>
    <div class="stat"><div class="stat-label">Potongan</div><div class="stat-value" id="sDeduct">—</div></div>
    <div class="stat"><div class="stat-label">Bersih</div><div class="stat-value" id="sNet">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="employees">Pekerja</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="runs">Payroll Run</button>
</div>

<section data-panel="employees">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>No. Pekerja</th><th class="table-num">Gaji Asas</th><th class="table-num">Elaun</th><th>Status</th></tr></thead>
            <tbody id="empTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="runs" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Tempoh</th><th class="table-num">Kasar</th><th class="table-num">Potongan</th><th class="table-num">Bersih</th><th>Status</th></tr></thead>
            <tbody id="runTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadEmployees(), loadRuns()]);
}

async function loadEmployees() {
    try {
        const r = await App.api.get('/api/v1/payroll/employees?per_page=50');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sEmployees').textContent = App.number(r.data?.total ?? rows.length);

        const gross = rows.reduce((s, e) => s + Number(e.basic_salary ?? 0) + Number(e.allowances_total ?? 0), 0);
        const deduct = rows.reduce((s, e) => s + Number(e.deductions_total ?? 0), 0);
        App.$('#sGross').textContent = App.rupiah(gross);
        App.$('#sDeduct').textContent = App.rupiah(deduct);
        App.$('#sNet').textContent = App.rupiah(gross - deduct);

        App.$('#empTable').innerHTML = rows.length ? rows.map(e => `
            <tr>
                <td class="text-sm">${App.escapeHtml(e.name ?? e.employee_name ?? '—')}</td>
                <td class="mono text-xs">${App.escapeHtml(e.employee_number ?? '—')}</td>
                <td class="table-num">${App.rupiah(e.basic_salary ?? 0)}</td>
                <td class="table-num">${App.rupiah(e.allowances_total ?? 0)}</td>
                <td><span class="badge badge-${e.status === 'active' ? 'success' : 'neutral'}">${App.escapeHtml(e.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada pekerja</div><div class="empty-text">Tambah pekerja untuk mula payroll.</div></div></td></tr>';
    } catch (e) {
        App.$('#empTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadRuns() {
    try {
        const r = await App.api.get('/api/v1/payroll/runs?per_page=20');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#runTable').innerHTML = rows.length ? rows.map(x => `
            <tr>
                <td class="text-sm">${App.escapeHtml(x.period ?? x.month ?? '—')}</td>
                <td class="table-num">${App.rupiah(x.gross_total ?? 0)}</td>
                <td class="table-num">${App.rupiah(x.deduction_total ?? 0)}</td>
                <td class="table-num">${App.rupiah(x.net_total ?? 0)}</td>
                <td><span class="badge badge-${x.status === 'paid' ? 'success' : x.status === 'approved' ? 'info' : 'warning'}">${App.escapeHtml(x.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada payroll run.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
