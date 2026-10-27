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
async function loadStats() {
    const refresh = App.$('#refreshDashboard');
    refresh.disabled = true;
    refresh.setAttribute('aria-busy', 'true');
    try {
        const authenticated = await App.loadSession();
        if (!authenticated) { location.href = '/login'; return; }

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
