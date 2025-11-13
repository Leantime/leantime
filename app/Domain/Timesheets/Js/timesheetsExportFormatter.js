/**
 * Timesheets export helper
 *
 * Provides a single place to resolve CSV export values while
 * keeping the original markup untouched (aside from data attributes).
 */
(function () {
    window.leantime = window.leantime || {};
    window.leantime.timesheetsExport = window.leantime.timesheetsExport || {};

    /**
     * Resolve the value that should be exported for a cell.
     *
     * @param {jQuery} $node
     * @param {string} fallbackData
     * @returns {string}
     */
    window.leantime.timesheetsExport.resolveCell = function ($node, fallbackData) {
        if (typeof $node.data('order') === 'undefined') {
            return fallbackData;
        }

        if (! $node.hasClass('js-timesheet-hours')) {
            return $node.data('order');
        }

        var tableFormat = ($node.closest('table[data-hours-format]').data('hours-format') || '').toString();

        if (tableFormat === 'human') {
            if (typeof $node.data('export-display') !== 'undefined') {
                return $node.data('export-display');
            }

            return $node.text().trim();
        }

        return $node.data('order');
    };
})();

