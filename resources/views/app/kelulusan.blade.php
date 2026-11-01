@extends('layouts.app')

@section('title', 'Peti Kelulusan')

@section('content')
<style>
    .pk-list { display: grid; gap: 12px; }
    .pk-card { border: 1px solid var(--border); background: var(--surface); border-radius: 14px; padding: 16px; display: grid; gap: 10px; }
    .pk-card[data-s=pending] { border-color: var(--brand); box-shadow: 0 0 0 1px var(--brand); }
    .pk-head { display: flex; justify-content: space-between; gap: 8px; align-items: baseline; flex-wrap: wrap; }
    .pk-title { font-weight: 700; font-size: 15px; }
    .pk-meta { font-size: 12px; color: var(--text-muted); }
    .pk-actions { display: flex; gap: 8px; flex-wrap: wrap; }
    .pk-actions .btn { min-height: 44px; }
    .pk-hasil { font-size: 13px; padding: 8px 10px; border-radius: 8px; background: var(--bg-subtle); }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">Peti Kelulusan</h1>
        <p class="page-desc">Cadangan ejen AI dan permintaan lain yang perlukan keputusan anda. Tiada apa dilaksanakan sebelum anda tekan Lulus.</p>
    </div>
    <button class="btn btn-sm" onclick="muat()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="pending">Menunggu <span class="badge badge-warning" id="cnt" hidden></span></button>
    <button class="tab" role="tab" aria-selected="false" data-tab="selesai">Sejarah</button>
</div>

<section data-panel="pending"><div class="pk-list" id="lPending"><div class="skeleton" style="height:120px"></div></div></section>
<section data-panel="selesai" hidden><div class="pk-list" id="lSelesai"></div></section>
@endsection

@push('scripts')
<script>
const JENIS = { 'ai.tool': 'Cadangan ejen AI' };
const STATUS = { pending: ['Menunggu', 'warning'], approved: ['Diluluskan', 'success'], rejected: ['Ditolak', 'danger'], cancelled: ['Dibatalkan', 'neutral'] };

document.querySelectorAll('[data-tab]').forEach(tab => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-tab]').forEach(x => x.setAttribute('aria-selected', 'false'));
    tab.setAttribute('aria-selected', 'true');
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
}));

function tajuk(a) {
    const p = a.payload ?? {};
    return p.ringkasan ?? (JENIS[a.type] ?? a.type);
}
function hasilTeks(h) {
    if (!h) return '';
    const ok = h.status === 'berjaya';
    return `<div class="pk-hasil">${ok ? '✅ Dilaksanakan & disahkan' : '⚠️ ' + App.escapeHtml(h.mesej ?? h.status)}</div>`;
}
function kad(a, aksi) {
    const p = a.payload ?? {}, [lbl, warna] = STATUS[a.status] ?? [a.status, 'neutral'];
    return `<article class="pk-card" data-s="${a.status}">
        <div class="pk-head">
            <span class="pk-title">${App.escapeHtml(tajuk(a))}</span>
            <span class="badge badge-${warna}">${lbl}</span>
        </div>
        <div class="pk-meta">${App.escapeHtml(JENIS[a.type] ?? a.type)}${p.agent ? ' · ejen: ' + App.escapeHtml(p.agent) : ''}${p.tool ? ' · alat: ' + App.escapeHtml(p.tool) : ''} · ${App.date(a.created_at)}</div>
        ${a.reason ? `<div class="text-sm">Sebab: ${App.escapeHtml(a.reason)}</div>` : ''}
        ${hasilTeks(p.hasil)}
        ${aksi ? `<div class="pk-actions">
            <button class="btn btn-primary" onclick="putus(${a.id}, 'approved')">Lulus</button>
            <button class="btn" onclick="putus(${a.id}, 'rejected')">Tolak</button>
        </div>` : ''}
    </article>`;
}

async function muat() {
    try {
        const [m, s] = await Promise.all([App.api.get('/api/v1/approvals?status=pending'), App.api.get('/api/v1/approvals')]);
        const pending = Array.isArray(m) ? m : (m.data ?? []);
        const semua = (Array.isArray(s) ? s : (s.data ?? [])).filter(a => a.status !== 'pending');
        App.$('#cnt').hidden = !pending.length;
        App.$('#cnt').textContent = pending.length;
        App.$('#lPending').innerHTML = pending.length ? pending.map(a => kad(a, true)).join('')
            : '<div class="card"><div class="empty"><div class="empty-title">Tiada yang menunggu</div><div class="empty-text">Cadangan ejen AI yang perlukan keputusan anda akan muncul di sini.</div></div></div>';
        App.$('#lSelesai').innerHTML = semua.length ? semua.slice(0, 50).map(a => kad(a, false)).join('')
            : '<div class="card"><div class="empty"><div class="empty-text">Belum ada keputusan.</div></div></div>';
    } catch (e) {
        App.$('#lPending').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`;
    }
}

async function putus(id, keputusan) {
    let reason = null;
    if (keputusan === 'rejected') {
        reason = prompt('Sebab tolak (pilihan):');
        if (reason === null) return;
    }
    try {
        const r = await App.api.post(`/api/v1/approvals/${id}/decide`, { decision: keputusan, reason: reason || null });
        const h = r.payload?.hasil;
        if (keputusan === 'approved' && h) App.toast(h.status === 'berjaya' ? 'Diluluskan & dilaksanakan' : 'Diluluskan, tetapi: ' + (h.mesej ?? h.status), h.status === 'berjaya' ? 'success' : 'error');
        else App.toast(keputusan === 'approved' ? 'Diluluskan' : 'Ditolak', 'success');
        muat();
    } catch (e) { App.toast(e.message, 'error'); }
}

(async () => {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    muat();
})();
</script>
@endpush
