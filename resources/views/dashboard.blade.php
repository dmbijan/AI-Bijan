<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI Bijan — Ujian Modul AI</title>
    <style>
        :root {
            --bg: #0f0f10;
            --panel: #18181b;
            --border: #2a2a2e;
            --text: #e7e7ea;
            --muted: #9a9aa2;
            --accent: #d97706;
            --ok: #22c55e;
            --err: #ef4444;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.55;
        }
        header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
            background: var(--panel);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .logo {
            width: 34px; height: 34px;
            border-radius: 9px;
            background: linear-gradient(135deg, var(--accent), #b45309);
            display: grid; place-items: center;
            font-weight: 700; font-size: 15px;
        }
        h1 { font-size: 16px; margin: 0; font-weight: 600; }
        .sub { font-size: 12px; color: var(--muted); }
        main { max-width: 1100px; margin: 0 auto; padding: 24px; }
        .card {
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 16px;
        }
        .card h2 { margin: 0 0 4px; font-size: 14px; font-weight: 600; }
        .card p.hint { margin: 0 0 14px; font-size: 12px; color: var(--muted); }
        .grid { display: grid; gap: 12px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }
        label { display: block; font-size: 11px; color: var(--muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: .04em; }
        input, select, textarea {
            width: 100%;
            padding: 9px 11px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: #121214;
            color: var(--text);
            font: inherit;
            font-size: 13px;
        }
        textarea { resize: vertical; min-height: 60px; }
        button {
            padding: 9px 16px;
            border-radius: 7px;
            border: 1px solid var(--border);
            background: #27272a;
            color: var(--text);
            font: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
        }
        button:hover { background: #323236; }
        button.primary { background: var(--accent); border-color: var(--accent); color: #1a1206; }
        button.primary:hover { background: #ea860b; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }
        pre {
            background: #0a0a0b;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px;
            font-size: 11.5px;
            overflow: auto;
            max-height: 340px;
            margin: 12px 0 0;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge.ok { background: rgba(34,197,94,.15); color: var(--ok); }
        .badge.err { background: rgba(239,68,68,.15); color: var(--err); }
        .badge.idle { background: rgba(154,154,162,.15); color: var(--muted); }
    </style>
</head>
<body>
<header>
    <div class="logo">B</div>
    <div>
        <h1>AI Bijan — Ujian Modul AI</h1>
        <div class="sub">Boardroom · AI Agents · Flow · Personal AI · Gateway</div>
    </div>
</header>

<main>
    <div class="card">
        <h2>1. Log Masuk</h2>
        <p class="hint">Daftar dahulu jika belum ada akaun.</p>
        <div class="grid">
            <div><label>E-mel</label><input id="email" value="ujian@bijan.test"></div>
            <div><label>Kata Laluan</label><input id="password" type="password" value="RahsiaKuat123"></div>
        </div>
        <div class="actions">
            <button class="primary" onclick="login()">Log Masuk</button>
            <button onclick="register()">Daftar Syarikat Baharu</button>
            <span id="authBadge" class="badge idle">Belum log masuk</span>
        </div>
        <pre id="authOut">—</pre>
    </div>

    <div class="card">
        <h2>2. Panggil Ejen AI</h2>
        <p class="hint">Ejen menerima arahan, dirouting ke model, dan jejak audit direkod.</p>
        <div class="grid">
            <div>
                <label>Ejen</label>
                <select id="agentKey">
                    <option value="ceo">CEO — Chief of Staff</option>
                    <option value="cfo" selected>CFO — Kewangan</option>
                    <option value="cmo">CMO — Pemasaran</option>
                    <option value="coo">COO — Operasi</option>
                    <option value="ciso">CSO — Jualan</option>
                    <option value="chro">CHRO — HR</option>
                    <option value="support">Support</option>
                    <option value="analyst">Analyst</option>
                </select>
            </div>
            <div><label>Objektif</label><input id="objective" value="Analisis aliran tunai bulan ini"></div>
        </div>
        <div class="actions"><button class="primary" onclick="invokeAgent()">Panggil Ejen</button></div>
        <pre id="agentOut">—</pre>
    </div>

    <div class="card">
        <h2>3. Sesi Boardroom</h2>
        <p class="hint">Aliran 7 langkah: persoalan → pilih ejen → dapatan → konflik → ringkasan → keputusan.</p>
        <div class="grid">
            <div><label>Persoalan</label><input id="question" value="Adakah kita patut tambah staf jualan bulan ini?"></div>
        </div>
        <div class="actions">
            <button class="primary" onclick="openSession()">1. Buka Sesi</button>
            <button onclick="selectAgents()">2. Pilih Ejen</button>
            <button onclick="addFinding()">4. Rekod Dapatan</button>
            <button onclick="prepareSummary()">6. Ringkasan</button>
            <button onclick="decide()">7. Keputusan</button>
        </div>
        <pre id="boardOut">—</pre>
    </div>

    <div class="card">
        <h2>4. Flow Automation</h2>
        <p class="hint">Uji idempotency dan kill switch.</p>
        <div class="actions">
            <button class="primary" onclick="createWorkflow()">Cipta Workflow</button>
            <button onclick="dryRun()">Dry Run</button>
            <button onclick="activate()">Aktifkan</button>
            <button onclick="trigger()">Trigger</button>
            <button onclick="killSwitch()">Kill Switch</button>
        </div>
        <pre id="flowOut">—</pre>
    </div>

    <div class="card">
        <h2>5. Wallet &amp; Penggunaan AI</h2>
        <div class="actions">
            <button class="primary" onclick="getWallet()">Lihat Wallet</button>
            <button onclick="topUp()">Top-up RM500</button>
            <button onclick="usage()">Ringkasan Penggunaan</button>
        </div>
        <pre id="walletOut">—</pre>
    </div>
</main>

<script>
const state = { tenant: null, sessionId: null, workflowId: null };

function out(id, data, ok) {
    const el = document.getElementById(id);
    el.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
}

function xsrf() {
    const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
    return m ? decodeURIComponent(m[1]) : '';
}

function headers() {
    const h = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': xsrf(),
    };
    if (state.tenant) h['X-Tenant'] = state.tenant;
    return h;
}

async function api(method, path, body) {
    const res = await fetch(path, {
        method,
        credentials: 'same-origin',
        headers: headers(),
        body: body ? JSON.stringify(body) : undefined,
    });
    let data;
    try { data = await res.json(); } catch { data = { status: res.status }; }
    return { ok: res.ok, status: res.status, data };
}

async function csrf() {
    await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
}

function setBadge(ok, text) {
    const b = document.getElementById('authBadge');
    b.className = 'badge ' + (ok ? 'ok' : 'err');
    b.textContent = text;
}

async function login() {
    await csrf();
    const r = await api('POST', '/api/v1/auth/login', {
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
    });
    if (r.ok) {
        await csrf();
        const me = await api('GET', '/api/v1/auth/me');
        const tenants = me.data?.tenants ?? [];
        if (tenants.length) state.tenant = tenants[0].ulid;
        setBadge(true, 'Log masuk: ' + (me.data?.user?.email ?? 'ok'));
        out('authOut', { tenant: state.tenant, me: me.data });
    } else {
        setBadge(false, 'Gagal log masuk');
        out('authOut', r.data);
    }
}

async function register() {
    await csrf();
    const r = await api('POST', '/api/v1/auth/register', {
        name: 'Pemilik Ujian',
        email: document.getElementById('email').value,
        password: document.getElementById('password').value,
        company: 'Syarikat ' + Date.now(),
    });
    if (r.ok) {
        state.tenant = r.data.tenants?.[0]?.ulid ?? null;
        setBadge(true, 'Akaun dicipta');
        out('authOut', r.data);
    } else {
        setBadge(false, 'Pendaftaran gagal');
        out('authOut', r.data);
    }
}

async function invokeAgent() {
    const r = await api('POST', '/api/v1/ai-agents/invoke', {
        agent_key: document.getElementById('agentKey').value,
        objective: document.getElementById('objective').value,
    });
    out('agentOut', r.data);
}

async function openSession() {
    const r = await api('POST', '/api/v1/boardroom/sessions', {
        question: document.getElementById('question').value,
    });
    if (r.ok) state.sessionId = r.data.data.id;
    out('boardOut', r.data);
}

async function selectAgents() {
    const r = await api('POST', `/api/v1/boardroom/sessions/${state.sessionId}/select-agents`, {});
    out('boardOut', r.data);
}

async function addFinding() {
    const agents = (await api('GET', `/api/v1/boardroom/sessions/${state.sessionId}`)).data?.data?.agents_involved ?? [];
    const r = await api('POST', `/api/v1/boardroom/sessions/${state.sessionId}/findings`, {
        agent_key: agents[0] ?? 'cfo',
        finding_type: 'fact',
        title: 'Margin kasar bulan lepas',
        content: 'Margin 32%, melebihi sasaran 30%.',
        source_reference: 'finance:report:2026-09',
        confidence_score: 0.95,
    });
    out('boardOut', r.data);
}

async function prepareSummary() {
    const r = await api('POST', `/api/v1/boardroom/sessions/${state.sessionId}/prepare-summary`);
    out('boardOut', r.data);
}

async function decide() {
    const r = await api('POST', `/api/v1/boardroom/sessions/${state.sessionId}/decide`, {
        decision: 'approve',
        reason: 'Margin kukuh, mampu tampung kos.',
    });
    out('boardOut', r.data);
}

async function createWorkflow() {
    const r = await api('POST', '/api/v1/flows/workflows', {
        name: 'Amaran Stok Rendah',
        trigger_type: 'manual',
        steps: [{ type: 'send_notification', config: { channel: 'in_app', recipient: 'owner', message: 'Stok @{{item}} rendah' } }],
    });
    if (r.ok) state.workflowId = r.data.data.id;
    out('flowOut', r.data);
}

async function dryRun() {
    const r = await api('POST', `/api/v1/flows/workflows/${state.workflowId}/dry-run`, { payload: { item: 'Gula' } });
    out('flowOut', r.data);
}

async function activate() {
    const r = await api('POST', `/api/v1/flows/workflows/${state.workflowId}/activate`);
    out('flowOut', r.data);
}

async function trigger() {
    const r = await api('POST', `/api/v1/flows/workflows/${state.workflowId}/trigger`, {
        payload: { item: 'Gula' },
        idempotency_key: 'demo-' + (state.workflowId ?? 'x'),
    });
    out('flowOut', r.data);
}

async function killSwitch() {
    const r = await api('POST', `/api/v1/flows/workflows/${state.workflowId}/kill-switch`, { enabled: true });
    out('flowOut', r.data);
}

async function getWallet() {
    const r = await api('GET', '/api/v1/ai-gateway/wallet');
    out('walletOut', r.data);
}

async function topUp() {
    const r = await api('POST', '/api/v1/ai-gateway/wallet/top-up', { amount: 500 });
    out('walletOut', r.data);
}

async function usage() {
    const r = await api('GET', '/api/v1/ai-gateway/usage/summary');
    out('walletOut', r.data);
}
</script>
</body>
</html>
