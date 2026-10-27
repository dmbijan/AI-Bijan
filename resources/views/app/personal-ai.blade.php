@extends('layouts.app')

@section('title', 'Pembantu Peribadi AI')

@section('content')
<div class="page-head">
    <div>
        <h1 class="page-title">Pembantu Peribadi AI</h1>
        <p class="page-desc">9 pembantu khusus dengan memori berterusan dan alert proaktif.</p>
    </div>
    <button class="btn btn-sm" onclick="loadAll()">Muat Semula</button>
</div>

<div class="grid grid-4 mb-5">
    <div class="stat"><div class="stat-label">Jumlah Tugas</div><div class="stat-value" id="sTotal">—</div></div>
    <div class="stat"><div class="stat-label">Aktif</div><div class="stat-value" id="sActive">—</div></div>
    <div class="stat"><div class="stat-label">Menunggu Kelulusan</div><div class="stat-value" id="sPending">—</div></div>
    <div class="stat"><div class="stat-label">Item Memori</div><div class="stat-value" id="sMemory">—</div></div>
</div>

<div class="tabs" role="tablist">
    <button class="tab" role="tab" aria-selected="true" data-tab="assist">Pembantu</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="tasks">Tugas</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="memory">Memori</button>
    <button class="tab" role="tab" aria-selected="false" data-tab="alerts">Alert</button>
</div>

<section data-panel="assist">
    <div class="grid grid-2" id="assistGrid">
        <div class="skeleton" style="height:120px"></div>
    </div>
</section>

<section data-panel="tasks" hidden>
    <div class="card mb-4">
        <div class="card-header">
            <div><h2 class="card-title">Cipta Tugas</h2></div>
        </div>
        <div class="grid grid-2" style="gap:12px">
            <div class="field" style="margin:0">
                <label class="label" for="taskAssistant">Pembantu</label>
                <select class="select" id="taskAssistant"></select>
            </div>
            <div class="field" style="margin:0">
                <label class="label" for="taskRisk">Tahap Risiko</label>
                <select class="select" id="taskRisk">
                    <option value="low">Rendah</option>
                    <option value="moderate">Sederhana</option>
                    <option value="high">Tinggi — perlu kelulusan</option>
                    <option value="critical">Kritikal — perlu kelulusan</option>
                </select>
            </div>
        </div>
        <div class="field mt-3">
            <label class="label" for="taskObjective">Objektif</label>
            <input class="input" id="taskObjective" placeholder="Contoh: Semak invois tertunggak">
        </div>
        <button class="btn btn-primary" id="createTaskBtn" onclick="createTask()">Cipta Tugas</button>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>Objektif</th><th>Pembantu</th><th>Risiko</th><th>Status</th><th>Tindakan</th></tr>
            </thead>
            <tbody id="taskTable">
                <tr><td colspan="5"><div class="empty"><div class="empty-text">Memuat…</div></div></td></tr>
            </tbody>
        </table>
    </div>
</section>

<section data-panel="memory" hidden>
    <div class="grid grid-2">
        <div class="card">
            <div class="card-header">
                <div><h2 class="card-title">Simpan Memori</h2></div>
            </div>
            <div class="field">
                <label class="label" for="memType">Jenis</label>
                <select class="select" id="memType">
                    <option value="preference">Preferensi</option>
                    <option value="context">Konteks Kerja</option>
                    <option value="insight">Insight</option>
                    <option value="knowledge">Pengetahuan</option>
                    <option value="history">Sejarah</option>
                </select>
            </div>
            <div class="field">
                <label class="label" for="memCategory">Kategori</label>
                <input class="input" id="memCategory" placeholder="laporan, jualan, kewangan">
            </div>
            <div class="field">
                <label class="label" for="memContent">Kandungan</label>
                <textarea class="textarea" id="memContent" rows="2"></textarea>
            </div>
            <button class="btn btn-primary" onclick="saveMemory()">Simpan</button>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Memori Aktif</h2>
                    <p class="card-subtitle" id="memCount">—</p>
                </div>
                <button class="btn btn-sm btn-danger" onclick="purgeMemory()">Padam Semua</button>
            </div>
            <div class="stack" id="memList">
                <div class="skeleton" style="height:40px"></div>
            </div>
        </div>
    </div>
</section>

<section data-panel="alerts" hidden>
    <div class="stack" id="alertList">
        <div class="skeleton" style="height:80px"></div>
    </div>
</section>
@endsection

@push('scripts')
<script>
const pa = { assistants: [] };

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

    try {
        const res = await App.api.get('/api/v1/personal-ai/assistants');
        pa.assistants = Object.entries(res.data ?? {}).map(([key, v]) => ({ key, ...v }));

        App.$('#assistGrid').innerHTML = pa.assistants.map(a => `
            <div class="card card-hover">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(a.name)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(a.role ?? '')}</p>
                    </div>
                </div>
                <p class="card-body text-sm">${App.escapeHtml(a.description ?? '')}</p>
            </div>
        `).join('');

        App.$('#taskAssistant').innerHTML = pa.assistants
            .map(a => `<option value="${a.key}">${App.escapeHtml(a.name)}</option>`).join('');
    } catch (e) {
        App.toast('Gagal memuat pembantu: ' + e.message, 'error');
    }

    await Promise.all([loadStats(), loadTasks(), loadMemory(), loadAlerts()]);
}

async function loadStats() {
    try {
        const t = await App.api.get('/api/v1/personal-ai/tasks-stats');
        App.$('#sTotal').textContent = t.data.total;
        App.$('#sActive').textContent = t.data.active;
        App.$('#sPending').textContent = t.data.awaiting_approval;
    } catch { /* abaikan */ }

    try {
        const m = await App.api.get('/api/v1/personal-ai/memory/summary');
        App.$('#sMemory').textContent = m.data.total;
    } catch { /* abaikan */ }
}

