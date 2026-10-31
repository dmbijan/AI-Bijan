@extends('layouts.app')

@section('title', 'Import Data')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Import data</h1>
        <p class="page-desc">Pindahkan produk, pelanggan dan stok awal dari Excel atau sistem lama. Semak dahulu, baru simpan.</p>
    </div>
</div>

<div class="card mb-4">
    <div class="stack" style="gap:16px">
        <div>
            <div class="label mb-2">1. Apa yang nak diimport?</div>
            <div class="row" style="flex-wrap:wrap;gap:8px" role="radiogroup" id="jenisPilih">
                <button class="btn btn-sm" data-jenis="products" aria-pressed="true">Produk</button>
                <button class="btn btn-sm" data-jenis="customers" aria-pressed="false">Pelanggan</button>
                <button class="btn btn-sm" data-jenis="opening-stock" aria-pressed="false">Stok awal</button>
            </div>
            <p class="text-xs text-muted mt-2" id="jenisNota"></p>
        </div>
        <div>
            <div class="label mb-2">2. Pilih fail CSV</div>
            <div class="row" style="flex-wrap:wrap;gap:8px;align-items:center">
                <label class="btn btn-primary btn-sm" for="fail" style="cursor:pointer">Pilih fail…</label>
                <input type="file" id="fail" accept=".csv,text/csv,.txt" hidden onchange="hantar(this.files[0]); this.value=''">
                <a class="btn btn-sm btn-ghost" id="templat" href="#">Muat turun templat</a>
                <span class="text-xs text-muted">Dari Excel: Fail → Simpan Sebagai → CSV.</span>
            </div>
        </div>
    </div>
</div>

<div id="pratonton" hidden>
    <div class="grid grid-4 mb-4" id="ringkas"></div>
    <details class="card mb-4" id="petaKad">
        <summary style="cursor:pointer;list-style:none"><span class="card-title">Padanan lajur</span> <span class="text-xs text-muted" id="petaRingkas"></span></summary>
        <p class="card-subtitle mt-2">Sistem teka sendiri. Betulkan jika salah, kemudian Semak semula.</p>
        <div class="grid grid-3 mt-3" id="peta" style="gap:12px"></div>
        <div class="row mt-3" style="gap:8px"><button class="btn btn-sm" onclick="semakSemula(this)">Semak semula</button></div>
    </details>
    <div class="row-between mb-4" style="flex-wrap:wrap;gap:8px">
        <div class="tabs" role="tablist" style="margin:0">
            <button class="tab" aria-selected="true" data-tapis="">Semua</button>
            <button class="tab" aria-selected="false" data-tapis="errors">Ada ralat</button>
            <button class="tab" aria-selected="false" data-tapis="create">Baharu</button>
            <button class="tab" aria-selected="false" data-tapis="update">Kemas kini</button>
        </div>
        <div class="row" style="gap:8px">
            <button class="btn btn-sm btn-ghost" onclick="batal()">Batal</button>
            <button class="btn btn-primary" id="simpan" onclick="simpan(this)">Simpan</button>
        </div>
    </div>
    <div class="table-wrap"><table class="table"><thead id="thead"></thead><tbody id="tbody"></tbody></table></div>
</div>

<div class="card mt-5">
    <div class="card-header"><div><h2 class="card-title">Import terdahulu</h2></div></div>
    <div class="table-wrap"><table class="table">
        <thead><tr><th>Tarikh</th><th>Fail</th><th class="table-num">Baris sah</th><th>Status</th></tr></thead>
        <tbody id="sejarah"></tbody>
    </table></div>
</div>
@endsection

@push('scripts')
<script>
const E = s => App.escapeHtml(String(s ?? ''));
const NOTA = {
    'products': 'SKU sama dengan produk sedia ada akan dikemas kini; lajur yang tiada dalam fail tidak diubah.',
    'customers': 'Pelanggan dengan emel atau telefon sama akan dikemas kini. Nombor telefon ditukar ke format 60…',
    'opening-stock': 'Untuk barang yang belum ada stok. Import produk dahulu. Perlu kos seunit. Simpan perlu kebenaran lulus stok.',
};
const LABEL = {
    sku: 'SKU', name: 'Nama', sell_price: 'Harga jual', standard_cost: 'Kos', category: 'Kategori', barcode: 'Barcode', uom: 'Unit', reorder_level: 'Paras pesanan semula',
    contact_phone: 'Telefon', contact_email: 'Emel', contact_person: 'Orang hubungan', customer_type: 'Jenis', ssm_number: 'SSM', sst_number: 'SST', tax_identification: 'TIN',
    address: 'Alamat', city: 'Bandar', state: 'Negeri', postcode: 'Poskod', credit_limit: 'Had kredit', qty: 'Kuantiti', unit_cost: 'Kos seunit', warehouse: 'Gudang',
};
let jenis = 'products', job = null, csvAkhir = null, namaFail = null, medan = [], tapis = '';

