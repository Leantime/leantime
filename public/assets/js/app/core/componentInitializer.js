/**
 * componentInitializer.js — Centralized component auto-init registry.
 *
 * Components register a CSS selector + init function here. The initializer
 * handles re-initialization automatically across all three contexts:
 *
 *   1. Initial page load (DOMContentLoaded)
 *   2. After HTMX swaps (htmx:afterSettle, scoped to swapped element)
 *   3. Inside modals (modalManager calls init(container) after content loads)
 *
 * Components that need cleanup before DOM replacement (e.g. SlimSelect,
 * rich text editors) can supply a destroyFn. The initializer calls it
 * automatically on htmx:beforeSwap.
 *
 * Usage:
 *
 *   leantime.componentInitializer.register(
 *       'select.select-chip',
 *       function (el) {
 *           el._slimSelect = new SlimSelect({ select: el, showSearch: false });
 *       },
 *       {
 *           sentinel:  'data-chip-init',   // attribute set after init (prevents double-init)
 *           destroyFn: function (el) {
 *               if (el._slimSelect) { el._slimSelect.destroy(); el._slimSelect = null; }
 *           }
 *       }
 *   );
 *
 *   // Manually init inside a container (e.g. after programmatic DOM insertion):
 *   leantime.componentInitializer.init(myContainer);
 */

window.leantime = window.leantime || {};

leantime.componentInitializer = (function () {
    'use strict';

    /** @type {Array<{selector: string, initFn: Function, destroyFn: Function|null, sentinel: string}>} */
    var registry = [];

    /**
     * Register a component type.
     *
     * @param {string}   selector  CSS selector matching uninitialized elements.
     * @param {Function} initFn    Called once per matching element. Receives the element.
     * @param {Object}   [opts]
     * @param {string}   [opts.sentinel]   Data attribute set after init. Prevents double-init.
     *                                     Defaults to 'data-ci-initialized'.
     * @param {Function} [opts.destroyFn]  Called before the element is removed from DOM
     *                                     (htmx:beforeSwap). Receives the element.
     */
    function register(selector, initFn, opts) {
        opts = opts || {};
        console.log('[ci] register:', selector);
        registry.push({
            selector:  selector,
            initFn:    initFn,
            destroyFn: opts.destroyFn || null,
            sentinel:  opts.sentinel  || 'data-ci-initialized',
        });
    }

    /**
     * Scan a container for registered components and initialize any that
     * haven't been initialized yet (sentinel attribute absent).
     *
     * @param {Element|null} [container]  Root element to scan. Defaults to document.body.
     */
    function init(container) {
        var root = container || document.body;
        if (!root) { return; }

        console.log('[ci] init called, root=', root.id || root.tagName, 'registry size=', registry.length);
        registry.forEach(function (entry) {
            var matches = root.querySelectorAll(entry.selector);
            if (matches.length > 0) {
                console.log('[ci]  ->', entry.selector, 'found', matches.length, 'elements');
            }
            matches.forEach(function (el) {
                if (el.hasAttribute(entry.sentinel)) { console.log('[ci]    skip (sentinel)', el.id || el); return; }
                try {
                    console.log('[ci]    INIT', entry.selector, el.id || el);
                    el.setAttribute(entry.sentinel, 'true');
                    entry.initFn(el);
                } catch (err) {
                    // Log but don't rethrow — one bad component shouldn't break others
                    console.error('[componentInitializer] Failed to init "' + entry.selector + '":', err);
                    // Remove sentinel so it can be retried if the issue resolves
                    el.removeAttribute(entry.sentinel);
                }
            });
        });
    }

    /**
     * Destroy all initialized components within a container (before DOM removal).
     * Removes the sentinel so the element could be re-initialized if reused.
     *
     * @param {Element} container
     */
    function destroyWithin(container) {
        if (!container) { return; }

        registry.forEach(function (entry) {
            if (!entry.destroyFn) { return; }
            container.querySelectorAll('[' + entry.sentinel + ']').forEach(function (el) {
                try {
                    entry.destroyFn(el);
                } catch (err) {
                    console.error('[componentInitializer] Failed to destroy "' + entry.selector + '":', err);
                }
                el.removeAttribute(entry.sentinel);
            });
        });
    }

    // ── Lifecycle hooks ───────────────────────────────────────────────────────

    // 1. Initial page load
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });

    // 2. After HTMX swaps — scoped to the swapped subtree only
    document.addEventListener('htmx:afterSettle', function (e) {
        console.log('[ci] htmx:afterSettle fired, elt=', e.detail?.elt?.id || e.detail?.elt?.tagName || 'none');
        init(e.detail.elt || document.body);
    });

    // 3. Cleanup before HTMX replaces DOM (gives components a chance to tear down).
    //    Only destroy when HTMX will actually perform the swap (shouldSwap=true).
    //    When shouldSwap is false the swap was either cancelled or already handled
    //    by a custom handler (e.g. the innerHTML workaround in entry-htmx.js)
    //    which may have already replaced the content and re-initialized components.
    document.addEventListener('htmx:beforeSwap', function (e) {
        if (e.detail.shouldSwap && e.detail.target) { destroyWithin(e.detail.target); }
    });

    // ── Public API ────────────────────────────────────────────────────────────

    return {
        register:      register,
        init:          init,
        destroyWithin: destroyWithin,
    };

})();
