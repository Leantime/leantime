/**
 * Vanilla tab controller — replaces jQuery UI .tabs() across Leantime.
 *
 * Supports:
 *  - data-tabs           auto-init marker
 *  - data-tabs-active    initial tab index (default 0)
 *  - data-tabs-persist   "url" | "hash" | "localStorage"
 *  - data-tabs-persist-key  localStorage key (default "ltTab_<container-id>")
 *
 * Callbacks (via opts object when called programmatically):
 *  - create(event, ui)   fires after first init
 *  - activate(event, ui) fires on every tab switch
 */
leantime.tabsController = (function () {
    'use strict';

    /**
     * Initialise a single tab container.
     *
     * @param {HTMLElement} container
     * @param {object}      [opts]
     * @param {number}      [opts.active]      initial tab index
     * @param {Function}    [opts.create]      create callback(event, ui)
     * @param {Function}    [opts.activate]    activate callback(event, ui)
     * @param {string}      [opts.persist]     "url" | "hash" | "localStorage"
     * @param {string}      [opts.persistKey]  localStorage key
     * @returns {void}
     */
    function initTabs(container, opts) {
        if (!container || container._tabsInitialized) { return; }
        opts = opts || {};

        var tabList = container.querySelector('ul');
        if (!tabList) { return; }
        tabList.setAttribute('role', 'tablist');

        var tabLinks = Array.prototype.slice.call(
            tabList.querySelectorAll(':scope > li > a[href^="#"]')
        );
        var panels = [];

        tabLinks.forEach(function (link) {
            var panel = container.querySelector(link.getAttribute('href'));
            if (panel) { panels.push(panel); }
        });

        if (panels.length === 0) { return; }

        // Read persistence mode from data attrs or opts
        var persist = opts.persist || container.dataset.tabsPersist || null;
        var persistKey = opts.persistKey || container.dataset.tabsPersistKey
            || 'ltTab_' + (container.id || '');

        // Determine initial active index
        var activeIndex = typeof opts.active === 'number' ? opts.active : 0;

        // Override from data attribute
        if (container.dataset.tabsActive !== undefined && typeof opts.active !== 'number') {
            activeIndex = parseInt(container.dataset.tabsActive, 10) || 0;
        }

        // Override from persistence
        var savedTab = readPersistedTab(persist, persistKey);
        if (savedTab) {
            tabLinks.forEach(function (link, i) {
                if (link.getAttribute('href') === '#' + savedTab) {
                    activeIndex = i;
                }
            });
        }

        // Clamp
        if (activeIndex < 0 || activeIndex >= panels.length) { activeIndex = 0; }

        // Set up panels and ARIA
        panels.forEach(function (panel, i) {
            panel.setAttribute('role', 'tabpanel');
            panel.style.display = (i === activeIndex) ? '' : 'none';
        });

        tabLinks.forEach(function (link, i) {
            var li = link.parentElement;
            link.setAttribute('role', 'tab');
            link.setAttribute('aria-controls', link.getAttribute('href').substring(1));

            if (i === activeIndex) {
                li.classList.add('active');
                link.setAttribute('aria-selected', 'true');
            } else {
                li.classList.remove('active',
                    'ui-tabs-active', 'ui-state-active');
                link.setAttribute('aria-selected', 'false');
            }
        });

        // Click handler
        tabLinks.forEach(function (link, i) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                switchTab(container, tabLinks, panels, i, opts, persist, persistKey);
            });
        });

        // Reveal (FOUC fix — templates use visibility:hidden until tabs init)
        container.style.visibility = '';

        // Mark as initialized
        container._tabsInitialized = true;

        // Fire create callback
        if (typeof opts.create === 'function') {
            opts.create.call(container, {},
                { tab: tabLinks[activeIndex], panel: panels[activeIndex] });
        }
    }

    /**
     * Switch to a specific tab by index.
     *
     * @param {HTMLElement} container
     * @param {Array}       tabLinks
     * @param {Array}       panels
     * @param {number}      index
     * @param {object}      opts
     * @param {string|null} persist
     * @param {string}      persistKey
     */
    function switchTab(container, tabLinks, panels, index, opts, persist, persistKey) {
        panels.forEach(function (p) { p.style.display = 'none'; });
        tabLinks.forEach(function (link) {
            var li = link.parentElement;
            li.classList.remove('active',
                'ui-tabs-active', 'ui-state-active');
            link.setAttribute('aria-selected', 'false');
        });

        if (panels[index]) { panels[index].style.display = ''; }
        var li = tabLinks[index].parentElement;
        li.classList.add('active');
        tabLinks[index].setAttribute('aria-selected', 'true');

        // Persist
        var panelId = tabLinks[index].getAttribute('href').substring(1);
        writePersistedTab(persist, persistKey, panelId);

        // Fire activate callback
        if (typeof opts.activate === 'function') {
            opts.activate.call(container, {},
                { newTab: tabLinks[index], newPanel: panels[index] });
        }
    }

    /**
     * Read persisted tab id from the chosen storage.
     *
     * @param {string|null} mode
     * @param {string}      key
     * @returns {string|null}
     */
    function readPersistedTab(mode, key) {
        if (mode === 'url') {
            try {
                var url = new URL(window.location.href);
                return url.searchParams.get('tab') || null;
            } catch (_) { return null; }
        }
        if (mode === 'hash') {
            var h = window.location.hash;
            return h ? h.substring(1) : null;
        }
        if (mode === 'localStorage') {
            try { return localStorage.getItem(key) || null; }
            catch (_) { return null; }
        }
        return null;
    }

    /**
     * Write active tab id to the chosen storage.
     *
     * @param {string|null} mode
     * @param {string}      key
     * @param {string}      panelId
     */
    function writePersistedTab(mode, key, panelId) {
        if (mode === 'url') {
            try {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', panelId);
                window.history.replaceState(null, null, url);
            } catch (_) { /* noop */ }
        } else if (mode === 'hash') {
            window.location.hash = '#' + panelId;
        } else if (mode === 'localStorage') {
            try { localStorage.setItem(key, panelId); }
            catch (_) { /* noop */ }
        }
    }

    /**
     * Auto-init all [data-tabs] containers within a root element.
     *
     * @param {HTMLElement} [root]
     */
    function initAll(root) {
        root = root || document;
        var containers = root.querySelectorAll('[data-tabs]');
        containers.forEach(function (el) {
            initTabs(el);
        });
    }

    // Auto-init on page load
    document.addEventListener('DOMContentLoaded', function () {
        initAll();
    });

    // Re-init after HTMX swaps
    document.addEventListener('htmx:afterSettle', function (e) {
        initAll(e.detail.elt);
    });

    // Watch for dynamically injected [data-tabs] (e.g. nyroModal AJAX content)
    if (typeof MutationObserver !== 'undefined') {
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (node.nodeType === 1) {
                        if (node.hasAttribute && node.hasAttribute('data-tabs') && !node._tabsInitialized) {
                            initTabs(node);
                        }
                        if (node.querySelectorAll) {
                            var nested = node.querySelectorAll('[data-tabs]');
                            nested.forEach(function (el) {
                                if (!el._tabsInitialized) {
                                    initTabs(el);
                                }
                            });
                        }
                    }
                });
            });
        });
        observer.observe(document.body, { childList: true, subtree: true });
    }

    return {
        initTabs: initTabs,
        initAll: initAll
    };
})();
