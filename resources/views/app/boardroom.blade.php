@extends('layouts.app')

@section('title', 'Boardroom')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Boardroom</h1>
        <p class="page-desc">Aliran keputusan 7 langkah: persoalan → ejen → dapatan → konflik → ringkasan → keputusan.</p>
    </div>
    <button class="btn btn-sm" onclick="loadSessions()">Muat Semula</button>
</div>

<div class="grid" style="grid-template-columns:minmax(0,1fr) 340px;align-items:start">
    <div>
        {{-- Langkah 1 --}}
        <div class="card mb-4">
            <div class="card-header">
                <div>
                    <h2 class="card-title">1. Kemukakan Persoalan</h2>
                    <p class="card-subtitle">Pemilik bertanya, Chief of Staff pilih ejen relevan</p>
                </div>
                <span class="badge badge-neutral" id="stepBadge">Belum mula</span>
            </div>
            <div class="field">
                <label class="label" for="question">Persoalan untuk Boardroom</label>
                <textarea class="textarea" id="question" rows="2" placeholder="Contoh: Adakah kita patut tambah staf jualan bulan ini?"></textarea>
            </div>
            <button class="btn btn-primary" id="openBtn" onclick="openSession()">Buka Sesi</button>
        </div>

        {{-- Langkah 2-4 --}}
        <div class="card mb-4" id="workCard" hidden>
            <div class="card-header">
                <div>
                    <h2 class="card-title">2–4. Ejen &amp; Dapatan</h2>
                    <p class="card-subtitle">Ejen terlibat dan dapatan yang direkod</p>
                </div>
                <button class="btn btn-sm" onclick="selectAgents()">Pilih Ejen</button>
            </div>

            <div class="stack mb-4" id="agentList"></div>

            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0">
                    <label class="label" for="findingAgent">Ejen</label>
                    <select class="select" id="findingAgent"></select>
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="findingType">Jenis Dapatan</label>
                    <select class="select" id="findingType">
                        <option value="fact">Fakta (wajib sumber)</option>
                        <option value="estimate">Anggaran (wajib sumber)</option>
                        <option value="forecast">Ramalan (wajib sumber)</option>
                        <option value="assumption">Andaian</option>
                        <option value="recommendation">Cadangan</option>
                    </select>
                </div>
            </div>

            <div class="field mt-3">
                <label class="label" for="findingTitle">Tajuk</label>
                <input class="input" id="findingTitle" placeholder="Contoh: Margin kasar bulan lepas">
            </div>
            <div class="field">
                <label class="label" for="findingContent">Kandungan</label>
                <textarea class="textarea" id="findingContent" rows="2" placeholder="Huraian dapatan"></textarea>
            </div>
            <div class="field">
                <label class="label" for="findingSource">Rujukan Sumber</label>
                <input class="input" id="findingSource" placeholder="finance:report:2026-09">
                <div class="field-hint">Wajib untuk fakta, anggaran dan ramalan.</div>
            </div>
            <button class="btn" onclick="addFinding()">Rekod Dapatan</button>

            <div class="table-wrap mt-4" id="findingTableWrap" hidden>
                <table class="table">
                    <thead><tr><th>Ejen</th><th>Jenis</th><th>Tajuk</th><th>Sumber</th></tr></thead>
                    <tbody id="findingTable"></tbody>
                </table>
            </div>
        </div>

        {{-- Langkah 5-7 --}}
        <div class="card" id="decisionCard" hidden>
            <div class="card-header">
                <div>
                    <h2 class="card-title">5–7. Ringkasan &amp; Keputusan</h2>
                    <p class="card-subtitle">Kesan konflik, ringkasan AI CEO, keputusan pemilik</p>
                </div>
            </div>
            <div class="row mb-4">
                <button class="btn" onclick="detectConflicts()">Kesan Konflik</button>
                <button class="btn" onclick="prepareSummary()">Sediakan Ringkasan</button>
            </div>
            <div id="summaryBox"></div>
            <div class="row mt-4" id="decideRow" hidden>
                <button class="btn btn-primary" onclick="decide('approve')">Lulus</button>
                <button class="btn btn-danger" onclick="decide('reject')">Tolak</button>
                <button class="btn" onclick="decide('defer')">Tangguh</button>
            </div>
        </div>
    </div>

    {{-- Senarai sesi --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Sesi Terdahulu</h2>
                <p class="card-subtitle" id="sessionCount">—</p>
            </div>
        </div>
        <div class="stack" id="sessionList">
            <div class="skeleton" style="height:44px"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const br = { sessionId: null, session: null };

async function loadSessions() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }

    try {
        const res = await App.api.get('/api/v1/boardroom/sessions?per_page=10');
        const rows = res.data?.data ?? [];
        App.$('#sessionCount').textContent = `${res.data?.total ?? 0} sesi`;

        App.$('#sessionList').innerHTML = rows.length ? rows.map(s => `
            <button class="btn btn-block" style="justify-content:flex-start;text-align:left"
                    onclick="resumeSession('${s.id}')">
                <span class="badge badge-${s.status === 'decided' ? 'success' : s.status === 'cancelled' ? 'danger' : 'warning'}">${s.status}</span>
                <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${App.escapeHtml(s.question)}</span>
            </button>
        `).join('') : '<div class="empty"><div class="empty-text">Tiada sesi lagi.</div></div>';
    } catch (e) {
        App.toast('Gagal memuat sesi', 'error');
    }
}

