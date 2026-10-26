@extends('layouts.app')

@section('title', 'Ejen AI')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Ejen AI</h1>
        <p class="page-desc">11 ejen khusus dengan tools, had bajet dan jejak audit.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="invoke">Panggil Ejen</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="list">Senarai Ejen</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="stats">Statistik</button>
</div>

<section data-panel="invoke">
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Panggil Ejen</h2>
                    <p class="card-subtitle">Arahan dihantar melalui AI Gateway</p>
                </div>
            </div>

            <div class="field">
                <label class="label" for="agentKey">Ejen</label>
                <select class="select" id="agentKey"></select>
            </div>
            <div class="field">
                <label class="label" for="objective">Objektif</label>
                <textarea class="textarea" id="objective" rows="3">Analisis aliran tunai bulan ini dan cadangkan tindakan.</textarea>
            </div>
            <button class="btn btn-primary btn-block" id="invokeBtn" onclick="invokeAgent()">Panggil Ejen</button>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Respons</h2>
                    <p class="card-subtitle" id="respMeta">—</p>
                </div>
            </div>
            <pre id="respOut" class="mono" style="max-height:340px;overflow:auto;white-space:pre-wrap">Menunggu…</pre>
        </div>
    </div>
</section>

<section data-panel="list" hidden>
    <div class="grid grid-2" id="agentGrid">
        <div class="skeleton" style="height:120px"></div>
        <div class="skeleton" style="height:120px"></div>
    </div>
</section>

<section data-panel="stats" hidden>
    <div class="grid grid-4" id="statGrid"></div>
</section>
@endsection

@push('scripts')
<script>
const agentState = { agents: [] };

document.querySelectorAll('[data-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', 'false'));
        tab.setAttribute('aria-selected', 'true');
        document.querySelectorAll('[data-panel]').forEach(p => {
            p.hidden = p.dataset.panel !== tab.dataset.tab;
        });
    });
});

async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }

    try {
        const res = await App.api.get('/api/v1/ai-agents/list');
        agentState.agents = Object.values(res.data ?? {});

        const sel = App.$('#agentKey');
        sel.innerHTML = agentState.agents.map(a =>
            `<option value="${a.key ?? a.id}">${App.escapeHtml(a.name)}</option>`
        ).join('');

        App.$('#agentGrid').innerHTML = agentState.agents.map(a => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(a.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(a.role ?? '')}</p>
                    </div>
                    <span class="badge badge-brand">${App.escapeHtml(a.key ?? '')}</span>
                </div>
                <p class="card-body text-sm">${App.escapeHtml(a.description ?? '')}</p>
                <div class="card-footer">
                    ${(a.tools ?? []).slice(0, 4).map(t =>
                        `<span class="badge badge-neutral mono">${App.escapeHtml(t)}</span>`
                    ).join('')}
                </div>
            </div>
        `).join('') || '<div class="empty"><div class="empty-title">Tiada ejen</div></div>';

        App.$('#statGrid').innerHTML = `
            <div class="stat"><div class="stat-label">Jumlah Ejen</div><div class="stat-value">${agentState.agents.length}</div></div>
            <div class="stat"><div class="stat-label">Aktif</div><div class="stat-value">${agentState.agents.filter(a => a.status !== 'disabled').length}</div></div>
        `;
    } catch (e) {
        App.toast('Gagal memuat ejen: ' + e.message, 'error');
    }
}

async function invokeAgent() {
    const btn = App.$('#invokeBtn');
    const key = App.$('#agentKey').value;
    const objective = App.$('#objective').value.trim();
    if (!objective) { App.toast('Objektif diperlukan', 'error'); return; }

    await App.withLoading(btn, async () => {
        try {
            const res = await App.api.post('/api/v1/ai-agents/invoke', {
                agent_key: key,
                objective,
            });
            App.$('#respMeta').textContent = `${res.model ?? '—'} · ${res.usage?.total_tokens ?? 0} token`;
            App.json('#respOut', res);
            App.toast('Ejen dijalankan', 'success');
        } catch (e) {
            App.json('#respOut', e.data ?? { error: e.message });
            App.toast(e.message, 'error');
        }
    });
}

loadAll();
</script>
@endpush
