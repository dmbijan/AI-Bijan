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

    // ---------- Tema ----------

    function initTheme() {
        const saved = localStorage.getItem('aibijan-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const theme = saved || (prefersDark ? 'dark' : 'light');
        setTheme(theme, false);
    }

    function setTheme(theme, persist = true) {
        state.theme = theme;
        document.documentElement.setAttribute('data-theme', theme);
        if (persist) localStorage.setItem('aibijan-theme', theme);
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
            state.tenants = me.tenants ?? [];
            if (state.tenants.length && !state.tenant) {
                const saved = localStorage.getItem('aibijan-tenant');
                const match = state.tenants.find(t => t.ulid === saved);
                state.tenant = match?.ulid ?? state.tenants[0].ulid;
                localStorage.setItem('aibijan-tenant', state.tenant);
            }
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
        localStorage.removeItem('aibijan-tenant');
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

        const saved = localStorage.getItem('aibijan-sidebar');
        if (saved) app.dataset.sidebar = saved;

        $('[data-sidebar-toggle]')?.addEventListener('click', () => {
            const isMobile = window.innerWidth <= 900;
            const cur = app.dataset.sidebar;
            const next = isMobile
                ? (cur === 'open' ? 'closed' : 'open')
                : (cur === 'collapsed' ? 'expanded' : 'collapsed');
            app.dataset.sidebar = next;
            if (!isMobile) localStorage.setItem('aibijan-sidebar', next);

            const scrim = $('.sidebar-scrim');
            if (scrim) scrim.hidden = next !== 'open';
        });

        $('.sidebar-scrim')?.addEventListener('click', () => {
            app.dataset.sidebar = 'closed';
            $('.sidebar-scrim').hidden = true;
        });
    }

    // ---------- Init ----------

    function init() {
        initTheme();
        initSidebar();
        $('[data-theme-toggle]')?.addEventListener('click', toggleTheme);
        $('[data-logout]')?.addEventListener('click', logout);
    }

    return {
        state, api, init, initTheme, setTheme, toggleTheme,
        loadSession, login, register, logout,
        toast, rupiah, number, date, escapeHtml, $, $$, json, withLoading,
    };
})();

document.addEventListener('DOMContentLoaded', () => App.init());
window.App = App;
