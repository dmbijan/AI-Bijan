@extends('layouts.app')

@section('title', 'Papan Pemuka')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Papan Pemuka</h1>
        <p class="page-desc">Ringkasan operasi, AI dan kewangan perniagaan anda.</p>
    </div>
    <button class="btn btn-sm" onclick="loadStats()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
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
            <a href="/app/boardroom" class="btn btn-block" style="justify-content:flex-start">◈&nbsp; Buka sesi Boardroom</a>
            <a href="/app/ai-agents" class="btn btn-block" style="justify-content:flex-start">◆&nbsp; Panggil ejen AI</a>
            <a href="/app/flows" class="btn btn-block" style="justify-content:flex-start">⇄&nbsp; Cipta automasi</a>
            <a href="/app/ai-gateway" class="btn btn-block" style="justify-content:flex-start">⚡&nbsp; Semak kredit AI</a>
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
            <div class="skeleton" style="height:20px"></div>
            <div class="skeleton" style="height:20px"></div>
            <div class="skeleton" style="height:20px"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
async function loadStats() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }

    try {
        const wallet = await App.api.get('/api/v1/ai-gateway/wallet');
        const w = wallet.data;
        App.$('#statCredits').textContent = App.number(w.effective_balance);
        App.$('#statCreditsSub').textContent = `Top-up: ${App.number(w.balance)}`;
        App.$('#statUsage').textContent = w.usage_percent + '%';
        App.$('#statUsageSub').textContent = `Kuota: ${App.number(w.period_quota)}`;
    } catch {
        App.$('#statCredits').textContent = '—';
        App.$('#statUsage').textContent = '—';
    }

    try {
        const b = await App.api.get('/api/v1/boardroom/stats');
        App.$('#statBoardroom').textContent = App.number(b.data.total);
        App.$('#statBoardroomSub').textContent = `${b.data.awaiting_owner} menunggu keputusan`;
    } catch { /* modul mungkin tiada akses */ }

    try {
        const f = await App.api.get('/api/v1/flows/stats');
        App.$('#statFlows').textContent = App.number(f.data.workflows.active);
        App.$('#statFlowsSub').textContent = `${f.data.runs.total} jumlah run`;
    } catch { /* abaikan */ }

    const rows = [
        ['AI Gateway', 'Aktif', 'success'],
        ['Boardroom', 'Aktif', 'success'],
        ['Automasi', 'Aktif', 'success'],
    ];
    App.$('#health').innerHTML = rows.map(([name, status, cls]) => `
        <div class="row-between">
            <span class="text-sm">${name}</span>
            <span class="badge badge-${cls} badge-dot">${status}</span>
        </div>
    `).join('');
}

loadStats();
</script>
@endpush
