/**
 * modalManager.js — Native <dialog> replacement for nyroModal
 *
 * Drop-in bridge that intercepts the same hashchange events as the legacy
 * nyroModal system, but renders content in a native <dialog> element.
 *
 * Public API (identical to legacy):
 *  - leantime.modals.openModal()
 *  - leantime.modals.closeModal()
 *  - leantime.modals.setCustomModalCallback(fn)
 *  - leantime.modals.openByUrl(url)          [new — for direct URL opens]
 *  - leantime.modals.nmManual(url, options)  [legacy shim]
 *  - leantime.modals.nmTop()                 [legacy shim]
 *  - leantime.modals.initNyroModal(elements, options) [legacy shim]
 */

leantime.modals = (function () {

    // ── State ──────────────────────────────────────────────────────────
    var isOpen = false;
    var closingProgrammatically = false;

    // ── DOM Helpers ────────────────────────────────────────────────────
    function getDialog()  { return document.getElementById('global-modal'); }
    function getContent() { return document.getElementById('global-modal-content'); }
    function getBox()     { return document.getElementById('global-modal-box'); }

    // ── Size Determination ─────────────────────────────────────────────
    function isLargeModal(url) {
        return /showTicket|ideaDialog|articleDialog/.test(url);
    }

    // ── Loading Spinner ────────────────────────────────────────────────
    function showLoading() {
        var c = getContent();
        if (c) {
            c.innerHTML =
                '<div style="display:flex;justify-content:center;align-items:center;padding:60px 40px;">' +
                '<span class="tw:loading tw:loading-spinner tw:loading-lg"></span>' +
                '</div>';
        }
    }

    // ── Script Execution ───────────────────────────────────────────────
    // innerHTML doesn't run <script> tags; cloning forces execution.
    function executeScripts(container) {
        var scripts = container.querySelectorAll('script');
        for (var i = 0; i < scripts.length; i++) {
            var old = scripts[i];
            var fresh = document.createElement('script');
            for (var j = 0; j < old.attributes.length; j++) {
                fresh.setAttribute(old.attributes[j].name, old.attributes[j].value);
            }
            fresh.textContent = old.textContent;
            old.parentNode.replaceChild(fresh, old);
        }
    }

    // ── Content Extraction ─────────────────────────────────────────────
    // Modal controllers render via displayPartial() → blank layout, so
    // the response is normally a template fragment. If a full page slips
    // through we extract .primaryContent.
    function extractContent(html) {
        if (/<html[\s>]/i.test(html) || /<!DOCTYPE/i.test(html)) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var el  = doc.querySelector('.primaryContent');
            if (el) { return el.innerHTML; }
            return doc.body ? doc.body.innerHTML : html;
        }
        return html;
    }

    // ── Post-Load Initialisation ───────────────────────────────────────
    function initContent(container) {
        container.querySelectorAll('.showDialogOnLoad').forEach(function (el) {
            el.style.display = '';
        });
        if (window.htmx) { window.htmx.process(container); }
        if (window.tippy) { tippy(container.querySelectorAll('[data-tippy-content]')); }
    }

    // ── Response Handler (shared by open + form submit) ────────────────
    // Returns a Promise that resolves to null (already handled) or the
    // response text.
    function handleResponse(response) {
        // HX-Trigger: close
        var hxTrigger = response.headers.get('HX-Trigger');
        if (hxTrigger && hxTrigger.indexOf('HTMX.closemodal') !== -1) {
            if (hxTrigger.indexOf('HTMX.ShowNotification') !== -1) {
                window.dispatchEvent(new CustomEvent('HTMX.ShowNotification'));
            }
            doClose();
            return Promise.resolve(null);
        }

        // HX-Redirect
        var hxRedirect = response.headers.get('HX-Redirect');
        if (hxRedirect) {
            doClose();
            window.location.href = hxRedirect;
            return Promise.resolve(null);
        }

        return response.text();
    }

    // ── TinyMCE Cleanup ────────────────────────────────────────────────
    // Destroy any TinyMCE editors inside the modal before replacing content
    // to prevent orphaned instances that leak memory and misbehave.
    function destroyModalEditors() {
        if (typeof tinymce === 'undefined') { return; }
        var c = getContent();
        if (!c) { return; }
        var editors = tinymce.get();
        for (var i = editors.length - 1; i >= 0; i--) {
            if (c.contains(editors[i].getElement())) {
                try { editors[i].save(); } catch (e) { /* noop */ }
                try { editors[i].destroy(false); } catch (e) { /* noop */ }
            }
        }
    }

    // ── Render HTML into modal ─────────────────────────────────────────
    function renderContent(html) {
        if (html === null) { return; }
        var c = getContent();
        if (!c) { return; }
        destroyModalEditors();
        c.innerHTML = extractContent(html);
        executeScripts(c);
        initContent(c);
    }

    // ── Core: Open Modal from URL ──────────────────────────────────────
    function openModalFromUrl(url) {
        var dialog = getDialog();
        var box    = getBox();
        if (!dialog) { return; }

        // Resize
        box.className = box.className
            .replace(/tw:max-w-\S+/g, '')
            .trim();
        box.style.minHeight = '';

        if (isLargeModal(url)) {
            box.classList.add('tw:max-w-5xl');
            box.style.minHeight = '80vh';
        } else {
            box.classList.add('tw:max-w-3xl');
        }

        showLoading();

        if (!dialog.open) {
            dialog.showModal();
        }
        isOpen = true;

        var baseUrl = leantime.appUrl.replace(/\/$/, '');
        var fullUrl = (url.indexOf('http') === 0) ? url : baseUrl + url;

        fetch(fullUrl, {
            credentials: 'include',
            headers: {
                'is-modal': 'true',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(handleResponse)
        .then(renderContent)
        .catch(function (err) {
            console.error('modalManager: load error', err);
            var c = getContent();
            if (c) {
                c.innerHTML =
                    '<div style="padding:24px;text-align:center;">' +
                    '<p>Could not load content.</p></div>';
            }
        });
    }

    // ── Close Helpers ──────────────────────────────────────────────────
    // doClose: the actual close logic (hash cleanup + callback/reload).
    // Called both programmatically and from the dialog's native close event.
    function doClose() {
        var dialog = getDialog();
        if (!dialog) { return; }
        if (!isOpen) { return; }  // Prevent double-close

        isOpen = false;
        destroyModalEditors();

        // Clear hash
        try {
            history.pushState('', document.title,
                window.location.pathname + window.location.search);
        } catch (e) { /* noop */ }

        // Close the <dialog> if still open (flag prevents re-entry from
        // the native 'close' event handler)
        if (dialog.open) {
            closingProgrammatically = true;
            dialog.close();
            closingProgrammatically = false;
        }

        // Callback or reload
        if (typeof window.globalModalCallback === 'function') {
            var cb = window.globalModalCallback;
            window.globalModalCallback = null;
            cb();
        } else {
            location.reload();
        }
    }

    // ── Public API ─────────────────────────────────────────────────────

    var openModal = function () {
        var url = window.location.hash.substring(1);
        if (!url) { return; }
        var parts = url.split('/');
        if (parts.length > 2 && parts[1] !== 'tab') {
            openModalFromUrl(url);
        }
    };

    var closeModal = function () {
        doClose();
    };

    var setCustomModalCallback = function (callback) {
        if (typeof callback === 'function') {
            window.globalModalCallback = callback;
        }
    };

    var openByUrl = function (url) {
        var baseUrl = leantime.appUrl.replace(/\/$/, '');
        var path = url;
        if (url.indexOf(baseUrl) === 0) {
            path = url.substring(baseUrl.length);
        }
        openModalFromUrl(path);
    };

    return {
        openModal: openModal,
        setCustomModalCallback: setCustomModalCallback,
        closeModal: closeModal,
        openByUrl: openByUrl,
        renderContent: renderContent
    };

})();


// ═══════════════════════════════════════════════════════════════════════
// Event Listeners
// ═══════════════════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', function () {

    // ── Open modal on page load if hash present ────────────────────────
    leantime.modals.openModal();

    // ── Wire .formModal links on the main page ──────────────────────────
    // Links with class="formModal" and non-hash hrefs should open in the modal.
    // Hash links are already handled by the hashchange listener.
    leantime.modals.initNyroModal(
        document.querySelectorAll('a.formModal'),
        { callbacks: { beforeClose: function () { location.reload(); } } }
    );

    // ── Handle the <dialog> native events ─────────────────────────────
    var dialog = document.getElementById('global-modal');
    if (dialog) {
        // Intercept Escape key: prevent native close, use our doClose()
        // so the callback/reload logic runs properly.
        dialog.addEventListener('cancel', function (e) {
            e.preventDefault();
            leantime.modals.closeModal();
        });

        // The native 'close' event fires when:
        //  - <form method="dialog"> buttons (X or backdrop) submit
        //  - dialog.close() is called programmatically
        // We delegate to closeModal() which handles the flag to prevent loops.
        dialog.addEventListener('close', function () {
            // doClose() already ran if it set closingProgrammatically.
            // For user-initiated closes (backdrop, X button), the dialog
            // is already closed at this point so doClose() would skip
            // dialog.close(). We just need the callback/reload.
            // Using closeModal() is safe because of the isOpen guard.
            leantime.modals.closeModal();
        });
    }

    // ── Intercept form submissions inside the modal ────────────────────
    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!form || !form.closest || !form.closest('#global-modal-content')) { return; }
        if (form.getAttribute('method') === 'dialog') { return; }
        if (form.hasAttribute('hx-post') || form.hasAttribute('hx-get') ||
            form.hasAttribute('hx-put')  || form.hasAttribute('hx-delete')) { return; }

        event.preventDefault();

        var method  = (form.getAttribute('method') || 'GET').toUpperCase();
        var action  = form.getAttribute('action') || window.location.href;
        var content = document.getElementById('global-modal-content');

        var opts = {
            method: method,
            credentials: 'include',
            headers: {
                'is-modal': 'true',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (method === 'POST') {
            opts.body = new FormData(form);
        } else {
            var qs = new URLSearchParams(new FormData(form));
            action += (action.indexOf('?') === -1 ? '?' : '&') + qs.toString();
        }

        fetch(action, opts).then(function (response) {
            var hxTrigger = response.headers.get('HX-Trigger');
            if (hxTrigger && hxTrigger.indexOf('HTMX.closemodal') !== -1) {
                if (hxTrigger.indexOf('HTMX.ShowNotification') !== -1) {
                    window.dispatchEvent(new CustomEvent('HTMX.ShowNotification'));
                }
                leantime.modals.closeModal();
                return null;
            }

            var hxRedirect = response.headers.get('HX-Redirect');
            if (hxRedirect) {
                leantime.modals.closeModal();
                window.location.href = hxRedirect;
                return null;
            }

            return response.text();
        }).then(function (html) {
            leantime.modals.renderContent(html);
        }).catch(function (err) {
            console.error('modalManager: form submit error', err);
        });
    }, true);
});

