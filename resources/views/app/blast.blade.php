@extends('layouts.app')

@section('title', 'Kempen Blast')

@section('content')
<style>
    .bl-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(340px, 1.1fr); gap: 16px; align-items: start; }
    @media (max-width: 1024px) { .bl-grid { grid-template-columns: 1fr; } }
    .bl-preview { background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--radius-md); padding: 14px; font-size: 13px; white-space: pre-wrap; min-height: 120px; }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">Kempen Blast</h1>
        <p class="page-desc">Cipta, pratonton, bina audiens, kelulusan dan hantar kempen e-mel / SMS / WhatsApp.</p>
    </div>
    <div class="row">
        <span class="badge badge-neutral" id="blQuota">Kuota: —</span>
        <button class="btn btn-sm" onclick="loadCampaigns(1)">Muat Semula</button>
    </div>
</div>

<div id="blError" class="alert alert-warning" hidden></div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Kempen</div><div class="stat-value" id="blTotal">—</div><div class="stat-delta" id="blPageSub">—</div></div>
    <div class="stat"><div class="stat-label">Draf</div><div class="stat-value" id="blDraft">—</div><div class="stat-delta">Belum diluluskan</div></div>
    <div class="stat"><div class="stat-label">Aktif</div><div class="stat-value" id="blActive">—</div><div class="stat-delta">Berjalan / berjadual</div></div>
    <div class="stat"><div class="stat-label">Dihantar Hari Ini</div><div class="stat-value" id="blSentToday">—</div><div class="stat-delta" id="blQuotaSub">—</div></div>
</div>

<div class="bl-grid">
    <div class="card">
        <div class="card-header">
            <div><h2 class="card-title">Kempen</h2><p class="card-subtitle">Klik untuk buka butiran & kelulusan</p></div>
        </div>
        <div class="row mb-4">
            <select class="select" id="blFilterStatus" aria-label="Tapis kempen mengikut status" style="width:auto" onchange="loadCampaigns(1)">
                <option value="">Semua status</option>
                <option value="draft">Draf</option>
                <option value="review">Semakan</option>
                <option value="approved">Diluluskan</option>
                <option value="scheduled">Berjadual</option>
                <option value="running">Berjalan</option>
                <option value="paused">Dijeda</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
            <select class="select" id="blFilterChannel" aria-label="Tapis kempen mengikut saluran" style="width:auto" onchange="loadCampaigns(1)">
                <option value="">Semua saluran</option>
                <option value="email">E-mel</option>
                <option value="sms">SMS</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="video">Video</option>
                <option value="multi">Berbilang</option>
            </select>
        </div>
        <div class="table-wrap" style="max-height:420px">
            <table class="table">
                <thead><tr><th>Nama</th><th>Saluran</th><th>Status</th><th>Dicipta</th><th></th></tr></thead>
                <tbody id="blTable"><tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat...</div></div></td></tr></tbody>
            </table>
        </div>
        <div class="row-between mt-4">
            <button class="btn btn-sm" id="blPrev" onclick="loadCampaigns(BL.page - 1)">← Sebelum</button>
            <span class="text-xs text-muted" id="blPageLabel">—</span>
            <button class="btn btn-sm" id="blNext" onclick="loadCampaigns(BL.page + 1)">Seterusnya →</button>
        </div>
    </div>

    <div>
        <div class="card mb-4">
            <div class="card-header">
                <div><h2 class="card-title">Kempen Baharu</h2><p class="card-subtitle">Mula sebagai draf</p></div>
            </div>
            <div class="field">
                <label class="label" for="blName">Nama Kempen</label>
                <input class="input" id="blName" placeholder="Promosi Oktober">
            </div>
            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0">
                    <label class="label" for="blChannel">Saluran</label>
                    <select class="select" id="blChannel">
                        <option value="email">E-mel</option>
                        <option value="sms">SMS</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="video">Video</option>
                        <option value="multi">Berbilang</option>
                    </select>
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="blSubject">Subjek (e-mel)</label>
                    <input class="input" id="blSubject" placeholder="Tawaran khas untuk @{{nama}}">
                </div>
            </div>
            <div class="field mt-3">
                <label class="label" for="blBody">Kandungan</label>
                <textarea class="textarea" id="blBody" rows="6" placeholder="Hai @{{nama}}, tawaran @{{tawaran}} untuk @{{syarikat}}."></textarea>
                <p class="field-hint" id="blTags">Tag tersedia dimuat semasa pratonton.</p>
            </div>
            <div class="card-footer">
                <button class="btn" id="blPreviewBtn" onclick="previewBody()">Pratonton</button>
                <button class="btn btn-primary" id="blCreateBtn" onclick="createCampaign()">Cipta Kempen</button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Pratonton</h2><p class="card-subtitle">Tag diganti dengan data contoh</p></div>
            </div>
            <div class="text-xs text-muted mb-4" id="blPreviewSubject">—</div>
            <div class="bl-preview" id="blPreviewBody">Tekan Pratonton untuk lihat hasil.</div>
        </div>
    </div>
