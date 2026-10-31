@extends('layouts.app')

@section('title', 'Pakej & Harga')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Pakej &amp; Harga</h1>
        <p class="page-desc">Super Admin: urus pakej langganan dan pakej token AI. Semua harga di sini yang dicaj kepada pelanggan. Pakej yang disediakan sistem hanyalah contoh.</p>
    </div>
    <div class="row">
        <button class="btn btn-sm" onclick="muat()">Muat Semula</button>
    </div>
</div>

<div id="tiadaAkses" class="alert alert-danger" hidden></div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="pelan">Pakej langganan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="token">Pakej token AI</button>
</div>

<section data-panel="pelan">
    <div class="row mb-4"><button class="btn btn-primary" onclick="bukaPelan()">+ Pakej baharu</button></div>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Pakej</th><th class="table-num">Bulanan</th><th class="table-num">Tahunan</th><th>Modul</th><th>Had utama</th><th>Status</th><th></th></tr></thead>
        <tbody id="tPelan"><tr><td colspan="7"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
    </table></div>
</section>

<section data-panel="token" hidden>
    <div class="row mb-4"><button class="btn btn-primary" onclick="bukaToken()">+ Pakej token baharu</button></div>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Pakej</th><th class="table-num">Token</th><th class="table-num">Harga jual</th><th class="table-num">Kos pembekal</th><th class="table-num">Margin</th><th>Status</th><th></th></tr></thead>
        <tbody id="tToken"><tr><td colspan="7"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
    </table></div>
    <p class="text-xs text-muted mt-3">Kos pembekal dan margin hanya dilihat oleh Super Admin; pelanggan cuma nampak nama, token dan harga jual.</p>
</section>

{{-- Dialog pakej langganan --}}
<div class="dialog-backdrop" id="dPelan" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="dPelanTajuk" style="max-width:640px">
        <div class="dialog-header">
            <h2 class="card-title" id="dPelanTajuk">Pakej langganan</h2>
            <button class="btn btn-ghost btn-sm" onclick="tutup('dPelan')" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <input type="hidden" id="pId">
            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0"><label class="label" for="pName">Nama pakej</label><input class="input" id="pName" placeholder="Growth"></div>
                <div class="field" style="margin:0"><label class="label" for="pCode">Kod (huruf kecil)</label><input class="input mono" id="pCode" placeholder="growth"></div>
                <div class="field" style="margin:0"><label class="label" for="pMonthly">Harga bulanan (RM)</label><input class="input" id="pMonthly" inputmode="decimal" oninput="cadangTahunan()"></div>
                <div class="field" style="margin:0"><label class="label" for="pYearly">Harga tahunan (RM)</label><input class="input" id="pYearly" inputmode="decimal" placeholder="Cadangan: 10× bulanan"></div>
                <div class="field" style="margin:0"><label class="label" for="pTrial">Hari percubaan</label><input class="input" id="pTrial" inputmode="numeric" placeholder="0"></div>
                <div class="field" style="margin:0"><label class="label" for="pSort">Susunan paparan</label><input class="input" id="pSort" inputmode="numeric" placeholder="0"></div>
            </div>
            <div class="field mt-4"><label class="label" for="pDesc">Penerangan</label><textarea class="input" id="pDesc" rows="2"></textarea></div>
            <div class="field"><label class="label" for="pModules">Modul (pisah dengan koma, <code>*</code> = semua)</label><input class="input mono" id="pModules" placeholder="pos, crm, inventory"></div>
            <div class="field"><label class="label" for="pFeatures">Ciri untuk halaman harga (satu baris satu ciri)</label><textarea class="input" id="pFeatures" rows="3"></textarea></div>
            <div class="field">
                <label class="label">Had pakej</label>
                <div id="pLimits" class="grid grid-2" style="gap:8px"></div>
            </div>
            <div class="row" style="gap:16px;flex-wrap:wrap">
                <label class="text-sm" style="display:flex;gap:6px;align-items:center"><input type="checkbox" id="pActive"> Aktif (boleh dilanggan)</label>
                <label class="text-sm" style="display:flex;gap:6px;align-items:center"><input type="checkbox" id="pPublic"> Papar di halaman harga</label>
                <label class="text-sm" style="display:flex;gap:6px;align-items:center"><input type="checkbox" id="pToken"> Wajib beli pakej token AI</label>
            </div>
            <div id="pError" class="alert alert-danger mt-4" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="tutup('dPelan')">Batal</button>
            <button class="btn btn-primary" id="pSave" onclick="simpanPelan()">Simpan</button>
        </div>
    </div>
