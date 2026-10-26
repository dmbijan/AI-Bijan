@extends('layouts.app')

@section('title', 'POS')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">POS</h1>
        <p class="page-desc">Kaunter jualan, syif tunai dan ringkasan harian.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jualan Hari Ini</div><div class="stat-value" id="sSales">—</div></div>
    <div class="stat"><div class="stat-label">Transaksi</div><div class="stat-value" id="sCount">—</div></div>
    <div class="stat"><div class="stat-label">Purata Tiket</div><div class="stat-value" id="sAvg">—</div></div>
    <div class="stat"><div class="stat-label">Kaunter Aktif</div><div class="stat-value" id="sRegisters">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="sales">Jualan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="registers">Kaunter</button>
</div>

<section data-panel="sales">
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>No. Jualan</th><th>Masa</th><th>Kaunter</th><th class="table-num">Jumlah</th><th>Status</th><th>Tindakan</th></tr>
            </thead>
            <tbody id="saleTable">
                <tr><td colspan="6"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr>
            </tbody>
        </table>
    </div>
</section>

<section data-panel="registers" hidden>
    <div class="card mb-4">
        <div class="card-header">
            <div><h2 class="card-title">Tambah Kaunter</h2></div>
        </div>
        <div class="grid grid-3" style="gap:12px">
            <div class="field" style="margin:0">
                <label class="label">Nama Kaunter</label>
                <input class="input" id="regName" placeholder="Kaunter 1">
            </div>
            <div class="field" style="margin:0">
                <label class="label">Lokasi</label>
                <input class="input" id="regLocation" placeholder="Cawangan Utama">
            </div>
            <div class="field" style="margin:0;display:flex;align-items:flex-end">
                <button class="btn btn-primary btn-block" onclick="createRegister()">Tambah</button>
            </div>
        </div>
    </div>
    <div class="grid grid-3" id="regGrid">
        <div class="skeleton" style="height:120px"></div>
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
    await Promise.all([loadSummary(), loadSales(), loadRegisters()]);
}

async function loadSummary() {
    try {
        const r = await App.api.get('/api/v1/pos/sales/summary');
        const d = r.data ?? {};
        App.$('#sSales').textContent = App.rupiah(d.total_amount ?? 0);
        App.$('#sCount').textContent = App.number(d.count ?? 0);
        App.$('#sAvg').textContent = App.rupiah(d.average ?? 0);
    } catch {
        App.$('#sSales').textContent = '—';
    }
}

async function loadSales() {
    try {
        const r = await App.api.get('/api/v1/pos/sales?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#saleTable').innerHTML = rows.length ? rows.map(s => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(s.sale_number ?? s.number ?? '—')}</td>
                <td class="text-xs">${App.date(s.created_at)}</td>
                <td class="text-xs">${App.escapeHtml(s.register?.name ?? '—')}</td>
                <td class="table-num">${App.rupiah(s.total ?? s.grand_total ?? 0)}</td>
                <td><span class="badge badge-${s.status === 'completed' ? 'success' : s.status === 'cancelled' ? 'danger' : 'warning'}">${App.escapeHtml(s.status ?? '—')}</span></td>
                <td>
                    ${s.status === 'draft' ? `<button class="btn btn-sm" onclick="completeSale(${s.id})">Selesai</button>` : '—'}
                </td>
            </tr>
        `).join('') : '<tr><td colspan="6"><div class="empty"><div class="empty-title">Tiada jualan</div><div class="empty-text">Jualan akan muncul di sini.</div></div></td></tr>';
    } catch (e) {
        App.$('#saleTable').innerHTML = `<tr><td colspan="6"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadRegisters() {
    try {
        const r = await App.api.get('/api/v1/pos/registers');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sRegisters').textContent = rows.length;

        App.$('#regGrid').innerHTML = rows.length ? rows.map(g => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(g.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(g.location ?? '')}</p>
                    </div>
                    <span class="badge badge-${g.is_active ? 'success' : 'neutral'}">${g.is_active ? 'Aktif' : 'Tutup'}</span>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm" onclick="toggleRegister(${g.id})">${g.is_active ? 'Tutup' : 'Buka'}</button>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-text">Tiada kaunter.</div></div></div>';
    } catch { /* abaikan */ }
}

async function createRegister() {
    const name = App.$('#regName').value.trim();
    if (!name) { App.toast('Nama diperlukan', 'error'); return; }
    try {
        await App.api.post('/api/v1/pos/registers', {
            name,
            location: App.$('#regLocation').value.trim() || null,
        });
        App.$('#regName').value = '';
        App.$('#regLocation').value = '';
        App.toast('Kaunter ditambah', 'success');
        loadRegisters();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function toggleRegister(id) {
    try {
        await App.api.post(`/api/v1/pos/registers/${id}/toggle`);
        App.toast('Status kaunter dikemas kini', 'success');
        loadRegisters();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function completeSale(id) {
    try {
        await App.api.post(`/api/v1/pos/sales/${id}/complete`);
        App.toast('Jualan selesai', 'success');
        loadSales(); loadSummary();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
