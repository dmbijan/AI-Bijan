@extends('layouts.app')

@section('title', 'B-Booking')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">B-Booking</h1>
        <p class="page-desc">Temujanji, perkhidmatan dan slot masa.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Hari Ini</div><div class="stat-value" id="sToday">—</div></div>
    <div class="stat"><div class="stat-label">Menunggu</div><div class="stat-value" id="sPending">—</div></div>
    <div class="stat"><div class="stat-label">Selesai</div><div class="stat-value" id="sDone">—</div></div>
    <div class="stat"><div class="stat-label">Perkhidmatan</div><div class="stat-value" id="sServices">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="appts">Temujanji</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="services">Perkhidmatan</button>
</div>

<section data-panel="appts">
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Pelanggan</th><th>Perkhidmatan</th><th>Masa</th><th>Status</th><th>Tindakan</th></tr></thead>
            <tbody id="apptTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="services" hidden>
    <div class="grid grid-3" id="serviceGrid">
        <div class="skeleton" style="height:110px"></div>
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
    await Promise.all([loadAppointments(), loadServices()]);
}

async function loadAppointments() {
    try {
        const r = await App.api.get('/api/v1/booking/appointments?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        const today = new Date().toDateString();
        App.$('#sToday').textContent = rows.filter(a => a.start_at && new Date(a.start_at).toDateString() === today).length;
        App.$('#sPending').textContent = rows.filter(a => ['pending', 'confirmed'].includes(a.status)).length;
        App.$('#sDone').textContent = rows.filter(a => a.status === 'completed').length;

        App.$('#apptTable').innerHTML = rows.length ? rows.map(a => `
            <tr>
                <td class="text-sm">${App.escapeHtml(a.customer?.name ?? a.customer_name ?? '—')}</td>
                <td class="text-sm text-muted">${App.escapeHtml(a.service?.name ?? a.service_name ?? '—')}</td>
                <td class="text-xs">${App.date(a.start_at ?? a.scheduled_at)}</td>
                <td><span class="badge badge-${a.status === 'completed' ? 'success' : a.status === 'cancelled' ? 'danger' : 'warning'}">${App.escapeHtml(a.status ?? '—')}</span></td>
                <td>${a.status !== 'completed' && a.status !== 'cancelled'
                    ? `<button class="btn btn-sm" onclick="completeAppt(${a.id})">Selesai</button>` : '—'}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada temujanji</div></div></td></tr>';
    } catch (e) {
        App.$('#apptTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadServices() {
    try {
        const r = await App.api.get('/api/v1/booking/services');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sServices').textContent = rows.length;

        App.$('#serviceGrid').innerHTML = rows.length ? rows.map(s => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(s.name)}</h3>
                        <p class="card-subtitle">${s.duration_minutes ?? 0} minit</p>
                    </div>
                    <span class="badge badge-brand">${App.rupiah(s.price ?? 0)}</span>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada perkhidmatan.</div></div></div>';
    } catch { /* abaikan */ }
}

async function completeAppt(id) {
    try {
        await App.api.post(`/api/v1/booking/appointments/${id}/complete`);
        App.toast('Temujanji selesai', 'success');
        loadAppointments();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
