/**
 * tableGridController.js
 *
 * Spreadsheet-style inline-editable table grid for Leantime tickets.
 * Uses DataTables with RowGroup (collapsible groups), RowReorder (drag-and-drop),
 * inline editing on all fields, quick-add rows, and subtask expansion via child rows.
 *
 * Follows the existing leantime.* IIFE module pattern.
 */
leantime.tableGridController = (function () {

    // -----------------------------------------------------------------------
    // State
    // -----------------------------------------------------------------------
    var config = {};
    var dataTable = null;
    var collapsedGroups = {};
    var activeEditCell = null;

    // Column index constants (match the <th> order in showAll.blade.php)
    var COL = {
        DRAG:       0,
        ID:         1,
        TITLE:      2,
        STATUS:     3,
        MILESTONE:  4,
        EFFORT:     5,
        PRIORITY:   6,
        EDITOR:     7,
        SPRINT:     8,
        TAGS:       9,
        DUEDATE:    10,
        PLANHOURS:  11,
        REMAINING:  12,
        BOOKED:     13,
        SUBMENU:    14,
        GROUP_KEY:  15,
        GROUP_LABEL:16,
        SORT_INDEX: 17
    };

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    function isNumeric(n) {
        return !isNaN(parseFloat(n)) && isFinite(n);
    }

    function patchTicket(ticketId, data, callback) {
        data.id = ticketId;
        jQuery.ajax({
            type: 'PATCH',
            url: config.baseUrl + '/api/tickets',
            data: data,
            success: function (response) {
                jQuery.growl({message: config.i18n.saveSuccess, style: "growl-notice", duration: 1800});
                if (typeof callback === 'function') callback(response);
            },
            error: function () {
                jQuery.growl({message: config.i18n.saveError, style: "growl-error", duration: 3000});
            }
        });
    }

    function quickAddTicket(data, callback) {
        jQuery.ajax({
            type: 'POST',
            url: config.baseUrl + '/hx/tickets/tableGrid/quickAdd',
            data: data,
            success: function (response) {
                jQuery.growl({message: config.i18n.saveSuccess, style: "growl-notice", duration: 1800});
                if (typeof callback === 'function') callback(response);
            },
            error: function () {
                jQuery.growl({message: config.i18n.saveError, style: "growl-error", duration: 3000});
            }
        });
    }

    // -----------------------------------------------------------------------
    // Phase 1: DataTable init with RowGroup + collapsible groups
    // -----------------------------------------------------------------------
    function initDataTable() {

        // Restore collapsed groups from localStorage
        try {
            var stored = localStorage.getItem('lt_tableGrid_collapsed_' + config.projectId);
            if (stored) collapsedGroups = JSON.parse(stored);
        } catch (e) { /* ignore */ }

        // Custom search filter for collapsible groups.
        // Hides rows whose groupKey is in the collapsedGroups object.
        jQuery.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            if (settings.nTable.id !== 'ticketGridTable') return true;
            var groupKey = data[COL.GROUP_KEY];
            return !collapsedGroups[groupKey];
        });

        var hasGroups = config.groupBy && config.groupBy !== '' && config.groupBy !== 'all';

        dataTable = jQuery('#ticketGridTable').DataTable({
            language: {
                decimal:        leantime.i18n.__("datatables.decimal"),
                emptyTable:     leantime.i18n.__("datatables.emptyTable"),
                info:           leantime.i18n.__("datatables.info"),
                infoEmpty:      leantime.i18n.__("datatables.infoEmpty"),
                infoFiltered:   leantime.i18n.__("datatables.infoFiltered"),
                infoPostFix:    leantime.i18n.__("datatables.infoPostFix"),
                thousands:      leantime.i18n.__("datatables.thousands"),
                lengthMenu:     leantime.i18n.__("datatables.lengthMenu"),
                loadingRecords: leantime.i18n.__("datatables.loadingRecords"),
                processing:     leantime.i18n.__("datatables.processing"),
                search:         leantime.i18n.__("datatables.search"),
                zeroRecords:    leantime.i18n.__("datatables.zeroRecords"),
                paginate: {
                    first:    leantime.i18n.__("datatables.first"),
                    last:     leantime.i18n.__("datatables.last"),
                    next:     leantime.i18n.__("datatables.next"),
                    previous: leantime.i18n.__("datatables.previous"),
                },
                aria: {
                    sortAscending:  leantime.i18n.__("datatables.sortAscending"),
                    sortDescending: leantime.i18n.__("datatables.sortDescending"),
                },
                buttons: {
                    colvis: leantime.i18n.__("datatables.buttons.colvis"),
                    csv: leantime.i18n.__("datatables.buttons.download")
                }
            },
            dom: '<"top">rt<"bottom"p><"clear">',
            searching: true,       // Needed for the custom group-collapse search filter
            stateSave: true,
            displayLength: 200,
            paging: false,         // Show all rows for spreadsheet feel
            order: hasGroups ? [[COL.GROUP_KEY, 'asc'], [COL.SORT_INDEX, 'asc']] : [[COL.SORT_INDEX, 'asc']],
            columnDefs: [
                { visible: false, targets: [COL.GROUP_KEY, COL.GROUP_LABEL, COL.SORT_INDEX] },
                { visible: false, targets: [COL.PLANHOURS, COL.REMAINING] },
                { orderable: false, targets: 'no-sort' },
                { orderable: false, targets: [COL.DRAG, COL.SUBMENU] },
                { className: 'reorder-handle', targets: COL.DRAG },
            ],

            // RowGroup: collapsible group headers
            // Return a plain string so RowGroup auto-wraps it in <tr><th colspan=N>
            // which automatically gets the correct colspan for visible columns.
            rowGroup: hasGroups ? {
                dataSrc: COL.GROUP_KEY,
                startRender: function (rows, group, level) {
                    var meta = findGroupMeta(group);
                    var isCollapsed = !!collapsedGroups[group];
                    var count = rows.count();
                    var icon = isCollapsed ? 'fa-angle-right' : 'fa-angle-down';
                    var colorSwatch = meta.color ? '<span class="group-color-swatch" style="background:' + meta.color + ';"></span> ' : '';

                    var html =
                        '<div class="group-header-cell" data-group-key="' + escapeHtml(group) + '">' +
                            colorSwatch +
                            '<span class="group-toggle"><i class="fa ' + icon + '"></i></span> ' +
                            '<strong class="group-label">' + escapeHtml(meta.label) + '</strong>' +
                            ' <span class="group-count badge">(' + count + ')</span>' +
                            (meta.moreInfo ? ' <small class="group-info">' + escapeHtml(meta.moreInfo) + '</small>' : '') +
                            '<span class="group-actions">' +
                                '<a href="javascript:void(0);" class="group-add-task btn btn-link btn-sm" data-group-key="' + escapeHtml(group) + '">' +
                                    '<i class="fa fa-plus-circle"></i> ' + config.i18n.addTask +
                                '</a>' +
                            '</span>' +
                        '</div>';

                    return html;
                }
            } : false,

            // RowReorder: drag-and-drop sorting
            rowReorder: config.isEditor ? {
                dataSrc: COL.SORT_INDEX,
                selector: '.drag-handle, .drag-handle i',
                snapX: true,
                update: false,
                cancelable: true,
                excludedChildren: 'a'
            } : false,

            // Ensure DOM-based search input is hidden (we only use programmatic search filter)
            initComplete: function () {
                jQuery('#ticketGridTable_filter').hide();
            }
        });

        // Add Buttons (CSV + colvis) after init
        var buttons = new jQuery.fn.dataTable.Buttons(dataTable, {
            buttons: [
                {
                    extend: 'csvHtml5',
                    title: leantime.i18n.__("label.filename_fileexport"),
                    charset: 'utf-8',
                    bom: true,
                    exportOptions: {
                        columns: ':not(:first-child):not(:last-child):visible',
                        format: {
                            body: function (data, row, column, node) {
                                if (typeof jQuery(node).data('order') !== 'undefined') {
                                    data = jQuery(node).data('order');
                                }
                                return data;
                            }
                        }
                    }
                },
                {
                    extend: 'colvis',
                    columns: ':not(.noVis):not(:first-child):not(:last-child)'
                }
            ]
        }).container().appendTo(jQuery('#tableButtons'));

        // Update data-order when inputs change
        jQuery('#ticketGridTable input').on('change', function () {
            jQuery(this).parent().attr('data-order', jQuery(this).val());
            dataTable.draw();
        });
    }

    function findGroupMeta(groupKey) {
        if (!config.groupMeta) return { label: groupKey, color: '', moreInfo: '', count: 0 };
        for (var i = 0; i < config.groupMeta.length; i++) {
            if (config.groupMeta[i].key == groupKey) return config.groupMeta[i];
        }
        return { label: groupKey, color: '', moreInfo: '', count: 0 };
    }

    function getVisibleColumnCount() {
        if (!dataTable) return 15;
        return dataTable.columns(':visible').count();
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // -----------------------------------------------------------------------
    // Phase 1: Collapsible groups
    // -----------------------------------------------------------------------
    function initCollapsibleGroups() {
        jQuery('#ticketGridTable').on('click', '.group-header-cell .group-toggle, .group-header-cell .group-label, .group-header-cell .group-count', function (e) {
            e.stopPropagation();
            var groupKey = jQuery(this).closest('.group-header-cell').data('group-key');
            toggleGroup(groupKey);
        });
    }

    function toggleGroup(groupKey) {
        if (collapsedGroups[groupKey]) {
            delete collapsedGroups[groupKey];
        } else {
            collapsedGroups[groupKey] = true;
        }

        // Persist collapsed state
        try {
            localStorage.setItem('lt_tableGrid_collapsed_' + config.projectId, JSON.stringify(collapsedGroups));
        } catch (e) { /* ignore */ }

        dataTable.draw(false);
    }

    // -----------------------------------------------------------------------
    // Phase 2: Click-to-edit title + keyboard navigation
    // -----------------------------------------------------------------------
    function initInlineEditTitle() {
        if (!config.isEditor) return;

        // Click on title text to start editing
        jQuery('#ticketGridTable').on('click', '.title-text', function (e) {
            e.preventDefault();
            e.stopPropagation();
            startTitleEdit(jQuery(this));
        });

        // Save on Enter, cancel on Escape
        jQuery('#ticketGridTable').on('keydown', '.title-edit-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveTitleEdit(jQuery(this));
            } else if (e.key === 'Escape') {
                e.preventDefault();
                cancelTitleEdit(jQuery(this));
            } else if (e.key === 'Tab') {
                e.preventDefault();
                saveTitleEdit(jQuery(this));
                // Move to next editable cell in the same row
                var row = jQuery(this).closest('tr');
                var nextInput = row.find('.secretInput:first, .dropdown-toggle:first');
                if (nextInput.length) nextInput.focus();
            }
        });

        // Save on blur
        jQuery('#ticketGridTable').on('blur', '.title-edit-input', function () {
            var $input = jQuery(this);
            // Small delay to allow Enter/Escape to fire first
            setTimeout(function () {
                if ($input.is(':visible')) {
                    saveTitleEdit($input);
                }
            }, 150);
        });
    }

    function startTitleEdit($titleText) {
        // Cancel any other active edit
        if (activeEditCell) {
            cancelTitleEdit(activeEditCell);
        }

        var $cell = $titleText.closest('.title-cell-inner');
        var $input = $cell.find('.title-edit-input');
        var currentValue = $titleText.text().trim();

        $titleText.hide();
        $input.val(currentValue).show().focus().select();
        activeEditCell = $input;
    }

    function saveTitleEdit($input) {
        var ticketId = $input.data('ticket-id');
        var newValue = $input.val().trim();
        var $cell = $input.closest('.title-cell-inner');
        var $titleText = $cell.find('.title-text');
        var oldValue = $titleText.text().trim();

        $input.hide();
        $titleText.text(newValue).show();
        activeEditCell = null;

        if (newValue && newValue !== oldValue) {
            patchTicket(ticketId, { headline: newValue });
            // Update the data-order attribute on the td
            $input.closest('td').attr('data-order', newValue);
        }
    }

    function cancelTitleEdit($input) {
        var $cell = $input.closest('.title-cell-inner');
        var $titleText = $cell.find('.title-text');
        $input.hide();
        $titleText.show();
        activeEditCell = null;
    }

    // -----------------------------------------------------------------------
    // Phase 2: Global keyboard navigation
    // -----------------------------------------------------------------------
    function initKeyboardNav() {
        jQuery('#ticketGridTable').on('keydown', 'input, select', function (e) {
            if (e.key === 'Escape') {
                jQuery(this).blur();
            }
        });
    }

    // -----------------------------------------------------------------------
    // Phase 3: Quick row creation within groups
    // -----------------------------------------------------------------------
    function initQuickAdd() {
        if (!config.isEditor) return;

        // Add-task button in group headers
        jQuery('#ticketGridTable').on('click', '.group-add-task', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var groupKey = jQuery(this).data('group-key');
            showQuickAddRow(groupKey);
        });

        // Persistent quick-add bar below the table
        jQuery('#persistentQuickAdd').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var headline = jQuery(this).val().trim();
                if (!headline) return;

                var postData = {
                    headline: headline,
                    projectId: config.projectId,
                    status: 3
                };

                var $input = jQuery(this);
                $input.prop('disabled', true);

                quickAddTicket(postData, function () {
                    $input.val('').prop('disabled', false).focus();
                    location.reload();
                });
            }
        });

        // Global quick-add via keyboard shortcut (Ctrl+Shift+N)
        jQuery(document).on('keydown', function (e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'N') {
                e.preventDefault();
                jQuery('#persistentQuickAdd').focus();
            }
        });
    }

    function showQuickAddRow(groupKey) {
        // Remove any existing quick-add row
        jQuery('#ticketGridTable .quick-add-row').remove();

        // Find the last row in this group (or the last row in the table)
        var lastRowInGroup = null;
        if (groupKey && groupKey !== 'all') {
            jQuery('#ticketGridTable tbody tr.ticketRow').each(function () {
                var rowGroupKey = dataTable.cell(this, COL.GROUP_KEY).data();
                if (rowGroupKey == groupKey) {
                    lastRowInGroup = this;
                }
            });
        }

        if (!lastRowInGroup) {
            lastRowInGroup = jQuery('#ticketGridTable tbody tr:last')[0];
        }

        // Build the quick-add row
        var colCount = getVisibleColumnCount();
        var $row = jQuery('<tr class="quick-add-row" data-group-key="' + escapeHtml(groupKey) + '">' +
            '<td></td>' + // drag
            '<td><i class="fa fa-plus" style="color:var(--accent2);"></i></td>' + // id
            '<td colspan="' + (colCount - 2) + '">' +
                '<input type="text" class="quick-add-title-input" ' +
                'placeholder="' + config.i18n.addTask + '... (Enter to save, Escape to cancel)" ' +
                'style="width:100%; border:none; background:transparent; font-size:var(--base-font-size); padding:4px;" />' +
            '</td>' +
        '</tr>');

        // Insert after the last row in the group
        if (lastRowInGroup) {
            jQuery(lastRowInGroup).after($row);
        } else {
            jQuery('#ticketGridTable tbody').append($row);
        }

        var $input = $row.find('.quick-add-title-input');
        $input.focus();

        // Handle Enter to create, Escape to cancel
        $input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var headline = jQuery(this).val().trim();
                if (!headline) return;

                var postData = {
                    headline: headline,
                    projectId: config.projectId,
                    status: 3
                };

                // Inherit group context
                if (groupKey && config.groupBy === 'milestoneid') {
                    postData.milestone = groupKey;
                } else if (groupKey && config.groupBy === 'sprint') {
                    postData.sprint = groupKey;
                } else if (groupKey && config.groupBy === 'priority') {
                    postData.priority = groupKey;
                } else if (groupKey && config.groupBy === 'editorId') {
                    postData.editorId = groupKey;
                }

                var $currentRow = jQuery(this).closest('tr');
                var currentInput = jQuery(this);

                quickAddTicket(postData, function (response) {
                    // Reload page to get fresh data with the new ticket
                    location.reload();
                });

                // Clear and keep the input for rapid entry
                currentInput.val('');
                currentInput.attr('placeholder', 'Saving... type next task');

            } else if (e.key === 'Escape') {
                e.preventDefault();
                jQuery(this).closest('.quick-add-row').remove();
            }
        });

        $input.on('blur', function () {
            var $thisRow = jQuery(this).closest('.quick-add-row');
            // Remove if empty after short delay (allows click events to fire)
            setTimeout(function () {
                if ($thisRow.find('.quick-add-title-input').val().trim() === '') {
                    $thisRow.remove();
                }
            }, 300);
        });
    }

    // -----------------------------------------------------------------------
    // Phase 4: Inline milestone & sprint creation
    // -----------------------------------------------------------------------
    function initInlineCreate() {
        if (!config.isEditor) return;

        jQuery('#addMilestoneInline').on('click', function (e) {
            e.preventDefault();
            showInlineCreateForm('milestone');
        });

        jQuery('#addSprintInline').on('click', function (e) {
            e.preventDefault();
            showInlineCreateForm('sprint');
        });
    }

    function showInlineCreateForm(type) {
        // Remove existing forms
        jQuery('.inline-create-active').remove();

        var templateId = type === 'milestone' ? '#inlineMilestoneTemplate' : '#inlineSprintTemplate';
        var $form = jQuery(templateId).children().first().clone();
        var $wrapper = jQuery('<div class="inline-create-active"></div>').append($form);

        jQuery('.table-grid-actions').before($wrapper);

        var $input = $wrapper.find('.inline-create-input');
        $input.focus();

        // Init date pickers on the date fields
        $wrapper.find('.inline-create-date-from, .inline-create-date-to').datepicker({
            dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
            autoSize: true,
        });

        $wrapper.find('.inline-create-save').on('click', function () {
            saveInlineCreate(type, $wrapper);
        });

        $wrapper.find('.inline-create-cancel').on('click', function () {
            $wrapper.remove();
        });

        $input.on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                saveInlineCreate(type, $wrapper);
            } else if (e.key === 'Escape') {
                e.preventDefault();
                $wrapper.remove();
            }
        });
    }

    function saveInlineCreate(type, $wrapper) {
        var name = $wrapper.find('.inline-create-input').val().trim();
        if (!name) return;

        var dateFrom = $wrapper.find('.inline-create-date-from').val();
        var dateTo = $wrapper.find('.inline-create-date-to').val();

        if (type === 'milestone') {
            jQuery.ajax({
                type: 'POST',
                url: config.baseUrl + '/hx/tickets/tableGrid/addMilestone',
                data: {
                    headline: name,
                    editFrom: dateFrom,
                    editTo: dateTo,
                    projectId: config.projectId
                },
                success: function () {
                    jQuery.growl({message: config.i18n.saveSuccess, style: "growl-notice", duration: 1800});
                    location.reload();
                },
                error: function () {
                    jQuery.growl({message: config.i18n.saveError, style: "growl-error", duration: 3000});
                }
            });
        } else {
            jQuery.ajax({
                type: 'POST',
                url: config.baseUrl + '/hx/tickets/tableGrid/addSprint',
                data: {
                    name: name,
                    startDate: dateFrom,
                    endDate: dateTo,
                    projectId: config.projectId
                },
                success: function () {
                    jQuery.growl({message: config.i18n.saveSuccess, style: "growl-notice", duration: 1800});
                    location.reload();
                },
                error: function () {
                    jQuery.growl({message: config.i18n.saveError, style: "growl-error", duration: 3000});
                }
            });
        }

        $wrapper.remove();
    }

    // -----------------------------------------------------------------------
    // Phase 5: Subtask expansion via child rows
    // -----------------------------------------------------------------------
    function initSubtaskExpansion() {
        // Click on the subtask toggle chevron
        jQuery('#ticketGridTable').on('click', '.subtask-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var ticketId = jQuery(this).data('ticket-id');
            toggleSubtasks(ticketId);
        });

        // Click on "Add Subtask" in the submenu dropdown.
        // Remove any inline onclick handlers first (the shared submenu partial
        // targets a #subtask-form-{id} that doesn't exist in the table grid view).
        jQuery('#ticketGridTable').on('click', '.add-subtask-link', function (e) {
            e.preventDefault();
            e.stopPropagation();
            // Close the dropdown
            jQuery(this).closest('.dropdown-menu').parent().find('.dropdown-toggle').dropdown('toggle');
            // Find the ticket ID from the closest row
            var $tr = jQuery(this).closest('tr.ticketRow');
            var ticketId = $tr.data('id');
            if (ticketId) {
                toggleSubtasks(ticketId, true); // true = focus add-subtask input
            }
        });

        // Remove inline onclick from add-subtask-links to prevent conflicts
        jQuery('#ticketGridTable .add-subtask-link').removeAttr('onclick');
    }

    function toggleSubtasks(ticketId, focusAddInput) {
        var $toggle = jQuery('.subtask-toggle[data-ticket-id="' + ticketId + '"]');
        var $tr;

        // If there's a toggle button, use its row
        if ($toggle.length) {
            $tr = $toggle.closest('tr');
        } else {
            // No toggle (0 subtasks yet) -- find the row by data-id
            $tr = jQuery('#ticketGridTable tr[data-id="' + ticketId + '"]');
        }

        if (!$tr.length) return;
        var row = dataTable.row($tr);

        if (row.child.isShown() && !focusAddInput) {
            // Collapse
            row.child.hide();
            $tr.removeClass('shown');
            if ($toggle.length) {
                $toggle.find('i').removeClass('fa-angle-down').addClass('fa-angle-right');
            }
        } else if (row.child.isShown() && focusAddInput) {
            // Already expanded -- just focus the add input
            $tr.next().find('.add-subtask-input').focus();
        } else {
            // Expand: load subtasks
            if ($toggle.length) {
                $toggle.find('i').removeClass('fa-angle-right').addClass('fa-spinner fa-spin');
            }

            jQuery.ajax({
                type: 'GET',
                url: config.baseUrl + '/hx/tickets/tableGrid/getSubtasks',
                data: { ticketId: ticketId },
                success: function (html) {
                    if ($toggle.length) {
                        $toggle.find('i').removeClass('fa-spinner fa-spin').addClass('fa-angle-down');
                    }
                    row.child(html).show();
                    $tr.addClass('shown');

                    // Init inline editing on subtask rows
                    initSubtaskInlineEditing($tr.next());

                    // Init due date pickers on subtasks
                    if (typeof leantime.ticketsController.initDueDateTimePickers === 'function') {
                        leantime.ticketsController.initDueDateTimePickers();
                    }

                    // Focus the add input if requested
                    if (focusAddInput) {
                        $tr.next().find('.add-subtask-input').focus();
                    }
                },
                error: function () {
                    if ($toggle.length) {
                        $toggle.find('i').removeClass('fa-spinner fa-spin').addClass('fa-angle-right');
                    }
                    jQuery.growl({message: config.i18n.saveError, style: "growl-error"});
                }
            });
        }
    }

    function initSubtaskInlineEditing($childRow) {
        // Title edit on subtask rows
        $childRow.find('.subtask-title-text').on('click', function () {
            var $text = jQuery(this);
            var $input = $text.siblings('.subtask-title-input');
            $text.hide();
            $input.show().focus().select();
        });

        $childRow.find('.subtask-title-input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var $input = jQuery(this);
                var ticketId = $input.data('ticket-id');
                var newValue = $input.val().trim();
                $input.hide();
                $input.siblings('.subtask-title-text').text(newValue).show();
                if (newValue) {
                    patchTicket(ticketId, { headline: newValue });
                }
            } else if (e.key === 'Escape') {
                var $input = jQuery(this);
                $input.hide();
                $input.siblings('.subtask-title-text').show();
            }
        });

        $childRow.find('.subtask-title-input').on('blur', function () {
            var $input = jQuery(this);
            setTimeout(function () {
                if ($input.is(':visible')) {
                    $input.hide();
                    $input.siblings('.subtask-title-text').show();
                }
            }, 150);
        });

        // Quick-add subtask
        $childRow.find('.add-subtask-input').on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var headline = jQuery(this).val().trim();
                if (!headline) return;
                var parentId = jQuery(this).data('parent-id');
                var $input = jQuery(this);

                jQuery.ajax({
                    type: 'POST',
                    url: config.baseUrl + '/hx/tickets/tableGrid/addSubtask',
                    data: {
                        headline: headline,
                        parentTicketId: parentId,
                        projectId: config.projectId,
                        status: 3
                    },
                    success: function () {
                        jQuery.growl({message: config.i18n.saveSuccess, style: "growl-notice", duration: 1800});
                        // Re-expand subtasks to show the new one
                        var $toggle = jQuery('.subtask-toggle[data-ticket-id="' + parentId + '"]');
                        var $tr = $toggle.closest('tr');
                        var row = dataTable.row($tr);
                        row.child.hide();
                        $tr.removeClass('shown');
                        $toggle.trigger('click');
                    },
                    error: function () {
                        jQuery.growl({message: config.i18n.saveError, style: "growl-error"});
                    }
                });

                $input.val('');
            } else if (e.key === 'Escape') {
                jQuery(this).val('').blur();
            }
        });
    }

    // -----------------------------------------------------------------------
    // Phase 6: Drag-and-drop reordering
    // -----------------------------------------------------------------------
    function initDragAndDrop() {
        if (!config.isEditor || !dataTable) return;

        dataTable.on('row-reorder', function (e, diff, edit) {
            if (!diff || diff.length === 0) return;

            var updates = [];
            var movedTicketId = edit.triggerRow ? jQuery(edit.triggerRow.node()).data('id') : null;

            // Collect all sort index updates from the diff
            for (var i = 0; i < diff.length; i++) {
                var rowNode = diff[i].node;
                var ticketId = jQuery(rowNode).data('id');
                if (ticketId) {
                    updates.push({
                        id: ticketId,
                        sortIndex: i
                    });
                }
            }

            if (updates.length === 0) return;

            // Save sort order
            jQuery.ajax({
                type: 'POST',
                url: config.baseUrl + '/hx/tickets/tableGrid/reorder',
                data: JSON.stringify({
                    updates: updates,
                    movedTicketId: movedTicketId,
                    newGroupKey: null,
                    groupBy: config.groupBy
                }),
                contentType: 'application/json',
                success: function () {
                    jQuery.growl({message: config.i18n.saveSuccess, style: "growl-notice", duration: 1800});
                },
                error: function () {
                    jQuery.growl({message: config.i18n.saveError, style: "growl-error"});
                }
            });
        });
    }

    // -----------------------------------------------------------------------
    // Public API
    // -----------------------------------------------------------------------
    function init(cfg) {
        config = cfg;

        initDataTable();
        initCollapsibleGroups();
        initInlineEditTitle();
        initKeyboardNav();
        initQuickAdd();
        initInlineCreate();
        initSubtaskExpansion();
        initDragAndDrop();
    }

    return {
        init: init,
        toggleGroup: toggleGroup,
        getDataTable: function () { return dataTable; }
    };

})();
