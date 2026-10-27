@extends('layouts.app')

@section('title', 'AI Gateway')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">AI Gateway</h1>
        <p class="page-desc">Katalog model, wallet kredit dan metering penggunaan.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat">
        <div class="stat-label">Baki Efektif</div>
        <div class="stat-value" id="sBalance">—</div>
        <div class="stat-delta" id="sBalanceSub">—</div>
    </div>
    <div class="stat">
        <div class="stat-label">Penggunaan Kuota</div>
        <div class="stat-value" id="sUsage">—</div>
        <div class="stat-delta" id="sUsageSub">—</div>
    </div>
    <div class="stat">
        <div class="stat-label">Jumlah Permintaan</div>
        <div class="stat-value" id="sRequests">—</div>
        <div class="stat-delta" id="sRequestsSub">—</div>
    </div>
    <div class="stat">
        <div class="stat-label">Kredit Digunakan</div>
        <div class="stat-value" id="sCredits">—</div>
        <div class="stat-delta" id="sCreditsSub">—</div>
    </div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="chat">Chat</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="models">Model</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="wallet">Wallet</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="usage">Penggunaan</button>
</div>

<section data-panel="chat">
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Uji Chat</h2>
                    <p class="card-subtitle">Prompt di-redaksi PII sebelum dihantar</p>
                </div>
            </div>
            <div class="field">
                <label class="label" for="taskType">Jenis Tugas</label>
                <select class="select" id="taskType">
                    <option value="quick">Pantas — GLM Flash</option>
                    <option value="reasoning">Penaakulan — GPT-6 Astra</option>
                    <option value="deep_reasoning">Mendalam — Claude Opus</option>
                    <option value="coding">Coding — DeepSeek V4</option>
                    <option value="creative">Kreatif — Muse Wonder</option>
                    <option value="long_context">Konteks Panjang — Kimi K3</option>
                    <option value="multilingual">Multibahasa — Qwen Max</option>
                </select>
            </div>
            <div class="field">
                <label class="label" for="chatMsg">Mesej</label>
                <textarea class="textarea" id="chatMsg" rows="3">Ringkaskan prestasi jualan bulan ini.</textarea>
            </div>
            <button class="btn btn-primary btn-block" id="chatBtn" onclick="sendChat()">Hantar</button>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Respons</h2>
                    <p class="card-subtitle" id="chatMeta">—</p>
                </div>
            </div>
            <pre id="chatOut" class="mono" style="max-height:320px;overflow:auto;white-space:pre-wrap">Menunggu…</pre>
        </div>
    </div>
</section>

<section data-panel="models" hidden>
    <div class="grid grid-3" id="modelGrid">
        <div class="skeleton" style="height:100px"></div>
    </div>
</section>

<section data-panel="wallet" hidden>
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Tambah Kredit</h2></div>
            </div>
            <div class="field">
                <label class="label" for="topUpAmount">Jumlah (RM)</label>
                <input class="input" type="number" id="topUpAmount" value="500" min="1">
            </div>
            <button class="btn btn-primary btn-block" onclick="topUp()">Top-up</button>
            <div class="field-hint mt-3">Kredit top-up tamat tempoh selepas 30 hari.</div>
        </div>

        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Ledger</h2></div>
            </div>
            <div class="stack" id="ledgerList">
                <div class="skeleton" style="height:40px"></div>
            </div>
        </div>
    </div>
</section>

<section data-panel="usage" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Model</th><th>Tier</th><th class="table-num">Permintaan</th><th class="table-num">Token</th><th class="table-num">Kredit</th></tr>
            </thead>
            <tbody id="usageTable">
                <tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr>
            </tbody>
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

    await Promise.all([loadWallet(), loadModels(), loadUsage(), loadLedger()]);
}

