/**
 * Accessibility Controller
 * Enhances custom JavaScript input components with proper ARIA attributes and keyboard support
 *
 * @author Leantime Team
 * @copyright 2024 Leantime
 */

leantime.accessibilityController = (function () {

    /**
     * Enhance SlimSelect dropdowns with ARIA attributes and keyboard support
     */
    var enhanceSlimSelectAccessibility = function() {
        var ssMainElements = document.querySelectorAll('.ss-main');

        ssMainElements.forEach(function(ssMain) {
            // Skip if already enhanced
            if (ssMain.dataset.a11yEnhanced) {
                return;
            }

            var originalSelect = ssMain.previousElementSibling;

            // Verify we found a select element
            if (!originalSelect || originalSelect.tagName !== 'SELECT') {
                return;
            }

            // Get label text
            var labelText = '';
            var selectId = originalSelect.getAttribute('id');
            if (selectId) {
                var label = document.querySelector('label[for="' + selectId + '"]');
                if (label) {
                    labelText = label.textContent.trim();
                }
            }

            var isMultiple = originalSelect.hasAttribute('multiple');

            // Set ARIA attributes on the main container
            ssMain.setAttribute('role', 'combobox');
            ssMain.setAttribute('aria-haspopup', 'listbox');
            ssMain.setAttribute('aria-expanded', 'false');
            ssMain.setAttribute('aria-label',
                labelText || originalSelect.getAttribute('data-placeholder') || (isMultiple ? 'Select options' : 'Select option')
            );

            if (isMultiple) {
                ssMain.setAttribute('aria-multiselectable', 'true');
            }

            // Set role on the dropdown list
            var ssList = ssMain.parentElement
                ? ssMain.parentElement.querySelector('.ss-content .ss-list')
                : null;
            if (ssList) {
                ssList.setAttribute('role', 'listbox');
            }

            // Observe class changes to detect open/close and update aria-expanded
            var observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        var contentEl = ssMain.parentElement
                            ? ssMain.parentElement.querySelector('.ss-content')
                            : null;
                        if (contentEl) {
                            var isOpen = contentEl.classList.contains('ss-open');
                            ssMain.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                        }
                    }
                });
            });

            var contentEl = ssMain.parentElement
                ? ssMain.parentElement.querySelector('.ss-content')
                : null;
            if (contentEl) {
                observer.observe(contentEl, { attributes: true, attributeFilter: ['class'] });
            }

            // Mark as enhanced
            ssMain.dataset.a11yEnhanced = 'true';
        });
    };

    /**
     * Enhance TagsInput with ARIA attributes
     */
    var enhanceTagsInputAccessibility = function() {
        var tagsInputElements = document.querySelectorAll('div.tagsinput');

        tagsInputElements.forEach(function(tagsInput) {
            var originalInput = tagsInput.nextElementSibling;

            if (!originalInput || originalInput.type !== 'text') {
                return;
            }

            var inputId = originalInput.getAttribute('id');
            var labelText = 'Enter tags';

            if (inputId) {
                var label = document.querySelector('label[for="' + inputId + '"]');
                if (label) {
                    labelText = label.textContent.trim();
                }
            }

            tagsInput.setAttribute('role', 'list');
            tagsInput.setAttribute('aria-label', labelText);

            // Set role on individual tags
            var tags = tagsInput.querySelectorAll('span.tag');
            tags.forEach(function(tag) {
                tag.setAttribute('role', 'listitem');
            });

            // Make tag input accessible
            var innerInput = tagsInput.querySelector('input');
            if (innerInput) {
                innerInput.setAttribute('aria-label', 'Add new tag');
                if (inputId) {
                    innerInput.setAttribute('aria-describedby', inputId + '-help');
                }
            }

            // Add help text if it does not exist
            if (inputId && !document.getElementById(inputId + '-help')) {
                var helpSpan = document.createElement('span');
                helpSpan.id = inputId + '-help';
                helpSpan.className = 'sr-only';
                helpSpan.textContent = 'Type and press enter to add tags. Press backspace to remove the last tag.';
                tagsInput.parentNode.insertBefore(helpSpan, tagsInput.nextSibling);
            }
        });
    };

    /**
     * Enhance Datepickers with ARIA attributes
     */
    var enhanceDatepickerAccessibility = function() {
        var datepickerInputs = document.querySelectorAll('input.hasDatepicker');

        datepickerInputs.forEach(function(input) {
            var inputId = input.getAttribute('id');
            var labelText = '';

            if (inputId) {
                var label = document.querySelector('label[for="' + inputId + '"]');
                if (label) {
                    labelText = label.textContent.trim();
                }
            }

            input.setAttribute('role', 'textbox');
            input.setAttribute('aria-label', labelText || 'Select date');

            if (inputId) {
                input.setAttribute('aria-describedby', inputId + '-help');

                // Add help text if it does not exist
                if (!document.getElementById(inputId + '-help')) {
                    var helpSpan = document.createElement('span');
                    helpSpan.id = inputId + '-help';
                    helpSpan.className = 'sr-only';
                    helpSpan.textContent = 'Date input. Use arrow keys to navigate calendar. Press enter to select date.';
                    input.parentNode.insertBefore(helpSpan, input.nextSibling);
                }
            }
        });

        // Enhance datepicker widget when it opens (delegated event)
        if (!document._a11yDatepickerBound) {
            document.addEventListener('focusin', function(e) {
                if (e.target && e.target.matches && e.target.matches('input.hasDatepicker')) {
                    setTimeout(function() {
                        var widget = document.getElementById('ui-datepicker-div');
                        if (widget && widget.style.display !== 'none') {
                            widget.setAttribute('role', 'dialog');
                            widget.setAttribute('aria-label', 'Choose date');
                            widget.setAttribute('aria-modal', 'true');
                        }
                    }, 100);
                }
            });
            document._a11yDatepickerBound = true;
        }
    };

    /**
     * Enhance TinyMCE editors with ARIA attributes
     */
    var enhanceTinyMCEAccessibility = function() {
        // Wait for TinyMCE to initialize
        if (typeof tinymce !== 'undefined') {
            tinymce.on('AddEditor', function(e) {
                var editor = e.editor;
                var labelText = 'Rich text editor';
                var label = document.querySelector('label[for="' + editor.id + '"]');
                if (label) {
                    labelText = label.textContent.trim();
                }

                editor.on('init', function() {
                    // Set ARIA label on editor iframe
                    var iframe = document.getElementById(editor.id + '_ifr');
                    if (iframe) {
                        iframe.setAttribute('aria-label', labelText);

                        // Add help text if it does not exist
                        if (!document.getElementById(editor.id + '-help')) {
                            var helpSpan = document.createElement('span');
                            helpSpan.id = editor.id + '-help';
                            helpSpan.className = 'sr-only';
                            helpSpan.textContent = 'Rich text editor. Press ALT+F10 to access toolbar. Press ESC to return to editing area.';
                            iframe.parentNode.insertBefore(helpSpan, iframe.nextSibling);
                        }
                    }
                });
            });
        }
    };

    /**
     * Fix time picker label associations
     */
    var fixTimepickerLabels = function() {
        var timeInputs = document.querySelectorAll('input[type="time"]');

        timeInputs.forEach(function(timeInput) {
            var id = timeInput.getAttribute('id');

            if (!id) {
                return;
            }

            // If no label exists, create ARIA label from context
            if (!document.querySelector('label[for="' + id + '"]')) {
                var labelText = 'Time';

                // Try to infer from nearby elements
                var formGroup = timeInput.closest('.form-group');
                if (formGroup) {
                    var prevLabel = formGroup.querySelector('label');
                    if (prevLabel) {
                        labelText = prevLabel.textContent.trim() + ' time';
                    }
                }

                timeInput.setAttribute('aria-label', labelText);
            }
        });
    };

    /**
     * Make kanban cards keyboard accessible
     */
    var enhanceKanbanCardAccessibility = function() {
        var cards = document.querySelectorAll('.ticketBox');

        cards.forEach(function(card) {
            if (!card.getAttribute('tabindex')) {
                card.setAttribute('tabindex', '0');
            }

            var headlineEl = card.querySelector('.ticketHeadline');
            var headline = headlineEl ? headlineEl.textContent.trim() : '';
            if (headline) {
                card.setAttribute('role', 'article');
                card.setAttribute('aria-label', 'Task: ' + headline);
            }

            // Make card clickable with keyboard (only if not already bound)
            if (!card.dataset.keyboardBound) {
                card.addEventListener('keydown', function(e) {
                    // Only handle Enter/Space if the card itself has focus
                    // Do not intercept events from interactive children (dropdowns, buttons, links, inputs)
                    var target = e.target;

                    // Check if the target is an interactive element
                    var isInteractive = target.matches('a, button, input, select, textarea, [role="button"], [tabindex]') ||
                                        target.closest('.dropdown-toggle, .ticketDropdown, .inlineDropDownContainer');

                    // Only handle the event if the card itself was focused and not an interactive child
                    if ((e.key === 'Enter' || e.key === ' ') && !isInteractive && e.target === card) {
                        e.preventDefault();
                        var link = card.querySelector('a');
                        if (link) {
                            link.click();
                        }
                    }
                });
                card.dataset.keyboardBound = 'true';
            }
        });
    };

    /**
     * Run all enhancement functions
     */
    var enhanceAll = function() {
        enhanceSlimSelectAccessibility();
        enhanceTagsInputAccessibility();
        enhanceDatepickerAccessibility();
        fixTimepickerLabels();
        enhanceKanbanCardAccessibility();
    };

    /**
     * Initialize all accessibility enhancements
     */
    var init = function() {
        // Run immediately on page load
        enhanceAll();
        enhanceTinyMCEAccessibility();

        // Re-run when new content is loaded (HTMX, modals, etc.)
        document.addEventListener('htmx:afterSwap', function() {
            setTimeout(enhanceAll, 100);
        });

        document.addEventListener('shown.bs.modal', function() {
            setTimeout(enhanceAll, 100);
        });

        // Single retry after initial page load to catch late-initializing dropdowns
        setTimeout(enhanceSlimSelectAccessibility, 1000);
    };

    // Public API
    return {
        init: init,
        enhanceSlimSelectAccessibility: enhanceSlimSelectAccessibility,
        enhanceTagsInputAccessibility: enhanceTagsInputAccessibility,
        enhanceDatepickerAccessibility: enhanceDatepickerAccessibility,
        enhanceTinyMCEAccessibility: enhanceTinyMCEAccessibility,
        fixTimepickerLabels: fixTimepickerLabels,
        enhanceKanbanCardAccessibility: enhanceKanbanCardAccessibility
    };

})();

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    leantime.accessibilityController.init();
});