</div>


<div class="dialog-backdrop" id="blDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="blDialogTitle" style="max-width:700px">
        <div class="dialog-header">
            <div>
                <h2 class="card-title" id="blDialogTitle">Butiran Kempen</h2>
                <p class="card-subtitle" id="blDialogSub">—</p>
            </div>
            <button class="btn btn-ghost btn-sm" onclick="closeBlastDialog()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <div id="blStats" class="grid grid-4 mb-4"></div>

            <div class="tabs">
                <button class="tab" aria-selected="true" data-btab="audience">Audiens</button>
                <button class="tab" aria-selected="false" data-btab="approve">Kelulusan</button>
                <button class="tab" aria-selected="false" data-btab="messages">Mesej</button>
            </div>

            <section data-bpanel="audience">
                <div class="grid grid-2" style="gap:12px">
                    <div class="field" style="margin:0">
                        <label class="label" for="auMinScore">Skor Minimum</label>
                        <input class="input" id="auMinScore" type="number" min="0" max="100" value="0">
                    </div>
                    <div class="field" style="margin:0">
                        <label class="label" for="auStatus">Status Prospek</label>
                        <select class="select" id="auStatus">
                            <option value="">Semua</option>
                            <option value="new">Baru</option>
                            <option value="enriched">Diperkaya</option>
                            <option value="scored">Diskor</option>
                            <option value="approved">Diluluskan</option>
                            <option value="contacted">Dihubungi</option>
                            <option value="qualified">Layak</option>
                        </select>
                    </div>
                </div>
                <p class="field-hint mt-2">Consent, penindihan dan had kadar disemak oleh pelayan — penerima tidak sah dilangkau automatik.</p>
                <button class="btn btn-primary" id="auBuildBtn" onclick="buildAudience()">Bina Audiens</button>
                <div id="auResult" class="mt-3" hidden></div>
            </section>

            <section data-bpanel="approve" hidden>
                <div class="alert alert-warning">
                    Penghantaran hanya dibenarkan selepas kelulusan. Mesej yang belum diluluskan tidak akan keluar.
                </div>
                <button class="btn btn-primary" id="apApproveBtn" onclick="approveCampaign()">Luluskan Kempen</button>
                <hr style="border:0;border-top:1px solid var(--border);margin:16px 0">
                <div class="row">
                    <button class="btn" id="apStartBtn" onclick="scheduleCampaign(true)">Hantar Sekarang</button>
                    <input class="input" id="apScheduleAt" type="datetime-local" aria-label="Tarikh dan masa jadual hantar" style="max-width:220px">
                    <button class="btn" id="apScheduleBtn" onclick="scheduleCampaign(false)">Jadualkan</button>
                </div>
                <div class="row mt-4">
                    <button class="btn btn-sm" onclick="controlCampaign('pause')">Jeda</button>
                    <button class="btn btn-sm" onclick="controlCampaign('resume')">Sambung</button>
                    <button class="btn btn-sm btn-danger" onclick="controlCampaign('cancel')">Batal</button>
                </div>
                <div id="apResult" class="mt-3" hidden></div>
            </section>

            <section data-bpanel="messages" hidden>
                <button class="btn btn-sm mb-4" onclick="loadMessages()">Muat Mesej</button>
                <div class="table-wrap" style="max-height:280px">
                    <table class="table">
                        <thead><tr><th>Penerima</th><th>Status</th><th>Dihantar</th></tr></thead>
                        <tbody id="msgBody"><tr><td colspan="3"><div class="empty"><div class="empty-text">Klik butang untuk muat.</div></div></td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