async function loadWallet() {
    try {
        const r = await App.api.get('/api/v1/ai-gateway/wallet');
        const w = r.data;
        App.$('#sBalance').textContent = App.number(w.effective_balance);
        App.$('#sBalanceSub').textContent = `Top-up: ${App.rupiah(w.balance)}`;
        App.$('#sUsage').textContent = w.usage_percent + '%';
        App.$('#sUsageSub').textContent = `Kuota: ${App.number(w.period_quota)}`;

        if ((r.alerts ?? []).length) {
            App.toast(r.alerts[0].message, 'error', 6000);
        }
    } catch (e) { App.toast('Gagal memuat wallet: ' + e.message, 'error'); }

    try {
        const u = await App.api.get('/api/v1/ai-gateway/usage/summary');
        const uu = u.data.usage;
        App.$('#sRequests').textContent = App.number(uu.total_requests);
        App.$('#sRequestsSub').textContent = `${uu.successful} berjaya · ${uu.failed} gagal`;
        App.$('#sCredits').textContent = App.number(uu.total_credits);
        App.$('#sCreditsSub').textContent = `${App.number(uu.total_tokens)} token`;
    } catch { /* abaikan */ }
}

async function loadModels() {
    try {
        const r = await App.api.get('/api/v1/ai-gateway/models');
        const rows = r.data ?? [];
        App.$('#modelGrid').innerHTML = rows.map(m => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(m.display_name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(m.best_for ?? '')}</p>
                    </div>
                    <span class="badge badge-${m.tier === 'premium' || m.tier === 'ultra' ? 'brand' : m.tier === 'flash' ? 'neutral' : 'info'}">${m.tier}</span>
                </div>
                <div class="row text-xs text-muted">
                    <span>Konteks: ${App.number(m.max_context ?? 0)}</span>
                </div>
            </div>
        `).join('') || '<div class="card"><div class="empty"><div class="empty-text">Tiada model. Jalankan seeder.</div></div></div>';
    } catch { /* abaikan */ }
}

async function loadUsage() {
    try {
        const r = await App.api.get('/api/v1/ai-gateway/admin/usage/events?per_page=30');
        const rows = r.data?.data ?? [];
        App.$('#usageTable').innerHTML = rows.length ? rows.map(e => `
            <tr>
                <td class="text-sm">${App.escapeHtml(e.model_display_name)}</td>
                <td><span class="badge badge-neutral">${e.tier}</span></td>
                <td class="table-num">${(e.input_tokens ?? 0) + (e.output_tokens ?? 0)}</td>
                <td class="table-num">${e.credits_charged ?? 0}</td>
                <td><span class="badge badge-${e.status === 'success' ? 'success' : e.status === 'blocked' ? 'warning' : 'danger'}">${e.status}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada penggunaan.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function loadLedger() {
    try {
        const r = await App.api.get('/api/v1/ai-gateway/wallet/ledger?limit=15');
        const rows = r.data ?? [];
        App.$('#ledgerList').innerHTML = rows.length ? rows.map(l => `
            <div class="row-between" style="padding:8px 0;border-bottom:1px solid var(--border)">
                <div>
                    <div class="text-sm">${App.escapeHtml(l.description ?? l.type)}</div>
                    <div class="text-xs text-muted">${App.date(l.created_at)}</div>
                </div>
                <span class="badge badge-${Number(l.amount) >= 0 ? 'success' : 'danger'}">${Number(l.amount) >= 0 ? '+' : ''}${App.number(l.amount)}</span>
            </div>
        `).join('') : '<div class="empty"><div class="empty-text">Tiada transaksi.</div></div>';
    } catch { /* abaikan */ }
}

async function sendChat() {
    const msg = App.$('#chatMsg').value.trim();
    if (!msg) { App.toast('Mesej diperlukan', 'error'); return; }

    await App.withLoading(App.$('#chatBtn'), async () => {
        try {
            const r = await App.api.post('/api/v1/ai-gateway/chat', {
                messages: [{ role: 'user', content: msg }],
                task_type: App.$('#taskType').value,
                max_output_tokens: 500,
            });
            App.$('#chatMeta').textContent = `${r.model} · ${r.usage?.total_tokens ?? 0} token · ${r.usage?.credits_charged ?? 0} kredit`;
            App.json('#chatOut', r);
            loadWallet();
        } catch (e) {
            App.json('#chatOut', e.data ?? { error: e.message });
            App.toast(e.message, 'error');
        }
    });
}

async function topUp() {
    const amount = Number(App.$('#topUpAmount').value);
    if (!(amount > 0)) { App.toast('Jumlah tidak sah', 'error'); return; }

    try {
        await App.api.post('/api/v1/ai-gateway/wallet/top-up', { amount, description: 'Top-up melalui papan pemuka' });
        App.toast(`Kredit ditambah: ${App.rupiah(amount)}`, 'success');
        loadWallet(); loadLedger();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