async function openSession() {
    const q = App.$('#question').value.trim();
    if (q.length < 5) { App.toast('Persoalan terlalu pendek', 'error'); return; }

    await App.withLoading(App.$('#openBtn'), async () => {
        try {
            const res = await App.api.post('/api/v1/boardroom/sessions', { question: q });
            br.sessionId = res.data.id;
            br.session = res.data;
            render();
            App.toast('Sesi dibuka — kategori: ' + res.data.category, 'success');
        } catch (e) {
            App.toast(e.message, 'error');
        }
    });
}

async function resumeSession(id) {
    const res = await App.api.get(`/api/v1/boardroom/sessions/${id}`);
    br.sessionId = id;
    br.session = res.data;
    render();
}

async function selectAgents() {
    try {
        const res = await App.api.post(`/api/v1/boardroom/sessions/${br.sessionId}/select-agents`, {});
        br.session = res.data;
        render();
        App.toast('Ejen dipilih: ' + (res.data.agents_involved ?? []).join(', '), 'success');
    } catch (e) { App.toast(e.message, 'error'); }
}

async function addFinding() {
    const payload = {
        agent_key: App.$('#findingAgent').value,
        finding_type: App.$('#findingType').value,
        title: App.$('#findingTitle').value.trim(),
        content: App.$('#findingContent').value.trim(),
        source_reference: App.$('#findingSource').value.trim() || undefined,
    };

    try {
        await App.api.post(`/api/v1/boardroom/sessions/${br.sessionId}/findings`, payload);
        App.toast('Dapatan direkod', 'success');
        App.$('#findingTitle').value = '';
        App.$('#findingContent').value = '';
        App.$('#findingSource').value = '';
        await refreshFindings();
    } catch (e) {
        const msg = e.data?.message ?? e.message;
        App.toast(msg, 'error');
    }
}

async function refreshFindings() {
    const res = await App.api.get(`/api/v1/boardroom/sessions/${br.sessionId}/findings`);
    const rows = res.data ?? [];
    App.$('#findingTableWrap').hidden = rows.length === 0;
    App.$('#findingTable').innerHTML = rows.map(f => `
        <tr>
            <td><span class="badge badge-brand">${App.escapeHtml(f.agent_key)}</span></td>
            <td><span class="badge badge-${f.finding_type === 'fact' ? 'success' : f.finding_type === 'assumption' ? 'warning' : 'info'}">${f.finding_type}</span></td>
            <td>${App.escapeHtml(f.title)}</td>
            <td class="mono">${App.escapeHtml(f.source_reference ?? '—')}</td>
        </tr>
    `).join('');
}

async function detectConflicts() {
    try {
        const res = await App.api.post(`/api/v1/boardroom/sessions/${br.sessionId}/detect-conflicts`);
        if (res.data.conflict_detected) {
            App.toast(`Konflik dikesan (${res.data.conflicts.length})`, 'error');
        } else {
            App.toast('Tiada konflik', 'success');
        }
        br.session = res.session;
        render();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function prepareSummary() {
    try {
        const res = await App.api.post(`/api/v1/boardroom/sessions/${br.sessionId}/prepare-summary`);
        br.session = res.data;
        render();
        App.toast('Ringkasan sedia', 'success');
    } catch (e) { App.toast(e.message, 'error'); }
}

async function decide(decision) {
    try {
        const res = await App.api.post(`/api/v1/boardroom/sessions/${br.sessionId}/decide`, {
            decision,
            reason: 'Keputusan pemilik melalui papan pemuka',
        });
        br.session = res.data;
        render();
        App.toast('Keputusan direkod: ' + decision, 'success');
        loadSessions();
    } catch (e) { App.toast(e.message, 'error'); }
}

function render() {
    const s = br.session;
    if (!s) return;

    App.$('#stepBadge').textContent = `Langkah ${s.current_step}/7`;
    App.$('#stepBadge').className = 'badge badge-brand';
    App.$('#workCard').hidden = s.current_step < 2;
    App.$('#decisionCard').hidden = s.current_step < 5;

    const agents = s.agents_involved ?? [];
    App.$('#agentList').innerHTML = agents.length
        ? agents.map(a => `<span class="badge badge-brand">${App.escapeHtml(a)}</span>`).join(' ')
        : '<span class="text-muted text-sm">Belum pilih ejen</span>';

    if (agents.length) {
        const sel = App.$('#findingAgent');
        const cur = sel.value;
        sel.innerHTML = agents.map(a => `<option value="${a}">${a}</option>`).join('');
        if (agents.includes(cur)) sel.value = cur;
    }

    if (s.summary) {
        const c = s.summary.counts ?? {};
        App.$('#summaryBox').innerHTML = `
            <div class="alert alert-info">${App.escapeHtml(s.summary.disclaimer)}</div>
            <div class="grid grid-4">
                <div class="stat"><div class="stat-label">Fakta</div><div class="stat-value">${c.facts ?? 0}</div></div>
                <div class="stat"><div class="stat-label">Anggaran</div><div class="stat-value">${c.estimates ?? 0}</div></div>
                <div class="stat"><div class="stat-label">Andaian</div><div class="stat-value">${c.assumptions ?? 0}</div></div>
                <div class="stat"><div class="stat-label">Cadangan</div><div class="stat-value">${c.recommendations ?? 0}</div></div>
            </div>
        `;
        App.$('#decideRow').hidden = s.status !== 'awaiting_owner_decision';
    }

    if (s.status === 'decided') {
        App.$('#decideRow').hidden = true;
        App.$('#summaryBox').innerHTML += `<div class="alert alert-success mt-4">Keputusan: <strong>${s.decision}</strong></div>`;
    }

    if (agents.length) refreshFindings();
}

loadSessions();
</script>
@endpush
