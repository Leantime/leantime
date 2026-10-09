/**
 * Accessibility Controller
 * Enhances custom JavaScript input components with proper ARIA attributes and keyboard support.
 * Enhanced selects get their ARIA from SlimSelect itself (see core/selects.js).
 *
 * @author Leantime Team
 * @copyright 2024 Leantime
 */

leantime.accessibilityController = (function () {

    /**
     * Enhance TagsInput with ARIA attributes
     */
    var enhanceTagsInputAccessibility = function() {
        jQuery('div.tagsinput').each(function() {
            var $tagsInput = jQuery(this);
            var $originalInput = $tagsInput.next('input[type="text"]');

            if (!$originalInput.length) {
                return;
            }

            var inputId = $originalInput.attr('id');
            var $label = jQuery('label[for="' + inputId + '"]');
            var labelText = $label.length ? $label.text().trim() : 'Enter tags';

            $tagsInput.attr({
                'role': 'list',
                'aria-label': labelText
            });

            // Set role on individual tags
            $tagsInput.find('span.tag').each(function() {
                jQuery(this).attr('role', 'listitem');
            });

            // Make tag input accessible
            var $input = $tagsInput.find('input');
            $input.attr({
                'aria-label': 'Add new tag',
                'aria-describedby': inputId + '-help'
            });

            // Add help text if doesn't exist
            if (inputId && !jQuery('#' + inputId + '-help').length) {
                $tagsInput.after(
                    '<span id="' + inputId + '-help" class="sr-only">' +
                    'Type and press enter to add tags. Press backspace to remove the last tag.' +
                    '</span>'
                );
            }
        });
    };

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
        jQuery(document).off('.ltDatepickerA11y').on('focus.ltDatepickerA11y', 'input.hasDatepicker', function() {
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
        enhanceTagsInputAccessibility();
        enhanceDatepickerAccessibility();
        fixTimepickerLabels();
        enhanceKanbanCardAccessibility();

        // Re-run when new content is loaded (HTMX, modals, etc.)
        jQuery(document).on('htmx:afterSwap shown.bs.modal', function() {
            setTimeout(function() {
                enhanceTagsInputAccessibility();
                enhanceDatepickerAccessibility();
                fixTimepickerLabels();
                enhanceKanbanCardAccessibility();
            }, 100);
        });

    };

    // Public API
    return {
        init: init,
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
