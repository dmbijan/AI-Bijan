@extends('layouts.app')

@section('title', 'Prospek')

@section('content')
<style>
    .ps-grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(280px, 1fr); gap: 16px; align-items: start; }
    @media (max-width: 1024px) { .ps-grid { grid-template-columns: 1fr; } }
    .ps-score { font-variant-numeric: tabular-nums; font-weight: 700; }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">Prospek</h1>
        <p class="page-desc">Senarai calon pelanggan, skor, consent PDPA dan jejak audit.</p>
    </div>
    <div class="row">
        <button class="btn btn-sm" id="psImportBtn" onclick="openImportDialog()">Import CSV</button>
        <button class="btn btn-sm" onclick="loadAllProspects()">Muat Semula</button>
    </div>
</div>

<div id="psError" class="alert alert-warning" hidden></div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Prospek</div><div class="stat-value" id="psTotal">—</div><div class="stat-delta" id="psPage">—</div></div>
    <div class="stat"><div class="stat-label">Consent Diberi</div><div class="stat-value" id="psGranted">—</div><div class="stat-delta" id="psGrantedSub">—</div></div>
    <div class="stat"><div class="stat-label">Tiada Bukti</div><div class="stat-value" id="psNoEvidence">—</div><div class="stat-delta" id="psNoEvidenceSub">—</div></div>
    <div class="stat"><div class="stat-label">Tamat Tempoh</div><div class="stat-value" id="psExpired">—</div><div class="stat-delta" id="psExpiredSub">—</div></div>
</div>

<div class="card mb-4">
    <div class="row-between">
        <div class="row">
            <input class="input" id="psSearch" type="search" placeholder="Cari nama / syarikat / e-mel..." aria-label="Cari prospek" style="max-width:260px" oninput="debouncedProspects()">
            <select class="select" id="psStatus" aria-label="Tapis mengikut status prospek" style="width:auto" onchange="loadProspects(1)">
                <option value="">Semua status</option>
                <option value="new">Baru</option>
                <option value="enriched">Diperkaya</option>
                <option value="scored">Diskor</option>
                <option value="approved">Diluluskan</option>
                <option value="contacted">Dihubungi</option>
                <option value="replied">Dibalas</option>
                <option value="qualified">Layak</option>
                <option value="closed">Ditutup</option>
                <option value="lost">Hilang</option>
            </select>
            <select class="select" id="psConsent" aria-label="Tapis mengikut status consent" style="width:auto" onchange="loadProspects(1)">
                <option value="">Semua consent</option>
                <option value="granted">Diberi</option>
                <option value="unknown">Tidak diketahui</option>
                <option value="denied">Ditolak</option>
            </select>
            <select class="select" id="psSource" aria-label="Tapis mengikut sumber prospek" style="width:auto" onchange="loadProspects(1)"></select>
        </div>
        <span class="text-xs text-muted" id="psCount">—</span>
    </div>
</div>

<div class="table-wrap mb-4">
    <table class="table">
        <thead>
            <tr><th>Nama</th><th>Syarikat</th><th>E-mel</th><th class="table-num">Skor</th><th>Status</th><th>Consent</th><th>Tindakan</th></tr>
        </thead>
        <tbody id="psTable">
            <tr><td colspan="7"><div class="empty"><div class="empty-text">Memuat...</div></div></td></tr>
        </tbody>
    </table>
</div>

<div class="row-between mb-5">
    <button class="btn btn-sm" id="psPrev" onclick="loadProspects(PS.page - 1)">← Sebelum</button>
    <span class="text-xs text-muted" id="psPageLabel">—</span>
    <button class="btn btn-sm" id="psNext" onclick="loadProspects(PS.page + 1)">Seterusnya →</button>
</div>

