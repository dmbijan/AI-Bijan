<!DOCTYPE html>
<html lang="ms" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Menu Meja</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v={{ @filemtime(public_path('assets/app.css')) }}">
    <style>
        body{background:var(--bg);color:var(--text);padding-bottom:96px}
        .mm-head{position:sticky;top:0;z-index:5;background:var(--surface);border-bottom:1px solid var(--border);padding:14px 16px}
        .mm-head-row{display:flex;justify-content:space-between;align-items:center;gap:8px;max-width:640px;margin:0 auto}
        .mm-shop{font-weight:700;font-size:16px}
        .mm-table{font-size:13px;color:var(--text-muted)}
        .mm-cats{display:flex;gap:6px;overflow-x:auto;padding:10px 16px 2px;max-width:640px;margin:0 auto;scrollbar-width:none}
        .mm-cat{flex:none;padding:7px 14px;border-radius:999px;border:1px solid var(--border);background:var(--surface);color:inherit;font-size:13px;cursor:pointer}
        .mm-cat.is-on{background:var(--brand);border-color:var(--brand);color:#1a1205;font-weight:600}
        .mm-list{max-width:640px;margin:0 auto;padding:8px 16px}
        .mm-item{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 0;border-bottom:1px solid var(--border)}
        .mm-name{font-weight:600;font-size:15px}
        .mm-price{font-size:14px;color:var(--text-muted);margin-top:2px}
        .mm-step{display:flex;align-items:center;gap:8px}
        .mm-step button{width:36px;height:36px;border-radius:10px;border:1px solid var(--border);background:var(--surface);color:inherit;font-size:18px;cursor:pointer}
        .mm-step .mm-add{width:auto;padding:0 14px;font-size:14px;font-weight:600;background:var(--brand);border-color:var(--brand);color:#1a1205}
        .mm-step span{min-width:18px;text-align:center;font-weight:700}
        .mm-bar{position:fixed;left:0;right:0;bottom:0;padding:12px 16px calc(12px + env(safe-area-inset-bottom));background:var(--surface);border-top:1px solid var(--border)}
        .mm-bar .btn{max-width:640px;margin:0 auto;display:flex;justify-content:space-between}
        .mm-sheet{position:fixed;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:flex-end;justify-content:center;z-index:20}
        .mm-sheet[hidden]{display:none}
        .mm-panel{background:var(--surface);width:100%;max-width:640px;border-radius:18px 18px 0 0;padding:18px 16px calc(18px + env(safe-area-inset-bottom));max-height:88vh;overflow-y:auto}
        .mm-line{padding:10px 0;border-bottom:1px solid var(--border)}
        .mm-line-top{display:flex;justify-content:space-between;gap:8px;font-weight:600}
        .mm-line input{margin-top:6px;font-size:13px}
        .mm-order{border:1px solid var(--border);border-radius:12px;padding:12px;margin-top:10px}
        .mm-badge{display:inline-block;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;background:var(--bg-subtle)}
        .mm-badge[data-s=dimasak]{background:#fef3c7;color:#92400e}
        .mm-badge[data-s=siap],.mm-badge[data-s=dihantar]{background:#dcfce7;color:#166534}
        .mm-empty{text-align:center;padding:48px 16px;color:var(--text-muted)}
    </style>
</head>
<body>
<header class="mm-head">
    <div class="mm-head-row">
        <div><div class="mm-shop" id="shop">…</div><div class="mm-table" id="tbl"></div></div>
        <div style="display:flex;gap:6px">
            <button class="btn btn-sm" id="btnMine" hidden></button>
            <button class="btn btn-sm" id="btnLang"></button>
        </div>
    </div>
</header>
<nav class="mm-cats" id="cats"></nav>
<main class="mm-list" id="list"><div class="mm-empty">…</div></main>

<div class="mm-bar" id="bar" hidden>
    <button class="btn btn-primary btn-lg" onclick="bukaTroli()"><span id="barQty"></span><span id="barTotal"></span></button>
</div>

<div class="mm-sheet" id="sheetCart" hidden onclick="if(event.target===this)this.hidden=true">
    <div class="mm-panel" role="dialog" aria-modal="true">
        <h2 class="card-title" id="tCart"></h2>
        <div id="cartLines"></div>
        <div class="field mt-4"><input class="input" id="custName" maxlength="80"></div>
        <div class="field"><input class="input" id="custNote" maxlength="500"></div>
        <div class="pos-total-row" style="display:flex;justify-content:space-between;font-weight:700;font-size:17px;margin:8px 0 14px"><span id="tTotal"></span><span id="cartTotal"></span></div>
        <button class="btn btn-primary btn-block btn-lg" id="btnSend" onclick="hantar()"></button>
        <p class="text-xs text-muted mt-3" style="text-align:center" id="tPayNote"></p>
    </div>
</div>

<div class="mm-sheet" id="sheetMine" hidden onclick="if(event.target===this)this.hidden=true">
    <div class="mm-panel" role="dialog" aria-modal="true">
        <h2 class="card-title" id="tMine"></h2>
        <div id="mineList"></div>
        <button class="btn btn-block mt-4" onclick="App.$('#sheetMine').hidden=true" id="btnClose"></button>
    </div>
</div>

<script src="{{ asset('assets/app.js') }}?v={{ @filemtime(public_path('assets/app.js')) }}"></script>
<script>
const TOKEN = location.pathname.split('/').pop();
const API = `/api/v1/public/meja/${TOKEN}`;
const K_MINE = `aib-meja-${TOKEN}`;
const T = {
    ms: { semua: 'Semua', tambah: 'Tambah', troli: 'Pesanan anda', lihat: 'Lihat pesanan', item: 'item', nama: 'Nama anda (pilihan)', notaUmum: 'Nota untuk dapur (pilihan)', notaItem: 'Nota (cth: kurang manis)', hantar: 'Hantar pesanan', sibuk: 'Menghantar…', jumlah: 'Jumlah', bayar: 'Bayar di kaunter selepas makan.', saya: 'Pesanan saya', tutup: 'Tutup', bahasa: 'English', tiada: 'Tiada menu buat masa ini.', hilang: 'Meja ini tidak aktif. Sila tanya staf.', berjaya: 'Pesanan dihantar ke dapur!',
          s: { baru: 'Diterima', dimasak: 'Sedang dimasak', siap: 'Siap', dihantar: 'Dihantar', selesai: 'Selesai', batal: 'Dibatalkan' } },
    en: { semua: 'All', tambah: 'Add', troli: 'Your order', lihat: 'View order', item: 'items', nama: 'Your name (optional)', notaUmum: 'Note for the kitchen (optional)', notaItem: 'Note (e.g. less sugar)', hantar: 'Place order', sibuk: 'Sending…', jumlah: 'Total', bayar: 'Pay at the counter after your meal.', saya: 'My orders', tutup: 'Close', bahasa: 'Bahasa Melayu', tiada: 'No menu available right now.', hilang: 'This table is not active. Please ask our staff.', berjaya: 'Order sent to the kitchen!',
          s: { baru: 'Received', dimasak: 'Cooking', siap: 'Ready', dihantar: 'Served', selesai: 'Completed', batal: 'Cancelled' } },
};
let lang = (() => { try { return localStorage.getItem('aib-lang') || 'ms'; } catch { return 'ms'; } })();
const t = k => T[lang][k];
let menu = [], cat = null;
const qty = {}, nota = {};
const rm = n => 'RM ' + Number(n || 0).toFixed(2);
const esc = s => App.escapeHtml(String(s ?? ''));
const mine = () => { try { return JSON.parse(localStorage.getItem(K_MINE) || '[]'); } catch { return []; } };

function teks() {
    App.$('#btnLang').textContent = t('bahasa');
    App.$('#btnMine').textContent = t('saya');
    App.$('#tCart').textContent = t('troli');
    App.$('#custName').placeholder = t('nama');
    App.$('#custNote').placeholder = t('notaUmum');
    App.$('#tTotal').textContent = t('jumlah');
    App.$('#btnSend').textContent = t('hantar');
    App.$('#tPayNote').textContent = t('bayar');
    App.$('#tMine').textContent = t('saya');
    App.$('#btnClose').textContent = t('tutup');
    App.$('#btnMine').hidden = !mine().length;
}
App.$('#btnLang').onclick = () => { lang = lang === 'ms' ? 'en' : 'ms'; try { localStorage.setItem('aib-lang', lang); } catch {} teks(); lukis(); };
App.$('#btnMine').onclick = bukaSaya;

async function muat() {
    try {
        const d = await App.api.get(API);
        App.$('#shop').textContent = d.syarikat;
        App.$('#tbl').textContent = d.meja;
        document.title = `${d.meja} · ${d.syarikat}`;
        menu = d.menu.map(m => ({ ...m, sell_price: Number(m.sell_price) }));
        lukis();
    } catch {
        App.$('#list').innerHTML = `<div class="mm-empty">${t('hilang')}</div>`;
    }
}

function lukis() {
    const kategori = [...new Set(menu.map(m => m.category).filter(Boolean))];
    App.$('#cats').innerHTML = kategori.length ? [null, ...kategori].map(c =>
        `<button class="mm-cat ${c === cat ? 'is-on' : ''}" onclick="cat=${c === null ? 'null' : `'${esc(c).replace(/'/g, "\\'")}'`};lukis()">${c === null ? t('semua') : esc(c)}</button>`).join('') : '';
    const rows = menu.filter(m => cat === null || m.category === cat);
    App.$('#list').innerHTML = rows.length ? rows.map(m => `
        <div class="mm-item">
            <div><div class="mm-name">${esc(m.name)}</div><div class="mm-price">${rm(m.sell_price)}</div></div>
            <div class="mm-step">${qty[m.id]
                ? `<button onclick="ubah(${m.id},-1)" aria-label="-">−</button><span>${qty[m.id]}</span><button onclick="ubah(${m.id},1)" aria-label="+">+</button>`
                : `<button class="mm-add" onclick="ubah(${m.id},1)">${t('tambah')}</button>`}</div>
        </div>`).join('') : `<div class="mm-empty">${t('tiada')}</div>`;
    const n = Object.values(qty).reduce((a, b) => a + b, 0);
    App.$('#bar').hidden = !n;
    App.$('#barQty').textContent = `${t('lihat')} · ${n} ${t('item')}`;
    App.$('#barTotal').textContent = rm(jumlah());
}
function jumlah() { return menu.reduce((s, m) => s + (qty[m.id] || 0) * m.sell_price, 0); }
function ubah(id, d) {
    qty[id] = Math.max(0, Math.min(99, (qty[id] || 0) + d));
    if (!qty[id]) delete qty[id];
    lukis();
    if (!App.$('#sheetCart').hidden) bukaTroli();
}
function bukaTroli() {
    const rows = menu.filter(m => qty[m.id]);
    if (!rows.length) { App.$('#sheetCart').hidden = true; return; }
    App.$('#cartLines').innerHTML = rows.map(m => `
        <div class="mm-line">
            <div class="mm-line-top"><span>${qty[m.id]} × ${esc(m.name)}</span><span>${rm(qty[m.id] * m.sell_price)}</span></div>
            <div class="mm-step" style="margin-top:6px"><button onclick="ubah(${m.id},-1)">−</button><span>${qty[m.id]}</span><button onclick="ubah(${m.id},1)">+</button></div>
            <input class="input" maxlength="200" placeholder="${t('notaItem')}" value="${esc(nota[m.id] || '')}" oninput="nota[${m.id}]=this.value">
        </div>`).join('');
    App.$('#cartTotal').textContent = rm(jumlah());
    App.$('#sheetCart').hidden = false;
}
async function hantar() {
    const btn = App.$('#btnSend');
    btn.disabled = true; btn.textContent = t('sibuk');
    try {
        // Laman sama domain dengan app: Sanctum semak CSRF, jadi ambil kuki XSRF dahulu.
        if (!document.cookie.includes('XSRF-TOKEN=')) await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
        const o = await App.api.post(`${API}/pesanan`, {
            customer_name: App.$('#custName').value.trim() || null,
            notes: App.$('#custNote').value.trim() || null,
            items: Object.entries(qty).map(([id, q]) => ({ item_id: Number(id), quantity: q, note: (nota[id] || '').trim() || null })),
        });
        try { localStorage.setItem(K_MINE, JSON.stringify([o.id, ...mine()].slice(0, 20))); } catch {}
        Object.keys(qty).forEach(k => delete qty[k]);
        Object.keys(nota).forEach(k => delete nota[k]);
        App.$('#custNote').value = '';
        App.$('#sheetCart').hidden = true;
        App.toast(t('berjaya'), 'success');
        teks(); lukis(); bukaSaya();
    } catch (e) {
        App.toast(e.message, 'error');
    } finally {
        btn.disabled = false; btn.textContent = t('hantar');
    }
}
async function bukaSaya() {
    const ids = mine();
    const rows = (await Promise.all(ids.map(id => App.api.get(`${API}/pesanan/${id}`).catch(() => null)))).filter(Boolean);
    App.$('#mineList').innerHTML = rows.map(o => `
        <div class="mm-order">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                <span class="text-xs text-muted">${new Date(o.masa).toLocaleTimeString(lang === 'ms' ? 'ms-MY' : 'en-MY', { hour: '2-digit', minute: '2-digit' })}</span>
                <span class="mm-badge" data-s="${o.status}">${t('s')[o.status] ?? o.status}</span>
            </div>
            ${o.items.map(i => `<div class="text-sm">${i.qty} × ${esc(i.nama)}${i.nota ? ` <span class="text-muted">(${esc(i.nota)})</span>` : ''}</div>`).join('')}
            <div style="text-align:right;font-weight:700;margin-top:6px">${rm(o.jumlah)}</div>
        </div>`).join('');
    App.$('#sheetMine').hidden = false;
}

teks();
muat();
setInterval(() => { if (!App.$('#sheetMine').hidden) bukaSaya(); }, 20000);
</script>
</body>
</html>
