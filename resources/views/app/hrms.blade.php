@extends('layouts.app')

@section('title', 'HRMS')

@section('content')
<style>
    .tabs { scrollbar-width: none; }
    [data-panel] .stat { min-width: 0; }
    [data-panel] .stat-value { white-space: nowrap; font-size: clamp(1.05rem, 4.6vw, 1.6rem); font-variant-numeric: tabular-nums; }
    .brief-item { display: grid; grid-template-columns: 6px 1fr; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--border, #e5e5e5); }
    .brief-item:last-child { border-bottom: 0; }
    .brief-bar { border-radius: 3px; }
    .sev-danger { background: var(--danger, #c0392b); } .sev-warning { background: var(--warning, #d68910); } .sev-info { background: var(--info, #2e86c1); }
    .kanban { display: grid; grid-auto-flow: column; grid-auto-columns: minmax(220px, 1fr); gap: 12px; overflow-x: auto; padding-bottom: 8px; }
    .kcol { background: var(--surface-2, rgba(127,127,127,.06)); border-radius: 10px; padding: 10px; min-height: 120px; }
    .kcol h3 { font-size: .8rem; text-transform: uppercase; letter-spacing: .04em; margin: 0 0 8px; display: flex; justify-content: space-between; }
    .kcard { background: var(--surface, #fff); border: 1px solid var(--border, #e5e5e5); border-radius: 8px; padding: 10px; margin-bottom: 8px; }
    .kcard .row { flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .bar { height: 6px; border-radius: 3px; background: var(--border, #e5e5e5); overflow: hidden; }
    .bar > i { display: block; height: 100%; background: var(--primary, #b7791f); }
    .lvl { display: inline-flex; gap: 2px; } .lvl b { width: 8px; height: 8px; border-radius: 50%; background: var(--border, #ddd); }
    .lvl b.on { background: var(--primary, #b7791f); }
    .cell-gap { background: rgba(214, 137, 16, .14); }
    .cell-exp { background: rgba(192, 57, 43, .14); }
    .list-row { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; padding: 12px 16px; border-bottom: 1px solid var(--border, #e5e5e5); }
    .list-row:last-child { border-bottom: 0; }
    .list-row .meta { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; }
    .list-row .act { margin-left: auto; display: flex; gap: 6px; flex-wrap: wrap; }
    .task { display: flex; gap: 10px; align-items: flex-start; padding: 6px 0; }
    .task input { margin-top: 3px; width: 18px; height: 18px; }
    @media (max-width: 640px) { [data-panel] .grid-4 { grid-template-columns: 1fr 1fr; } [data-panel] .grid-3, [data-panel] .grid-2 { grid-template-columns: 1fr; } }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">HRMS</h1>
        <p class="page-desc">Pekerja, cuti, pengambilan, prestasi, gaji, kemahiran, faedah dan masuk/keluar kerja.</p>
    </div>
    <button class="btn btn-sm" onclick="muatSemula()">Muat semula</button>
</div>

<div class="tabs" role="tablist" style="overflow-x:auto">
    <button class="tab" role="tab" aria-selected="true" data-tab="brief">AI CHRO</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="employees">Pekerja</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="leave">Cuti</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="attendance">Kehadiran</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="recruitment">Pengambilan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="performance">Prestasi</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="compensation">Gaji & bonus</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="skills">Kemahiran</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="engagement">Suara pekerja</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="benefits">Faedah</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="lifecycle">Masuk & keluar</button>
</div>

{{-- AI CHRO (AI-006) --}}
<section data-panel="brief">
    <div class="grid grid-4 mb-4">
        <div class="stat"><div class="stat-label">Pekerja aktif</div><div class="stat-value" id="bHead">—</div></div>
        <div class="stat"><div class="stat-label">Kos gaji sebulan</div><div class="stat-value" id="bCost">—</div><div class="stat-delta text-xs" id="bCostNote"></div></div>
        <div class="stat"><div class="stat-label">Cuti menunggu</div><div class="stat-value" id="bLeave">—</div></div>
        <div class="stat"><div class="stat-label">Tuntutan & gaji menunggu</div><div class="stat-value" id="bPend">—</div></div>
    </div>
    <div class="card" style="padding:0">
        <div class="card-header" style="padding:16px 16px 0"><div><h2 class="card-title">Perkara untuk diberi perhatian</h2><p class="card-subtitle" id="bPolicy"></p></div></div>
        <div id="bItems"></div>
    </div>
</section>

<section data-panel="employees" hidden>
    <div class="row mb-4" style="gap:8px;flex-wrap:wrap">
        <input class="input" id="eCari" placeholder="Cari nama, emel, no. pekerja" style="max-width:320px" oninput="clearTimeout(window._ec); window._ec = setTimeout(loadEmployees, 300)">
    </div>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Nama</th><th>No. pekerja</th><th>Jawatan</th><th>Jabatan</th><th>Status</th><th></th></tr></thead>
        <tbody id="empTable"></tbody>
    </table></div>
</section>

<section data-panel="leave" hidden>
    <div class="card" style="padding:0"><div id="leaveList"></div></div>
</section>

<section data-panel="attendance" hidden>
    <p class="text-sm text-muted mb-4">7 hari terakhir.</p>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Pekerja</th><th>Tarikh</th><th>Masuk</th><th>Keluar</th><th>Status</th></tr></thead>
        <tbody id="attTable"></tbody>
    </table></div>
</section>

{{-- HR-004 --}}
<section data-panel="recruitment" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <div class="field" style="margin:0"><label class="label" for="rVac">Jawatan kosong</label><select class="select" id="rVac" onchange="loadApplicants()"></select></div>
        <div class="row" style="gap:8px;flex-wrap:wrap">
            <button class="btn btn-sm" onclick="tambahJawatan()">+ Jawatan kosong</button>
            <button class="btn btn-primary btn-sm" onclick="tambahCalon()">+ Calon</button>
        </div>
    </div>
    <div class="kanban" id="rBoard"></div>
</section>

{{-- HR-009 --}}
<section data-panel="performance" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <div class="field" style="margin:0"><label class="label" for="pPeriod">Tempoh</label><input class="input" id="pPeriod" style="max-width:140px" onchange="loadPerformance()"></div>
        <div class="row" style="gap:8px;flex-wrap:wrap">
            <button class="btn btn-sm" onclick="tambahSasaran()">+ Sasaran</button>
            <button class="btn btn-primary btn-sm" onclick="tulisPenilaian()">Tulis penilaian</button>
        </div>
    </div>
    <div class="grid grid-2">
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><h2 class="card-title">Sasaran</h2></div><div id="pGoals"></div></div>
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><h2 class="card-title">Penilaian</h2></div><div id="pReviews"></div></div>
    </div>
</section>

{{-- HR-010 --}}
<section data-panel="compensation" hidden>
    <div id="cDenied"></div>
    <div class="row mb-4" style="gap:8px;flex-wrap:wrap;justify-content:flex-end">
        <button class="btn btn-sm" onclick="tambahGred()">+ Gred gaji</button>
        <button class="btn btn-primary btn-sm" onclick="mohonUbahGaji()">Mohon kenaikan / bonus</button>
    </div>
    <div class="card mb-4" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><div><h2 class="card-title">Menunggu keputusan</h2><p class="card-subtitle">Pemohon tidak boleh meluluskan permohonan sendiri. Gaji baharu ditulis ke Payroll bila No. IC sepadan.</p></div></div><div id="cChanges"></div></div>
    <div class="grid grid-2">
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><h2 class="card-title">Gred gaji</h2></div><div id="cBands"></div></div>
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><h2 class="card-title">Kedudukan staf dalam gred</h2></div><div id="cStaff"></div></div>
    </div>
</section>

{{-- HR-011 --}}
<section data-panel="skills" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <p class="text-sm text-muted" id="sGapNote" style="margin:0"></p>
        <div class="row" style="gap:8px;flex-wrap:wrap">
            <button class="btn btn-sm" onclick="tambahKemahiran()">+ Kemahiran</button>
            <button class="btn btn-sm" onclick="tetapKeperluan()">Keperluan jawatan</button>
            <button class="btn btn-primary btn-sm" onclick="nilaiKemahiran()">Nilai kemahiran</button>
        </div>
    </div>
    <div class="table-wrap"><table class="table" id="sMatrix"></table></div>
    <p class="text-xs text-muted mt-2">Tahap: 0 tiada · 1 asas · 2 boleh sendiri · 3 mahir · 4 boleh ajar. Kuning = di bawah keperluan jawatan. Merah = sijil tamat.</p>
</section>

{{-- HR-012 --}}
<section data-panel="engagement" hidden>
    <div class="row mb-4" style="gap:8px;flex-wrap:wrap;justify-content:flex-end">
        <button class="btn btn-sm" onclick="beriPenghargaan()">Beri penghargaan</button>
        <button class="btn btn-primary btn-sm" onclick="buatTinjauan()">+ Tinjauan</button>
    </div>
    <div class="grid grid-2">
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><div><h2 class="card-title">Tinjauan</h2><p class="card-subtitle">Tanpa nama: keputusan hanya dipapar selepas 3 jawapan.</p></div></div><div id="gSurveys"></div></div>
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><h2 class="card-title">Penghargaan terkini</h2></div><div id="gRecog"></div></div>
    </div>
    <div id="gResults" class="mt-4"></div>
</section>

{{-- HR-013 --}}
<section data-panel="benefits" hidden>
    <div class="row mb-4" style="gap:8px;flex-wrap:wrap;justify-content:flex-end">
        <button class="btn btn-sm" onclick="tambahPelan()">+ Pelan faedah</button>
        <button class="btn btn-primary btn-sm" onclick="buatTuntutan()">+ Tuntutan</button>
    </div>
    <div class="grid grid-2">
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><h2 class="card-title">Pelan</h2></div><div id="fPlans"></div></div>
        <div class="card" style="padding:0"><div class="card-header" style="padding:16px 16px 0"><h2 class="card-title">Tuntutan</h2></div><div id="fClaims"></div></div>
    </div>
</section>

{{-- HR-014 --}}
<section data-panel="lifecycle" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <div class="field" style="margin:0"><label class="label" for="lType">Tunjuk</label>
            <select class="select" id="lType" onchange="loadLifecycle()"><option value="">Semua</option><option value="onboarding">Masuk kerja</option><option value="offboarding">Keluar kerja</option></select></div>
        <button class="btn btn-primary btn-sm" onclick="prosesKeluar()">Proses pekerja keluar</button>
    </div>
    <div id="lGroups" class="stack"></div>
</section>

<dialog class="dialog" id="dlg">
    <form method="dialog" id="dlgForm">
        <div class="dialog-header"><h2 class="card-title" id="dlgTitle"></h2></div>
        <div class="dialog-body stack" id="dlgBody"></div>
        <div class="dialog-footer row" style="justify-content:flex-end;gap:8px">
            <button class="btn btn-ghost" value="cancel" formnovalidate>Batal</button>
            <button class="btn btn-primary" value="ok">Simpan</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
const RM = n => App.rupiah(n ?? 0);
const E = s => App.escapeHtml(String(s ?? ''));
const tarikh = d => d ? new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(String(d).slice(0, 10) + 'T00:00:00')) : '—';
const isoTempatan = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
const hariIni = () => isoTempatan(new Date());
const kosong = t => `<div class="empty" style="padding:24px"><div class="empty-text">${E(t)}</div></div>`;
const kosongTr = (c, t) => `<tr><td colspan="${c}">${kosong(t)}</td></tr>`;
const nama = e => [e.first_name, e.last_name].filter(Boolean).join(' ') || '—';
const STATUS_PEKERJA = { active: ['Aktif', 'success'], on_leave: ['Bercuti', 'warning'], suspended: ['Digantung', 'danger'], terminated: ['Diberhentikan', 'neutral'], resigned: ['Berhenti', 'neutral'] };
const PERINGKAT = [['applied', 'Mohon'], ['screening', 'Saringan'], ['interview', 'Temu duga'], ['offer', 'Tawaran'], ['hired', 'Diambil'], ['rejected', 'Ditolak']];
const STATUS_UMUM = { pending: ['Menunggu', 'warning'], approved: ['Lulus', 'success'], rejected: ['Ditolak', 'danger'], draft: ['Draf', 'neutral'], submitted: ['Dihantar', 'success'], open: ['Dibuka', 'success'], closed: ['Ditutup', 'neutral'] };
const lencana = (map, k) => { const [t, c] = map[k] ?? [k ?? '—', 'neutral']; return `<span class="badge badge-${c}">${E(t)}</span>`; };
const JENIS_UBAH = { increment: 'Kenaikan gaji', promotion: 'Naik pangkat', bonus: 'Bonus', allowance: 'Elaun tetap' };
const dimuat = {};
let PEKERJA = [];

const pemuat = { brief: loadBrief, employees: loadEmployees, leave: loadLeave, attendance: loadAttendance, recruitment: loadRecruitment, performance: loadPerformance,
    compensation: loadCompensation, skills: loadSkills, engagement: loadEngagement, benefits: loadBenefits, lifecycle: loadLifecycle };
function bukaTab(n) {
    document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', String(t.dataset.tab === n)));
    document.querySelector(`[data-tab="${n}"]`)?.scrollIntoView({ inline: 'nearest', block: 'nearest' });
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== n);
    if (!dimuat[n]) { dimuat[n] = true; pemuat[n](); }
    history.replaceState(null, '', '#' + n);
}
document.querySelectorAll('[data-tab]').forEach(t => t.addEventListener('click', () => bukaTab(t.dataset.tab)));
window.addEventListener('hashchange', () => { const n = location.hash.slice(1); if (pemuat[n]) bukaTab(n); });
function muatSemula() { Object.keys(dimuat).forEach(k => delete dimuat[k]); muatPekerja().then(() => bukaTab(location.hash.slice(1) in pemuat ? location.hash.slice(1) : 'brief')); }

// Dialog: medan {n, l, t, opts, req, v, hint, ph}. t: text|number|date|textarea|checkbox
function borang(tajuk, medan) {
    const dlg = App.$('#dlg');
    App.$('#dlgTitle').textContent = tajuk;
    App.$('#dlgBody').innerHTML = medan.map(m => `
        <div class="field"><label class="label" for="f_${m.n}">${E(m.l)}</label>
        ${m.opts ? `<select class="select" id="f_${m.n}">${m.opts.map(o => `<option value="${E(o[0])}" ${String(o[0]) === String(m.v ?? '') ? 'selected' : ''}>${E(o[1])}</option>`).join('')}</select>`
          : m.t === 'textarea' ? `<textarea class="input" id="f_${m.n}" rows="4" ${m.req ? 'required' : ''} placeholder="${E(m.ph ?? '')}">${E(m.v ?? '')}</textarea>`
          : m.t === 'checkbox' ? `<input type="checkbox" id="f_${m.n}" ${m.v ? 'checked' : ''}>`
          : `<input class="input" id="f_${m.n}" type="${m.t || 'text'}" ${m.req ? 'required' : ''} ${m.t === 'number' ? 'step="any"' : ''} value="${E(m.v ?? '')}" placeholder="${E(m.ph ?? '')}">`}
        ${m.hint ? `<div class="field-hint">${E(m.hint)}</div>` : ''}</div>`).join('');
    dlg.showModal();
    return new Promise(res => {
        dlg.onclose = () => {
            if (dlg.returnValue !== 'ok') return res(null);
            const out = {};
            medan.forEach(m => {
                const el = App.$('#f_' + m.n);
                if (m.t === 'checkbox') { out[m.n] = el.checked; return; }
                const v = el.value.trim();
                if (v !== '') out[m.n] = m.t === 'number' ? Number(v) : v;
            });
            res(out);
        };
    });
}
async function cuba(fn, ok) {
    try { const r = await fn(); if (ok) App.toast(ok, 'success'); return r; }
    catch (e) { App.toast(e.message, 'error'); return null; }
}
const pilihPekerja = (v) => ({ n: 'employee_id', l: 'Pekerja', opts: PEKERJA.filter(e => ['active', 'on_leave'].includes(e.status)).map(e => [e.id, nama(e)]), v });
async function muatPekerja() {
    const r = await cuba(() => App.api.get('/api/v1/hrms/employees?per_page=200'));
    PEKERJA = r?.data?.data ?? [];
}

// ── AI CHRO ──
async function loadBrief() {
    const r = await cuba(() => App.api.get('/api/v1/hrms/talent/brief'));
    if (!r) return;
    const d = r.data;
    App.$('#bHead').textContent = App.number(d.headcount);
    App.$('#bCost').textContent = RM(d.monthly_salary_cost);
    App.$('#bCostNote').textContent = d.payroll_matched ? `${d.payroll_matched} orang dalam Payroll` : 'Belum ada data Payroll';
    App.$('#bLeave').textContent = d.pending.leave;
    App.$('#bPend').textContent = d.pending.claims + d.pending.compensation;
    App.$('#bPolicy').textContent = d.policy;
    App.$('#bItems').innerHTML = d.items.length ? d.items.map(i => `
        <div class="brief-item"><div class="brief-bar sev-${E(i.severity)}"></div>
            <div><div class="text-sm" style="font-weight:600">${E(i.title)}</div>
            <div class="text-sm">${E(i.detail)}</div>
            <div class="row mt-2" style="gap:8px;flex-wrap:wrap;align-items:center"><span class="text-xs text-muted">${E(i.action)}</span>
            ${pemuat[i.tab] ? `<button class="btn btn-sm btn-ghost" onclick="bukaTab('${i.tab}')">Buka</button>` : ''}</div></div></div>`).join('')
        : kosong('Tiada perkara yang perlu diberi perhatian sekarang.');
}

// ── Pekerja ──
async function loadEmployees() {
    const q = App.$('#eCari').value.trim();
    const r = await cuba(() => App.api.get('/api/v1/hrms/employees?per_page=200' + (q ? '&search=' + encodeURIComponent(q) : '')));
    const rows = r?.data?.data ?? [];
    App.$('#empTable').innerHTML = rows.length ? rows.map(e => `<tr>
        <td class="text-sm">${E(nama(e))}<div class="text-xs text-muted">${E(e.email)}</div></td>
        <td class="mono text-xs">${E(e.employee_no)}</td>
        <td class="text-sm text-muted">${E(e.designation?.name ?? '—')}</td>
        <td class="text-sm">${E(e.department?.name ?? '—')}</td>
        <td>${lencana(STATUS_PEKERJA, e.status)}</td>
        <td>${['active', 'on_leave'].includes(e.status) ? `<button class="btn btn-sm btn-ghost" onclick="mulaMasuk(${e.id})">Senarai semak masuk</button>` : ''}</td>
    </tr>`).join('') : kosongTr(6, q ? 'Tiada padanan.' : 'Belum ada pekerja. Tambah melalui Pengambilan atau Import data.');
}

// ── Cuti ──
async function loadLeave() {
    const r = await cuba(() => App.api.get('/api/v1/hrms/leave/pending?limit=100'));
    const rows = r?.data ?? [];
    App.$('#leaveList').innerHTML = rows.length ? rows.map(l => `<div class="list-row">
        <div class="text-sm" style="font-weight:600">${E(l.employee ? nama(l.employee) : '—')}</div>
        <div class="text-sm">${E(l.days_requested)} hari</div>
        <div class="meta"><span class="badge badge-neutral">${E(l.leave_type?.name ?? 'Cuti')}</span>
            <span class="text-xs text-muted">${tarikh(l.start_date)} – ${tarikh(l.end_date)}</span>
            ${l.reason ? `<span class="text-xs text-muted">${E(l.reason)}</span>` : ''}
            <span class="act"><button class="btn btn-sm btn-primary" onclick="putusCuti(${l.id}, 'approve')">Lulus</button><button class="btn btn-sm btn-danger" onclick="putusCuti(${l.id}, 'reject')">Tolak</button></span></div>
    </div>`).join('') : kosong('Tiada permohonan cuti menunggu.');
}
async function putusCuti(id, a) {
    if (await cuba(() => App.api.post(`/api/v1/hrms/leave/requests/${id}/${a}`), a === 'approve' ? 'Cuti diluluskan' : 'Cuti ditolak')) { loadLeave(); delete dimuat.brief; }
}

// ── Kehadiran ──
async function loadAttendance() {
    const d = new Date(); d.setDate(d.getDate() - 6);
    const r = await cuba(() => App.api.get(`/api/v1/hrms/attendance?start_date=${isoTempatan(d)}&end_date=${hariIni()}`));
    const rows = r?.data?.data ?? [];
    const S = { present: ['Hadir', 'success'], late: ['Lewat', 'warning'], absent: ['Tidak hadir', 'danger'], half_day: ['Separuh hari', 'warning'], on_leave: ['Cuti', 'neutral'], holiday: ['Cuti umum', 'neutral'] };
    App.$('#attTable').innerHTML = rows.length ? rows.map(a => `<tr>
        <td class="text-sm">${E(a.employee ? nama(a.employee) : '—')}</td><td class="text-xs">${tarikh(a.date)}</td>
        <td class="text-xs mono">${E(a.check_in_time ?? '—')}</td><td class="text-xs mono">${E(a.check_out_time ?? '—')}</td><td>${lencana(S, a.status)}</td></tr>`).join('')
        : kosongTr(5, 'Tiada rekod kehadiran 7 hari terakhir.');
}

// ── Pengambilan ──
let JAWATAN = [];
async function loadRecruitment() {
    const r = await cuba(() => App.api.get('/api/v1/hrms/recruitment/vacancies'));
    JAWATAN = r?.data ?? [];
    const sel = App.$('#rVac'), cur = sel.value;
    sel.innerHTML = '<option value="">Semua jawatan</option>' + JAWATAN.map(v => `<option value="${v.id}">${E(v.title)}${v.status !== 'open' ? ' (ditutup)' : ''}</option>`).join('');
    sel.value = cur;
    loadApplicants();
}
async function loadApplicants() {
    const v = App.$('#rVac').value;
    const r = await cuba(() => App.api.get('/api/v1/hrms/recruitment/applicants' + (v ? '?vacancy_id=' + v : '')));
    if (!r) return;
    const list = r.data.applicants;
    if (!JAWATAN.length) { App.$('#rBoard').innerHTML = kosong('Mula dengan tambah jawatan kosong.'); return; }
    const jt = id => JAWATAN.find(j => j.id === id)?.title ?? '';
    App.$('#rBoard').innerHTML = PERINGKAT.map(([k, t], i) => {
        const c = list.filter(a => a.stage === k);
        return `<div class="kcol"><h3><span>${t}</span><span>${c.length}</span></h3>${c.map(a => `<div class="kcard">
            <div class="text-sm" style="font-weight:600">${E(a.name)}</div>
            <div class="text-xs text-muted">${E([v ? '' : jt(a.vacancy_id), a.source, a.score ? '★'.repeat(a.score) : ''].filter(Boolean).join(' · '))}</div>
            ${a.interview_at ? `<div class="text-xs">Temu duga: ${E(new Date(a.interview_at).toLocaleString('ms-MY'))}</div>` : ''}
            ${a.offer_salary ? `<div class="text-xs">Tawaran: ${RM(a.offer_salary)}</div>` : ''}
            ${!['hired', 'rejected'].includes(k) ? `<div class="row">
                ${k === 'offer' ? `<button class="btn btn-sm btn-primary" onclick="ambilKerja(${a.id})">Ambil bekerja</button>`
                    : `<button class="btn btn-sm" onclick="gerakCalon(${a.id}, '${PERINGKAT[i + 1][0]}')">→ ${PERINGKAT[i + 1][1]}</button>`}
                <button class="btn btn-sm btn-ghost" onclick="gerakCalon(${a.id}, 'rejected')">Tolak</button></div>` : ''}
        </div>`).join('')}</div>`;
    }).join('');
}
async function tambahJawatan() {
    const d = await borang('Jawatan kosong', [{ n: 'title', l: 'Jawatan', req: true, ph: 'cth. Florist' },
        { n: 'employment_type', l: 'Jenis', opts: [['full_time', 'Sepenuh masa'], ['part_time', 'Separuh masa'], ['contract', 'Kontrak'], ['intern', 'Latihan industri']] },
        { n: 'openings', l: 'Bilangan kekosongan', t: 'number', v: 1 }, { n: 'salary_min', l: 'Gaji dari (RM)', t: 'number' }, { n: 'salary_max', l: 'Gaji hingga (RM)', t: 'number' },
        { n: 'closes_on', l: 'Tutup pada', t: 'date' }, { n: 'description', l: 'Keterangan', t: 'textarea' }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/recruitment/vacancies', d), 'Jawatan ditambah')) loadRecruitment();
}
async function tambahCalon() {
    const buka = JAWATAN.filter(j => j.status === 'open');
    if (!buka.length) return App.toast('Tambah jawatan kosong dahulu.', 'error');
    const d = await borang('Calon baharu', [{ n: 'vacancy_id', l: 'Jawatan', opts: buka.map(j => [j.id, j.title]), v: App.$('#rVac').value },
        { n: 'name', l: 'Nama penuh', req: true }, { n: 'email', l: 'Emel', t: 'email' }, { n: 'phone', l: 'Telefon' },
        { n: 'source', l: 'Sumber', ph: 'cth. Jobstreet, WhatsApp, kenalan' }, { n: 'notes', l: 'Nota', t: 'textarea' }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/recruitment/applicants', d), 'Calon ditambah')) loadApplicants();
}
async function gerakCalon(id, stage) {
    let extra = {};
    if (stage === 'interview') { extra = await borang('Jadual temu duga', [{ n: 'interview_at', l: 'Tarikh & masa', t: 'datetime-local' }, { n: 'score', l: 'Skor saringan (1–5)', t: 'number' }]); if (!extra) return; }
    if (stage === 'offer') { extra = await borang('Tawaran', [{ n: 'offer_salary', l: 'Gaji ditawarkan (RM)', t: 'number' }, { n: 'score', l: 'Skor temu duga (1–5)', t: 'number' }]); if (!extra) return; }
    if (stage === 'rejected' && !confirm('Tolak calon ini?')) return;
    if (await cuba(() => App.api.post(`/api/v1/hrms/recruitment/applicants/${id}/move`, { stage, ...extra }))) loadApplicants();
}
async function ambilKerja(id) {
    const d = await borang('Ambil bekerja', [{ n: 'date_joined', l: 'Tarikh mula', t: 'date', v: hariIni() }, { n: 'email', l: 'Emel (jika calon tiada emel)', t: 'email' },
        { n: 'probation_months', l: 'Tempoh percubaan (bulan)', t: 'number', v: 3 }]);
    if (d && await cuba(() => App.api.post(`/api/v1/hrms/recruitment/applicants/${id}/hire`, d), 'Pekerja dicipta. Senarai semak masuk kerja dimulakan.')) { await muatPekerja(); loadRecruitment(); delete dimuat.employees; delete dimuat.lifecycle; }
}

// ── Prestasi ──
function tempohLalai() { const d = new Date(); return d.getFullYear() + (d.getMonth() < 6 ? '-H1' : '-H2'); }
async function loadPerformance() {
    const p = App.$('#pPeriod').value || (App.$('#pPeriod').value = tempohLalai());
    const [g, r] = await Promise.all([cuba(() => App.api.get('/api/v1/hrms/performance/goals?period=' + encodeURIComponent(p))), cuba(() => App.api.get('/api/v1/hrms/performance/reviews?period=' + encodeURIComponent(p)))]);
    const goals = g?.data ?? [], revs = r?.data ?? [];
    App.$('#pGoals').innerHTML = goals.length ? goals.map(x => `<div class="list-row">
        <div class="text-sm"><b>${E(x.employee_name)}</b> · ${E(x.title)}</div><div class="text-sm">${x.progress}%</div>
        <div class="meta" style="display:block"><div class="bar"><i style="width:${Math.min(100, x.progress)}%"></i></div>
        <div class="row mt-2" style="gap:8px;align-items:center"><span class="text-xs text-muted">${App.number(x.actual)} / ${App.number(x.target)} · wajaran ${x.weight}%</span>
        <button class="btn btn-sm btn-ghost" style="margin-left:auto" onclick="kemasSasaran(${x.id}, ${x.actual})">Kemas kini</button></div></div></div>`).join('')
        : kosong('Belum ada sasaran untuk tempoh ini.');
    App.$('#pReviews').innerHTML = revs.length ? revs.map(x => `<div class="list-row">
        <div class="text-sm" style="font-weight:600">${E(x.employee_name)}</div><div>${x.rating ? '★'.repeat(x.rating) + '☆'.repeat(5 - x.rating) : '—'}</div>
        <div class="meta">${lencana(STATUS_UMUM, x.status)}<span class="text-xs text-muted">Sasaran ${x.goal_score ?? x.live_goal_score ?? '—'}%</span>
        ${x.strengths ? `<span class="text-xs">${E(x.strengths)}</span>` : ''}</div></div>`).join('') : kosong('Belum ada penilaian.');
}
async function tambahSasaran() {
    const d = await borang('Sasaran baharu', [pilihPekerja(), { n: 'title', l: 'Sasaran', req: true, ph: 'cth. Jualan bulanan' }, { n: 'target', l: 'Sasaran angka', t: 'number', v: 100 },
        { n: 'actual', l: 'Pencapaian setakat ini', t: 'number', v: 0 }, { n: 'weight', l: 'Wajaran (%)', t: 'number', v: 100 }, { n: 'due_on', l: 'Tarikh akhir', t: 'date' }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/performance/goals', { ...d, period: App.$('#pPeriod').value }), 'Sasaran ditambah')) loadPerformance();
}
async function kemasSasaran(id, actual) {
    const d = await borang('Kemas kini pencapaian', [{ n: 'actual', l: 'Pencapaian', t: 'number', v: actual }]);
    if (d && await cuba(() => App.api.put(`/api/v1/hrms/performance/goals/${id}`, d), 'Dikemas kini')) loadPerformance();
}
async function tulisPenilaian() {
    const d = await borang('Penilaian prestasi', [pilihPekerja(), { n: 'rating', l: 'Penarafan', opts: [['', '— pilih —'], [5, '5 Cemerlang'], [4, '4 Baik'], [3, '3 Memuaskan'], [2, '2 Perlu baiki'], [1, '1 Lemah']] },
        { n: 'strengths', l: 'Kekuatan', t: 'textarea' }, { n: 'improvements', l: 'Perlu diperbaiki', t: 'textarea' }, { n: 'submit', l: 'Hantar sekarang (tidak boleh diubah selepas ini)', t: 'checkbox' }]);
    if (!d) return;
    if (d.rating) d.rating = Number(d.rating);
    if (await cuba(() => App.api.post('/api/v1/hrms/performance/reviews', { ...d, period: App.$('#pPeriod').value }), d.submit ? 'Penilaian dihantar' : 'Draf disimpan')) loadPerformance();
}

// ── Gaji & bonus ──
let GRED = [];
async function loadCompensation() {
    let r;
    try { r = await App.api.get('/api/v1/hrms/compensation'); }
    catch (e) { App.$('#cDenied').innerHTML = `<div class="alert alert-warning mb-4 text-sm">${E(e.message)} Maklumat gaji hanya untuk peranan yang boleh meluluskan HR.</div>`; return; }
    const d = r.data; GRED = d.bands;
    const pend = d.changes.filter(c => c.status === 'pending');
    const baris = c => `<div class="list-row">
        <div class="text-sm"><b>${E(c.employee_name)}</b> · ${E(JENIS_UBAH[c.type])}</div>
        <div class="text-sm" style="font-weight:600">${c.new_salary ? RM(c.new_salary) : RM(c.amount)}</div>
        <div class="meta">${lencana(STATUS_UMUM, c.status)}<span class="text-xs text-muted">${c.current_salary ? 'Kini ' + RM(c.current_salary) + ' · ' : ''}berkuat kuasa ${tarikh(c.effective_on)}</span>
        ${c.reason ? `<span class="text-xs">${E(c.reason)}</span>` : ''}
        ${c.status === 'pending' ? `<span class="act"><button class="btn btn-sm btn-primary" onclick="putusGaji(${c.id}, true)">Lulus</button><button class="btn btn-sm btn-danger" onclick="putusGaji(${c.id}, false)">Tolak</button></span>` : ''}</div></div>`;
    App.$('#cChanges').innerHTML = pend.length ? pend.map(baris).join('') : kosong('Tiada permohonan menunggu.');
    App.$('#cBands').innerHTML = d.bands.length ? d.bands.map(b => `<div class="list-row"><div class="text-sm"><b>${E(b.grade)}</b> ${E(b.name)}</div><div class="text-sm">${RM(b.min_salary)} – ${RM(b.max_salary)}</div></div>`).join('')
        : kosong('Belum ada gred gaji.');
    const POS = { below: ['Di bawah gred', 'warning'], within: ['Dalam gred', 'success'], above: ['Atas gred', 'danger'] };
    App.$('#cStaff').innerHTML = d.staff.length ? d.staff.map(s => `<div class="list-row"><div class="text-sm">${E(s.name)}</div><div class="text-sm">${s.salary !== null ? RM(s.salary) : '—'}</div>
        <div class="meta"><span class="text-xs text-muted">Gred ${E(s.grade ?? '—')}</span>${s.position ? lencana(POS, s.position) : ''}
        <span class="act"><button class="btn btn-sm btn-ghost" onclick="tetapGred(${s.employee_id}, '${E(s.grade ?? '')}')">Tetap gred</button></span></div></div>`).join('') : kosong('Tiada pekerja aktif.');
}
async function tambahGred() {
    const d = await borang('Gred gaji', [{ n: 'grade', l: 'Kod gred', req: true, ph: 'cth. G1' }, { n: 'name', l: 'Nama', req: true, ph: 'cth. Florist' },
        { n: 'min_salary', l: 'Minimum (RM)', t: 'number', req: true }, { n: 'max_salary', l: 'Maksimum (RM)', t: 'number', req: true }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/compensation/bands', d), 'Gred disimpan')) loadCompensation();
}
async function tetapGred(id, cur) {
    const d = await borang('Tetap gred', [{ n: 'grade', l: 'Gred', opts: [['', 'Tiada'], ...GRED.map(g => [g.grade, g.grade + ' ' + g.name])], v: cur }]);
    if (d && await cuba(() => App.api.post(`/api/v1/hrms/compensation/employees/${id}/grade`, { grade: d.grade ?? null }), 'Gred ditetapkan')) loadCompensation();
}
async function mohonUbahGaji() {
    const d = await borang('Mohon perubahan gaji', [pilihPekerja(), { n: 'type', l: 'Jenis', opts: Object.entries(JENIS_UBAH) },
        { n: 'new_salary', l: 'Gaji baharu (kenaikan / naik pangkat)', t: 'number' }, { n: 'amount', l: 'Amaun (bonus / elaun)', t: 'number' },
        { n: 'effective_on', l: 'Berkuat kuasa', t: 'date', v: hariIni() }, { n: 'reason', l: 'Sebab' }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/compensation/changes', d), 'Permohonan dihantar untuk kelulusan')) loadCompensation();
}
async function putusGaji(id, approve) {
    if (!confirm(approve ? 'Lulus perubahan gaji ini? Gaji di Payroll akan dikemas kini.' : 'Tolak permohonan ini?')) return;
    const r = await cuba(() => App.api.post(`/api/v1/hrms/compensation/changes/${id}/decide`, { approve }));
    if (r) { App.toast(approve ? (r.data.payroll_updated ? 'Diluluskan dan Payroll dikemas kini' : 'Diluluskan. Tiada rekod Payroll sepadan (semak No. IC).') : 'Ditolak', 'success'); loadCompensation(); }
}

// ── Kemahiran ──
let SKILLS = [];
async function loadSkills() {
    const r = await cuba(() => App.api.get('/api/v1/hrms/skills'));
    if (!r) return;
    const d = r.data; SKILLS = d.skills;
    App.$('#sGapNote').textContent = d.gaps ? `${d.gaps} jurang kemahiran berbanding keperluan jawatan.` : 'Tiada jurang kemahiran dikesan.';
    if (!d.skills.length) { App.$('#sMatrix').innerHTML = `<tbody>${kosongTr(1, 'Tambah kemahiran dahulu, cth. Gubahan bunga, Juruwang, Penghantaran.')}</tbody>`; return; }
    const dot = n => `<span class="lvl">${[1, 2, 3, 4].map(i => `<b class="${i <= n ? 'on' : ''}"></b>`).join('')}</span>`;
    App.$('#sMatrix').innerHTML = `<thead><tr><th>Pekerja</th>${d.skills.map(s => `<th class="text-xs">${E(s.name)}</th>`).join('')}</tr></thead><tbody>
        ${d.rows.length ? d.rows.map(r => `<tr><td class="text-sm">${E(r.name)}</td>${d.skills.map(s => { const c = r.skills[s.id];
            return `<td class="${c?.expired ? 'cell-exp' : c?.gap ? 'cell-gap' : ''}" title="${c ? 'Tahap ' + c.level + (c.required ? ', perlu ' + c.required : '') : ''}">${c ? dot(c.level) : '<span class="text-muted">·</span>'}</td>`; }).join('')}</tr>`).join('')
        : kosongTr(d.skills.length + 1, 'Tiada pekerja aktif.')}</tbody>`;
}
async function tambahKemahiran() {
    const d = await borang('Kemahiran', [{ n: 'name', l: 'Nama', req: true }, { n: 'category', l: 'Kategori', ph: 'cth. Teknikal, Servis, Keselamatan' }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/skills', d), 'Kemahiran ditambah')) loadSkills();
}
const TAHAP = [[0, '0 Tiada'], [1, '1 Asas'], [2, '2 Boleh sendiri'], [3, '3 Mahir'], [4, '4 Boleh ajar']];
async function nilaiKemahiran() {
    if (!SKILLS.length) return App.toast('Tambah kemahiran dahulu.', 'error');
    const d = await borang('Nilai kemahiran', [pilihPekerja(), { n: 'skill_id', l: 'Kemahiran', opts: SKILLS.map(s => [s.id, s.name]) }, { n: 'level', l: 'Tahap', opts: TAHAP, v: 2 },
        { n: 'certified_until', l: 'Sijil sah hingga (jika ada)', t: 'date' }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/skills/rate', { ...d, level: Number(d.level) }), 'Disimpan')) loadSkills();
}
async function tetapKeperluan() {
    if (!SKILLS.length) return App.toast('Tambah kemahiran dahulu.', 'error');
    const jaw = [...new Map(PEKERJA.filter(e => e.designation).map(e => [e.designation.id, e.designation.name])).entries()];
    if (!jaw.length) return App.toast('Belum ada jawatan pada rekod pekerja.', 'error');
    const d = await borang('Keperluan jawatan', [{ n: 'designation_id', l: 'Jawatan', opts: jaw }, { n: 'skill_id', l: 'Kemahiran', opts: SKILLS.map(s => [s.id, s.name]) }, { n: 'required_level', l: 'Tahap diperlukan', opts: TAHAP.slice(1), v: 2 }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/skills/require', { ...d, required_level: Number(d.required_level) }), 'Keperluan disimpan')) loadSkills();
}

// ── Suara pekerja ──
let TINJAUAN = [];
async function loadEngagement() {
    const [s, r] = await Promise.all([cuba(() => App.api.get('/api/v1/hrms/engagement/surveys')), cuba(() => App.api.get('/api/v1/hrms/engagement/recognitions'))]);
    TINJAUAN = s?.data ?? [];
    App.$('#gSurveys').innerHTML = TINJAUAN.length ? TINJAUAN.map(x => `<div class="list-row"><div class="text-sm" style="font-weight:600">${E(x.title)}</div><div class="text-sm">${x.responses} jawapan</div>
        <div class="meta">${lencana(STATUS_UMUM, x.status)}${x.anonymous ? '<span class="badge badge-neutral">Tanpa nama</span>' : ''}
        <span class="act">${x.status === 'open' ? `<button class="btn btn-sm" onclick="jawabTinjauan(${x.id})">Jawab</button><button class="btn btn-sm btn-ghost" onclick="tutupTinjauan(${x.id})">Tutup</button>` : ''}
        <button class="btn btn-sm btn-ghost" onclick="keputusanTinjauan(${x.id})">Keputusan</button></span></div></div>`).join('') : kosong('Belum ada tinjauan.');
    const rec = r?.data ?? [];
    App.$('#gRecog').innerHTML = rec.length ? rec.map(x => `<div class="list-row"><div class="text-sm"><b>${E(x.employee_name)}</b>${x.value_tag ? ` <span class="badge badge-neutral">${E(x.value_tag)}</span>` : ''}</div>
        <div class="text-xs text-muted">${tarikh(x.created_at)}</div><div class="meta"><span class="text-sm">${E(x.message)}</span></div></div>`).join('') : kosong('Belum ada penghargaan. Ucapan terima kasih kecil pun bermakna.');
}
async function buatTinjauan() {
    const d = await borang('Tinjauan baharu', [{ n: 'title', l: 'Tajuk', req: true, ph: 'cth. Pulse November' },
        { n: 'skala', l: 'Soalan skala 1–5 (satu baris satu soalan)', t: 'textarea', req: true, v: 'Saya faham apa yang diharapkan daripada saya\nSaya ada alat yang cukup untuk buat kerja\nSaya akan cadangkan tempat ini kepada kawan' },
        { n: 'teks', l: 'Soalan jawapan bebas (pilihan)', t: 'textarea', v: 'Satu perkara yang boleh kita perbaiki' },
        { n: 'anonymous', l: 'Tanpa nama', t: 'checkbox', v: true }, { n: 'closes_on', l: 'Tutup pada', t: 'date' }]);
    if (!d) return;
    const q = [...(d.skala ?? '').split('\n').map(t => ({ text: t.trim(), type: 'scale' })), ...(d.teks ?? '').split('\n').map(t => ({ text: t.trim(), type: 'text' }))].filter(x => x.text);
    if (await cuba(() => App.api.post('/api/v1/hrms/engagement/surveys', { title: d.title, anonymous: d.anonymous, closes_on: d.closes_on, questions: q }), 'Tinjauan dibuka')) loadEngagement();
}
async function jawabTinjauan(id) {
    const s = TINJAUAN.find(x => x.id === id);
    const d = await borang(s.title, s.questions.map(q => q.type === 'scale' ? { n: q.id, l: q.text, opts: [['', '— pilih —'], [5, '5 Sangat setuju'], [4, '4 Setuju'], [3, '3 Neutral'], [2, '2 Tidak setuju'], [1, '1 Sangat tidak setuju']] } : { n: q.id, l: q.text, t: 'textarea' }));
    if (d && await cuba(() => App.api.post(`/api/v1/hrms/engagement/surveys/${id}/respond`, { answers: d }), 'Terima kasih, jawapan direkodkan.')) loadEngagement();
}
async function tutupTinjauan(id) { if (confirm('Tutup tinjauan ini?') && await cuba(() => App.api.post(`/api/v1/hrms/engagement/surveys/${id}/close`), 'Ditutup')) loadEngagement(); }
async function keputusanTinjauan(id) {
    const r = await cuba(() => App.api.get(`/api/v1/hrms/engagement/surveys/${id}/results`));
    if (!r) return;
    const d = r.data;
    App.$('#gResults').innerHTML = `<div class="card"><div class="card-header"><div><h2 class="card-title">${E(TINJAUAN.find(x => x.id === id)?.title)}</h2>
        <p class="card-subtitle">${d.responses} jawapan${d.score ? ' · skor purata ' + d.score + '/5' : ''}</p></div></div>
        ${d.hidden ? `<p class="text-sm">${E(d.message)}</p>` : d.questions.map(q => q.type === 'scale'
            ? `<div class="mb-4"><div class="text-sm">${E(q.text)} <b>${q.average ?? '—'}</b></div><div class="bar mt-2"><i style="width:${(q.average ?? 0) / 5 * 100}%"></i></div></div>`
            : `<div class="mb-4"><div class="text-sm" style="font-weight:600">${E(q.text)}</div>${q.answers.map(a => `<div class="text-sm text-muted">“${E(a)}”</div>`).join('') || '<div class="text-xs text-muted">Tiada jawapan.</div>'}</div>`).join('')}</div>`;
    App.$('#gResults').scrollIntoView({ behavior: 'smooth' });
}
async function beriPenghargaan() {
    const d = await borang('Beri penghargaan', [pilihPekerja(), { n: 'value_tag', l: 'Nilai', opts: [['', '—'], ['Layanan pelanggan', 'Layanan pelanggan'], ['Kerja berpasukan', 'Kerja berpasukan'], ['Inisiatif', 'Inisiatif'], ['Kualiti', 'Kualiti']] },
        { n: 'message', l: 'Mesej', t: 'textarea', req: true }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/engagement/recognitions', d), 'Penghargaan dihantar')) loadEngagement();
}

// ── Faedah ──
let PELAN = [];
async function loadBenefits() {
    const r = await cuba(() => App.api.get('/api/v1/hrms/benefits'));
    if (!r) return;
    PELAN = r.data.plans;
    const JP = { medical: 'Perubatan', dental: 'Pergigian', optical: 'Optik', insurance: 'Insurans', other: 'Lain-lain' };
    App.$('#fPlans').innerHTML = PELAN.length ? PELAN.map(p => `<div class="list-row"><div class="text-sm"><b>${E(p.name)}</b> <span class="badge badge-neutral">${E(JP[p.type] ?? p.type)}</span></div>
        <div class="text-sm">${RM(p.annual_limit)}/tahun</div><div class="meta"><span class="text-xs text-muted">${p.per_claim_limit ? 'Maks ' + RM(p.per_claim_limit) + ' setiap tuntutan · ' : ''}${p.eligible_after_days ? 'layak selepas ' + p.eligible_after_days + ' hari' : 'layak serta-merta'}</span>
        ${p.panel_clinics?.length ? `<span class="text-xs">Panel: ${E(p.panel_clinics.join(', '))}</span>` : ''}</div></div>`).join('') : kosong('Belum ada pelan faedah.');
    const c = r.data.claims;
    App.$('#fClaims').innerHTML = c.length ? c.map(x => `<div class="list-row"><div class="text-sm"><b>${E(x.employee_name)}</b> · ${E(x.plan_name)}</div>
        <div class="text-sm" style="font-weight:600">${RM(x.approved_amount ?? x.amount)}</div>
        <div class="meta">${lencana(STATUS_UMUM, x.status)}<span class="text-xs text-muted">${tarikh(x.claim_date)}${x.provider ? ' · ' + E(x.provider) : ''} · baki ${RM(x.balance)}</span>
        ${x.status === 'pending' ? `<span class="act"><button class="btn btn-sm btn-primary" onclick="putusTuntutan(${x.id}, ${x.amount})">Lulus</button><button class="btn btn-sm btn-danger" onclick="tolakTuntutan(${x.id})">Tolak</button></span>` : ''}</div></div>`).join('')
        : kosong('Tiada tuntutan.');
}
async function tambahPelan() {
    const d = await borang('Pelan faedah', [{ n: 'name', l: 'Nama', req: true, ph: 'cth. Klinik panel' }, { n: 'type', l: 'Jenis', opts: [['medical', 'Perubatan'], ['dental', 'Pergigian'], ['optical', 'Optik'], ['insurance', 'Insurans'], ['other', 'Lain-lain']] },
        { n: 'annual_limit', l: 'Had setahun (RM)', t: 'number', req: true }, { n: 'per_claim_limit', l: 'Had setiap tuntutan (RM)', t: 'number' },
        { n: 'eligible_after_days', l: 'Layak selepas (hari bekerja)', t: 'number', v: 0 }, { n: 'panel', l: 'Klinik panel (asingkan dengan koma)' }]);
    if (!d) return;
    if (d.panel) d.panel_clinics = d.panel.split(',').map(s => s.trim()).filter(Boolean);
    delete d.panel;
    if (await cuba(() => App.api.post('/api/v1/hrms/benefits/plans', d), 'Pelan disimpan')) loadBenefits();
}
async function buatTuntutan() {
    if (!PELAN.length) return App.toast('Tambah pelan faedah dahulu.', 'error');
    const d = await borang('Tuntutan faedah', [pilihPekerja(), { n: 'plan_id', l: 'Pelan', opts: PELAN.filter(p => p.is_active).map(p => [p.id, p.name]) },
        { n: 'claim_date', l: 'Tarikh rawatan', t: 'date', v: hariIni() }, { n: 'amount', l: 'Amaun (RM)', t: 'number', req: true },
        { n: 'provider', l: 'Klinik / penyedia' }, { n: 'receipt_ref', l: 'No. resit' }]);
    if (d && await cuba(() => App.api.post('/api/v1/hrms/benefits/claims', d), 'Tuntutan dihantar')) loadBenefits();
}
async function putusTuntutan(id, amt) {
    const d = await borang('Lulus tuntutan', [{ n: 'approved_amount', l: 'Amaun lulus (RM)', t: 'number', v: amt }, { n: 'notes', l: 'Nota' }]);
    if (d && await cuba(() => App.api.post(`/api/v1/hrms/benefits/claims/${id}/decide`, { approve: true, ...d }), 'Tuntutan diluluskan')) loadBenefits();
}
async function tolakTuntutan(id) {
    const d = await borang('Tolak tuntutan', [{ n: 'notes', l: 'Sebab', req: true }]);
    if (d && await cuba(() => App.api.post(`/api/v1/hrms/benefits/claims/${id}/decide`, { approve: false, ...d }), 'Tuntutan ditolak')) loadBenefits();
}

// ── Masuk & keluar ──
async function loadLifecycle() {
    const t = App.$('#lType').value;
    const r = await cuba(() => App.api.get('/api/v1/hrms/lifecycle' + (t ? '?type=' + t : '')));
    if (!r) return;
    const g = r.data.groups;
    App.$('#lGroups').innerHTML = g.length ? g.map(x => `<div class="card">
        <div class="row-between" style="flex-wrap:wrap;gap:8px"><div><div style="font-weight:600">${E(x.employee_name)}</div>
            <div class="text-xs text-muted">${x.type === 'onboarding' ? 'Masuk kerja' : 'Keluar kerja'} · ${x.done}/${x.total} siap${x.overdue ? ` · <span style="color:var(--danger,#c0392b)">${x.overdue} lewat</span>` : ''}</div></div>
            ${x.type === 'offboarding' ? `<button class="btn btn-sm btn-ghost" onclick="temuBualKeluar(${x.employee_id})">Temu bual keluar</button>` : ''}</div>
        <div class="bar mt-2 mb-2"><i style="width:${x.total ? x.done / x.total * 100 : 0}%"></i></div>
        ${x.tasks.map(k => `<label class="task"><input type="checkbox" ${k.done_at ? 'checked' : ''} onchange="tandaTugas(${k.id})">
            <span class="text-sm" style="${k.done_at ? 'text-decoration:line-through;opacity:.6' : ''}">${E(k.title)}<span class="text-xs text-muted"> · ${E(k.owner ?? '')} · ${tarikh(k.due_on)}</span></span></label>`).join('')}
    </div>`).join('') : `<div class="card">${kosong('Tiada senarai semak. Ia bermula automatik bila calon diambil bekerja atau pekerja diproses keluar.')}</div>`;
}
async function tandaTugas(id) { if (await cuba(() => App.api.post(`/api/v1/hrms/lifecycle/tasks/${id}/toggle`))) loadLifecycle(); }
async function mulaMasuk(id) { if (await cuba(() => App.api.post(`/api/v1/hrms/lifecycle/${id}/onboarding`), 'Senarai semak masuk kerja dimulakan')) { delete dimuat.lifecycle; bukaTab('lifecycle'); } }
async function prosesKeluar() {
    const d = await borang('Proses pekerja keluar', [pilihPekerja(), { n: 'exit_type', l: 'Jenis', opts: [['resigned', 'Letak jawatan'], ['terminated', 'Diberhentikan']] },
        { n: 'last_worked_date', l: 'Hari terakhir bekerja', t: 'date', req: true, v: hariIni() }, { n: 'reason', l: 'Sebab' }]);
    if (!d || !confirm('Akses log masuk pekerja ini akan dilumpuhkan pada hari terakhir. Teruskan?')) return;
    if (await cuba(() => App.api.post(`/api/v1/hrms/lifecycle/${d.employee_id}/offboarding`, d), 'Proses keluar dimulakan')) { await muatPekerja(); loadLifecycle(); delete dimuat.employees; }
}
async function temuBualKeluar(id) {
    const d = await borang('Temu bual keluar', [{ n: 'reason', l: 'Sebab utama', opts: [['kerjaya', 'Peluang kerjaya'], ['gaji', 'Gaji'], ['pengurus', 'Pengurus'], ['beban', 'Beban kerja'], ['peribadi', 'Peribadi'], ['lain', 'Lain-lain']] },
        { n: 'would_rehire', l: 'Akan ambil semula', t: 'checkbox' }, { n: 'feedback', l: 'Maklum balas', t: 'textarea' }]);
    if (d && await cuba(() => App.api.post(`/api/v1/hrms/lifecycle/${id}/exit-interview`, d), 'Disimpan')) loadLifecycle();
}

(async () => {
    if (!(await App.loadSession())) { location.href = '/login'; return; }
    await muatPekerja();
    const n = location.hash.slice(1);
    bukaTab(pemuat[n] ? n : 'brief');
})();
</script>
@endpush
