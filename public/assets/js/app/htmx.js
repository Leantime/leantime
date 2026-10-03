window.htmx = require('htmx.org');

// Global view transitions are intentionally OFF. With no hx-boost and no per-element
// view-transition-name scoping, htmx.config.globalViewTransitions=true wrapped EVERY partial
// swap in document.startViewTransition(), which snapshots and cross-fades the whole viewport —
// so the dashboard's ~22 widget swaps each repainted the entire page (heavy flicker, including
// the hovered element under the cursor). Opt in per swap instead, e.g.
// hx-swap="innerHTML transition:true" plus a scoped `view-transition-name` in CSS.
window.htmx.config.globalViewTransitions = false;

// No string-to-code evaluation: hx-on handlers, js: values and trigger filters would turn any
// htmx attribute that slips into rendered user content into script. Wire behaviour in JS instead.
window.htmx.config.allowEval = false;

// Favorite star: drop the spinner state once its toggle request finishes.
document.addEventListener('htmx:afterRequest', function (event) {
    var requestElement = event.detail && event.detail.elt;
    if (requestElement && requestElement.classList && requestElement.classList.contains('favoriteClick')) {
        requestElement.classList.remove('go');
    }
});