function pilihJenis(j) {
    jenis = j;
    document.querySelectorAll('[data-jenis]').forEach(b => { b.setAttribute('aria-pressed', String(b.dataset.jenis === j)); b.classList.toggle('btn-primary', b.dataset.jenis === j); });
    App.$('#jenisNota').textContent = NOTA[j];
    App.$('#templat').href = `/api/v1/imports/${j}/template`;
    App.$('#pratonton').hidden = true;
    job = null;
    App.api.get(`/api/v1/imports/${j}/fields`).then(r => medan = r.data).catch(() => {});
    sejarah();
}
document.querySelectorAll('[data-jenis]').forEach(b => b.addEventListener('click', () => pilihJenis(b.dataset.jenis)));
document.querySelectorAll('[data-tapis]').forEach(b => b.addEventListener('click', () => {
    document.querySelectorAll('[data-tapis]').forEach(x => x.setAttribute('aria-selected', String(x === b)));
    tapis = b.dataset.tapis;
    muatJob();
}));

// Templat perlu pengepala log masuk/tenant: muat turun melalui fetch, bukan pautan terus.
App.$('#templat').addEventListener('click', async ev => {
    ev.preventDefault();
    try {
        const res = await fetch(`/api/v1/imports/${jenis}/template`, { credentials: 'same-origin', headers: { 'X-Tenant': App.state.tenant ?? '', Accept: 'text/csv' } });
        if (!res.ok) throw new Error('Tiada kebenaran atau ralat ' + res.status);
        const url = URL.createObjectURL(await res.blob());
        Object.assign(document.createElement('a'), { href: url, download: `templat-${jenis}.csv` }).click();
        URL.revokeObjectURL(url);
    } catch (e) { App.toast(e.message, 'error'); }
});

async function hantar(fail, mapping) {
    if (!fail && !csvAkhir) return;
    try {
        if (fail) { csvAkhir = await fail.text(); namaFail = fail.name; }
        if (job && job.status === 'preview') await App.api.post(`/api/v1/imports/${jenis}/${job.id}/cancel`, {}).catch(() => {});
        const r = await App.api.post(`/api/v1/imports/${jenis}`, { csv: csvAkhir, filename: namaFail, ...(mapping ? { mapping } : {}) });
        job = r.data;
        tapis = job.error_rows ? 'errors' : '';
        document.querySelectorAll('[data-tapis]').forEach(x => x.setAttribute('aria-selected', String(x.dataset.tapis === tapis)));
        if (tapis) await muatJob(); else papar();
        sejarah();
    } catch (e) {
        App.toast(e.message, 'error', 7000);
        // Lajur wajib tidak dikenali: tunjuk padanan manual daripada tajuk fail.
        if (csvAkhir && /Lajur wajib/.test(e.message)) {
            const tajuk = csvAkhir.split(/\r?\n/)[0].split(/[,;\t]/).map(h => h.replace(/^"|"$/g, ''));
            job = { id: null, status: 'none', headers: tajuk, mapping: {}, rows: [], summary: { create: 0, update: 0, skip: 0 }, total_rows: 0, error_rows: 0 };
            papar();
        }
    }
}