</div>

{{-- Dialog pakej token --}}
<div class="dialog-backdrop" id="dToken" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="dTokenTajuk">
        <div class="dialog-header">
            <h2 class="card-title" id="dTokenTajuk">Pakej token AI</h2>
            <button class="btn btn-ghost btn-sm" onclick="tutup('dToken')" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <input type="hidden" id="kId">
            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0"><label class="label" for="kName">Nama</label><input class="input" id="kName" placeholder="60 Juta token"></div>
                <div class="field" style="margin:0"><label class="label" for="kCode">Kod</label><input class="input mono" id="kCode" placeholder="t60m"></div>
                <div class="field" style="margin:0"><label class="label" for="kTokens">Token dijual</label><input class="input" id="kTokens" inputmode="numeric"></div>
                <div class="field" style="margin:0"><label class="label" for="kUsage">Had guna sebenar (pilihan)</label><input class="input" id="kUsage" inputmode="numeric"></div>
                <div class="field" style="margin:0"><label class="label" for="kPrice">Harga jual (RM)</label><input class="input" id="kPrice" inputmode="decimal" oninput="kiraMargin()"></div>
                <div class="field" style="margin:0"><label class="label" for="kCost">Kos pembekal (RM)</label><input class="input" id="kCost" inputmode="decimal" oninput="kiraMargin()"></div>
                <div class="field" style="margin:0"><label class="label" for="kDays">Sah (hari)</label><input class="input" id="kDays" inputmode="numeric" placeholder="30"></div>
                <div class="field" style="margin:0"><label class="label" for="kSort">Susunan</label><input class="input" id="kSort" inputmode="numeric" placeholder="0"></div>
            </div>
            <p class="text-sm mt-4">Margin: <b id="kMargin">—</b></p>
            <label class="text-sm" style="display:flex;gap:6px;align-items:center"><input type="checkbox" id="kActive"> Aktif (boleh dibeli)</label>
            <div id="kError" class="alert alert-danger mt-4" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="tutup('dToken')">Batal</button>
            <button class="btn btn-primary" id="kSave" onclick="simpanToken()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const HAD = [
    ['seats', 'Pengguna', 'pengguna', 'total'],
    ['ai_tokens', 'AI Tokens', 'token', 'monthly'],
    ['storage_bytes', 'Storan (bait)', 'bait', 'total'],
    ['whatsapp_messages', 'WhatsApp', 'mesej', 'monthly'],
    ['sms_credits', 'SMS', 'SMS', 'monthly'],
];
let PELAN = [], TOKEN = [];

document.querySelectorAll('[data-tab]').forEach(tab => tab.addEventListener('click', () => {
    document.querySelectorAll('[data-tab]').forEach(x => x.setAttribute('aria-selected', 'false'));
    tab.setAttribute('aria-selected', 'true');
    document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
}));

const rm = n => n === null || n === undefined || n === '' ? '—' : App.rupiah(n);
const angka = v => { const n = String(v ?? '').replace(/[, _]/g, ''); return n === '' ? null : Number(n); };
function tutup(id) { App.$('#' + id).hidden = true; }
function aksesDitolak(e) {
    const el = App.$('#tiadaAkses');
    el.hidden = false;
    el.textContent = e.status === 403
        ? (e.message.includes('2FA') ? 'Halaman ini perlukan 2FA. Aktifkan 2FA pada akaun Super Admin, kemudian log masuk semula.' : 'Halaman ini untuk Super Admin platform sahaja.')
        : e.message;
}

