@extends('layouts.app')

@section('title', 'Affiliate')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Affiliate</h1>
        <p class="page-desc">Ahli gabungan, rujukan dan komisen.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Affiliate</div><div class="stat-value" id="sTotal">—</div></div>
    <div class="stat"><div class="stat-label">Aktif</div><div class="stat-value" id="sActive">—</div></div>
    <div class="stat"><div class="stat-label">Jumlah Rujukan</div><div class="stat-value" id="sRefs">—</div></div>
    <div class="stat"><div class="stat-label">Komisen Tertunggak</div><div class="stat-value" id="sCommission">—</div></div>
</div>

<div class="table-wrap">
    <table class="table">
        <thead><tr><th>Nama</th><th>Kod</th><th>Peringkat</th><th class="table-num">Rujukan</th><th class="table-num">Komisen</th><th>Status</th></tr></thead>
        <tbody id="affTable"><tr><td colspan="6"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }

    try {
        const r = await App.api.get('/api/v1/affiliate/affiliates?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#sTotal').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sActive').textContent = rows.filter(a => a.status === 'active').length;
        App.$('#sRefs').textContent = App.number(rows.reduce((s, a) => s + Number(a.referral_count ?? 0), 0));
        App.$('#sCommission').textContent = App.rupiah(rows.reduce((s, a) => s + Number(a.pending_commission ?? 0), 0));

        App.$('#affTable').innerHTML = rows.length ? rows.map(a => `
            <tr>
                <td class="text-sm">${App.escapeHtml(a.name ?? a.user?.name ?? '—')}</td>
                <td class="mono text-xs">${App.escapeHtml(a.code ?? '—')}</td>
                <td><span class="badge badge-brand">${App.escapeHtml(a.tier ?? '—')}</span></td>
                <td class="table-num">${App.number(a.referral_count ?? 0)}</td>
                <td class="table-num">${App.rupiah(a.total_commission ?? 0)}</td>
                <td><span class="badge badge-${a.status === 'active' ? 'success' : a.status === 'pending' ? 'warning' : 'neutral'}">${App.escapeHtml(a.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="6"><div class="empty"><div class="empty-title">Tiada affiliate</div><div class="empty-text">Ahli gabungan akan muncul di sini.</div></div></td></tr>';
    } catch (e) {
        App.$('#affTable').innerHTML = `<tr><td colspan="6"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

loadAll();
</script>
@endpush
