/**
 * Sinkronisasi harga/stok produk tanpa reload penuh.
 * - Poll ringan saat tab terlihat
 * - Broadcast antar tab via localStorage + CustomEvent
 * - Patch hanya elemen [data-live-*], tidak mengganggu input/scroll
 */
(() => {
    if (window.AlmairaLiveSync) return;

    const CHANNEL_KEY = 'almaira_products_revision';
    const POLL_MS = 12000;
    let lastRevision = 0;
    let timer = null;
    let inFlight = false;

    function endpoint() {
        return window.AlmairaLiveSyncConfig?.url || '/catalog/live';
    }

    function collectIds() {
        const ids = new Set();
        document.querySelectorAll('[data-live-product]').forEach((el) => {
            const id = parseInt(el.getAttribute('data-live-product') || '', 10);
            if (id > 0) ids.add(id);
        });
        return [...ids];
    }

    function setText(el, value) {
        if (!el || value == null) return;
        const next = String(value);
        if (el.textContent !== next) {
            el.textContent = next;
            el.classList.add('live-flash');
            setTimeout(() => el.classList.remove('live-flash'), 700);
        }
    }

    function patchProduct(id, data) {
        document.querySelectorAll(`[data-live-product="${id}"]`).forEach((root) => {
            root.querySelectorAll('[data-live-field]').forEach((field) => {
                const key = field.getAttribute('data-live-field');
                if (!key) return;

                if (key === 'stock_short' || key === 'stock_label') {
                    setText(field, data[key]);
                    const chip = field.closest('[data-live-stock-chip]');
                    if (chip && data.stock_chip) {
                        chip.className = chip.getAttribute('data-live-stock-base') || chip.className;
                        data.stock_chip.split(/\s+/).forEach((c) => c && chip.classList.add(c));
                    }
                    const dot = root.querySelector('[data-live-stock-dot]');
                    if (dot && data.stock_dot) {
                        dot.className = (dot.getAttribute('data-live-dot-base') || 'w-1.5 h-1.5 rounded-full') + ' ' + data.stock_dot;
                    }
                    return;
                }

                if (key === 'wholesale_formatted' && data.wholesale_price <= 0) {
                    setText(field, '—');
                    return;
                }

                if (key === 'price_secondary_wrap') {
                    field.style.display = data.price_secondary != null ? '' : 'none';
                    return;
                }

                if (Object.prototype.hasOwnProperty.call(data, key) && data[key] != null) {
                    setText(field, data[key]);
                }
            });

            if (data.exceeds_het != null) {
                const sellEl = root.querySelector('[data-live-field="sell_formatted"]');
                if (sellEl) {
                    sellEl.classList.toggle('text-rose-700', !!data.exceeds_het);
                    sellEl.classList.toggle('text-emerald-700', !data.exceeds_het);
                }
            }
        });
    }

    async function fetchSnapshot(ids, force = false) {
        if (!ids.length || inFlight) return;
        if (document.hidden && !force) return;

        inFlight = true;
        try {
            const url = new URL(endpoint(), window.location.origin);
            url.searchParams.set('ids', ids.join(','));
            if (lastRevision > 0) {
                url.searchParams.set('since', String(lastRevision));
            }

            const res = await fetch(url.toString(), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            if (!res.ok) return;

            const json = await res.json();
            const rev = parseInt(json.revision || 0, 10) || 0;
            const products = json.products || {};

            Object.keys(products).forEach((id) => patchProduct(id, products[id]));

            if (rev > 0) {
                lastRevision = rev;
                try {
                    localStorage.setItem(CHANNEL_KEY, String(rev));
                } catch (_) {}
            }

            // Soft refresh Livewire hanya saat notify/fokus — poll diam hanya patch DOM
            if (force && window.Livewire && typeof window.Livewire.dispatch === 'function') {
                window.Livewire.dispatch('products-live-refresh');
            }
        } catch (_) {
            // diamkan — jangan ganggu aktivitas user
        } finally {
            inFlight = false;
        }
    }

    function tick(force = false) {
        const ids = collectIds();
        if (!ids.length) return;
        fetchSnapshot(ids, force);
    }

    function notify() {
        try {
            localStorage.setItem(CHANNEL_KEY, String(Date.now()));
        } catch (_) {}
        window.dispatchEvent(new CustomEvent('almaira:products-updated'));
        tick(true);
    }

    function start() {
        if (timer) return;
        tick(false);
        timer = setInterval(() => tick(false), POLL_MS);
    }

    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) return;
        tick(true);
    });

    window.addEventListener('storage', (e) => {
        if (e.key === CHANNEL_KEY) tick(true);
    });

    window.addEventListener('almaira:products-updated', () => tick(true));

    document.addEventListener('livewire:navigated', () => {
        setTimeout(() => tick(true), 50);
    });

    document.addEventListener('DOMContentLoaded', start);
    if (document.readyState !== 'loading') start();

    window.AlmairaLiveSync = { start, stop, tick, notify, patchProduct };
})();
