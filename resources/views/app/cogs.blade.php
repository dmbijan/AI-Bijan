@extends('layouts.app')

@section('title', 'COGS')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">COGS</h1>
        <p class="page-desc">Kos jualan, margin dan analisis keuntungan.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-2">
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Kira Margin Produk</h2>
                <p class="card-subtitle">Masukkan ID produk untuk analisis</p>
            </div>
        </div>
        <div class="field">
            <label class="label" for="productId">ID Produk</label>
            <input class="input" id="productId" type="number" placeholder="1">
        </div>
        <button class="btn btn-primary" onclick="calcMargin()">Kira</button>
        <div id="marginOut" class="mt-4"></div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Laporan Margin</h2>
                <p class="card-subtitle">Ringkasan keuntungan produk</p>
            </div>
        </div>
        <div id="reportOut"><div class="empty"><div class="empty-text">Memuat…</div></div></div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <div><h2 class="card-title">Kos Komponen</h2></div>
      </div>
    <div class="row">
        <input class="input" id="componentId" type="number" placeholder="ID komponen" style="max-width:200px">
        <button class="btn" onclick="loadComponent()">Lihat Komponen</button>
    </div>
    <div id="componentOut" class="mt-4"></div>
</div>
@endsection

@push('scripts')
<script>
async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
}

async function calcMargin() {
    const id = App.$('#productId').value;
    if (!id) { App.toast('ID produk diperlukan', 'error'); return; }

    try {
        const r = await App.api.post(`/api/v1/cogs/costs/product/${id}/calculate`);
        const d = r.data?.data ?? r.data ?? {};
        App.$('#marginOut').innerHTML = `
            <div class="grid grid-2">
                <div class="stat"><div class="stat-label">Kos</div><div class="stat-value">${App.rupiah(d.cost ?? d.total_cost ?? 0)}</div></div>
                <div class="stat"><div class="stat-label">Margin</div><div class="stat-value">${(d.margin_percent ?? d.margin ?? 0).toFixed ? (d.margin_percent ?? d.margin ?? 0).toFixed(1) : (d.margin_percent ?? d.margin ?? 0)}%</div></div>
            </div>
        `;
    } catch (e) {
        App.$('#marginOut').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}

async function loadComponent() {
    const id = App.$('#componentId').value;
    if (!id) { App.toast('ID komponen diperlukan', 'error'); return; }

    try {
        const r = await App.api.get(`/api/v1/cogs/costs/components/${id}`);
        App.$('#componentOut').innerHTML = `<pre class="mono" style="max-height:240px;overflow:auto">${App.escapeHtml(JSON.stringify(r.data, null, 2))}</pre>`;
    } catch (e) {
        App.$('#componentOut').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}

loadAll();
</script>
@endpush
