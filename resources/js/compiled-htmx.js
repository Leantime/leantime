import htmx from 'htmx.org';
window.htmx = htmx;
window.htmx.config.globalViewTransitions = true;
// HTMX 2.x defaults selfRequestsOnly to true (blocks cross-origin).
// Keep this default for security.
