leantime.kanbanController = (function () {

    /**
     * Toggle quick-add form visibility
     * @param {HTMLElement} triggerElement - The link element that triggered the toggle
     */
    var toggleQuickAdd = function(triggerElement) {
        var container = triggerElement.closest('.quickaddContainer');
        var form = container.querySelector('[data-quickadd-form]');
        var input = form.querySelector('[data-quickadd-input]');
        var link = container.querySelector('.quickAddLink');

        var isVisible = form.classList.contains('active');

        if (isVisible) {
            // Hide form, show link
            form.classList.remove('active');
            form.style.display = 'none';
            form.dataset.submitting = 'false';
            link.style.display = '';
            link.setAttribute('aria-expanded', 'false');
        } else {
            // Show form, hide link
            form.classList.add('active');
            form.style.display = 'block';
            form.dataset.submitting = 'false';
            link.style.display = 'none';
            link.setAttribute('aria-expanded', 'true');
            input.focus();
            // Initialize tooltips for this form if not already done
            initQuickAddHelp();
        }
    };

    /**
     * Initialize keyboard interactions for quick-add forms
     * - Enter: Save and stay open
     * - Shift+Enter: Save and close
     * - Escape: Cancel
     */
    var initQuickAddKeyboard = function() {
        document.addEventListener('keydown', function(e) {
            var input = e.target;
            if (!input.matches('[data-quickadd-input]')) return;

            var form = input.closest('[data-quickadd-form]');

            // Prevent multiple submissions
            if (form.dataset.submitting === 'true') {
                e.preventDefault();
                return;
            }

            var stayOpenInput = form.querySelector('[data-stay-open-input]');

            // Enter: Save and stay open
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dataset.submitting = 'true';
                stayOpenInput.value = '1';
                form.submit();
            }

            // Shift+Enter: Save and close
            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                form.dataset.submitting = 'true';
                stayOpenInput.value = '0';
                form.submit();
            }

            // Escape: Cancel
            if (e.key === 'Escape') {
                e.preventDefault();
                var container = form.closest('.quickaddContainer');
                var link = container.querySelector('.quickAddLink');
                toggleQuickAdd(link);
                input.value = '';
            }
        });
    };

    /**
     * Initialize click outside behavior
     * - If form has text: save and close
     * - If form is empty: just close
     */
    var initClickOutsideSave = function() {
        document.addEventListener('click', function(e) {
            var forms = document.querySelectorAll('[data-quickadd-form].active');

            forms.forEach(function(form) {
                var container = form.closest('.quickaddContainer');
                var link = container.querySelector('.quickAddLink');
                var input = form.querySelector('[data-quickadd-input]');
                var isClickInside = form.contains(e.target) || link.contains(e.target);

                // Prevent action if already submitting
                if (form.dataset.submitting === 'true') {
                    return;
                }

                if (!isClickInside && input.value.trim() !== '') {
                    // Save if there's text
                    var stayOpenInput = form.querySelector('[data-stay-open-input]');
                    stayOpenInput.value = '0';
                    form.dataset.submitting = 'true';
                    form.submit();
                } else if (!isClickInside) {
                    // Just close if empty
                    toggleQuickAdd(link);
                }
            });
        });
    };

    /**
     * Initialize help tooltips for quick-add forms
     */
    var initQuickAddHelp = function() {
        var helpIcons = document.querySelectorAll('[data-quickadd-help]');

        if (typeof tippy === 'undefined') {
            return;
        }

        helpIcons.forEach(function(icon) {
            tippy(icon, {
                content: icon.getAttribute('data-tippy-content'),
                allowHTML: true,
                placement: 'left',
                arrow: true,
                theme: 'light-border',
                interactive: false,
                role: 'tooltip',
                trigger: 'mouseenter focus'
            });
        });
    };

    /**
     * Toggle swimlane collapse/expand
     * @param {string} swimlaneId - Swimlane identifier
     */
    var toggleSwimlane = function(swimlaneId) {
        var sidebar = document.querySelector('.kanban-swimlane-sidebar[data-swimlane-id="' + swimlaneId + '"]');
        var content = document.getElementById('swimlane-content-' + swimlaneId);
        var collapsed = document.getElementById('swimlane-collapsed-' + swimlaneId);
        var toggleBtn = sidebar ? sidebar.querySelector('.accordion-toggle-swimlane') : null;

        if (!sidebar || !content) {
            console.error('Swimlane sidebar or content not found for ID:', swimlaneId);
            return;
        }

        var isExpanded = toggleBtn ? toggleBtn.getAttribute('aria-expanded') === 'true' : false;
        var newExpanded = !isExpanded;

        // Update aria-expanded attribute
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-expanded', newExpanded.toString());
        }

        // Update chevron icon
        var chevronIcon = sidebar.querySelector('.kanban-lane-chevron i');
        if (chevronIcon) {
            chevronIcon.className = newExpanded ? 'fa fa-chevron-up' : 'fa fa-chevron-right';
        }

        // Toggle between expanded content and collapsed compact view
        if (newExpanded) {
            // Show full kanban columns, hide compact view
            content.style.display = 'block';
            if (collapsed) {
                collapsed.style.display = 'none';
            }
        } else {
            // Hide full kanban columns, show compact view
            content.style.display = 'none';
            if (collapsed) {
                collapsed.style.display = 'flex';
            }
        }

        // Persist state to session via AJAX
        jQuery.ajax({
            url: leantime.appUrl + '/api/submenu',
            type: 'POST',
            data: {
                submenu: 'swimlane_' + swimlaneId,
                state: newExpanded ? 'open' : 'closed'
            }
        });
    };

    /**
     * Initialize keyboard support for swimlane headers
     * - Enter/Space: Toggle swimlane
     * - Arrow keys: Navigate between swimlanes
     */
    var initSwimlaneKeyboard = function() {
        document.addEventListener('keydown', function(e) {
            var header = e.target.closest('[data-swimlane-id]');
            if (!header) return;

            var swimlaneId = header.getAttribute('data-swimlane-id');

            // Enter or Space to toggle
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleSwimlane(swimlaneId);
            }

            // Arrow Up/Down to navigate between swimlanes
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault();
                var allHeaders = Array.from(document.querySelectorAll('[data-swimlane-id]'));
                var currentIndex = allHeaders.indexOf(header);

                if (e.key === 'ArrowDown' && currentIndex < allHeaders.length - 1) {
                    allHeaders[currentIndex + 1].focus();
                } else if (e.key === 'ArrowUp' && currentIndex > 0) {
                    allHeaders[currentIndex - 1].focus();
                }
            }
        });
    };

    /**
     * Initialize Tippy.js tooltips for progress bar segments
     */
    var initProgressBarTooltips = function() {
        if (typeof tippy === 'undefined') {
            return;
        }

        var segments = document.querySelectorAll('.status-segment[data-tippy-content]');
        if (segments.length > 0) {
            tippy(segments, {
                allowHTML: true,
                placement: 'top',
                arrow: true,
                theme: 'light-border',
                trigger: 'mouseenter focus'
            });
        }
    };

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initQuickAddKeyboard();
            initClickOutsideSave();
            initQuickAddHelp();
            initSwimlaneKeyboard();
            initProgressBarTooltips();
        });
    } else {
        initQuickAddKeyboard();
        initClickOutsideSave();
        initQuickAddHelp();
        initSwimlaneKeyboard();
        initProgressBarTooltips();
    }

    // Make public what you want to have public, everything else is private
    return {
        toggleQuickAdd: toggleQuickAdd,
        initQuickAddKeyboard: initQuickAddKeyboard,
        initClickOutsideSave: initClickOutsideSave,
        initQuickAddHelp: initQuickAddHelp,
        toggleSwimlane: toggleSwimlane,
        initSwimlaneKeyboard: initSwimlaneKeyboard,
        initProgressBarTooltips: initProgressBarTooltips
    };

})();
