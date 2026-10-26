@extends('layouts.app')

@section('title', 'Kewangan')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Kewangan</h1>
        <p class="page-desc">Carta akaun, jurnal dan laporan kewangan.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="reports">Laporan</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="coa">Carta Akaun</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="journal">Jurnal</button>
</div>

<section data-panel="reports">
    <div class="row mb-4">
        <button class="btn btn-primary btn-sm" onclick="loadReport('profit-loss')">Untung Rugi</button>
        <button class="btn btn-sm" onclick="loadReport('balance-sheet')">Kunci Kira-kira</button>
        <button class="btn btn-sm" onclick="loadReport('trial-balance')">Imbangan Duga</button>
    </div>
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title" id="reportTitle">Pilih Laporan</h2>
                <p class="card-subtitle" id="reportSub">Klik butang di atas</p>
            </div>
        </div>
        <div id="reportBody">
            <div class="empty"><div class="empty-text">Tiada laporan dipilih.</div></div>
        </div>
    </div>
</section>

<section data-panel="coa" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>Kod</th><th>Nama Akaun</th><th>Jenis</th><th class="table-num">Baki</th></tr></thead>
            <tbody id="coaTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>

<section data-panel="journal" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead><tr><th>No. Jurnal</th><th>Tarikh</th><th>Keterangan</th><th>Status</th></tr></thead>
            <tbody id="journalTable"><tr><td colspan="4"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr></tbody>
        </table>
    </div>
</section>
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

const titles = {
    'profit-loss': 'Untung Rugi',
    'balance-sheet': 'Kunci Kira-kira',
    'trial-balance': 'Imbangan Duga',
};

async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await Promise.all([loadCoa(), loadJournal()]);
}

async function loadReport(type) {
    App.$('#reportTitle').textContent = titles[type] ?? type;
    App.$('#reportSub').textContent = 'Memuat…';
    App.$('#reportBody').innerHTML = '<div class="skeleton" style="height:160px"></div>';

    try {
        const r = await App.api.get(`/api/v1/finance/reports/${type}`);
        const d = r.data ?? {};
        App.$('#reportSub').textContent = d.period ?? '';
        App.$('#reportBody').innerHTML = renderReport(d);
    } catch (e) {
        App.$('#reportSub').textContent = '';
        App.$('#reportBody').innerHTML = `<div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div>`;
    }
}

function renderReport(d) {
    if (Array.isArray(d.lines)) {
        const total = d.lines.reduce((s, l) => s + Number(l.amount ?? 0), 0);
        return `
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>Akaun</th><th class="table-num">Jumlah</th></tr></thead>
                    <tbody>
                        ${d.lines.map(l => `
                            <tr>
                                <td>${App.escapeHtml(l.name ?? l.account ?? '—')}</td>
                                <td class="table-num">${App.rupiah(l.amount ?? 0)}</td>
                            </tr>
                        `).join('')}
                        <tr style="font-weight:600">
                            <td>Jumlah</td>
                            <td class="table-num">${App.rupiah(total)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `;
    }

    return `<pre class="mono" style="max-height:320px;overflow:auto">${App.escapeHtml(JSON.stringify(d, null, 2))}</pre>`;
}

async function loadCoa() {
    try {
        const r = await App.api.get('/api/v1/finance/chart-of-accounts');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#coaTable').innerHTML = rows.length ? rows.map(a => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(a.code ?? '—')}</td>
                <td class="text-sm">${App.escapeHtml(a.name ?? '—')}</td>
                <td><span class="badge badge-neutral">${App.escapeHtml(a.type ?? '—')}</span></td>
                <td class="table-num">${App.rupiah(a.balance ?? 0)}</td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-title">Tiada akaun</div></div></td></tr>';
    } catch (e) {
        App.$('#coaTable').innerHTML = `<tr><td colspan="4"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></td></tr>`;
    }
}

async function loadJournal() {
    try {
        const r = await App.api.get('/api/v1/finance/journal-entries?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        App.$('#journalTable').innerHTML = rows.length ? rows.map(j => `
            <tr>
                <td class="mono text-xs">${App.escapeHtml(j.entry_number ?? '—')}</td>
                <td class="text-xs">${App.date(j.entry_date ?? j.created_at)}</td>
                <td class="text-sm">${App.escapeHtml(j.description ?? '—')}</td>
                <td><span class="badge badge-${j.status === 'posted' ? 'success' : 'warning'}">${App.escapeHtml(j.status ?? '—')}</span></td>
            </tr>
        `).join('') : '<tr><td colspan="4"><div class="empty"><div class="empty-text">Tiada jurnal.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
