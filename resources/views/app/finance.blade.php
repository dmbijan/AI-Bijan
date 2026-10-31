@extends('layouts.app')

@section('title', 'Kewangan')

@section('content')
<style>
    /* Angka wang tidak dipecah dua baris; mengecil di skrin sempit. */
    [data-panel] .stat-value { white-space: nowrap; font-size: clamp(1.05rem, 4.6vw, 1.6rem); font-variant-numeric: tabular-nums; }
    [data-panel] .stat { min-width: 0; }
    .tabs { scrollbar-width: none; }
    .bline { display: grid; grid-template-columns: 1fr auto; gap: 4px 12px; padding: 14px 16px; border-bottom: 1px solid var(--border, #e5e5e5); }
    .bline:last-child { border-bottom: 0; }
    .bline-ket { font-size: .9rem; overflow-wrap: anywhere; }
    .bline-amt { font-variant-numeric: tabular-nums; font-weight: 600; text-align: right; white-space: nowrap; }
    .bline-meta { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 6px 10px; align-items: center; }
    .bline-act { margin-left: auto; display: flex; gap: 6px; }
    @media (max-width: 640px) { [data-panel] .grid-4 { grid-template-columns: 1fr 1fr; } [data-panel] .grid-3, [data-panel] .grid-2 { grid-template-columns: 1fr; } }
</style>
<div class="page-head">
    <div>
        <h1 class="page-title">Kewangan</h1>
        <p class="page-desc">Tunai, bank, cukai SST, bajet dan laporan kewangan syarikat.</p>
    </div>
    <a class="btn btn-sm" href="/app/import">Import data</a>
</div>

<div class="tabs" role="tablist" style="overflow-x:auto">
    <button class="tab" role="tab" aria-selected="true" data-tab="ringkasan">Ringkasan tunai</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="bank">Rekon bank</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="tunai">Peti tunai</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="sst">SST</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="bajet">Bajet</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="laporan">Laporan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="coa">Carta akaun</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="journal">Jurnal</button>
</div>

{{-- ── Ringkasan tunai (FIN-006) ── --}}
<section data-panel="ringkasan">
    <div id="tWarn"></div>
    <div class="grid grid-4 mb-4">
        <div class="stat"><div class="stat-label">Tunai sekarang</div><div class="stat-value" id="tTotal">—</div></div>
        <div class="stat"><div class="stat-label">Titik terendah 30 hari</div><div class="stat-value" id="tLow">—</div><div class="stat-delta text-xs" id="tLowDate"></div></div>
        <div class="stat"><div class="stat-label">Akan masuk (invois)</div><div class="stat-value" id="tIn">—</div></div>
        <div class="stat"><div class="stat-label">Akan keluar (bil, SST)</div><div class="stat-value" id="tOut">—</div></div>
    </div>
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header"><div><h2 class="card-title">Di mana duit sekarang</h2><p class="card-subtitle">Baki penyata bank terakhir & peti tunai</p></div></div>
            <div class="table-wrap"><table class="table"><tbody id="tPos"></tbody></table></div>
        </div>
        <div class="card">
            <div class="card-header"><div><h2 class="card-title">30 hari akan datang</h2><p class="card-subtitle" id="tLewat">Hari yang ada duit masuk/keluar</p></div></div>
            <div class="table-wrap"><table class="table"><thead><tr><th>Tarikh</th><th class="table-num">Masuk</th><th class="table-num">Keluar</th><th class="table-num">Baki</th></tr></thead><tbody id="tDays"></tbody></table></div>
        </div>
    </div>
</section>

{{-- ── Rekon bank (FIN-017) ── --}}
<section data-panel="bank" hidden>
    <div class="card mb-4">
        <div class="row-between" style="flex-wrap:wrap;gap:12px">
            <div class="row" style="flex-wrap:wrap;gap:8px;align-items:end">
                <div class="field" style="margin:0"><label class="label" for="bAkaun">Akaun bank</label><select class="select" id="bAkaun" onchange="loadBank()"></select></div>
                <div class="field" style="margin:0"><label class="label" for="bStatus">Tunjuk</label>
                    <select class="select" id="bStatus" onchange="loadBankLines()"><option value="unmatched">Belum padan</option><option value="matched">Sudah padan</option><option value="ignored">Diabaikan</option><option value="">Semua</option></select></div>
            </div>
            <div class="row" style="gap:8px;flex-wrap:wrap">
                <label class="btn btn-primary btn-sm" for="bFail" style="cursor:pointer">Import penyata CSV</label>
                <input type="file" id="bFail" accept=".csv,text/csv" hidden onchange="importPenyata(this)">
                <button class="btn btn-sm" onclick="autoPadan(this)">Padan automatik</button>
                <button class="btn btn-sm btn-ghost" onclick="tambahAkaun()">+ Akaun bank</button>
            </div>
        </div>
        <p class="text-xs text-muted mt-2">Muat turun penyata dari perbankan internet dalam format CSV. Sistem kenal sendiri lajur tarikh, keterangan, debit/kredit dan baki.</p>
    </div>
    <div class="grid grid-4 mb-4" id="bStats"></div>
    <div class="card" style="padding:0"><div id="bLines"></div></div>
</section>

{{-- ── Peti tunai (FIN-018) ── --}}
<section data-panel="tunai" hidden>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <p class="text-sm text-muted" style="margin:0">Petty cash, peti besi dan deposit ke bank. Laci kaunter POS diurus di skrin POS.</p>
        <button class="btn btn-sm" onclick="tambahPeti()">+ Peti tunai</button>
    </div>
    <div class="grid grid-3" id="cBoxes"></div>
</section>

{{-- ── SST (FIN-010) ── --}}
<section data-panel="sst" hidden>
    <div class="alert alert-info mb-4 text-sm">Draf untuk semakan sahaja. Kadar boleh berubah: sahkan dengan JKDM sebelum hantar melalui MySST.</div>
    <div class="card mb-4">
        <div class="row" style="flex-wrap:wrap;gap:8px;align-items:end">
            <div class="field" style="margin:0"><label class="label" for="sDate">Tempoh yang mengandungi tarikh</label><input class="input" type="date" id="sDate" onchange="loadSst()"></div>
            <div class="field" style="margin:0"><label class="label" for="sReg">No. pendaftaran SST</label><input class="input mono" id="sReg" placeholder="cth. W10-1808-32000001"></div>
            <div class="field" style="margin:0"><label class="label" for="sMula">Bulan mula kitaran</label><select class="select" id="sMula"></select></div>
            <button class="btn btn-sm" onclick="simpanTetapanSst(this)">Simpan tetapan</button>
        </div>
    </div>
    <div class="grid grid-4 mb-4">
        <div class="stat"><div class="stat-label">Tempoh</div><div class="stat-value" id="sTempoh" style="white-space:normal;font-size:1rem;line-height:1.35">—</div></div>
        <div class="stat"><div class="stat-label">Cukai jualan</div><div class="stat-value" id="sJualan">—</div></div>
        <div class="stat"><div class="stat-label">Cukai perkhidmatan</div><div class="stat-value" id="sServis">—</div></div>
        <div class="stat"><div class="stat-label">Perlu dibayar</div><div class="stat-value" id="sJumlah">—</div><div class="stat-delta text-xs" id="sDue"></div></div>
    </div>
    <div id="sWarn"></div>
    <div class="table-wrap mb-4"><table class="table">
        <thead><tr><th>Kod</th><th>Keterangan</th><th class="table-num">Kadar</th><th class="table-num">Nilai bercukai</th><th class="table-num">Cukai</th></tr></thead>
        <tbody id="sLines"></tbody>
    </table></div>
    <details class="card"><summary class="card-title" style="cursor:pointer">Kod cukai</summary>
        <div class="table-wrap mt-3"><table class="table"><thead><tr><th>Kod</th><th>Nama</th><th class="table-num">Kadar</th><th>Berkuat kuasa</th></tr></thead><tbody id="sCodes"></tbody></table></div>
    </details>
</section>

{{-- ── Bajet (FIN-005) ── --}}
<section data-panel="bajet" hidden>
    <div class="card mb-4">
        <div class="row" style="flex-wrap:wrap;gap:8px;align-items:end">
            <div class="field" style="margin:0"><label class="label" for="jBajet">Bajet</label><select class="select" id="jBajet" onchange="loadBajetReport()"></select></div>
            <div class="field" style="margin:0"><label class="label" for="jDari">Dari bulan</label><select class="select" id="jDari" onchange="loadBajetReport()"></select></div>
            <div class="field" style="margin:0"><label class="label" for="jHingga">Hingga bulan</label><select class="select" id="jHingga" onchange="loadBajetReport()"></select></div>
            <button class="btn btn-sm btn-ghost" onclick="tambahBajet()">+ Bajet baharu</button>
        </div>
    </div>
    <div class="grid grid-3 mb-4" id="jStats"></div>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Akaun</th><th class="table-num">Bajet</th><th class="table-num">Sebenar</th><th class="table-num">Beza</th><th></th></tr></thead>
        <tbody id="jLines"></tbody>
    </table></div>
</section>

{{-- ── Laporan ── --}}
<section data-panel="laporan" hidden>
    <div class="card mb-4">
        <div class="row" style="flex-wrap:wrap;gap:8px;align-items:end">
            <div class="field" style="margin:0"><label class="label" for="rDari">Dari</label><input class="input" type="date" id="rDari"></div>
            <div class="field" style="margin:0"><label class="label" for="rHingga">Hingga</label><input class="input" type="date" id="rHingga"></div>
            <button class="btn btn-primary btn-sm" onclick="loadReport('profit-loss')">Untung rugi</button>
            <button class="btn btn-sm" onclick="loadReport('balance-sheet')">Kunci kira-kira</button>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><div><h2 class="card-title" id="reportTitle">Pilih laporan</h2><p class="card-subtitle" id="reportSub"></p></div></div>
        <div id="reportBody"><div class="empty"><div class="empty-text">Pilih tarikh dan laporan di atas.</div></div></div>
    </div>
</section>

<section data-panel="coa" hidden>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Kod</th><th>Nama akaun</th><th>Jenis</th></tr></thead>
        <tbody id="coaTable"></tbody>
    </table></div>
</section>

<section data-panel="journal" hidden>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>No. jurnal</th><th>Tarikh</th><th>Keterangan</th><th>Status</th></tr></thead>
        <tbody id="journalTable"></tbody>
    </table></div>
</section>

<dialog class="dialog" id="dlg">
    <form method="dialog" id="dlgForm">
        <div class="dialog-header"><h2 class="card-title" id="dlgTitle"></h2></div>
        <div class="dialog-body stack" id="dlgBody"></div>
        <div class="dialog-footer row" style="justify-content:flex-end;gap:8px">
            <button class="btn btn-ghost" value="cancel" formnovalidate>Batal</button>
            <button class="btn btn-primary" value="ok" id="dlgOk">Simpan</button>
        </div>
    </form>
</dialog>
@endsection

@push('scripts')
<script>
const RM = n => App.rupiah(n ?? 0);
const E = s => App.escapeHtml(String(s ?? ''));
const tarikh = d => d ? new Intl.DateTimeFormat('ms-MY', { day: '2-digit', month: 'short', year: 'numeric' }).format(new Date(String(d).slice(0, 10) + 'T00:00:00')) : '—';
const kosong = (cols, teks) => `<tr><td colspan="${cols}"><div class="empty"><div class="empty-text">${E(teks)}</div></div></td></tr>`;
const BULAN = ['Jan', 'Feb', 'Mac', 'Apr', 'Mei', 'Jun', 'Jul', 'Ogo', 'Sep', 'Okt', 'Nov', 'Dis'];
// Tarikh tempatan (bukan UTC: di Malaysia, toISOString memberi semalam sebelum 8 pagi).
const isoTempatan = d => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
const hariIni = () => isoTempatan(new Date());
const dimuat = {};

// ── tab ──────────────────────────────────────────────
const pemuat = { ringkasan: loadTreasury, bank: loadBankAccounts, tunai: loadBoxes, sst: loadSst, bajet: loadBudgets, coa: loadCoa, journal: loadJournal, laporan: () => {} };
function bukaTab(nama) {
    document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', String(t.dataset.tab === nama)));
    document.querySelector(`[data-tab="${nama}"]`)?.scrollIntoView({ inline: 'nearest', block: 'nearest' });
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== nama);
    if (!dimuat[nama]) { dimuat[nama] = true; pemuat[nama](); }
    history.replaceState(null, '', '#' + nama);
}
document.querySelectorAll('[data-tab]').forEach(t => t.addEventListener('click', () => bukaTab(t.dataset.tab)));
window.addEventListener('hashchange', () => { const n = location.hash.slice(1); if (pemuat[n]) bukaTab(n); });

// ── dialog ringkas: medan → nilai ─────────────────────
function borang(tajuk, medan) {
    const dlg = App.$('#dlg');
    App.$('#dlgTitle').textContent = tajuk;
    App.$('#dlgBody').innerHTML = medan.map(m => `
        <div class="field"><label class="label" for="f_${m.n}">${E(m.l)}</label>
        ${m.opts ? `<select class="select" id="f_${m.n}">${m.opts.map(o => `<option value="${E(o[0])}">${E(o[1])}</option>`).join('')}</select>`
                 : `<input class="input" id="f_${m.n}" type="${m.t || 'text'}" ${m.req ? 'required' : ''} ${m.step ? `step="${m.step}"` : ''} value="${E(m.v ?? '')}" ${m.ph ? `placeholder="${E(m.ph)}"` : ''}>`}
        ${m.hint ? `<div class="field-hint">${E(m.hint)}</div>` : ''}</div>`).join('');
    dlg.showModal();
    return new Promise(res => {
        dlg.onclose = () => {
            if (dlg.returnValue !== 'ok') return res(null);
            const out = {};
            medan.forEach(m => { const v = App.$('#f_' + m.n).value.trim(); if (v !== '') out[m.n] = m.t === 'number' ? Number(v) : v; });
            res(out);
        };
    });
}
async function cuba(fn, ok) {
    try { const r = await fn(); if (ok) App.toast(ok, 'success'); return r; }
    catch (e) { App.toast(e.message, 'error'); return null; }
}

// ── Ringkasan tunai ──────────────────────────────────
async function loadTreasury() {
    const [p, f] = await Promise.all([
        cuba(() => App.api.get('/api/v1/finance/treasury/position')),
        cuba(() => App.api.get('/api/v1/finance/treasury/forecast?days=30')),
    ]);
    if (!p || !f) return;
    const d = p.data, r = f.data;
    App.$('#tTotal').textContent = RM(d.total);
    App.$('#tLow').textContent = RM(r.lowest.balance);
    App.$('#tLow').style.color = r.lowest.balance < 0 ? 'var(--danger, #c0392b)' : '';
    App.$('#tLowDate').textContent = 'pada ' + tarikh(r.lowest.date);
    App.$('#tIn').textContent = RM(r.total_in);
    App.$('#tOut').textContent = RM(r.total_out);
    App.$('#tWarn').innerHTML = [...d.warnings, ...(r.lowest.balance < 0 ? [`Baki dijangka negatif pada ${tarikh(r.lowest.date)}. Kutip hutang atau tangguh bayaran.`] : [])]
        .map(w => `<div class="alert alert-warning mb-4 text-sm">${E(w)}</div>`).join('');
    const baris = [
        ...d.bank_accounts.map(b => `<tr><td>${E(b.name)}<div class="text-xs text-muted">${b.as_of ? 'penyata ' + tarikh(b.as_of) : 'tiada penyata'}</div></td><td class="table-num">${b.balance === null ? '—' : RM(b.balance)}</td></tr>`),
        ...d.cash_boxes.map(b => `<tr><td>${E(b.name)}<div class="text-xs text-muted">${b.type === 'safe' ? 'peti besi' : 'petty cash'}</div></td><td class="table-num">${RM(b.balance)}</td></tr>`),
    ];
    App.$('#tPos').innerHTML = baris.length ? baris.join('') : kosong(2, 'Tambah akaun bank di tab Rekon bank.');
    App.$('#tLewat').textContent = r.overdue_receivables > 0 ? `${RM(r.overdue_receivables)} hutang pelanggan sudah lewat (tidak dikira masuk)` : 'Hari yang ada duit masuk/keluar';
    const hari = r.days.filter(x => x.in || x.out);
    App.$('#tDays').innerHTML = hari.length ? hari.map(x => `<tr><td class="text-sm">${tarikh(x.date)}</td><td class="table-num">${x.in ? RM(x.in) : ''}</td><td class="table-num">${x.out ? RM(x.out) : ''}</td><td class="table-num" ${x.balance < 0 ? 'style="color:var(--danger,#c0392b);font-weight:600"' : ''}>${RM(x.balance)}</td></tr>`).join('')
        : kosong(4, 'Tiada invois atau bil yang matang dalam 30 hari.');
}

// ── Rekon bank ───────────────────────────────────────
async function loadBankAccounts() {
    const r = await cuba(() => App.api.get('/api/v1/finance/bank-accounts'));
    if (!r) return;
    const sel = App.$('#bAkaun');
    sel.innerHTML = r.data.length ? r.data.map(a => `<option value="${a.id}">${E(a.name)}${a.account_last4 ? ' ··' + E(a.account_last4) : ''}</option>`).join('') : '<option value="">Tiada akaun</option>';
    loadBank();
}
async function loadBank() {
    const id = App.$('#bAkaun').value;
    if (!id) { App.$('#bStats').innerHTML = ''; App.$('#bLines').innerHTML = '<div class="empty"><div class="empty-text">Tambah akaun bank dahulu, kemudian import penyata CSV.</div></div>'; return; }
    const s = (await cuba(() => App.api.get(`/api/v1/finance/bank-accounts/${id}/summary`)))?.data;
    bankKosong = s ? s.matched.count + s.unmatched.count + s.ignored.count === 0 : false;
    if (s) App.$('#bStats').innerHTML = [
        ['Baki penyata', s.closing_balance === null ? '—' : RM(s.closing_balance), s.closing_date ? tarikh(s.closing_date) : ''],
        ['Sudah padan', s.matched.count, RM(s.matched.amount)],
        ['Belum padan', s.unmatched.count, RM(s.unmatched.amount)],
        ['Diabaikan', s.ignored.count, 'caj bank, faedah, dll.'],
    ].map(([l, v, d]) => `<div class="stat"><div class="stat-label">${l}</div><div class="stat-value">${E(v)}</div><div class="stat-delta text-xs">${E(d)}</div></div>`).join('');
    loadBankLines();
}
let bankKosong = false;
async function loadBankLines() {
    const id = App.$('#bAkaun').value, st = App.$('#bStatus').value;
    if (!id) return;
    const r = await cuba(() => App.api.get(`/api/v1/finance/bank-accounts/${id}/lines?per_page=100${st ? '&status=' + st : ''}`));
    if (!r) return;
    const lencana = { matched: ['success', 'Padan'], unmatched: ['warning', 'Belum'], ignored: ['neutral', 'Abai'] };
    App.$('#bLines').innerHTML = r.data.length ? r.data.map(l => `<div class="bline">
        <div class="bline-ket">${E(l.description || '—')}</div>
        <div class="bline-amt" style="color:${Number(l.amount) < 0 ? 'var(--danger,#c0392b)' : 'var(--success,#1e8449)'}">${RM(l.amount)}</div>
        <div class="bline-meta text-xs text-muted">
            <span>${tarikh(l.txn_date)}</span>${l.reference ? `<span class="mono">${E(l.reference)}</span>` : ''}
            <span class="badge badge-${lencana[l.status][0]}">${lencana[l.status][1]}</span>
            <span class="bline-act">${l.status === 'unmatched'
                ? `<button class="btn btn-sm" onclick="calon(${l.id})">Padan</button><button class="btn btn-sm btn-ghost" onclick="tindakBaris(${l.id},'ignore')">Abai</button>`
                : `<button class="btn btn-sm btn-ghost" onclick="tindakBaris(${l.id},'unmatch')">Buat asal</button>`}</span>
        </div>
        <div id="cal${l.id}" style="grid-column:1/-1"></div></div>`).join('')
        : `<div class="empty"><div class="empty-text">${E(bankKosong ? 'Belum ada penyata untuk akaun ini. Tekan "Import penyata CSV".' : st === 'unmatched' ? 'Semua baris sudah dipadankan.' : 'Tiada baris.')}</div></div>`;
}
async function calon(id) {
    const r = await cuba(() => App.api.get(`/api/v1/finance/bank-lines/${id}/candidates`));
    if (!r) return;
    App.$('#cal' + id).innerHTML = r.data.length
        ? `<div class="stack mt-2" style="gap:4px">${r.data.map(c => `<button class="btn btn-sm" style="justify-content:flex-start" onclick="padan(${id},'${c.type}',${c.id})">↔ ${E(c.label)} · ${tarikh(c.date)}</button>`).join('')}</div>`
        : '<div class="text-xs text-muted mt-2">Tiada rekod dengan jumlah sama dalam ±3 hari. Rekod bayaran dahulu, atau Abai jika caj bank.</div>';
}
async function padan(id, type, tid) {
    if (await cuba(() => App.api.post(`/api/v1/finance/bank-lines/${id}/match`, { type, id: tid }), 'Dipadankan')) loadBank();
}
async function tindakBaris(id, aksi) {
    if (await cuba(() => App.api.post(`/api/v1/finance/bank-lines/${id}/${aksi}`, {}))) loadBank();
}
async function autoPadan(btn) {
    const id = App.$('#bAkaun').value;
    if (!id) return;
    await App.withLoading(btn, async () => {
        const r = await cuba(() => App.api.post(`/api/v1/finance/bank-accounts/${id}/auto-match`, {}));
        if (r) { App.toast(`${r.data.auto_matched} baris dipadankan`, 'success'); loadBank(); }
    });
}
async function importPenyata(input) {
    const id = App.$('#bAkaun').value, f = input.files[0];
    input.value = '';
    if (!f) return;
    if (!id) { App.toast('Tambah akaun bank dahulu.', 'error'); return; }
    const r = await cuba(() => f.text().then(csv => App.api.post(`/api/v1/finance/bank-accounts/${id}/import`, { csv })));
    if (!r) return;
    const d = r.data;
    App.toast(`${d.imported} baris diimport, ${d.auto_matched} dipadankan${d.duplicates ? `, ${d.duplicates} sudah ada` : ''}${d.errors.length ? `, ${d.errors.length} ralat` : ''}`, d.errors.length ? 'warning' : 'success', 6000);
    loadBank();
}
async function tambahAkaun() {
    const v = await borang('Akaun bank baharu', [
        { n: 'name', l: 'Nama', req: true, ph: 'cth. Maybank Operasi' },
        { n: 'bank_name', l: 'Bank', ph: 'Maybank, CIMB, Public Bank…' },
        { n: 'account_last4', l: '4 digit akhir no. akaun', ph: '1234', hint: 'Hanya 4 digit akhir disimpan.' },
    ]);
    if (v && await cuba(() => App.api.post('/api/v1/finance/bank-accounts', v), 'Akaun ditambah')) loadBankAccounts();
}

// ── Peti tunai ───────────────────────────────────────
let akaunBank = [];
async function loadBoxes() {
    const [r, b] = await Promise.all([cuba(() => App.api.get('/api/v1/finance/cash-boxes')), cuba(() => App.api.get('/api/v1/finance/bank-accounts'))]);
    if (!r) return;
    akaunBank = b?.data ?? [];
    App.$('#cBoxes').innerHTML = r.data.length ? r.data.map(x => `
        <div class="card">
            <div class="card-header"><div><h2 class="card-title">${E(x.name)}</h2><p class="card-subtitle">${x.type === 'safe' ? 'Peti besi' : 'Petty cash · float ' + RM(x.float_amount)}</p></div></div>
            <div class="stat-value mb-4">${RM(x.balance)}</div>
            <div class="row" style="flex-wrap:wrap;gap:6px">
                <button class="btn btn-sm" onclick="entriPeti(${x.id},'out')">Belanja</button>
                <button class="btn btn-sm" onclick="entriPeti(${x.id},'in')">Masuk</button>
                ${x.type === 'petty' ? `<button class="btn btn-sm" onclick="isiFloat(${x.id})">Isi semula float</button>` : ''}
                <button class="btn btn-sm" onclick="depositPeti(${x.id})">Deposit ke bank</button>
                <button class="btn btn-sm btn-ghost" onclick="kiraPeti(${x.id})">Kira tunai</button>
            </div>
        </div>`).join('') : '<div class="empty"><div class="empty-text">Tiada peti tunai. Tambah petty cash pejabat atau peti besi kedai.</div></div>';
}
async function tambahPeti() {
    const v = await borang('Peti tunai baharu', [
        { n: 'name', l: 'Nama', req: true, ph: 'cth. Petty cash kedai' },
        { n: 'type', l: 'Jenis', opts: [['petty', 'Petty cash (ada paras float)'], ['safe', 'Peti besi']] },
        { n: 'float_amount', l: 'Paras float (RM)', t: 'number', step: '0.01', hint: 'Untuk petty cash: jumlah tetap yang diisi semula.' },
    ]);
    if (v && await cuba(() => App.api.post('/api/v1/finance/cash-boxes', v), 'Peti ditambah')) loadBoxes();
}
async function entriPeti(id, type) {
    const v = await borang(type === 'out' ? 'Belanja dari peti' : 'Tunai masuk', [
        { n: 'amount', l: 'Amaun (RM)', t: 'number', step: '0.01', req: true },
        ...(type === 'out' ? [{ n: 'category', l: 'Kategori', ph: 'cth. Pengangkutan, Alat tulis' }] : []),
        { n: 'description', l: 'Keterangan' },
        { n: 'reference', l: 'No. resit' },
    ]);
    if (v && await cuba(() => App.api.post(`/api/v1/finance/cash-boxes/${id}/entries`, { ...v, type }), 'Direkod')) loadBoxes();
}
async function isiFloat(id) {
    if (await cuba(() => App.api.post(`/api/v1/finance/cash-boxes/${id}/top-up`, {}), 'Float diisi semula')) loadBoxes();
}
async function depositPeti(id) {
    if (!akaunBank.length) { App.toast('Tambah akaun bank dahulu (tab Rekon bank).', 'error'); return; }
    const v = await borang('Deposit tunai ke bank', [
        { n: 'bank_account_id', l: 'Akaun bank', opts: akaunBank.map(a => [a.id, a.name]) },
        { n: 'amount', l: 'Amaun (RM)', t: 'number', step: '0.01', req: true },
        { n: 'reference', l: 'No. slip deposit', hint: 'Membantu padanan automatik dengan penyata bank.' },
    ]);
    if (v && await cuba(() => App.api.post(`/api/v1/finance/cash-boxes/${id}/deposit`, { ...v, bank_account_id: Number(v.bank_account_id) }), 'Deposit direkod')) loadBoxes();
}
async function kiraPeti(id) {
    const den = ['100', '50', '20', '10', '5', '1', '0.50', '0.20', '0.10', '0.05'];
    const v = await borang('Kira tunai fizikal', den.map(d => ({ n: 'd' + d.replace('.', '_'), l: (Number(d) >= 1 ? 'RM' + d : (Number(d) * 100) + ' sen') + ' × keping', t: 'number', step: '1' })));
    if (!v) return;
    const denominations = {};
    den.forEach(d => { const k = 'd' + d.replace('.', '_'); if (v[k]) denominations[d] = v[k]; });
    const r = await cuba(() => App.api.post('/api/v1/finance/cash-counts', { cash_box_id: id, denominations }));
    if (!r) return;
    const k = r.data, beza = Number(k.variance);
    if (Math.abs(beza) < 0.005) { App.toast(`Dikira ${RM(k.counted_total)}: tepat dengan buku.`, 'success'); return; }
    if (confirm(`Dikira ${RM(k.counted_total)}, buku ${RM(k.expected_total)}.\nBeza ${RM(beza)}.\n\nLaraskan baki buku ikut kiraan? (perlu kebenaran lulus)`)) {
        if (await cuba(() => App.api.post(`/api/v1/finance/cash-counts/${k.id}/adjust`, {}), 'Baki dilaraskan')) loadBoxes();
    }
}

// ── SST ──────────────────────────────────────────────
let tetapanSst = null;
async function loadSst() {
    if (!App.$('#sDate').value) App.$('#sDate').value = hariIni();
    if (!tetapanSst) {
        const [t, c] = await Promise.all([cuba(() => App.api.get('/api/v1/finance/tax-settings')), cuba(() => App.api.get('/api/v1/finance/tax-codes'))]);
        tetapanSst = t?.data ?? {};
        App.$('#sReg').value = tetapanSst.sst_registration_no ?? '';
        App.$('#sMula').innerHTML = BULAN.map((b, i) => `<option value="${i + 1}" ${Number(tetapanSst.period_start_month || 1) === i + 1 ? 'selected' : ''}>${b}</option>`).join('');
        App.$('#sCodes').innerHTML = (c?.data ?? []).map(k => `<tr><td class="mono">${E(k.code)}</td><td class="text-sm">${E(k.name)}</td><td class="table-num">${Number(k.rate)}%</td><td class="text-xs">${tarikh(k.effective_from)}${k.effective_to ? ' – ' + tarikh(k.effective_to) : ''}</td></tr>`).join('');
    }
    const r = await cuba(() => App.api.get('/api/v1/finance/reports/sst-02?date=' + App.$('#sDate').value));
    if (!r) return;
    const d = r.data;
    App.$('#sTempoh').textContent = `${tarikh(d.period.from)} – ${tarikh(d.period.to)}`;
    App.$('#sJualan').textContent = RM(d.sales_tax);
    App.$('#sServis').textContent = RM(d.service_tax);
    App.$('#sJumlah').textContent = RM(d.total_tax_payable);
    App.$('#sDue').textContent = 'hantar & bayar sebelum ' + tarikh(d.period.due);
    App.$('#sWarn').innerHTML = d.warnings.map(w => `<div class="alert alert-warning mb-4 text-sm">${E(w)}</div>`).join('');
    App.$('#sLines').innerHTML = d.lines.length ? d.lines.map(l => `<tr><td class="mono">${E(l.code)}</td><td class="text-sm">${E(l.name)}<div class="text-xs text-muted">${l.pos_lines} baris POS · ${l.invoice_lines} baris invois</div></td><td class="table-num">${l.rate}%</td><td class="table-num">${RM(l.taxable)}</td><td class="table-num">${RM(l.tax)}</td></tr>`).join('')
        : kosong(5, 'Tiada jualan berkod cukai dalam tempoh ini.');
}
async function simpanTetapanSst(btn) {
    await App.withLoading(btn, async () => {
        const r = await cuba(() => App.api.put('/api/v1/finance/tax-settings', { sst_registration_no: App.$('#sReg').value.trim() || null, period_start_month: Number(App.$('#sMula').value) }), 'Tetapan disimpan');
        if (r) { tetapanSst = r.data; loadSst(); }
    });
}

// ── Bajet ────────────────────────────────────────────
async function loadBudgets() {
    const r = await cuba(() => App.api.get('/api/v1/finance/budgets'));
    if (!r) return;
    App.$('#jBajet').innerHTML = r.data.length ? r.data.map(b => `<option value="${b.id}">${E(b.name)} (${b.fiscal_year})${b.status === 'approved' ? ' ✓' : ' · draf'}</option>`).join('') : '<option value="">Tiada bajet</option>';
    const bln = new Date().getMonth() + 1;
    App.$('#jDari').innerHTML = BULAN.map((b, i) => `<option value="${i + 1}" ${i === 0 ? 'selected' : ''}>${b}</option>`).join('');
    App.$('#jHingga').innerHTML = BULAN.map((b, i) => `<option value="${i + 1}" ${i + 1 === bln ? 'selected' : ''}>${b}</option>`).join('');
    loadBajetReport();
}
async function loadBajetReport() {
    const id = App.$('#jBajet').value;
    if (!id) { App.$('#jStats').innerHTML = ''; App.$('#jLines').innerHTML = kosong(5, 'Tiada bajet. Cipta bajet, kemudian tetapkan amaun ikut akaun.'); return; }
    const r = await cuba(() => App.api.get(`/api/v1/finance/budgets/${id}/report?from_month=${App.$('#jDari').value}&to_month=${App.$('#jHingga').value}`));
    if (!r) return;
    const d = r.data, t = d.totals;
    App.$('#jStats').innerHTML = [['Hasil', t.revenue], ['Kos & belanja', t.costs], ['Untung', t.profit]]
        .map(([l, x]) => `<div class="stat"><div class="stat-label">${l}</div><div class="stat-value">${RM(x.actual)}</div><div class="stat-delta text-xs">bajet ${RM(x.budget)}</div></div>`).join('');
    App.$('#jLines').innerHTML = d.lines.length ? d.lines.map(l => `<tr>
        <td class="text-sm"><span class="mono text-xs">${E(l.code)}</span> ${E(l.name)}</td>
        <td class="table-num">${RM(l.budget)}</td><td class="table-num">${RM(l.actual)}</td>
        <td class="table-num" style="color:${l.favourable ? 'var(--success,#1e8449)' : 'var(--danger,#c0392b)'}">${RM(l.variance)}${l.variance_pct !== null ? ` (${l.variance_pct > 0 ? '+' : ''}${l.variance_pct}%)` : ''}</td>
        <td>${l.alert ? '<span class="badge badge-danger">Lari bajet</span>' : ''}</td></tr>`).join('')
        : kosong(5, 'Bajet ini belum ada baris akaun.');
}
async function tambahBajet() {
    const v = await borang('Bajet baharu', [
        { n: 'name', l: 'Nama', req: true, v: 'Bajet ' + new Date().getFullYear() },
        { n: 'fiscal_year', l: 'Tahun', t: 'number', req: true, v: new Date().getFullYear() },
    ]);
    if (v && await cuba(() => App.api.post('/api/v1/finance/budgets', v), 'Bajet dicipta. Tetapkan amaun ikut akaun melalui API/import.')) loadBudgets();
}

// ── Laporan ──────────────────────────────────────────
function seksyen(tajuk, s, tebal) {
    if (!s) return '';
    const akaun = (s.accounts ?? []).map(a => `<tr><td class="text-sm" style="padding-left:24px">${E(a.name)}</td><td class="table-num">${RM(a.amount)}</td></tr>`).join('');
    return `${akaun ? `<tr><td colspan="2" class="text-xs text-muted" style="padding-top:14px">${E(tajuk)}</td></tr>${akaun}` : ''}
        <tr style="${tebal ? 'font-weight:700;border-top:2px solid var(--border,#ddd)' : 'font-weight:600'}"><td>${E(akaun ? 'Jumlah ' + tajuk.toLowerCase() : tajuk)}</td><td class="table-num">${RM(s.total ?? s.amount)}</td></tr>`;
}
async function loadReport(jenis) {
    const dari = App.$('#rDari').value, hingga = App.$('#rHingga').value;
    App.$('#reportTitle').textContent = jenis === 'profit-loss' ? 'Untung rugi' : 'Kunci kira-kira';
    App.$('#reportBody').innerHTML = '<div class="skeleton" style="height:160px"></div>';
    const q = jenis === 'profit-loss' ? `date_from=${dari}&date_to=${hingga}` : `as_of_date=${hingga}`;
    const d = await cuba(() => App.api.get(`/api/v1/finance/reports/${jenis}?${q}`));
    if (!d) { App.$('#reportBody').innerHTML = ''; return; }
    const s = d.sections;
    if (jenis === 'profit-loss') {
        App.$('#reportSub').textContent = `${tarikh(dari)} – ${tarikh(hingga)}`;
        App.$('#reportBody').innerHTML = `<div class="table-wrap"><table class="table"><tbody>
            ${seksyen('Pendapatan', s.revenue)}${seksyen('Kos jualan', s.cost_of_goods)}${seksyen('Untung kasar', s.gross_profit, true)}
            ${seksyen('Perbelanjaan operasi', s.operating_expenses)}${seksyen('Untung sebelum cukai', s.net_profit_before_tax, true)}
            ${seksyen('Cukai pendapatan', s.tax_expense)}${seksyen('Untung bersih', s.net_profit_after_tax, true)}</tbody></table></div>`;
    } else {
        const a = s.assets.subsections, l = s.liabilities.subsections, t = d.totals;
        App.$('#reportSub').textContent = 'Pada ' + tarikh(hingga);
        App.$('#reportBody').innerHTML = `${Math.abs(t.difference) > 0.005 ? `<div class="alert alert-danger text-sm">Tidak seimbang: beza ${RM(t.difference)}. Semak jurnal.</div>` : ''}
            <div class="table-wrap"><table class="table"><tbody>
            ${seksyen('Aset semasa', a.current_assets)}${seksyen('Aset bukan semasa', a.non_current_assets)}${seksyen('Jumlah aset', { total: t.total_assets }, true)}
            ${seksyen('Liabiliti semasa', l.current_liabilities)}${seksyen('Liabiliti bukan semasa', l.non_current_liabilities)}
            ${seksyen('Ekuiti', s.equity)}${seksyen('Untung ditahan', s.retained_earnings)}${seksyen('Jumlah liabiliti & ekuiti', { total: t.total_liabilities_and_equity }, true)}</tbody></table></div>`;
    }
}

// ── Carta akaun & jurnal ─────────────────────────────
async function loadCoa() {
    const r = await cuba(() => App.api.get('/api/v1/finance/chart-of-accounts'));
    const rows = r?.data ?? [];
    const jenis = { asset: 'Aset', liability: 'Liabiliti', equity: 'Ekuiti', revenue: 'Hasil', expense: 'Belanja', cost_of_goods: 'Kos jualan' };
    App.$('#coaTable').innerHTML = rows.length ? rows.map(a => `<tr><td class="mono text-xs">${E(a.code)}</td><td class="text-sm">${E(a.name_bm || a.name)}</td><td><span class="badge badge-neutral">${E(jenis[a.type] ?? a.type)}</span></td></tr>`).join('') : kosong(3, 'Tiada akaun.');
}
async function loadJournal() {
    const r = await cuba(() => App.api.get('/api/v1/finance/journal-entries?per_page=30'));
    const rows = r?.data?.data ?? r?.data ?? [];
    App.$('#journalTable').innerHTML = rows.length ? rows.map(j => `<tr><td class="mono text-xs">${E(j.entry_number)}</td><td class="text-xs">${tarikh(j.entry_date)}</td><td class="text-sm">${E(j.description || '—')}</td><td><span class="badge badge-${j.status === 'posted' ? 'success' : 'warning'}">${j.status === 'posted' ? 'Diposting' : E(j.status)}</span></td></tr>`).join('') : kosong(4, 'Tiada jurnal.');
}

(async () => {
    if (!(await App.loadSession())) { location.href = '/login'; return; }
    const d = new Date();
    App.$('#rDari').value = isoTempatan(new Date(d.getFullYear(), d.getMonth(), 1));
    App.$('#rHingga').value = hariIni();
    const awal = location.hash.slice(1);
    bukaTab(pemuat[awal] ? awal : 'ringkasan');
})();
</script>
@endpush
