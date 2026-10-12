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

    // Text of a label without the text of the <select> it may wrap (whose options would leak in).
    var labelText = function (label, select) {
        if (!label) {
            return '';
        }
        var copy = label.cloneNode(true);
        Array.prototype.forEach.call(copy.querySelectorAll('select'), function (el) { el.remove(); });
        return copy.textContent.trim();
    };

    // The enhanced control's accessible name, from the ways our templates label a select.
    // Returns '' when there is none, and the caller then leaves SlimSelect's own default in place.
    var labelFor = function (select) {
        if (select.getAttribute('aria-label')) {
            return select.getAttribute('aria-label');
        }
        if (select.id) {
            var forLabel = document.querySelector('label[for="' + CSS.escape(select.id) + '"]');
            if (labelText(forLabel, select) !== '') {
                return labelText(forLabel, select);
            }
        }
        // <label>Name <select>…</select></label>
        if (labelText(select.closest('label'), select) !== '') {
            return labelText(select.closest('label'), select);
        }
        // <label>Name</label> <select>… — directly before the select or before its wrapper.
        var candidates = [select.previousElementSibling, select.parentElement && select.parentElement.previousElementSibling];
        for (var i = 0; i < candidates.length; i++) {
            if (candidates[i] && candidates[i].tagName === 'LABEL' && labelText(candidates[i], select) !== '') {
                return labelText(candidates[i], select);
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

        var settings = {
            showSearch: showSearchFor(select),
            placeholderText: placeholder !== '' ? placeholder : leantime.i18n.__('label.choose_option'),
            searchPlaceholder: leantime.i18n.__('input.placeholders.search'),
            searchText: leantime.i18n.__('label.no_results'),
            allowDeselect: select.getAttribute('data-allow-deselect') === 'true',
            closeOnSelect: select.getAttribute('data-close-on-select') !== 'false'
        };

        var ariaLabel = labelFor(select);
        if (ariaLabel !== '') {
            settings.ariaLabel = ariaLabel;
        }

        return settings;
    };

    // Tag fields (<x-global::forms.tags>): suggestions come from the project's existing tags, typing a
    // new one adds it, and the chips are written back as one comma-separated string to the hidden
    // input the backend reads (the old jQuery tagsInput contract). That input fires `change`.
    var tagEventsFor = function (select) {
        var target = document.getElementById(select.getAttribute('data-lt-tags'));

        var syncTarget = function (options) {
            if (!target) {
                return;
            }
            var value = options.map(function (option) { return option.value; }).join(',');
            if (target.value === value) {
                return;
            }
            target.value = value;
            target.dispatchEvent(new Event('change', { bubbles: true }));
        };

        return {
            search: function (term, currentData) {
                var chosen = currentData.filter(function (option) { return option.selected; })
                    .map(function (option) { return option.value; });

                return leantime.rpc('Tags.Tags.getTags', { projectId: leantime.currentProject, term: term })
                    .then(function (tags) {
                        return (tags || [])
                            .filter(function (tag) { return chosen.indexOf(tag) === -1; })
                            .map(function (tag) { return { text: tag, value: tag }; });
                    })
                    .catch(function () {
                        return [];
                    });
            },
            addable: function (value) {
                var tag = value.replace(/,/g, ' ').trim();
                return tag === '' ? false : tag;
            },
            afterChange: function (selected) {
                syncTarget(selected);
            }
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
            var config = { select: select, settings: settingsFor(select) };
            if (select.hasAttribute('data-lt-tags')) {
                config.events = tagEventsFor(select);
            }
            var instance = new SlimSelect(config);
            instances.set(select, instance);

            // The option list is mounted on <body>, so inside a Bootstrap dropdown panel a pick would
            // count as an outside click and close the panel. Keep those clicks to the list.
            if (select.closest('.dropdown-menu')) {
                instance.render.content.main.addEventListener('click', function (event) {
                    event.stopPropagation();
                });
            }
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
