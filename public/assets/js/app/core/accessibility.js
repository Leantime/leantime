/**
 * Accessibility Controller
 * Enhances custom JavaScript input components with proper ARIA attributes and keyboard support
 *
 * @author Leantime Team
 * @copyright 2024 Leantime
 */

leantime.accessibilityController = (function () {

    /**
     * Enhance TomSelect instances with ARIA attributes.
     * TomSelect renders its own combobox markup with correct ARIA roles.
     * We supplement with labels derived from associated <label> elements.
     */
    var enhanceTomSelectAccessibility = function() {
        document.querySelectorAll('.ts-wrapper').forEach(function(wrapper) {
            if (wrapper.dataset.a11yEnhanced) { return; }

            // Find the hidden <select> or <input> that TomSelect replaced
            var originalEl = wrapper.querySelector('select, input.tag-input');
            if (!originalEl) { return; }

            var labelText = '';
            var elId = originalEl.id;
            if (elId) {
                var labelEl = document.querySelector('label[for="' + elId + '"]');
                if (labelEl) { labelText = labelEl.textContent.trim(); }
            }

            // TomSelect's control div already has role="combobox" — just ensure aria-label
            var control = wrapper.querySelector('.ts-control');
            if (control && labelText) {
                control.setAttribute('aria-label', labelText);
            }

            wrapper.dataset.a11yEnhanced = 'true';
        });
    };

    // Kept as aliases for backward compatibility with any external callers
    var enhanceChosenAccessibility = enhanceTomSelectAccessibility;
    var enhanceSlimSelectAccessibility = function() { /* no-op — SlimSelect removed */ };
    var enhanceTagsInputAccessibility = enhanceTomSelectAccessibility;

    /**
     * Enhance Datepickers with ARIA attributes
     */
    var enhanceDatepickerAccessibility = function() {
        jQuery('input.hasDatepicker').each(function() {
            var $input = jQuery(this);
            var inputId = $input.attr('id');
            var $label = jQuery('label[for="' + inputId + '"]');
            var labelText = $label.length ? $label.text().trim() : '';

            $input.attr({
                'role': 'textbox',
                'aria-label': labelText || 'Select date',
                'aria-describedby': inputId + '-help'
            });

            // Add help text if doesn't exist
            if (inputId && !jQuery('#' + inputId + '-help').length) {
                $input.after(
                    '<span id="' + inputId + '-help" class="sr-only">' +
                    'Date input. Use arrow keys to navigate calendar. Press enter to select date.' +
                    '</span>'
                );
            }
        });

        // Enhance datepicker widget when it opens
        jQuery(document).on('focus', 'input.hasDatepicker', function() {
            setTimeout(function() {
                var $widget = jQuery('#ui-datepicker-div');
                if ($widget.is(':visible')) {
                    $widget.attr({
                        'role': 'dialog',
                        'aria-label': 'Choose date',
                        'aria-modal': 'true'
                    });
                }
            }, 100);
        });
    };

    /**
     * Fix time picker label associations
     */
    var fixTimepickerLabels = function() {
        jQuery('input[type="time"]').each(function() {
            var $timeInput = jQuery(this);
            var id = $timeInput.attr('id');

            if (!id) {
                return;
            }

            // If no label exists, create ARIA label from context
            if (!jQuery('label[for="' + id + '"]').length) {
                var labelText = 'Time';

                // Try to infer from nearby elements
                var $prevLabel = $timeInput.closest('.form-group').find('label').first();
                if ($prevLabel.length) {
                    labelText = $prevLabel.text().trim() + ' time';
                }

                $timeInput.attr('aria-label', labelText);
            }
        });
    };

    /**
     * Make kanban cards keyboard accessible
     */
    var enhanceKanbanCardAccessibility = function() {
        jQuery('.ticketBox').each(function() {
            var $card = jQuery(this);

            if (!$card.attr('tabindex')) {
                $card.attr('tabindex', '0');
            }

            var headline = $card.find('.ticketHeadline').text().trim();
            if (headline) {
                $card.attr({
                    'role': 'article',
                    'aria-label': 'Task: ' + headline
                });
            }

            // Make card clickable with keyboard (only if not already bound)
            if (!$card.data('keyboard-bound')) {
                $card.on('keydown', function(e) {
                    // Only handle Enter/Space if the card itself has focus
                    // Don't intercept events from interactive children (dropdowns, buttons, links, inputs)
                    var $target = jQuery(e.target);

                    // Check if the target is an interactive element
                    var isInteractive = $target.is('a, button, input, select, textarea, [role="button"], [tabindex]') ||
                                       $target.closest('.dropdown-toggle, .ticketDropdown, .inlineDropDownContainer').length > 0;

                    // Only handle the event if the card itself was focused and not an interactive child
                    if ((e.key === 'Enter' || e.key === ' ') && !isInteractive && e.target === this) {
                        e.preventDefault();
                        var $link = $card.find('a').first();
                        if ($link.length) {
                            $link[0].click();
                        }
                    }
                });
                $card.data('keyboard-bound', true);
            }
        });
    };

    /**
     * Initialize all accessibility enhancements
     */
    var init = function() {
        // Run immediately on page load
        enhanceTomSelectAccessibility();
        enhanceDatepickerAccessibility();
        fixTimepickerLabels();
        enhanceKanbanCardAccessibility();

        // Re-run when new content is loaded (HTMX, modals, etc.)
        jQuery(document).on('htmx:afterSwap shown.bs.modal', function() {
            setTimeout(function() {
                enhanceTomSelectAccessibility();
                enhanceDatepickerAccessibility();
                fixTimepickerLabels();
                enhanceKanbanCardAccessibility();
            }, 100);
        });

        // Retry after initial page load to catch late-initializing TomSelect instances
        setTimeout(enhanceTomSelectAccessibility, 500);
    };

    // Public API
    return {
        init: init,
        enhanceTomSelectAccessibility: enhanceTomSelectAccessibility,
        // Backward-compat aliases
        enhanceChosenAccessibility: enhanceChosenAccessibility,
        enhanceSlimSelectAccessibility: enhanceSlimSelectAccessibility,
        enhanceTagsInputAccessibility: enhanceTagsInputAccessibility,
        enhanceDatepickerAccessibility: enhanceDatepickerAccessibility,
        fixTimepickerLabels: fixTimepickerLabels,
        enhanceKanbanCardAccessibility: enhanceKanbanCardAccessibility
    };

})();

// Initialize on page load
jQuery(document).ready(function() {
    leantime.accessibilityController.init();
});
