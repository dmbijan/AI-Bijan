@extends('layouts.app')

@section('title', 'Inventori')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Inventori</h1>
        <p class="page-desc">Stok, pergerakan dan baki mengikut gudang.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="items">Item</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="moves">Pergerakan</button>
</div>

<section data-panel="items">
    <div class="card mb-4">
        <div class="row">
            <input class="input" id="itemSearch" placeholder="Cari SKU atau nama…" style="max-width:280px" oninput="loadItems()">
            <button class="btn" onclick="openItemDialog()">Tambah Item</button>
        </div>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>SKU</th><th>Nama</th><th class="table-num">Baki</th><th class="table-num">Reorder</th><th>Status</th></tr>
            </thead>
            <tbody id="itemTable">
                <tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr>
            </tbody>
        </table>
    </div>
</section>

<section data-panel="moves" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Masa</th><th>Item</th><th>Jenis</th><th class="table-num">Kuantiti</th><th>Status</th></tr>
            </thead>
            <tbody id="moveTable">
                <tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr>
            </tbody>
        </table>
    </div>
</section>

<div class="dialog-backdrop" id="itemDialog" hidden>
    <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="itemDialogTitle">
        <div class="dialog-header">
            <h2 class="card-title" id="itemDialogTitle">Tambah Item</h2>
            <button class="icon-btn" onclick="closeItemDialog()" aria-label="Tutup">✕</button>
        </div>
        <div class="dialog-body">
            <div class="field">
                <label class="label" for="iSku">SKU</label>
                <input class="input" id="iSku" placeholder="SKU-001">
            </div>
            <div class="field">
                <label class="label" for="iName">Nama</label>
                <input class="input" id="iName">
            </div>
            <div class="grid grid-2" style="gap:12px">
                <div class="field" style="margin:0">
                    <label class="label" for="iPrice">Harga Jual</label>
                    <input class="input" id="iPrice" type="number" step="0.01" value="0">
                </div>
                <div class="field" style="margin:0">
                    <label class="label" for="iReorder">Tahap Reorder</label>
                    <input class="input" id="iReorder" type="number" value="5">
                </div>
            </div>
            <div id="itemError" class="alert alert-danger mt-3" hidden></div>
        </div>
        <div class="dialog-footer">
            <button class="btn" onclick="closeItemDialog()">Batal</button>
            <button class="btn btn-primary" id="itemSaveBtn" onclick="saveItem()">Simpan</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-tab]').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('[data-tab]').forEach(t => t.setAttribute('aria-selected', 'false'));
        tab.setAttribute('aria-selected', 'true');
        document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== tab.dataset.tab);
    });
});

async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await Promise.all([loadItems(), loadMoves()]);
}

async function loadItems() {
    const q = App.$('#itemSearch')?.value?.trim() ?? '';
    try {
        const r = await App.api.get('/api/v1/inventory/items' + (q ? `?search=${encodeURIComponent(q)}` : ''));
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#itemTable').innerHTML = rows.length ? rows.map(i => {
            const stock = i.stock ?? i.balance ?? 0;
            const reorder = i.reorder_level ?? 0;
            const low = stock <= reorder;
            return `
                <tr>
                    <td class="mono text-xs">${App.escapeHtml(i.sku ?? '—')}</td>
                    <td>${App.escapeHtml(i.name ?? '—')}</td>
                    <td class="table-num">${App.number(stock)}</td>
                    <td class="table-num">${App.number(reorder)}</td>
                    <td><span class="badge badge-${low ? 'warning' : 'success'}">${low ? 'Rendah' : 'OK'}</span></td>
                </tr>
            `;
        }).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-title">Tiada item</div><div class="empty-text">Tambah item pertama anda.</div></div></td></tr>';
    } catch (e) {
        App.$('#itemTable').innerHTML = `<tr><td colspan="5"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadMoves() {
    try {
        const r = await App.api.get('/api/v1/inventory/movements?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#moveTable').innerHTML = rows.length ? rows.map(m => `
            <tr>
                <td class="text-xs">${App.date(m.created_at)}</td>
                <td class="text-sm">${App.escapeHtml(m.item?.name ?? m.item_name ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(m.type ?? m.movement_type ?? '—')}</span></td>
                <td class="table-num">${App.number(m.quantity ?? 0)}</td>
                <td><span class="badge badge-${m.status === 'posted' ? 'success' : 'warning'}">${App.escapeHtml(m.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada pergerakan.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

function openItemDialog() {
    App.$('#itemDialog').hidden = false;
    App.$('#iSku').focus();
}

function closeItemDialog() {
    App.$('#itemDialog').hidden = true;
    App.$('#itemError').hidden = true;
    ['#iSku', '#iName', '#iPrice', '#iReorder'].forEach(s => App.$(s).value = '');
}

async function saveItem() {
    const payload = {
        sku: App.$('#iSku').value.trim(),
        name: App.$('#iName').value.trim(),
        price: Number(App.$('#iPrice').value) || 0,
        reorder_level: Number(App.$('#iReorder').value) || 0,
    };

    if (!payload.sku || !payload.name) {
        App.$('#itemError').textContent = 'SKU dan nama diperlukan';
        App.$('#itemError').hidden = false;
        return;
    }

    await App.withLoading(App.$('#itemSaveBtn'), async () => {
        try {
            await App.api.post('/api/v1/inventory/items', payload);
            App.toast('Item disimpan', 'success');
            closeItemDialog();
            loadItems();
        } catch (e) {
            const msg = e.data?.errors ? Object.values(e.data.errors).flat()[0] : e.message;
            App.$('#itemError').textContent = msg;
            App.$('#itemError').hidden = false;
        }
    });
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && !App.$('#itemDialog').hidden) closeItemDialog();
});

loadAll();
</script>
@endpush
