/**
 * DataTables Column State Persistence
 * 
 * Lightweight addon that hooks into DataTables column visibility events
 * and automatically saves/restores state to/from backend.
 * 
 * No UI changes - works with existing DataTables Buttons extension
 * 
 * @author Leantime Team  
 * @version 1.0.0
 */

(function() {
    'use strict';

    // Configuration
    const SAVE_ENDPOINT = '/timesheets/saveColumnPreferences';
    const DEBOUNCE_MS = 500;
    
    let saveTimeout = null;

    /**
     * Save column state to backend
     */
    function saveColumnState(tableId, state) {
        clearTimeout(saveTimeout);
        
        saveTimeout = setTimeout(async () => {
            try {
                const response = await fetch(leantime.appUrl + SAVE_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        tableId: tableId,
                        columnState: state
                    })
                });

                if (!response.ok) {
                    console.warn('Failed to save column state:', response.status);
                }
            } catch (error) {
                console.warn('Failed to save column state:', error);
            }
        }, DEBOUNCE_MS);
    }

    /**
     * Load column state from backend
     */
    async function loadColumnState(tableId) {
        try {
            const response = await fetch(`${leantime.appUrl}${SAVE_ENDPOINT}?tableId=${tableId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            if (response.ok) {
                const data = await response.json();
                return data.columnState || null;
            }
        } catch (error) {
            console.warn('Failed to load column state:', error);
        }
        
        return null;
    }

    /**
     * Apply column state to DataTable
     */
    function applyColumnState(table, state) {
        if (!state || typeof state !== 'object') return;

        table.columns().every(function(index) {
            const column = this;
            const columnName = jQuery(column.header()).data('column-name');
            
            if (columnName && state.hasOwnProperty(columnName)) {
                column.visible(state[columnName]);
            }
        });
    }

    /**
     * Get current column state from DataTable
     */
    function getColumnState(table) {
        const state = {};
        
        table.columns().every(function(index) {
            const column = this;
            const columnName = jQuery(column.header()).data('column-name');
            
            if (columnName) {
                state[columnName] = column.visible();
            }
        });
        
        return state;
    }

    /**
     * Hook into DataTable initialization
     */
    function hookDataTable(tableId) {
        const selector = '#' + tableId;
        
        // Wait for DataTable to be initialized
        const checkInterval = setInterval(() => {
            if (jQuery.fn.dataTable.isDataTable(selector)) {
                clearInterval(checkInterval);
                
                const table = jQuery(selector).DataTable();
                
                // Load and apply saved state
                loadColumnState(tableId).then(savedState => {
                    if (savedState) {
                        applyColumnState(table, savedState);
                    }
                });

                // Listen for column visibility changes
                table.on('column-visibility.dt', function (e, settings, column, state) {
                    const currentState = getColumnState(table);
                    saveColumnState(tableId, currentState);
                });
            }
        }, 100);

        // Cleanup after 10 seconds if DataTable not found
        setTimeout(() => clearInterval(checkInterval), 10000);
    }

    // Auto-initialize for known tables
    jQuery(document).ready(function() {
        // Initialize for All Timesheets table
        if (jQuery('#allTimesheetsTable').length) {
            hookDataTable('allTimesheetsTable');
        }
    });

    // Export for manual initialization if needed
    window.leantimeDataTablesColumnState = {
        hook: hookDataTable,
        save: saveColumnState,
        load: loadColumnState
    };

})();