<div class="dialog-backdrop" id="importDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="importTitle">
        <div class="dialog-header">
            <h2 class="card-title" id="importTitle">Import Prospek (CSV)</h2>
            <button class="btn btn-ghost btn-sm" onclick="closeImportDialog()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <div class="field">
                <label class="label" for="impFile">Fail CSV (maks 10 MB)</label>
                <input class="input" id="impFile" type="file" accept=".csv,text/csv,text/plain">
                <p class="field-hint">Lajur disokong: first_name, last_name, email, phone, company, job_title, country</p>
            </div>
            <div class="field">
                <label class="label" for="impSource">Sumber</label>
                <select class="select" id="impSource"></select>
            </div>
            <label class="checkbox mb-4">
                <input type="checkbox" id="impAssumeConsent"> Anggap consent diberi (hanya jika anda ada bukti sah)
            </label>
            <div id="importError" class="alert alert-danger" hidden></div>
            <div id="importResult" class="alert alert-success" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="closeImportDialog()">Tutup</button>
            <button class="btn btn-primary" id="importBtn" onclick="submitImport()">Import</button>
        </div>
    </div>
</div>

<div class="dialog-backdrop" id="detailDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="detailTitle" style="max-width:640px">
        <div class="dialog-header">
            <h2 class="card-title" id="detailTitle">Butiran Prospek</h2>
            <button class="btn btn-ghost btn-sm" onclick="closeDetailDialog()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <div id="detailBody"><div class="skeleton" style="height:60px"></div></div>

            <div class="tabs mt-4">
                <button class="tab" aria-selected="true" data-dtab="consent">Consent</button>
                <button class="tab" aria-selected="false" data-dtab="audit">Audit</button>
                <button class="tab" aria-selected="false" data-dtab="url">Semak URL</button>
            </div>

            <section data-dpanel="consent">
                <div class="grid grid-2" style="gap:12px">
                    <div class="field" style="margin:0">
                        <label class="label" for="ctSource">Sumber Bukti</label>
                        <input class="input" id="ctSource" placeholder="cth. borang laman web">
                    </div>
                    <div class="field" style="margin:0">
                        <label class="label" for="ctDays">Sah (hari, pilihan)</label>
                        <input class="input" id="ctDays" type="number" min="1" max="1095" placeholder="365">
                    </div>
                </div>
                <div class="field mt-3">
                    <label class="label" for="ctEvidence">Bukti Persetujuan</label>
                    <input class="input" id="ctEvidence" placeholder="cth. screenshot / id borang / tarikh opt-in">
                </div>
                <div class="row">
                    <button class="btn btn-primary" id="ctRecordBtn" onclick="recordConsent()">Rekod Consent</button>
                    <button class="btn btn-danger" id="ctRevokeBtn" onclick="revokeConsent()">Tarik Balik</button>
                </div>
                <div id="consentResult" class="alert alert-info mt-3" hidden></div>
            </section>

            <section data-dpanel="audit" hidden>
                <button class="btn btn-sm mb-4" onclick="loadAudit()">Muat Jejak Audit</button>
                <div class="table-wrap" style="max-height:260px">
                    <table class="table">
                        <thead><tr><th>Masa</th><th>Tindakan</th><th>Butiran</th></tr></thead>
                        <tbody id="auditBody"><tr><td colspan="3"><div class="empty"><div class="empty-text">Klik butang untuk muat.</div></div></td></tr></tbody>
                    </table>
                </div>
            </section>

            <section data-dpanel="url" hidden>
                <p class="text-xs text-muted">Semak sama ada URL dibenarkan (pola + robots.txt) sebelum scraping — dry-run.</p>
                <div class="row">
                    <input class="input" id="urlCheck" type="url" placeholder="https://contoh.com/halaman" aria-label="URL untuk semakan robots.txt" style="flex:1;min-width:200px">
                    <button class="btn" id="urlCheckBtn" onclick="checkUrl()">Semak</button>
                </div>
                <div id="urlResult" class="mt-3" hidden></div>
            </section>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
const PS = { page: 1, lastPage: 1, sources: [], active: null };

function psError(msg) {
    const el = App.$('#psError');
    el.textContent = msg + ' — pastikan tenant aktif dan kebenaran prospecting.view dimiliki.';
    el.hidden = false;
}

async function loadAllProspects() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await loadSources();
    await Promise.all([loadConsentReport(), loadProspects(1)]);
}

async function loadSources() {
    try {
        const r = await App.api.get('/api/v1/prospecting/sources');
        PS.sources = Array.isArray(r.data) ? r.data : [];
        const opts = PS.sources.map(s => `<option value="${s.id}">${App.escapeHtml(s.name)}</option>`).join('');
        App.$('#psSource').innerHTML = '<option value="">Semua sumber</option>' + opts;
        App.$('#impSource').innerHTML = '<option value="">Tiada sumber</option>' + opts;
    } catch { /* abaikan */ }
}

