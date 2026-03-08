import htmx from 'htmx.org';
window.htmx = htmx;
// globalViewTransitions disabled: when multiple HTMX responses arrive
// simultaneously (e.g. dashboard widgets), concurrent View Transitions
// cause swap operations to produce empty content. The View Transitions API
// only supports one transition at a time; overlapping transitions silently
// discard DOM mutations from earlier swaps.
window.htmx.config.globalViewTransitions = false;
// HTMX 2.x defaults allowScriptTags to false for security. Re-enable it
// because the codebase relies on inline <script> tags in templates that
// must execute after HTMX content swaps (e.g. SlimSelect init, DataTables,
// ticket controllers). Without this, tab switches via hx-boost leave
// scripts in the swapped content unexecuted.
window.htmx.config.allowScriptTags = true;
// HTMX 2.x defaults selfRequestsOnly to true (blocks cross-origin).
// Keep this default for security.

// ---------------------------------------------------------------------------
// Fix hx-select inheritance from .rightpanel.
//
// .rightpanel sets hx-select=".primaryContent" for SPA navigation via
// hx-boost. Interactive elements with explicit hx-get/hx-post/etc. inherit
// this, causing their partial responses to be filtered through
// querySelectorAll(".primaryContent") — which finds nothing and wipes the
// target. Setting hx-select="unset" on these elements stops the inheritance.
// Boosted <a href> links are unaffected (they have no verb attributes).
// ---------------------------------------------------------------------------
document.addEventListener('htmx:beforeProcessNode', function (evt) {
    var elt = evt.detail.elt || evt.target;
    if (!(elt instanceof Element)) return;
    if (elt.hasAttribute('hx-select') || elt.hasAttribute('data-hx-select')) return;

    if (elt.hasAttribute('hx-get') || elt.hasAttribute('hx-post')
        || elt.hasAttribute('hx-put') || elt.hasAttribute('hx-patch')
        || elt.hasAttribute('hx-delete') || elt.hasAttribute('data-hx-get')
        || elt.hasAttribute('data-hx-post') || elt.hasAttribute('data-hx-put')
        || elt.hasAttribute('data-hx-patch') || elt.hasAttribute('data-hx-delete')) {
        elt.setAttribute('hx-select', 'unset');
    }
});

// ---------------------------------------------------------------------------
// For non-innerHTML swaps (outerHTML, etc.), hx-select="unset" must be
// neutralized before HTMX applies it as a CSS selector. "unset" matches no
// elements, which would produce an empty swap. We temporarily remove the
// attribute so HTMX uses the full response, then restore it afterward to
// maintain the inheritance block.
// ---------------------------------------------------------------------------
document.addEventListener('htmx:beforeSwap', function (evt) {
    var elt = evt.detail.elt;
    if (!elt || elt.getAttribute('hx-select') !== 'unset') return;

    var swapStyle = (evt.detail.requestConfig && evt.detail.requestConfig.swapOverride)
        ? evt.detail.requestConfig.swapOverride.split(/\s/)[0]
        : null;
    if (!swapStyle) {
        var swapAttr = elt.closest('[hx-swap]');
        swapStyle = swapAttr ? swapAttr.getAttribute('hx-swap').split(/\s/)[0] : 'innerHTML';
    }
    // innerHTML is handled by the custom swap handler below — skip.
    if (swapStyle === 'innerHTML') return;

    // Strip hx-select so HTMX uses the full response for this swap
    elt.removeAttribute('hx-select');
    requestAnimationFrame(function () {
        elt.setAttribute('hx-select', 'unset');
    });
});

