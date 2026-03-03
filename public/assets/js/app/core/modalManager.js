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
    var _formSubmitted = false;  // tracks whether a form was POSTed in this modal session

    // ── DOM Helpers ────────────────────────────────────────────────────
    function getDialog()  { return document.getElementById('global-modal'); }
    function getContent() { return document.getElementById('global-modal-content'); }
    function getBox()     { return document.getElementById('global-modal-box'); }

    // ── Backdrop Overlay ─────────────────────────────────────────────
    // A regular div that provides the frosted-glass blur behind the modal.
    // We use dialog.show() (not .showModal()) so the dialog stays in normal
    // document flow where backdrop-filter composites correctly with the page.
    // .showModal() puts the dialog in the browser's "top layer" where the
    // ::backdrop pseudo-element blocks backdrop-filter from reaching the page.
    function getOverlay() {
        var el = document.getElementById('modal-blur-overlay');
        if (!el) {
            el = document.createElement('div');
            el.id = 'modal-blur-overlay';
            el.addEventListener('click', function () {
                leantime.modals.closeModal();
            });
            document.body.appendChild(el);
        }
        return el;
    }
    function showOverlay() {
        getOverlay().classList.add('active');
        // Mark the page content as inert so keyboard/screen-reader focus
        // stays inside the dialog (replaces .showModal()'s built-in trap)
        var wrapper = document.querySelector('.mainwrapper');
        if (wrapper) { wrapper.setAttribute('inert', ''); }
    }
    function hideOverlay() {
        getOverlay().classList.remove('active');
        var wrapper = document.querySelector('.mainwrapper');
        if (wrapper) { wrapper.removeAttribute('inert'); }
    }

    // ── Size Determination ─────────────────────────────────────────────
    function isLargeModal(url) {
        return /showTicket|ideaDialog|articleDialog|notesDialog|notebookDialog/.test(url);
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
        // Snapshot to a static array — the live NodeList shifts as we
        // replace nodes, which can orphan later entries.
        var scripts = Array.prototype.slice.call(container.querySelectorAll('script'));
        for (var i = 0; i < scripts.length; i++) {
            var old = scripts[i];
            if (!old.parentNode) { continue; }
            var fresh = document.createElement('script');
            for (var j = 0; j < old.attributes.length; j++) {
                fresh.setAttribute(old.attributes[j].name, old.attributes[j].value);
            }
            fresh.textContent = old.textContent;
            try {
                old.parentNode.replaceChild(fresh, old);
            } catch (e) {
                // Script node was detached during iteration — skip it
            }
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
        // Initialize TipTap editors within the modal content
        if (window.leantime && window.leantime.tiptapController &&
            typeof window.leantime.tiptapController.initEditors === 'function') {
            window.leantime.tiptapController.initEditors(container);
        }
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

    // ── Editor Cleanup ───────────────────────────────────────────────
    // Destroy any Tiptap editors inside the modal before replacing content
    // to prevent orphaned instances that leak memory and misbehave.
    function destroyModalEditors() {
        var c = getContent();
        if (!c) { return; }
        // Tiptap cleanup via registry
        if (window.leantime && window.leantime.tiptapController &&
            window.leantime.tiptapController.registry) {
            try { window.leantime.tiptapController.registry.destroyWithin(c); } catch (e) { /* noop */ }
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
        _formSubmitted = false;  // reset for new modal session

        // Resize — use inline styles instead of Tailwind utilities to
        // avoid CSS @layer conflicts (unlayered reset beats layered DaisyUI).
        if (isLargeModal(url)) {
            box.style.maxWidth = '1024px';
            box.style.width = '95vw';
            box.style.minHeight = '80vh';
        } else {
            box.style.maxWidth = '48rem';
            box.style.width = '';
            box.style.minHeight = '';
        }

        showLoading();

        if (!dialog.open) {
            showOverlay();
            dialog.show();
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
        hideOverlay();

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

        // Refresh the page behind the modal when data may have changed.
        if (typeof window.globalModalCallback === 'function') {
            var cb = window.globalModalCallback;
            window.globalModalCallback = null;
            _formSubmitted = false;
            cb();
        } else if (_formSubmitted) {
            // A form was submitted during this modal session — reload so the
            // page behind reflects the updated state.
            _formSubmitted = false;
            window.location.reload();
        }
        // Otherwise: view-only close — page content is still intact behind
        // the modal, no refresh needed.
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

    // ── Document-level event delegation for modal links ────────────────
    // Instead of attaching handlers to individual links (which misses
    // dynamically added content from hx-boost or HTMX swaps), we use a
    // single delegated handler on the document that catches ALL clicks
    // on hash-based modal links.
    document.addEventListener('click', function (e) {
        // Walk up from the click target to find the nearest <a>
        var link = e.target.closest('a[href^="#/"], a.formModal, a.ticketModal');
        if (!link) { return; }

        var href = link.getAttribute('href');
        if (!href) { return; }

        // Only handle hash links (#/...) or links with modal classes
        if (href.indexOf('#/') === 0) {
            e.preventDefault();
            e.stopPropagation();

            var path = href.substring(1);
            try {
                history.pushState('', document.title,
                    window.location.pathname + window.location.search + href);
            } catch (ex) { /* noop */ }
            leantime.modals.openByUrl(path);
        } else if (link.classList.contains('formModal') || link.classList.contains('ticketModal')) {
            // Non-hash modal links (full URLs with modal class)
            if (href.indexOf('javascript:') === 0) { return; } // skip void links
            e.preventDefault();
            e.stopPropagation();

            leantime.modals.openByUrl(href);
        }
    }, true); // Use capture phase to run before HTMX or other handlers

    // ── Handle the <dialog> native events ─────────────────────────────
    var dialog = document.getElementById('global-modal');
    if (dialog) {
        // Intercept Escape key: .show() doesn't fire 'cancel' like
        // .showModal() does, so we listen for keydown on the dialog.
        dialog.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                e.preventDefault();
                leantime.modals.closeModal();
            }
        });
        // Keep the cancel listener as a safety net (e.g. if showModal is
        // ever used somewhere else).
        dialog.addEventListener('cancel', function (e) {
            e.preventDefault();
            leantime.modals.closeModal();
        });

        // Click outside the modal box to close (the <dialog> covers the
        // entire viewport with transparent background, so clicks on the
        // transparent padding area should close the modal).
        dialog.addEventListener('click', function (e) {
            if (e.target === dialog) {
                leantime.modals.closeModal();
            }
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

        // Sync rich-text editors (TipTap/ProseMirror) to their textareas
        // before building FormData so typed content isn't lost.
        var _tiptapSyncCount = 0;
        form.querySelectorAll('.tiptap-wrapper').forEach(function (wrapper) {
            var textarea = wrapper.querySelector('textarea');
            var pm = wrapper.querySelector('.ProseMirror');
            if (textarea && pm) {
                textarea.value = pm.innerHTML;
                _tiptapSyncCount++;
            }
        });

        _formSubmitted = true;  // track that data may have changed

        var method  = (form.getAttribute('method') || 'GET').toUpperCase();
        var action  = form.getAttribute('action') || window.location.href;
        var content = document.getElementById('global-modal-content');

        console.log('[modalManager] form submit', {
            formId: form.id, action: action, method: method,
            tiptapSynced: _tiptapSyncCount,
            submitter: event.submitter ? event.submitter.name + '=' + event.submitter.value : 'none',
        });

        var opts = {
            method: method,
            credentials: 'include',
            headers: {
                'is-modal': 'true',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (method === 'POST') {
            var fd = new FormData(form);
            // Include the clicked submit button's name/value (FormData omits it).
            // Only override an existing field (e.g. a hidden input with the same
            // name) when the button carries a meaningful value — otherwise keep
            // the hidden input's value intact.
            if (event.submitter && event.submitter.name) {
                if (!fd.has(event.submitter.name)) {
                    fd.set(event.submitter.name, event.submitter.value || '');
                } else if (event.submitter.value) {
                    fd.set(event.submitter.name, event.submitter.value);
                }
            }
            // Debug: log form data entries
            var _fdEntries = {};
            fd.forEach(function(v, k) { _fdEntries[k] = typeof v === 'string' ? v.substring(0, 120) : '[File]'; });
            console.log('[modalManager] FormData', _fdEntries);

            opts.body = fd;
        } else {
            var qs = new URLSearchParams(new FormData(form));
            action += (action.indexOf('?') === -1 ? '?' : '&') + qs.toString();
        }

        fetch(action, opts).then(function (response) {
            console.log('[modalManager] response', {
                status: response.status, redirected: response.redirected,
                url: response.url, hxTrigger: response.headers.get('HX-Trigger'),
            });
            var hxTrigger = response.headers.get('HX-Trigger');
            if (hxTrigger && hxTrigger.indexOf('HTMX.closemodal') !== -1) {
                if (hxTrigger.indexOf('HTMX.ShowNotification') !== -1) {
                    window.dispatchEvent(new CustomEvent('HTMX.ShowNotification'));
                }
                // _formSubmitted is already true — doClose() will reload.
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
 * Handles both hash links (href="#/...") and full URL links.
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
        if (!href) { return; }

        el.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (options && options.callbacks &&
                typeof options.callbacks.beforeClose === 'function') {
                window.globalModalCallback = options.callbacks.beforeClose;
            }

            // Hash links: extract the path from the hash fragment
            if (href.indexOf('#') === 0) {
                var path = href.substring(1);
                // Update the URL hash for bookmarkability
                try {
                    history.pushState('', document.title,
                        window.location.pathname + window.location.search + href);
                } catch (ex) { /* noop */ }
                leantime.modals.openByUrl(path);
            } else {
                leantime.modals.openByUrl(href);
            }
        });
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