function papar() {
    App.$('#pratonton').hidden = false;
    const s = job.summary;
    App.$('#ringkas').innerHTML = [['Baris', job.total_rows], ['Baharu', s.create], ['Kemas kini', s.update], ['Ada ralat (dilangkau)', s.skip]]
        .map(([l, v], i) => `<div class="stat"><div class="stat-label">${l}</div><div class="stat-value" ${i === 3 && v ? 'style="color:var(--danger,#c0392b)"' : ''}>${v}</div></div>`).join('');
    App.$('#peta').innerHTML = medan.map(m => `<div class="field" style="margin:0"><label class="label" for="m_${m.field}">${E(LABEL[m.field] ?? m.field)}${m.required ? ' *' : ''}</label>
        <select class="select" id="m_${m.field}"><option value="">— tiada —</option>${(job.headers ?? []).map((h, i) => `<option value="${i}" ${job.mapping?.[m.field] === i ? 'selected' : ''}>${E(h)}</option>`).join('')}</select></div>`).join('');
    const dipadan = medan.filter(m => job.mapping?.[m.field] !== undefined).length;
    const kurang = medan.some(m => m.required && job.mapping?.[m.field] === undefined);
    App.$('#petaRingkas').textContent = kurang ? '· lajur wajib belum dipadan' : `· ${dipadan} lajur dikenali (klik untuk ubah)`;
    App.$('#petaKad').open = kurang;
    const btn = App.$('#simpan');
    btn.disabled = !job.id || job.status !== 'preview' || !(s.create + s.update);
    btn.textContent = job.status === 'committed' ? 'Sudah disimpan' : `Simpan ${s.create + s.update} baris`;
    jadual(job.rows);
}
function jadual(rows) {
    const kol = Object.keys(job.mapping ?? {});
    const ikon = { create: '<span class="badge badge-success">Baharu</span>', update: '<span class="badge badge-info">Kemas kini</span>', skip: '<span class="badge badge-danger">Ralat</span>' };
    App.$('#thead').innerHTML = `<tr><th>Baris</th><th></th>${kol.map(k => `<th>${E(LABEL[k] ?? k)}</th>`).join('')}</tr>`;
    App.$('#tbody').innerHTML = rows.length ? rows.map(r => `<tr>
        <td class="mono text-xs">${r.row_no}</td><td>${ikon[r.action]}</td>
        ${kol.map(k => `<td class="text-sm">${E(r.data?.[k])}</td>`).join('')}</tr>
        ${r.errors ? `<tr><td></td><td colspan="${kol.length + 1}" class="text-xs" style="color:var(--danger,#c0392b);padding-top:0">${r.errors.map(E).join(' · ')}</td></tr>` : ''}`).join('')
        : `<tr><td colspan="${kol.length + 2}"><div class="empty"><div class="empty-text">Tiada baris.</div></div></td></tr>`;
}
async function muatJob() {
    if (!job?.id) return;
    try { job = (await App.api.get(`/api/v1/imports/${jenis}/${job.id}${tapis ? '?filter=' + tapis : ''}`)).data; papar(); }
    catch (e) { App.toast(e.message, 'error'); }
}
async function semakSemula(btn) {
    const mapping = {};
    medan.forEach(m => { const v = App.$('#m_' + m.field)?.value; if (v !== '' && v !== undefined) mapping[m.field] = Number(v); });
    await App.withLoading(btn, () => hantar(null, mapping));
}
async function simpan(btn) {
    const s = job.summary;
    if (!confirm(`Simpan ${s.create} rekod baharu dan kemas kini ${s.update}?${s.skip ? `\n${s.skip} baris ralat akan dilangkau.` : ''}`)) return;
    await App.withLoading(btn, async () => {
        try {
            job = (await App.api.post(`/api/v1/imports/${jenis}/${job.id}/commit`, {})).data;
            const h = job.result ?? {};
            App.toast(jenis === 'opening-stock' ? `Stok awal disimpan (${App.rupiah(h.value)}).` : `${h.created ?? 0} baharu, ${h.updated ?? 0} dikemas kini.`, 'success', 6000);
            papar();
            sejarah();
        } catch (e) { App.toast(e.message, 'error', 7000); }
    });
}
async function batal() {
    if (job?.id && job.status === 'preview') await App.api.post(`/api/v1/imports/${jenis}/${job.id}/cancel`, {}).catch(() => {});
    job = null; csvAkhir = null;
    App.$('#pratonton').hidden = true;
    sejarah();
}
async function sejarah() {
    try {
        const r = await App.api.get(`/api/v1/imports/${jenis}`);
        const st = { preview: ['warning', 'Belum disimpan'], committed: ['success', 'Disimpan'], cancelled: ['neutral', 'Dibatalkan'] };
        App.$('#sejarah').innerHTML = r.data.length ? r.data.map(j => `<tr><td class="text-xs">${App.date(j.created_at)}</td><td class="text-sm">${E(j.filename || 'CSV')}</td><td class="table-num">${j.valid_rows} / ${j.total_rows}</td><td><span class="badge badge-${st[j.status][0]}">${st[j.status][1]}</span></td></tr>`).join('')
            : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Belum ada import.</div></div></td></tr>';
    } catch (e) {
        App.$('#sejarah').innerHTML = `<tr><td colspan="4"><div class="empty"><div class="empty-text">${E(e.message)}</div></div></td></tr>`;
    }
}

(async () => {
    if (!(await App.loadSession())) { location.href = '/login'; return; }
    const awal = new URLSearchParams(location.search).get('jenis');
    pilihJenis(NOTA[awal] ? awal : 'products');
})();
</script>
@endpush