async function loadConsentReport() {
    try {
        const r = await App.api.get('/api/v1/prospecting/consent-report');
        const d = r.data ?? {};
        App.$('#psTotal').textContent = App.number(d.jumlah ?? 0);
        App.$('#psGranted').textContent = App.number(d.granted ?? 0);
        App.$('#psGrantedSub').textContent = 'Ditolak ' + App.number(d.denied ?? 0) + ' · tidak diketahui ' + App.number(d.unknown ?? 0);
        App.$('#psNoEvidence').textContent = App.number(d.tiada_bukti ?? 0);
        App.$('#psNoEvidenceSub').textContent = 'Granted tanpa evidence — tidak boleh blast';
        App.$('#psExpired').textContent = App.number(d.tamat_tempoh ?? 0);
        App.$('#psExpiredSub').textContent = 'Consent granted yang sudah luput';
    } catch (e) {
        psError(e.message);
    }
}

let psTimer = null;
function debouncedProspects() { clearTimeout(psTimer); psTimer = setTimeout(() => loadProspects(1), 250); }

function consentBadge(c) {
    const map = { granted: 'success', denied: 'danger', unknown: 'neutral' };
    return `<span class="badge badge-${map[c] ?? 'neutral'}">${App.escapeHtml(c ?? '—')}</span>`;
}

