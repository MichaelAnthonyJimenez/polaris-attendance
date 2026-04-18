import './bootstrap';

function debounce(fn, waitMs) {
    let t = null;
    return (...args) => {
        if (t) window.clearTimeout(t);
        t = window.setTimeout(() => fn(...args), waitMs);
    };
}

function escapeHtml(str) {
    return String(str ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function createSuggestionItem(result) {
    const a = document.createElement('a');
    a.href = result.url;
    a.className =
        'block px-4 py-3 hover:bg-white/5 focus:bg-white/5 focus:outline-none transition';

    const top = document.createElement('div');
    top.className = 'flex items-center justify-between gap-3';

    const label = document.createElement('div');
    label.className = 'text-sm text-slate-100 font-medium truncate';
    label.innerHTML = escapeHtml(result.label);

    const type = document.createElement('div');
    type.className =
        'text-[11px] px-2 py-0.5 rounded-full bg-white/10 border border-white/10 text-slate-300 shrink-0';
    type.innerHTML = escapeHtml(result.type);

    top.appendChild(label);
    top.appendChild(type);

    a.appendChild(top);

    if (result.meta) {
        const meta = document.createElement('div');
        meta.className = 'text-xs text-slate-400 truncate mt-0.5';
        meta.innerHTML = escapeHtml(result.meta);
        a.appendChild(meta);
    }

    return a;
}

function createSuggestionSection(title) {
    const wrap = document.createElement('div');
    wrap.className = 'px-4 py-2 text-[11px] uppercase tracking-wider text-slate-400 bg-white/5 border-t border-white/10';
    wrap.textContent = title;
    return wrap;
}

function initGlobalSearch() {
    const inputs = Array.from(document.querySelectorAll('input[data-global-search="true"]'));
    if (inputs.length === 0) return;

    const state = new Map(); // input -> { container, controller, activeIndex, items }

    function getContainerForInput(input) {
        const target = input.getAttribute('data-global-search-target') || 'top';
        return document.getElementById(`global-search-suggestions-${target}`);
    }

    function hide(input) {
        const s = state.get(input);
        if (!s?.container) return;
        s.container.classList.add('hidden');
        s.container.innerHTML = '';
        s.activeIndex = -1;
        s.items = [];
    }

    function show(input) {
        const s = state.get(input);
        if (!s?.container) return;
        s.container.classList.remove('hidden');
    }

    function setActive(input, idx) {
        const s = state.get(input);
        if (!s) return;
        s.activeIndex = idx;
        s.items.forEach((el, i) => {
            el.classList.toggle('bg-white/5', i === idx);
        });
    }

    async function fetchSuggestions(input, query) {
        const s = state.get(input);
        if (!s) return;

        if (s.controller) s.controller.abort();
        s.controller = new AbortController();

        const url = new URL('/search/suggest', window.location.origin);
        url.searchParams.set('search', query);

        const res = await fetch(url.toString(), {
            headers: { 'Accept': 'application/json' },
            signal: s.controller.signal,
        });

        if (!res.ok) return [];
        const data = await res.json();
        return Array.isArray(data?.results) ? data.results : [];
    }

    const debouncedUpdate = debounce(async (input) => {
        const s = state.get(input);
        if (!s?.container) return;

        const q = (input.value || '').trim();
        if (q.length < 2) {
            hide(input);
            return;
        }

        let results = [];
        try {
            results = await fetchSuggestions(input, q);
        } catch (e) {
            // Abort is expected when typing quickly.
            return;
        }

        s.container.innerHTML = '';
        s.items = [];
        s.activeIndex = -1;

        if (!results.length) {
            const empty = document.createElement('div');
            empty.className = 'px-4 py-3 text-sm text-slate-400';
            empty.textContent = 'No results';
            s.container.appendChild(empty);
            show(input);
            return;
        }

        const order = ['User', 'Driver', 'Attendance'];
        const groups = new Map();
        results.forEach((r) => {
            const key = r?.type ? String(r.type) : 'Other';
            if (!groups.has(key)) groups.set(key, []);
            groups.get(key).push(r);
        });

        const keys = [
            ...order.filter((k) => groups.has(k)),
            ...Array.from(groups.keys()).filter((k) => !order.includes(k)),
        ];

        keys.forEach((key, idx) => {
            // Only add the header if there is at least one result.
            const header = createSuggestionSection(key + 's');
            // First header should not have top border in most cases.
            if (idx === 0) header.classList.remove('border-t');
            s.container.appendChild(header);

            groups.get(key).forEach((r) => {
                const item = createSuggestionItem(r);
                s.container.appendChild(item);
                s.items.push(item);
            });
        });

        show(input);
    }, 180);

    inputs.forEach((input) => {
        const container = getContainerForInput(input);
        if (!container) return;

        state.set(input, { container, controller: null, activeIndex: -1, items: [] });

        input.addEventListener('input', () => debouncedUpdate(input));

        input.addEventListener('focus', () => {
            if ((input.value || '').trim().length >= 2) debouncedUpdate(input);
        });

        input.addEventListener('keydown', (e) => {
            const s = state.get(input);
            if (!s || s.container.classList.contains('hidden')) return;

            if (e.key === 'Escape') {
                hide(input);
                return;
            }

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                const next = Math.min(s.items.length - 1, (s.activeIndex ?? -1) + 1);
                setActive(input, next);
                return;
            }

            if (e.key === 'ArrowUp') {
                e.preventDefault();
                const prev = Math.max(0, (s.activeIndex ?? 0) - 1);
                setActive(input, prev);
                return;
            }

            if (e.key === 'Enter' && s.activeIndex >= 0) {
                e.preventDefault();
                const el = s.items[s.activeIndex];
                if (el && el.getAttribute('href')) {
                    window.location.href = el.getAttribute('href');
                }
            }
        });
    });

    document.addEventListener('click', (e) => {
        inputs.forEach((input) => {
            const s = state.get(input);
            if (!s) return;
            const clickedInside = input.contains(e.target) || s.container.contains(e.target);
            if (!clickedInside) hide(input);
        });
    });
}

document.addEventListener('DOMContentLoaded', initGlobalSearch);
