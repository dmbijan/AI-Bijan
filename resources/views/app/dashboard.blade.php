@extends('layouts.app')

@section('title', 'Papan Pemuka')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Papan Pemuka</h1>
        <p class="page-desc">Ringkasan operasi, AI dan kewangan perniagaan anda. Status dikemas kini daripada servis sebenar.</p>
    </div>
    <button class="btn btn-sm" id="refreshDashboard" onclick="loadStats()" aria-controls="dashboardStats health" aria-busy="false">Muat Semula</button>
</div>

<style>
    .hk-tanya { display: flex; gap: 8px; margin-bottom: 8px; }
    .hk-tanya .input { flex: 1; min-height: 48px; font-size: 15px; }
    .hk-cadang { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
    .hk-cadang button { padding: 6px 12px; border-radius: 999px; border: 1px solid var(--border); background: var(--surface); color: inherit; cursor: pointer; font-size: 13px; }
    .hk-jawapan { white-space: pre-line; padding: 12px 14px; border-radius: 12px; background: var(--bg-subtle); margin-bottom: 16px; font-size: 14px; }
    .hk-kad { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-bottom: 24px; }
    @media (max-width: 1000px) { .hk-kad { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 520px) { .hk-kad { grid-template-columns: 1fr; } }
    .hk-kad a { text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 6px; min-height: 120px; }
    .hk-kad .stat-value { font-size: 26px; }
    .hk-kad .hk-aksi { margin-top: auto; font-size: 13px; color: var(--brand); font-weight: 600; }
    .hk-kad .stat[data-perhatian="1"] { border-color: var(--brand); box-shadow: 0 0 0 1px var(--brand); }
</style>

<form class="hk-tanya" onsubmit="event.preventDefault(); tanya(App.$('#soalan').value)">
    <input class="input" id="soalan" placeholder="Tanya AI Bijan… cth: jualan hari ni?" aria-label="Tanya AI Bijan" autocomplete="off">
    <button class="btn btn-primary" type="submit">Tanya</button>
</form>
<div class="hk-cadang" id="cadangan"></div>
<div class="hk-jawapan" id="jawapan" hidden aria-live="polite"></div>

<div class="hk-kad" id="kadBisnes" aria-live="polite">
    <div class="stat"><a href="/app/pos"><span class="stat-label">Jualan hari ni</span><span class="stat-value" id="kJualan">—</span><span class="stat-delta" id="kJualanSub">&nbsp;</span><span class="hk-aksi">Buka kaunter →</span></a></div>
    <div class="stat" id="kadHutang"><a href="/app/invoicing"><span class="stat-label">Duit belum kutip</span><span class="stat-value" id="kHutang">—</span><span class="stat-delta" id="kHutangSub">&nbsp;</span><span class="hk-aksi">Lihat invois →</span></a></div>
    <div class="stat" id="kadStok"><a href="/app/inventory"><span class="stat-label">Stok perlu tindakan</span><span class="stat-value" id="kStok">—</span><span class="stat-delta" id="kStokSub">&nbsp;</span><span class="hk-aksi">Semak stok →</span></a></div>
    <div class="stat" id="kadLulus"><a href="/app/kelulusan"><span class="stat-label">Cadangan AI untuk diluluskan</span><span class="stat-value" id="kLulus">—</span><span class="stat-delta">&nbsp;</span><span class="hk-aksi">Buka Peti Kelulusan →</span></a></div>
</div>

<h2 class="card-title mb-4">AI &amp; Automasi</h2>
<div class="grid grid-4 mb-5" id="dashboardStats" aria-live="polite">
    <div class="stat">
        <div class="stat-label">Kredit AI</div>
        <div class="stat-value" id="statCredits">—</div>
        <div class="stat-delta" id="statCreditsSub">—</div>
    </div>
    <div class="stat">
        <div class="stat-label">Penggunaan AI</div>
        <div class="stat-value" id="statUsage">—</div>
        <div class="stat-delta" id="statUsageSub">—</div>
    </div>
    <div class="stat">
        <div class="stat-label">Sesi Boardroom</div>
        <div class="stat-value" id="statBoardroom">—</div>
        <div class="stat-delta" id="statBoardroomSub">—</div>
    </div>
    <div class="stat">
        <div class="stat-label">Aliran Aktif</div>
        <div class="stat-value" id="statFlows">—</div>
        <div class="stat-delta" id="statFlowsSub">—</div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Pintasan Pantas</h2>
                <p class="card-subtitle">Mulakan tugas biasa</p>
            </div>
        </div>
        <div class="stack">
            <a href="/app/boardroom" class="btn btn-block dashboard-shortcut">◈&nbsp; Buka sesi Boardroom</a>
            <a href="/app/ai-agents" class="btn btn-block dashboard-shortcut">◆&nbsp; Panggil ejen AI</a>
            <a href="/app/flows" class="btn btn-block dashboard-shortcut">⇄&nbsp; Cipta automasi</a>
            <a href="/app/ai-gateway" class="btn btn-block dashboard-shortcut">⚡&nbsp; Semak kredit AI</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Status Sistem</h2>
                <p class="card-subtitle">Kesihatan komponen utama</p>
            </div>
        </div>
        <div class="stack" id="health">
            <div class="skeleton" style="height:20px" aria-hidden="true"></div>
            <div class="skeleton" style="height:20px" aria-hidden="true"></div>
            <div class="skeleton" style="height:20px" aria-hidden="true"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CADANGAN = ['Jualan hari ni?', 'Siapa belum bayar?', 'Stok apa nak habis?', 'Laporan tutup kedai'];
App.$('#cadangan').innerHTML = CADANGAN.map(c => `<button type="button" onclick="tanya(this.textContent)">${c}</button>`).join('');

async function tanya(soalan) {
    soalan = (soalan || '').trim();
    if (!soalan) return;
    App.$('#soalan').value = soalan;
    const el = App.$('#jawapan');
    el.hidden = false; el.textContent = 'Sedang semak…';
    // Format tebal WhatsApp (*teks*) dibuang untuk paparan web
    try { el.textContent = (await App.api.post('/api/v1/ai-agents/tanya', { soalan })).jawapan.replace(/\*([^*\n]+)\*/g, '$1'); }
    catch (e) { el.textContent = e.message; }
}

async function alat(nama) {
    try { return (await App.api.post('/api/v1/ai-agents/tools/run', { tool: nama, agent: 'papan_pemuka' })).hasil; }
    catch { return null; }
}
async function loadBisnes() {
    const rm = n => App.rupiah(n ?? 0);
    const [jual, hutang, stok, isu, lulus] = await Promise.all([
        alat('pos.ringkasan_hari'), alat('invois.lewat_bayar'), alat('stok.rendah'), alat('pos.semakan_stok'),
        App.api.get('/api/v1/approvals?status=pending').catch(() => null),
    ]);
    if (jual) { App.$('#kJualan').textContent = rm(jual.jumlah); App.$('#kJualanSub').textContent = `${jual.bilangan_jualan} transaksi`; }
    else App.$('#kJualanSub').textContent = 'Tiada akses';
    if (hutang) { App.$('#kHutang').textContent = rm(hutang.jumlah_tertunggak); App.$('#kHutangSub').textContent = `${hutang.bilangan} invois lewat`; App.$('#kadHutang').dataset.perhatian = hutang.bilangan ? '1' : '0'; }
    else App.$('#kHutangSub').textContent = 'Tiada akses';
    const nStok = (stok?.bilangan ?? 0) + (isu?.bilangan ?? 0);
    if (stok || isu) { App.$('#kStok').textContent = nStok; App.$('#kStokSub').textContent = `${stok?.bilangan ?? 0} hampir habis · ${isu?.bilangan ?? 0} isu jualan`; App.$('#kadStok').dataset.perhatian = nStok ? '1' : '0'; }
    else App.$('#kStokSub').textContent = 'Tiada akses';
    const senarai = Array.isArray(lulus) ? lulus : (lulus?.data ?? null);
    if (senarai) { App.$('#kLulus').textContent = senarai.length; App.$('#kadLulus').dataset.perhatian = senarai.length ? '1' : '0'; }
}

async function loadStats() {
    const refresh = App.$('#refreshDashboard');
    refresh.disabled = true;
    refresh.setAttribute('aria-busy', 'true');
    try {
        const authenticated = await App.loadSession();
        if (!authenticated) { location.href = '/login'; return; }
        loadBisnes();

        const results = await Promise.allSettled([
            App.api.get('/api/v1/ai-gateway/wallet'),
            App.api.get('/api/v1/boardroom/stats'),
            App.api.get('/api/v1/flows/stats'),
        ]);
        const normalize = (result, isValid) => {
            if (result.status === 'rejected') return result;
            if (isValid(result.value?.data)) return result;
            return { status: 'rejected', reason: new Error('Respons servis tidak sah') };
        };
        const [wallet, boardroom, flows] = [
            normalize(results[0], data => data && typeof data === 'object' && ['effective_balance', 'balance', 'period_quota'].every(key => key in data)),
            normalize(results[1], data => data && typeof data === 'object' && ['total', 'awaiting_owner'].every(key => key in data)),
            normalize(results[2], data => data && typeof data === 'object'
                && data.workflows && typeof data.workflows === 'object' && 'active' in data.workflows
                && data.runs && typeof data.runs === 'object' && 'total' in data.runs),
        ];
        const setTile = (valueId, subId, value, sub) => {
            App.$(`#${valueId}`).textContent = value;
            App.$(`#${subId}`).textContent = sub;
        };
        const format = value => value == null ? '—' : App.number(value);

        if (wallet.status === 'fulfilled') {
            const data = wallet.value.data;
            setTile('statCredits', 'statCreditsSub', format(data.effective_balance), `Top-up: ${format(data.balance)}`);
            const usage = Number(data.usage_percent);
            const hasUsage = data.usage_percent != null && Number.isFinite(usage);
            setTile('statUsage', 'statUsageSub', hasUsage ? `${usage}%` : '—', `Kuota: ${format(data.period_quota)}`);
        } else {
            setTile('statCredits', 'statCreditsSub', '—', 'Data tidak dapat dimuat');
            setTile('statUsage', 'statUsageSub', '—', 'Data tidak dapat dimuat');
        }

        if (boardroom.status === 'fulfilled') {
            const data = boardroom.value.data;
            setTile('statBoardroom', 'statBoardroomSub', format(data.total), `${format(data.awaiting_owner)} menunggu keputusan`);
        } else {
            setTile('statBoardroom', 'statBoardroomSub', '—', 'Data tidak dapat dimuat');
        }

        if (flows.status === 'fulfilled') {
            const data = flows.value.data;
            setTile('statFlows', 'statFlowsSub', format(data.workflows.active), `${format(data.runs.total)} jumlah run`);
        } else {
            setTile('statFlows', 'statFlowsSub', '—', 'Data tidak dapat dimuat');
        }

        const services = [
            ['AI Gateway', wallet, '/app/ai-gateway'],
            ['Boardroom', boardroom, '/app/boardroom'],
            ['Automasi', flows, '/app/flows'],
        ];
        App.$('#health').innerHTML = services.map(([name, result, href]) => {
            const available = result.status === 'fulfilled';
            const error = result.status === 'rejected' ? result.reason : null;
            const code = error?.status ?? error?.statusCode ?? error?.response?.status;
            const status = available ? 'Respons diterima' : code === 403 ? 'Tiada akses' : 'Tidak dapat disahkan';
            const badge = available ? 'success' : 'warning';
            return `<div class="row-between">
                <a class="text-sm health-link" href="${href}">${name}</a>
                <span class="badge badge-${badge} badge-dot">${status}</span>
            </div>`;
        }).join('');
    } finally {
        refresh.disabled = false;
        refresh.setAttribute('aria-busy', 'false');
    }
}

loadStats();
</script>
@endpush
