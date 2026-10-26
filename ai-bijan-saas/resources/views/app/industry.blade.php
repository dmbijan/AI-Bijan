@extends('layouts.app')

@section('title', 'Industry Packs')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Industry Packs</h1>
        <p class="page-desc">Templat industri siap pakai dengan dashboard, peranan dan aliran kerja.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="card mb-5">
    <div class="card-header">
        <div>
            <h2 class="card-title">Cari Industri Sesuai</h2>
            <p class="card-subtitle">Berdasarkan jenis perniagaan anda</p>
        </div>
    </div>
    <div class="row">
        <input class="input" id="industryKey" placeholder="cth: fnb, retail, clinic, construction" style="max-width:300px">
        <button class="btn btn-primary" onclick="recommend()">Cadangkan</button>
    </div>
    <div id="recOut" class="mt-4"></div>
</div>

<div class="grid grid-3" id="packGrid"><div class="skeleton" style="height:130px"></div></div>
@endsection

@push('scripts')
<script>
async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }

    try {
        const r = await App.api.get('/api/v1/industry-packs/list');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#packGrid').innerHTML = rows.length ? Object.entries(rows).map(([key, v]) => {
            const d = typeof v === 'object' ? v : { name: v };
            return `
                <div class="card card-hover">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">${App.escapeHtml(d.name ?? key)}</h3>
                            <p class="card-subtitle">${App.escapeHtml(key)}</p>
                        </div>
                    </div>
                    <p class="card-body text-sm">${App.escapeHtml((d.description ?? '').slice(0, 110))}</p>
                    <div class="card-footer">
                        <button class="btn btn-sm" onclick="applyPack('${key}')">Guna</button>
                    </div>
                </div>
            `;
        }).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada industry pack</div></div></div>';
    } catch (e) {
        App.$('#packGrid').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`;
    }
}

async function recommend() {
    const key = App.$('#industryKey').value.trim();
    if (!key) { App.toast('Masukkan kunci industri', 'error'); return; }

    try {
        const r = await App.api.post('/api/v1/industry-packs/recommend', { industry_key: key });
        App.$('#recOut').innerHTML = `<pre class="mono" style="max-height:220px;overflow:auto">${App.escapeHtml(JSON.stringify(r.data, null, 2))}</pre>`;
    } catch (e) {
        App.$('#recOut').innerHTML = `<div class="alert alert-danger">${App.escapeHtml(e.message)}</div>`;
    }
}

async function applyPack(key) {
    if (!confirm(`Guna industry pack "${key}"? Ini akan mengubah konfigurasi tenant.`)) return;
    try {
        await App.api.post('/api/v1/industry-packs/apply', { industry_key: key });
        App.toast('Industry pack digunakan', 'success');
    } catch (e) { App.toast(e.message, 'error'); }
}

loadAll();
</script>
@endpush
