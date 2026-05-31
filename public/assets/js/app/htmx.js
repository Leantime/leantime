window.htmx = require('htmx.org');

// Global view transitions are intentionally OFF. With no hx-boost and no per-element
// view-transition-name scoping, htmx.config.globalViewTransitions=true wrapped EVERY partial
// swap in document.startViewTransition(), which snapshots and cross-fades the whole viewport —
// so the dashboard's ~22 widget swaps each repainted the entire page (heavy flicker, including
// the hovered element under the cursor). Opt in per swap instead, e.g.
// hx-swap="innerHTML transition:true" plus a scoped `view-transition-name` in CSS.
window.htmx.config.globalViewTransitions = false;
