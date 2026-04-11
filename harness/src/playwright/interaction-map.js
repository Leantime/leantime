// This runs inside the browser via page.evaluate() — plain JS, no TypeScript.
// Discovers all interactive elements on the page and returns a structured map.
(() => {
  const results = [];
  const seen = new Set();

  const posKey = (r) => `${Math.round(r.left)},${Math.round(r.top)}`;

  const add = (el, type, label, selector) => {
    const rect = el.getBoundingClientRect();
    if (rect.width === 0 || rect.height === 0) return;
    const pk = posKey(rect);
    if (seen.has(pk)) return;
    seen.add(pk);
    results.push({
      selector,
      type,
      label: (label || "").slice(0, 80),
      bbox: {
        x: Math.round(rect.x),
        y: Math.round(rect.y),
        w: Math.round(rect.width),
        h: Math.round(rect.height),
      },
    });
  };

  const describeEl = (el) =>
    el.getAttribute("aria-label") ||
    el.getAttribute("title") ||
    (el.textContent || "").trim().slice(0, 50) ||
    el.tagName.toLowerCase();

  const buildSelector = (el) => {
    if (el.id) return "#" + el.id;
    const hxGet = el.getAttribute("hx-get");
    if (hxGet) return '[hx-get="' + hxGet + '"]';
    const href = el.getAttribute("href");
    if (href && href !== "#")
      return el.tagName.toLowerCase() + '[href="' + href + '"]';
    const name = el.getAttribute("name");
    if (name) return el.tagName.toLowerCase() + '[name="' + name + '"]';
    const cls = Array.from(el.classList).slice(0, 2).join(".");
    return cls ? el.tagName.toLowerCase() + "." + cls : el.tagName.toLowerCase();
  };

  // 1. Modal triggers
  document
    .querySelectorAll(
      '[data-toggle="modal"], [data-bs-toggle="modal"], .nyroModal, a.nyroModal'
    )
    .forEach((el) =>
      add(el, "modal-trigger", describeEl(el), buildSelector(el))
    );

  // 2. Dropdowns
  document
    .querySelectorAll(
      '[data-toggle="dropdown"], [data-bs-toggle="dropdown"], .dropdown-toggle'
    )
    .forEach((el) => add(el, "dropdown", describeEl(el), buildSelector(el)));

  // 3. HTMX click actions
  document
    .querySelectorAll(
      "[hx-get], [hx-post], [hx-put], [hx-delete]"
    )
    .forEach((el) => {
      const trigger = el.getAttribute("hx-trigger") || "";
      // Skip non-click triggers (lazy-load, SSE, polling, etc.)
      if (
        trigger.includes("revealed") ||
        trigger.includes("load") ||
        trigger.includes("intersect") ||
        trigger.includes("every") ||
        trigger.includes("sse:") ||
        trigger.includes("from:")
      ) {
        return;
      }
      add(el, "htmx-action", describeEl(el), buildSelector(el));
    });

  // 4. Forms
  document.querySelectorAll("form").forEach((el) => {
    const action = el.getAttribute("action") || el.id || "unnamed";
    add(el, "form", "Form: " + action, buildSelector(el));
  });

  // 5. Sortable tables
  document
    .querySelectorAll(
      "table.dataTable, table.table-sortable, .dataTables_wrapper"
    )
    .forEach((el) =>
      add(el, "sortable-table", describeEl(el), buildSelector(el))
    );

  // 6. Drag-and-drop zones
  document
    .querySelectorAll(
      '[draggable="true"], .sortable, .ui-sortable, .kanban-lane, .ticketBox, ' +
        ".grid-stack, .grid-stack-item, .nested-sortable, [data-sortable], " +
        ".drag-handle, .move-handle, .kanbanCard, .draggable"
    )
    .forEach((el) => add(el, "drag-drop", describeEl(el), buildSelector(el)));

  // 7. Tabs
  document
    .querySelectorAll(
      '[data-toggle="tab"], [data-bs-toggle="tab"], .nav-tabs a, [role="tab"]'
    )
    .forEach((el) => add(el, "tab", describeEl(el), buildSelector(el)));

  // 8. Accordions / collapsibles
  document
    .querySelectorAll(
      '[data-toggle="collapse"], [data-bs-toggle="collapse"], .accordion-toggle'
    )
    .forEach((el) => add(el, "accordion", describeEl(el), buildSelector(el)));

  // 9. onclick handlers
  document.querySelectorAll("[onclick]").forEach((el) => {
    add(el, "link-with-handler", describeEl(el), buildSelector(el));
  });

  // 10. Standalone buttons (exclude third-party component internals)
  document
    .querySelectorAll(
      'button:not([type="submit"]):not([data-toggle]):not([data-bs-toggle])'
    )
    .forEach((el) => {
      if (seen.has(posKey(el.getBoundingClientRect()))) return;
      // Skip third-party library internals — these clutter the map
      // and waste evaluator time on components that aren't ours
      if (el.closest(
        '.tiptap-toolbar, .tiptap, .ProseMirror, ' +
        '.fc-toolbar, .fullcalendar, ' +
        '.dataTables_wrapper thead, ' +
        '.tom-select, .ts-control, ' +
        '.shepherd-content, .tippy-content, ' +
        '.uppy-Dashboard, .croppie-container'
      )) return;
      add(el, "button", describeEl(el), buildSelector(el));
    });

  // Also retroactively filter any results inside third-party containers
  var dominated = [
    '.tiptap-toolbar', '.tiptap', '.ProseMirror',
    '.fc-toolbar', '.fullcalendar',
    '.tom-select', '.ts-control',
    '.shepherd-content', '.tippy-content',
    '.uppy-Dashboard', '.croppie-container'
  ].join(', ');
  var dominated_els = new Set();
  document.querySelectorAll(dominated).forEach(function(container) {
    container.querySelectorAll('*').forEach(function(child) {
      dominated_els.add(child);
    });
  });

  var filtered = results.filter(function(r) {
    // Keep all non-button types from third-party containers only if they're
    // the container itself (e.g. the form wrapping the editor), not internals
    var el = document.querySelector(r.selector);
    if (!el) return true;
    if (el.closest(dominated)) {
      // Keep the top-level container element, skip its children
      return el.matches(dominated);
    }
    return true;
  });

  return filtered;
})();
