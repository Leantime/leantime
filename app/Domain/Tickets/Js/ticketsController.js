leantime.ticketsController = (function () {

    //Variables


    //Functions
    function countTickets()
    {

        let ticketCounts = [];
        document.querySelectorAll(".sortableTicketList").forEach(function (listEl) {
            listEl.querySelectorAll(".column").forEach(function (colEl, indexCol) {

                if (ticketCounts[indexCol] === undefined) {
                    ticketCounts[indexCol] = 0;
                }

                var counting = colEl.querySelectorAll('.moveable').length;
                ticketCounts[indexCol] += counting;

            });

        });

        document.querySelectorAll(".widgettitle .count").forEach(function (el, index) {
            el.textContent = ticketCounts[index];
        });

    }

    /**
     * Update swimlane row counts after card movement
     * Counts tickets in each swimlane row and updates the count badges
     */
    function updateSwimlaneCounts()
    {
        document.querySelectorAll(".kanban-swimlane-row").forEach(function(row) {
            var swimlaneId = row.id;

            if (!swimlaneId) return;

            // Extract the swimlane identifier from the row ID (e.g., "swimlane-row-3" -> "3")
            var swimlaneKey = swimlaneId.replace('swimlane-row-', '');

            // Find the swimlane content area and count tickets
            var content = row.querySelector('.kanban-swimlane-content');
            var ticketCount = content ? content.querySelectorAll('.moveable').length : 0;

            // Update all count badges in this swimlane's sidebar
            var sidebar = row.querySelector('.kanban-swimlane-sidebar');
            if (sidebar) {
                sidebar.querySelectorAll('.kanban-lane-count').forEach(function(el) {
                    el.textContent = ticketCount;
                });

                // Update the aria-label for accessibility
                var currentLabel = sidebar.getAttribute('aria-label') || '';
                var newLabel = currentLabel.replace(/\d+ tasks/, ticketCount + ' tasks');
                sidebar.setAttribute('aria-label', newLabel);
            }
        });

        // Also update the progress bars
        updateSwimlaneProgressBars();
    }

    /**
     * Update swimlane progress bars after card movement
     * Recalculates status distribution and updates the micro-progress-bar segments
     */
    function updateSwimlaneProgressBars()
    {
        document.querySelectorAll(".kanban-swimlane-row").forEach(function(row) {
            var content = row.querySelector('.kanban-swimlane-content');
            var progressBar = row.querySelector('.micro-progress-bar .progress-segments');

            if (!progressBar) return;

            // Count tickets per status in this swimlane
            var statusCounts = {};
            var totalCount = 0;

            if (content) {
                content.querySelectorAll('.contentInner').forEach(function(column) {
                    var classAttr = column.getAttribute('class') || '';
                    var statusMatch = classAttr.match(/status_(-?\d+)/);
                    if (statusMatch) {
                        var statusId = statusMatch[1];
                        var ticketCount = column.querySelectorAll('.moveable').length;
                        if (ticketCount > 0) {
                            statusCounts[statusId] = ticketCount;
                            totalCount += ticketCount;
                        }
                    }
                });
            }

            // Get existing segments and their status IDs
            var segments = progressBar.querySelectorAll('.status-segment');

            if (totalCount === 0) {
                // No tickets - hide all segments
                segments.forEach(function(seg) {
                    seg.style.flex = '0 1 0%';
                    var countEl = seg.querySelector('.segment-count');
                    if (countEl) countEl.textContent = '';
                    seg.setAttribute('data-tippy-content', '');
                });
                return;
            }

            // Update each segment's flex-grow proportionally
            segments.forEach(function(segment) {
                var classAttr = segment.getAttribute('class');
                if (!classAttr) return;

                var statusMatch = classAttr.match(/status-(-?\d+)/);
                if (statusMatch) {
                    var statusId = statusMatch[1];
                    var count = statusCounts[statusId] || 0;
                    var percentage = (count / totalCount) * 100;

                    // Use flex-grow proportionally so segments fill 100% without gaps
                    segment.style.flex = percentage + ' 1 0%';

                    // Update count text
                    var countSpan = segment.querySelector('.segment-count');
                    if (countSpan) {
                        countSpan.textContent = count > 0 ? count : '';
                    }

                    // Update tooltip - only show if count > 0
                    var currentTooltip = segment.getAttribute('data-tippy-content') || '';
                    var labelMatch = currentTooltip.match(/^([^:]+):/);
                    var label = labelMatch ? labelMatch[1] : 'Status ' + statusId;
                    segment.setAttribute('data-tippy-content', count > 0 ? label + ': ' + count : '');
                }
            });
        });
    }

    /**
     * Move a ticket card to a different swimlane when grouped field is changed
     * @param {number} ticketId - The ticket ID
     * @param {string|number} newSwimlaneValue - The new swimlane value (priority, milestone, etc.)
     */
    var moveCardToSwimlane = function(ticketId, newSwimlaneValue) {
        // Find the card element
        var card = document.getElementById("ticket_" + ticketId);

        if (!card) {
            console.warn("Card not found for ticket ID:", ticketId);
            return;
        }

        // Get current status from card's column
        var currentColumn = card.closest('.contentInner');
        var classAttr = currentColumn ? currentColumn.getAttribute('class') || '' : '';
        var statusMatch = classAttr.match(/status_(\d+)/);

        if (!statusMatch) {
            console.warn("Could not determine status for ticket:", ticketId);
            location.reload(); // Fallback to reload
            return;
        }

        var statusId = statusMatch[1];

        // Find target swimlane column
        var targetColumn = document.querySelector("#kanboard-" + newSwimlaneValue + " .contentInner.status_" + statusId);

        if (!targetColumn) {
            console.warn("Target swimlane not found:", newSwimlaneValue, statusId);
            location.reload(); // Fallback to reload
            return;
        }

        // Don't move if already in correct location
        if (currentColumn === targetColumn) {
            return;
        }

        // Add exit animation
        card.classList.add('card-moving-out');

        setTimeout(function() {
            // Move card to new swimlane
            targetColumn.appendChild(card);

            // Add entrance animation
            card.classList.remove('card-moving-out');
            card.classList.add('card-moving-in');

            // Update ticket counts
            countTickets();
            updateSwimlaneCounts();

            // SortableJS auto-detects DOM changes, no refresh needed

            // Remove animation class after transition
            setTimeout(function() {
                card.classList.remove('card-moving-in');
            }, 300);
        }, 200);
    };


    var updateRemainingHours = function (element, id) {
        var el = typeof element === 'string' ? document.querySelector(element) : element;
        var value = el.value;
        leantime.ticketsRepository.updateRemainingHours(
            id,
            value,
            function () {
                leantime.toast.show({message: leantime.i18n.__("short_notifications.remaining_hours_updated"), style: "success"});
            }
        );

    };

    var updatePlannedHours = function (element, id) {
        var el = typeof element === 'string' ? document.querySelector(element) : element;
        var value = el.value;
        leantime.ticketsRepository.updatePlannedHours(
            id,
            value,
            function () {
                leantime.toast.show({message: leantime.i18n.__("short_notifications.planned_hours_updated"), style: "success"});
            }
        );

    };


    var toggleFilterBar = function () {
        document.querySelectorAll(".filterBar").forEach(function(el) {
            el.style.display = el.style.display === 'none' ? '' : 'none';
        });

    };

    var initGanttChart = function (tasks, viewMode, readonly) {

        function htmlEntities(str)
        {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        document.addEventListener('DOMContentLoaded',
            function () {

                // Helper to format date for gantt popups using jQuery datepicker if available, else ISO string
                function formatGanttDate(dateVal) {
                    if (typeof jQuery !== 'undefined' && jQuery.datepicker && jQuery.datepicker.formatDate) {
                        return jQuery.datepicker.formatDate(leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"), new Date(dateVal));
                    }
                    return new Date(dateVal).toLocaleDateString();
                }

                if (readonly === false) {
                    var gantt_chart = new Gantt(
                        "#gantt",
                        tasks,
                        {
                            header_height: 55,
                            column_width: 20,
                            step: 24,
                            view_modes: ['Day', 'Week', 'Month'],
                            bar_height: 40,
                            static_progress_indicator: true,
                            bar_corner_radius: 10,
                            arrow_curve: 10,
                            padding:20,
                            view_mode: 'Month',
                            date_format: leantime.i18n.__("language.momentJSDate"),
                            language: leantime.i18n.__("language.code").slice(0, 2), //Get first 2 characters of language code
                            additional_rows: 5,
                            custom_popup_html: function (task) {

                                // the task object will contain the updated
                                // dates and progress value
                                var end_date = task._end;
                                var dateTime = formatGanttDate(end_date);

                                var popUpHTML = '<div class="details-container" style="min-width:600px;"> ';

                                if (task.projectName !== undefined) {
                                    popUpHTML +=  '<h3><b>' + task.projectName + '</b></h3>';
                                }
                                popUpHTML += '<small>' + task.type + ' #' + task.id + ' </small>';

                                if (task.type === 'milestone') {
                                    popUpHTML += '<h4><a href="#/tickets/editMilestone/' + task.id + '" >' + htmlEntities(task.name) + '</a></h4><br /> ' +
                                     '<p>' + leantime.i18n.__("text.expected_to_finish_by") + ' <strong>' + dateTime + '</strong><br /> ' +
                                     '' + Math.round(task.progress) + '%</p> ' +
                                     '<a href="#/tickets/editMilestone/' + task.id + '" ><span class="fa fa-map"></span> ' + leantime.i18n.__("links.edit_milestone") + '</a> | ' +
                                     '<a href="' + leantime.appUrl + '/tickets/showKanban?milestone=' + task.id + '"><span class="fa-pushpin"></span> ' + leantime.i18n.__("links.view_todos") + '</a> ';
                                } else {
                                    popUpHTML += '<h4><a href="#/tickets/showTicket/' + task.id + '">' + htmlEntities(task.name) + '</a></h4><br /> ' +
                                     '<a href="#/tickets/showTicket/' + task.id + '"><span class="fa fa-thumb-tack"></span> ' + leantime.i18n.__("links.edit_todo") + '</a> ';
                                }

                                 popUpHTML += '</div>';

                                return popUpHTML;
                            },
                            on_click: function (task) {

                            },
                            on_date_change: function (task, start, end) {

                                leantime.ticketsRepository.updateMilestoneDates(task.id, start, end, task._index+1);

                            },
                            on_sort_change: function (tasks) {

                                var statusPostData = {
                                    action: "ganttSort",
                                    payload: {}
                                };

                                for (var i = 0; i < tasks.length; i++) {
                                        //start sorting counter at 1 instead of 0 since 0 will cause date comparison
                                        statusPostData.payload[tasks[i].id] = tasks[i]._index+1;
                                }

                                // POST to server
                                var formData = new URLSearchParams();
                                formData.append('action', statusPostData.action);
                                for (var key in statusPostData.payload) {
                                    formData.append('payload[' + key + ']', statusPostData.payload[key]);
                                }

                                fetch(leantime.appUrl + '/api/tickets', {
                                    method: 'POST',
                                    body: formData,
                                    credentials: 'include',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });
                            },
                            on_progress_change: function (task, progress) {

                                //_initModals();
                            },
                            on_view_change: function (mode) {

                                leantime.usersRepository.updateUserViewSettings("roadmap", mode);

                            },
                            on_popup_show: function (task) {

                            }
                        }
                    );
                } else {
                    var gantt_chart = new Gantt(
                        "#gantt",
                        tasks,
                        {
                            readonlyGantt: true,
                            resizing: false,
                            progress: false,
                            is_draggable: false,
                            custom_popup_html: function (task) {


                                var end_date = task._end;
                                var dateTime = formatGanttDate(end_date);

                                var popUpHTML = '<div class="details-container" style="min-width:600px;"> ';

                                if (task.projectName !== undefined) {
                                    popUpHTML +=  '<h3><b>' + task.projectName + '</b></h3>';
                                }
                                popUpHTML += '<small>' + task.type + ' #' + task.id + ' </small>';

                                if (task.type === 'milestone') {
                                    popUpHTML += '<h4>' + htmlEntities(task.name) + '</h4><br /> ' +
                                        '<p>' + leantime.i18n.__("text.expected_to_finish_by") + ' <strong>' + dateTime + '</strong><br /> ' +
                                        '' + Math.round(task.progress) + '%</p> ' +
                                        '<a href="' + leantime.appUrl + '/tickets/showKanban?milestone=' + task.id + '"><span class="fa-pushpin"></span> ' + leantime.i18n.__("links.view_todos") + '</a> ';
                                } else {
                                    popUpHTML += '<h4><a href="#/tickets/showTicket/' + task.id + '">' + htmlEntities(task.name) + '</a></h4><br /> ' +
                                        '<a href="#/tickets/showTicket/' + task.id + '"><span class="fa fa-thumb-tack"></span> ' + leantime.i18n.__("links.edit_todo") + '</a> ';
                                }

                                popUpHTML += '</div>';

                                return popUpHTML;

                            },
                            on_click: function (task) {

                            },
                            on_date_change: function (task, start, end) {


                            },
                            on_progress_change: function (task, progress) {


                            },
                            on_view_change: function (mode) {

                                leantime.usersRepository.updateUserViewSettings("roadmap", mode);

                            }
                        }
                    );
                }

                var ganttTimeControl = document.getElementById("ganttTimeControl");
                if (ganttTimeControl) {
                    ganttTimeControl.addEventListener("click", function (e) {
                        var btn = e.target.closest("a");
                        if (!btn) return;

                        var mode = btn.getAttribute("data-value");
                        gantt_chart.change_view_mode(mode);
                        ganttTimeControl.querySelectorAll('a').forEach(function(a) { a.classList.remove('active'); });
                        btn.classList.add('active');
                        var label = btn.textContent;
                        document.querySelectorAll(".viewText").forEach(function(el) { el.textContent = label; });
                    });
                }

                gantt_chart.change_view_mode(viewMode);

            }
        );

    };

    var initSprintDates = function () {

        Date.prototype.addDays = function (days) {
            this.setDate(this.getDate() + days);
            return this;
        };
        jQuery.datepicker.setDefaults(
            { beforeShow: function (i) {
                if (i.hasAttribute('readonly')) {
                    return false; } } }
        );

        var dateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),

            from = jQuery("#sprintStart")
                .datepicker(
                    {
                        numberOfMonths: 1,
                        dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                        dayNames: leantime.i18n.__("language.dayNames").split(","),
                        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                        monthNames: leantime.i18n.__("language.monthNames").split(","),
                        currentText: leantime.i18n.__("language.currentText"),
                        closeText: leantime.i18n.__("language.closeText"),
                        buttonText: leantime.i18n.__("language.buttonText"),
                        isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                        nextText: leantime.i18n.__("language.nextText"),
                        prevText: leantime.i18n.__("language.prevText"),
                        weekHeader: leantime.i18n.__("language.weekHeader"),
                        firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                    }
                )
                .on(
                    "change",
                    function () {
                        to.datepicker("option", "minDate", getDate(this));
                        var newEndDate = getDate(this).addDays(13);
                        to.datepicker('setDate', newEndDate); //set date

                    }
                ),

            to = jQuery("#sprintEnd").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1,
                    dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                    dayNames: leantime.i18n.__("language.dayNames").split(","),
                    dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                    dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                    monthNames: leantime.i18n.__("language.monthNames").split(","),
                    currentText: leantime.i18n.__("language.currentText"),
                    closeText: leantime.i18n.__("language.closeText"),
                    buttonText: leantime.i18n.__("language.buttonText"),
                    isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                    nextText: leantime.i18n.__("language.nextText"),
                    prevText: leantime.i18n.__("language.prevText"),
                    weekHeader: leantime.i18n.__("language.weekHeader"),
                    firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                }
            )
            .on(
                "change",
                function () {
                    from.datepicker("option", "maxDate", getDate(this));
                }
            );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch ( error ) {
                date = null;
                console.log(error);
            }

            return date;
        }
    };

    var _initMilestoneDates = function () {
        var dateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
            from = jQuery("#milestoneEditFrom")
                .datepicker(
                    {
                        numberOfMonths: 1,
                        dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                        dayNames: leantime.i18n.__("language.dayNames").split(","),
                        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                        monthNames: leantime.i18n.__("language.monthNames").split(","),
                        currentText: leantime.i18n.__("language.currentText"),
                        closeText: leantime.i18n.__("language.closeText"),
                        buttonText: leantime.i18n.__("language.buttonText"),
                        isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                        nextText: leantime.i18n.__("language.nextText"),
                        prevText: leantime.i18n.__("language.prevText"),
                        weekHeader: leantime.i18n.__("language.weekHeader"),
                        firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                    }
                )
                .on(
                    "change",
                    function () {
                        to.datepicker("option", "minDate", getDate(this));
                    }
                ),
            to = jQuery("#milestoneEditTo").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1,
                    dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                    dayNames: leantime.i18n.__("language.dayNames").split(","),
                    dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                    dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                    monthNames: leantime.i18n.__("language.monthNames").split(","),
                    currentText: leantime.i18n.__("language.currentText"),
                    closeText: leantime.i18n.__("language.closeText"),
                    buttonText: leantime.i18n.__("language.buttonText"),
                    isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                    nextText: leantime.i18n.__("language.nextText"),
                    prevText: leantime.i18n.__("language.prevText"),
                    weekHeader: leantime.i18n.__("language.weekHeader"),
                    firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                }
            )
                .on(
                    "change",
                    function () {
                        from.datepicker("option", "maxDate", getDate(this));
                    }
                );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch ( error ) {
                date = null;
                console.log(error);
            }

            return date;
        }
    };

    var initMilestoneDatesAsyncUpdate = function () {

        var dateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
            from = jQuery(".milestoneEditFromAsync")
                .datepicker(
                    {
                        numberOfMonths: 1,
                        dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                        dayNames: leantime.i18n.__("language.dayNames").split(","),
                        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                        monthNames: leantime.i18n.__("language.monthNames").split(","),
                        currentText: leantime.i18n.__("language.currentText"),
                        closeText: leantime.i18n.__("language.closeText"),
                        buttonText: leantime.i18n.__("language.buttonText"),
                        isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                        nextText: leantime.i18n.__("language.nextText"),
                        prevText: leantime.i18n.__("language.prevText"),
                        weekHeader: leantime.i18n.__("language.weekHeader"),
                        firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                    }
                )
                .on(
                    "change",
                    function () {

                        var date = this.value;
                        var id = this.getAttribute("data-id");

                        var toDatePicker = jQuery(".toDateTicket-" + id);
                        toDatePicker.datepicker("option", "minDate", getDate(this));

                        leantime.ticketsRepository.updateEditFromDates(id, date, function() {
                            leantime.toast.show({message: leantime.i18n.__("short_notifications.date_updated"), style: "success"});
                        });





                    }
                ),
            to = jQuery(".milestoneEditToAsync").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1,
                    dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
                    dayNames: leantime.i18n.__("language.dayNames").split(","),
                    dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                    dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                    monthNames: leantime.i18n.__("language.monthNames").split(","),
                    currentText: leantime.i18n.__("language.currentText"),
                    closeText: leantime.i18n.__("language.closeText"),
                    buttonText: leantime.i18n.__("language.buttonText"),
                    isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
                    nextText: leantime.i18n.__("language.nextText"),
                    prevText: leantime.i18n.__("language.prevText"),
                    weekHeader: leantime.i18n.__("language.weekHeader"),
                    firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                }
            )
                .on(
                    "change",
                    function () {

                        var id = this.getAttribute("data-id");
                        var fromDateTicket = jQuery(".fromDateTicket-" + id);
                        fromDateTicket.datepicker("option", "maxDate", getDate(this));

                        var date = this.value;

                        leantime.ticketsRepository.updateEditToDates(id, date, function() {
                            leantime.toast.show({message: leantime.i18n.__("short_notifications.date_updated"), style: "success"});
                        });

                    }
                );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch ( error ) {
                date = null;
                console.log(error);
            }

            return date;
        }
    };

    var initToolTips = function () {
        // tooltip() is a Bootstrap/jQuery UI plugin - use jQuery if available
        if (typeof jQuery !== 'undefined' && jQuery.fn.tooltip) {
            jQuery('[data-toggle="tooltip"]').tooltip();
        }
    };

    var initEffortDropdown = function () {

        var storyPointLabels = {
            '0.5': '< 2min',
            '1': 'XS',
            '2': 'S',
            '3': "M",
            '5': "L",
            '8' : "XL",
            '13': "XXL"
        };

        document.querySelectorAll(".effortDropdown .dropdown-menu a").forEach(function (link) {
            link.replaceWith(link.cloneNode(true));
        });
        document.querySelectorAll(".effortDropdown .dropdown-menu a").forEach(function (link) {
            link.addEventListener("click", function () {

                var dataValue = this.getAttribute("data-value").split("_");

                if (dataValue.length === 2) {
                    var ticketId = dataValue[0];
                    var effortId = dataValue[1];

                    var formData = new URLSearchParams();
                    formData.append('id', ticketId);
                    formData.append('storypoints', effortId);

                    fetch(leantime.appUrl + '/api/tickets', {
                        method: 'PATCH',
                        body: formData,
                        credentials: 'include',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function () {
                        var textEl = document.querySelector("#effortDropdownMenuLink" + ticketId + " span.text");
                        if (textEl) textEl.textContent = storyPointLabels[effortId];
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.effort_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by effort
                        if (leantime.kanbanGroupBy === 'storypoints') {
                            moveCardToSwimlane(ticketId, effortId);
                        }

                    });
                } else {
                    console.log("Ticket Controller: Effort data value not set correctly");
                }
            });
        });

    };

    var initPriorityDropdown = function () {
        // '1' => 'Critical', '2' => 'High', '3' => 'Medium', '4' => 'Low'
        var priorityLabels = {
            '1': 'Critical',
            '2': 'High',
            '3': "Medium",
            '4': "Low",
            '5': "Lowest"
        };

        document.querySelectorAll(".priorityDropdown .dropdown-menu a").forEach(function (link) {
            link.replaceWith(link.cloneNode(true));
        });
        document.querySelectorAll(".priorityDropdown .dropdown-menu a").forEach(function (link) {
            link.addEventListener("click", function () {

                var dataValue = this.getAttribute("data-value").split("_");

                if (dataValue.length === 2) {
                    var ticketId = dataValue[0];
                    var priorityId = dataValue[1];

                    var formData = new URLSearchParams();
                    formData.append('id', ticketId);
                    formData.append('priority', priorityId);

                    fetch(leantime.appUrl + '/api/tickets', {
                        method: 'PATCH',
                        body: formData,
                        credentials: 'include',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function () {
                        var dropdownLink = document.getElementById("priorityDropdownMenuLink" + ticketId);
                        if (dropdownLink) {
                            var textEl = dropdownLink.querySelector("span.text");
                            if (textEl) textEl.textContent = priorityLabels[priorityId];
                            dropdownLink.classList.remove("priority-bg-1", "priority-bg-2", "priority-bg-3", "priority-bg-4", "priority-bg-5");
                            dropdownLink.classList.add("priority-bg-" + priorityId);

                            var ticketBox = dropdownLink.closest(".ticketBox");
                            if (ticketBox) {
                                ticketBox.classList.remove("priority-border-1", "priority-border-2", "priority-border-3", "priority-border-4", "priority-border-5");
                                ticketBox.classList.add("priority-border-" + priorityId);
                            }
                        }

                        leantime.toast.show({message: leantime.i18n.__("short_notifications.priority_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by priority
                        if (leantime.kanbanGroupBy === 'priority') {
                            moveCardToSwimlane(ticketId, priorityId);
                        }

                    });
                } else {
                    console.log("Ticket Controller: Priority data value not set correctly");
                }
            });
        });

    };

    var initMilestoneDropdown = function () {

        document.querySelectorAll(".milestoneDropdown .dropdown-menu a").forEach(function (link) {
            link.replaceWith(link.cloneNode(true));
        });
        document.querySelectorAll(".milestoneDropdown .dropdown-menu a").forEach(function (link) {
            link.addEventListener("click", function () {

                var dataValue = this.getAttribute("data-value").split("_");
                var dataLabel = this.getAttribute('data-label');

            if (dataValue.length === 3) {
                var ticketId = dataValue[0];
                var milestoneId = dataValue[1];
                var color = dataValue[2];

                var textEl = document.querySelector("#milestoneDropdownMenuLink" + ticketId + " span.text");
                if (textEl) textEl.insertAdjacentHTML('beforeend', " ...");

                var formData = new URLSearchParams();
                formData.append('id', ticketId);
                formData.append('milestoneid', milestoneId);

                fetch(leantime.appUrl + '/api/tickets', {
                    method: 'PATCH',
                    body: formData,
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function () {
                        var textEl = document.querySelector("#milestoneDropdownMenuLink" + ticketId + " span.text");
                        if (textEl) textEl.textContent = dataLabel;
                        var linkEl = document.getElementById("milestoneDropdownMenuLink" + ticketId);
                        if (linkEl) linkEl.style.backgroundColor = color;
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.milestone_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by milestone
                        if (leantime.kanbanGroupBy === 'milestoneid') {
                            moveCardToSwimlane(ticketId, milestoneId);
                        }
                    }
                );
            }
            });
        });
    };

    var initStatusDropdown = function () {

        document.querySelectorAll(".statusDropdown .dropdown-menu a").forEach(function (link) {
            link.replaceWith(link.cloneNode(true));
        });
        document.querySelectorAll(".statusDropdown .dropdown-menu a").forEach(function (link) {
            link.addEventListener("click", function () {

                var dataValue = this.getAttribute("data-value").split("_");
                var dataLabel = this.getAttribute('data-label');

            if (dataValue.length == 3) {
                var ticketId = dataValue[0];
                var statusId = dataValue[1];
                var className = dataValue[2];

                var formData = new URLSearchParams();
                formData.append('id', ticketId);
                formData.append('status', statusId);

                fetch(leantime.appUrl + '/api/tickets', {
                    method: 'PATCH',
                    body: formData,
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function (response) { return response.json(); })
                .then(function (response) {
                        var textEl = document.querySelector("#statusDropdownMenuLink" + ticketId + " span.text");
                        if (textEl) textEl.textContent = dataLabel;
                        var linkEl = document.getElementById("statusDropdownMenuLink" + ticketId);
                        if (linkEl) linkEl.className = className + " dropdown-toggle f-left status ";
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.status_updated"), style: "success"});

                        leantime.handleAsyncResponse(response);

                    }
                );
            }
            });
        });

    };

    var initUserDropdown = function () {

        document.querySelectorAll(".userDropdown .dropdown-menu a").forEach(function (link) {
            link.replaceWith(link.cloneNode(true));
        });
        document.querySelectorAll(".userDropdown .dropdown-menu a").forEach(function (link) {
            link.addEventListener("click", function () {

                var dataValue = this.getAttribute("data-value").split("_");
                var dataLabel = this.getAttribute('data-label');

            if (dataValue.length === 3) {
                var ticketId = dataValue[0];
                var userId = dataValue[1];
                var profileImageId = dataValue[2];

                var formData = new URLSearchParams();
                formData.append('id', ticketId);
                formData.append('editorId', userId);

                fetch(leantime.appUrl + '/api/tickets', {
                    method: 'PATCH',
                    body: formData,
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function () {
                        var imgEl = document.querySelector("#userDropdownMenuLink" + ticketId + " span.text span#userImage" + ticketId + " img");
                        if (imgEl) imgEl.setAttribute("src", leantime.appUrl + "/api/users?profileImage=" + userId);
                        var userEl = document.querySelector("#userDropdownMenuLink" + ticketId + " span.text span#user" + ticketId);
                        if (userEl) userEl.textContent = dataLabel;
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.user_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by user
                        if (leantime.kanbanGroupBy === 'editorId') {
                            moveCardToSwimlane(ticketId, userId);
                        }
                    }
                );
            }
            });
        });
    };

    var initAsyncInputChange = function () {

        document.querySelectorAll(".asyncInputUpdate").forEach(function (input) {
            input.addEventListener("change", function () {
                var dataLabel = this.getAttribute('data-label').split("-");

                if (dataLabel.length == 2) {
                    var fieldName = dataLabel[0];
                    var entityId = dataLabel[1];
                    var value = this.value;

                    var formData = new URLSearchParams();
                    formData.append('id', entityId);
                    formData.append(fieldName, value);

                    fetch(leantime.appUrl + '/api/tickets', {
                        method: 'PATCH',
                        body: formData,
                        credentials: 'include',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function () {
                            leantime.toast.show({message: leantime.i18n.__("notifications.subtask_saved"), style: "success"});
                        }
                    );
                }

            });
        });
    };

    var initSprintDropdown = function () {

        document.querySelectorAll(".sprintDropdown .dropdown-menu a").forEach(function (link) {
            link.replaceWith(link.cloneNode(true));
        });
        document.querySelectorAll(".sprintDropdown .dropdown-menu a").forEach(function (link) {
            link.addEventListener("click", function () {

                var dataValue = this.getAttribute("data-value").split("_");
                var dataLabel = this.getAttribute('data-label');

            if (dataValue.length == 2) {
                var ticketId = dataValue[0];
                var sprintId = dataValue[1];

                var formData = new URLSearchParams();
                formData.append('id', ticketId);
                formData.append('sprint', sprintId);

                fetch(leantime.appUrl + '/api/tickets', {
                    method: 'PATCH',
                    body: formData,
                    credentials: 'include',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                }).then(function () {
                        var textEl = document.querySelector("#sprintDropdownMenuLink" + ticketId + " span.text");
                        if (textEl) textEl.textContent = dataLabel;
                        leantime.toast.show({message: leantime.i18n.__("short_notifications.sprint_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by sprint
                        if (leantime.kanbanGroupBy === 'sprint') {
                            moveCardToSwimlane(ticketId, sprintId);
                        }
                    }
                );
            }
            });
        });
    };

    var initSimpleColorPicker = function () {

            var colors = ['#821219',
                '#BB1B25',
                '#75BB1B',
                '#4B7811',
                '#fdab3d',
                '#1bbbb1',
                '#006d9f',
                '#124F7D',
                '#082236',
                '#5F0F40',
                '#bb1b75',
                '#F26CA7',
                '#BB611B',
                '#aaaaaa',
                '#4c4c4c',
            ];
            document.querySelectorAll('input.simpleColorPicker').forEach(function (input) {
                // Convert text input to native color picker
                input.type = 'color';
                input.style.height = '34px';
                input.style.padding = '2px';
                input.style.cursor = 'pointer';
                if (input.value && input.value.charAt(0) !== '#') {
                    input.value = '#' + input.value;
                }
                if (!input.value) {
                    input.value = '#1b75bb';
                }
                input.addEventListener('input', function () {
                    this.style.background = this.value;
                });
            });


    };

    var initDueDateTimePickers = function () {
        // Reset due date by clicking a button on the task in the dashboard
        document.querySelectorAll(".date-picker-form-control .reset-button").forEach(function (btn) {
            btn.addEventListener('click', function () {
                // Ticket id for api patch call
                var id = this.getAttribute("data-id");

                // Update date input to have "text-anytime" instead of old date
                var dateInput = document.getElementById("due-date-picker-" + id);
                if (dateInput) dateInput.value = leantime.i18n.__("text.anytime");

                // Set date to null to reset
                leantime.ticketsRepository.updateDueDates(id, null, function () {
                    // Notify user that due date is updated
                    leantime.toast.show({message: leantime.i18n.__("short_notifications.duedate_updated"), style: "success"});
                });
            });
        });

        leantime.dateController.initDatePicker(".quickDueDates, .duedates", function(date, instance) {
            //TODO: Update to use htmx, this is awful
            var day = instance.currentDay;
            var month = instance.currentMonth;
            var year = instance.currentYear;

            var dateObject = new Date(year, month, day);
            // jQuery.datepicker.formatDate is a jQuery UI dependency - use if available
            var parsed = (typeof jQuery !== 'undefined' && jQuery.datepicker && jQuery.datepicker.formatDate)
                ? jQuery.datepicker.formatDate(leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"), dateObject)
                : dateObject.toLocaleDateString();

            var id = this.getAttribute("data-id");

            leantime.ticketsRepository.updateDueDates(id, parsed, function () {
                leantime.toast.show({message: leantime.i18n.__("short_notifications.duedate_updated"), style: "success"});
            });

        });

    };

    var initTimeSheetChart = function (labels, d2, d3, canvasId) {

        var ctx = document.getElementById(canvasId).getContext('2d');
        var stackedLine = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets:[{
                    label: leantime.i18n.__("label.booked_hours"),
                    backgroundColor: 'rgba(201,48,44, 0.5)',
                    borderColor: 'rgb(201,48,44)',
                    data:d2
                },
                    {
                        label:leantime.i18n.__("label.planned_hours"),
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor:'rgb(54, 162, 235)',
                        data:d3
                }]
            },
            options: {
                scales: {
                    x: {
                        display: true,
                        title: {
                            display: true,
                            text: leantime.i18n.__("label.booked_hours"),
                        },
                        type: 'time',
                        time: {
                            unit: 'day'
                        }
                    },
                    y: {
                        display: true,
                        title: {
                            display: true,
                            text: leantime.i18n.__("label.planned_hours")
                        },
                        ticks: {
                            beginAtZero: true
                        }
                    }
                }
            }
        });
    };

    var colorTicketBoxes = function (currentBox) {

        var color = "#fff";
        document.querySelectorAll(".ticketBox").forEach(function (box) {

            var linkEl = box.querySelector(".statusDropdown > a");
            var value = linkEl ? linkEl.getAttribute("class") : undefined;

            if (value != undefined) {
                if (value.indexOf("important") > -1) {
                    color = "#b94a48";
                } else if (value.indexOf("info") > -1) {
                        color = "#2d6987";
                } else if (value.indexOf("warning") > -1) {
                    color = "#f89406";
                } else if (value.indexOf("success") > -1) {
                    color = "#468847";
                } else if (value.indexOf("default") > -1) {
                    color = "#999999";
                } else {
                    color = "#999999";
                }

                box.style.borderLeft = "5px solid " + color;

                if (currentBox != null) {
                    if (box.getAttribute("data-val") == currentBox) {
                        // Simple highlight flash without jQuery animate
                        var targetBox = document.querySelector("#ticket_" + currentBox + " .ticketBox");
                        if (targetBox) {
                            targetBox.style.transition = 'background-color 0.2s ease';
                            targetBox.style.backgroundColor = color;
                            setTimeout(function() {
                                targetBox.style.transition = 'background-color 0.6s ease';
                                targetBox.style.backgroundColor = "#fff";
                            }, 200);
                        }
                    }
                }
            }

        });

    };

    var initTicketTabs = function () {

        // Note: DOMContentLoaded has already fired by the time this runs
        // (called from inline <script> after page load), so run directly.
        var url = new URL(window.location.href);
        var tab = url.searchParams.get("tab");

        var activeTabIndex = 0;
        if (tab) {
            var tabsContainer = document.querySelector('.ticketTabs');
            if (tabsContainer) {
                var tabLink = tabsContainer.querySelector('a[href="#' + tab + '"]');
                if (tabLink && tabLink.parentElement) {
                    activeTabIndex = Array.prototype.indexOf.call(tabLink.parentElement.parentElement.children, tabLink.parentElement);
                }
            }
        }

        // jQuery UI tabs() requires jQuery - use jQuery if available
        if (typeof jQuery !== 'undefined' && jQuery.fn.tabs) {
            jQuery('.ticketTabs').tabs({
                create: function ( event, ui ) {
                    document.querySelectorAll('.ticketTabs').forEach(function(el) {
                        el.style.visibility = "visible";
                    });
                },
                activate: function (event, ui) {

                    url = new URL(window.location.href);

                    url.searchParams.set('tab', ui.newPanel[0].id);

                    window.history.replaceState(null, null, url);

                },
                load: function () {

                },
                enable: function () {

                },
                active: activeTabIndex

            });
        }

    };

    var initTicketSearchSubmit = function (url) {

        var searchForm = document.getElementById("ticketSearch");
        if (searchForm) {
            searchForm.addEventListener('submit', function (e) {
                e.preventDefault();

                var getVal = function(id) { var el = document.getElementById(id); return el ? el.value : undefined; };
                var getChecked = function(name) { var el = document.querySelector("input[name='" + name + "']:checked"); return el ? el.value : undefined; };

                var project = getVal("projectIdInput");
                var users = getVal("userSelect");
                var milestones = getVal("milestoneSelect");
                var term = getVal("termInput");
                var sprints = getVal("sprintSelect");
                var types = getVal("typeSelect");
                var priority = getVal("prioritySelect");
                var status = getVal("statusSelect");
                var sort = getVal("sortBySelect");
                var groupBy = getChecked("groupBy");
                var showTasks = getChecked("showTasks");

                var query = "?search=true";
                if (project != "" && project != undefined) {
                    query = query + "&projectId=" + project}
                if (users != "" && users != undefined) {
                    query = query + "&users=" + users}
                if (milestones != ""  && milestones != undefined) {
                    query = query + "&milestone=" + milestones}
                if (term != ""  && term != undefined) {
                    query = query + "&term=" + term;}
                if (sprints != ""  && sprints != undefined) {
                    query = query + "&sprint=" + sprints;}
                if (types != "" && types != undefined) {
                    query = query + "&type=" + types;}
                if (priority != "" && priority != undefined) {
                    query = query + "&priority=" + priority;}
                if (status != "" && status != undefined) {
                    query = query + "&status=" + status;}
                if (sort != "" && sort != undefined) {
                    query = query + "&sort=" + sort;}
                if (groupBy != "" && groupBy != undefined) {
                    query = query + "&groupBy=" + groupBy;}
                if (showTasks != "" && showTasks != undefined) {
                    query = query + "&showTasks=" + showTasks;}

                var rediredirectUrl = url + query;

                window.location.href = rediredirectUrl;

            });
        }
    };

    var initTicketSearchUrlBuilder = function (url) {

            var getVal = function(id) { var el = document.getElementById(id); return el ? el.value : undefined; };
            var getChecked = function(name) { var el = document.querySelector("input[name='" + name + "']:checked"); return el ? el.value : undefined; };

            var project = getVal("projectIdInput");
            var users = getVal("userSelect");
            var milestones = getVal("milestoneSelect");
            var term = getVal("termInput");
            var sprints = getVal("sprintSelect");
            var types = getVal("typeSelect");
            var priority = getVal("prioritySelect");
            var status = getVal("statusSelect");
            var sort = getVal("sortBySelect");
            var groupBy = getChecked("groupBy");

            var query = "?search=true";
        if (project != "" && project != undefined) {
            query = query + "&projectId=" + project}
        if (users != "" && users != undefined) {
            query = query + "&users=" + users}
        if (milestones != ""  && milestones != undefined) {
            query = query + "&milestone=" + milestones}
        if (term != ""  && term != undefined) {
            query = query + "&term=" + term;}
        if (sprints != ""  && sprints != undefined) {
            query = query + "&sprint=" + sprints;}
        if (types != "" && types != undefined) {
            query = query + "&type=" + types;}
        if (priority != "" && priority != undefined) {
            query = query + "&priority=" + priority;}
        if (status != "" && status != undefined) {
            query = query + "&status=" + status;}
        if (sort != "" && sort != undefined) {
            query = query + "&sort=" + sort;}
        if (groupBy != "" && groupBy != undefined) {
            query = query + "&groupBy=" + groupBy;}

            var rediredirectUrl = url + query;

            window.location.href = rediredirectUrl;

    };

    var setUpKanbanColumns = function () {

        document.addEventListener('DOMContentLoaded', function () {

            countTickets();
            updateSwimlaneCounts();

            document.querySelectorAll(".filterBar .row-fluid").forEach(function(el) {
                el.style.opacity = "1";
            });

            document.querySelectorAll(".sortableTicketList").forEach(function(listEl) {

                var kanbanLaneId = listEl.id;

                // Skip collapsed swimlanes - let CSS handle their height
                var swimlaneRow = listEl.closest('.kanban-swimlane-row');
                if (swimlaneRow && swimlaneRow.getAttribute('data-expanded') === 'false') {
                    return;
                }

                var height = 250;

                listEl.querySelectorAll(".column .contentInner").forEach(function (innerEl) {
                    if (innerEl.offsetHeight > height) {
                        height = innerEl.offsetHeight;
                    }
                });

                document.querySelectorAll("#" + kanbanLaneId + " .column .contentInner").forEach(function(innerEl) {
                    innerEl.style.height = height + "px";
                });

            });

        });

    }

    var initTicketKanban = function (ticketStatusListParameter) {

        var ticketStatusList = ticketStatusListParameter;

        document.querySelectorAll(".sortableTicketList.kanbanBoard .ticketBox").forEach(function (box) {
            box.addEventListener("mouseenter", function () {
                this.style.background = "var(--kanban-card-hover)";
            });
            box.addEventListener("mouseleave", function () {
                this.style.background = "var(--kanban-card-bg)";
            });
        });

        var position_updated = false;

        function serializeSortable(containerEl) {
            var items = containerEl.querySelectorAll(':scope > .moveable');
            var parts = [];
            items.forEach(function (item) {
                if (item.id) {
                    var idx = item.id.indexOf('_');
                    if (idx !== -1) {
                        parts.push(item.id.substring(0, idx) + '[]=' + item.id.substring(idx + 1));
                    }
                }
            });
            return parts.join('&');
        }

        document.querySelectorAll(".sortableTicketList").forEach(function (currentElement) {

            currentElement.querySelectorAll(".contentInner").forEach(function (contentInner) {
                if (contentInner._sortableInstance) contentInner._sortableInstance.destroy();
                contentInner._sortableInstance = new Sortable(contentInner, {
                    group: 'ticket-kanban',
                    draggable: '.moveable',
                    ghostClass: 'ui-state-highlight',
                    filter: '.portlet-toggle, input, a, select, textarea',
                    preventOnFilter: false,
                    animation: 150,
                    delay: 10,
                    delayOnTouchOnly: true,

                    onStart: function (evt) {
                        evt.item.classList.add('tilt');
                        tilt_direction(evt.item);

                        // Store original swimlane for cross-swimlane detection
                        var originalSwimlane = evt.item.closest('.sortableTicketList.kanbanBoard');
                        evt.item.dataset.originalSwimlaneId = originalSwimlane ? originalSwimlane.id : '';
                    },

                    onEnd: function (evt) {
                        evt.item.classList.remove('tilt');
                        if (evt.item._moveHandler) {
                            document.removeEventListener('mousemove', evt.item._moveHandler);
                            delete evt.item._moveHandler;
                        }

                        countTickets();
                        updateSwimlaneCounts();

                        // Update empty state for all columns after drag-and-drop
                        document.querySelectorAll(".sortableTicketList .contentInner").forEach(function(container) {
                            var hasTickets = container.querySelectorAll(".moveable").length > 0;

                            if (hasTickets) {
                                container.classList.remove("empty-column");
                                container.setAttribute("data-empty-text", "");
                                var currentLabel = container.getAttribute("aria-label") || "";
                                container.setAttribute("aria-label", currentLabel.replace("Empty column", "") + " column items");
                            } else {
                                container.classList.add("empty-column");
                                container.setAttribute("data-empty-text", "Empty");
                                container.setAttribute("aria-label", "Empty column");
                            }
                        });

                        // Detect cross-swimlane movement and update groupBy field
                        var newSwimlane = evt.item.closest('.sortableTicketList.kanbanBoard');
                        var newSwimlaneId = newSwimlane ? newSwimlane.id : '';
                        var originalSwimlaneId = evt.item.dataset.originalSwimlaneId;

                        if (originalSwimlaneId && newSwimlaneId && originalSwimlaneId !== newSwimlaneId) {
                            // Card moved to different swimlane - update the groupBy field
                            var ticketId = evt.item.id.replace('ticket_', '');
                            var newGroupValue = newSwimlaneId.replace('kanboard-', '');

                            // Map groupBy values to API field names
                            var groupByFieldMap = {
                                'milestoneid': 'milestoneid',
                                'editorId': 'editorId',
                                'priority': 'priority',
                                'storypoints': 'storypoints',
                                'sprint': 'sprint',
                                'dueDate': 'dateToFinish'
                            };

                            var groupBy = leantime.kanbanGroupBy;
                            var fieldName = groupByFieldMap[groupBy];

                            // Special handling for dueDate - calculate actual date from bucket ID
                            if (groupBy === 'dueDate') {
                                var today = new Date();
                                today.setHours(12, 0, 0, 0);

                                switch (newGroupValue) {
                                    case '0':
                                        var yesterday = new Date(today);
                                        yesterday.setDate(yesterday.getDate() - 1);
                                        newGroupValue = yesterday.toISOString().split('T')[0];
                                        break;
                                    case '1':
                                        var endOfWeek = new Date(today);
                                        var daysUntilSaturday = 6 - endOfWeek.getDay();
                                        if (daysUntilSaturday < 0) daysUntilSaturday = 0;
                                        endOfWeek.setDate(endOfWeek.getDate() + daysUntilSaturday);
                                        newGroupValue = endOfWeek.toISOString().split('T')[0];
                                        break;
                                    case '2':
                                        var nextWeekEnd = new Date(today);
                                        var daysUntilNextSaturday = 6 - nextWeekEnd.getDay() + 7;
                                        nextWeekEnd.setDate(nextWeekEnd.getDate() + daysUntilNextSaturday);
                                        newGroupValue = nextWeekEnd.toISOString().split('T')[0];
                                        break;
                                    case '3':
                                        var later = new Date(today);
                                        later.setDate(later.getDate() + 21);
                                        newGroupValue = later.toISOString().split('T')[0];
                                        break;
                                    case '4':
                                        newGroupValue = '';
                                        break;
                                }
                            }

                            if (fieldName && ticketId) {
                                var card = document.getElementById('ticket_' + ticketId);

                                // OPTIMISTIC UI UPDATE - update visuals immediately before server confirms
                                if (groupBy === 'milestoneid') {
                                    var swimlaneHeader = document.querySelector('#swimlane-row-' + newGroupValue + ' .swimlane-header-label');
                                    var newLabel = (swimlaneHeader ? swimlaneHeader.textContent : '') || leantime.i18n.__("label.no_milestone");

                                    var milestoneDropdown = card ? card.querySelector('.milestoneDropdown .dropdown-toggle .text') : null;
                                    if (milestoneDropdown) {
                                        milestoneDropdown.textContent = newLabel;
                                    }

                                    var milestoneMenuItem = card ? card.querySelector('.milestoneDropdown .dropdown-menu a[data-value^="' + ticketId + '_' + newGroupValue + '_"]') : null;
                                    var milestoneColor = '#b0b0b0';

                                    if (milestoneMenuItem && newGroupValue !== '0' && newGroupValue !== '') {
                                        var dataValue = milestoneMenuItem.getAttribute('data-value');
                                        if (dataValue) {
                                            var parts = dataValue.split('_');
                                            if (parts.length >= 3) {
                                                milestoneColor = parts.slice(2).join('_');
                                            }
                                        }
                                        if (!milestoneColor || milestoneColor === '#b0b0b0') {
                                            var inlineStyle = milestoneMenuItem.getAttribute('style');
                                            if (inlineStyle) {
                                                var colorMatch = inlineStyle.match(/background-color:\s*([^;]+)/i);
                                                if (colorMatch) {
                                                    milestoneColor = colorMatch[1].trim();
                                                }
                                            }
                                        }
                                    }

                                    var milestoneToggle = card ? card.querySelector('.milestoneDropdown .dropdown-toggle') : null;
                                    if (milestoneToggle) milestoneToggle.style.backgroundColor = milestoneColor;

                                } else if (groupBy === 'priority') {
                                    if (card) {
                                        card.className = card.className.replace(/(^|\s)priority-border-\S+/g, '');
                                        card.classList.add('priority-border-' + newGroupValue);
                                    }

                                    var priorityLabels = {'1': 'Critical', '2': 'High', '3': 'Medium', '4': 'Low', '5': 'Lowest'};
                                    var priorityDropdown = document.getElementById('priorityDropdownMenuLink' + ticketId);
                                    if (priorityDropdown) {
                                        var prioText = priorityDropdown.querySelector('span.text');
                                        if (prioText) prioText.textContent = priorityLabels[newGroupValue] || newGroupValue;
                                        priorityDropdown.classList.remove('priority-bg-1', 'priority-bg-2', 'priority-bg-3', 'priority-bg-4', 'priority-bg-5');
                                        priorityDropdown.classList.add('priority-bg-' + newGroupValue);
                                    }

                                } else if (groupBy === 'editorId') {
                                    var userDropdown = document.getElementById('userDropdownMenuLink' + ticketId);
                                    if (userDropdown) {
                                        var userImg = userDropdown.querySelector('span.text span img');
                                        if (userImg) userImg.setAttribute('src', leantime.appUrl + '/api/users?profileImage=' + newGroupValue);

                                        var swimlaneHeader = document.querySelector('#swimlane-row-' + newGroupValue + ' .swimlane-header-label');
                                        var newUserName = (swimlaneHeader ? swimlaneHeader.textContent : '') || leantime.i18n.__("label.not_assigned");
                                        var userNameEl = userDropdown.querySelector('span.text span#user' + ticketId);
                                        if (userNameEl) userNameEl.textContent = newUserName;
                                    }

                                } else if (groupBy === 'storypoints') {
                                    var storyPointLabels = {'0.5': '< 2min', '1': 'XS', '2': 'S', '3': 'M', '5': 'L', '8': 'XL', '13': 'XXL'};
                                    var effortDropdown = document.getElementById('effortDropdownMenuLink' + ticketId);
                                    if (effortDropdown) {
                                        var effortText = effortDropdown.querySelector('span.text');
                                        if (effortText) effortText.textContent = storyPointLabels[newGroupValue] || newGroupValue;
                                    }

                                } else if (groupBy === 'sprint') {
                                    var sprintDropdown = document.getElementById('sprintDropdownMenuLink' + ticketId);
                                    if (sprintDropdown) {
                                        var swimlaneHeader = document.querySelector('#swimlane-row-' + newGroupValue + ' .swimlane-header-label');
                                        var newSprintName = (swimlaneHeader ? swimlaneHeader.textContent : '') || leantime.i18n.__("label.backlog");
                                        var sprintText = sprintDropdown.querySelector('span.text');
                                        if (sprintText) sprintText.textContent = newSprintName;
                                    }

                                } else if (groupBy === 'dueDate') {
                                    var calIcon = card ? card.querySelector('.dues .fa-calendar') : null;
                                    var dateDisplay = calIcon ? calIcon.parentElement : null;
                                    if (dateDisplay && newGroupValue) {
                                        var dateParts = newGroupValue.split('-');
                                        var formattedDate = dateParts[1] + '/' + dateParts[2] + '/' + dateParts[0];
                                        // Replace first text node
                                        var textNodes = [];
                                        dateDisplay.childNodes.forEach(function(node) {
                                            if (node.nodeType === 3) textNodes.push(node);
                                        });
                                        if (textNodes.length > 0) {
                                            textNodes[0].textContent = ' ' + formattedDate;
                                        }
                                    } else if (dateDisplay && !newGroupValue) {
                                        var textNodes = [];
                                        dateDisplay.childNodes.forEach(function(node) {
                                            if (node.nodeType === 3) textNodes.push(node);
                                        });
                                        if (textNodes.length > 0) {
                                            textNodes[0].textContent = ' No due date';
                                        }
                                    }
                                }

                                leantime.toast.show({message: "To-Do Updated", style: "success"});

                                var formData = new URLSearchParams();
                                formData.append('id', ticketId);
                                formData.append(fieldName, newGroupValue);

                                fetch(leantime.appUrl + '/api/tickets', {
                                    method: 'PATCH',
                                    body: formData,
                                    credentials: 'include',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                }).then(function(response) {
                                    if (!response.ok) throw new Error('Request failed');
                                }).catch(function() {
                                    leantime.toast.show({message: leantime.i18n.__("short_notifications.not_saved") || "Error updating ticket", style: "error"});
                                    location.reload();
                                });
                            }
                        }

                        // Clean up stored data
                        delete evt.item.dataset.originalSwimlaneId;

                        // Get the new parent swimlane for status update
                        var targetSwimlane = evt.item.closest('.sortableTicketList');

                        var statusPostData = {
                            action: "kanbanSort",
                            payload: {},
                            handler: evt.item.id
                        };

                        for (var i = 0; i < ticketStatusList.length; i++) {
                            var col = targetSwimlane.querySelector(".contentInner.status_" + ticketStatusList[i]);
                            if (col) {
                                statusPostData.payload[ticketStatusList[i]] = serializeSortable(col);
                            }
                        }

                        var sortFormData = new URLSearchParams();
                        sortFormData.append('action', statusPostData.action);
                        sortFormData.append('handler', statusPostData.handler);
                        for (var statusKey in statusPostData.payload) {
                            sortFormData.append('payload[' + statusKey + ']', statusPostData.payload[statusKey]);
                        }

                        fetch(leantime.appUrl + '/api/tickets', {
                            method: 'POST',
                            body: sortFormData,
                            credentials: 'include',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }).then(function (response) { return response.json(); })
                        .then(function (response) {
                            leantime.handleAsyncResponse(response);
                        });
                    }
                });
            });

        });

        function tilt_direction(item)
        {
            var left_pos = item.getBoundingClientRect().left,
                move_handler = function (e) {

                    if ((e.pageX + 5) > left_pos) {
                        item.classList.add("right");
                        item.classList.remove("left");
                    } else if (e.pageX < (left_pos + 5)) {
                        item.classList.add("left");
                        item.classList.remove("right");
                    } else {
                        item.classList.remove("left");
                        item.classList.remove("right");
                    }

                    left_pos = e.pageX;

                };
            document.addEventListener("mousemove", move_handler);
            item._moveHandler = move_handler;
        }

        document.querySelectorAll(".portlet").forEach(function(portlet) {
            portlet.classList.add("ui-widget", "ui-widget-content", "ui-helper-clearfix", "ui-corner-all");
            portlet.querySelectorAll(".portlet-header").forEach(function(header) {
                header.classList.add("ui-widget-header", "ui-corner-all");
                header.insertAdjacentHTML("afterbegin", "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
            });
        });

        document.querySelectorAll(".portlet-toggle").forEach(function(toggle) {
            toggle.addEventListener("click", function () {
                this.classList.toggle("ui-icon-minusthick");
                this.classList.toggle("ui-icon-plusthick");
                var portlet = this.closest(".portlet");
                if (portlet) {
                    portlet.querySelectorAll(".portlet-content").forEach(function(content) {
                        content.style.display = content.style.display === 'none' ? '' : 'none';
                    });
                }
            });
        });

    };

    var initTicketsTable = function (groupBy) {

        function isNumeric(n)
        {
            return !isNaN(parseFloat(n)) && isFinite(n);
        }

        // DataTables requires jQuery - wrap in DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function () {

            var size = 100;
            var columnIndex = false;


            var defaultOrder = [];

            // Helper to extract value from HTML string that may contain an input
            function extractInputValue(htmlStr) {
                if (typeof htmlStr === 'string' && htmlStr.indexOf('<') !== -1) {
                    var tmp = document.createElement('div');
                    tmp.innerHTML = htmlStr;
                    var input = tmp.querySelector('input');
                    return input ? input.value : htmlStr;
                }
                return htmlStr;
            }

            // DataTables init must use jQuery
            var allTickets = jQuery(".ticketTable").DataTable({
                "language": {
                    "decimal":        leantime.i18n.__("datatables.decimal"),
                    "emptyTable":     leantime.i18n.__("datatables.emptyTable"),
                    "info":           leantime.i18n.__("datatables.info"),
                    "infoEmpty":      leantime.i18n.__("datatables.infoEmpty"),
                    "infoFiltered":   leantime.i18n.__("datatables.infoFiltered"),
                    "infoPostFix":    leantime.i18n.__("datatables.infoPostFix"),
                    "thousands":      leantime.i18n.__("datatables.thousands"),
                    "lengthMenu":     leantime.i18n.__("datatables.lengthMenu"),
                    "loadingRecords": leantime.i18n.__("datatables.loadingRecords"),
                    "processing":     leantime.i18n.__("datatables.processing"),
                    "search":         leantime.i18n.__("datatables.search"),
                    "zeroRecords":    leantime.i18n.__("datatables.zeroRecords"),
                    "paginate": {
                        "first":      leantime.i18n.__("datatables.first"),
                        "last":       leantime.i18n.__("datatables.last"),
                        "next":       leantime.i18n.__("datatables.next"),
                        "previous":   leantime.i18n.__("datatables.previous"),
                    },
                    "aria": {
                        "sortAscending":  leantime.i18n.__("datatables.sortAscending"),
                        "sortDescending":leantime.i18n.__("datatables.sortDescending"),
                    },
                    "buttons": {
                        colvis: leantime.i18n.__("datatables.buttons.colvis"),
                        csv: leantime.i18n.__("datatables.buttons.download")
                    }

                },
                "dom": '<"top">rt<"bottom">p<"clear">',
                "searching": false,
                "stateSave": true,
                "displayLength":100,
                "order": defaultOrder,
                "columnDefs": [
                        { "visible": false, "targets": 10 },
                        { "visible": false, "targets": 11 },
                        { "target": "no-sort", "orderable": false},
                    ],
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;

                    // converting to interger to find total
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                        i : 0;
                    };

                    // computing column Total of the complete result


                    var plannedHours = api
                        .column(10)
                        .data()
                        .reduce(function (a, b) {

                            if (isNumeric(a) === false) {
                                a = extractInputValue(a);
                            }

                            if (isNumeric(b) === false) {
                                b = extractInputValue(b);
                            }

                            if (isNaN(a)) {
                                a = 0; }
                            if (isNaN(b)) {
                                b = 0; }


                            return parseFloat(a) + parseFloat(b);
                        }, 0);

                    var hoursLeft = api
                        .column(11)
                        .data()
                        .reduce(function (a, b) {

                            if (isNumeric(a) === false) {
                                a = extractInputValue(a);
                            }

                            if (isNumeric(b) === false) {
                                b = extractInputValue(b);
                            }

                            if (isNaN(a)) {
                                a = 0; }
                            if (isNaN(b)) {
                                b = 0; }


                            return parseFloat(a) + parseFloat(b);
                        }, 0);

                    var loggedHours = api
                        .column(12)
                        .data()
                        .reduce(function (a, b) {
                            return parseFloat(a) + parseFloat(b);
                        }, 0);


                    // Update footer by showing the total with the reference of the column index
                    var col9Footer = api.column(9).footer();
                    if (col9Footer) col9Footer.innerHTML = 'Total';
                    var col10Footer = api.column(10).footer();
                    if (col10Footer) col10Footer.innerHTML = plannedHours;
                    var col11Footer = api.column(11).footer();
                    if (col11Footer) col11Footer.innerHTML = hoursLeft;
                    var col12Footer = api.column(12).footer();
                    if (col12Footer) col12Footer.innerHTML = loggedHours;

                },

            });

            // DataTables Buttons API requires jQuery
            var buttons = new jQuery.fn.dataTable.Buttons(allTickets.table(0), {
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: leantime.i18n.__("label.filename_fileexport"),
                        charset: 'utf-8',
                        bom: true,
                        exportOptions: {
                            format: {
                                body: function ( data, row, column, node ) {

                                    if ( typeof node.dataset.order !== 'undefined' && node.dataset.order !== undefined) {
                                        data = node.dataset.order;
                                    }
                                    return data;
                                }
                            }
                        }
                },
                {
                    extend: 'colvis',
                    columns: ':not(.noVis)'
                }
                ]
            }).container().appendTo(document.getElementById('tableButtons'));

            // When the column visibility changes on the firs table, also change it on // the others tables.
            allTickets.table(0).on(
                'column-visibility',
                function ( e, settings, colIdx, visibility ) {

                    // Toggle the visibility
                    for (var i = 1; i < allTickets.tables().context.length; i++) {
                        allTickets.tables(i).column(colIdx).visible(visibility);
                    }

                    allTickets.draw();

                }
            );

            document.querySelectorAll('.ticketTable input').forEach(function (input) {
                input.addEventListener('change', function () {
                    this.parentElement.setAttribute('data-order', this.value);
                    allTickets.draw();
                });
            });
        });
    };

    var initTicketsList = function (groupBy) {

        // DataTables requires jQuery - wrap in DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function () {

            var size = 50;
            var columnIndex = false;
            var collapsedGroups = {};

            var defaultOrder = [];


            // DataTables init must use jQuery
            var allTickets = jQuery(".listStyleTable").DataTable({
                "language": {
                    "decimal":        leantime.i18n.__("datatables.decimal"),
                    "emptyTable":     leantime.i18n.__("datatables.emptyTable"),
                    "info":           leantime.i18n.__("datatables.info"),
                    "infoEmpty":      leantime.i18n.__("datatables.infoEmpty"),
                    "infoFiltered":   leantime.i18n.__("datatables.infoFiltered"),
                    "infoPostFix":    leantime.i18n.__("datatables.infoPostFix"),
                    "thousands":      leantime.i18n.__("datatables.thousands"),
                    "lengthMenu":     leantime.i18n.__("datatables.lengthMenu"),
                    "loadingRecords": leantime.i18n.__("datatables.loadingRecords"),
                    "processing":     leantime.i18n.__("datatables.processing"),
                    "search":         leantime.i18n.__("datatables.search"),
                    "zeroRecords":    leantime.i18n.__("datatables.zeroRecords"),
                    "paginate": {
                        "first":      leantime.i18n.__("datatables.first"),
                        "last":       leantime.i18n.__("datatables.last"),
                        "next":       leantime.i18n.__("datatables.next"),
                        "previous":   leantime.i18n.__("datatables.previous"),
                    },
                    "aria": {
                        "sortAscending":  leantime.i18n.__("datatables.sortAscending"),
                        "sortDescending":leantime.i18n.__("datatables.sortDescending"),
                    },
                    "buttons": {
                        colvis: leantime.i18n.__("datatables.buttons.colvis"),
                        csv: leantime.i18n.__("datatables.buttons.download")
                    }

                },
                "dom": '<"top">rt<"bottom"<"center"p>><"clear">',
                "searching": false,
                "stateSave": true,
                "displayLength":25,
                "order": defaultOrder,
                "fnDrawCallback": function (oSettings) {

                    if (oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
                        var paginateEl = oSettings.nTableWrapper.querySelector('.dataTables_paginate');
                        if (paginateEl) paginateEl.style.display = 'none';
                    } else {
                        var paginateEl = oSettings.nTableWrapper.querySelector('.dataTables_paginate');
                        if (paginateEl) paginateEl.style.display = '';
                    }

                }
            });


        });
    };

    var initMilestoneTable = function (groupBy) {

        function isNumeric(n)
        {
            return !isNaN(parseFloat(n)) && isFinite(n);
        }

        // DataTables requires jQuery - wrap in DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function () {

            var size = 100;
            var columnIndex = false;


            var defaultOrder = [];

            // DataTables init must use jQuery
            var allTickets = jQuery(".ticketTable").DataTable({
                "language": {
                    "decimal":        leantime.i18n.__("datatables.decimal"),
                    "emptyTable":     leantime.i18n.__("datatables.emptyTable"),
                    "info":           leantime.i18n.__("datatables.info"),
                    "infoEmpty":      leantime.i18n.__("datatables.infoEmpty"),
                    "infoFiltered":   leantime.i18n.__("datatables.infoFiltered"),
                    "infoPostFix":    leantime.i18n.__("datatables.infoPostFix"),
                    "thousands":      leantime.i18n.__("datatables.thousands"),
                    "lengthMenu":     leantime.i18n.__("datatables.lengthMenu"),
                    "loadingRecords": leantime.i18n.__("datatables.loadingRecords"),
                    "processing":     leantime.i18n.__("datatables.processing"),
                    "search":         leantime.i18n.__("datatables.search"),
                    "zeroRecords":    leantime.i18n.__("datatables.zeroRecords"),
                    "paginate": {
                        "first":      leantime.i18n.__("datatables.first"),
                        "last":       leantime.i18n.__("datatables.last"),
                        "next":       leantime.i18n.__("datatables.next"),
                        "previous":   leantime.i18n.__("datatables.previous"),
                    },
                    "aria": {
                        "sortAscending":  leantime.i18n.__("datatables.sortAscending"),
                        "sortDescending":leantime.i18n.__("datatables.sortDescending"),
                    },
                    "buttons": {
                        colvis: leantime.i18n.__("datatables.buttons.colvis"),
                        csv: leantime.i18n.__("datatables.buttons.download")
                    }

                },
                "dom": '<"top">rt<"bottom"><"clear">',
                "searching": false,
                "stateSave": true,
                "displayLength":100,
                "order": defaultOrder,
                "columnDefs": [
                    { "visible": false, "targets": 7 },
                    { "visible": false, "targets": 8 },
                    { "target": "no-sort", "orderable": false},
                ]

            });

            // DataTables Buttons API requires jQuery
            var buttons = new jQuery.fn.dataTable.Buttons(allTickets.table(0), {
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: leantime.i18n.__("label.filename_fileexport"),
                        charset: 'utf-8',
                        bom: true,
                        exportOptions: {
                            format: {
                                body: function ( data, row, column, node ) {

                                    if ( typeof node.dataset.order !== 'undefined' && node.dataset.order !== undefined) {
                                        data = node.dataset.order;
                                    }
                                    return data;
                                }
                            }
                        }
                },
                    {
                        extend: 'colvis',
                        columns: ':not(.noVis)'
                }
                ]
            }).container().appendTo(document.getElementById('tableButtons'));

            // When the column visibility changes on the firs table, also change it on // the others tables.
            allTickets.table(0).on(
                'column-visibility',
                function ( e, settings, colIdx, visibility ) {

                    // Toggle the visibility
                    for (var i = 1; i < allTickets.tables().context.length; i++) {
                        allTickets.tables(i).column(colIdx).visible(visibility);
                    }

                    allTickets.draw();

                }
            );

            document.querySelectorAll('.ticketTable input').forEach(function (input) {
                input.addEventListener('change', function () {
                    this.parentElement.setAttribute('data-order', this.value);
                    allTickets.draw();
                });
            });

        });
    };

    var loadTicketToContainer = function (id, element) {

        var containerEl = typeof element === 'string' ? document.querySelector(element) : element;

        // Save and remove TinyMCE editors if present (TinyMCE requires jQuery)
        if (typeof jQuery !== 'undefined') {
            var editors = document.querySelectorAll('textarea.complexEditor');
            if (editors.length > 0 && jQuery('textarea.complexEditor').tinymce() !== null) {
                jQuery('textarea.complexEditor').tinymce().save();
                jQuery('textarea.complexEditor').tinymce().remove();
            }
        }

        document.querySelectorAll(".ticketRows").forEach(function(row) {
            row.classList.remove("active");
        });
        var activeRow = document.getElementById("row-" + id);
        if (activeRow) activeRow.classList.add("active");

        containerEl.innerHTML = "<div class='center'><img src='" + leantime.appUrl + "/dist/images/svg/loading-animation.svg' width='100px' /></div>";

        function formSubmitHandler(containerEl)
        {

            containerEl.querySelectorAll("form").forEach(function (form) {

                form.addEventListener("submit", function (e) {

                    e.preventDefault();

                    // Save and remove TinyMCE editors if present
                    if (typeof jQuery !== 'undefined') {
                        var editors = document.querySelectorAll('textarea.complexEditor');
                        if (editors.length > 0 && jQuery('textarea.complexEditor').tinymce() !== null) {
                            jQuery('textarea.complexEditor').tinymce().save();
                            jQuery('textarea.complexEditor').tinymce().remove();
                        }
                    }

                    containerEl.innerHTML = "<div class='center'><img src='" + leantime.appUrl + "/dist/images/svg/loading-animation.svg' width='100px'/></div>";

                    var formData = new FormData(this);
                    var actionUrl = this.getAttribute("action");

                    fetch(actionUrl, {
                        method: "POST",
                        body: formData,
                        credentials: 'include',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function (response) { return response.text(); })
                    .then(function (data) {

                            containerEl.innerHTML = data;
                            formSubmitHandler(containerEl);

                    });
                });

            });
        }



        fetch(leantime.appUrl + '/tickets/showTicket/' + id, {
            credentials: 'include',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) { return response.text(); })
        .then(function (data) {

            containerEl.innerHTML = data;
            formSubmitHandler(containerEl);

        });

    };

    var initTagsInput = function ( ) {
        // tagsInput() is a jQuery plugin - must use jQuery
        if (typeof jQuery !== 'undefined' && jQuery.fn.tagsInput) {
            jQuery("#tags").tagsInput({
                'autocomplete_url': leantime.appUrl + '/api/tags',
            });
        }

        var tagsTagEl = document.getElementById("tags_tag");
        if (tagsTagEl) {
            tagsTagEl.addEventListener("focusout", function () {
                var tag = this.value;

                if (tag != '') {
                    // addTag is a tagsInput jQuery plugin method
                    if (typeof jQuery !== 'undefined') {
                        jQuery("#tags").addTag(tag);
                    }
                }
            });
        }

    };

    var addCommentTimesheetContent = function (commentId, taskId) {
        var commentEl = document.getElementById("commentText-" + commentId);
        var content = "Discussion on To-Do #" + taskId + ":"
        + "\n\r"
        + (commentEl ? commentEl.textContent : '');

        var timesheetLink = document.querySelector('li a[href*="timesheet"]');
        if (timesheetLink) timesheetLink.click();

        var descriptionEl = document.querySelector("#timesheet #description");
        if (descriptionEl) descriptionEl.value = content;

    };

    // Make public what you want to have public, everything else is private
    return {
        toggleFilterBar: toggleFilterBar,

        initGanttChart:initGanttChart,
        updateRemainingHours:updateRemainingHours,
        updatePlannedHours:updatePlannedHours,
        initTimeSheetChart:initTimeSheetChart,
        initTicketTabs:initTicketTabs,
        initTicketSearchSubmit:initTicketSearchSubmit,
        initTicketKanban:initTicketKanban,
        initTicketsTable:initTicketsTable,
        initEffortDropdown:initEffortDropdown,
        initPriorityDropdown:initPriorityDropdown,
        initMilestoneDropdown:initMilestoneDropdown,
        initStatusDropdown:initStatusDropdown,
        initUserDropdown:initUserDropdown,
        initSprintDropdown:initSprintDropdown,
        initToolTips:initToolTips,
        initTagsInput:initTagsInput,
        initMilestoneDatesAsyncUpdate:initMilestoneDatesAsyncUpdate,
        initAsyncInputChange:initAsyncInputChange,
        initDueDateTimePickers:initDueDateTimePickers,
        setUpKanbanColumns:setUpKanbanColumns,
        addCommentTimesheetContent:addCommentTimesheetContent,
        initMilestoneTable:initMilestoneTable,
        initMilestoneDates:_initMilestoneDates,
        initTicketsList:initTicketsList,
        loadTicketToContainer:loadTicketToContainer,
        initTicketSearchUrlBuilder:initTicketSearchUrlBuilder,
        initSprintDates:initSprintDates,
        initSimpleColorPicker:initSimpleColorPicker,
        openTicketModalManually: function (url) {
            if (leantime.modals && leantime.modals.openByUrl) {
                leantime.modals.openByUrl(url);
            }
        }
    };
})();