async function muat() {
    try {
        [PELAN, TOKEN] = await Promise.all([App.api.get('/api/v1/admin/plans'), App.api.get('/api/v1/admin/token-packages')]);
    } catch (e) { aksesDitolak(e); return; }
    App.$('#tiadaAkses').hidden = true;
    lukisPelan(); lukisToken();
}

function hadRingkas(p) {
    const l = (p.limits ?? []).find(x => x.limit_key === 'seats');
    return l ? `${l.limit_value ?? '∞'} pengguna` : (p.max_users ? `${p.max_users} pengguna` : '—');
}
function lukisPelan() {
    App.$('#tPelan').innerHTML = PELAN.length ? PELAN.map((p, i) => `
        <tr>
            <td><b>${App.escapeHtml(p.name)}</b><div class="text-xs text-muted mono">${App.escapeHtml(p.code)}</div>${p.description ? `<div class="text-xs text-muted">${App.escapeHtml(p.description)}</div>` : ''}</td>
            <td class="table-num">${rm(p.price_monthly)}</td>
            <td class="table-num">${rm(p.price_yearly)}</td>
            <td class="text-xs">${App.escapeHtml((p.modules ?? []).join(', ') || '—')}</td>
            <td class="text-xs">${hadRingkas(p)}</td>
            <td><span class="badge badge-${p.is_active ? 'success' : 'neutral'}">${p.is_active ? 'Aktif' : 'Tutup'}</span>${p.is_public === false ? ' <span class="badge badge-neutral">Tersembunyi</span>' : ''}</td>
            <td><button class="btn btn-sm" onclick="bukaPelan(${i})">Edit</button></td>
        </tr>`).join('') : '<tr><td colspan="7"><div class="empty"><div class="empty-text">Tiada pakej.</div></div></td></tr>';
}
function lukisToken() {
    App.$('#tToken').innerHTML = TOKEN.length ? TOKEN.map((k, i) => `
        <tr>
            <td><b>${App.escapeHtml(k.name)}</b><div class="text-xs text-muted mono">${App.escapeHtml(k.code)}</div></td>
            <td class="table-num">${App.number(k.tokens)}</td>
            <td class="table-num">${rm(k.price)}</td>
            <td class="table-num">${rm(k.supplier_cost)}</td>
            <td class="table-num">${k.margin_pct === null ? '—' : k.margin_pct + '%'}</td>
            <td><span class="badge badge-${k.is_active ? 'success' : 'neutral'}">${k.is_active ? 'Aktif' : 'Tutup'}</span></td>
            <td><button class="btn btn-sm" onclick="bukaToken(${i})">Edit</button></td>
        </tr>`).join('') : '<tr><td colspan="7"><div class="empty"><div class="empty-text">Tiada pakej token.</div></div></td></tr>';
}

