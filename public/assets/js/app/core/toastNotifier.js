/**
 * Vanilla toast notification utility — replaces jQuery Growl.
 *
 * Exposes leantime.toast.show({ message, style }) as the public API.
 * A window._growlShim fallback is provided for any remaining legacy callers.
 *
 * Self-contained: all styling is applied inline, no external CSS required.
 */
(function () {
    'use strict';

    var DEFAULTS = {
        duration: 3200,
        close: '\u00d7',
        location: 'default',
        style: 'success',
        size: 'medium'
    };

    // Style tokens for each notification type
    var STYLE_MAP = {
        success: {
            bg: 'var(--green)',
            color: '#fff',
            border: 'var(--green)'
        },
        error: {
            bg: 'var(--red)',
            color: '#fff',
            border: 'var(--red)'
        },
        warning: {
            bg: 'var(--yellow)',
            color: '#333',
            border: 'var(--yellow)'
        },
        notice: {
            bg: 'var(--accent1)',
            color: '#fff',
            border: 'var(--accent1)'
        },
        default: {
            bg: 'var(--secondary-background)',
            color: 'var(--primary-font-color)',
            border: 'var(--main-border-color)'
        }
    };

    /**
     * Inject the container styles once.
     */
    var stylesInjected = false;
    function injectStyles() {
        if (stylesInjected) { return; }
        stylesInjected = true;
        var css = document.createElement('style');
        css.textContent =
            '#growls-default {' +
            '  position: fixed; top: 70px; right: 20px; z-index: 100060;' +
            '  display: flex; flex-direction: column; gap: 8px;' +
            '  pointer-events: none; max-width: 380px;' +
            '}' +
            '.lt-toast {' +
            '  pointer-events: auto; padding: 12px 36px 12px 16px; border-radius: var(--box-radius, 8px);' +
            '  box-shadow: var(--regular-shadow, 0 2px 8px rgba(0,0,0,.15)); font-size: var(--font-size-s, 13px);' +
            '  line-height: 1.4; position: relative; overflow: hidden;' +
            '  opacity: 0; transform: translateX(30px);' +
            '  transition: opacity .25s ease, transform .25s ease;' +
            '}' +
            '.lt-toast.lt-toast-visible {' +
            '  opacity: 1; transform: translateX(0);' +
            '}' +
            '.lt-toast.lt-toast-outgoing {' +
            '  opacity: 0; transform: translateX(30px);' +
            '}' +
            '.lt-toast-close {' +
            '  position: absolute; top: 8px; right: 10px; cursor: pointer;' +
            '  font-size: 18px; line-height: 1; opacity: .7;' +
            '}' +
            '.lt-toast-close:hover { opacity: 1; }' +
            '.lt-toast-title {' +
            '  font-weight: 600; margin-bottom: 2px;' +
            '}' +
            '.lt-toast-message { word-break: break-word; }';
        document.head.appendChild(css);
    }

    /**
     * Ensure the toast container exists for the given location.
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
        injectStyles();
        opts = opts || {};
        var settings = {};
        for (var k in DEFAULTS) { settings[k] = DEFAULTS[k]; }
        for (var k2 in opts) { settings[k2] = opts[k2]; }

        var colors = STYLE_MAP[settings.style] || STYLE_MAP['default'];
        var container = ensureContainer(settings.location);

        // Build toast element
        var el = document.createElement('div');
        el.className = 'lt-toast';
        el.style.background = colors.bg;
        el.style.color = colors.color;
        el.style.borderLeft = '4px solid ' + colors.border;

        el.innerHTML =
            '<div class="lt-toast-close">' + settings.close + '</div>' +
            (settings.title ? '<div class="lt-toast-title">' + settings.title + '</div>' : '') +
            '<div class="lt-toast-message">' + (settings.message || '') + '</div>';

        container.appendChild(el);

        // Close handler
        var closeBtn = el.querySelector('.lt-toast-close');
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

        // Animate in (trigger reflow then add visible class)
        void el.offsetHeight;
        el.classList.add('lt-toast-visible');

        function startDismissTimer() {
            timer = setTimeout(function () { dismiss(el); }, settings.duration);
        }

        function dismiss(element) {
            if (timer) { clearTimeout(timer); timer = null; }
            element.classList.remove('lt-toast-visible');
            element.classList.add('lt-toast-outgoing');
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

    // jQuery.growl shim — ticketsController.js and other legacy code call jQuery.growl()
    if (typeof jQuery !== 'undefined') {
        jQuery.growl = growlShim;
    } else {
        // Attach once jQuery is available
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof jQuery !== 'undefined') {
                jQuery.growl = growlShim;
            }
        });
    }
})();
