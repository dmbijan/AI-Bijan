@extends('layouts.app')

@section('title', 'HRMS')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">HRMS</h1>
        <p class="page-desc">Pekerja, kehadiran, cuti dan syif.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Pekerja</div><div class="stat-value" id="sEmployees">—</div></div>
    <div class="stat"><div class="stat-label">Hadir Hari Ini</div><div class="stat-value" id="sPresent">—</div></div>
    <div class="stat"><div class="stat-label">Cuti Menunggu</div><div class="stat-value" id="sLeave">—</div></div>
    <div class="stat"><div class="stat-label">Jabatan</div><div class="stat-value" id="sDepts">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="employees">Pekerja</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="leave">Cuti</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="attendance">Kehadiran</button>
</div>

<section data-panel="employees">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Nama</th><th>No. Pekerja</th><th>Jawatan</th><th>Jabatan</th><th>Status</th></tr></thead>
            <tbody id="empTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="leave" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Pekerja</th><th>Jenis</th><th>Dari</th><th>Hingga</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody id="leaveTable"><tr><td colspan="6"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="attendance" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Pekerja</th><th>Tarikh</th><th>Masuk</th><th>Keluar</th><th>Status</th></tr></thead>
            <tbody id="attTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
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
    await Promise.all([loadEmployees(), loadLeave(), loadAttendance()]);
}

async function loadEmployees() {
    try {
        const r = await App.api.get('/api/v1/hrms/employees?per_page=50');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sEmployees').textContent = App.number(r.data?.total ?? rows.length);

        App.$('#empTable').innerHTML = rows.length ? rows.map(e => `
            <tr>
                <td class="text-sm">${App.escapeHtml(e.name ?? e.full_name ?? '—')}</td>
                <td class="mono text-xs">${App.escapeHtml(e.employee_number ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(e.position ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(e.department?.name ?? e.department ?? '—')}</span></td>
                <td><span class="badge badge-${e.status === 'active' ? 'success' : 'neutral'}">${App.escapeHtml(e.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada pekerja</div></div></td></tr>';
    } catch (e) {
        App.$('#empTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadLeave() {
    try {
        const r = await App.api.get('/api/v1/hrms/leave/pending');
        const rows = r.data?.data ?? r.data ?? [];
        const pending = rows.filter(l => l.status === 'pending').length;
        App.$('#sLeave').textContent = pending;

        App.$('#leaveTable').innerHTML = rows.length ? rows.map(l => `
            <tr>
                <td class="text-sm">${App.escapeHtml(l.employee?.name ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(l.leave_type ?? l.type ?? '—')}</span></td>
                <td class="text-xs">${App.date(l.start_date)}</td>
                <td class="text-xs">${App.date(l.end_date)}</td>
                <td><span class="badge badge-${l.status === 'approved' ? 'success' : l.status === 'rejected' ? 'danger' : 'warning'}">${App.escapeHtml(l.status ?? '—')}</span></td>
                <td>
                    ${l.status === 'pending' ? `
                        <button class="btn btn-sm" onclick="approveLeave(${l.id}, 'approve')">Lulus</button>
                        <button class="btn btn-sm btn-danger" onclick="approveLeave(${l.id}, 'reject')">Tolak</button>
                    ` : '—'}
                </td>
            </tr>
        `).join('') : '<tr><td colspan="6"><div class="empty"><div class="empty-text">Tiada permohonan cuti.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function loadAttendance() {
    try {
        const r = await App.api.get('/api/v1/hrms/attendance?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sPresent').textContent = rows.filter(a => a.status === 'present').length;

        App.$('#attTable').innerHTML = rows.length ? rows.map(a => `
            <tr>
                <td class="text-sm">${App.escapeHtml(a.employee?.name ?? '—')}</td>
                <td class="text-xs">${App.date(a.date)}</td>
                <td class="text-xs mono">${App.escapeHtml(a.clock_in ?? '—')}</td>
                <td class="text-xs mono">${App.escapeHtml(a.clock_out ?? '—')}</td>
                <td><span class="badge badge-${a.status === 'present' ? 'success' : a.status === 'absent' ? 'danger' : 'warning'}">${App.escapeHtml(a.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada rekod kehadiran.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function approveLeave(id, action) {
    try {
        await App.api.post(`/api/v1/hrms/leave/requests/${id}/${action}`);
        App.toast('Cuti ' + (action === 'approve' ? 'diluluskan' : 'ditolak'), 'success');
        loadLeave();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
