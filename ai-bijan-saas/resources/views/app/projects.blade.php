@extends('layouts.app')

@section('title', 'Projek')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Projek</h1>
        <p class="page-desc">Projek, milestone, tugasan dan timesheet.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Projek</div><div class="stat-value" id="sTotal">—</div></div>
    <div class="stat"><div class="stat-label">Aktif</div><div class="stat-value" id="sActive">—</div></div>
    <div class="stat"><div class="stat-label">Selesai</div><div class="stat-value" id="sDone">—</div></div>
    <div class="stat"><div class="stat-label">Lewat</div><div class="stat-value" id="sLate">—</div></div>
</div>

<div class="grid grid-2" id="projectGrid"><div class="skeleton" style="height:140px"></div></div>
@endsection

@push('scripts')
<script>
async function loadAll() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }

    try {
        const r = await App.api.get('/api/v1/projects?per_page=30');
        const rows = r.data?.data ?? r.data ?? [];
        const now = new Date();

        App.$('#sTotal').textContent = App.number(r.data?.total ?? rows.length);
        App.$('#sActive').textContent = rows.filter(p => p.status === 'active' || p.status === 'in_progress').length;
        App.$('#sDone').textContent = rows.filter(p => p.status === 'completed').length;
        App.$('#sLate').textContent = rows.filter(p =>
            p.due_date && new Date(p.due_date) < now && p.status !== 'completed'
        ).length;

        App.$('#projectGrid').innerHTML = rows.length ? rows.map(p => {
            const pct = Math.min(100, Math.max(0, Number(p.progress ?? 0)));
            return `
                <div class="card card-hover">
                    <div class="card-header">
                        <div>
                            <h3 class="card-title">${App.escapeHtml(p.name)}</h3>
                            <p class="card-subtitle">${App.escapeHtml(p.client?.name ?? p.client_name ?? '')}</p>
                        </div>
                        <span class="badge badge-${p.status === 'completed' ? 'success' : p.status === 'active' ? 'info' : 'neutral'}">${App.escapeHtml(p.status ?? '—')}</span>
                    </div>
                    <div class="row-between text-xs text-muted mb-4">
                        <span>Kemajuan</span>
                        <span>${pct}%</span>
                    </div>
                    <div style="height:6px;background:var(--surface-3);border-radius:3px;overflow:hidden">
                        <div style="height:100%;width:${pct}%;background:var(--brand);border-radius:3px"></div>
                    </div>
                    <div class="card-footer">
                        <span class="text-xs text-muted">Tamat: ${App.date(p.due_date ?? p.end_date)}</span>
                    </div>
                </div>
            `;
        }).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada projek</div><div class="empty-text">Cipta projek pertama anda.</div></div></div>';
    } catch (e) {
        App.$('#projectGrid').innerHTML = `<div class="card"><div class="empty"><div class="empty-text">${App.escapeHtml(e.message)}</div></div></div>`;
    }
}

loadAll();
</script>
@endpush
