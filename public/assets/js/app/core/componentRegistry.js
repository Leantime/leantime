/**
 * componentRegistry.js — Centralized component lifecycle manager.
 *
 * Leantime's equivalent of Livewire's store.js. Components register a CSS
 * selector + init/destroy functions. The registry handles initialization
 * across all content-arrival patterns:
 *
 *   1. Initial page load (DOMContentLoaded)
 *   2. HTMX swaps (htmx:afterSettle, scoped to swapped element)
 *   3. Modal opens (manual init(container) call from modalManager)
 *
 * Components that need cleanup before DOM replacement (e.g. SlimSelect,
 * tiptap editors) supply a destroyFn. The registry calls it automatically
 * on htmx:beforeSwap — but ONLY when the swap is actually going to happen
 * (detail.shouldSwap === true). This prevents freshly-initialized components
 * from being destroyed by unrelated concurrent HTMX requests.
 *
 * ## Instance Tracking
 *
 * Each initialized element gets `el.__ltComponent = { type, state }`.
 * This lets us query which components are alive and retrieve their state.
 *
 * ## State Persistence (Hook Points)
 *
 * Registrations can include a `stateKey` option. When a component is
 * destroyed, its state is saved in a WeakMap keyed by a stable identifier.
 * When re-initialized on the same element (same stateKey), the previous
 * state is passed to the init function. This enables components to survive
 * HTMX morphs/re-renders without losing client-side context.
 *
 * ## Morph Support (Hook Points)
 *
 * For future idiomorph/morphdom integration: when the DOM is morphed rather
 * than replaced, components stay in the DOM but their content may change.
 * The registry exposes `refresh(container)` which calls each component's
 * optional `refreshFn` without destroy/re-init. This preserves JS state
 * (open dropdowns, scroll position, focus) while allowing the component
 * to react to changed data attributes.
 *
 * ## Usage
 *
 *   leantime.componentRegistry.register(
 *       'select.select-chip',
 *       function (el, prevState) {
 *           el._slimSelect = new SlimSelect({ select: el, showSearch: false });
 *           // prevState is null on first init, or the object returned by
 *           // the previous destroyFn if stateKey was set.
 *       },
 *       {
 *           sentinel:   'data-chip-init',
 *           stateKey:   function (el) { return el.id; },
 *           destroyFn:  function (el) {
 *               var state = { wasOpen: el._slimSelect.data.contentOpen };
 *               el._slimSelect.destroy();
 *               el._slimSelect = null;
 *               return state; // saved for next init if stateKey is set
 *           },
 *           refreshFn:  function (el) {
 *               // Called on morph — update without destroy/re-init
 *               el._slimSelect.setData(readOptionsFromDOM(el));
 *           }
 *       }
 *   );
 *
 *   // Manually init a container (e.g. after programmatic DOM insertion):
 *   leantime.componentRegistry.init(myContainer);
 *
 *   // Manually refresh after morph:
 *   leantime.componentRegistry.refresh(myContainer);
 */

window.leantime = window.leantime || {};

