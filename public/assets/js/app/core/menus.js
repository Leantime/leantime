/**
 * Dropdown menus (x-global::actions.dropdown): what Bootstrap 2's dropdown doesn't do.
 *
 * - aria-expanded on every trigger that declares it follows its menu.
 * - [data-lt-dropdown-float] menus are positioned against the window, so a dialog or a
 *   scrolling box can't clip them. They open below the trigger, or above when there is more
 *   room there, right-aligned to it, follow it when the page or a box around it scrolls, and
 *   close once it is out of sight (or the window is resized).
 * - Escape on a dropdown trigger closes its open menu; on a closed one it closes the dialog the
 *   trigger is in (Bootstrap 2 would open the menu instead).
 * - In-place drill-down: a [data-lt-drill-open] item (aria-controls → its panel) swaps the menu
 *   to that panel on click, Enter, Space or ArrowRight, focusing its checked choice; the panel's
 *   [data-lt-drill-back] button, ArrowLeft or Escape return to the top level with focus on the
 *   item. A menu always opens at its top level; after an htmx pick inside the panel it returns
 *   there too, the menu still open.
 * - [data-lt-menu-keys] menus: ArrowDown/ArrowUp/Home/End move between the menu's items,
 *   Escape closes the menu and returns focus to its trigger, Space picks a radio item. ArrowDown
 *   on the trigger of an open menu goes to its first item. (Bootstrap only does this for
 *   role="menu", which these menus aren't: they mix headings, text and items.) Arrows only move
 *   focus, also through a radio group: a choice that saves to the server waits for Enter/Space.
 * - After an htmx swap inside a menu, focus goes back to the swapped-in content (the checked
 *   item, if any) when it was there before, or to the menu's trigger when picking closed the
 *   menu, and an element with data-lt-announce="…" is read out through one polite live region.
 *
 * Everything is delegated from document, so menus added by htmx or in dialogs need no init.
 */
