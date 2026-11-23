/**
 * Section tabs (navigation.section-tabs): the tab sets inside cards and dialogs (To-Do, project and client
 * settings, Company settings, My Profile, project selector).
 *
 * Markup contract, kept from the jQuery-UI tabs this replaces because plugins print into it:
 *   <div data-lt-section-tabs>
 *     <ul>  <li><a href="#panelId">Label</a></li> …  </ul>
 *     <div id="panelId">…</div> …
 *   </div>
 * A plugin adds a tab by printing one more `<li><a href="#x">` into the list and a `<div id="x">` into the box.
 *
 * Turns that into an ARIA tablist (roving tabindex, Arrow/Home/End, Space/Enter), shows one panel at a time and
 * fires `lt:tabs:changed` ({group, name, index}) on the box, like navigation.tabs.
 *
 * Which tab opens: the one the URL hash names (`/users/editOwn#notifications`), else the remembered one, else the
 * first. `data-tabs-remember` keeps the choice:
 *   hash         in the URL hash (page-level sets whose forms post back to `…#section`)
 *   query:name   in a query parameter (To-Do: `?tab=timesheet`)
 *   local:key    in localStorage (project selector)
 *
 * Boxes with data-lt-section-tabs set themselves up on page load, after htmx swaps and in dialogs.
 * `leantime.sectionTabs.init(elementOrSelector)` sets up a box without the attribute (plugin pages that still call
 * leantime.projectsController.initProjectTabs()).
 */
leantime.sectionTabs = (function () {

    var READY = 'data-tabs-ready';

    var tabsOf = function (box) {
        var list = box.querySelector(':scope > ul');
        if (!list) {
            return [];
        }

        return Array.prototype.slice.call(list.querySelectorAll(':scope > li > a[href^="#"]'));
    };

    var panelOf = function (box, tab) {
        var panelId = tab.getAttribute('href').slice(1);
        if (!panelId) {
            return null;
        }

        return box.querySelector('#' + CSS.escape(panelId));
    };

    var rememberedTab = function (box, tabs) {
        var remember = box.getAttribute('data-tabs-remember') || '';
        var savedName = null;

        try {
            if (remember.indexOf('query:') === 0) {
                savedName = new URL(window.location.href).searchParams.get(remember.slice(6));
            } else if (remember.indexOf('local:') === 0) {
                savedName = localStorage.getItem(remember.slice(6));
            }
        } catch (e) { /* storage blocked */ }

        if (!savedName) {
            return -1;
        }

        return tabs.findIndex(function (tab) { return tab.getAttribute('href') === '#' + savedName; });
    };

    var remember = function (box, name) {
        var remember = box.getAttribute('data-tabs-remember') || '';

        try {
            if (remember === 'hash') {
                window.history.replaceState(null, '', '#' + name);
            } else if (remember.indexOf('query:') === 0) {
                var url = new URL(window.location.href);
                url.searchParams.set(remember.slice(6), name);
                window.history.replaceState(null, '', url);
            } else if (remember.indexOf('local:') === 0) {
                localStorage.setItem(remember.slice(6), name);
            }
        } catch (e) { /* storage blocked */ }
    };

    var activate = function (box, index, options) {
        var tabs = tabsOf(box);
        var activeTab = tabs[index];
        if (!activeTab) {
            return;
        }

        tabs.forEach(function (tab, tabIndex) {
            var isActive = tabIndex === index;
            tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            tab.setAttribute('tabindex', isActive ? '0' : '-1');

            var panel = panelOf(box, tab);
            if (panel) {
                panel.hidden = !isActive;
            }
        });

        if (options.focus) {
            activeTab.focus();
        }

        var name = activeTab.getAttribute('href').slice(1);
        if (options.remember) {
            remember(box, name);
        }

        box.dispatchEvent(new CustomEvent('lt:tabs:changed', {
            bubbles: true,
            detail: { group: box.id || null, name: name, index: index },
        }));
    };

    // Tab and panel roles. Runs again after an htmx swap inside a set up box: a panel that replaces itself
    // (CustomFields' form swaps its outerHTML on save) comes back without them.
    var markTabsAndPanels = function (box) {
        tabsOf(box).forEach(function (tab) {
            var panelId = tab.getAttribute('href').slice(1);
            tab.parentElement.setAttribute('role', 'presentation');
            tab.setAttribute('role', 'tab');
            if (!tab.id) {
                tab.id = panelId + '-tab';
            }

            var panel = panelOf(box, tab);
            if (!panel) {
                return;
            }
            tab.setAttribute('aria-controls', panelId);
            panel.setAttribute('role', 'tabpanel');
            panel.setAttribute('aria-labelledby', tab.id);
            if (tab.hasAttribute('aria-selected')) {
                panel.hidden = tab.getAttribute('aria-selected') !== 'true';
            }
        });
    };

    var setUp = function (box) {
        if (!box || box.hasAttribute(READY)) {
            return;
        }

        var tabs = tabsOf(box);
        if (!tabs.length) {
            return;
        }
        box.setAttribute(READY, '');
        box.classList.add('lt-section-tabs');

        var list = tabs[0].closest('ul');
        list.classList.add('lt-section-tabs-nav');
        list.setAttribute('role', 'tablist');

        markTabsAndPanels(box);

        // Delegated on the list, so a tab added later (a plugin script) works too.
        list.addEventListener('click', function (event) {
            var tab = event.target.closest('[role="tab"]');
            if (!tab || !list.contains(tab)) {
                return;
            }
            event.preventDefault();
            activate(box, tabsOf(box).indexOf(tab), { remember: true });
        });

        list.addEventListener('keydown', function (event) {
            var currentTabs = tabsOf(box);
            var current = currentTabs.indexOf(document.activeElement);
            if (current < 0) {
                return;
            }

            var next = null;
            if (event.key === 'ArrowRight') {
                next = (current + 1) % currentTabs.length;
            } else if (event.key === 'ArrowLeft') {
                next = (current - 1 + currentTabs.length) % currentTabs.length;
            } else if (event.key === 'Home') {
                next = 0;
            } else if (event.key === 'End') {
                next = currentTabs.length - 1;
            } else if (event.key === ' ') {
                next = current;
            }

            if (next === null) {
                return;
            }
            event.preventDefault();
            activate(box, next, { focus: true, remember: true });
        });

        var hashIndex = tabs.findIndex(function (tab) { return tab.getAttribute('href') === window.location.hash; });
        var startIndex = hashIndex >= 0 ? hashIndex : rememberedTab(box, tabs);
        activate(box, startIndex >= 0 ? startIndex : 0, { remember: false });
    };

    var init = function (root) {
        if (!root) {
            return;
        }

        var swappedInto = root.parentElement && root.parentElement.closest('[' + READY + ']');
        if (swappedInto) {
            markTabsAndPanels(swappedInto);
        }

        if (root.matches && root.matches('[data-lt-section-tabs]')) {
            setUp(root);
        }
        if (root.querySelectorAll) {
            root.querySelectorAll('[data-lt-section-tabs]').forEach(setUp);
        }
    };

    htmx.onLoad(init);
    // Content inserted outside an htmx swap (dialogs …): see leantime.initLoadedContent in app.js.
    document.addEventListener('lt:ui:content.loaded', function (event) { init(event.detail.root); });

    return {
        init: function (element) {
            if (typeof element === 'string') {
                document.querySelectorAll(element).forEach(setUp);
                return;
            }
            setUp(element);
        },
    };
})();