const BL = { page: 1, lastPage: 1, active: null, campaign: null };

function blError(msg) {
    const el = App.$('#blError');
    el.textContent = msg + ' — pastikan tenant aktif dan kebenaran blast.view dimiliki.';
    el.hidden = false;
}

const STATUS_BADGE = {
    draft: 'neutral', review: 'warning', approved: 'info',
    scheduled: 'info', running: 'success', paused: 'warning',
    completed: 'success', cancelled: 'danger',
};

function statusBadge(s) {
    return `<span class="badge badge-${STATUS_BADGE[s] ?? 'neutral'}">${App.escapeHtml(s ?? '—')}</span>`;
}

async function loadAllBlast() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await Promise.all([loadCampaigns(1), loadQuota()]);
}

async function loadQuota() {
    try {
        const r = await App.api.get('/api/v1/blast/quota');
        const d = r.data ?? {};
        App.$('#blQuota').textContent = 'Kuota: ' + App.number(d.remaining ?? 0) + ' / ' + App.number(d.daily_per_tenant ?? 0);
        App.$('#blSentToday').textContent = App.number(d.sent_today ?? 0);
        App.$('#blQuotaSub').textContent = 'Baki ' + App.number(d.remaining ?? 0) + ' · ' + (d.per_second ?? '—') + '/saat';
    } catch { /* abaikan */ }
}

