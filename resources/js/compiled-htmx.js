import htmx from 'htmx.org';
window.htmx = htmx;
// globalViewTransitions disabled: when multiple HTMX responses arrive
// simultaneously (e.g. dashboard widgets), concurrent View Transitions
// cause swap operations to produce empty content. The View Transitions API
// only supports one transition at a time; overlapping transitions silently
// discard DOM mutations from earlier swaps.
window.htmx.config.globalViewTransitions = false;
// HTMX 2.x defaults selfRequestsOnly to true (blocks cross-origin).
// Keep this default for security.

// Workaround for HTMX 2.0.8 innerHTML swap bug:
// HTMX's internal swapInnerHTML removes old children but fails to insert
// new content from the parsed fragment. This affects all innerHTML swaps
// where the target already has children (e.g., loading placeholders).
// We intercept the swap, parse the response with DOMParser, and perform
// the DOM insertion manually. This only applies to innerHTML swaps; other
// swap styles are left to HTMX's default handling.
document.addEventListener('htmx:beforeSwap', function (evt) {
    var detail = evt.detail;
    if (!detail.shouldSwap) return;

    // Only intercept innerHTML swaps (the broken swap style in 2.0.8)
    var swapStyle = detail.requestConfig
        && detail.requestConfig.swapOverride
        ? detail.requestConfig.swapOverride.split(/\s/)[0]
        : null;
    if (!swapStyle) {
        var swapAttr = detail.target.closest('[hx-swap]');
        swapStyle = swapAttr ? swapAttr.getAttribute('hx-swap').split(/\s/)[0] : 'innerHTML';
    }
    if (swapStyle !== 'innerHTML') return;

    var target = detail.target;
    var response = detail.serverResponse;
    if (!target || !response) return;

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
});