async function loadProspects(page) {
    if (page < 1 || page > PS.lastPage && PS.lastPage > 0) return;
    const p = new URLSearchParams();
    p.set('page', String(page));
    p.set('per_page', '25');
    const q = App.$('#psSearch').value.trim(); if (q) p.set('search', q);
    const st = App.$('#psStatus').value; if (st) p.set('status', st);
    const cs = App.$('#psConsent').value; if (cs) p.set('consent_status', cs);
    const src = App.$('#psSource').value; if (src) p.set('source_id', src);

    try {
        const r = await App.api.get('/api/v1/prospecting/prospects?' + p.toString());
        const list = Array.isArray(r.data) ? r.data : [];
        const meta = r.meta ?? {};
        PS.page = Number(meta.current_page ?? page) || 1;
        PS.lastPage = Number(meta.last_page ?? 1) || 1;

        App.$('#psCount').textContent = App.number(meta.total ?? list.length) + ' prospek';
        App.$('#psPageLabel').textContent = 'Halaman ' + PS.page + ' / ' + PS.lastPage;
        App.$('#psPrev').disabled = PS.page <= 1;
        App.$('#psNext').disabled = PS.page >= PS.lastPage;
        App.$('#psPage').textContent = 'Halaman ' + PS.page + ' daripada ' + PS.lastPage;

        App.$('#psTable').innerHTML = list.length ? list.map(x => {
            const nama = [x.first_name, x.last_name].filter(Boolean).join(' ') || '—';
            const skor = Number(x.score) || 0;
            return `
            <tr>
                <td class="text-sm">${App.escapeHtml(nama)}<div class="text-xs text-muted">${App.escapeHtml(x.job_title ?? '')}</div></td>
                <td class="text-xs">${App.escapeHtml(x.company ?? '—')}</td>
                <td class="text-xs">${App.escapeHtml(x.email ?? '—')}</td>
                <td class="table-num"><span class="ps-score badge badge-${skor >= 85 ? 'danger' : skor >= 70 ? 'brand' : 'neutral'}">${skor}</span></td>
                <td class="text-xs">${App.escapeHtml(x.status ?? '—')}</td>
                <td>${consentBadge(x.consent_status)}</td>
                <td>
                    <button class="btn btn-sm" onclick="openDetail(${x.id})">Butiran</button>
                    <button class="btn btn-sm" onclick="rescore(${x.id})">Skor</button>
                </td>
            </tr>`;
        }).join('') : '<tr><td colspan="7"><div class="empty"><div class="empty-title">Tiada prospek</div><div class="empty-text">Import CSV untuk mula.</div></div></td></tr>';
    } catch (e) {
        App.$('#psTable').innerHTML = `<tr><td colspan="7"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function rescore(id) {
    try {
        const r = await App.api.post(`/api/v1/prospecting/prospects/${id}/rescore`);
        App.toast('Skor dikemas kini: ' + (r.label ?? r.data?.score ?? ''), 'success');
        loadProspects(PS.page); loadConsentReport();
    } catch (e) { App.toast(e.message, 'error'); }
}

// ---------- Import CSV ----------
function openImportDialog() {
    App.$('#importError').hidden = true;
    App.$('#importResult').hidden = true;
    App.$('#impFile').value = '';
    App.$('#impAssumeConsent').checked = false;
    App.$('#importDialog').hidden = false;
}
function closeImportDialog() { App.$('#importDialog').hidden = true; }

async function submitImport() {
    const file = App.$('#impFile').files[0];
    if (!file) { const el = App.$('#importError'); el.textContent = 'Pilih fail CSV dahulu.'; el.hidden = false; return; }

    const form = new FormData();
    form.append('file', file);
    const src = App.$('#impSource').value; if (src) form.append('source_id', src);
    form.append('assume_consent', App.$('#impAssumeConsent').checked ? '1' : '0');

    await App.withLoading(App.$('#importBtn'), async () => {
        try {
            const res = await fetch('/api/v1/prospecting/import-csv', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-XSRF-TOKEN': decodeURIComponent((document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/) || [])[1] || ''),
                    ...(App.state.tenant ? { 'X-Tenant': App.state.tenant } : {}),
                },
                body: form,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const msg = data.message || data.errors?.file?.[0] || ('Ralat ' + res.status);
                const el = App.$('#importError'); el.textContent = msg; el.hidden = false;
                return;
            }
            const d = data.data ?? {};
            const ok = App.$('#importResult');
            ok.textContent = (data.message ?? 'Import selesai') + ` · diimport ${d.imported ?? 0}, duplikat ${d.duplicates ?? 0}, dilangkau ${d.skipped ?? 0}`;
            ok.hidden = false;
            App.toast('Import selesai', 'success');
            loadProspects(1); loadConsentReport();
        } catch (e) {
            const el = App.$('#importError'); el.textContent = e.message; el.hidden = false;
        }
    });
}

// ---------- Butiran prospek ----------
document.querySelectorAll('[data-dtab]').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-dtab]').forEach(t => t.setAttribute('aria-selected', 'false'));
        tab.setAttribute('aria-selected', 'true');
        document.querySelectorAll('[data-dpanel]').forEach(p => p.hidden = p.dataset.dpanel !== tab.dataset.dtab);
    });
});

async function openDetail(id) {
    PS.active = id;
    App.$('#detailDialog').hidden = false;
    App.$('#consentResult').hidden = true;
    App.$('#urlResult').hidden = true;
    App.$('#detailBody').innerHTML = '<div class="skeleton" style="height:60px"></div>';
    try {
        const r = await App.api.get(`/api/v1/prospecting/prospects/${id}`);
        const x = r.data ?? {};
        const consent = r.consent ?? {};
        const nama = [x.first_name, x.last_name].filter(Boolean).join(' ') || 'Prospek';
        App.$('#detailTitle').textContent = nama;
        App.$('#detailBody').innerHTML = `
            <div class="grid grid-2" style="gap:10px">
                <div><div class="label">Syarikat</div><div class="text-sm">${App.escapeHtml(x.company ?? '—')}</div></div>
                <div><div class="label">Jawatan</div><div class="text-sm">${App.escapeHtml(x.job_title ?? '—')}</div></div>
                <div><div class="label">E-mel</div><div class="text-sm">${App.escapeHtml(x.email ?? '—')}</div></div>
                <div><div class="label">Telefon</div><div class="text-sm">${App.escapeHtml(x.phone ?? '—')}</div></div>
                <div><div class="label">Skor</div><div class="text-sm">${App.number(x.score ?? 0)}</div></div>
                <div><div class="label">Status</div><div class="text-sm">${App.escapeHtml(x.status ?? '—')}</div></div>
            </div>
            <div class="alert alert-info mt-4" style="margin-bottom:0">
                <div>
                    <div>Consent status: <strong>${App.escapeHtml(x.consent_status ?? '—')}</strong></div>
                    <div class="text-xs mt-2">
                        E-mel: ${consent.boleh_email ? 'YA' : 'TIDAK'} &nbsp;·&nbsp;
                        SMS: ${consent.boleh_sms ? 'YA' : 'TIDAK'} &nbsp;·&nbsp;
                        WhatsApp: ${consent.boleh_whatsapp ? 'YA' : 'TIDAK'}
                    </div>
                </div>
            </div>`;
    } catch (e) {
        App.$('#detailBody').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}
function closeDetailDialog() { App.$('#detailDialog').hidden = true; }


async function recordConsent() {
    if (!PS.active) return;
    const source = App.$('#ctSource').value.trim();
    const evidence = App.$('#ctEvidence').value.trim();
    const out = App.$('#consentResult');
    if (!source || !evidence) {
        out.textContent = 'Sumber dan bukti diperlukan.';
        out.className = 'alert alert-danger mt-3';
        out.hidden = false;
        return;
    }
    await App.withLoading(App.$('#ctRecordBtn'), async () => {
        try {
            await App.api.post(`/api/v1/prospecting/prospects/${PS.active}/consent`, {
                source,
                evidence,
                valid_days: Number(App.$('#ctDays').value) || null,
            });
            out.textContent = 'Consent direkod.';
            out.className = 'alert alert-success mt-3';
            out.hidden = false;
            loadProspects(PS.page);
            loadConsentReport();
            openDetail(PS.active);
        } catch (e) {
            out.textContent = e.message;
            out.className = 'alert alert-danger mt-3';
            out.hidden = false;
        }
    });
}

async function revokeConsent() {
    if (!PS.active) return;
    if (!confirm('Tarik balik consent prospek ini?')) return;
    const out = App.$('#consentResult');
    await App.withLoading(App.$('#ctRevokeBtn'), async () => {
        try {
            await App.api.post(`/api/v1/prospecting/prospects/${PS.active}/consent/revoke`, { reason: 'withdrawn' });
            out.textContent = 'Consent ditarik balik.';
            out.className = 'alert alert-warning mt-3';
            out.hidden = false;
            loadProspects(PS.page);
            loadConsentReport();
        } catch (e) {
            out.textContent = e.message;
            out.className = 'alert alert-danger mt-3';
            out.hidden = false;
        }
    });
}

// ---------- Jejak audit ----------
async function loadAudit() {
    if (!PS.active) return;
    try {
        const r = await App.api.get(`/api/v1/prospecting/prospects/${PS.active}/audit`);
        const list = Array.isArray(r.data) ? r.data : [];
        App.$('#auditBody').innerHTML = list.length ? list.map(a => `
            <tr>
                <td class="text-xs">${App.date(a.created_at)}</td>
                <td class="text-xs">${App.escapeHtml(a.action ?? '—')}</td>
                <td class="text-xs">${App.escapeHtml(JSON.stringify(a.payload ?? a.meta ?? {}))}</td>
            </tr>`).join('') : '<tr><td colspan="3"><div class="empty"><div class="empty-text">Tiada jejak audit.</div></div></td></tr>';
    } catch (e) {
        App.toast(e.message, 'error');
    }
}

// ---------- Semak URL (dry-run) ----------
async function checkUrl() {
    const url = App.$('#urlCheck').value.trim();
    if (!url) { App.toast('Masukkan URL.', 'error'); return; }
    await App.withLoading(App.$('#urlCheckBtn'), async () => {
        try {
            const r = await App.api.post('/api/v1/prospecting/check-url', { url });
            const d = r.data ?? {};
            const el = App.$('#urlResult');
            el.hidden = false;
            el.innerHTML = `
                <div class="alert alert-${d.permitted ? 'success' : 'danger'}" style="margin:0">
                    <div>
                        <div><strong>${d.permitted ? 'Dibenarkan' : 'Tidak dibenarkan'}</strong></div>
                        <div class="text-xs mt-2">Pola URL: ${d.url_pattern_allowed ? 'YA' : 'TIDAK'} · robots.txt: ${d.robots_allowed ? 'YA' : 'TIDAK'}</div>
                    </div>
                </div>`;
        } catch (e) {
            App.toast(e.message, 'error');
        }
    });
}

loadAllProspects();
</script>
@endpush

