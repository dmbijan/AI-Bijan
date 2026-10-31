/* ============================================================
   AI BIJAN — JS Teras
   API client, tema, toast, modal, helper
   ============================================================ */

const App = (() => {
    const state = {
        user: null,
        tenants: [],
        tenant: null,
        permissions: [],
        theme: null,
    };

    function getPreference(key) {
        try { return localStorage.getItem(key); } catch { return null; }
    }

    function setPreference(key, value) {
        try { localStorage.setItem(key, value); } catch { /* Tetapan pilihan sahaja. */ }
    }

    // ---------- Tema ----------

    function initTheme() {
        const saved = getPreference('aibijan-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved === 'light' || saved === 'dark'
            ? saved
            : (prefersDark ? 'dark' : 'light');
        setTheme(theme, false);
    }

    function setTheme(theme, persist = true) {
        state.theme = theme;
        document.documentElement.setAttribute('data-theme', theme);
        if (persist) setPreference('aibijan-theme', theme);
        const btn = document.querySelector('[data-theme-toggle]');
        if (btn) btn.textContent = theme === 'dark' ? '☀' : '☾';
    }

    function toggleTheme() {
        setTheme(state.theme === 'dark' ? 'light' : 'dark');
    }

    // ---------- API ----------

    function xsrf() {
        const m = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);
        return m ? decodeURIComponent(m[1]) : '';
    }

    function headers(extra = {}) {
        const h = {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': xsrf(),
            ...extra,
        };
        if (state.tenant) h['X-Tenant'] = state.tenant;
        return h;
    }

    async function csrf() {
        await fetch('/sanctum/csrf-cookie', { credentials: 'same-origin' });
    }

    async function request(method, path, body, opts = {}) {
        const res = await fetch(path, {
            method,
            credentials: 'same-origin',
            headers: headers(opts.headers),
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });

        let data = null;
        const ct = res.headers.get('content-type') || '';
        if (ct.includes('application/json')) {
            try { data = await res.json(); } catch { data = null; }
        }

        if (!res.ok) {
            const msg = data?.message || data?.error || `Ralat ${res.status}`;
            const err = new Error(msg);
            err.status = res.status;
            err.data = data;
            throw err;
        }

        return data;
    }

    const api = {
        get: (p, o) => request('GET', p, undefined, o),
        post: (p, b, o) => request('POST', p, b, o),
        put: (p, b, o) => request('PUT', p, b, o),
        del: (p, o) => request('DELETE', p, undefined, o),
        request,
        csrf,
    };

    // ---------- Sesi ----------

    async function loadSession() {
        try {
            const me = await api.get('/api/v1/auth/me');
            state.user = me.user ?? null;
            const navAdmin = document.getElementById('navSuperAdmin');
            if (navAdmin) navAdmin.hidden = !state.user?.platform_admin;
            state.tenants = me.tenants ?? [];
            if (state.tenants.length && !state.tenant) {
                const saved = getPreference('aibijan-tenant');
                const match = state.tenants.find(t => t.ulid === saved);
                state.tenant = match?.ulid ?? state.tenants[0].ulid;
                setPreference('aibijan-tenant', state.tenant);
            }
            renderTenantPicker();
            renderWorkspaceIdentity();
            return true;
        } catch {
            return false;
        }
    }

    async function login(email, password) {
        await csrf();
        const res = await api.post('/api/v1/auth/login', { email, password });
        await csrf();
        await loadSession();
        return res;
    }

    async function register(payload) {
        await csrf();
        const res = await api.post('/api/v1/auth/register', payload);
        await csrf();
        await loadSession();
        return res;
    }

    async function logout() {
        try { await api.post('/api/v1/auth/logout'); } catch { /* abaikan */ }
        try { localStorage.removeItem('aibijan-tenant'); } catch { /* Tetapan pilihan sahaja. */ }
        location.href = '/login';
    }

    // ---------- Toast ----------

    function toastStack() {
        let el = document.querySelector('.toast-stack');
        if (!el) {
            el = document.createElement('div');
            el.className = 'toast-stack';
            el.setAttribute('role', 'status');
            el.setAttribute('aria-live', 'polite');
            document.body.appendChild(el);
        }
        return el;
    }

    function toast(message, type = 'info', duration = 3600) {
        const el = document.createElement('div');
        el.className = `toast ${type}`;
        el.textContent = message;
        toastStack().appendChild(el);
        setTimeout(() => {
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 200);
        }, duration);
    }

    // ---------- Format ----------

    function rupiah(n, currency = 'MYR') {
        const num = Number(n) || 0;
        return new Intl.NumberFormat('ms-MY', {
            style: 'currency',
            currency,
            minimumFractionDigits: 2,
        }).format(num);
    }

    function number(n) {
        return new Intl.NumberFormat('ms-MY').format(Number(n) || 0);
    }

    function date(iso) {
        if (!iso) return '—';
        return new Intl.DateTimeFormat('ms-MY', {
            day: '2-digit', month: 'short', year: 'numeric',
            hour: '2-digit', minute: '2-digit',
        }).format(new Date(iso));
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        })[c]);
    }

    // ---------- Helper DOM ----------

    function $(sel, root = document) { return root.querySelector(sel); }
    function $$(sel, root = document) { return [...root.querySelectorAll(sel)]; }

    function json(el, data) {
        if (typeof el === 'string') el = $(el);
        if (!el) return;
        el.textContent = typeof data === 'string' ? data : JSON.stringify(data, null, 2);
    }

    async function withLoading(btn, fn) {
        if (!btn) return fn();
        btn.dataset.loading = 'true';
        btn.disabled = true;
        try {
            return await fn();
        } finally {
            delete btn.dataset.loading;
            btn.disabled = false;
        }
    }

    // ---------- Sidebar ----------

    function initSidebar() {
        const app = $('.app');
        if (!app) return;

        const sidebar = $('.sidebar', app);
        const toggle = $('[data-sidebar-toggle]', app);
        const scrim = $('.sidebar-scrim', app);
        const mobile = window.matchMedia('(max-width: 900px)');
        let desktopState = 'expanded';
        try {
            if (localStorage.getItem('aibijan-sidebar') === 'collapsed') desktopState = 'collapsed';
        } catch { /* Navigasi kekal berfungsi apabila storan disekat. */ }

        function update(next) {
            const hidden = mobile.matches && next !== 'open';
            if (hidden && sidebar?.contains(document.activeElement)) toggle?.focus();
            app.dataset.sidebar = next;
            if (sidebar) sidebar.inert = hidden;
            if (scrim) scrim.hidden = !mobile.matches || next !== 'open';
            toggle?.setAttribute('aria-expanded', String(next === 'open' || next === 'expanded'));
        }

        toggle?.addEventListener('click', () => {
            if (mobile.matches) {
                update(app.dataset.sidebar === 'open' ? 'closed' : 'open');
            } else {
                desktopState = app.dataset.sidebar === 'collapsed' ? 'expanded' : 'collapsed';
                update(desktopState);
                try { localStorage.setItem('aibijan-sidebar', desktopState); } catch { /* Storan pilihan. */ }
            }
        });
        scrim?.addEventListener('click', () => {
            update('closed');
            toggle?.focus();
        });
        document.addEventListener('keydown', event => {
            if (event.key === 'Escape' && mobile.matches && app.dataset.sidebar === 'open') {
                update('closed');
                toggle?.focus();
            }
        });
        app.addEventListener('focusin', event => {
            if (mobile.matches && app.dataset.sidebar === 'open'
                && !sidebar?.contains(event.target) && event.target !== toggle) update('closed');
        });
        mobile.addEventListener('change', () => update(mobile.matches ? 'closed' : desktopState));
        update(mobile.matches ? 'closed' : desktopState);
    }

    // ---------- Tab boleh akses ----------

    /**
     * Wayar ARIA + papan kekunci untuk widget tab `[role=tablist]`.
     * Pasangkan butang `[data-tab]` dengan panel `[data-panel]` dalam
     * skop dokumen yang sama, urus roving tabindex dan kekunci anak panah.
     * Handler klik sedia ada pada halaman kekal berfungsi — fungsi ini hanya
     * menambah semantik dan navigasi kekunci.
     */
    function initTabs(root = document) {
        $$('[role="tablist"]', root).forEach((list, listIdx) => {
            const scope = list.parentElement || document;
            const tabs = $$('[data-tab]', list);
            if (!tabs.length) return;
            const panels = $$('[data-panel]', scope).filter(p => !p.closest('[role="tablist"]'));

            tabs.forEach((tab, i) => {
                const name = tab.dataset.tab;
                const panel = panels.find(p => p.dataset.panel === name);
                const tabId = tab.id || `tab-${listIdx}-${name}`;
                tab.id = tabId;
                tab.setAttribute('role', 'tab');
                tab.tabIndex = tab.getAttribute('aria-selected') === 'true' ? 0 : -1;
                if (panel) {
                    const panelId = panel.id || `panel-${listIdx}-${name}`;
                    panel.id = panelId;
                    panel.setAttribute('role', 'tabpanel');
                    panel.setAttribute('aria-labelledby', tabId);
                    tab.setAttribute('aria-controls', panelId);
                    if (panel.hidden) panel.tabIndex = -1;
                    else panel.removeAttribute('tabindex');
                }

                tab.addEventListener('click', () => {
                    tabs.forEach(t => { t.tabIndex = t === tab ? 0 : -1; });
                });

                tab.addEventListener('keydown', e => {
                    const keys = ['ArrowLeft', 'ArrowRight', 'Home', 'End'];
                    if (!keys.includes(e.key)) return;
                    e.preventDefault();
                    let next = i;
                    if (e.key === 'ArrowRight') next = (i + 1) % tabs.length;
                    else if (e.key === 'ArrowLeft') next = (i - 1 + tabs.length) % tabs.length;
                    else if (e.key === 'Home') next = 0;
                    else next = tabs.length - 1;
                    tabs[next].focus();
                    tabs[next].click();
                });            });
        });
    }

    // ---------- Pemilih tenant ----------

    function renderTenantPicker() {
        const host = $('#tenantPicker');
        if (!host) return;
        if (state.tenants.length < 2) { host.innerHTML = ''; host.hidden = true; return; }

        host.hidden = false;
        const sel = document.createElement('select');
        sel.className = 'select tenant-select';
        sel.id = 'tenantSelect';
        sel.setAttribute('aria-label', 'Tukar ruang kerja (tenant)');
        state.tenants.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.ulid;
            opt.textContent = t.name ?? t.ulid;
            if (t.ulid === state.tenant) opt.selected = true;
            sel.appendChild(opt);
        });
        sel.addEventListener('change', () => {
            if (!sel.value || sel.value === state.tenant) return;
            state.tenant = sel.value;
            setPreference('aibijan-tenant', sel.value);
            location.reload();
        });
        host.innerHTML = '';
        host.appendChild(sel);
    }

    function renderWorkspaceIdentity() {
        const host = $('#workspaceIdentity');
        if (!host || !state.user) return;

        const tenant = state.tenants.find(item => item.ulid === state.tenant) ?? state.tenants[0];
        const name = state.user.name || state.user.email || 'Pengguna';
        $('#workspaceName').textContent = tenant?.name ?? name;
        $('#workspaceEmail').textContent = tenant ? name : state.user.email || '';
        $('#workspaceAvatar').textContent = name.trim().charAt(0).toUpperCase() || 'U';
        host.hidden = false;
    }

    function initModuleSearch() {
        const input = $('#moduleSearch');
        const empty = $('#moduleSearchEmpty');
        const nav = $('#moduleNavigation');
        if (!input || !nav) return;

        const items = $$('.nav-item[href]', nav);
        const groups = $$('.nav-group', nav);

        function filterModules() {
            const query = input.value.trim().toLocaleLowerCase();
            let visible = 0;
            items.forEach(item => {
                const match = item.textContent.trim().toLocaleLowerCase().includes(query);
                item.hidden = !match;
                if (match) visible++;
            });
            groups.forEach(group => {
                group.hidden = !group.querySelector('.nav-item:not([hidden])');
            });
            if (empty) empty.hidden = visible > 0;
        }

        input.addEventListener('input', filterModules);
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape' && input.value) {
                input.value = '';
                filterModules();
            } else if (event.key === 'Enter') {
                const first = items.find(item => !item.hidden);
                if (first) location.href = first.href;
            }
        });

        document.addEventListener('keydown', event => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                const app = $('.app');
                const toggle = $('[data-sidebar-toggle]', app);
                const mobile = window.matchMedia('(max-width: 900px)').matches;
                if (mobile && app?.dataset.sidebar !== 'open') toggle?.click();
                else if (!mobile && app?.dataset.sidebar === 'collapsed') toggle?.click();
                input.focus();
                input.select();
            }
        });
    }

    // ---------- Init ----------

    function init() {
        initTheme();
        initSidebar();
        initModuleSearch();
        initTabs();
        $('[data-theme-toggle]')?.addEventListener('click', toggleTheme);
        $('[data-logout]')?.addEventListener('click', logout);
    }

    return {
        state, api, init, initTheme, setTheme, toggleTheme,
        loadSession, login, register, logout,
        toast, rupiah, number, date, escapeHtml, $, $$, json, withLoading,
        initTabs, renderTenantPicker,
    };
})();

document.addEventListener('DOMContentLoaded', () => App.init());
window.App = App;