leantime.menus = (function () {

    var ITEMS = 'a[href]:not([aria-disabled="true"]), button:not([disabled]), [role^="menuitem"], [role="radio"]';

    var liveRegion = function () {
        var region = document.getElementById('lt-live-region');
        if (!region) {
            region = document.createElement('div');
            region.id = 'lt-live-region';
            region.className = 'tw-sr-only';
            region.setAttribute('aria-live', 'polite');
            region.setAttribute('role', 'status');
            document.body.appendChild(region);
        }
        return region;
    };

    var announce = function (text) {
        if (!text) {
            return;
        }
        var region = liveRegion();
        region.textContent = '';
        // A change after the clear is what screen readers announce.
        setTimeout(function () { region.textContent = text; }, 50);
    };

    var wrapperOf = function (element) {
        return element.closest('.inlineDropDownContainer, .dropdown, .btn-group, .dropdownWrapper, [data-lt-dropdown-float]');
    };

    var toggleOf = function (wrapper) {
        return wrapper ? wrapper.querySelector(':scope > [data-toggle="dropdown"]') : null;
    };

    var menuOf = function (wrapper) {
        return wrapper ? wrapper.querySelector(':scope > .dropdown-menu') : null;
    };

    var syncExpanded = function () {
        document.querySelectorAll('[data-toggle="dropdown"][aria-expanded]').forEach(function (toggle) {
            var wrapper = toggle.parentElement;
            toggle.setAttribute('aria-expanded', wrapper && wrapper.classList.contains('open') ? 'true' : 'false');
        });
    };

    // position: fixed is relative to the viewport unless an ancestor has a transform, filter,
    // backdrop-filter, perspective, paint/layout containment or will-change of those: then that
    // ancestor's padding box is the containing block (and the menu scrolls with it). The dialog
    // backdrop (.nyroModalBg, backdrop-filter, scrolls) is one.
    var containingBlock = function (element) {
        for (var node = element.parentElement; node && node !== document.documentElement; node = node.parentElement) {
            var style = getComputedStyle(node);
            if ((style.transform && style.transform !== 'none')
                || (style.filter && style.filter !== 'none')
                || (style.backdropFilter && style.backdropFilter !== 'none')
                || (style.webkitBackdropFilter && style.webkitBackdropFilter !== 'none')
                || (style.perspective && style.perspective !== 'none')
                || /paint|layout|strict|content/.test(style.contain || '')
                || /transform|filter|perspective/.test(style.willChange || '')) {
                return node;
            }
        }
        return null;
    };

    var place = function (wrapper) {
        var toggle = toggleOf(wrapper);
        var menu = menuOf(wrapper);
        if (!toggle || !menu || !wrapper.classList.contains('open')) {
            return;
        }

        var gap = 4;
        var edge = 8;
        var anchor = toggle.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.right = 'auto';
        menu.style.bottom = 'auto';
        menu.style.maxHeight = '';
        menu.style.overflowY = '';

        var width = menu.offsetWidth;
        var height = menu.offsetHeight;
        var below = window.innerHeight - anchor.bottom - gap - edge;
        var above = anchor.top - gap - edge;
        var openUp = height > below && above > below;
        var room = openUp ? above : below;
        if (height > room) {
            menu.style.maxHeight = room + 'px';
            menu.style.overflowY = 'auto';
            height = room;
        }

        // Worked out in viewport coordinates, then moved into the containing block's, if any.
        var left = Math.max(edge, Math.min(anchor.right - width, window.innerWidth - width - edge));
        var top = openUp ? anchor.top - gap - height : anchor.bottom + gap;
        var block = containingBlock(menu);
        if (block) {
            var box = block.getBoundingClientRect();
            left = left - box.left - block.clientLeft + block.scrollLeft;
            top = top - box.top - block.clientTop + block.scrollTop;
        }
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        wrapper.classList.toggle('lt-dropdown-up', openUp);
    };

    var closeFloating = function () {
        document.querySelectorAll('[data-lt-dropdown-float].open').forEach(function (wrapper) {
            wrapper.classList.remove('open');
        });
        syncExpanded();
    };

    var visibleItems = function (menu) {
        return Array.prototype.filter.call(menu.querySelectorAll(ITEMS), function (item) {
            return item.offsetParent !== null || item.getClientRects().length > 0;
        });
    };

    var close = function (wrapper) {
        wrapper.classList.remove('open');
        syncExpanded();
        var toggle = toggleOf(wrapper);
        if (toggle) {
            toggle.focus();
        }
    };

    // In-place drill-down ([data-lt-drill-open] item with aria-controls → its panel): the menu
    // shows only that panel (.lt-drilled hides the menu's other items) until [data-lt-drill-back],
    // ArrowLeft or Escape returns to the top level.
    var drillIn = function (trigger) {
        var menu = trigger.closest('.dropdown-menu');
        var panel = document.getElementById(trigger.getAttribute('aria-controls'));
        if (!menu || !panel) {
            return;
        }
        panel.hidden = false;
        menu.classList.add('lt-drilled');
        trigger.setAttribute('aria-expanded', 'true');
        var wrapper = wrapperOf(menu);
        if (wrapper && wrapper.matches('[data-lt-dropdown-float]')) {
            place(wrapper);
        }
        var start = panel.querySelector('[aria-checked="true"]') || panel.querySelector('[role="radio"]') || panel.querySelector('[data-lt-drill-back]');
        if (start) {
            start.focus();
        }
    };

    var drillOut = function (menu, focusTrigger) {
        menu.classList.remove('lt-drilled');
        var trigger = null;
        menu.querySelectorAll('[data-lt-drill-open]').forEach(function (item) {
            item.setAttribute('aria-expanded', 'false');
            var panel = document.getElementById(item.getAttribute('aria-controls'));
            if (panel && !panel.hidden) {
                trigger = item;
            }
            if (panel) {
                panel.hidden = true;
            }
        });
        var wrapper = wrapperOf(menu);
        if (wrapper && wrapper.matches('[data-lt-dropdown-float]')) {
            place(wrapper);
        }
        if (focusTrigger) {
            (trigger || menu.querySelector('[data-lt-drill-open]'))?.focus();
        }
    };

    // Capture phase: drill items live in content that keeps its clicks from Bootstrap (so the
    // menu stays open), which also keeps them from a bubbling listener.
    document.addEventListener('click', function (event) {
        var target = event.target && event.target.closest ? event.target : null;
        if (!target) {
            return;
        }
        var open = target.closest('[data-lt-drill-open]');
        if (open) {
            event.preventDefault();
            drillIn(open);
            return;
        }
        var back = target.closest('[data-lt-drill-back]');
        if (back && back.closest('.dropdown-menu')) {
            event.preventDefault();
            drillOut(back.closest('.dropdown-menu'), true);
        }
    }, true);

    // Runs after Bootstrap's own click handler has opened or closed the menu. A menu always opens
    // at its top level.
    document.addEventListener('click', function (event) {
        setTimeout(function () {
            syncExpanded();
            var toggle = event.target.closest && event.target.closest('[data-toggle="dropdown"]');
            var wrapper = toggle ? toggle.parentElement : null;
            var menu = wrapper ? menuOf(wrapper) : null;
            if (menu && menu.classList.contains('lt-drilled')) {
                drillOut(menu, false);
            }
            if (wrapper && wrapper.matches('[data-lt-dropdown-float]')) {
                place(wrapper);
            }
        }, 0);
    });

    document.addEventListener('keydown', function (event) {
        var target = event.target;
        if (!target || !target.closest) {
            return;
        }

        var toggle = target.closest('[data-toggle="dropdown"]');

        // Bootstrap 2 answers Escape on a closed menu's trigger by opening the menu, and swallows
        // the key: Escape there could never close the dialog around it. An open menu closes; on a
        // closed one Escape goes to the dialog (or nowhere).
        if (toggle && event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            if (toggle.parentElement.classList.contains('open')) {
                close(toggle.parentElement);
            } else if (window.jQuery && jQuery.nmTop && jQuery.nmTop() && toggle.closest('.nyroModalCont')) {
                leantime.modals.closeModal();
            }
            return;
        }

        if (toggle && event.key === 'ArrowDown') {
            var toggleMenu = menuOf(toggle.parentElement);
            if (toggleMenu && toggleMenu.matches('[data-lt-menu-keys]') && toggle.parentElement.classList.contains('open')) {
                var first = visibleItems(toggleMenu)[0];
                if (first) {
                    event.preventDefault();
                    first.focus();
                }
            }
            return;
        }

        var menu = target.closest('[data-lt-menu-keys]');
        if (!menu) {
            return;
        }
        var wrapper = wrapperOf(menu);

        if ((event.key === 'Escape' || event.key === 'ArrowLeft') && menu.classList.contains('lt-drilled')) {
            event.preventDefault();
            event.stopPropagation();
            drillOut(menu, true);
            return;
        }

        if (event.key === 'ArrowRight' && target.matches('[data-lt-drill-open]')) {
            event.preventDefault();
            drillIn(target);
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation(); // the menu closes, not the dialog around it
            close(wrapper);
            return;
        }

        if (event.key === ' ' && target.matches('[role="radio"], [role="button"], [role^="menuitem"]')) {
            event.preventDefault();
            target.click();
            return;
        }

        var keys = ['ArrowDown', 'ArrowUp', 'Home', 'End'];
        if (keys.indexOf(event.key) === -1) {
            return;
        }
        var items = visibleItems(menu);
        if (!items.length) {
            return;
        }
        event.preventDefault();
        var index = items.indexOf(document.activeElement);
        var next = {
            ArrowDown: index < 0 ? 0 : Math.min(items.length - 1, index + 1),
            ArrowUp: index < 0 ? items.length - 1 : Math.max(0, index - 1),
            Home: 0,
            End: items.length - 1
        }[event.key];
        items[next].focus();
    }, true);

    // A floating menu follows its trigger when the page or a box around it scrolls, and closes
    // once the trigger has scrolled out of sight.
    var following = false;
    var follow = function () {
        following = false;
        document.querySelectorAll('[data-lt-dropdown-float].open').forEach(function (wrapper) {
            var toggle = toggleOf(wrapper);
            var rect = toggle ? toggle.getBoundingClientRect() : null;
            if (!rect || rect.bottom < 0 || rect.top > window.innerHeight || rect.width === 0) {
                wrapper.classList.remove('open');
                syncExpanded();
                return;
            }
            place(wrapper);
        });
    };
    window.addEventListener('scroll', function (event) {
        if (event.target && event.target.closest && event.target.closest('[data-lt-dropdown-float] .dropdown-menu')) {
            return;
        }
        if (!following) {
            following = true;
            requestAnimationFrame(follow);
        }
    }, true);
    window.addEventListener('resize', closeFloating);

    // Focus and announcements around htmx swaps inside menus.
    // Recorded when the request starts: a pick is a click, and Bootstrap closes the menu (taking
    // the focused item out of sight) before the response comes back.
    var hadFocus = new WeakSet();
    document.addEventListener('htmx:beforeRequest', function (event) {
        var elt = event.detail && event.detail.elt;
        var target = event.detail && event.detail.target;
        // Only after a pick (a POST from an item), not when a menu's content first loads.
        if (elt && target && elt.closest && elt.hasAttribute('hx-post') && elt.closest('.dropdown-menu') && target.contains(elt)) {
            hadFocus.add(target);
        }
    });
    document.addEventListener('htmx:afterSettle', function (event) {
        var elt = event.detail && event.detail.elt;
        if (!elt || !elt.isConnected) {
            var id = event.detail && event.detail.target && event.detail.target.id;
            elt = id ? document.getElementById(id) : null;
        }
        if (!elt) {
            return;
        }

        var announced = elt.matches('[data-lt-announce]') ? elt : elt.querySelector('[data-lt-announce]');
        if (announced) {
            announce(announced.getAttribute('data-lt-announce'));
        }

        var floating = elt.closest('[data-lt-dropdown-float].open');
        if (floating) {
            place(floating);
        }

        var source = event.detail && event.detail.target;
        if (source && hadFocus.has(source)) {
            var menuWrapper = wrapperOf(elt);
            var swappedMenu = elt.closest('.dropdown-menu');
            if (swappedMenu && swappedMenu.classList.contains('lt-drilled')) {
                // The pick is saved: show the top level with its new label, focus on its item.
                drillOut(swappedMenu, true);
            } else if (menuWrapper && menuWrapper.classList.contains('open')) {
                var focusTarget = elt.querySelector('[aria-checked="true"]') || visibleItems(elt)[0];
                if (focusTarget) {
                    focusTarget.focus();
                }
            } else if (toggleOf(menuWrapper)) {
                // Picking closed the menu (Bootstrap closes on any click): back to its trigger.
                toggleOf(menuWrapper).focus();
            }
        }
    });

    return {
        announce: announce,
        place: place
    };
})();
