<?php
/**
 * Timesheet Search Bar Submodule
 * Reusable search component for timesheets
 *
 * This is a separate module that can be included without modifying core files
 */
defined('RESTRICTED') or exit('Restricted access');
?>

<!-- Modern Search Component -->
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/components/modernSearch.css">

<div class="pull-right" style="margin-right: 15px;">
    <div class="modern-search-wrapper" id="timesheetSearchWrapper">
        <input
            type="text"
            id="timesheetSearch"
            class="modern-search-input"
            placeholder="Search by ticket ID, ticket label, project, employee or tag..."
            autocomplete="off"
        />
        <button type="button" id="timesheetSearchClear" class="modern-search-clear" aria-label="Clear search">
            <span class="fa fa-times"></span>
        </button>
    </div>
</div>

<script>
// Load modern search core component
if (typeof leantime.modernSearch === 'undefined') {
    var script1 = document.createElement('script');
    script1.src = '<?= BASE_URL ?>/assets/js/app/core/modernSearch.js';
    script1.onload = function() {
        // Load adapter after core is loaded
        var script2 = document.createElement('script');
        script2.src = '<?= BASE_URL ?>/assets/js/app/timesheets/timesheetSearch.js';
        script2.onload = function() {
            // Initialize search after scripts are loaded AND DataTable is ready
            // Wait for DataTable to be initialized
            var initAttempts = 0;
            var maxAttempts = 50;

            var tryInit = function() {
                initAttempts++;

                try {
                    var dt = jQuery('#allTimesheetsTable').DataTable();
                    if (dt && dt.settings && dt.settings().length > 0) {
                        leantime.timesheetSearch.init(dt);
                        return; // Success!
                    }
                } catch (e) {
                    // DataTable not ready yet
                }

                // Try again if not ready
                if (initAttempts < maxAttempts) {
                    setTimeout(tryInit, 200);
                }
            };

            // Start trying after a short delay
            setTimeout(tryInit, 500);
        };
        document.head.appendChild(script2);
    };
    document.head.appendChild(script1);
}
</script>

