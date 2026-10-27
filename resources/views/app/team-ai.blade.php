@extends('layouts.app')

@section('title', 'Team AI')

@section('content')
<style>
    .team-ai-grid { display: grid; grid-template-columns: minmax(280px, .75fr) minmax(0, 1.4fr); gap: 16px; align-items: start; }
    .team-ai-assets { display: grid; gap: 10px; }
    .team-ai-asset { padding: 12px; border: 1px solid var(--border); border-radius: var(--radius-md); background: var(--surface-1); }
    .team-ai-content { max-height: 260px; overflow: auto; margin: 10px 0 0; white-space: pre-wrap; overflow-wrap: anywhere; font-size: 12px; }
    .team-ai-repo { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--border); }
    .team-ai-repo:last-child { border-bottom: 0; }
    @media (max-width: 900px) { .team-ai-grid { grid-template-columns: 1fr; } }
</style>

<div class="page-head">
    <div>
        <h1 class="page-title">Team AI</h1>
        <p class="page-desc">Urus aset AI pasukan dalam Git dan kongsi konteks kepada ruang kerja AI Bijan.</p>
    </div>
</div>

<div class="grid grid-3 mb-5">
    <a class="card card-hover" href="/app/ai-agents"><h2 class="card-title">Ejen &amp; arahan</h2><p class="card-subtitle">Urus dan jalankan ejen AI Bijan.</p></a>
    <a class="card card-hover" href="/app/openbot"><h2 class="card-title">Chat AI</h2><p class="card-subtitle">Buka OpenBot atau chat native.</p></a>
    <a class="card card-hover" href="/app/flows"><h2 class="card-title">Workflow</h2><p class="card-subtitle">Automasi aliran kerja pasukan.</p></a>
</div>

