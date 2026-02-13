/**
 * Vanilla toast notification utility — replaces jQuery Growl.
 *
 * Exposes leantime.toast.show({ message, style }) as the public API.
 * A window._growlShim fallback is provided for any remaining legacy callers.
 */
(function () {
    'use strict';

    var DEFAULTS = {
        duration: 3200,
        close: '\u00d7',
        location: 'default',
        style: 'success',
        size: 'medium',
        namespace: 'growl'
    };

    /**
     * Ensure the growl container exists for the given location.
     */
    function ensureContainer(location) {
        var id = 'growls-' + location;
        var container = document.getElementById(id);
        if (!container) {
            container = document.createElement('div');
            container.id = id;
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Show a toast notification.
     *
     * @param {Object} opts
     * @param {string} opts.message  - Notification text (HTML allowed)
     * @param {string} [opts.style]  - 'success' | 'error' | 'warning' | 'notice' | 'default'
     * @param {string} [opts.title]  - Optional title text
     * @param {number} [opts.duration] - Auto-dismiss in ms (default 3200)
     */
    function show(opts) {
        opts = opts || {};
        var settings = {};
        for (var k in DEFAULTS) { settings[k] = DEFAULTS[k]; }
        for (var k2 in opts) { settings[k2] = opts[k2]; }

        var ns = settings.namespace;
        var container = ensureContainer(settings.location);

        // Build growl element (same markup as jQuery Growl for CSS compat)
        var el = document.createElement('div');
        el.className = ns + ' ' + ns + '-' + settings.style + ' ' + ns + '-' + settings.size + ' ' + ns + '-incoming';
        el.innerHTML =
            '<div class="' + ns + '-close">' + settings.close + '</div>' +
            (settings.title ? '<div class="' + ns + '-title">' + settings.title + '</div>' : '') +
            '<div class="' + ns + '-message">' + (settings.message || '') + '</div>';

        container.appendChild(el);

        // Close handler
        var closeBtn = el.querySelector('.' + ns + '-close');
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dismiss(el);
        });

        // Right-click to close
        el.addEventListener('contextmenu', function (e) {
            e.preventDefault();
            dismiss(el);
        });

        // Hover to pause
        var timer = null;
        el.addEventListener('mouseenter', function () {
            if (timer) { clearTimeout(timer); timer = null; }
        });
        el.addEventListener('mouseleave', function () {
            startDismissTimer();
        });

        // Present (trigger reflow then remove -incoming)
        void el.offsetHeight;
        el.classList.remove(ns + '-incoming');

        function startDismissTimer() {
            timer = setTimeout(function () { dismiss(el); }, settings.duration);
        }

        function dismiss(element) {
            if (timer) { clearTimeout(timer); timer = null; }
            element.classList.add(ns + '-outgoing');
            element.addEventListener('transitionend', function () {
                if (element.parentNode) element.parentNode.removeChild(element);
            });
            // Fallback if no transition fires
            setTimeout(function () {
                if (element.parentNode) element.parentNode.removeChild(element);
            }, 500);
        }

        startDismissTimer();
    }

    // Expose on leantime namespace
    var leantime = window.leantime || (window.leantime = {});
    leantime.toast = { show: show };

    // Global fallback for any remaining legacy callers
    var growlShim = function (options) { return show(options); };
    growlShim.error = function (options) {
        options = options || {};
        options.style = 'error';
        options.title = options.title || 'Error!';
        return show(options);
    };
    growlShim.notice = function (options) {
        options = options || {};
        options.style = 'notice';
        options.title = options.title || 'Notice!';
        return show(options);
    };
    growlShim.warning = function (options) {
        options = options || {};
        options.style = 'warning';
        options.title = options.title || 'Warning!';
        return show(options);
    };
    window._growlShim = growlShim;
})();
