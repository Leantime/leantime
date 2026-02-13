leantime.ticketsController = (function () {

    //Variables


    //Functions
    function countTickets()
    {

        let ticketCounts = [];
        jQuery(".sortableTicketList").each(function (indexList) {
            jQuery(this).find(".column").each(function (indexCol) {

                if (ticketCounts[indexCol] === undefined) {
                    ticketCounts[indexCol] = 0;
                }

                var counting = jQuery(this).find('.moveable').length;
                ticketCounts[indexCol] += counting;

            });

        });

        jQuery(".widgettitle .count").each(function (index) {
            jQuery(this).text(ticketCounts[index]);
        });

    }

    /**
     * Update swimlane row counts after card movement
     * Counts tickets in each swimlane row and updates the count badges
     */
    function updateSwimlaneCounts()
    {
        jQuery(".kanban-swimlane-row").each(function() {
            var $row = jQuery(this);
            var swimlaneId = $row.attr('id');

            if (!swimlaneId) return;

            // Extract the swimlane identifier from the row ID (e.g., "swimlane-row-3" -> "3")
            var swimlaneKey = swimlaneId.replace('swimlane-row-', '');

            // Find the swimlane content area and count tickets
            var $content = $row.find('.kanban-swimlane-content');
            var ticketCount = $content.find('.moveable').length;

            // Update all count badges in this swimlane's sidebar
            var $sidebar = $row.find('.kanban-swimlane-sidebar');
            $sidebar.find('.kanban-lane-count').text(ticketCount);

            // Update the aria-label for accessibility
            var currentLabel = $sidebar.attr('aria-label') || '';
            var newLabel = currentLabel.replace(/\d+ tasks/, ticketCount + ' tasks');
            $sidebar.attr('aria-label', newLabel);
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
        jQuery(".kanban-swimlane-row").each(function() {
            var $row = jQuery(this);
            var $content = $row.find('.kanban-swimlane-content');
            var $progressBar = $row.find('.micro-progress-bar .progress-segments');

            if (!$progressBar.length) return;

            // Count tickets per status in this swimlane
            var statusCounts = {};
            var totalCount = 0;

            $content.find('.contentInner').each(function() {
                var $column = jQuery(this);
                var classAttr = $column.attr('class') || '';
                var statusMatch = classAttr.match(/status_(-?\d+)/);
                if (statusMatch) {
                    var statusId = statusMatch[1];
                    var ticketCount = $column.find('.moveable').length;
                    if (ticketCount > 0) {
                        statusCounts[statusId] = ticketCount;
                        totalCount += ticketCount;
                    }
                }
            });

            // Get existing segments and their status IDs
            var $segments = $progressBar.find('.status-segment');

            if (totalCount === 0) {
                // No tickets - hide all segments
                $segments.css('flex', '0 1 0%').find('.segment-count').text('');
                $segments.attr('data-tippy-content', '');
                return;
            }

            // Update each segment's flex-grow proportionally
            $segments.each(function() {
                var $segment = jQuery(this);
                var classAttr = $segment.attr('class');
                if (!classAttr) return;

                var statusMatch = classAttr.match(/status-(-?\d+)/);
                if (statusMatch) {
                    var statusId = statusMatch[1];
                    var count = statusCounts[statusId] || 0;
                    var percentage = (count / totalCount) * 100;

                    // Use flex-grow proportionally so segments fill 100% without gaps
                    $segment.css('flex', percentage + ' 1 0%');

                    // Update count text
                    var $countSpan = $segment.find('.segment-count');
                    if ($countSpan.length) {
                        $countSpan.text(count > 0 ? count : '');
                    }

                    // Update tooltip - only show if count > 0
                    var currentTooltip = $segment.attr('data-tippy-content') || '';
                    var labelMatch = currentTooltip.match(/^([^:]+):/);
                    var label = labelMatch ? labelMatch[1] : 'Status ' + statusId;
                    $segment.attr('data-tippy-content', count > 0 ? label + ': ' + count : '');
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
        var $card = jQuery("#ticket_" + ticketId);

        if (!$card.length) {
            console.warn("Card not found for ticket ID:", ticketId);
            return;
        }

        // Get current status from card's column
        var $currentColumn = $card.closest('.contentInner');
        var classAttr = $currentColumn.attr('class') || '';
        var statusMatch = classAttr.match(/status_(\d+)/);

        if (!statusMatch) {
            console.warn("Could not determine status for ticket:", ticketId);
            location.reload(); // Fallback to reload
            return;
        }

        var statusId = statusMatch[1];

        // Find target swimlane column
        var $targetColumn = jQuery("#kanboard-" + newSwimlaneValue + " .contentInner.status_" + statusId);

        if (!$targetColumn.length) {
            console.warn("Target swimlane not found:", newSwimlaneValue, statusId);
            location.reload(); // Fallback to reload
            return;
        }

        // Don't move if already in correct location
        if ($currentColumn[0] === $targetColumn[0]) {
            return;
        }

        // Add exit animation
        $card.addClass('card-moving-out');

        setTimeout(function() {
            // Move card to new swimlane
            $card.detach().appendTo($targetColumn);

            // Add entrance animation
            $card.removeClass('card-moving-out').addClass('card-moving-in');

            // Update ticket counts
            countTickets();
            updateSwimlaneCounts();

            // Refresh sortable to recalculate positions
            if ($targetColumn.sortable) {
                $targetColumn.sortable('refresh');
            }

            // Remove animation class after transition
            setTimeout(function() {
                $card.removeClass('card-moving-in');
            }, 300);
        }, 200);
    };


    var updateRemainingHours = function (element, id) {
        var value = jQuery(element).val();
        leantime.ticketsRepository.updateRemainingHours(
            id,
            value,
            function () {
                jQuery.growl({message: leantime.i18n.__("short_notifications.remaining_hours_updated"), style: "success"});
            }
        );

    };

    var updatePlannedHours = function (element, id) {
        var value = jQuery(element).val();
        leantime.ticketsRepository.updatePlannedHours(
            id,
            value,
            function () {
                jQuery.growl({message: leantime.i18n.__("short_notifications.planned_hours_updated"), style: "success"});
            }
        );

    };


    var toggleFilterBar = function () {
        jQuery(".filterBar").toggle();

    };

    var initGanttChart = function (tasks, viewMode, readonly) {

        function htmlEntities(str)
        {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        jQuery(document).ready(
            function () {

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
                                var dateObject = new Date(end_date);
                                var dateTime = jQuery.datepicker.formatDate(leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),  new Date(end_date));

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

                                // POST to server using $.post or $.ajax
                                jQuery.ajax({
                                    type: 'POST',
                                    url: leantime.appUrl + '/api/tickets',
                                    data: statusPostData

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
                                var dateObject = new Date(end_date);
                                var dateTime = jQuery.datepicker.formatDate(leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),  new Date(end_date));

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

                jQuery("#ganttTimeControl").on(
                    "click",
                    "a",
                    function () {

                        var $btn = jQuery(this);
                        var mode = $btn.attr("data-value");
                        gantt_chart.change_view_mode(mode);
                        $btn.parent().parent().find('a').removeClass('active');
                        $btn.addClass('active');
                        var label = $btn.text();
                        jQuery(".viewText").text(label);
                    }
                );

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
                if (jQuery(i).attr('readonly')) {
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

                        var date = jQuery(this).val();
                        var id = jQuery(this).attr("data-id");

                        var toDatePicker = jQuery(".toDateTicket-" + id);
                        toDatePicker.datepicker("option", "minDate", getDate(this));

                        var dateTo = jQuery(".toDateTicket-" + id).val();

                        leantime.ticketsRepository.updateEditFromDates(id, date, function() {
                            jQuery.growl({message: leantime.i18n.__("short_notifications.date_updated"), style: "success"});
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

                        var id = jQuery(this).attr("data-id");
                        var fromDateTicket = jQuery(".fromDateTicket-" + id);
                        fromDateTicket.datepicker("option", "maxDate", getDate(this));

                        var date = jQuery(this).val();

                        var dateFrom = jQuery(".fromDateTicket-" + id).val();

                        leantime.ticketsRepository.updateEditToDates(id, date, function() {
                            jQuery.growl({message: leantime.i18n.__("short_notifications.date_updated"), style: "success"});
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
        jQuery('[data-toggle="tooltip"]').tooltip();
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

        jQuery(".effortDropdown .dropdown-menu a").unbind().on("click", function () {

            var dataValue = jQuery(this).attr("data-value").split("_");

            if (dataValue.length === 2) {
                var ticketId = dataValue[0];
                var effortId = dataValue[1];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl + '/api/tickets',
                        data:
                            {
                                id: ticketId,
                                storypoints: effortId
                        }
                    }
                ).done(
                    function () {
                        jQuery("#effortDropdownMenuLink" + ticketId + " span.text").text(storyPointLabels[effortId]);
                        jQuery.growl({message: leantime.i18n.__("short_notifications.effort_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by effort
                        if (leantime.kanbanGroupBy === 'storypoints') {
                            moveCardToSwimlane(ticketId, effortId);
                        }

                    }
                );
            } else {
                console.log("Ticket Controller: Effort data value not set correctly");
            }
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

        jQuery(".priorityDropdown .dropdown-menu a").unbind().on("click", function () {

            var dataValue = jQuery(this).attr("data-value").split("_");

            if (dataValue.length === 2) {
                var ticketId = dataValue[0];
                var priorityId = dataValue[1];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl + '/api/tickets',
                        data:
                            {
                                id: ticketId,
                                priority: priorityId
                        }
                    }
                ).done(
                    function () {
                        jQuery("#priorityDropdownMenuLink" + ticketId + " span.text").text(priorityLabels[priorityId]);
                        jQuery("#priorityDropdownMenuLink" + ticketId + "").removeClass("priority-bg-1 priority-bg-2 priority-bg-3 priority-bg-4 priority-bg-5");
                        jQuery("#priorityDropdownMenuLink" + ticketId + "").addClass("priority-bg-" + priorityId);

                        jQuery("#priorityDropdownMenuLink" + ticketId + "").parents(".ticketBox").removeClass("priority-border-1 priority-border-2 priority-border-3 priority-border-4 priority-border-5");
                        jQuery("#priorityDropdownMenuLink" + ticketId + "").parents(".ticketBox").addClass("priority-border-" + priorityId);


                        jQuery.growl({message: leantime.i18n.__("short_notifications.priority_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by priority
                        if (leantime.kanbanGroupBy === 'priority') {
                            moveCardToSwimlane(ticketId, priorityId);
                        }

                    }
                );
            } else {
                console.log("Ticket Controller: Priority data value not set correctly");
            }
        });

    };

    var initMilestoneDropdown = function () {

        jQuery(".milestoneDropdown .dropdown-menu a").unbind().on("click", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length === 3) {
                var ticketId = dataValue[0];
                var milestoneId = dataValue[1];
                var color = dataValue[2];

                jQuery("#milestoneDropdownMenuLink" + ticketId + " span.text").append(" ...");

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl + '/api/tickets',
                        data:
                            {
                                id : ticketId,
                                milestoneid:milestoneId
                        }
                        }
                ).done(
                    function () {
                        jQuery("#milestoneDropdownMenuLink" + ticketId + " span.text").text(dataLabel);
                        jQuery("#milestoneDropdownMenuLink" + ticketId).css("backgroundColor", color);
                        jQuery.growl({message: leantime.i18n.__("short_notifications.milestone_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by milestone
                        if (leantime.kanbanGroupBy === 'milestoneid') {
                            moveCardToSwimlane(ticketId, milestoneId);
                        }
                    }
                );
            }
        });
    };

    var initStatusDropdown = function () {

        jQuery(".statusDropdown .dropdown-menu a").unbind().on("click", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length == 3) {
                var ticketId = dataValue[0];
                var statusId = dataValue[1];
                var className = dataValue[2];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl + '/api/tickets',
                        data:
                            {
                                id : ticketId,
                                status:statusId
                        }
                        }
                ).done(
                    function (response) {
                        jQuery("#statusDropdownMenuLink" + ticketId + " span.text").text(dataLabel);
                        jQuery("#statusDropdownMenuLink" + ticketId).removeClass().addClass(className + " dropdown-toggle f-left status ");
                        jQuery.growl({message: leantime.i18n.__("short_notifications.status_updated"), style: "success"});

                        leantime.handleAsyncResponse(response);

                    }
                );
            }
        });

    };

    var initUserDropdown = function () {

        jQuery(".userDropdown .dropdown-menu a").unbind().on("click", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length === 3) {
                var ticketId = dataValue[0];
                var userId = dataValue[1];
                var profileImageId = dataValue[2];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl + '/api/tickets',
                        data:
                            {
                                id : ticketId,
                                editorId:userId
                        }
                        }
                ).done(
                    function () {
                        jQuery("#userDropdownMenuLink" + ticketId + " span.text span#userImage" + ticketId + " img").attr("src", leantime.appUrl + "/api/users?profileImage=" + userId);
                        jQuery("#userDropdownMenuLink" + ticketId + " span.text span#user" + ticketId).text(dataLabel);
                        jQuery.growl({message: leantime.i18n.__("short_notifications.user_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by user
                        if (leantime.kanbanGroupBy === 'editorId') {
                            moveCardToSwimlane(ticketId, userId);
                        }
                    }
                );
            }
        });
    };

    var initAsyncInputChange = function () {

        jQuery(".asyncInputUpdate").on("change", function () {
            var dataLabel = jQuery(this).attr('data-label').split("-");

            if (dataLabel.length == 2) {
                var fieldName = dataLabel[0];
                var entityId = dataLabel[1];
                var value = jQuery(this).val();

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl + '/api/tickets',
                        data:
                            {
                                id : entityId,
                                [fieldName]:value,

                        }
                    }
                ).done(
                    function () {
                        jQuery.growl({message: leantime.i18n.__("notifications.subtask_saved"), style: "success"});
                    }
                );
            }

        });
    };

    var initSprintDropdown = function () {

        jQuery(".sprintDropdown .dropdown-menu a").unbind().on("click", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

            if (dataValue.length == 2) {
                var ticketId = dataValue[0];
                var sprintId = dataValue[1];

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl + '/api/tickets',
                        data:
                            {
                                id : ticketId,
                                sprint:sprintId
                        }
                        }
                ).done(
                    function () {
                        jQuery("#sprintDropdownMenuLink" + ticketId + " span.text").text(dataLabel);
                        jQuery.growl({message: leantime.i18n.__("short_notifications.sprint_updated"), style: "success"});

                        // Move card to correct swimlane if grouped by sprint
                        if (leantime.kanbanGroupBy === 'sprint') {
                            moveCardToSwimlane(ticketId, sprintId);
                        }
                    }
                );
            }
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
        jQuery(".date-picker-form-control .reset-button").on('click', function () {
            // Ticket id for api patch call
            const id = jQuery(this).attr("data-id");

            // Update date input to have "text-anytime" instead of old date
            jQuery("#due-date-picker-"+id).val(leantime.i18n.__("text.anytime"));

            // Set date to null to reset
            leantime.ticketsRepository.updateDueDates(id, null, function () {
                // Notify user that due date is updated
                jQuery.growl({message: leantime.i18n.__("short_notifications.duedate_updated"), style: "success"});
            });
        });

        leantime.dateController.initDatePicker(".quickDueDates, .duedates", function(date, instance) {
            //TODO: Update to use htmx, this is awful
            var day = instance.currentDay;
            var month = instance.currentMonth;
            var year = instance.currentYear;

            var dateObject = new Date(year, month, day);
            var parsed = jQuery.datepicker.formatDate(leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"), dateObject);

            var id = jQuery(this).attr("data-id");

            leantime.ticketsRepository.updateDueDates(id, parsed, function () {
                jQuery.growl({message: leantime.i18n.__("short_notifications.duedate_updated"), style: "success"});
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
        jQuery(".ticketBox").each(function (index) {

            var value = jQuery(this).find(".statusDropdown > a").attr("class");

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

                jQuery(this).css("borderLeft", "5px solid " + color);

                if (currentBox != null) {
                    if (jQuery(this).attr("data-val") == currentBox) {
                        jQuery("#ticket_" + currentBox + " .ticketBox").animate({backgroundColor: color}, 'fast').animate({backgroundColor: "#fff"}, 'slow');
                    }
                }
            }

        });

    };

    var initTicketTabs = function () {

        jQuery(document).ready(function () {


            let url = new URL(window.location.href);
            let tab = url.searchParams.get("tab");

            let activeTabIndex = 0;
            if (tab) {
                activeTabIndex = jQuery('.ticketTabs').find('a[href="#' + tab + '"]').parent().index();
            }

            jQuery('.ticketTabs').tabs({
                create: function ( event, ui ) {
                    jQuery('.ticketTabs').css("visibility", "visible");

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


        });

    };

    var initTicketSearchSubmit = function (url) {

        jQuery("#ticketSearch").on('submit', function (e) {
            e.preventDefault();

            var project = jQuery("#projectIdInput").val();
            var users = jQuery("#userSelect").val();
            var milestones = jQuery("#milestoneSelect").val();
            var term = jQuery("#termInput").val();
            var sprints = jQuery("#sprintSelect").val();
            var types = jQuery("#typeSelect").val();
            var priority = jQuery("#prioritySelect").val();
            var status = jQuery("#statusSelect").val();
            var sort = jQuery("#sortBySelect").val();
            var groupBy = jQuery("input[name='groupBy']:checked").val();
            var showTasks = jQuery("input[name='showTasks']:checked").val();

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
    };

    var initTicketSearchUrlBuilder = function (url) {

            var project = jQuery("#projectIdInput").val();
            var users = jQuery("#userSelect").val();
            var milestones = jQuery("#milestoneSelect").val();
            var term = jQuery("#termInput").val();
            var sprints = jQuery("#sprintSelect").val();
            var types = jQuery("#typeSelect").val();
            var priority = jQuery("#prioritySelect").val();
            var status = jQuery("#statusSelect").val();
            var sort = jQuery("#sortBySelect").val();
            var groupBy = jQuery("input[name='groupBy']:checked").val();

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

        jQuery(document).ready(function () {

            countTickets();
            updateSwimlaneCounts();

            jQuery(".filterBar .row-fluid").css("opacity", "1");

            jQuery(".sortableTicketList").each(function(){

                let kanbanLaneId = jQuery(this).attr("id");

                // Skip collapsed swimlanes - let CSS handle their height
                var swimlaneRow = jQuery(this).closest('.kanban-swimlane-row');
                if (swimlaneRow.length && swimlaneRow.attr('data-expanded') === 'false') {
                    return;
                }

                let height = 250;

                jQuery(this).find(".column .contentInner").each(function () {
                    if (jQuery(this).height() > height) {
                        height = jQuery(this).height();
                    }
                });

                jQuery("#"+kanbanLaneId+" .column .contentInner").css("height", height);

            });

        });

    }

    var initTicketKanban = function (ticketStatusListParameter) {

        var ticketStatusList = ticketStatusListParameter;

        jQuery(".sortableTicketList.kanbanBoard .ticketBox").hover(function () {
            jQuery(this).css("background", "var(--kanban-card-hover)");
        },function () {
            jQuery(this).css("background", "var(--kanban-card-bg)");
        });

        var position_updated = false;

        jQuery(".sortableTicketList").each(function () {

            var currentElement = this;

            jQuery(currentElement).find(".contentInner").sortable({
                connectWith: ".contentInner",
                items: "> .moveable",
                tolerance: 'intersect',
                placeholder: "ui-state-highlight",
                forcePlaceholderSize: true,
                cancel: ".portlet-toggle,:input,a,input",
                distance: 10,

                start: function (event, ui) {
                    ui.item.addClass('tilt');
                    tilt_direction(ui.item);

                    // Store original swimlane for cross-swimlane detection
                    var $originalSwimlane = ui.item.closest('.sortableTicketList.kanbanBoard');
                    ui.item.data('originalSwimlaneId', $originalSwimlane.attr('id'));
                },
                stop: function (event, ui) {
                    ui.item.removeClass("tilt");
                    jQuery("html").unbind('mousemove', ui.item.data("move_handler"));
                    ui.item.removeData("move_handler");

                    countTickets();
                    updateSwimlaneCounts();

                    // Update empty state for all columns after drag-and-drop
                    jQuery(".sortableTicketList").find(".contentInner").each(function() {
                        var $container = jQuery(this);
                        var hasTickets = $container.find(".moveable").length > 0;

                        if (hasTickets) {
                            // Column has tickets - remove empty state
                            $container.removeClass("empty-column");
                            $container.attr("data-empty-text", "");
                            var currentLabel = $container.attr("aria-label") || "";
                            $container.attr("aria-label", currentLabel.replace("Empty column", "") + " column items");
                        } else {
                            // Column is empty - add empty state
                            $container.addClass("empty-column");
                            $container.attr("data-empty-text", "Empty");
                            $container.attr("aria-label", "Empty column");
                        }
                    });

                    // Detect cross-swimlane movement and update groupBy field
                    var $newSwimlane = ui.item.closest('.sortableTicketList.kanbanBoard');
                    var newSwimlaneId = $newSwimlane.attr('id');
                    var originalSwimlaneId = ui.item.data('originalSwimlaneId');

                    if (originalSwimlaneId && newSwimlaneId && originalSwimlaneId !== newSwimlaneId) {
                        // Card moved to different swimlane - update the groupBy field
                        var ticketId = ui.item[0].id.replace('ticket_', '');
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
                            today.setHours(12, 0, 0, 0); // Set to noon to avoid timezone issues

                            // Bucket IDs: 0=overdue, 1=due-this-week, 2=due-next-week, 3=due-later, 4=no-due-date
                            switch (newGroupValue) {
                                case '0': // Overdue - set to yesterday
                                    var yesterday = new Date(today);
                                    yesterday.setDate(yesterday.getDate() - 1);
                                    newGroupValue = yesterday.toISOString().split('T')[0];
                                    break;
                                case '1': // Due This Week - set to end of this week (Saturday)
                                    var endOfWeek = new Date(today);
                                    var daysUntilSaturday = 6 - endOfWeek.getDay();
                                    if (daysUntilSaturday < 0) daysUntilSaturday = 0;
                                    endOfWeek.setDate(endOfWeek.getDate() + daysUntilSaturday);
                                    newGroupValue = endOfWeek.toISOString().split('T')[0];
                                    break;
                                case '2': // Due Next Week - set to next week Saturday
                                    var nextWeekEnd = new Date(today);
                                    var daysUntilNextSaturday = 6 - nextWeekEnd.getDay() + 7;
                                    nextWeekEnd.setDate(nextWeekEnd.getDate() + daysUntilNextSaturday);
                                    newGroupValue = nextWeekEnd.toISOString().split('T')[0];
                                    break;
                                case '3': // Due Later - set to 3 weeks from now
                                    var later = new Date(today);
                                    later.setDate(later.getDate() + 21);
                                    newGroupValue = later.toISOString().split('T')[0];
                                    break;
                                case '4': // No Due Date - clear the date
                                    newGroupValue = '';
                                    break;
                            }
                        }

                        if (fieldName && ticketId) {
                            var $card = jQuery('#ticket_' + ticketId);

                            // OPTIMISTIC UI UPDATE - update visuals immediately before server confirms
                            if (groupBy === 'milestoneid') {
                                // Find the new swimlane's label for the milestone
                                var $newSwimlaneHeader = jQuery('#swimlane-row-' + newGroupValue + ' .swimlane-header-label');
                                var newLabel = $newSwimlaneHeader.text() || leantime.i18n.__("label.no_milestone");

                                // Update milestone dropdown on card
                                var $milestoneDropdown = $card.find('.milestoneDropdown .dropdown-toggle .text');
                                if ($milestoneDropdown.length) {
                                    $milestoneDropdown.text(newLabel);
                                }

                                // Get the milestone color from the dropdown menu item
                                var $milestoneMenuItem = $card.find('.milestoneDropdown .dropdown-menu a[data-value^="' + ticketId + '_' + newGroupValue + '_"]');
                                var milestoneColor = '#b0b0b0'; // Default gray for "No Milestone"

                                if ($milestoneMenuItem.length && newGroupValue !== '0' && newGroupValue !== '') {
                                    // Extract color from the data-value (format: ticketId_milestoneId_color)
                                    var dataValue = $milestoneMenuItem.attr('data-value');
                                    if (dataValue) {
                                        var parts = dataValue.split('_');
                                        if (parts.length >= 3) {
                                            milestoneColor = parts.slice(2).join('_'); // Handle colors with underscores
                                        }
                                    }
                                    // Also try getting from inline style
                                    if (!milestoneColor || milestoneColor === '#b0b0b0') {
                                        var inlineStyle = $milestoneMenuItem.attr('style');
                                        if (inlineStyle) {
                                            var colorMatch = inlineStyle.match(/background-color:\s*([^;]+)/i);
                                            if (colorMatch) {
                                                milestoneColor = colorMatch[1].trim();
                                            }
                                        }
                                    }
                                }

                                // Update the dropdown toggle background color
                                $card.find('.milestoneDropdown .dropdown-toggle').css('background-color', milestoneColor);

                            } else if (groupBy === 'priority') {
                                // Update priority border and dropdown on card
                                $card.removeClass(function(index, className) {
                                    return (className.match(/(^|\s)priority-border-\S+/g) || []).join(' ');
                                }).addClass('priority-border-' + newGroupValue);

                                // Update priority dropdown text and background
                                var priorityLabels = {'1': 'Critical', '2': 'High', '3': 'Medium', '4': 'Low', '5': 'Lowest'};
                                var $priorityDropdown = jQuery('#priorityDropdownMenuLink' + ticketId);
                                if ($priorityDropdown.length) {
                                    $priorityDropdown.find('span.text').text(priorityLabels[newGroupValue] || newGroupValue);
                                    $priorityDropdown.removeClass('priority-bg-1 priority-bg-2 priority-bg-3 priority-bg-4 priority-bg-5');
                                    $priorityDropdown.addClass('priority-bg-' + newGroupValue);
                                }

                            } else if (groupBy === 'editorId') {
                                // Update user dropdown on card
                                var $userDropdown = jQuery('#userDropdownMenuLink' + ticketId);
                                if ($userDropdown.length) {
                                    // Update user image
                                    $userDropdown.find('span.text span img').attr('src', leantime.appUrl + '/api/users?profileImage=' + newGroupValue);

                                    // Get the new user's name from swimlane header
                                    var $newSwimlaneHeader = jQuery('#swimlane-row-' + newGroupValue + ' .swimlane-header-label');
                                    var newUserName = $newSwimlaneHeader.text() || leantime.i18n.__("label.not_assigned");
                                    $userDropdown.find('span.text span#user' + ticketId).text(newUserName);
                                }

                            } else if (groupBy === 'storypoints') {
                                // Update effort dropdown on card
                                var storyPointLabels = {'0.5': '< 2min', '1': 'XS', '2': 'S', '3': 'M', '5': 'L', '8': 'XL', '13': 'XXL'};
                                var $effortDropdown = jQuery('#effortDropdownMenuLink' + ticketId);
                                if ($effortDropdown.length) {
                                    $effortDropdown.find('span.text').text(storyPointLabels[newGroupValue] || newGroupValue);
                                }

                            } else if (groupBy === 'sprint') {
                                // Update sprint dropdown on card
                                var $sprintDropdown = jQuery('#sprintDropdownMenuLink' + ticketId);
                                if ($sprintDropdown.length) {
                                    // Get the new sprint's name from swimlane header
                                    var $newSwimlaneHeader = jQuery('#swimlane-row-' + newGroupValue + ' .swimlane-header-label');
                                    var newSprintName = $newSwimlaneHeader.text() || leantime.i18n.__("label.backlog");
                                    $sprintDropdown.find('span.text').text(newSprintName);
                                }

                            } else if (groupBy === 'dueDate') {
                                // Update due date display on card
                                var $dateDisplay = $card.find('.dues .fa-calendar').parent();
                                if ($dateDisplay.length && newGroupValue) {
                                    // Format the date for display (MM/DD/YYYY)
                                    var dateParts = newGroupValue.split('-');
                                    var formattedDate = dateParts[1] + '/' + dateParts[2] + '/' + dateParts[0];
                                    $dateDisplay.contents().filter(function() {
                                        return this.nodeType === 3; // Text nodes only
                                    }).first().replaceWith(' ' + formattedDate);
                                } else if ($dateDisplay.length && !newGroupValue) {
                                    // Clear the date display for "No Due Date"
                                    $dateDisplay.contents().filter(function() {
                                        return this.nodeType === 3;
                                    }).first().replaceWith(' No due date');
                                }
                            }

                            // Show success notification immediately for better perceived speed
                            jQuery.growl({message: "To-Do Updated", style: "success"});

                            var patchData = {
                                id: ticketId
                            };
                            patchData[fieldName] = newGroupValue;

                            // PATCH the ticket with the new swimlane value (confirmation happens in background)
                            jQuery.ajax({
                                type: 'PATCH',
                                url: leantime.appUrl + '/api/tickets',
                                data: patchData
                            }).fail(function() {
                                jQuery.growl({message: leantime.i18n.__("short_notifications.not_saved") || "Error updating ticket", style: "error"});
                                // Reload on failure to restore correct state
                                location.reload();
                            });
                        }
                    }

                    // Clean up stored data
                    ui.item.removeData('originalSwimlaneId');

                    // Get the new parent swimlane for status update
                    var $targetSwimlane = ui.item.closest('.sortableTicketList');

                    var statusPostData = {
                        action: "kanbanSort",
                        payload: {},
                        handler: ui.item[0].id
                    };


                    for (var i = 0; i < ticketStatusList.length; i++) {
                        if ($targetSwimlane.find(".contentInner.status_" + ticketStatusList[i]).length) {
                            statusPostData.payload[ticketStatusList[i]] = $targetSwimlane.find(".contentInner.status_" + ticketStatusList[i]).sortable('serialize');
                        }
                    }

                    // POST to server using $.post or $.ajax
                    jQuery.ajax({
                        type: 'POST',
                        url: leantime.appUrl + '/api/tickets',
                        data: statusPostData

                    }).done(function (response) {
                        leantime.handleAsyncResponse(response);
                    });

                }
            });

        });

        function tilt_direction(item)
        {
            var left_pos = item.position().left,
                move_handler = function (e) {

                    if ((e.pageX + 5) > left_pos) {
                        item.addClass("right");
                        item.removeClass("left");
                    } else if (e.pageX < (left_pos + 5)) {
                        item.addClass("left");
                        item.removeClass("right");
                    } else {
                        item.removeClass("left");
                        item.removeClass("right");
                    }

                    left_pos = e.pageX;

                };
            jQuery("html").bind("mousemove", move_handler);
            item.data("move_handler", move_handler);
        }

        jQuery(".portlet")
            .addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
            .find(".portlet-header")
            .addClass("ui-widget-header ui-corner-all")
            .prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

        jQuery(".portlet-toggle").click(function () {
            var icon = jQuery(this);
            icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
            icon.closest(".portlet").find(".portlet-content").toggle();
        });

    };

    var initTicketsTable = function (groupBy) {

        function isNumeric(n)
        {
            return !isNaN(parseFloat(n)) && isFinite(n);
        }

        jQuery(document).ready(function () {

            var size = 100;
            var columnIndex = false;


            var defaultOrder = [];

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
                                a = jQuery(a).val();
                            }

                            if (isNumeric(b) === false) {
                                b = jQuery(b).val();
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
                                a = jQuery(a).val();
                            }

                            if (isNumeric(b) === false) {
                                b = jQuery(b).val();
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
                    jQuery(api.column(9).footer()).html('Total');
                    jQuery(api.column(10).footer()).html(plannedHours);
                    jQuery(api.column(11).footer()).html(hoursLeft);
                    jQuery(api.column(12).footer()).html(loggedHours);

                },

            });

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

                                    if ( typeof jQuery(node).data('order') !== 'undefined') {
                                        data = jQuery(node).data('order');
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
            }).container().appendTo(jQuery('#tableButtons'));

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

            jQuery('.ticketTable input').on('change', function ( e, settings, column, state ) {

                jQuery(this).parent().attr('data-order',jQuery(this).val());
                allTickets.draw();

            });
        });
    };

    var initTicketsList = function (groupBy) {

        jQuery(document).ready(function () {

            var size = 50;
            var columnIndex = false;
            var collapsedGroups = {};

            var defaultOrder = [];


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
                        jQuery(oSettings.nTableWrapper).find('.dataTables_paginate').hide();
                    } else {
                        jQuery(oSettings.nTableWrapper).find('.dataTables_paginate').show();
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

        jQuery(document).ready(function () {

            var size = 100;
            var columnIndex = false;


            var defaultOrder = [];

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

                                    if ( typeof jQuery(node).data('order') !== 'undefined') {
                                        data = jQuery(node).data('order');
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
            }).container().appendTo(jQuery('#tableButtons'));

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

            jQuery('.ticketTable input').on('change', function ( e, settings, column, state ) {

                jQuery(this).parent().attr('data-order',jQuery(this).val());
                allTickets.draw();

            });

        });
    };

    var loadTicketToContainer = function (id, element) {

        if (jQuery('textarea.complexEditor').length > 0 && jQuery('textarea.complexEditor').tinymce() !== null) {
            jQuery('textarea.complexEditor').tinymce().save();
            jQuery('textarea.complexEditor').tinymce().remove();
        }

        jQuery(".ticketRows").removeClass("active");
        jQuery("#row-" + id).addClass("active");

        jQuery(element).html("<div class='center'><img src='" + leantime.appUrl + "/dist/images/svg/loading-animation.svg' width='100px' /></div>");

        function formSubmitHandler(element)
        {

            jQuery(element).find("form").each(function () {

                jQuery(this).on("submit", function (e) {

                    e.preventDefault();

                    if (jQuery('textarea.complexEditor').length > 0 && jQuery('textarea.complexEditor').tinymce() !== null) {
                        jQuery('textarea.complexEditor').tinymce().save();
                        jQuery('textarea.complexEditor').tinymce().remove();
                    }

                    jQuery(element).html("<div class='center'><img src='" + leantime.appUrl + "/dist/images/svg/loading-animation.svg' width='100px'/></div>");

                    var data = jQuery(this).serialize();

                    jQuery.ajax({
                        url: jQuery(this).attr("action"),
                        data: data,
                        type: "post",
                        success: function (data) {

                            jQuery(element).html(data);
                            formSubmitHandler(element);

                        },
                        error: function () {

                        }
                    });
                });

            });
        }



        jQuery.get(leantime.appUrl + '/tickets/showTicket/' + id, function ( data ) {

            jQuery(element).html(data);
            formSubmitHandler(element);

        });

    };

    var initTagsInput = function ( ) {
        jQuery("#tags").tagsInput({
            'autocomplete_url': leantime.appUrl + '/api/tags',
        });

        jQuery("#tags_tag").on("focusout", function () {
            let tag = jQuery(this).val();

            if (tag != '') {
                jQuery("#tags").addTag(tag);
            }
        });

    };

    var addCommentTimesheetContent = function (commentId, taskId) {
        var content = "Discussion on To-Do #" + taskId + ":"
        + "\n\r"
        + jQuery("#commentText-" + commentId).text();

        jQuery('li a[href*="timesheet"]').click();

        jQuery("#timesheet #description").val(content);

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
        initSimpleColorPicker:initSimpleColorPicker
    };
})();
