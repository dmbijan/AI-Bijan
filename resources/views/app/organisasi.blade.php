@extends('layouts.app')

@section('title', 'Organisasi & Akses')

@section('content')
<style>
    .tabs { scrollbar-width: none; }
    [data-panel] .stat { min-width: 0; }
    [data-panel] .stat-value { white-space: nowrap; font-size: clamp(1.05rem, 4.6vw, 1.6rem); font-variant-numeric: tabular-nums; }
    .list-row { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; padding: 12px 16px; border-bottom: 1px solid var(--border, #e5e5e5); }
    .list-row:last-child { border-bottom: 0; }
    .list-row .meta { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; }
    .list-row .act { margin-left: auto; display: flex; gap: 6px; flex-wrap: wrap; }
    .tree-node { display: flex; align-items: center; gap: 8px; padding: 10px 16px; border-bottom: 1px solid var(--border, #e5e5e5); flex-wrap: wrap; }
    .tree-node:last-child { border-bottom: 0; }
    .tree-type { font-size: .7rem; text-transform: uppercase; letter-spacing: .05em; padding: 2px 6px; border-radius: 4px; background: var(--surface-2, rgba(127,127,127,.1)); }
    .archived { opacity: .5; }
    .bar { height: 8px; border-radius: 4px; background: var(--border, #e5e5e5); overflow: hidden; }
    .bar > i { display: block; height: 100%; background: var(--primary, #b7791f); }
    .mx { width: 100%; border-collapse: collapse; }
    .mx th, .mx td { padding: 8px 10px; border-bottom: 1px solid var(--border, #e5e5e5); text-align: left; vertical-align: top; }
    .mx th { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; }
    .pill { display: inline-flex; align-items: center; gap: 4px; border: 1px solid var(--border, #ddd); border-radius: 999px; padding: 4px 10px; margin: 2px; font-size: .8rem; cursor: pointer; background: transparent; color: inherit; min-height: 32px; }
    .pill[data-s="grant"] { background: rgba(39, 174, 96, .14); border-color: rgba(39, 174, 96, .5); }
    .pill[data-s="deny"] { background: rgba(192, 57, 43, .14); border-color: rgba(192, 57, 43, .5); text-decoration: line-through; }
    .pill[data-s="inherit"] { background: rgba(46, 134, 193, .1); border-style: dashed; }
    .pill:focus-visible { outline: 2px solid var(--primary, #b7791f); outline-offset: 2px; }
    .locked { opacity: .55; }
    .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; }
    .dot-on { background: var(--success, #27ae60); } .dot-off { background: var(--border, #ccc); }
    .sticky-actions { position: sticky; bottom: 0; background: var(--surface, #fff); border-top: 1px solid var(--border, #e5e5e5); padding: 12px 16px; display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    @media (max-width: 640px) { [data-panel] .grid-4 { grid-template-columns: 1fr 1fr; } [data-panel] .grid-2, [data-panel] .grid-3 { grid-template-columns: 1fr; } }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">Organisasi & Akses</h1>
        <p class="page-desc">Syarikat, cawangan, ahli, peranan, kebenaran, seat dan keselamatan akaun.</p>
    </div>
    <button class="btn btn-sm" onclick="muatSemula()">Muat semula</button>
</div>

<div class="tabs" role="tablist" style="overflow-x:auto">
    <button class="tab" role="tab" aria-selected="true" data-tab="overview">Ringkasan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="structure">Struktur</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="members">Ahli & staf</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="invites">Jemputan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="roles">Peranan & kebenaran</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="modules">Akses modul</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="seats">Langganan & seat</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="security">Keselamatan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="audit">Log audit</button>
</div>

<section data-panel="overview">
    <div class="grid grid-4 mb-4" id="oStats"></div>
    <div class="grid grid-2">
        <div class="card"><div class="card-header"><h2 class="card-title">Seat pengguna</h2></div><div id="oSeat"></div></div>
        <div class="card"><div class="card-header"><h2 class="card-title">Langganan</h2></div><div id="oSub"></div></div>
    </div>
    <p class="text-xs text-muted mt-4">Langganan menentukan apa yang syarikat miliki. Peranan menentukan apa yang setiap orang boleh buat.</p>
</section>

<section data-panel="structure" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <p class="text-sm text-muted" style="margin:0;max-width:60ch">Kumpulan → Syarikat → Cawangan → Jabatan → Pasukan. Kedai kecil cukup satu syarikat dan satu cawangan. Unit tidak dipadam, hanya diarkib.</p>
        <button class="btn btn-primary btn-sm" onclick="tambahUnit()">+ Unit</button>
    </div>
    <div class="card" style="padding:0"><div id="sTree"></div></div>
    <div class="card mt-4" style="padding:0">
        <div class="card-header" style="padding:16px 16px 0"><div><h2 class="card-title">Tautkan ke unit</h2>
            <p class="card-subtitle">Tetapkan gudang, kaunter POS dan pekerja milik cawangan/jabatan mana. Ahli berskop cawangan hanya nampak rekod unitnya. Rekod tanpa unit hanya dilihat oleh ahli berskop seluruh organisasi.</p></div></div>
        <div id="sLinks"></div>
    </div>
</section>

<section data-panel="members" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <div class="row" style="gap:8px;flex-wrap:wrap">
            <input class="input" id="mCari" placeholder="Cari nama atau emel" style="max-width:240px" oninput="lukisAhli()">
            <select class="select" id="mState" onchange="lukisAhli()" aria-label="Status"><option value="">Semua status</option><option value="active">Aktif</option><option value="invited">Dijemput</option><option value="suspended">Digantung</option><option value="revoked">Ditarik balik</option><option value="expired">Tamat</option></select>
        </div>
        <button class="btn btn-primary btn-sm" onclick="jemput()">+ Jemput</button>
    </div>
    <div class="card" style="padding:0"><div id="mList"></div></div>
</section>

<section data-panel="invites" hidden>
    <div class="row mb-4" style="justify-content:flex-end"><button class="btn btn-primary btn-sm" onclick="jemput()">+ Jemput</button></div>
    <p class="text-sm text-muted mb-4">Jemputan tidak memakai seat sehingga diterima. Pautan sah 7 hari.</p>
    <div class="card" style="padding:0"><div id="iList"></div></div>
</section>

<section data-panel="roles" hidden>
    <div class="grid grid-3" style="grid-template-columns: minmax(220px, 300px) 1fr; align-items:start">
        <div class="card" style="padding:0">
            <div class="row-between" style="padding:12px 16px"><h2 class="card-title" style="margin:0">Peranan</h2><button class="btn btn-sm" onclick="peranBaru()">+ Baharu</button></div>
            <div id="rList"></div>
        </div>
        <div class="card" style="padding:0" id="rEditor">
            <div style="padding:24px" class="text-sm text-muted">Pilih peranan untuk lihat atau ubah kebenarannya.</div>
        </div>
    </div>
</section>

<section data-panel="modules" hidden>
    <p class="text-sm text-muted mb-4">Modul yang tidak dilanggan tetap disekat walaupun peranan ada kebenarannya. ● ada akses · ○ tiada</p>
    <div class="card" style="padding:0;overflow-x:auto"><table class="mx" id="modTable"></table></div>
</section>

<section data-panel="seats" hidden>
    <div class="grid grid-4 mb-4" id="seStats"></div>
    <div class="card mb-4"><div class="card-header"><h2 class="card-title">Penggunaan seat</h2></div><div id="seBar"></div></div>
    <div class="card"><div class="card-header"><div><h2 class="card-title">Had pakej</h2><p class="card-subtitle">Tambah seat atau naik taraf pakej melalui pasukan AI Bijan.</p></div></div><div id="seLimits"></div></div>
</section>

<section data-panel="security" hidden>
    <div class="card mb-4" style="padding:0">
        <div class="card-header" style="padding:16px 16px 0"><div><h2 class="card-title">Sesi log masuk anda</h2><p class="card-subtitle">Tamatkan sesi pada peranti yang anda tidak kenal.</p></div></div>
        <div id="secSessions"></div>
    </div>
    <div class="card">
        <div class="card-header"><h2 class="card-title">Semak akses</h2></div>
        <p class="text-sm text-muted">Uji sama ada seseorang boleh buat sesuatu, dan kenapa.</p>
        <div class="row" style="gap:8px;flex-wrap:wrap;align-items:end">
            <div class="field" style="margin:0"><label class="label" for="cMember">Ahli</label><select class="select" id="cMember"></select></div>
            <div class="field" style="margin:0"><label class="label" for="cPerm">Kebenaran</label><input class="input" id="cPerm" list="permList" placeholder="cth. finance.approve"></div>
            <div class="field" style="margin:0"><label class="label" for="cAmt">Amaun (RM)</label><input class="input" id="cAmt" type="number" style="max-width:120px"></div>
            <button class="btn btn-primary btn-sm" onclick="semakAkses()">Semak</button>
        </div>
        <datalist id="permList"></datalist>
        <div id="cResult" class="mt-4"></div>
    </div>
</section>

<section data-panel="audit" hidden>
    <div class="row mb-4" style="gap:8px;flex-wrap:wrap">
        <select class="select" id="aFilter" onchange="loadAudit()" aria-label="Jenis"><option value="">Semua</option><option value="members">Ahli</option><option value="roles">Peranan</option><option value="org">Struktur</option><option value="auth">Log masuk</option><option value="hrms.compensation">Gaji</option><option value="finance">Kewangan</option></select>
    </div>
    <div class="card" style="padding:0"><div id="aList"></div></div>
</section>

<dialog class="dialog" id="dlg">
    <form method="dialog">
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
const E = s => App.escapeHtml(String(s ?? ''));
const tarikh = d => d ? new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(d)) : '—';
const masa = d => d ? new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' }).format(new Date(d)) : '—';
const kosong = t => `<div class="empty" style="padding:24px"><div class="empty-text">${E(t)}</div></div>`;
const JENIS = { group: 'Kumpulan', company: 'Syarikat', branch: 'Cawangan', department: 'Jabatan', team: 'Pasukan' };
const ANAK = { '': ['group', 'company'], group: ['company'], company: ['branch', 'department'], branch: ['department', 'team'], department: ['department', 'team'], team: [] };
const STATE = { active: ['Aktif', 'success'], invited: ['Dijemput', 'info'], suspended: ['Digantung', 'warning'], revoked: ['Ditarik balik', 'danger'], expired: ['Tamat', 'neutral'] };
const lencana = (map, k) => { const [t, c] = map[k] ?? [k ?? '—', 'neutral']; return `<span class="badge badge-${c}">${E(t)}</span>`; };
const AKSI = { view: 'Lihat', manage: 'Urus', approve: 'Lulus', reports: 'Laporan', export: 'Eksport', execute: 'Jalankan', create: 'Cipta', configure: 'Tetapan', sell: 'Jual', audit: 'Audit', post: 'Pos', sign: 'Tandatangan',
    issue: 'Keluarkan', void: 'Batal', sensitive: 'Sensitif', decide: 'Putuskan', invite: 'Jemput', upload: 'Muat naik', update: 'Ubah', timesheet: 'Masa kerja', participate: 'Sertai', kill_switch: 'Suis henti',
    use: 'Guna', wallet: 'Dompet', models: 'Model', admin: 'Admin', finance: 'Kewangan', shipping: 'Penghantaran', channels: 'Saluran', insights: 'Wawasan', forecasts: 'Ramalan', recommendations: 'Cadangan', tasks: 'Tugasan', memory: 'Memori' };
const aksi = k => { const a = k.split('.').slice(1).join('.'); return AKSI[a] ?? a; };
const dimuat = {};
let UNITS = [], MEMBERS = [], ROLES = [], CATALOG = [], LIMITS = {}, SCOPES = {};

const pemuat = { overview: loadOverview, structure: loadUnits, members: loadMembers, invites: loadInvites, roles: loadRoles, modules: loadModules, seats: loadSeats, security: loadSecurity, audit: loadAudit };
function bukaTab(n) {
    document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', String(t.dataset.tab === n)));
    document.querySelector(`[data-tab="${n}"]`)?.scrollIntoView({ inline: 'nearest', block: 'nearest' });
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== n);
    if (!dimuat[n]) { dimuat[n] = true; pemuat[n](); }
    history.replaceState(null, '', '#' + n);
}
document.querySelectorAll('[data-tab]').forEach(t => t.addEventListener('click', () => bukaTab(t.dataset.tab)));
window.addEventListener('hashchange', () => { const n = location.hash.slice(1); if (pemuat[n]) bukaTab(n); });
function muatSemula() { Object.keys(dimuat).forEach(k => delete dimuat[k]); bukaTab(pemuat[location.hash.slice(1)] ? location.hash.slice(1) : 'overview'); }

function borang(tajuk, medan) {
    const dlg = App.$('#dlg');
    App.$('#dlgTitle').textContent = tajuk;
    App.$('#dlgBody').innerHTML = medan.map(m => m.html ?? `
        <div class="field"><label class="label" for="f_${m.n}">${E(m.l)}</label>
        ${m.opts ? `<select class="select" id="f_${m.n}" ${m.multi ? 'multiple size="5"' : ''}>${m.opts.map(o => `<option value="${E(o[0])}" ${[].concat(m.v ?? []).map(String).includes(String(o[0])) ? 'selected' : ''}>${E(o[1])}</option>`).join('')}</select>`
          : `<input class="input" id="f_${m.n}" type="${m.t || 'text'}" ${m.req ? 'required' : ''} value="${E(m.v ?? '')}" placeholder="${E(m.ph ?? '')}">`}
        ${m.hint ? `<div class="field-hint">${E(m.hint)}</div>` : ''}</div>`).join('');
    dlg.showModal();
    return new Promise(res => {
        dlg.onclose = () => {
            if (dlg.returnValue !== 'ok') return res(null);
            const out = {};
            medan.filter(m => m.n).forEach(m => {
                const el = App.$('#f_' + m.n);
                if (m.multi) { out[m.n] = [...el.selectedOptions].map(o => o.value); return; }
                const v = el.value.trim(); if (v !== '') out[m.n] = v;
            });
            res(out);
        };
    });
}
async function cuba(fn, ok) {
    try { const r = await fn(); if (ok) App.toast(ok, 'success'); return r; }
    catch (e) { App.toast(e.message, 'error'); return null; }
}
const namaUnit = id => UNITS.find(u => u.id === id)?.name ?? '—';
async function muatUnit() { const r = await cuba(() => App.api.get('/api/v1/org/units')); UNITS = r?.data ?? []; }
async function muatPeranan() { const r = await cuba(() => App.api.get('/api/v1/roles')); ROLES = r?.roles ?? []; CATALOG = r?.catalog ?? []; LIMITS = r?.limit_keys ?? {}; }

// ── Ringkasan ──
async function loadOverview() {
    const r = await cuba(() => App.api.get('/api/v1/org/overview'));
    if (!r) return;
    const d = r.data, m = d.members, s = d.seats;
    App.$('#oStats').innerHTML = [
        ['Syarikat', d.units.company || 1], ['Cawangan', d.units.branch], ['Staf aktif', m.active ?? 0], ['Jemputan menunggu', m.invited ?? 0],
        ['Jabatan', d.units.department], ['Peranan', d.roles], ['Modul aktif', d.subscription.jumlah_modul_aktif], ['Kelulusan menunggu', d.pending_approvals],
    ].map(([l, v]) => `<div class="stat"><div class="stat-label">${l}</div><div class="stat-value">${App.number(v ?? 0)}</div></div>`).join('');
    App.$('#oSeat').innerHTML = seatBar(s);
    const L = d.subscription.langganan, P = d.subscription.plan;
    App.$('#oSub').innerHTML = P ? `<div class="text-sm"><b>${E(P.name)}</b> ${L ? lencana({ trial: ['Percubaan', 'info'], active: ['Aktif', 'success'], past_due: ['Tertunggak', 'warning'], suspended: ['Digantung', 'danger'], cancelled: ['Dibatalkan', 'neutral'] }, L.status) : ''}</div>
        <div class="text-sm text-muted mt-2">${L?.ends_at ? 'Tamat ' + tarikh(L.ends_at) : ''}</div>
        <div class="text-xs text-muted mt-2">Data syarikat tidak dipadam bila langganan tamat; akses jadi terhad.</div>` : kosong('Tiada langganan.');
}
function seatBar(s) {
    if (s.unlimited) return `<div class="text-sm"><b>${s.used}</b> pengguna aktif · seat tanpa had</div>`;
    const tot = s.included + s.addon, pct = tot ? Math.min(100, s.used / tot * 100) : 0;
    return `<div class="row-between text-sm mb-2"><span><b>${s.used}</b> / ${tot} digunakan</span><span>${s.available} kosong</span></div>
        <div class="bar"><i style="width:${pct}%;${pct >= 100 ? 'background:var(--danger,#c0392b)' : ''}"></i></div>
        <div class="text-xs text-muted mt-2">${s.pending_invites} jemputan belum diterima (tidak dikira).${pct >= 100 ? ' Seat penuh: jemputan baharu tidak boleh diterima.' : ''}</div>`;
}

// ── Struktur ──
async function loadUnits() {
    await muatUnit();
    const kids = p => UNITS.filter(u => (u.parent_id ?? null) === p);
    const baris = (u, d) => `<div class="tree-node ${u.status === 'archived' ? 'archived' : ''}" style="padding-left:${16 + d * 22}px">
        <span class="tree-type">${JENIS[u.type]}</span><span class="text-sm" style="font-weight:600">${E(u.name)}</span>
        ${u.code ? `<span class="mono text-xs text-muted">${E(u.code)}</span>` : ''}${u.registration_number ? `<span class="text-xs text-muted">SSM ${E(u.registration_number)}</span>` : ''}
        <span class="text-xs text-muted">${u.members} ahli</span>
        <span style="margin-left:auto;display:flex;gap:6px;flex-wrap:wrap">
            ${ANAK[u.type].length && u.status === 'active' ? `<button class="btn btn-sm btn-ghost" onclick="tambahUnit(${u.id})">+ Di bawah</button>` : ''}
            <button class="btn btn-sm btn-ghost" onclick="ubahUnit(${u.id})">Ubah</button>
            <button class="btn btn-sm btn-ghost" onclick="arkibUnit(${u.id})">${u.status === 'active' ? 'Arkib' : 'Pulih'}</button></span></div>` + kids(u.id).map(c => baris(c, d + 1)).join('');
    App.$('#sTree').innerHTML = UNITS.length ? kids(null).map(u => baris(u, 0)).join('') : kosong('Belum ada struktur. Mula dengan tambah syarikat anda.');
    loadLinks();
}
function medanUnit(u, jenisOpts) {
    return [{ n: 'type', l: 'Jenis', opts: jenisOpts.map(t => [t, JENIS[t]]), v: u?.type }, { n: 'name', l: 'Nama', req: true, v: u?.name },
        { n: 'code', l: 'Kod (pilihan)', v: u?.code, ph: 'cth. SA' }, { n: 'registration_number', l: 'No. pendaftaran SSM (syarikat)', v: u?.registration_number },
        { n: 'address', l: 'Alamat', v: u?.address }];
}
async function tambahUnit(parentId) {
    const p = UNITS.find(u => u.id === parentId);
    const d = await borang(p ? 'Unit di bawah ' + p.name : 'Unit baharu', medanUnit(null, ANAK[p?.type ?? '']));
    if (d && await cuba(() => App.api.post('/api/v1/org/units', { ...d, parent_id: parentId ?? null }), 'Unit ditambah')) { loadUnits(); delete dimuat.overview; }
}
async function ubahUnit(id) {
    const u = UNITS.find(x => x.id === id);
    const d = await borang('Ubah ' + u.name, medanUnit(u, [u.type]).slice(1));
    if (d && await cuba(() => App.api.put(`/api/v1/org/units/${id}`, d), 'Disimpan')) loadUnits();
}
async function loadLinks() {
    const r = await cuba(() => App.api.get('/api/v1/org/links'));
    if (!r) { App.$('#sLinks').innerHTML = kosong('Hanya ahli berskop seluruh organisasi boleh menautkan unit.'); return; }
    const opts = sel => '<option value="">— tiada (ibu pejabat) —</option>' + UNITS.filter(u => u.status === 'active').map(u => `<option value="${u.id}" ${u.id === sel ? 'selected' : ''}>${E(JENIS[u.type] + ': ' + u.name)}</option>`).join('');
    App.$('#sLinks').innerHTML = Object.entries(r.data).map(([type, g]) => `<div style="padding:12px 16px"><h3 class="text-sm" style="font-weight:600;margin:0 0 6px">${E(g.label)} (${g.rows.length})</h3>
        ${g.rows.length ? g.rows.map(x => `<div class="row-between" style="gap:8px;flex-wrap:wrap;padding:6px 0;border-bottom:1px solid var(--border,#eee)">
            <span class="text-sm">${E(x.name)} ${x.code ? `<span class="mono text-xs text-muted">${E(x.code)}</span>` : ''}${x.hint ? ` <span class="text-xs text-muted">(${E(x.hint)})</span>` : ''}</span>
            <select class="select" style="max-width:280px" aria-label="Unit untuk ${E(x.name)}" onchange="tautkan('${type}', ${x.id}, this.value)">${opts(x.org_unit_id)}</select></div>`).join('') : '<div class="text-xs text-muted">Tiada.</div>'}</div>`).join('');
}
async function tautkan(type, id, unit) {
    await cuba(() => App.api.put('/api/v1/org/links', { type, ids: [id], org_unit_id: unit ? Number(unit) : null }), 'Ditautkan');
}
async function arkibUnit(id) { if (await cuba(() => App.api.post(`/api/v1/org/units/${id}/archive`), 'Dikemas kini')) loadUnits(); }

// ── Ahli ──
async function loadMembers() {
    const [r] = await Promise.all([cuba(() => App.api.get('/api/v1/org/members')), muatUnit(), ROLES.length ? null : muatPeranan()]);
    MEMBERS = r?.data ?? []; SCOPES = r?.meta?.data_scopes ?? {};
    lukisAhli();
}
function lukisAhli() {
    const q = App.$('#mCari').value.toLowerCase(), st = App.$('#mState').value;
    const rows = MEMBERS.filter(m => (!q || (m.name + m.email).toLowerCase().includes(q)) && (!st || m.state === st));
    App.$('#mList').innerHTML = rows.length ? rows.map(m => `<div class="list-row">
        <div class="text-sm"><b>${E(m.name)}</b>${m.is_me ? ' <span class="text-xs text-muted">(anda)</span>' : ''}<br><span class="text-xs text-muted">${E(m.email)}${m.employee_no ? ' · ' + E(m.employee_no) : ''}</span></div>
        <div>${lencana(STATE, m.state)}</div>
        <div class="meta">${m.roles.map(r => `<span class="badge badge-neutral">${E(r.name)}</span>`).join('')}
            <span class="text-xs text-muted">${m.org_unit ? E(m.org_unit) + ' · ' : ''}${E(SCOPES[m.data_scope] ?? m.data_scope)}${m.expires_at ? ' · tamat ' + tarikh(m.expires_at) : ''}</span>
            <span class="text-xs text-muted">Aktif terakhir ${m.last_active_at ? masa(m.last_active_at) : '—'}</span>
            ${m.status_reason ? `<span class="text-xs">“${E(m.status_reason)}”</span>` : ''}
            <span class="act">${m.state !== 'revoked' ? `<button class="btn btn-sm" onclick="tukarPeranan(${m.id})">Peranan</button><button class="btn btn-sm btn-ghost" onclick="tukarSkop(${m.id})">Skop</button>` : ''}
                <button class="btn btn-sm btn-ghost" onclick="lihatAkses(${m.id})">Akses</button>
                ${m.is_me ? '' : m.state === 'active' || m.state === 'expired' ? `<button class="btn btn-sm btn-ghost" onclick="tukarStatus(${m.id}, 'suspend')">Gantung</button>`
                    : m.state === 'suspended' ? `<button class="btn btn-sm btn-ghost" onclick="tukarStatus(${m.id}, 'reactivate')">Aktifkan</button>` : ''}
                ${!m.is_me && !['revoked', 'invited'].includes(m.state) ? `<button class="btn btn-sm btn-danger" onclick="tukarStatus(${m.id}, 'revoke')">Tarik balik</button>` : ''}</span></div>
    </div>`).join('') : kosong('Tiada ahli sepadan.');
}
async function jemput() {
    if (!ROLES.length) await muatPeranan();
    const d = await borang('Jemput ahli', [{ n: 'email', l: 'Emel', t: 'email', req: true }, { n: 'roles', l: 'Peranan (tekan Ctrl untuk pilih lebih)', opts: ROLES.map(r => [r.key, r.name]), multi: true, v: ['staff'] }]);
    if (!d) return;
    const r = await cuba(() => App.api.post('/api/v1/members/invite', d));
    if (r) { await salinPautan(r.invite_url); delete dimuat.members; delete dimuat.invites; delete dimuat.overview; bukaTab('invites'); }
}
async function salinPautan(url) {
    try { await navigator.clipboard.writeText(url); App.toast('Pautan jemputan disalin. Hantar sendiri melalui WhatsApp atau emel.', 'success'); }
    catch { prompt('Salin pautan jemputan ini:', url); }
}
async function tukarPeranan(id) {
    const m = MEMBERS.find(x => x.id === id);
    const d = await borang('Peranan ' + m.name, [{ n: 'roles', l: 'Peranan (tekan Ctrl untuk pilih lebih)', opts: ROLES.map(r => [r.key, r.name]), multi: true, v: m.roles.map(r => r.key),
        hint: 'Anda hanya boleh memberi kebenaran yang anda sendiri ada.' }]);
    if (d?.roles?.length && await cuba(() => App.api.put(`/api/v1/members/${id}`, { roles: d.roles }), 'Peranan dikemas kini')) loadMembers();
}
async function tukarSkop(id) {
    const m = MEMBERS.find(x => x.id === id);
    const uo = UNITS.filter(u => u.status === 'active').map(u => [u.id, JENIS[u.type] + ': ' + u.name]);
    const d = await borang('Skop data ' + m.name, [
        { n: 'data_scope', l: 'Boleh lihat', opts: Object.entries(SCOPES), v: m.data_scope },
        { n: 'org_unit_id', l: 'Unit utama', opts: [['', '— tiada —'], ...uo], v: m.org_unit_id },
        { n: 'selected_unit_ids', l: 'Unit terpilih (untuk "Unit terpilih")', opts: uo, multi: true, v: m.selected_unit_ids },
        { n: 'expires_at', l: 'Akses tamat (pilihan, cth. pekerja sementara)', t: 'date', v: m.expires_at }, { n: 'employee_no', l: 'No. pekerja', v: m.employee_no }]);
    if (!d) return;
    if (!d.selected_unit_ids?.length) delete d.selected_unit_ids; else d.selected_unit_ids = d.selected_unit_ids.map(Number);
    if (d.org_unit_id) d.org_unit_id = Number(d.org_unit_id);
    if (await cuba(() => App.api.put(`/api/v1/org/members/${id}/scope`, d), 'Skop disimpan')) loadMembers();
}
async function tukarStatus(id, a) {
    const m = MEMBERS.find(x => x.id === id);
    const teks = { suspend: `Gantung akses ${m.name}? Dia tidak boleh log masuk ke syarikat ini sehingga diaktifkan semula.`, revoke: `Tarik balik akses ${m.name} secara kekal? Perlu jemput semula jika mahu kembali.`, reactivate: `Aktifkan semula ${m.name}?` }[a];
    let body = {};
    if (a !== 'reactivate') { const d = await borang(a === 'revoke' ? 'Tarik balik akses' : 'Gantung akses', [{ html: `<p class="text-sm">${E(teks)}</p>` }, { n: 'reason', l: 'Sebab (pilihan)' }]); if (!d) return; body = d; }
    else if (!confirm(teks)) return;
    if (await cuba(() => App.api.post(`/api/v1/org/members/${id}/${a}`, body), 'Status dikemas kini')) { loadMembers(); delete dimuat.overview; delete dimuat.seats; }
}
async function lihatAkses(id) {
    const r = await cuba(() => App.api.get(`/api/v1/org/members/${id}/effective`));
    if (!r) return;
    const d = r.data, g = {};
    d.permissions.forEach(p => (g[p.split('.')[0]] ??= []).push(aksi(p)));
    await borang('Akses berkesan', [{ html: `<div class="text-sm">${lencana(STATE, d.state)} ${d.inherited_from.length ? ' · mewarisi ' + E(d.inherited_from.join(', ')) : ''}</div>
        <div class="text-sm mt-2">Skop unit: ${d.visible_unit_ids === null ? 'seluruh organisasi' : d.visible_unit_ids.length ? E(d.visible_unit_ids.map(namaUnit).join(', ')) : 'tiada'}</div>
        ${Object.keys(d.limits).length ? `<div class="text-sm mt-2">Had: ${Object.entries(d.limits).map(([k, v]) => E(LIMITS[k] ?? k) + ' ' + App.rupiah(v)).join(' · ')}</div>` : ''}
        ${d.denied.length ? `<div class="text-sm mt-2" style="color:var(--danger,#c0392b)">Ditolak: ${E(d.denied.join(', '))}</div>` : ''}
        <div class="mt-2" style="max-height:45vh;overflow:auto">${Object.entries(g).map(([mod, a]) => `<div class="text-sm" style="padding:4px 0"><b>${E(mod)}</b> <span class="text-muted">${E(a.join(', '))}</span></div>`).join('') || kosong('Tiada kebenaran.')}</div>` }]);
}

// ── Jemputan ──
async function loadInvites() {
    const r = await cuba(() => App.api.get('/api/v1/org/invitations'));
    const rows = r?.data ?? [];
    App.$('#iList').innerHTML = rows.length ? rows.map(x => `<div class="list-row"><div class="text-sm"><b>${E(x.email)}</b></div>
        <div>${x.expired ? '<span class="badge badge-neutral">Tamat</span>' : '<span class="badge badge-info">Menunggu</span>'}</div>
        <div class="meta"><span class="text-xs text-muted">${E(x.roles.join(', '))} · ${x.expires_at ? 'sah hingga ' + tarikh(x.expires_at) : ''}</span>
        <span class="act"><button class="btn btn-sm" onclick="hantarSemula(${x.id})">Pautan baharu</button><button class="btn btn-sm btn-ghost" onclick="batalJemputan(${x.id})">Batal</button></span></div></div>`).join('')
        : kosong('Tiada jemputan menunggu.');
}
async function hantarSemula(id) { const r = await cuba(() => App.api.post(`/api/v1/org/invitations/${id}/resend`)); if (r) { await salinPautan(r.data.invite_url); loadInvites(); } }
async function batalJemputan(id) { if (confirm('Batalkan jemputan ini?') && await cuba(() => App.api.post(`/api/v1/org/invitations/${id}/cancel`), 'Jemputan dibatalkan')) loadInvites(); }

// ── Peranan & kebenaran ──
let EDIT = null;
async function loadRoles() { await muatPeranan(); lukisPeranan(); if (EDIT) bukaPeranan(EDIT.id); }
function lukisPeranan() {
    App.$('#rList').innerHTML = ROLES.map(r => `<button class="list-row" style="width:100%;text-align:left;background:${EDIT?.id === r.id ? 'var(--surface-2, rgba(127,127,127,.08))' : 'transparent'};border-left:0;border-right:0;border-top:0;cursor:pointer;color:inherit" onclick="bukaPeranan(${r.id})">
        <span class="text-sm"><b>${E(r.name)}</b>${r.is_system ? ' <span class="text-xs text-muted">templat</span>' : ''}<br><span class="text-xs text-muted">${r.effective.length} kebenaran${r.parent_id ? ' · mewarisi' : ''}</span></span>
        <span class="text-xs text-muted">${r.members} orang</span></button>`).join('');
}
function bukaPeranan(id) {
    const r = ROLES.find(x => x.id === id);
    if (!r) return;
    const induk = ROLES.find(x => x.id === r.parent_id);
    const warisan = induk ? new Set(ROLES.find(x => x.id === induk.id).effective) : new Set();
    EDIT = { id: r.id, key: r.key, grant: new Set(r.permissions), deny: new Set(r.denies), inherit: warisan, locked: r.key === 'tenant_owner' };
    lukisPeranan();
    const mods = {};
    CATALOG.forEach(p => (mods[p.key.split('.')[0]] ??= []).push(p));
    App.$('#rEditor').innerHTML = `<div style="padding:16px">
        <div class="row-between" style="flex-wrap:wrap;gap:8px"><div><h2 class="card-title" style="margin:0">${E(r.name)}</h2><div class="text-xs text-muted">${E(r.description ?? '')}</div></div>
            <div class="row" style="gap:6px;flex-wrap:wrap">${EDIT.locked ? '<span class="text-xs text-muted">Peranan pemilik tidak boleh diubah.</span>' : `<button class="btn btn-sm" onclick="salinPeranan(${r.id})">Salin</button>`}</div></div>
        ${EDIT.locked ? '' : `<div class="grid grid-2 mt-4">
            <div class="field" style="margin:0"><label class="label" for="rName">Nama</label><input class="input" id="rName" value="${E(r.name)}"></div>
            <div class="field" style="margin:0"><label class="label" for="rParent">Warisi kebenaran daripada</label><select class="select" id="rParent"><option value="">— tiada —</option>${ROLES.filter(x => x.id !== r.id && x.key !== 'tenant_owner').map(x => `<option value="${x.id}" ${x.id === r.parent_id ? 'selected' : ''}>${E(x.name)}</option>`).join('')}</select></div>
            <div class="field" style="margin:0;grid-column:1/-1"><label class="label" for="rDesc">Keterangan</label><input class="input" id="rDesc" value="${E(r.description ?? '')}"></div>
        </div>
        <h3 class="text-sm mt-4" style="font-weight:600">Had transaksi & kelulusan</h3>
        <div class="grid grid-2">${Object.entries(LIMITS).map(([k, l]) => `<div class="field" style="margin:0"><label class="label" for="lim_${k}">${E(l)}</label><input class="input" type="number" min="0" step="0.01" id="lim_${k}" data-lim="${k}" value="${r.limits?.[k] ?? ''}" placeholder="Tiada had"></div>`).join('')}</div>`}
        <div class="row-between mt-4" style="flex-wrap:wrap;gap:8px"><h3 class="text-sm" style="font-weight:600;margin:0">Kebenaran</h3>
            <span class="text-xs text-muted">Klik: kosong → <span style="color:var(--success,#27ae60)">beri</span> → <span style="color:var(--danger,#c0392b)">tolak</span>. Garis putus = diwarisi. Tolak sentiasa menang.</span></div>
    </div>
    <div style="overflow-x:auto"><table class="mx"><thead><tr><th style="width:160px">Modul</th><th>Tindakan</th><th style="width:110px"></th></tr></thead><tbody>
        ${Object.entries(mods).map(([mod, ps]) => `<tr><td class="text-sm"><b>${E(mod)}</b></td><td>${ps.map(p => `<button type="button" class="pill ${EDIT.locked ? 'locked' : ''}" data-k="${E(p.key)}" title="${E(p.description ?? p.key)}" ${EDIT.locked ? 'disabled' : ''} onclick="kitar(this)">${E(aksi(p.key))}</button>`).join('')}</td>
            <td>${EDIT.locked ? '' : `<button class="btn btn-sm btn-ghost" onclick="semuaModul('${E(mod)}', true)">Semua</button><button class="btn btn-sm btn-ghost" onclick="semuaModul('${E(mod)}', false)">Kosong</button>`}</td></tr>`).join('')}
    </tbody></table></div>
    ${EDIT.locked ? '' : `<div class="sticky-actions"><span class="text-sm text-muted" id="rPreview" style="margin-right:auto;align-self:center"></span><button class="btn btn-sm btn-ghost" onclick="semuaModul(null, false)">Kosongkan semua</button><button class="btn btn-primary btn-sm" onclick="simpanPeranan(this)">Simpan peranan</button></div>`}`;
    App.$('#rParent')?.addEventListener('change', e => { const p = ROLES.find(x => x.id === Number(e.target.value)); EDIT.inherit = new Set(p ? p.effective : []); warnaPill(); });
    warnaPill();
    if (window.innerWidth < 900) App.$('#rEditor').scrollIntoView({ behavior: 'smooth' });
}
function keadaan(k) { return EDIT.deny.has(k) ? 'deny' : EDIT.grant.has(k) ? 'grant' : EDIT.inherit.has(k) ? 'inherit' : ''; }
function warnaPill() {
    document.querySelectorAll('#rEditor .pill').forEach(b => { b.dataset.s = EDIT.locked ? 'grant' : keadaan(b.dataset.k); b.setAttribute('aria-pressed', String(b.dataset.s === 'grant')); });
    const n = [...new Set([...EDIT.grant, ...EDIT.inherit])].filter(k => !EDIT.deny.has(k)).length;
    if (App.$('#rPreview')) App.$('#rPreview').textContent = `${n} kebenaran berkesan · ${EDIT.deny.size} ditolak`;
}
function kitar(b) {
    const k = b.dataset.k;
    if (EDIT.deny.has(k)) EDIT.deny.delete(k);
    else if (EDIT.grant.has(k)) { EDIT.grant.delete(k); EDIT.deny.add(k); }
    else EDIT.grant.add(k);
    warnaPill();
}
function semuaModul(mod, on) {
    CATALOG.filter(p => !mod || p.key.split('.')[0] === mod).forEach(p => { EDIT.deny.delete(p.key); on ? EDIT.grant.add(p.key) : EDIT.grant.delete(p.key); });
    warnaPill();
}
async function simpanPeranan(btn) {
    const r = ROLES.find(x => x.id === EDIT.id);
    const limits = {}; document.querySelectorAll('[data-lim]').forEach(i => limits[i.dataset.lim] = i.value === '' ? null : Number(i.value));
    const body = { name: App.$('#rName').value.trim() || r.name, description: App.$('#rDesc').value.trim() || null, parent_id: Number(App.$('#rParent').value) || null,
        permissions: [...EDIT.grant], denies: [...EDIT.deny], limits };
    if (await App.withLoading(btn, () => cuba(() => App.api.put(`/api/v1/roles/${EDIT.id}`, body), 'Peranan disimpan'))) { await muatPeranan(); bukaPeranan(EDIT.id); delete dimuat.modules; }
}
async function peranBaru() {
    const d = await borang('Peranan baharu', [{ n: 'name', l: 'Nama', req: true, ph: 'cth. Juruwang Kanan' }, { n: 'parent_id', l: 'Mula daripada (warisi)', opts: [['', '— kosong —'], ...ROLES.filter(r => r.key !== 'tenant_owner').map(r => [r.id, r.name])] }]);
    if (!d) return;
    const r = await cuba(() => App.api.post('/api/v1/roles', { name: d.name, parent_id: Number(d.parent_id) || null, permissions: [] }), 'Peranan dicipta');
    if (r) { await muatPeranan(); bukaPeranan(r.id); }
}
async function salinPeranan(id) {
    const src = ROLES.find(x => x.id === id);
    const d = await borang('Salin ' + src.name, [{ n: 'name', l: 'Nama peranan baharu', req: true, v: src.name + ' (salinan)' }]);
    if (!d) return;
    const r = await cuba(() => App.api.post(`/api/v1/org/roles/${id}/clone`, d), 'Peranan disalin');
    if (r) { await muatPeranan(); bukaPeranan(r.data.id); }
}

// ── Akses modul ──
async function loadModules() {
    const r = await cuba(() => App.api.get('/api/v1/org/modules'));
    if (!r) return;
    const { modules, roles } = r.data;
    App.$('#modTable').innerHTML = `<thead><tr><th>Modul</th><th>Langganan</th>${roles.map(x => `<th class="text-xs">${E(x.name)}</th>`).join('')}</tr></thead><tbody>
        ${modules.map(m => `<tr ${m.subscribed ? '' : 'class="locked"'}><td class="text-sm"><b>${E(m.module)}</b></td><td>${m.subscribed ? '<span class="badge badge-success">Ada</span>' : '<span class="badge badge-neutral">Tiada</span>'}</td>
            ${roles.map(x => { const a = m.roles[x.id] ?? []; return `<td title="${E(a.map(aksi).join(', '))}"><span class="dot ${a.length && m.subscribed ? 'dot-on' : 'dot-off'}"></span> <span class="text-xs text-muted">${a.length ? a.length : ''}</span></td>`; }).join('')}</tr>`).join('')}</tbody>`;
}

// ── Seat ──
async function loadSeats() {
    const [s, o] = await Promise.all([cuba(() => App.api.get('/api/v1/org/seats')), cuba(() => App.api.get('/api/v1/org/overview'))]);
    if (!s) return;
    const d = s.data;
    App.$('#seStats').innerHTML = [['Pakej', d.plan ?? '—'], ['Seat termasuk', d.unlimited ? 'Tanpa had' : d.included], ['Seat tambahan', d.addon], ['Digunakan', d.used]]
        .map(([l, v]) => `<div class="stat"><div class="stat-label">${l}</div><div class="stat-value">${E(v)}</div></div>`).join('');
    App.$('#seBar').innerHTML = seatBar(d);
    const had = o?.data?.subscription?.had ?? [];
    App.$('#seLimits').innerHTML = (Array.isArray(had) ? had : Object.values(had)).length ? (Array.isArray(had) ? had : Object.values(had)).map(h => `<div class="list-row"><div class="text-sm">${E(h.name ?? h.limit_key)}</div><div class="text-sm">${h.limit_value ?? h.had ?? '∞'} ${E(h.unit ?? '')}</div></div>`).join('') : kosong('Tiada had khusus.');
}

// ── Keselamatan ──
async function loadSecurity() {
    const r = await cuba(() => App.api.get('/api/v1/org/sessions'));
    const rows = r?.data ?? [];
    App.$('#secSessions').innerHTML = rows.length ? rows.map(s => `<div class="list-row"><div class="text-sm">${E(peranti(s.user_agent))}${s.current ? ' <span class="badge badge-success">Sesi ini</span>' : ''}<br><span class="text-xs text-muted">${E(s.ip_address ?? '')}</span></div>
        <div class="text-xs text-muted">${masa(s.last_activity)}</div>${s.current ? '' : `<div class="meta"><span class="act"><button class="btn btn-sm btn-ghost" onclick="tamatSesi('${s.id}')">Tamatkan</button></span></div>`}</div>`).join('') : kosong('Tiada sesi direkod.');
    if (!MEMBERS.length) { const m = await cuba(() => App.api.get('/api/v1/org/members')); MEMBERS = m?.data ?? []; }
    App.$('#cMember').innerHTML = MEMBERS.map(m => `<option value="${m.id}" ${m.is_me ? 'selected' : ''}>${E(m.name)}</option>`).join('');
    if (!CATALOG.length) await muatPeranan();
    App.$('#permList').innerHTML = CATALOG.map(p => `<option value="${E(p.key)}">${E(p.description ?? '')}</option>`).join('');
}
const peranti = ua => { ua = ua || ''; const os = /Android/.test(ua) ? 'Android' : /iPhone|iPad/.test(ua) ? 'iPhone/iPad' : /Windows/.test(ua) ? 'Windows' : /Mac OS/.test(ua) ? 'Mac' : /Linux/.test(ua) ? 'Linux' : 'Peranti';
    const br = /Edg\//.test(ua) ? 'Edge' : /Chrome\//.test(ua) ? 'Chrome' : /Firefox\//.test(ua) ? 'Firefox' : /Safari\//.test(ua) ? 'Safari' : ''; return os + (br ? ' · ' + br : ''); };
async function tamatSesi(id) { if (confirm('Tamatkan sesi ini?') && await cuba(() => App.api.del(`/api/v1/org/sessions/${encodeURIComponent(id)}`), 'Sesi ditamatkan')) loadSecurity(); }
async function semakAkses() {
    const perm = App.$('#cPerm').value.trim();
    if (!perm) return App.toast('Masukkan kebenaran.', 'error');
    const body = { member_id: Number(App.$('#cMember').value), permission: perm };
    if (App.$('#cAmt').value) body.amount = Number(App.$('#cAmt').value);
    const r = await cuba(() => App.api.post('/api/v1/org/access/check', body));
    if (!r) return;
    const STEP = { membership: 'Keahlian', entitlement: 'Langganan', permission: 'Kebenaran', data_scope: 'Skop data', limit: 'Had' };
    App.$('#cResult').innerHTML = r.data.allowed ? '<div class="alert alert-success text-sm">Dibenarkan.</div>'
        : `<div class="alert alert-warning text-sm"><b>Ditolak di langkah ${E(STEP[r.data.step] ?? r.data.step)}:</b> ${E(r.data.reason)}</div>`;
}

// ── Audit ──
async function loadAudit() {
    const f = App.$('#aFilter').value;
    const r = await cuba(() => App.api.get('/api/v1/audit?per_page=100' + (f ? '&action=' + encodeURIComponent(f) : '')));
    const rows = r?.data ?? [];
    if (!MEMBERS.length) { const m = await cuba(() => App.api.get('/api/v1/org/members')); MEMBERS = m?.data ?? []; }
    const siapa = id => MEMBERS.find(m => m.user_id === id)?.name ?? (id ? '#' + id : 'Sistem');
    App.$('#aList').innerHTML = rows.length ? rows.map(a => `<div class="list-row"><div class="text-sm"><span class="mono text-xs">${E(a.action)}</span><br><span class="text-xs text-muted">${E(siapa(a.user_id))}${a.subject_type ? ' · ' + E(a.subject_type.split('\\').pop()) + ' #' + E(a.subject_id) : ''}</span></div>
        <div class="text-xs text-muted">${masa(a.created_at)}</div></div>`).join('') : kosong('Tiada rekod.');
}

(async () => {
    if (!(await App.loadSession())) { location.href = '/login'; return; }
    const n = location.hash.slice(1);
    bukaTab(pemuat[n] ? n : 'overview');
})();
</script>
@endpush
