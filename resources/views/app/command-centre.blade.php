@extends('layouts.app')

@section('title', 'Command Centre')

@section('content')
<style>
    .cc-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 1024px) { .cc-grid { grid-template-columns: 1fr; } }
    .cc-bar { height: 9px; border-radius: var(--radius-full); background: var(--surface-3); overflow: hidden; }
    .cc-bar > i { display: block; height: 100%; border-radius: var(--radius-full); background: linear-gradient(90deg, var(--brand-400), var(--brand-700)); }
    .cc-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 8px 0; font-size: 13px; }
    .cc-ask { display: flex; gap: 10px; }
    .cc-answer { background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 12px 14px; font-size: 13px; white-space: pre-wrap; }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">Command Centre</h1>
        <p class="page-desc" id="ccGreeting">Keadaan perniagaan dalam satu pandangan.</p>
    </div>
    <div class="row">
        <span class="badge badge-brand" id="ccAutonomyBadge">Autonomi: —</span>
        <button class="btn btn-sm" id="ccGenerateBtn" onclick="generateBriefing()">Jana Briefing</button>
        <button class="btn btn-sm" onclick="loadCommandCentre()">Muat Semula</button>
    </div>
</div>

<div id="ccError" class="alert alert-warning" hidden></div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Deal Baharu</div><div class="stat-value" id="ccDeals">—</div><div class="stat-delta" id="ccDealsSub">—</div></div>
    <div class="stat"><div class="stat-label">Nilai Pipeline</div><div class="stat-value" id="ccPipelineValue">—</div><div class="stat-delta" id="ccPipelineSub">—</div></div>
    <div class="stat"><div class="stat-label">Invois Baharu</div><div class="stat-value" id="ccInvoices">—</div><div class="stat-delta" id="ccInvoicesSub">—</div></div>
    <div class="stat"><div class="stat-label">Belum Dijelaskan</div><div class="stat-value" id="ccOutstanding">—</div><div class="stat-delta" id="ccOutstandingSub">—</div></div>
</div>

<div class="card mb-5">
    <div class="card-header">
        <div>
            <h2 class="card-title">Ringkasan Eksekutif</h2>
            <p class="card-subtitle">Dijana automatik daripada jualan, pipeline, prospek dan risiko</p>
        </div>
        <span class="badge badge-neutral" id="ccBriefingType">harian</span>
    </div>
    <div class="cc-answer" id="ccRingkasan">Memuat…</div>
</div>

<div class="cc-grid mb-5">
    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Risiko Perlu Perhatian</h2><p class="card-subtitle">SLA, bounce blast dan stok kritikal</p></div>
            <span class="badge badge-neutral" id="ccRisikoCount">0</span>
        </div>
        <div id="ccRisiko"><div class="skeleton" style="height:56px"></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Prospek Panas</h2><p class="card-subtitle">Skor tertinggi yang perlu tindakan</p></div>
        </div>
        <div class="table-wrap" style="max-height:280px">
            <table class="table">
                <thead><tr><th>Nama</th><th>Syarikat</th><th class="table-num">Skor</th><th class="table-num">Hari</th></tr></thead>
                <tbody id="ccProspek"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
            </table>
        </div>
    </div>
</div>

<div class="cc-grid mb-5">
    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Pipeline Mengikut Peringkat</h2><p class="card-subtitle">Nilai peluang dalam CRM</p></div>
        </div>
        <div id="ccPipeline"><div class="skeleton" style="height:90px"></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Kempen Blast Aktif</h2><p class="card-subtitle">Sedang berjalan / berjadual / dijeda</p></div>
        </div>
        <div id="ccBlast"><div class="skeleton" style="height:90px"></div></div>
    </div>
</div>

<div class="card mb-5">
    <div class="card-header">
        <div>
            <h2 class="card-title">Tanya Pembantu Eksekutif</h2>
            <p class="card-subtitle" id="ccAskHint">Tahap autonomi semasa menentukan sama ada tindakan perlu kelulusan.</p>
        </div>
    </div>
    <div class="cc-ask mb-4">
        <input class="input" id="ccQuestion" type="text" placeholder="cth. Berapa nilai pipeline bulan ini?" aria-label="Soalan untuk AI eksekutif" onkeydown="if(event.key === 'Enter') askExecutive()">
        <button class="btn btn-primary" id="ccAskBtn" onclick="askExecutive()">Tanya</button>
    </div>
    <div id="ccAskResult" hidden>
        <div class="cc-answer" id="ccAnswer"></div>
        <div id="ccActions" class="mt-4"></div>
    </div>
</div>