// Hash change -> open modal
window.addEventListener('hashchange', function () {
    leantime.modals.openModal();
});

// Custom close events
window.addEventListener('closeModal', function () {
    leantime.modals.closeModal();
});

// HTMX server-triggered close
document.addEventListener('HTMX.closemodal', function () {
    leantime.modals.closeModal();
});


// ═══════════════════════════════════════════════════════════════════════
// Legacy Shims (backward compatibility — no jQuery dependency)
// ═══════════════════════════════════════════════════════════════════════

/**
 * leantime.modals.nmManual(url, options)
 * Opens a modal by URL — replaces nyroModal's static helper.
 * Also installed on jQuery.nmManual if jQuery is present.
 */
leantime.modals.nmManual = function (url, options) {
    // Some callers pass a factory function instead of an options object
    if (typeof options === 'function') {
        options = options();
    }
    if (options && options.callbacks && typeof options.callbacks.beforeClose === 'function') {
        window.globalModalCallback = options.callbacks.beforeClose;
    }
    leantime.modals.openByUrl(url);
};

/**
 * leantime.modals.nmTop()
 * Returns a mock of nyroModal's top-modal object with vanilla DOM elements.
 * Also installed on jQuery.nmTop if jQuery is present.
 */
leantime.modals.nmTop = function () {
    return {
        close: function () {
            leantime.modals.closeModal();
        },
        elts: {
            cont: document.getElementById('global-modal-box'),
            bg:   document.querySelector('#global-modal .tw\\:modal-backdrop'),
            load: document.querySelector('#global-modal .tw\\:loading'),
            all:  document.getElementById('global-modal')
        }
    };
};

