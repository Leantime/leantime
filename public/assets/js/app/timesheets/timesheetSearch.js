/**
 * Timesheet Search Adapter
 * Uses the modern search component for timesheets filtering
 */

leantime.timesheetSearch = (function () {
    'use strict';

    let dataTableInstance = null;

    /**
     * Extract unique values from timesheets
     */
    function getUniqueValues(timesheets, field) {
        const values = new Set();
        timesheets.forEach(ts => {
            const value = ts[field];
            if (value && value.trim() !== '') {
                values.add(value.trim());
            }
        });
        return Array.from(values);
    }

    /**
     * Get timesheet data from DataTable
     */
    function getTimesheetData() {
        const timesheets = [];
        
        try {
            document.querySelectorAll('#allTimesheetsTable tbody tr').forEach(row => {
                const cells = row.querySelectorAll('td');
                
                // Extract Task ID from ticket link (e.g., #/tickets/showTicket/31)
                let taskId = '';
                const ticketLink = cells[5]?.querySelector('a');
                if (ticketLink) {
                    const href = ticketLink.getAttribute('href') || '';
                    const match = href.match(/showTicket\/(\d+)/);
                    if (match) {
                        taskId = match[1];
                    }
                }
                
                timesheets.push({
                    id: cells[0]?.textContent.trim().replace(/[^0-9]/g, '') || '',
                    taskId: taskId,
                    ticket: cells[5]?.textContent.trim() || '',
                    project: cells[6]?.textContent.trim() || '',
                    client: cells[7]?.textContent.trim() || '',
                    employee: cells[8]?.textContent.trim() || '',
                    type: cells[9]?.textContent.trim() || '',
                    tags: cells[11]?.textContent.trim() || '',
                    description: cells[12]?.textContent.trim() || ''
                });
            });
        } catch (e) {
            // Silently handle error
        }
        
        return timesheets;
    }

    /**
     * Build suggestions based on search query
     */
    function buildSuggestions(query) {
        const searchLower = query.toLowerCase();
        const suggestions = [];
        const timesheets = getTimesheetData();

        // Get unique values
        const uniqueIds = getUniqueValues(timesheets, 'id');
        const uniqueTaskIds = getUniqueValues(timesheets, 'taskId');
        const uniqueTickets = getUniqueValues(timesheets, 'ticket');
        const uniqueProjects = getUniqueValues(timesheets, 'project');
        const uniqueEmployees = getUniqueValues(timesheets, 'employee');
        
        // Collect all tags
        const allTags = new Set();
        timesheets.forEach(ts => {
            if (ts.tags) {
                ts.tags.split(',').forEach(tag => {
                    const trimmed = tag.trim();
                    if (trimmed) allTags.add(trimmed);
                });
            }
        });

        // Match Task IDs
        uniqueTaskIds.forEach(taskId => {
            if (taskId && taskId.toLowerCase().includes(searchLower)) {
                suggestions.push({
                    type: 'taskId',
                    label: 'Task #' + taskId,
                    value: taskId,
                    icon: 'fa-ticket',
                    meta: 'Task ID'
                });
            }
        });

        // Match tickets
        uniqueTickets.forEach(ticket => {
            if (ticket.toLowerCase().includes(searchLower)) {
                suggestions.push({
                    type: 'ticket',
                    label: ticket,
                    value: ticket,
                    icon: 'fa-ticket',
                    meta: 'ticket'
                });
            }
        });

        // Match projects
        uniqueProjects.forEach(project => {
            if (project.toLowerCase().includes(searchLower)) {
                suggestions.push({
                    type: 'project',
                    label: project,
                    value: project,
                    icon: 'fa-folder',
                    meta: 'project'
                });
            }
        });

        // Match employees
        uniqueEmployees.forEach(employee => {
            if (employee.toLowerCase().includes(searchLower)) {
                suggestions.push({
                    type: 'employee',
                    label: employee,
                    value: employee,
                    icon: 'fa-user',
                    meta: 'employee'
                });
            }
        });

        // Match tags
        Array.from(allTags).forEach(tag => {
            if (tag.toLowerCase().includes(searchLower)) {
                suggestions.push({
                    type: 'tag',
                    label: tag,
                    value: tag,
                    icon: 'fa-tag',
                    meta: 'tag'
                });
            }
        });

        return suggestions.slice(0, 10); // Limit to 10 suggestions
    }

    /**
     * Custom search function for DataTables
     * Searches in: Ticket (col 5), Tags (col 11), Description (col 12)
     */
    let customSearchFunction = null;
    let currentSearchQuery = '';
    let currentSearchType = 'all';

    function setupCustomSearch(dataTable) {
        // Remove existing custom search if any
        if (customSearchFunction) {
            jQuery.fn.dataTable.ext.search = jQuery.fn.dataTable.ext.search.filter(function(fn) {
                return fn !== customSearchFunction;
            });
        }

        // Create new custom search function
        customSearchFunction = function(settings, data, dataIndex) {
            // Only apply to our table
            if (settings.nTable.id !== 'allTimesheetsTable') {
                return true;
            }

            // If no search query, show all rows
            if (!currentSearchQuery || currentSearchQuery.trim() === '') {
                if (dataIndex === 0) {
                    console.log('CustomSearch: No query - showing all rows');
                }
                return true;
            }

            const query = currentSearchQuery.toLowerCase().trim();
            
            // Extract relevant columns (0-indexed)
            // Column 5: Ticket (headline) + link contains Task ID
            // Column 6: Project
            // Column 8: Employee
            // Column 11: Tags
            // Column 12: Description
            
            // Extract Task ID from ticket link in raw data
            let taskId = '';
            // data[5] contains HTML like: <a href="#/tickets/showTicket/31">ticket name</a>
            const ticketHtml = data[5] || '';
            const taskIdMatch = ticketHtml.match(/showTicket\/(\d+)/);
            if (taskIdMatch) {
                taskId = taskIdMatch[1].toLowerCase();
            }
            
            const ticket = ticketHtml.toLowerCase();
            const project = (data[6] || '').toLowerCase();
            const client = (data[7] || '').toLowerCase();
            const employee = (data[8] || '').toLowerCase();
            const tags = (data[11] || '').toLowerCase();
            const description = (data[12] || '').toLowerCase();

            const activeType = (currentSearchType || 'all').toLowerCase();
            let matches = false;

            switch (activeType) {
                case 'taskid':
                    matches = taskId.includes(query);
                    break;
                case 'ticket':
                    matches = ticket.includes(query);
                    break;
                case 'project':
                    matches = project.includes(query);
                    break;
                case 'employee':
                    matches = employee.includes(query);
                    break;
                case 'tag':
                    matches = tags.includes(query);
                    break;
                default:
                    matches =
                        taskId.includes(query) ||
                        ticket.includes(query) ||
                        project.includes(query) ||
                        client.includes(query) ||
                        employee.includes(query) ||
                        tags.includes(query) ||
                        description.includes(query);
                    break;
            }

            if (dataIndex === 0) {
                console.log('CustomSearch: Query:', query, '| Type:', activeType, '| First row matches:', matches, '| TaskID:', taskId);
            }

            return matches;
        };

        // Register custom search function
        jQuery.fn.dataTable.ext.search.push(customSearchFunction);
    }

    /**
     * Initialize timesheet search
     */
    function init(dataTable) {
        dataTableInstance = dataTable;

        // FORCE enable searching - override stateSave cache
        if (dataTable && dataTable.settings && dataTable.settings()[0]) {
            dataTable.settings()[0].oFeatures.bFilter = true;
        }

        // Setup custom search function
        setupCustomSearch(dataTable);

        leantime.modernSearch.init({
            inputSelector: '#timesheetSearch',
            containerSelector: '#timesheetSearchWrapper',
            dataTableInstance: dataTable,
            getSuggestions: buildSuggestions,
            onSelect: (item) => {
                // Autocomplete item selected - insert into search and trigger search
                const input = document.querySelector('#timesheetSearch');
                if (input) {
                    input.value = item.value;
                }
                // Automatically trigger search when item is selected
                currentSearchType = item.type || 'all';
                currentSearchQuery = item.value;
                dataTable.draw();
            },
            debounceDelay: 300,
            autocompleteDelay: 150,
            // Custom search handler
            onSearch: (query) => {
                currentSearchType = 'all';
                currentSearchQuery = query;
                // Trigger DataTable redraw to apply custom search
                dataTable.draw();
            }
        });

        // Setup clear button
        const input = document.querySelector('#timesheetSearch');
        const clearButton = document.querySelector('#timesheetSearchClear');
        const wrapper = document.querySelector('#timesheetSearchWrapper');

        if (input && clearButton && wrapper) {
            input.addEventListener('input', () => {
                if (input.value.trim() !== '') {
                    wrapper.classList.add('has-value');
                    clearButton.style.display = 'block';
                } else {
                    wrapper.classList.remove('has-value');
                    clearButton.style.display = 'none';
                    // Auto-clear search when input is empty
                    if (currentSearchQuery !== '') {
                        console.log('TimesheetSearch: Clearing search, old query:', currentSearchQuery);
                        currentSearchQuery = '';
                        currentSearchType = 'all';
                        console.log('TimesheetSearch: New query:', currentSearchQuery);
                        
                        // Also clear default DataTables search
                        dataTable.search('');
                        
                        dataTable.draw();
                        console.log('TimesheetSearch: Table redrawn, visible rows:', dataTable.rows({search: 'applied'}).count());
                        console.log('TimesheetSearch: TOTAL rows in table (from server):', dataTable.rows().count());
                    }
                }
            });

            clearButton.addEventListener('click', () => {
                input.value = '';
                wrapper.classList.remove('has-value');
                clearButton.style.display = 'none';
                // Clear search using custom search function
                currentSearchQuery = '';
                currentSearchType = 'all';
                // Also clear default DataTables search
                dataTable.search('');
                dataTable.draw();
                input.focus();
            });
        }
    }

    // Public API
    return {
        init: init
    };
})();