// ---- pakej langganan ----
function bukaPelan(i) {
    const p = i === undefined ? { is_active: true, is_public: true, token_required: true, limits: [] } : PELAN[i];
    App.$('#pId').value = p.id ?? '';
    App.$('#pName').value = p.name ?? '';
    App.$('#pCode').value = p.code ?? '';
    App.$('#pMonthly').value = p.price_monthly ?? '';
    App.$('#pYearly').value = p.price_yearly ?? '';
    App.$('#pTrial').value = p.trial_days ?? '';
    App.$('#pSort').value = p.sort_order ?? '';
    App.$('#pDesc').value = p.description ?? '';
    App.$('#pModules').value = (p.modules ?? []).join(', ');
    App.$('#pFeatures').value = (p.features ?? []).join('\n');
    App.$('#pActive').checked = !!p.is_active;
    App.$('#pPublic').checked = p.is_public !== false;
    App.$('#pToken').checked = p.token_required !== false;
    App.$('#pLimits').innerHTML = HAD.map(([k, n]) => {
        const l = (p.limits ?? []).find(x => x.limit_key === k);
        return `<div class="field" style="margin:0"><label class="label" for="lim_${k}">${n}</label><input class="input" id="lim_${k}" inputmode="numeric" placeholder="kosong = tiada had" value="${l?.limit_value ?? ''}"></div>`;
    }).join('');
    App.$('#pError').hidden = true;
    App.$('#dPelan').hidden = false;
}
function cadangTahunan() {
    if (!App.$('#pYearly').value && App.$('#pMonthly').value) App.$('#pYearly').placeholder = 'Cadangan: RM ' + (Number(App.$('#pMonthly').value) * 10).toFixed(2) + ' (2 bulan percuma)';
}
async function simpanPelan() {
    const id = App.$('#pId').value;
    const badan = {
        name: App.$('#pName').value.trim(), code: App.$('#pCode').value.trim(),
        price_monthly: angka(App.$('#pMonthly').value), price_yearly: angka(App.$('#pYearly').value),
        trial_days: angka(App.$('#pTrial').value), sort_order: angka(App.$('#pSort').value),
        description: App.$('#pDesc').value.trim() || null,
        modules: App.$('#pModules').value.split(',').map(s => s.trim()).filter(Boolean),
        features: App.$('#pFeatures').value.split('\n').map(s => s.trim()).filter(Boolean),
        is_active: App.$('#pActive').checked, is_public: App.$('#pPublic').checked, token_required: App.$('#pToken').checked,
        limits: HAD.map(([k, n, unit, period]) => ({ limit_key: k, name: n, unit, period, limit_value: angka(App.$('#lim_' + k).value) })),
    };
    await App.withLoading(App.$('#pSave'), async () => {
        try {
            id ? await App.api.put(`/api/v1/admin/plans/${id}`, badan) : await App.api.post('/api/v1/admin/plans', badan);
            App.toast('Pakej disimpan', 'success'); tutup('dPelan'); muat();
        } catch (e) { const el = App.$('#pError'); el.textContent = e.message; el.hidden = false; }
    });
}

// ---- pakej token ----
function bukaToken(i) {
    const k = i === undefined ? { is_active: true, validity_days: 30 } : TOKEN[i];
    App.$('#kId').value = k.id ?? '';
    App.$('#kName').value = k.name ?? '';
    App.$('#kCode').value = k.code ?? '';
    App.$('#kTokens').value = k.tokens ?? '';
    App.$('#kUsage').value = k.usage_limit ?? '';
    App.$('#kPrice').value = k.price ?? '';
    App.$('#kCost').value = k.supplier_cost ?? '';
    App.$('#kDays').value = k.validity_days ?? '';
    App.$('#kSort').value = k.sort_order ?? '';
    App.$('#kActive').checked = !!k.is_active;
    App.$('#kError').hidden = true;
    kiraMargin();
    App.$('#dToken').hidden = false;
}
function kiraMargin() {
    const h = angka(App.$('#kPrice').value), k = angka(App.$('#kCost').value);
    const el = App.$('#kMargin');
    if (!h || k === null) { el.textContent = '—'; return; }
    const m = (h - k) / h * 100;
    el.textContent = `${m.toFixed(1)}% (untung ${App.rupiah(h - k)})`;
    el.style.color = m < 50 ? 'var(--danger-500)' : '';
}
async function simpanToken() {
    const id = App.$('#kId').value;
    const badan = {
        name: App.$('#kName').value.trim(), code: App.$('#kCode').value.trim(),
        tokens: angka(App.$('#kTokens').value), usage_limit: angka(App.$('#kUsage').value),
        price: angka(App.$('#kPrice').value), supplier_cost: angka(App.$('#kCost').value),
        validity_days: angka(App.$('#kDays').value), sort_order: angka(App.$('#kSort').value),
        is_active: App.$('#kActive').checked,
    };
    await App.withLoading(App.$('#kSave'), async () => {
        try {
            id ? await App.api.put(`/api/v1/admin/token-packages/${id}`, badan) : await App.api.post('/api/v1/admin/token-packages', badan);
            App.toast('Pakej token disimpan', 'success'); tutup('dToken'); muat();
        } catch (e) { const el = App.$('#kError'); el.textContent = e.message; el.hidden = false; }
    });
}

(async () => {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    muat();
})();
</script>
@endpush