// Workaround for HTMX 2.0.8 innerHTML swap bug:
// HTMX's internal swapInnerHTML removes old children but fails to insert
// new content from the parsed fragment. This affects all innerHTML swaps
// where the target already has children (e.g., loading placeholders).
// We intercept the swap, parse the response with DOMParser, and perform
// the DOM insertion manually. This only applies to innerHTML swaps; other
// swap styles are left to HTMX's default handling.
document.addEventListener('htmx:beforeSwap', function (evt) {
    var detail = evt.detail;
    if (!detail.shouldSwap) { console.log('[htmx-fix] beforeSwap: shouldSwap already false, skipping'); return; }

    // Only intercept innerHTML swaps (the broken swap style in 2.0.8)
    var swapStyle = detail.requestConfig
        && detail.requestConfig.swapOverride
        ? detail.requestConfig.swapOverride.split(/\s/)[0]
        : null;
    if (!swapStyle) {
        var swapAttr = detail.target.closest('[hx-swap]');
        swapStyle = swapAttr ? swapAttr.getAttribute('hx-swap').split(/\s/)[0] : 'innerHTML';
    }
    console.log('[htmx-fix] beforeSwap: swapStyle=', swapStyle, 'target=', detail.target?.id || detail.target);
    if (swapStyle !== 'innerHTML') return;

    var target = detail.target;
    var response = detail.serverResponse;
    if (!target || !response) return;

    console.log('[htmx-fix] intercepting innerHTML swap for', target.id, 'response length:', response.length);

    // Prevent HTMX's broken default swap
    detail.shouldSwap = false;

    // Parse and insert using DOMParser (reliable across all browsers)
    var doc = new DOMParser().parseFromString(response, 'text/html');
    target.innerHTML = '';
    while (doc.body.firstChild) {
        target.appendChild(doc.body.firstChild);
    }

    // Execute inline <script> tags that DOMParser rendered inert.
    // DOMParser sets the "already started" flag on script elements, so they
    // won't run when moved into the live document. We replace each inert
    // script with a fresh clone so the browser treats it as newly inserted.
    if (htmx.config.allowScriptTags) {
        target.querySelectorAll('script').forEach(function (inert) {
            // Honor nonce requirement if configured
            if (htmx.config.inlineScriptNonce && htmx.config.inlineScriptNonce.length > 0) {
                if (inert.nonce !== htmx.config.inlineScriptNonce) return;
            }
            var fresh = document.createElement('script');
            // Copy all attributes (type, nonce, src, etc.)
            Array.from(inert.attributes).forEach(function (attr) {
                fresh.setAttribute(attr.name, attr.value);
            });
            fresh.textContent = inert.textContent;
            inert.parentNode.replaceChild(fresh, inert);
        });
    }

    // Let HTMX process the new content for hx-* attributes
    htmx.process(target);

    // Dispatch the lifecycle events HTMX would have fired during a normal swap.
    // Setting shouldSwap = false above skips HTMX's entire swap/settle pipeline,
    // so htmx:afterSettle, htmx:afterSwap, and htmx.onLoad never fire. Many
    // subsystems depend on these events (componentInitializer, tabsController,
    // tiptap editors, accessibility enhancements, etc.). We dispatch them
    // manually so the rest of the app behaves as if a normal swap occurred.
    var afterDetail = { elt: target, target: target };
    console.log('[htmx-fix] dispatching afterSettle+afterSwap for', target.id, 'chips in DOM:', target.querySelectorAll('select.select-chip').length);
    target.dispatchEvent(new CustomEvent('htmx:afterSettle', { bubbles: true, detail: afterDetail }));
    target.dispatchEvent(new CustomEvent('htmx:afterSwap',   { bubbles: true, detail: afterDetail }));
});

// ---------------------------------------------------------------------------
// Auto-initialize SlimSelect on filter dropdowns.
//
// SlimSelect initialization is centralized here instead of in inline
// <script> tags to avoid race conditions during HTMX content swaps.
// A MutationObserver detects new <select> elements in .filterBar and
// initializes them after the DOM has settled (via requestAnimationFrame).
// SlimSelect adds .ss-hide to initialized selects — used as guard.
// ---------------------------------------------------------------------------
function initFilterSlimSelects() {
    if (typeof SlimSelect === 'undefined') return;
    document.querySelectorAll('.filterBar select:not(.ss-hide)').forEach(function (el) {
        // Skip elements not attached to the live document
        if (!el.isConnected) return;
        // Skip if SlimSelect already created its container as a sibling
        if (el.nextElementSibling && el.nextElementSibling.classList.contains('ss-main')) return;
        try {
            var placeholder = el.querySelector('option[data-placeholder]');
            new SlimSelect({
                select: el,
                settings: {
                    placeholderText: placeholder ? placeholder.textContent.trim() : 'All',
                },
            });
        } catch (e) { /* element may be detached or hidden — safe to skip */ }
    });
}

// Run on initial page load. Retry until SlimSelect module is available.
(function waitForSlimSelect(attempts) {
    if (typeof SlimSelect === 'undefined') {
        if (attempts >= 100) return;
        setTimeout(function () { waitForSlimSelect(attempts + 1); }, 50);
        return;
    }
    // Wait one animation frame so the browser has done layout
    requestAnimationFrame(function () { initFilterSlimSelects(); });
})(0);

// Watch for future DOM changes (HTMX swaps, any content injection).
// Debounced 200ms + requestAnimationFrame to ensure the swap is fully
// complete and the browser has done layout before SlimSelect measures.
var _ssInitTimer = null;
new MutationObserver(function () {
    if (_ssInitTimer) clearTimeout(_ssInitTimer);
    _ssInitTimer = setTimeout(function () {
        requestAnimationFrame(function () { initFilterSlimSelects(); });
    }, 200);
}).observe(document.querySelector('.maincontent') || document.body, { childList: true, subtree: true });
