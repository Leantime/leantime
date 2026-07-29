{{--
    Shared tab-group — the ONE tablist implementation (navigation.tabs).

    ARIA button-tablist with roving tabindex, Arrow/Home/End keyboard support,
    optional persistence, and HTMX-safe vanilla-JS init (no jQuery). Replaces
    the legacy jQuery-UI tabs wrapper and the four hand-rolled copies of the
    same pattern (board tabs, report deck, goal dialog, Resource Allocation).

    Props:
      group     string  REQUIRED. Unique id prefix for this group on the page;
                        tab/panel ids derive from it ({group}-tab-{name} /
                        {group}-panel-{name}).
      label     string  REQUIRED. aria-label for the tablist.
      variant   string  'attached' (default) — gradient accent band with a
                        translucent framed segment group (board/report look), or
                        'floating' — free-floating fully-rounded pills (RA look).
      dark      bool    Floating variant only: dark-header color scheme
                        (server-stamped, mirrors the theme's color mode).
      panels    string  'toggle' (default) — the JS shows/hides elements marked
                        data-tabs-panel for this group; 'manual' — the JS only
                        manages tab state and dispatches the event below, and
                        the page's own JS moves content (e.g. a deck track).
      storage   string  'none' (default) | 'session' | 'local' — where the
                        active tab persists across reloads.
      storageKey string Storage key; REQUIRED when storage != none. Scope it
                        per entity (e.g. "ra-tab-{programId}").

    Slots:
      default   The x-global::navigation.tabs.tab children.
      actions   Optional right-side cluster (filters, period pickers, pager
                arrows). Attached variant renders it inside the band.

    Panel contract (caller-rendered):
      <div id="{group}-panel-{name}" role="tabpanel"
           aria-labelledby="{group}-tab-{name}" tabindex="0"
           data-tabs-panel="{name}" data-tabs-group="{group}">…</div>

    Event contract: on every activation the root dispatches a bubbling
    CustomEvent "lt:tabs:changed" with detail {group, name, index} — pages with
    special behavior (deck translation, lazy loads) listen for it.
--}}
@props([
    'group',
    'label',
    'variant' => 'attached',
    'dark' => false,
    'panels' => 'toggle',
    'storage' => 'none',
    'storageKey' => null,
])

<div {{ $attributes->merge(['class' => 'lt-tabs lt-tabs--'.$variant.($dark ? ' lt-tabs--dark' : '')]) }}
     data-lt-tabs="{{ $group }}"
     data-tabs-panels="{{ $panels }}"
     data-tabs-storage="{{ $storage }}"
     @if ($storageKey) data-tabs-storage-key="{{ $storageKey }}" @endif>
    <div class="lt-tabs-group" role="tablist" aria-label="{{ $label }}">
        {{ $slot }}
    </div>
    @isset ($actions)
        <div {{ $actions->attributes->merge(['class' => 'lt-tabs-actions']) }}>{{ $actions }}</div>
    @endisset
</div>

@once('lt-tabs-script')
    <script>
        (function () {
            'use strict';

            function activate(root, tabs, idx, focus) {
                tabs.forEach(function (t, i) {
                    var on = i === idx;
                    t.classList.toggle('on', on);
                    t.setAttribute('aria-selected', on ? 'true' : 'false');
                    t.setAttribute('tabindex', on ? '0' : '-1');
                });
                var tab = tabs[idx];
                if (focus) { tab.focus(); }

                var group = root.getAttribute('data-lt-tabs');
                var name = tab.getAttribute('data-tab-name');

                if (root.getAttribute('data-tabs-panels') === 'toggle') {
                    document.querySelectorAll('[data-tabs-group="' + group + '"][data-tabs-panel]').forEach(function (p) {
                        p.hidden = p.getAttribute('data-tabs-panel') !== name;
                    });
                }

                var storage = root.getAttribute('data-tabs-storage');
                var key = root.getAttribute('data-tabs-storage-key');
                if (key && (storage === 'session' || storage === 'local')) {
                    try { (storage === 'session' ? sessionStorage : localStorage).setItem(key, name); } catch (e) { /* private mode */ }
                }

                root.dispatchEvent(new CustomEvent('lt:tabs:changed', {
                    bubbles: true,
                    detail: { group: group, name: name, index: idx },
                }));
            }

            function init(scope) {
                (scope.querySelectorAll ? scope : document).querySelectorAll('[data-lt-tabs]:not([data-tabs-init])').forEach(function (root) {
                    root.setAttribute('data-tabs-init', '1');
                    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
                    if (!tabs.length) { return; }

                    tabs.forEach(function (t, i) {
                        t.addEventListener('click', function () { activate(root, tabs, i, false); });
                    });

                    // Roving focus on the tablist: Left/Right wrap, Home/End jump.
                    root.querySelector('[role="tablist"]').addEventListener('keydown', function (e) {
                        var current = tabs.findIndex(function (t) { return t.getAttribute('aria-selected') === 'true'; });
                        var next = null;
                        if (e.key === 'ArrowRight') { next = (current + 1) % tabs.length; }
                        else if (e.key === 'ArrowLeft') { next = (current - 1 + tabs.length) % tabs.length; }
                        else if (e.key === 'Home') { next = 0; }
                        else if (e.key === 'End') { next = tabs.length - 1; }
                        if (next !== null) { e.preventDefault(); activate(root, tabs, next, true); }
                    });

                    // Restore persisted selection (falls back to the server-rendered state).
                    var storage = root.getAttribute('data-tabs-storage');
                    var key = root.getAttribute('data-tabs-storage-key');
                    if (key && (storage === 'session' || storage === 'local')) {
                        try {
                            var saved = (storage === 'session' ? sessionStorage : localStorage).getItem(key);
                            var idx = tabs.findIndex(function (t) { return t.getAttribute('data-tab-name') === saved; });
                            if (idx >= 0) { activate(root, tabs, idx, false); return; }
                        } catch (e) { /* private mode */ }
                    }

                    // Sync panels to the server-rendered selection in toggle mode.
                    if (root.getAttribute('data-tabs-panels') === 'toggle') {
                        var current = tabs.findIndex(function (t) { return t.getAttribute('aria-selected') === 'true'; });
                        activate(root, tabs, current >= 0 ? current : 0, false);
                    }
                });
            }

            if (document.readyState !== 'loading') { init(document); }
            else { document.addEventListener('DOMContentLoaded', function () { init(document); }); }
            if (window.htmx) { window.htmx.onLoad(init); }
        })();
    </script>
@endonce
