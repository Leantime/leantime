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
// TomSelect — component registrations
//
//   1. Chip selects  (<select class="select-chip">)
//      Pill-shaped trigger that renders badge HTML from data-chip-html on
//      each <option>.
//
//   2. Standard selects  (<select class="tomselect">)
//      Searchable styled dropdown.
//
//   3. Filter-bar selects  (.filterBar select)
//      Same as standard but reads placeholder from a data-placeholder option.
//
//   4. Tag inputs  (<input class="tag-input">)
//      Free-form comma-delimited tag entry with optional autocomplete.
//      Pass autocomplete URL via data-autocomplete-url attribute.
// ═══════════════════════════════════════════════════════════════════════════

(function () {
    'use strict';

    var cr = (typeof leantime !== 'undefined') && leantime.componentRegistry;
    if (!cr) { return; }

    // ── 1. Chip selects ────────────────────────────────────────────────────

    cr.register(
        'select.select-chip',
        function initChipSelect(el) {
            if (typeof TomSelect === 'undefined') { return; }

            // Read badge HTML from data-chip-html on each <option> before TomSelect
            // touches the element. Browsers strip child elements from <option> tags,
            // so badge HTML is stored in data attributes.
            var chipHtmlMap = {};
            Array.prototype.forEach.call(el.options, function (opt) {
                if (opt.dataset.chipHtml) {
                    chipHtmlMap[opt.value] = opt.dataset.chipHtml;
                }
            });

            try {
                el._tomSelect = new TomSelect(el, {
                    // No search input — chip is a compact pill
                    controlInput: null,
                    // Allow the dropdown to escape overflow:auto containers (e.g. .widgetContent)
                    dropdownParent: 'body',
                    // Size dropdown to its content. Body-appended absolutely-positioned
                    // elements inherit their containing block width, not content width.
                    // scrollWidth after render gives the true content width.
                    onDropdownOpen: function (dropdown) {
                        dropdown.style.width = 'auto';
                        dropdown.style.width = Math.min(dropdown.scrollWidth + 5, 200) + 'px';
                    },
                    // Render option rows in the dropdown
                    render: {
                        option: function (data) {
                            var html = chipHtmlMap[data.value];
                            if (html) {
                                return '<div class="ts-chip-option">' + html + '</div>';
                            }
                            return '<div class="ts-chip-option">' + (data.text || data.value) + '</div>';
                        },
                        // Render the selected item in the control pill.
                        // The caret SVG is injected inside the badge <span> (before </span>)
                        // so it sits inside the coloured pill, not floating after it.
                        item: function (data) {
                            var caret = '<svg class="ts-chip-caret" viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';
                            var html = chipHtmlMap[data.value];
                            if (html) {
                                // Inject caret before the closing </span> of the chip-badge
                                var withCaret = html.replace('</span>', caret + '</span>');
                                return '<div class="ts-chip-item">' + withCaret + '</div>';
                            }
                            return '<div class="ts-chip-item">' + data.value + caret + '</div>';
                        },
                    },
                });
            } catch (e) {
                console.error('[componentRegistry] TomSelect chip init failed:', e);
            }
        },
        {
            sentinel:  'data-chip-init',
            destroyFn: function (el) {
                if (el._tomSelect) {
                    try { el._tomSelect.destroy(); } catch (e) { /* noop */ }
                    el._tomSelect = null;
                }
            },
        }
    );

    // ── 2. Standard selects ────────────────────────────────────────────────

    cr.register(
        'select.tomselect',
        function initStandardSelect(el) {
            if (typeof TomSelect === 'undefined') { return; }
            var isMultiple = el.hasAttribute('multiple');
            try {
                el._tomSelect = new TomSelect(el, {
                    plugins: isMultiple ? ['remove_button', 'clear_button'] : [],
                    allowEmptyOption: true,
                    // Show search when there are more than 8 options
                    controlInput: (el.options && el.options.length > 8) ? undefined : null,
                });
            } catch (e) {
                console.error('[componentRegistry] TomSelect standard init failed:', e);
            }
        },
        {
            sentinel:  'data-ts-init',
            destroyFn: function (el) {
                if (el._tomSelect) {
                    try { el._tomSelect.destroy(); } catch (e) { /* noop */ }
                    el._tomSelect = null;
                }
            },
        }
    );

    // ── 3. Filter-bar selects ──────────────────────────────────────────────

    cr.register(
        '.filterBar select',
        function initFilterSelect(el) {
            if (typeof TomSelect === 'undefined') { return; }
            // Skip chip selects — they have their own registration above
            if (el.classList.contains('select-chip')) { return; }
            // Skip already-initialized standard selects
            if (el.classList.contains('tomselect')) { return; }
            var placeholder = el.querySelector('option[data-placeholder]');
            try {
                el._tomSelect = new TomSelect(el, {
                    allowEmptyOption: true,
                    placeholder: placeholder ? placeholder.textContent.trim() : 'All',
                });
            } catch (e) {
                console.error('[componentRegistry] TomSelect filter init failed:', e);
            }
        },
        {
            sentinel:  'data-ts-init',
            destroyFn: function (el) {
                if (el._tomSelect) {
                    try { el._tomSelect.destroy(); } catch (e) { /* noop */ }
                    el._tomSelect = null;
                }
            },
        }
    );

    // ── 4. Tag inputs ──────────────────────────────────────────────────────

    cr.register(
        'input.tag-input',
        function initTagInput(el) {
            if (typeof TomSelect === 'undefined') { return; }

            var autocompleteUrl = el.dataset.autocompleteUrl || null;
            var options = {};
            if (autocompleteUrl) {
                options.load = function (query, callback) {
                    if (!query.length) { return callback(); }
                    fetch(autocompleteUrl + encodeURIComponent(query), {
                        credentials: 'include',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    })
                        .then(function (r) { return r.json(); })
                        .then(function (json) {
                            // Expect [{value, text}] or [string]
                            if (Array.isArray(json)) {
                                callback(json.map(function (item) {
                                    return typeof item === 'string'
                                        ? { value: item, text: item }
                                        : item;
                                }));
                            } else {
                                callback();
                            }
                        })
                        .catch(function () { callback(); });
                };
            }

            try {
                el._tomSelect = new TomSelect(el, Object.assign({
                    plugins: ['remove_button'],
                    create: true,
                    delimiter: ',',
                    persist: false,
                }, options));
            } catch (e) {
                console.error('[componentRegistry] TomSelect tag-input init failed:', e);
            }
        },
        {
            sentinel:  'data-ts-init',
            destroyFn: function (el) {
                if (el._tomSelect) {
                    try { el._tomSelect.destroy(); } catch (e) { /* noop */ }
                    el._tomSelect = null;
                }
            },
        }
    );

    // ── jQuery.fn.chosen shim — delegates to TomSelect ────────────────────
    // Keeps legacy jQuery('.selector').chosen() calls working during migration.

    if (typeof jQuery !== 'undefined') {
        jQuery.fn.chosen = function (action) {
            this.each(function () {
                var el = this;
                if (el.tagName !== 'SELECT') { return; }

                // Destroy existing TomSelect instance if any
                if (el._tomSelect) {
                    try { el._tomSelect.destroy(); } catch (e) { /* noop */ }
                    el._tomSelect = null;
                    el.removeAttribute('data-ts-init');
                }

                // If action is 'destroy', just remove the instance
                if (action === 'destroy') { return; }

                // Initialize TomSelect
                if (typeof TomSelect !== 'undefined') {
                    try {
                        var isMultiple = el.hasAttribute('multiple');
                        el._tomSelect = new TomSelect(el, {
                            plugins: isMultiple ? ['remove_button', 'clear_button'] : [],
                            allowEmptyOption: true,
                            controlInput: (el.options && el.options.length > 8) ? undefined : null,
                        });
                    } catch (e) {
                        // TomSelect may throw on hidden/detached elements — safe to ignore
                    }
                }
            });
            return this;
        };

        // Shim for liszt:updated / chosen:updated — TomSelect syncs from <select>
        // natively when you call tomSelect.sync(), but most call sites just trigger
        // the event to force a refresh. We map the event to tomSelect.sync().
        jQuery(document).on('liszt:updated chosen:updated', 'select', function () {
            var el = this;
            if (el._tomSelect) {
                try { el._tomSelect.sync(); } catch (e) { /* noop */ }
            }
        });
    }

})();