async function loadTasks() {
    try {
        const res = await App.api.get('/api/v1/personal-ai/tasks?per_page=20');
        const rows = res.data?.data ?? [];
        App.$('#taskTable').innerHTML = rows.length ? rows.map(t => `
            <tr>
                <td class="text-sm">${App.escapeHtml(t.objective)}</td>
                <td><span class="badge badge-brand">${App.escapeHtml(t.assistant_type)}</span></td>
                <td><span class="badge badge-${['high','critical'].includes(t.risk_level) ? 'danger' : 'neutral'}">${t.risk_level}</span></td>
                <td><span class="badge badge-${t.status === 'Completed' ? 'success' : t.status === 'Failed' ? 'danger' : 'warning'}">${t.status}</span></td>
                <td>${actionsFor(t)}</td>
            </tr>
        `).join('') : '<tr><td colspan="5"><div class="empty"><div class="empty-text">Tiada tugas.</div></div></td></tr>';
    } catch { /* abaikan */ }
}

function actionsFor(t) {
    const map = {
        Draft: ['plan', 'Rancang'],
        Planned: ['queue', 'Queue'],
        Queued: ['start', 'Mula'],
        Running: ['complete', 'Selesai'],
    };
    const entry = map[t.status];
    if (!entry) return '<span class="text-xs text-muted">—</span>';
    return `<button class="btn btn-sm" onclick="transitionTask('${t.id}','${entry[0]}')">${entry[1]}</button>`;
}

async function createTask() {
    const objective = App.$('#taskObjective').value.trim();
    if (!objective) { App.toast('Objektif diperlukan', 'error'); return; }

    await App.withLoading(App.$('#createTaskBtn'), async () => {
        try {
            await App.api.post('/api/v1/personal-ai/tasks', {
                objective,
                assistant_type: App.$('#taskAssistant').value,
                risk_level: App.$('#taskRisk').value,
            });
            App.$('#taskObjective').value = '';
            App.toast('Tugas dicipta', 'success');
            loadTasks(); loadStats();
        } catch (e) { App.toast(e.message, 'error'); }
    });
}

async function transitionTask(id, action) {
    try {
        await App.api.post(`/api/v1/personal-ai/tasks/${id}/transition`, {
            action,
            result: action === 'complete' ? { reference: 'UI-' + Date.now() } : {},
        });
        App.toast('Status dikemas kini', 'success');
        loadTasks(); loadStats();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function loadMemory() {
    try {
        const res = await App.api.get('/api/v1/personal-ai/memory?limit=20');
        const rows = res.data ?? [];
        App.$('#memCount').textContent = `${rows.length} item`;
        App.$('#memList').innerHTML = rows.length ? rows.map(m => `
            <div class="row-between" style="padding:8px 0;border-bottom:1px solid var(--border)">
                <div style="min-width:0">
                    <div class="text-sm">${App.escapeHtml(m.content_text)}</div>
                    <div class="text-xs text-muted">${m.memory_type} · ${App.escapeHtml(m.category ?? '')}</div>
                </div>
                <button class="btn btn-sm" onclick="deleteMemory(${m.id})">Padam</button>
            </div>
        `).join('') : '<div class="empty"><div class="empty-text">Tiada memori.</div></div>';
    } catch { /* abaikan */ }
}

async function saveMemory() {
    const content = App.$('#memContent').value.trim();
    if (!content) { App.toast('Kandungan diperlukan', 'error'); return; }

    try {
        await App.api.post('/api/v1/personal-ai/memory', {
            memory_type: App.$('#memType').value,
            category: App.$('#memCategory').value.trim() || 'umum',
            content_text: content,
        });
        App.$('#memContent').value = '';
        App.$('#memCategory').value = '';
        App.toast('Memori disimpan', 'success');
        loadMemory(); loadStats();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function deleteMemory(id) {
    try {
        await App.api.del(`/api/v1/personal-ai/memory/${id}`);
        App.toast('Memori dipadam', 'success');
        loadMemory(); loadStats();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function purgeMemory() {
    if (!confirm('Padam SEMUA memori peribadi? Rekod kewangan statutori dikekalkan.')) return;
    try {
        const r = await App.api.post('/api/v1/personal-ai/memory/purge');
        App.toast(`Dipadam: ${r.data.memory_items_deleted} item`, 'success');
        loadMemory(); loadStats();
    } catch (e) { App.toast(e.message, 'error'); }
}

async function loadAlerts() {
    try {
        const res = await App.api.get('/api/v1/personal-ai/alerts');
        const rows = res.data ?? [];
        App.$('#alertList').innerHTML = rows.length ? rows.map(a => `
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 class="card-title">${App.escapeHtml(a.title)}</h3>
                        <p class="card-subtitle">${App.escapeHtml(a.what_happened)}</p>
                    </div>
                    <span class="badge badge-${a.priority === 'critical' ? 'danger' : a.priority === 'high' ? 'warning' : 'neutral'}">${a.priority}</span>
                </div>
                <p class="card-body text-sm"><strong>Cadangan:</strong> ${App.escapeHtml(a.suggested_action)}</p>
                <div class="card-footer">
                    <span class="badge badge-neutral">Kepastian: ${a.confidence}</span>
                    ${a.can_automate ? '<span class="badge badge-success">Boleh automatik</span>' : '<span class="badge badge-neutral">Perlu manual</span>'}
                </div>
            </div>
        `).join('') : '<div class="card"><div class="empty"><div class="empty-title">Tiada alert</div><div class="empty-text">Semua dalam keadaan baik.</div></div></div>';
    } catch { /* abaikan */ }
}

loadAll();
</script>
@endpush
