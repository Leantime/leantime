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

            // Enter: Save and close
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dataset.submitting = 'true';
                stayOpenInput.value = '0';
                form.submit();
            }

            // Shift+Enter: Save and stay open
            if (e.key === 'Enter' && e.shiftKey) {
                e.preventDefault();
                form.dataset.submitting = 'true';
                stayOpenInput.value = '1';
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
     * Equalize column heights within a swimlane content area
     * Sets all columns to the max height for consistent appearance
     * @param {HTMLElement} contentElement - The swimlane content container
     */
    var equalizeColumnHeights = function(contentElement) {
        var columns = contentElement.querySelectorAll('.column .contentInner');
        if (columns.length === 0) return;

        // Reset ALL height styles first to get natural heights
        // This includes height set by setUpKanbanColumns() in ticketsController
        columns.forEach(function(col) {
            col.style.minHeight = '';
            col.style.height = '';
        });

        // Find max height from natural content
        var maxHeight = 0;
        columns.forEach(function(col) {
            var height = col.offsetHeight;
            if (height > maxHeight) {
                maxHeight = height;
            }
        });

        // Set all columns to max height using minHeight (allows growth if needed)
        if (maxHeight > 0) {
            columns.forEach(function(col) {
                col.style.minHeight = maxHeight + 'px';
            });
        }
    };

    /**
     * Reset column heights to natural height
     * @param {HTMLElement} contentElement - The swimlane content container
     */
    var resetColumnHeights = function(contentElement) {
        var columns = contentElement.querySelectorAll('.column .contentInner');
        columns.forEach(function(col) {
            col.style.minHeight = '';
            col.style.height = '';  // Clear height set by setUpKanbanColumns()
        });
    };

    /**
     * Initialize column heights for all expanded swimlanes on page load
     * Uses requestAnimationFrame to ensure DOM is fully rendered before measuring
     */
    var initExpandedColumnHeights = function() {
        // Wait for next frame to ensure DOM is painted
        requestAnimationFrame(function() {
            // Double RAF for extra safety (after layout + paint)
            requestAnimationFrame(function() {
                var expandedContents = document.querySelectorAll('.kanban-swimlane-content:not(.collapsed)');
                expandedContents.forEach(function(content) {
                    equalizeColumnHeights(content);
                });
            });
        });
    };

    /**
     * Toggle swimlane collapse/expand
     * Two states only:
     * - Expanded: full ticket cards
     * - Collapsed: compact ticket cards
     * @param {string} swimlaneId - Swimlane identifier
     */
    var toggleSwimlane = function(swimlaneId) {
        var row = document.getElementById('swimlane-row-' + swimlaneId);
        var sidebar = document.querySelector('.kanban-swimlane-sidebar[data-swimlane-id="' + swimlaneId + '"]');
        var content = document.getElementById('swimlane-content-' + swimlaneId);

        if (!row || !sidebar || !content) {
            console.error('Swimlane elements not found for ID:', swimlaneId);
            return;
        }

        var isExpanded = sidebar.getAttribute('aria-expanded') === 'true';
        var newExpanded = !isExpanded;

        // Update aria-expanded on sidebar
        sidebar.setAttribute('aria-expanded', newExpanded.toString());

        // Update chevron icon
        var chevronIcon = sidebar.querySelector('.kanban-lane-chevron i');
        if (chevronIcon) {
            chevronIcon.className = newExpanded ? 'fa fa-chevron-down' : 'fa fa-chevron-right';
        }

        // Update data-expanded attribute on row (CSS uses this for styling)
        row.setAttribute('data-expanded', newExpanded.toString());

        // Toggle state on content area
        if (newExpanded) {
            content.classList.remove('collapsed');
            // Equalize column heights so empty columns match tallest
            equalizeColumnHeights(content);
        } else {
            content.classList.add('collapsed');
            // Equalize column heights when collapsed - maintain visual alignment
            equalizeColumnHeights(content);
        }

        // Persist state to session via AJAX
        fetch(leantime.appUrl + '/api/submenu', {
            method: 'POST',
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                submenu: 'swimlane_' + swimlaneId,
                state: newExpanded ? 'open' : 'closed'
            })
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
     * Initialize Tippy.js tooltips for swimlane header elements
     */
    var initProgressBarTooltips = function() {
        if (typeof tippy === 'undefined') {
            return;
        }

        var tippyConfig = {
            allowHTML: true,
            placement: 'top',
            arrow: true,
            theme: 'light-border',
            trigger: 'mouseenter focus',
            delay: [500, 0] // 500ms show delay, 0ms hide delay
        };

        // All swimlane header elements with tooltips
        var tooltipSelectors = [
            '.status-segment[data-tippy-content]',      // Progress bar segments
            '.time-indicator[data-tippy-content]',      // Time indicators (overdue, due soon, stale)
            '.kanban-lane-count[data-tippy-content]',   // Count badges
            '.tshirt-icon[data-tippy-content]',         // Effort icons
            '.thermometer-icon[data-tippy-content]',    // Priority icons
            '.user-avatar[data-tippy-content]',         // User avatars
            '.type-icon[data-tippy-content]',           // Type icons
            '.sprint-icon[data-tippy-content]',         // Sprint icons
            '.milestone-icon[data-tippy-content]',      // Milestone icons
            '.swimlane-header-label[data-tippy-content]' // Swimlane labels
        ];

        tooltipSelectors.forEach(function(selector) {
            var elements = document.querySelectorAll(selector);
            if (elements.length > 0) {
                tippy(elements, tippyConfig);
            }
        });
    };

    /**
     * Initialize tap-to-reveal column counts on mobile/touch devices
     * Toggles .count-visible class on .widgettitle elements
     */
    var initMobileColumnCountToggle = function() {
        // Only on touch devices
        if (!('ontouchstart' in window)) return;

        document.querySelectorAll('.widgettitle').forEach(function(header) {
            header.addEventListener('click', function(e) {
                // Don't interfere with dropdown clicks
                if (e.target.closest('.dropdown-toggle, .dropdown-menu')) return;

                // Toggle visibility
                this.classList.toggle('count-visible');
            });
        });
    };

    /**
     * Initialize column heights for all collapsed swimlanes on page load
     * Equalize heights so columns visually align even when collapsed
     */
    var initCollapsedColumnHeights = function() {
        var collapsedContents = document.querySelectorAll('.kanban-swimlane-content.collapsed');
        collapsedContents.forEach(function(content) {
            equalizeColumnHeights(content);
        });
    };

    /**
     * Initialize sticky swimlane sidebars using scroll listener and transform
     * Uses transform instead of position:fixed to avoid layout shifts
     */
    var initStickySwimlaneSidebars = function() {
        var rows = document.querySelectorAll('.kanban-swimlane-row');
        if (rows.length === 0) return;

        // Skip on mobile (vertical layout doesn't need sticky)
        if (window.innerWidth <= 768) return;

        var STICKY_TOP = 120; // Distance from viewport top when sticky

        var updateStickyPositions = function() {
            rows.forEach(function(row) {
                var sidebar = row.querySelector('.kanban-swimlane-sidebar');
                var sidebarInner = row.querySelector('.kanban-swimlane-sidebar-inner');
                var sentinel = row.querySelector('.kanban-swimlane-sentinel');
                if (!sidebar || !sidebarInner || !sentinel) return;

                var sentinelRect = sentinel.getBoundingClientRect();
                var sidebarRect = sidebar.getBoundingClientRect();

                // Calculate if sidebar content should be sticky
                var shouldBeSticky = sentinelRect.top < STICKY_TOP && sidebarRect.bottom > (STICKY_TOP + 100);

                if (shouldBeSticky) {
                    // Calculate how much to translate the inner content
                    var translateY = STICKY_TOP - sidebarRect.top;

                    // Don't translate beyond the sidebar bottom
                    var maxTranslate = sidebarRect.height - sidebarInner.offsetHeight - 12;
                    translateY = Math.min(translateY, Math.max(0, maxTranslate));

                    sidebar.classList.add('is-sticky');
                    sidebarInner.style.transform = 'translateY(' + translateY + 'px)';
                } else {
                    sidebar.classList.remove('is-sticky');
                    sidebarInner.style.transform = '';
                }
            });
        };

        // Update on scroll
        window.addEventListener('scroll', updateStickyPositions, { passive: true });

        // Initial check
        updateStickyPositions();
    };

    // Initialize on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initQuickAddKeyboard();
            initClickOutsideSave();
            initSwimlaneKeyboard();
            initProgressBarTooltips();
            initStickySwimlaneSidebars();
            initMobileColumnCountToggle();
            initCollapsedColumnHeights();
            initExpandedColumnHeights();
        });
    } else {
        initQuickAddKeyboard();
        initClickOutsideSave();
        initSwimlaneKeyboard();
        initProgressBarTooltips();
        initStickySwimlaneSidebars();
        initMobileColumnCountToggle();
        initCollapsedColumnHeights();
        initExpandedColumnHeights();
    }

    // Make public what you want to have public, everything else is private
    return {
        toggleQuickAdd: toggleQuickAdd,
        initQuickAddKeyboard: initQuickAddKeyboard,
        initClickOutsideSave: initClickOutsideSave,
        toggleSwimlane: toggleSwimlane,
        initSwimlaneKeyboard: initSwimlaneKeyboard,
        initProgressBarTooltips: initProgressBarTooltips,
        equalizeColumnHeights: equalizeColumnHeights,
        resetColumnHeights: resetColumnHeights,
        initCollapsedColumnHeights: initCollapsedColumnHeights,
        initExpandedColumnHeights: initExpandedColumnHeights,
        initStickySwimlaneSidebars: initStickySwimlaneSidebars,
        initMobileColumnCountToggle: initMobileColumnCountToggle
    };

})();