<div class="cc-grid">
    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Briefing Terkini</h2><p class="card-subtitle">Sejarah briefing yang dijana</p></div>
            <button class="btn btn-sm" onclick="loadBriefings()">Segar</button>
        </div>
        <div id="ccBriefings"><div class="skeleton" style="height:70px"></div></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Status Sistem</h2><p class="card-subtitle">Komponen AI dan autonomi</p></div>
        </div>
        <div id="ccSistem"><div class="skeleton" style="height:70px"></div></div>
    </div>
</div>
@endsection


@push('scripts')
<script>
const CC = { briefing: null };

function greeting() {
    const h = new Date().getHours();
    if (h < 12) return 'Selamat pagi';
    if (h < 15) return 'Selamat tengah hari';
    if (h < 19) return 'Selamat petang';
    return 'Selamat malam';
}

function todayLabel() {
    return new Intl.DateTimeFormat('ms-MY', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' }).format(new Date());
}

function showCcError(msg) {
    const el = App.$('#ccError');
    el.textContent = msg + ' — pastikan tenant aktif dan kebenaran executive_ai.view dimiliki.';
    el.hidden = false;
}

async function loadCommandCentre() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    App.$('#ccGreeting').textContent = greeting() + ' · ' + todayLabel();
    try {
        const r = await App.api.get('/api/v1/executive-ai/command-centre');
        CC.briefing = r.data ?? {};
        renderCommandCentre(CC.briefing, r.autonomi ?? null);
    } catch (e) {
        showCcError(e.message);
    }
    await loadBriefings();
}

function renderCommandCentre(d, autonomi) {
    const kpi = d.kpi ?? {};
    App.$('#ccDeals').textContent = App.number(kpi.jumlah_deal ?? 0);
    App.$('#ccDealsSub').textContent = 'Peluang dicipta dalam tempoh';
    App.$('#ccPipelineValue').textContent = App.rupiah(kpi.nilai_pipeline ?? 0);
    App.$('#ccPipelineSub').textContent = 'Jumlah nilai dianggarkan';
    App.$('#ccInvoices').textContent = App.number(kpi.jumlah_invois ?? 0);
    App.$('#ccInvoicesSub').textContent = 'Invois dicipta dalam tempoh';
    App.$('#ccOutstanding').textContent = App.number(kpi.bayaran_belum ?? 0);
    App.$('#ccOutstandingSub').textContent = 'Invois belum dijelaskan';

    App.$('#ccRingkasan').textContent = d.ringkasan || 'Tiada ringkasan untuk tempoh ini.';

    if (autonomi) {
        App.$('#ccAutonomyBadge').textContent = 'Autonomi: ' + (autonomi.aras ?? '—');
        App.$('#ccAskHint').textContent = autonomi.peratus ?? '';
    }

    const risiko = d.risiko ?? [];
    App.$('#ccRisikoCount').textContent = risiko.length;
    App.$('#ccRisiko').innerHTML = risiko.length ? risiko.map(x => `
        <div class="cc-row">
            <div>
                <div class="text-sm">${App.escapeHtml(x.label ?? x.jenis ?? 'Risiko')}</div>
                <div class="text-xs text-muted">${App.escapeHtml(x.jenis ?? '')}</div>
            </div>
            <div class="row" style="gap:8px">
                <span class="badge badge-${x.tahap === 'tinggi' ? 'danger' : x.tahap === 'sederhana' ? 'warning' : 'neutral'}">${App.escapeHtml(x.tahap ?? '—')}</span>
                <strong>${App.number(x.jumlah ?? 0)}</strong>
            </div>
        </div>`).join('') : '<div class="empty"><div class="empty-title">Tiada risiko</div><div class="empty-text">Semua penunjuk dalam lingkungan normal.</div></div>';

    const prospek = d.prospect_panas ?? [];
    App.$('#ccProspek').innerHTML = prospek.length ? prospek.map(p => `
        <tr>
            <td class="text-sm">${App.escapeHtml(p.nama ?? '—')}</td>
            <td class="text-xs">${App.escapeHtml(p.syarikat ?? '—')}</td>
            <td class="table-num"><span class="badge badge-${(p.skor ?? 0) >= 85 ? 'danger' : 'brand'}">${App.number(p.skor ?? 0)}</span></td>
            <td class="table-num text-xs">${p.dikunci ?? '—'}</td>
        </tr>`).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada prospek panas.</div></div></td></tr>';

    const pipe = d.pipeline ?? [];
    const maxNilai = Math.max(1, ...pipe.map(p => Number(p.nilai) || 0));
    App.$('#ccPipeline').innerHTML = pipe.length ? pipe.map(p => `
        <div class="mb-4">
            <div class="cc-row" style="padding:0 0 4px">
                <span class="text-sm">${App.escapeHtml(p.stage ?? '—')}</span>
                <span class="text-xs text-muted">${App.number(p.jumlah ?? 0)} deal · ${App.rupiah(p.nilai ?? 0)}</span>
            </div>
            <div class="cc-bar"><i style="width:${Math.round(((Number(p.nilai) || 0) / maxNilai) * 100)}%"></i></div>
        </div>`).join('') : '<div class="empty"><div class="empty-text">Tiada peluang dalam pipeline.</div></div>';

    const blast = d.blast ?? [];
    App.$('#ccBlast').innerHTML = blast.length ? blast.map(b => `
        <div class="cc-row">
            <div>
                <div class="text-sm">${App.escapeHtml(b.name ?? '—')}</div>
                <div class="text-xs text-muted">${App.escapeHtml(b.channel ?? '')}${b.scheduled_at ? ' · ' + App.date(b.scheduled_at) : ''}</div>
            </div>
            <span class="badge badge-${b.status === 'running' ? 'success' : b.status === 'paused' ? 'warning' : 'info'}">${App.escapeHtml(b.status ?? '—')}</span>
        </div>`).join('') : '<div class="empty"><div class="empty-text">Tiada kempen aktif.</div></div>';

    const s = d.sistem ?? {};
    App.$('#ccSistem').innerHTML = `
        <div class="cc-row"><span class="text-muted">AI Gateway langsung</span><span class="badge badge-${s.ai_gateway_live ? 'success' : 'neutral'}">${s.ai_gateway_live ? 'Aktif' : 'Mod simulasi'}</span></div>
        <div class="cc-row"><span class="text-muted">Tahap autonomi lalai</span><strong>${App.number(s.autonomi_aras ?? 0)}</strong></div>
        <div class="cc-row"><span class="text-muted">Masa briefing</span><strong>${App.escapeHtml(s.masa_briefian ?? '—')}</strong></div>`;
}

