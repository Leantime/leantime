/**
 * Accessibility Controller
 * Enhances custom JavaScript input components with proper ARIA attributes and keyboard support
 *
 * @author Leantime Team
 * @copyright 2024 Leantime
 */

leantime.accessibilityController = (function () {

    /**
     * Enhance Chosen dropdowns with ARIA attributes
     */
    var enhanceChosenAccessibility = function() {
        jQuery('.chosen-container').each(function() {
            var $container = jQuery(this);

            // Skip if already enhanced
            if ($container.data('a11y-enhanced')) {
                return;
            }

            var $originalSelect = null;

            // Try to find the original select element
            var containerId = $container.attr('id');

            if (containerId && containerId.indexOf('_chosen') > -1) {
                // Standard case: container has ID like "status-select_chosen"
                var selectId = containerId.replace('_chosen', '');
                $originalSelect = jQuery('#' + selectId);
            }

            // Fallback: look for a hidden select element that precedes this container
            if (!$originalSelect || !$originalSelect.length) {
                $originalSelect = $container.prev('select[data-placeholder]');
            }

            // Second fallback: look for any hidden select near this container
            if (!$originalSelect || !$originalSelect.length) {
                $originalSelect = $container.siblings('select').first();
            }

            // If we still can't find the select, skip this container
            if (!$originalSelect || !$originalSelect.length) {
                return;
            }

            // Get label text
            var labelText = '';
            var selectId = $originalSelect.attr('id');
            if (selectId) {
                var $label = jQuery('label[for="' + selectId + '"]');
                if ($label.length) {
                    labelText = $label.text().trim();
                }
            }

            // Set ARIA attributes on Chosen container
            var $chosenSingle = $container.find('.chosen-single');
            var $chosenChoices = $container.find('.chosen-choices');

            if ($chosenSingle.length) {
                // Single select
                $chosenSingle.attr({
                    'role': 'combobox',
                    'aria-haspopup': 'listbox',
                    'aria-expanded': 'false',
                    'aria-label': labelText || $originalSelect.attr('data-placeholder') || 'Select option',
                    'tabindex': '0'
                });
            }

            if ($chosenChoices.length) {
                // Multi-select
                $chosenChoices.attr({
                    'role': 'combobox',
                    'aria-haspopup': 'listbox',
                    'aria-expanded': 'false',
                    'aria-label': labelText || $originalSelect.attr('data-placeholder') || 'Select options',
                    'aria-multiselectable': 'true',
                    'tabindex': '0'
                });
            }

            // Update aria-expanded on open/close
            $container.on('chosen:showing_dropdown', function() {
                $chosenSingle.add($chosenChoices).attr('aria-expanded', 'true');
            });

            $container.on('chosen:hiding_dropdown', function() {
                $chosenSingle.add($chosenChoices).attr('aria-expanded', 'false');
            });

            // Set role on dropdown
            $container.find('.chosen-drop').attr('role', 'listbox');
            $container.find('.chosen-results li').attr('role', 'option');

            // Mark as enhanced
            $container.data('a11y-enhanced', true);
        });
    };

    /**
     * Enhance SlimSelect with ARIA attributes
     */
    var enhanceSlimSelectAccessibility = function() {
        jQuery('.ss-main').each(function() {
            var $ssMain = jQuery(this);
            var $originalSelect = $ssMain.prev('select');

            if (!$originalSelect.length) {
                return;
            }

            var $label = jQuery('label[for="' + $originalSelect.attr('id') + '"]');
            var labelText = $label.length ? $label.text().trim() : '';

            $ssMain.attr({
                'role': 'combobox',
                'aria-haspopup': 'listbox',
                'aria-label': labelText || $originalSelect.attr('data-placeholder') || 'Select option',
                'aria-multiselectable': $originalSelect.attr('multiple') ? 'true' : 'false'
            });
        });
    };

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
        enhanceChosenAccessibility();
        enhanceSlimSelectAccessibility();
        enhanceTagsInputAccessibility();
        enhanceDatepickerAccessibility();
        fixTimepickerLabels();
        enhanceKanbanCardAccessibility();

        // Re-run when new content is loaded (HTMX, modals, etc.)
        jQuery(document).on('htmx:afterSwap shown.bs.modal', function() {
            setTimeout(function() {
                enhanceChosenAccessibility();
                enhanceSlimSelectAccessibility();
                enhanceTagsInputAccessibility();
                enhanceDatepickerAccessibility();
                fixTimepickerLabels();
                enhanceKanbanCardAccessibility();
            }, 100);
        });

        // Re-run when Chosen is re-initialized
        jQuery(document).on('chosen:ready', function() {
            // Wait a bit longer to ensure Chosen is fully ready
            setTimeout(enhanceChosenAccessibility, 100);
        });

        // Single retry after initial page load to catch late-initializing dropdowns
        setTimeout(enhanceChosenAccessibility, 1000);
    };

    // Public API
    return {
        init: init,
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
