/**
 * Vanilla replacement for Bootstrap 2.x dropdown JS.
 *
 * Listens for clicks on [data-toggle="dropdown"] and toggles the .open class
 * on the parent .dropdown or .btn-group — identical to Bootstrap 2.x behavior.
 * Also handles keyboard Escape to close, and clicks outside to dismiss.
 */
document.addEventListener('click', function (e) {
    var toggle = e.target.closest('[data-toggle="dropdown"]');

    // Close all open dropdowns that don't contain the clicked toggle
    document.querySelectorAll('.dropdown.open, .btn-group.open, .inlineDropDownContainer.open, .open > .dropdown-menu').forEach(function (el) {
        var container = el.classList.contains('dropdown-menu') ? el.parentElement : el;
        if (!toggle || !container.contains(toggle)) {
            container.classList.remove('open');
        }
    });

    // Toggle clicked dropdown
    if (toggle) {
        e.preventDefault();
        e.stopPropagation();
        var parent = toggle.closest('.dropdown, .btn-group, .inlineDropDownContainer')
                  || toggle.parentElement;
        if (parent) {
            parent.classList.toggle('open');
        }
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.dropdown.open, .btn-group.open, .inlineDropDownContainer.open').forEach(function (el) {
            el.classList.remove('open');
        });
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// Bootstrap Modal Bridge — replaces Bootstrap 2.x modal JS
// Handles inline modals (<div class="modal">) used by Ideas/Canvas templates.
// ═══════════════════════════════════════════════════════════════════════════

(function () {
    'use strict';

    // Inject minimal modal positioning CSS (Bootstrap provided this before)
    var css = document.createElement('style');
    css.textContent =
        '.modal.in {' +
        '  display: block !important; position: fixed; top: 0; left: 0; right: 0; bottom: 0;' +
        '  z-index: 100050; overflow-x: hidden; overflow-y: auto;' +
        '}' +
        '.modal .modal-dialog {' +
        '  position: relative; margin: 60px auto; max-width: 600px;' +
        '}' +
        '.modal .modal-dialog.modal-lg { max-width: 900px; }' +
        '.modal .modal-content {' +
        '  background: var(--secondary-background, #fff); border-radius: var(--box-radius, 8px);' +
        '  box-shadow: var(--large-shadow, 0 4px 20px rgba(0,0,0,.2)); padding: 20px;' +
        '}' +
        '.modal .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }' +
        '.modal .modal-header .close { background: none; border: none; font-size: 24px; cursor: pointer; color: var(--primary-font-color); }' +
        '.modal .modal-body { margin-bottom: 15px; }' +
        '.modal .modal-footer { display: flex; justify-content: flex-end; gap: 8px; }';
    document.head.appendChild(css);

    var activeBackdrop = null;

    function showModal(modalEl) {
        if (!modalEl) { return; }
        modalEl.style.display = 'block';
        // Force reflow before adding transition class
        void modalEl.offsetHeight;
        modalEl.classList.add('in');
        document.body.classList.add('modal-open');

        // Add backdrop
        if (!activeBackdrop) {
            activeBackdrop = document.createElement('div');
            activeBackdrop.className = 'modal-backdrop fade in';
            activeBackdrop.style.cssText = 'display:block;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);z-index:100049;';
            document.body.appendChild(activeBackdrop);
            activeBackdrop.addEventListener('click', function () {
                var openModal = document.querySelector('.modal.in');
                if (openModal) { hideModal(openModal); }
            });
        }
    }

    function hideModal(modalEl) {
        if (!modalEl) { return; }
        modalEl.classList.remove('in');
        modalEl.style.display = 'none';
        document.body.classList.remove('modal-open');

        if (activeBackdrop && activeBackdrop.parentNode) {
            activeBackdrop.parentNode.removeChild(activeBackdrop);
            activeBackdrop = null;
        }
    }

    // Handle data-dismiss="modal" clicks
    document.addEventListener('click', function (e) {
        var dismissBtn = e.target.closest('[data-dismiss="modal"]');
        if (dismissBtn) {
            var modal = dismissBtn.closest('.modal');
            if (modal) { hideModal(modal); }
        }
    });

    // Escape key closes inline modals too
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var openModal = document.querySelector('.modal.in');
            if (openModal) { hideModal(openModal); }
        }
    });

    // Install jQuery.fn.modal shim if jQuery is present
    if (typeof jQuery !== 'undefined') {
        jQuery.fn.modal = function (action) {
            this.each(function () {
                if (action === 'show') {
                    showModal(this);
                } else if (action === 'hide') {
                    hideModal(this);
                } else if (action === 'toggle') {
                    if (this.classList.contains('in')) {
                        hideModal(this);
                    } else {
                        showModal(this);
                    }
                }
            });
            return this;
        };
    }

    // Expose for vanilla JS callers
    window.leantime = window.leantime || {};
    leantime.bootstrapModal = { show: showModal, hide: hideModal };
})();

// ═══════════════════════════════════════════════════════════════════════════
// jQuery UI Tabs Bridge — thin shim delegating to tabsController.
// Keeps legacy jQuery('.tabs').tabs() calls working during migration.
// ═══════════════════════════════════════════════════════════════════════════

if (typeof jQuery !== 'undefined') {
    jQuery.fn.tabs = function (opts) {
        this.each(function () { leantime.tabsController.initTabs(this, opts); });
        return this;
    };
}

// ═══════════════════════════════════════════════════════════════════════════
// Chosen.js → SlimSelect Bridge
// Replaces jQuery('.selector').chosen() with SlimSelect initialization.
// Handles .chosen('destroy') by destroying the SlimSelect instance.
// ═══════════════════════════════════════════════════════════════════════════

(function () {
    'use strict';

    if (typeof jQuery === 'undefined') { return; }

    jQuery.fn.chosen = function (action) {
        this.each(function () {
            var el = this;
            if (el.tagName !== 'SELECT') { return; }

            // Destroy existing SlimSelect instance if any
            if (el._slimSelect) {
                try { el._slimSelect.destroy(); } catch (e) { /* noop */ }
                el._slimSelect = null;
            }

            // If action is 'destroy', just remove the instance
            if (action === 'destroy') { return; }

            // Initialize SlimSelect
            if (typeof SlimSelect !== 'undefined') {
                try {
                    // Only allow deselect (X button) on multi-selects
                    var isMultiple = el.hasAttribute('multiple');
                    el._slimSelect = new SlimSelect({
                        select: el,
                        showSearch: (el.options && el.options.length > 8),
                        allowDeselect: isMultiple && !el.hasAttribute('required')
                    });
                } catch (e) {
                    // SlimSelect may throw on hidden/detached elements — safe to ignore
                }
            }
        });
        return this;
    };
})();
