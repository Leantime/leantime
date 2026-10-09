/**
 * Enhanced selects (SlimSelect v2) for <x-global::forms.select enhanced>.
 *
 * The component marks the native <select> with data-lt-select; this file is the only place that
 * creates SlimSelect instances. Templates never call `new SlimSelect(...)` themselves.
 *
 * Lifecycle:
 *  - init: on first paint and after every htmx swap (htmx.onLoad), and when a modal shows content.
 *  - destroy: when htmx removes the element (htmx:beforeCleanupElement) and when a modal releases its
 *    content (modals.js releaseContent). SlimSelect mounts its dropdown panel on <body>, so a select
 *    that disappears without destroy() would leave that panel and its window listeners behind.
 *
 * The native <select> stays the source of truth: SlimSelect writes the value back to it, fires a
 * bubbling `change` on it (htmx hx-trigger="change" and jQuery .change() handlers keep working) and
 * watches it, so code that adds, removes or hides <option>s is picked up without a refresh call.
 */
leantime.selectController = (function () {

    // Show the search box automatically once a list is long enough to need it.
    var SEARCH_THRESHOLD = 10;

    var instances = new WeakMap();

    var labelFor = function (select) {
        if (select.getAttribute('aria-label')) {
            return select.getAttribute('aria-label');
        }
        if (select.id) {
            var label = document.querySelector('label[for="' + CSS.escape(select.id) + '"]');
            if (label && label.textContent.trim() !== '') {
                return label.textContent.trim();
            }
        }
        return select.getAttribute('data-placeholder') || select.getAttribute('title') || '';
    };

    var showSearchFor = function (select) {
        var setting = select.getAttribute('data-search');
        if (setting === 'true') {
            return true;
        }
        if (setting === 'false') {
            return false;
        }
        return select.querySelectorAll('option').length > SEARCH_THRESHOLD;
    };

    var settingsFor = function (select) {
        var placeholder = select.getAttribute('data-placeholder') || '';

        return {
            showSearch: showSearchFor(select),
            placeholderText: placeholder !== '' ? placeholder : leantime.i18n.__('label.choose_option'),
            searchPlaceholder: leantime.i18n.__('input.placeholders.search'),
            searchText: leantime.i18n.__('label.no_results'),
            allowDeselect: select.getAttribute('data-allow-deselect') === 'true',
            closeOnSelect: select.getAttribute('data-close-on-select') !== 'false',
            ariaLabel: labelFor(select)
        };
    };

    var enhance = function (select) {
        if (instances.has(select) || typeof SlimSelect === 'undefined') {
            return;
        }

        // A leading blank <option value=""></option> means "nothing picked yet": show it as the
        // placeholder instead of as an empty, clickable row.
        var firstOption = select.options[0];
        if (firstOption && firstOption.value === '' && firstOption.text.trim() === ''
            && !firstOption.hasAttribute('data-placeholder')) {
            firstOption.setAttribute('data-placeholder', 'true');
        }

        try {
            var instance = new SlimSelect({ select: select, settings: settingsFor(select) });
            instances.set(select, instance);
            select.setAttribute('data-lt-select-ready', 'true');
        } catch (error) {
            // A select that fails to enhance still works as a native select.
            console.warn('[selects] Could not enhance select', select, error);
        }
    };

    var selectsWithin = function (root, selector) {
        if (!root || !root.querySelectorAll) {
            return [];
        }
        var found = Array.prototype.slice.call(root.querySelectorAll(selector));
        if (root.matches && root.matches(selector)) {
            found.unshift(root);
        }
        return found;
    };

    var init = function (root) {
        selectsWithin(root || document, 'select[data-lt-select]').forEach(enhance);
    };

    var destroy = function (select) {
        var instance = instances.get(select);
        if (!instance) {
            return;
        }
        try {
            instance.destroy();
        } catch (error) {
            console.warn('[selects] Could not destroy select', select, error);
        }
        instances.delete(select);
        select.removeAttribute('data-lt-select-ready');
    };

    var destroyWithin = function (root) {
        selectsWithin(root, 'select[data-lt-select-ready]').forEach(destroy);
    };

    var get = function (select) {
        return instances.get(select) || null;
    };

    // Set a select's value from code, enhanced or not. Both paths fire `change` on the select, the
    // same as a user pick — guard your own change handler if it sets this value back.
    var setValue = function (select, value) {
        var instance = instances.get(select);
        if (instance) {
            instance.setSelected(value);
            return;
        }
        select.value = value;
        select.dispatchEvent(new Event('change', { bubbles: true }));
    };

    htmx.onLoad(init);

    // htmx fires this for every element it removes (and each of its descendants).
    document.addEventListener('htmx:beforeCleanupElement', function (event) {
        if (event.target && event.target.matches && event.target.matches('select[data-lt-select-ready]')) {
            destroy(event.target);
        }
    });

    return {
        init: init,
        get: get,
        setValue: setValue,
        destroy: destroy,
        destroyWithin: destroyWithin
    };
})();