<div class="team-ai-grid">
    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Repository pasukan</h2>
                <p class="card-subtitle">Sambungkan repository GitHub yang mengandungi aset AI.</p>
            </div>
        </div>
        <form id="teamAiRepoForm">
            <div class="field">
                <label class="label" for="teamAiOwner">Pemilik GitHub</label>
                <input class="input" id="teamAiOwner" maxlength="39" autocomplete="off" required>
            </div>
            <div class="field">
                <label class="label" for="teamAiRepository">Nama repository</label>
                <input class="input" id="teamAiRepository" maxlength="100" autocomplete="off" required>
            </div>
            <div class="field">
                <label class="label" for="teamAiBranch">Branch</label>
                <input class="input" id="teamAiBranch" value="main" maxlength="200" autocomplete="off" required>
            </div>
            <div class="field">
                <label class="label" for="teamAiToken">GitHub token (pilihan untuk repo private)</label>
                <input class="input" id="teamAiToken" type="password" maxlength="500" autocomplete="new-password">
                <p class="field-hint">Dienkripsi pada backend. Token tidak dipaparkan semula. Beri akses baca sahaja kepada repository ini.</p>
            </div>
            <button class="btn btn-primary btn-block" id="teamAiSaveBtn" type="submit">Simpan repository</button>
        </form>

        <div class="mt-5">
            <h3 class="card-title">Repository disambungkan</h3>
            <div id="teamAiRepositories" class="stack mt-3"><div class="skeleton" style="height:48px"></div></div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Aset pasukan</h2>
                <p class="card-subtitle">Skills, rules, agents, MCP config, workflows, learnings, wiki dan models yang disegerakkan.</p>
            </div>
            <button type="button" class="btn btn-sm" id="teamAiRefreshBtn">Segar semula</button>
        </div>
        <div class="field">
            <label class="label" for="teamAiCategory">Kategori aset</label>
            <select class="select" id="teamAiCategory">
                <option value="">Semua kategori</option>
                <option value="skills">Skills</option><option value="rules">Rules</option>
                <option value="agents">Agents</option><option value="hooks">Hooks</option>
                <option value="workflows">Workflows</option><option value="mcp">MCP</option>
                <option value="culture">Culture</option><option value="learnings">Learnings</option>
                <option value="wiki">Team wiki &amp; docs</option><option value="models">Models</option>
                <option value="packages">Packages</option>
            </select>
        </div>
        <div id="teamAiAssets" class="team-ai-assets"><div class="skeleton" style="height:90px"></div></div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const repositoriesEl = document.querySelector('#teamAiRepositories');
    const assetsEl = document.querySelector('#teamAiAssets');
    const categoryEl = document.querySelector('#teamAiCategory');

    function showError(container, message) {
        container.replaceChildren();
        const error = document.createElement('div');
        error.className = 'alert alert-warning';
        error.textContent = message;
        container.append(error);
    }

    async function loadRepositories() {
        try {
            const response = await App.api.get('/api/v1/team-ai/repositories');
            repositoriesEl.replaceChildren();
            if (!response.data.length) {
                repositoriesEl.textContent = 'Belum ada repository disambungkan.';
                return;
            }
            response.data.forEach((repository) => {
                const row = document.createElement('div');
                row.className = 'team-ai-repo';
                const info = document.createElement('div');
                const title = document.createElement('strong');
                title.textContent = `${repository.owner}/${repository.repository}`;
                const detail = document.createElement('div');
                detail.className = 'text-xs text-muted';
                detail.textContent = `${repository.branch} · ${repository.asset_count} aset · ${repository.last_synced_at ? new Date(repository.last_synced_at).toLocaleString('ms-MY') : 'belum disegerakkan'}`;
                info.append(title, detail);
                const actions = document.createElement('div');
                actions.className = 'row';
                const sync = document.createElement('button');
                sync.type = 'button'; sync.className = 'btn btn-sm'; sync.textContent = 'Sync';
                sync.addEventListener('click', () => syncRepository(repository.id, sync));
                const remove = document.createElement('button');
                remove.type = 'button'; remove.className = 'btn btn-sm btn-danger'; remove.textContent = 'Padam';
                remove.addEventListener('click', () => deleteRepository(repository.id));
                actions.append(sync, remove);
                row.append(info, actions);
                repositoriesEl.append(row);
            });
        } catch (error) {
            showError(repositoriesEl, `Gagal memuat repository: ${error.message}`);
        }
    }

    async function loadAssets() {
        try {
            const query = categoryEl.value ? `?category=${encodeURIComponent(categoryEl.value)}` : '';
            const response = await App.api.get(`/api/v1/team-ai/assets${query}`);
            assetsEl.replaceChildren();
            if (!response.data.data.length) {
                assetsEl.textContent = 'Tiada aset untuk kategori ini. Sambungkan repository dan jalankan sync.';
                return;
            }
            response.data.data.forEach((asset) => {
                const card = document.createElement('article');
                card.className = 'team-ai-asset';
                const heading = document.createElement('div');
                heading.className = 'row-between';
                const title = document.createElement('strong');
                title.textContent = asset.title;
                const badge = document.createElement('span');
                badge.className = 'badge badge-neutral';
                badge.textContent = asset.category;
                heading.append(title, badge);
                const path = document.createElement('div');
                path.className = 'text-xs text-muted';
                path.textContent = `${asset.repository?.owner ?? ''}/${asset.repository?.repository ?? ''} · ${asset.path}`;
                const content = document.createElement('pre');
                content.className = 'team-ai-content';
                content.textContent = asset.content;
                card.append(heading, path, content);
                assetsEl.append(card);
            });
        } catch (error) {
            showError(assetsEl, `Gagal memuat aset: ${error.message}`);
        }
    }

    async function syncRepository(id, button) {
        await App.withLoading(button, async () => {
            try {
                const result = await App.api.post(`/api/v1/team-ai/repositories/${id}/sync`, {});
                App.toast(`${result.data.imported} aset disegerakkan.`, 'success');
                await Promise.all([loadRepositories(), loadAssets()]);
            } catch (error) {
                App.toast(error.message, 'error');
            }
        });
    }

    async function deleteRepository(id) {
        if (!window.confirm('Padam sambungan repository dan aset yang disegerakkan?')) return;
        try {
            await App.api.request('DELETE', `/api/v1/team-ai/repositories/${id}`);
            await Promise.all([loadRepositories(), loadAssets()]);
            App.toast('Repository dipadam.', 'success');
        } catch (error) {
            App.toast(error.message, 'error');
        }
    }

    document.querySelector('#teamAiRepoForm').addEventListener('submit', async (event) => {
        event.preventDefault();
        const button = document.querySelector('#teamAiSaveBtn');
        const payload = {
            owner: document.querySelector('#teamAiOwner').value.trim(),
            repository: document.querySelector('#teamAiRepository').value.trim(),
            branch: document.querySelector('#teamAiBranch').value.trim(),
            access_token: document.querySelector('#teamAiToken').value || null,
        };
        await App.withLoading(button, async () => {
            try {
                await App.api.post('/api/v1/team-ai/repositories', payload);
                document.querySelector('#teamAiToken').value = '';
                await loadRepositories();
                App.toast('Repository disimpan.', 'success');
            } catch (error) {
                App.toast(error.message, 'error');
            }
        });
    });

    document.querySelector('#teamAiRefreshBtn').addEventListener('click', loadAssets);
    categoryEl.addEventListener('change', loadAssets);
    App.loadSession().then(async (ok) => {
        if (!ok) { window.location.href = '/login'; return; }
        await Promise.all([loadRepositories(), loadAssets()]);
    });
})();
</script>
@endpush