/**
 * leantime.modals.initNyroModal(elements, options)
 * Converts .nyroModal() init calls into native-dialog click handlers.
 * Hash links (href="#/...") already work via hashchange and are skipped.
 * Also installed on jQuery.fn.nyroModal if jQuery is present.
 *
 * @param {NodeList|Array|HTMLElement} elements - DOM elements to initialize
 * @param {Object} [options] - Options with optional callbacks
 */
leantime.modals.initNyroModal = function (elements, options) {
    if (elements instanceof HTMLElement) {
        elements = [elements];
    }
    Array.prototype.forEach.call(elements, function (el) {
        if (el.dataset.modalManagerInit) { return; }
        el.dataset.modalManagerInit = 'true';

        if (options && options.callbacks) {
            // Store options reference on the element for later retrieval
            el._modalManagerOpts = options;
        }

        var href = el.getAttribute('href');

        // Hash links are handled by the global hashchange listener
        if (href && href.indexOf('#') === 0) { return; }

        // Non-hash links: open in modal on click
        if (href) {
            el.addEventListener('click', function (e) {
                e.preventDefault();
                if (options && options.callbacks &&
                    typeof options.callbacks.beforeClose === 'function') {
                    window.globalModalCallback = options.callbacks.beforeClose;
                }
                leantime.modals.openByUrl(href);
            });
        }
    });
};

// If jQuery is present, install shims on it for backward compatibility
if (typeof jQuery !== 'undefined') {
    jQuery.nmManual = leantime.modals.nmManual;
    jQuery.nmTop = leantime.modals.nmTop;
    jQuery.fn.nyroModal = function (options) {
        leantime.modals.initNyroModal(this.toArray(), options);
        return this;
    };
}
