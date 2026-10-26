@extends('layouts.app')

@section('title', 'Automasi Aliran')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Automasi Aliran</h1>
        <p class="page-desc">Trigger → Kondisi → Tindakan. Dilengkapi idempotency, kill switch dan retry.</p>
    </div>
    <button class="btn btn-primary btn-sm" onclick="createWorkflow()">Cipta Workflow</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Workflow</div><div class="stat-value" id="sTotal">—</div></div>
    <div class="stat"><div class="stat-label">Aktif</div><div class="stat-value" id="sActive">—</div></div>
    <div class="stat"><div class="stat-label">Run Gagal</div><div class="stat-value" id="sFailed">—</div></div>
    <div class="stat"><div class="stat-label">Kill Switch</div><div class="stat-value" id="sKilled">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="wf">Workflow</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="runs">Run</button>
</div>

<section data-panel="wf">
    <div class="grid grid-2" id="wfGrid">
        <div class="skeleton" style="height:160px"></div>
    </div>
</section>

<section data-panel="runs" hidden>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Masa</th><th>Status</th><th>Langkah</th><th>Retry</th><th>Ralat</th></tr>
            </thead>
            <tbody id="runTable">
                <tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr>
            </tbody>
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
        if (tab.dataset.tab === 'runs') loadRuns();
    });
});

async function loadStats() {
    try {
        const r = await App.api.get('/api/v1/flows/stats');
        App.$('#sTotal').textContent = r.data.workflows.total;
        App.$('#sActive').textContent = r.data.workflows.active;
        App.$('#sFailed').textContent = r.data.runs.failed;
        App.$('#sKilled').textContent = r.data.workflows.killed;
    } catch { /* abaikan */ }
}

async function loadWorkflows() {
    const ok = await App.loadSession();
    if (!ok) { location.href = '/login'; return; }
    await loadStats();

    try {
        const res = await App.api.get('/api/v1/flows/workflows?per_page=20');
        const rows = res.data?.data ?? [];

        App.$('#wfGrid').innerHTML = rows.length ? rows.map(w => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(w.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(w.trigger_type)} · v${w.version} · ${(w.steps ?? []).length} langkah</p>
                    </div>
                    <div class="stack" style="gap:4px;align-items:flex-end">
                        <span class="badge badge-${w.is_active ? 'success' : 'neutral'}">${w.is_active ? 'Aktif' : 'Tidak Aktif'}</span>
                        ${w.kill_switch ? '<span class="badge badge-danger">Kill</span>' : ''}
                    </div>
                </div>
                <div class="row text-xs text-muted">
                    <span>${w.run_count ?? 0} run</span>
                    <span>·</span>
                    <span>${w.failure_count ?? 0} gagal</span>
                </div>
                <div class="card-footer">
                    <button class="btn btn-sm" onclick="dryRun(${w.id})">Dry Run</button>
                    <button class="btn btn-sm" onclick="toggleActive(${w.id}, ${!w.is_active})">${w.is_active ? 'Nyahaktif' : 'Aktifkan'}</button>
                    <button class="btn btn-sm" onclick="trigger(${w.id})">Trigger</button>
                    <button class="btn btn-sm btn-danger" onclick="killSwitch(${w.id})">Kill</button>
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada workflow</div><div class="empty-text">Cipta workflow pertama anda.</div></div></div>';
    } catch (e) {
        App.toast('Gagal memuat workflow', 'error');
    }
}

async function loadRuns() {
    try {
        const res = await App.api.get('/api/v1/flows/runs?per_page=30');
        const rows = res.data?.data ?? [];
        App.$('#runTable').innerHTML = rows.length ? rows.map(r => `
            <tr>
                <td class="text-xs">${App.date(r.created_at)}</td>
                <td><span class="badge badge-${r.status === 'completed' ? 'success' : r.status === 'failed' || r.status === 'dead_letter' ? 'danger' : 'warning'}">${r.status}</span></td>
                <td class="text-xs">${r.current_step ?? 0}/${r.total_steps ?? 0}</td>
                <td class="text-xs">${r.retry_count ?? 0}</td>
                <td class="text-xs text-muted">${App.escapeHtml(r.error_message ?? '—')}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada run.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

async function createWorkflow() {
    const name = prompt('Nama workflow:', 'Amaran Stok Rendah');
    if (!name) return;

    try {
        await App.api.post('/api/v1/flows/workflows', {
            name,
            trigger_type: 'manual',
            steps: [{ type: 'send_notification', config: { channel: 'in_app', recipient: 'owner', message: 'Notifikasi ujian' } }],
        });
        App.toast('Workflow dicipta (tidak aktif)', 'success');
        loadWorkflows();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function dryRun(id) {
    try {
        const r = await App.api.post(`/api/v1/flows/workflows/${id}/dry-run`, { payload: {} });
        App.toast(`Dry run: ${r.data.total_steps} langkah, ${r.data.requires_approval ? 'perlu kelulusan' : 'tiada kelulusan'}`, 'success');
    } catch (e) { App.toast(e.message, 'error'); }
}

async function toggleActive(id, active) {
    try {
        await App.api.post(`/api/v1/flows/workflows/${id}/${active ? 'activate' : 'deactivate'}`);
        App.toast(active ? 'Workflow diaktifkan' : 'Workflow dinyahaktifkan', 'success');
        loadWorkflows();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function trigger(id) {
    try {
        const r = await App.api.post(`/api/v1/flows/workflows/${id}/trigger`, {
            payload: {},
            idempotency_key: `ui-${id}-${Date.now()}`,
        });
        App.toast(`Run: ${r.data.status}`, r.data.status === 'completed' ? 'success' : 'info');
        loadRuns();
        loadStats();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function killSwitch(id) {
    if (!confirm('Hidupkan kill switch? Semua run baru akan dihentikan.')) return;
    try {
        await App.api.post(`/api/v1/flows/workflows/${id}/kill-switch`, { enabled: true });
        App.toast('Kill switch dihidupkan', 'success');
        loadWorkflows();
    } catch (e) { App.toast(e.message, 'error'); }
}

loadWorkflows();
</script>
@endpush