// ---------- Tanya pembantu eksekutif ----------
async function askExecutive() {
    const q = App.$('#ccQuestion').value.trim();
    if (!q) { App.toast('Tulis soalan dahulu.', 'error'); return; }
    await App.withLoading(App.$('#ccAskBtn'), async () => {
        try {
            const r = await App.api.post('/api/v1/executive-ai/ask', { question: q });
            const d = r.data ?? {};
            App.$('#ccAskResult').hidden = false;
            App.$('#ccAnswer').textContent = d.jawapan || 'Tiada jawapan teks dijana. Lihat cadangan tindakan di bawah.';

            const acts = d.tindakan_dicadangkan ?? [];
            App.$('#ccActions').innerHTML = acts.length ? acts.map(a => `
                <div class="alert alert-info" style="margin:0;align-items:center">
                    <span>Cadangan: <strong>${App.escapeHtml(a.label ?? a.action ?? '')}</strong></span>
                    <span class="badge badge-${d.perlu_kelulusan ? 'warning' : 'success'}" style="margin-left:auto">${d.perlu_kelulusan ? 'Perlu kelulusan' : 'Boleh laksana'}</span>
                </div>`).join('') : '';
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

// ---------- Briefing ----------
async function loadBriefings() {
    try {
        const r = await App.api.get('/api/v1/executive-ai/briefings?limit=8');
        const list = Array.isArray(r.data) ? r.data : [];
        App.$('#ccBriefings').innerHTML = list.length ? list.map(b => `
            <div class="cc-row">
                <div>
                    <div class="text-sm">${App.escapeHtml(b.type ?? '—')}</div>
                    <div class="text-xs text-muted">${App.date(b.generated_at)}</div>
                </div>
                <div class="row" style="gap:6px">
                    ${b.read_at
                        ? '<span class="badge badge-neutral">Dibaca</span>'
                        : `<button class="btn btn-sm" onclick="markRead(${b.id})">Tanda dibaca</button>`}
                </div>
            </div>`).join('') : '<div class="empty"><div class="empty-text">Belum ada briefing.</div></div>';
    } catch { /* abaikan */ }
}

async function markRead(id) {
    try { await App.api.post(`/api/v1/executive-ai/briefings/${id}/read`); loadBriefings(); }
    catch (e) { App.toast(e.message, 'error'); }
}

async function generateBriefing() {
    await App.withLoading(App.$('#ccGenerateBtn'), async () => {
        try {
            await App.api.post('/api/v1/executive-ai/briefings/generate', { type: 'daily' });
            App.toast('Briefing baharu dijana', 'success');
            loadCommandCentre();
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

loadCommandCentre();
</script>
@endpush