async function loadCampaigns(page) {
    if (page < 1 || (BL.lastPage > 0 && page > BL.lastPage)) return;
    const p = new URLSearchParams();
    p.set('page', String(page));
    p.set('per_page', '25');
    const st = App.$('#blFilterStatus').value; if (st) p.set('status', st);
    const ch = App.$('#blFilterChannel').value; if (ch) p.set('channel', ch);

    try {
        const r = await App.api.get('/api/v1/blast/campaigns?' + p.toString());
        const list = Array.isArray(r.data) ? r.data : [];
        const meta = r.meta ?? {};
        BL.page = Number(meta.current_page ?? page) || 1;
        BL.lastPage = Number(meta.last_page ?? 1) || 1;

        App.$('#blTotal').textContent = App.number(meta.total ?? list.length);
        App.$('#blPageSub').textContent = 'Halaman ' + BL.page + ' / ' + BL.lastPage;
        App.$('#blPageLabel').textContent = 'Halaman ' + BL.page + ' / ' + BL.lastPage;
        App.$('#blPrev').disabled = BL.page <= 1;
        App.$('#blNext').disabled = BL.page >= BL.lastPage;

        const drafts = list.filter(x => x.status === 'draft' || x.status === 'review').length;
        const active = list.filter(x => ['running', 'scheduled', 'paused'].includes(x.status)).length;
        App.$('#blDraft').textContent = App.number(drafts);
        App.$('#blActive').textContent = App.number(active);

        App.$('#blTable').innerHTML = list.length ? list.map(c => `
            <tr>
                <td class="text-sm">${App.escapeHtml(c.name ?? '—')}<div class="text-xs text-muted">${App.escapeHtml(c.subject ?? '')}</div></td>
                <td class="text-xs">${App.escapeHtml(c.channel ?? '—')}</td>
                <td>${statusBadge(c.status)}</td>
                <td class="text-xs">${App.date(c.created_at)}</td>
                <td><button class="btn btn-sm" onclick="openCampaign(${c.id})">Buka</button></td>
            </tr>`).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada kempen</div><div class="empty-text">Cipta kempen pertama di panel kanan.</div></div></td></tr>';
    } catch (e) {
        App.$('#blTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

// ---------- Pratonton ----------
async function previewBody() {
    const body = App.$('#blBody').value.trim();
    if (!body) { App.toast('Kandungan kosong.', 'error'); return; }
    await App.withLoading(App.$('#blPreviewBtn'), async () => {
        try {
            const r = await App.api.post('/api/v1/blast/preview', {
                body,
                subject: App.$('#blSubject').value.trim() || null,
            });
            const d = r.data ?? {};
            App.$('#blPreviewSubject').textContent = d.subject || '(tanpa subjek)';
            App.$('#blPreviewBody').textContent = d.body || '';
            const tags = d.tags ?? [];
            App.$('#blTags').innerHTML = 'Tag: ' + tags.map(t => `<code class="mono bl-tag" onclick="insertTag('${App.escapeHtml(String(t))}')">${App.escapeHtml(String(t))}</code>`).join(' ');
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

function insertTag(tag) {
    const el = App.$('#blBody');
    el.value = el.value + ' ' + tag;
    el.focus();
}

async function createCampaign() {
    const name = App.$('#blName').value.trim();
    const body = App.$('#blBody').value.trim();
    if (!name) { App.toast('Nama kempen diperlukan.', 'error'); return; }
    if (!body) { App.toast('Kandungan diperlukan.', 'error'); return; }
    await App.withLoading(App.$('#blCreateBtn'), async () => {
        try {
            await App.api.post('/api/v1/blast/campaigns', {
                name,
                channel: App.$('#blChannel').value,
                subject: App.$('#blSubject').value.trim() || null,
                body,
            });
            App.toast('Kempen dicipta sebagai draf', 'success');
            App.$('#blName').value = '';
            App.$('#blSubject').value = '';
            App.$('#blBody').value = '';
            loadCampaigns(1);
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

// ---------- Butiran kempen ----------
document.querySelectorAll('[data-btab]').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-btab]').forEach(t => t.setAttribute('aria-selected', 'false'));
        tab.setAttribute('aria-selected', 'true');
        document.querySelectorAll('[data-bpanel]').forEach(p => p.hidden = p.dataset.bpanel !== tab.dataset.btab);
    });
});

async function openCampaign(id) {
    BL.active = id;
    BL.campaign = null;
    App.$('#blDialog').hidden = false;
    App.$('#blDialogTitle').textContent = 'Butiran Kempen';
    App.$('#blDialogSub').textContent = 'Memuat...';
    App.$('#blStats').innerHTML = '<div class="skeleton" style="height:56px"></div>';
    App.$('#auResult').hidden = true;
    App.$('#apResult').hidden = true;
    try {
        const r = await App.api.get(`/api/v1/blast/campaigns/${id}`);
        BL.campaign = r.data ?? {};
        App.$('#blDialogTitle').textContent = BL.campaign.name ?? 'Kempen';
        App.$('#blDialogSub').textContent = (BL.campaign.channel ?? '') + ' · ' + (BL.campaign.status ?? '');
        await loadStats();
    } catch (e) {
        App.$('#blDialogSub').textContent = e.message;
    }
}
function closeBlastDialog() { App.$('#blDialog').hidden = true; BL.active = null; }

async function loadStats() {
    if (!BL.active) return;
    try {
        const r = await App.api.get(`/api/v1/blast/campaigns/${BL.active}/stats`);
        const d = r.data ?? {};
        App.$('#blStats').innerHTML = `
            <div class="stat"><div class="stat-label">Jumlah</div><div class="stat-value">${App.number(d.jumlah ?? 0)}</div></div>
            <div class="stat"><div class="stat-label">Dihantar</div><div class="stat-value">${App.number(d.dihantar ?? 0)}</div><div class="stat-delta">${d.kadar_hantar ?? 0}%</div></div>
            <div class="stat"><div class="stat-label">Buka</div><div class="stat-value">${App.number(d.dibuka ?? 0)}</div><div class="stat-delta">${d.kadar_buka ?? 0}%</div></div>
            <div class="stat"><div class="stat-label">Bounce</div><div class="stat-value">${App.number(d.bounce ?? 0)}</div><div class="stat-delta ${Number(d.kadar_bounce) > 5 ? 'down' : 'up'}">${d.kadar_bounce ?? 0}%</div></div>`;
    } catch (e) {
        App.$('#blStats').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}

function showPanel(el, msg, cls) {
    const box = App.$(el);
    box.hidden = false;
    box.className = 'mt-3 alert alert-' + cls;
    box.textContent = msg;
}

async function buildAudience() {
    if (!BL.active) return;
    await App.withLoading(App.$('#auBuildBtn'), async () => {
        try {
            const r = await App.api.post(`/api/v1/blast/campaigns/${BL.active}/audience`, {
                min_score: Number(App.$('#auMinScore').value) || 0,
                status: App.$('#auStatus').value || null,
            });
            showPanel('#auResult', r.message ?? 'Audiens dibina.', 'success');
            loadStats(); loadCampaigns(BL.page);
        } catch (e) { showPanel('#auResult', e.message, 'danger'); }
    });
}

async function approveCampaign() {
    if (!BL.active) return;
    await App.withLoading(App.$('#apApproveBtn'), async () => {
        try {
            await App.api.post(`/api/v1/blast/campaigns/${BL.active}/approve`);
            showPanel('#apResult', 'Kempen diluluskan.', 'success');
            loadCampaigns(BL.page);
        } catch (e) { showPanel('#apResult', e.message, 'danger'); }
    });
}

async function scheduleCampaign(startNow) {
    if (!BL.active) return;
    if (!startNow && !App.$('#apScheduleAt').value) {
        showPanel('#apResult', 'Pilih tarikh & masa jadual.', 'danger');
        return;
    }
    await App.withLoading(startNow ? App.$('#apStartBtn') : App.$('#apScheduleBtn'), async () => {
        try {
            const payload = { start_now: !!startNow };
            if (!startNow) payload.scheduled_at = App.$('#apScheduleAt').value.replace('T', ' ') + ':00';
            await App.api.post(`/api/v1/blast/campaigns/${BL.active}/schedule`, payload);
            showPanel('#apResult', startNow ? 'Penghantaran dimulakan.' : 'Kempen dijadualkan.', 'success');
            loadCampaigns(BL.page);
        } catch (e) { showPanel('#apResult', e.message, 'danger'); }
    });
}

async function controlCampaign(action) {
    if (!BL.active) return;
    try {
        await App.api.post(`/api/v1/blast/campaigns/${BL.active}/control`, { action });
        showPanel('#apResult', 'Tindakan "' + action + '" berjaya.', 'success');
        loadCampaigns(BL.page);
    } catch (e) { showPanel('#apResult', e.message, 'danger'); }
}

async function loadMessages() {
    if (!BL.active) return;
    try {
        const r = await App.api.get(`/api/v1/blast/campaigns/${BL.active}/messages`);
        const list = Array.isArray(r.data) ? r.data : [];
        App.$('#msgBody').innerHTML = list.length ? list.map(m => `
            <tr>
                <td class="text-xs">${App.escapeHtml(m.recipient ?? m.email ?? m.phone ?? '—')}</td>
                <td>${statusBadge(m.status)}</td>
                <td class="text-xs">${m.sent_at ? App.date(m.sent_at) : '—'}</td>
            </tr>`).join('') : '<tr><td colspan="3"><div class="empty"><div class="empty-text">Tiada mesej.</div></div></td></tr>';
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAllBlast();
</script>
@endpush