leantime.componentRegistry = (function () {
    'use strict';

    /** @type {Array<{selector: string, initFn: Function, destroyFn: Function|null, refreshFn: Function|null, sentinel: string, stateKey: Function|null}>} */
    var registry = [];

    /** Persisted state from destroyed components, keyed by stateKey result */
    var stateStore = new Map();

    /**
     * Register a component type.
     *
     * @param {string}   selector  CSS selector matching uninitialized elements.
     * @param {Function} initFn    Called once per matching element. Receives (el, prevState).
     *                             prevState is null on first init, or the value returned by
     *                             destroyFn on the previous lifecycle (if stateKey was set).
     * @param {Object}   [opts]
     * @param {string}   [opts.sentinel]   Data attribute set after init. Prevents double-init.
     *                                     Defaults to 'data-lt-init'.
     * @param {Function} [opts.destroyFn]  Called before the element is removed from DOM.
     *                                     Receives the element. May return a state object
     *                                     that will be passed to initFn on next lifecycle.
     * @param {Function} [opts.refreshFn]  Called on morph (DOM patched, not replaced).
     *                                     Receives the element. Optional.
     * @param {Function} [opts.stateKey]   Function(el) → string. Stable identifier for
     *                                     state persistence across destroy/re-init cycles.
     *                                     If not set, no state is persisted.
     */
    function register(selector, initFn, opts) {
        opts = opts || {};
        registry.push({
            selector:   selector,
            initFn:     initFn,
            destroyFn:  opts.destroyFn  || null,
            refreshFn:  opts.refreshFn  || null,
            sentinel:   opts.sentinel   || 'data-lt-init',
            stateKey:   opts.stateKey   || null,
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

        registry.forEach(function (entry) {
            var matches = root.querySelectorAll(entry.selector);
            matches.forEach(function (el) {
                if (el.hasAttribute(entry.sentinel)) { return; }
                try {
                    el.setAttribute(entry.sentinel, 'true');

                    // Retrieve persisted state if stateKey is configured
                    var prevState = null;
                    if (entry.stateKey) {
                        var key = entry.stateKey(el);
                        if (key && stateStore.has(key)) {
                            prevState = stateStore.get(key);
                            stateStore.delete(key);
                        }
                    }

                    entry.initFn(el, prevState);

                    // Track the instance on the element
                    el.__ltComponent = {
                        type: entry.selector,
                        state: null,
                    };
                } catch (err) {
                    console.error('[componentRegistry] Failed to init "' + entry.selector + '":', err);
                    el.removeAttribute(entry.sentinel);
                }
            });
        });
    }

    /**
     * Destroy all initialized components within a container (before DOM removal).
     * Removes the sentinel so the element could be re-initialized if reused.
     * If the component has a stateKey, the return value of destroyFn is saved
     * for the next init cycle.
     *
     * @param {Element} container
     */
    function destroyWithin(container) {
        if (!container) { return; }

        registry.forEach(function (entry) {
            if (!entry.destroyFn) { return; }
            var found = container.querySelectorAll('[' + entry.sentinel + ']');
            found.forEach(function (el) {
                try {
                    var returnedState = entry.destroyFn(el);

                    // Persist state if stateKey is configured
                    if (entry.stateKey && returnedState !== undefined) {
                        var key = entry.stateKey(el);
                        if (key) {
                            stateStore.set(key, returnedState);
                        }
                    }
                } catch (err) {
                    console.error('[componentRegistry] Failed to destroy "' + entry.selector + '":', err);
                }
                el.removeAttribute(entry.sentinel);
                delete el.__ltComponent;
            });
        });
    }

    /**
     * Refresh components within a container after a morph (DOM patched, not replaced).
     * Calls each component's refreshFn if defined, without destroy/re-init.
     *
     * @param {Element} container
     */
    function refresh(container) {
        if (!container) { return; }

        registry.forEach(function (entry) {
            if (!entry.refreshFn) { return; }
            container.querySelectorAll('[' + entry.sentinel + ']').forEach(function (el) {
                try {
                    entry.refreshFn(el);
                } catch (err) {
                    console.error('[componentRegistry] Failed to refresh "' + entry.selector + '":', err);
                }
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
        init(e.detail.elt || document.body);
    });

    // 3. Cleanup before HTMX replaces DOM (gives components a chance to tear down).
    //    Only destroy when HTMX will actually perform the swap (shouldSwap=true).
    //    When shouldSwap is false the swap was either cancelled or already handled
    //    by a custom handler (e.g. the innerHTML workaround in entry-htmx.js)
    //    which may have already replaced the content and re-initialized components.
    //
    //    IMPORTANT: skip destroy for hx-swap="none". Nothing is replaced in the
    //    DOM for a none-swap, so tearing down components is both wasteful and
    //    harmful. The classic case is a chip <select> with hx-swap="none" that
    //    saves a value: destroying TomSelect causes a visible flash to the raw
    //    <select> (which still holds the server-rendered value), then afterSettle
    //    re-inits from that stale DOM — showing the old value instead of the new.
    document.addEventListener('htmx:beforeSwap', function (e) {
        if (!e.detail.shouldSwap || !e.detail.target) { return; }

        // Resolve swap style from the request config or the nearest hx-swap attribute.
        var swapStyle = (e.detail.requestConfig && e.detail.requestConfig.swapOverride)
            ? e.detail.requestConfig.swapOverride.split(/\s/)[0]
            : null;
        if (!swapStyle) {
            var swapAttr = e.detail.target.closest ? e.detail.target.closest('[hx-swap]') : null;
            swapStyle = swapAttr ? swapAttr.getAttribute('hx-swap').split(/\s/)[0] : 'innerHTML';
        }

        if (swapStyle === 'none') { return; }

        destroyWithin(e.detail.target);
    });

    // ── Public API ────────────────────────────────────────────────────────────

    return {
        register:      register,
        init:          init,
        destroyWithin: destroyWithin,
        refresh:       refresh,
    };

})();
