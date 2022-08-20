leantime.ticketsController = (function () {

    //Variables

    var milestoneModalConfig = {
        sizes: {
            minW: 500,
            minH: 750
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforeShowCont: function() {
                jQuery(".showDialogOnLoad").show();
            },
            afterShowCont: function () {
                jQuery(".showDialogOnLoad").show();
                _initSprintDates();
                _initSimpleColorPicker();
                jQuery(".formModal, #commentForm, .deleteComment").nyroModal(milestoneModalConfig);
            },
            beforeClose: function () {

                location.reload();
            }


        },
        titleFromIframe: true
    };

    var ticketModalConfig = {
        sizes: {
            minW:  800,
            minH: 1000
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforePostSubmit: function() {
                jQuery('textarea.complexEditor').tinymce().save();
                jQuery('textarea.complexEditor').tinymce().remove();
            },
            beforeShowCont: function() {




            },
            afterShowCont: function () {

                jQuery("#commentForm, .deleteComment, .ticketModal").nyroModal(ticketModalConfig);
            },
            beforeClose: function () {

                location.reload();
            },



        },
        titleFromIframe: true
    };

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {

                _initModals();
            }
        );

    })();

    //Functions
    function countTickets () {

        jQuery("#sortableTicketKanban .column").each(function(){
            var counting= jQuery(this).find('.moveable').length;
            jQuery(this).find(' .count').text(counting);
        });

    }


    var updateRemainingHours = function (element, id) {
        var value = jQuery(element).val();
        leantime.ticketsRepository.updateRemainingHours(
            id, value, function () {
                jQuery.jGrowl(leantime.i18n.__("short_notifications.remaining_hours_updated"), {theme: "success"});
            }
        );

    };

    var updatePlannedHours = function (element, id) {
        var value = jQuery(element).val();
        leantime.ticketsRepository.updatePlannedHours(
            id, value, function () {
                jQuery.jGrowl(leantime.i18n.__("short_notifications.planned_hours_updated"), {theme: "success"});
            }
        );

    };

    var triggerMilestoneModal = function (id) {
        jQuery.nmManual(leantime.appUrl+'/tickets/editMilestone/'+id, milestoneModalConfig);

    };

    var openMilestoneModalManually = function (url) {
        jQuery.nmManual(url, milestoneModalConfig);
    };

    var openTicketModalManually = function (url) {
        jQuery.nmManual(url, ticketModalConfig);
    };

    var toggleFilterBar = function () {
        jQuery(".filterBar").toggle();

    };

    var initGanttChart = function (tasks, viewMode, readonly) {

        function htmlEntities(str) {
            return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        jQuery(document).ready(
            function () {

                if(readonly == false) {

                    var gantt_chart = new Gantt(
                        "#gantt", tasks, {
                            custom_popup_html: function (task) {
                                // the task object will contain the updated
                                // dates and progress value
                                var end_date = task._end.format(leantime.i18n.__("language.momentJSDate"));
                                return '<div class="details-container"> ' +
                                    '<h4><a href="' + leantime.appUrl + '/tickets/editMilestone/' + task.id + '" class="milestoneModal">' + htmlEntities(task.name) + '</a></h4><br /> ' +
                                    '<p>' + leantime.i18n.__("text.expected_to_finish_by") + ' <strong>' + end_date + '</strong><br /> ' +
                                    '' + Math.round(task.progress) + '%</p> ' +
                                    '<a href="' + leantime.appUrl + '/tickets/editMilestone/' + task.id + '" class="milestoneModal"><span class="fa fa-map"></span> ' + leantime.i18n.__("links.edit_milestone") + '</a> | ' +
                                    '<a href="' + leantime.appUrl + '/tickets/showKanban&milestone=' + task.id + '"><span class="iconfa-pushpin"></span> ' + leantime.i18n.__("links.view_todos") + '</a> ' +

                                    '</div>';
                            },
                            on_click: function (task) {

                            },
                            on_date_change: function (task, start, end) {

                                leantime.ticketsRepository.updateMilestoneDates(task.id, start, end);
                                _initModals();

                            },
                            on_progress_change: function (task, progress) {

                                //_initModals();
                            },
                            on_view_change: function (mode) {

                                leantime.usersRepository.updateUserViewSettings("roadmap", mode);
                                _initModals();
                            }
                        }
                    );
                }else{
                    var gantt_chart = new Gantt(
                        "#gantt", tasks, {
                            readonlyGantt: true,
                            custom_popup_html: function (task) {
                                // the task object will contain the updated
                                // dates and progress value
                                var end_date = task._end.format(leantime.i18n.__("language.momentJSDate"));
                                return '<div class="details-container"> ' +
                                    '<h4>' + htmlEntities(task.name) + '</h4><br /> ' +
                                    '<p>' + leantime.i18n.__("text.expected_to_finish_by") + ' <strong>' + end_date + '</strong><br /> ' +
                                    '' + Math.round(task.progress) + '%</p> ' +
                                    '<a href="' + leantime.appUrl + '/tickets/showKanban&milestone=' + task.id + '"><span class="iconfa-pushpin"></span> ' + leantime.i18n.__("links.view_todos") + '</a> ' +

                                    '</div>';
                            },
                            on_click: function (task) {

                            },
                            on_date_change: function (task, start, end) {


                            },
                            on_progress_change: function (task, progress) {

                                //_initModals();
                            },
                            on_view_change: function (mode) {

                                leantime.usersRepository.updateUserViewSettings("roadmap", mode);
                                _initModals();
                            }
                        }
                    );
                }

                jQuery("#ganttTimeControl").on(
                    "click", "a", function () {

                        var $btn =jQuery(this);
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

    var _initDates = function () {

        jQuery(".dates").datepicker(
            {
                dateFormat:  leantime.i18n.__("language.jsdateformat"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
                firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            }
        );
    };

    var initModals = function () {
        _initModals();
    };

    var _initSprintDates = function () {

        Date.prototype.addDays = function (days) {
            this.setDate(this.getDate() + days);
            return this;
        };
        jQuery.datepicker.setDefaults(
            { beforeShow: function (i) {
                if (jQuery(i).attr('readonly')) { return false; } } }
        );

        var dateFormat = leantime.i18n.__("language.jsdateformat"),

            from = jQuery("#sprintStart")
                .datepicker(
                    {
                        numberOfMonths: 1,
                        dateFormat:  leantime.i18n.__("language.jsdateformat"),
                        dayNames: leantime.i18n.__("language.dayNames").split(","),
                        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                        monthNames: leantime.i18n.__("language.monthNames").split(","),
                        currentText: leantime.i18n.__("language.currentText"),
                        closeText: leantime.i18n.__("language.closeText"),
                        buttonText: leantime.i18n.__("language.buttonText"),
                        isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                        nextText: leantime.i18n.__("language.nextText"),
                        prevText: leantime.i18n.__("language.prevText"),
                        weekHeader: leantime.i18n.__("language.weekHeader"),
                        firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                    }
                )
                .on(
                    "change", function () {
                        to.datepicker("option", "minDate", getDate(this));
                        var newEndDate = getDate(this).addDays(13);
                        to.datepicker('setDate', newEndDate); //set date

                    }
                ),

            to = jQuery("#sprintEnd").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1,
                    dateFormat:  leantime.i18n.__("language.jsdateformat"),
                    dayNames: leantime.i18n.__("language.dayNames").split(","),
                    dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                    dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                    monthNames: leantime.i18n.__("language.monthNames").split(","),
                    currentText: leantime.i18n.__("language.currentText"),
                    closeText: leantime.i18n.__("language.closeText"),
                    buttonText: leantime.i18n.__("language.buttonText"),
                    isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                    nextText: leantime.i18n.__("language.nextText"),
                    prevText: leantime.i18n.__("language.prevText"),
                    weekHeader: leantime.i18n.__("language.weekHeader"),
                    firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                }
            )
            .on(
                "change", function () {
                    from.datepicker("option", "maxDate", getDate(this));
                }
            );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch( error ) {
                date = null;
                console.log(error);
            }

            return date;
        }
    };

    var _initMilestoneDates = function () {
        var dateFormat = leantime.i18n.__("language.jsdateformat"),
            from = jQuery("#milestoneEditFrom")
                .datepicker(
                    {
                        numberOfMonths: 1,
                        dateFormat:  leantime.i18n.__("language.jsdateformat"),
                        dayNames: leantime.i18n.__("language.dayNames").split(","),
                        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                        monthNames: leantime.i18n.__("language.monthNames").split(","),
                        currentText: leantime.i18n.__("language.currentText"),
                        closeText: leantime.i18n.__("language.closeText"),
                        buttonText: leantime.i18n.__("language.buttonText"),
                        isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                        nextText: leantime.i18n.__("language.nextText"),
                        prevText: leantime.i18n.__("language.prevText"),
                        weekHeader: leantime.i18n.__("language.weekHeader"),
                        firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                    }
                )
                .on(
                    "change", function () {
                        to.datepicker("option", "minDate", getDate(this));
                    }
                ),
            to = jQuery("#milestoneEditTo").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1,
                    dateFormat:  leantime.i18n.__("language.jsdateformat"),
                    dayNames: leantime.i18n.__("language.dayNames").split(","),
                    dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                    dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                    monthNames: leantime.i18n.__("language.monthNames").split(","),
                    currentText: leantime.i18n.__("language.currentText"),
                    closeText: leantime.i18n.__("language.closeText"),
                    buttonText: leantime.i18n.__("language.buttonText"),
                    isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                    nextText: leantime.i18n.__("language.nextText"),
                    prevText: leantime.i18n.__("language.prevText"),
                    weekHeader: leantime.i18n.__("language.weekHeader"),
                    firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                }
            )
                .on(
                    "change", function () {
                        from.datepicker("option", "maxDate", getDate(this));
                    }
                );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch( error ) {
                date = null;
                console.log(error);
            }

            return date;
        }
    };

    var initMilestoneDatesAsyncUpdate = function () {

        var dateFormat = leantime.i18n.__("language.jsdateformat"),
            from = jQuery(".milestoneEditFromAsync")
                .datepicker(
                    {
                        numberOfMonths: 1,
                        dateFormat:  leantime.i18n.__("language.jsdateformat"),
                        dayNames: leantime.i18n.__("language.dayNames").split(","),
                        dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                        dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                        monthNames: leantime.i18n.__("language.monthNames").split(","),
                        currentText: leantime.i18n.__("language.currentText"),
                        closeText: leantime.i18n.__("language.closeText"),
                        buttonText: leantime.i18n.__("language.buttonText"),
                        isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                        nextText: leantime.i18n.__("language.nextText"),
                        prevText: leantime.i18n.__("language.prevText"),
                        weekHeader: leantime.i18n.__("language.weekHeader"),
                        firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                    }
                )
                .on(
                    "change", function () {

                        var date = jQuery(this).val();
                        var id = jQuery(this).attr("data-id");

                        var toDatePicker = jQuery(".toDateTicket-"+id);
                        toDatePicker.datepicker("option", "minDate", getDate(this));

                        var dateTime = moment(date, leantime.i18n.__("language.momentJSDate")).format("YYYY-MM-DD HH:mm:ss");

                        var newDate = dateTime;
                        leantime.ticketsRepository.updateEditFromDates(id, newDate, function() {
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.date_updated"), {theme: "success"});
                        });

                        var dateTo = jQuery(".toDateTicket-"+id).val();

                        var dateTimeTo = moment(dateTo, leantime.i18n.__("language.momentJSDate")).format("YYYY-MM-DD HH:mm:ss");
                        var newDateTo = dateTimeTo;

                        leantime.ticketsRepository.updateEditToDates(id, newDateTo, function() {

                        });
                    }
                ),
            to = jQuery(".milestoneEditToAsync").datepicker(
                {
                    defaultDate: "+1w",
                    numberOfMonths: 1,
                    dateFormat:  leantime.i18n.__("language.jsdateformat"),
                    dayNames: leantime.i18n.__("language.dayNames").split(","),
                    dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                    dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                    monthNames: leantime.i18n.__("language.monthNames").split(","),
                    currentText: leantime.i18n.__("language.currentText"),
                    closeText: leantime.i18n.__("language.closeText"),
                    buttonText: leantime.i18n.__("language.buttonText"),
                    isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                    nextText: leantime.i18n.__("language.nextText"),
                    prevText: leantime.i18n.__("language.prevText"),
                    weekHeader: leantime.i18n.__("language.weekHeader"),
                    firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                }
            )
                .on(
                    "change", function () {

                        var id = jQuery(this).attr("data-id");
                        var fromDateTicket = jQuery(".fromDateTicket-"+id);
                        fromDateTicket.datepicker("option", "maxDate", getDate(this));

                        var date = jQuery(this).val();

                        var dateTime = moment(date, leantime.i18n.__("language.momentJSDate")).format("YYYY-MM-DD HH:mm:ss");

                        var newDate = dateTime;
                        leantime.ticketsRepository.updateEditToDates(id, newDate, function() {
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.date_updated"), {theme: "success"});
                        });

                        var dateFrom = jQuery(".fromDateTicket-"+id).val();

                        var dateTimeFrom = moment(dateFrom, leantime.i18n.__("language.momentJSDate")).format("YYYY-MM-DD HH:mm:ss");
                        var newDateFrom = dateTimeFrom;
                        leantime.ticketsRepository.updateEditFromDates(id, newDateFrom, function() {

                        });


                    }
                );

        function getDate( element )
        {
            var date;
            try {
                date = jQuery.datepicker.parseDate(dateFormat, element.value);
            } catch( error ) {
                date = null;
                console.log(error);
            }

            return date;
        }
    };

    var initToolTips = function () {
        jQuery('[data-toggle="tooltip"]').tooltip();
    };

    var _initModals = function () {

        var regularModelConfig = {
            callbacks: {
                afterShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    _initDates();
                    jQuery(".regularModal, .formModal").nyroModal(regularModelConfig);
                }
            }
        };

        jQuery(".regularModal").nyroModal(regularModelConfig);

        var sprintModalConfig = {
            sizes: {
                minW: 400,
                minH: 350
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                afterShowCont: function () {
                    _initSprintDates();
                    initToolTips();

                    jQuery(".formModal").nyroModal(sprintModalConfig);
                },
                beforeClose: function () {
                    location.reload();
                }


            },
            titleFromIframe: true
        };
        jQuery(".sprintModal").nyroModal(sprintModalConfig);

        var modalConfig = {
            sizes: {
                minW:  800,
                minH: 500
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                beforeShowCont: function() {
                    jQuery(".showDialogOnLoad").show();
                },
                afterShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    _initMilestoneDates();
                    _initSimpleColorPicker();
                    jQuery(".formModal, #commentForm, .deleteComment").nyroModal(modalConfig);


                },
                beforeClose: function () {

                    location.reload();
                }


            },
            titleFromIframe: true
        };

        jQuery(".milestoneModal").nyroModal(modalConfig);

        var ticketModalConfig = {
            sizes: {
                minW:  800,
                minH: 500
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                beforePostSubmit: function() {
                    jQuery('textarea.complexEditor').tinymce().save();
                    jQuery('textarea.complexEditor').tinymce().remove();
                },
                beforeShowCont: function() {

                },
                afterShowCont: function () {

                    jQuery("#commentForm, .deleteComment, .ticketModal").nyroModal(ticketModalConfig);
                },
                beforeClose: function () {

                    location.reload();
                },

            },
            titleFromIframe: true
        };

        jQuery(".ticketModal").nyroModal(ticketModalConfig);


    };

    var initSprintPopover = function () {
        jQuery('.sprintPopover').popover(
            {
                template:'<div class="popover sprintPopoverContainer" role="tooltip"><div class="popover-arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>'

            }
        );

        jQuery(".sprintPopoverContainer input").unbind().on("click", function () {

                var ticket = jQuery(this).attr("name").split("_");
                var val = jQuery(this).val();

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl+'/api/tickets',
                        data:
                        {
                            id : ticket[1],
                            sprint:val
                        }
                    }
                ).done(
                    function () {

                    }
                );

            }
        );
    };

    var initEffortDropdown = function() {

        var storyPointLabels = {
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
                        url: leantime.appUrl+'/api/tickets',
                        data:
                            {
                                id: ticketId,
                                storypoints: effortId
                            }
                    }
                ).done(
                    function () {
                        jQuery("#effortDropdownMenuLink" + ticketId + " span.text").text(storyPointLabels[effortId]);
                        jQuery.jGrowl(leantime.i18n.__("short_notifications.effort_updated"), {theme: "success"});

                    }
                );

            }else{
                console.log("Ticket Controller: Effort data value not set correctly");
            }
        });

    };

    var initPriorityDropdown = function() {
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
                        url: leantime.appUrl+'/api/tickets',
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
                        jQuery("#priorityDropdownMenuLink" + ticketId + "").addClass("priority-bg-"+priorityId);

                        jQuery("#priorityDropdownMenuLink" + ticketId + "").parents(".ticketBox").removeClass("priority-border-1 priority-border-2 priority-border-3 priority-border-4 priority-border-5");
                        jQuery("#priorityDropdownMenuLink" + ticketId + "").parents(".ticketBox").addClass("priority-border-"+priorityId);


                        jQuery.jGrowl(leantime.i18n.__("short_notifications.priority_updated"), {theme: "success"});

                    }
                );

            }else{
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

                    jQuery("#milestoneDropdownMenuLink"+ticketId+" span.text").append(" ...");

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl+'/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    dependingTicketId:milestoneId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#milestoneDropdownMenuLink"+ticketId+" span.text").text(dataLabel);
                            jQuery("#milestoneDropdownMenuLink"+ticketId).css("backgroundColor", color);
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.milestone_updated"), {theme: "success"});
                        }
                    );

                }
            }
        );
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
                            url: leantime.appUrl+'/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    status:statusId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#statusDropdownMenuLink"+ticketId+" span.text").text(dataLabel);
                            jQuery("#statusDropdownMenuLink"+ticketId).removeClass().addClass(className+" dropdown-toggle f-left status ");
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.status_updated"), {theme: "success"});

                        }
                    );

                }
            }
        );

    };

    var initUserDropdown = function () {

        jQuery(".userDropdown .dropdown-menu a").unbind().on("click", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 3) {

                    var ticketId = dataValue[0];
                    var userId = dataValue[1];
                    var profileImageId = dataValue[2];

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl+'/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    editorId:userId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#userDropdownMenuLink"+ticketId+" span.text span#userImage"+ticketId+" img").attr("src", leantime.appUrl+"/api/users?profileImage="+profileImageId);
                            jQuery("#userDropdownMenuLink"+ticketId+" span.text span#user"+ticketId).text(dataLabel);
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.user_updated"), {theme: "success"});
                        }
                    );

                }
            }
        );
    };

    var initAsyncInputChange = function() {

        jQuery(".asyncInputUpdate").on("change", function(){
            var dataLabel = jQuery(this).attr('data-label').split("-");

            if(dataLabel.length == 2){
                var fieldName = dataLabel[0];
                var entityId = dataLabel[1];
                var value = jQuery(this).val();

                jQuery.ajax(
                    {
                        type: 'PATCH',
                        url: leantime.appUrl+'/api/tickets',
                        data:
                            {
                                id : entityId,
                                [fieldName]:value,

                            }
                    }
                ).done(
                    function () {
                        jQuery.jGrowl(leantime.i18n.__("notifications.subtask_saved"), {theme: "success"});
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
                            url: leantime.appUrl+'/api/tickets',
                            data:
                                {
                                    id : ticketId,
                                    sprint:sprintId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#sprintDropdownMenuLink"+ticketId+" span.text").text(dataLabel);
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.sprint_updated"), {theme: "success"});
                        }
                    );

                }
            }
        );
    };

    var _initSimpleColorPicker = function () {

            var colors = ['#064779', '#1B76BB', '#00814A', '#35CB8B', '#F3B600', '#FFD042', '#BC3600', '#F34500'];
            jQuery('input.simpleColorPicker').simpleColorPicker(
                { colors: colors,
                    onChangeColor: function (color) {
                        jQuery(this).css('background', color); }
                }
            );

            var currentColor = jQuery('input.simpleColorPicker').val();

            if(currentColor != ''){
                jQuery('input.simpleColorPicker').css('background', currentColor);
            }


    };

    var _initDueDateTimePickers = function () {

        jQuery(".quickDueDates").datepicker(
            {
                dateFormat:  leantime.i18n.__("language.jsdateformat"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
                firstDay: leantime.i18n.__("language.firstDayOfWeek"),
                onClose: function(date) {

                    var newDate = "";

                    if(date == "") {
                        jQuery(this).val(leantime.i18n.__("text.anytime"));
                    }

                    var dateTime = moment(date, leantime.i18n.__("language.momentJSDate")).format("YYYY-MM-DD HH:mm:ss");

                    var id = jQuery(this).attr("data-id");
                    newDate = dateTime;

                    leantime.ticketsRepository.updateDueDates(id, newDate, function() {
                        jQuery.jGrowl(leantime.i18n.__("short_notifications.duedate_updated"), {theme: "success"});
                    });

                }
            }
        );
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

    var colorTicketBoxes = function (currentBox){

        var color = "#fff";
        jQuery(".ticketBox").each(function(index){

            var value = jQuery(this).find(".statusDropdown > a").attr("class");

            if(value != undefined) {
                if (value.indexOf("important") > -1) {

                    color = "#b94a48";

                }else if (value.indexOf("info") > -1) {

                        color = "#2d6987";

                } else if (value.indexOf("warning") > -1) {

                    color = "#f89406";

                } else if (value.indexOf("success") > -1) {

                    color = "#468847";

                } else if (value.indexOf("default") > -1) {

                    color = "#999999";
                }else{

                    color = "#999999";

                }

                jQuery(this).css("borderLeft", "5px solid " + color);

                if(currentBox != null) {
                    if (jQuery(this).attr("data-val") == currentBox) {
                        jQuery("#ticket_" + currentBox + " .ticketBox").animate({backgroundColor: color}, 'fast').animate({backgroundColor: "#fff"}, 'slow');
                    }
                }
            }

        });

    };

    var initTicketTabs = function(){

        jQuery(document).ready(function () {

            jQuery('.ticketTabs').tabs({
                create: function( event, ui ) {
                    jQuery('.ticketTabs').css("visibility", "visible");

                },
                activate: function(event, ui) {
                    window.location.hash = ui.newPanel.selector;
                },
                load: function() {

                }

            });
        });

    };

    var initTicketSearchSubmit = function (url) {

        jQuery("#ticketSearch").on('submit', function(e) {
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

            var query = "?search=true";
            if(project != "" && project != undefined) {query = query + "&projectId=" + project}
            if(users != "" && users != undefined) {query = query + "&users=" + users}
            if(milestones != ""  && milestones != undefined) {query = query + "&milestone=" + milestones}
            if(term != ""  && term != undefined) {query = query + "&term=" + term;}
            if(sprints != ""  && sprints != undefined) {query = query + "&sprint=" + sprints;}
            if(types != "" && types != undefined) {query = query + "&type=" + types;}
            if(priority != "" && priority != undefined) {query = query + "&priority=" + priority;}
            if(status != "" && status != undefined) {query = query + "&status=" + status;}
            if(sort != "" && sort != undefined) {query = query + "&sort=" + sort;}
            if(groupBy != "" && groupBy != undefined) {query = query + "&groupBy=" + groupBy;}

            var rediredirectUrl = url + query;

            window.location.href = rediredirectUrl;

        });
    };

    var setUpKanbanColumns = function () {

        jQuery(document).ready(function () {

            countTickets();
            jQuery(".filterBar .row-fluid").css("opacity", "1");

            var height = jQuery("html").height()-250;
            jQuery("#sortableTicketKanban .column .contentInner").css("min-height", height);

        });

    }

    var initTicketKanban = function (ticketStatusList) {

        jQuery("#sortableTicketKanban .ticketBox").hover(function(){
            jQuery(this).css("background", "var(--kanban-card-hover)");
        },function(){
            jQuery(this).css("background", "var(--kanban-card-bg)");
        });

        var position_updated = false;

        jQuery("#sortableTicketKanban .contentInner").sortable({
            connectWith: ".contentInner",
            items: "> .moveable",
            tolerance: 'intersect',
            placeholder: "ui-state-highlight",
            forcePlaceholderSize: true,
            cancel: ".portlet-toggle,:input,a,input",
            distance: 25,

            start: function (event, ui) {
                ui.item.addClass('tilt');
                tilt_direction(ui.item);
            },
            stop: function (event, ui) {
                ui.item.removeClass("tilt");
                jQuery("html").unbind('mousemove', ui.item.data("move_handler"));
                ui.item.removeData("move_handler");

                countTickets();

                var statusPostData = {
                    action: "kanbanSort",
                    payload: {},
                    handler: ui.item[0].id
                };

                for (var i = 0; i < ticketStatusList.length; i++) {

                    if (jQuery(".contentInner.status_" + ticketStatusList[i]).length) {
                        statusPostData.payload[ticketStatusList[i]] = jQuery(".contentInner.status_" + ticketStatusList[i]).sortable('serialize');
                    }
                }

                // POST to server using $.post or $.ajax
                jQuery.ajax({
                    type: 'POST',
                    url: leantime.appUrl + '/api/tickets',
                    data: statusPostData

                });

            }
        });

        function tilt_direction(item) {
            var left_pos = item.position().left,
                move_handler = function (e) {
                    if (e.pageX >= left_pos) {
                        item.addClass("right");
                        item.removeClass("left");
                    } else {
                        item.addClass("left");
                        item.removeClass("right");
                    }
                    left_pos = e.pageX;
                };
            jQuery("html").bind("mousemove", move_handler);
            item.data("move_handler", move_handler);
        }

        jQuery( ".portlet" )
            .addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
            .find( ".portlet-header" )
            .addClass( "ui-widget-header ui-corner-all" )
            .prepend( "<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");

        jQuery( ".portlet-toggle" ).click(function() {
            var icon = jQuery( this );
            icon.toggleClass( "ui-icon-minusthick ui-icon-plusthick" );
            icon.closest( ".portlet" ).find( ".portlet-content" ).toggle();
        });

    };

    var initUserSelectBox = function () {

        jQuery(".user-select").chosen();

    };

    var initStatusSelectBox = function () {

        jQuery(".status-select").chosen();

    };

    var initTicketsTable = function (groupBy) {

        jQuery(document).ready(function() {

            var size = 100;
            var columnIndex = false;

            if(groupBy != "") {
                columnIndex = jQuery("#allTicketsTable thead").find("." + groupBy + "-col").index();
            }

            var plannedHoursIndex = jQuery("#allTicketsTable thead").find(".planned-hours-col").index();
            var remainingHoursIndex =  jQuery("#allTicketsTable thead").find(".remaining-hours-col").index();
            var loggedHoursIndex =  jQuery("#allTicketsTable thead").find(".booked-hours-col").index();
            var dueDateCol = jQuery("#allTicketsTable thead").find(".duedate-col").index();

            var rowGroupOption = false;
            var orderFixedOption = false;
            var defaultOrder = [];

            if(columnIndex !== false) {

                rowGroupOption = {
                    startRender: function (rows, group) {

                        var sumPlanned = rows
                            .data()
                            .pluck(plannedHoursIndex)
                            .reduce(function (a, b) {
                                return parseFloat(a) + parseFloat(b["@data-order"]);
                            }, "0");

                        var sumRemaining = rows
                            .data()
                            .pluck(remainingHoursIndex)
                            .reduce(function (a, b) {

                                return parseFloat(a) + parseFloat(b["@data-order"]);
                            }, "0");

                        var sumLogged = rows
                            .data()
                            .pluck(loggedHoursIndex)
                            .reduce(function (a, b) {

                                return parseFloat(a) + parseFloat(b["@data-order"]);
                            }, "0");

                        var visiblePlannedHoursIndex = jQuery("#allTicketsTable thead").find(".planned-hours-col").index();
                        var visibleRemainingHoursIndex =  jQuery("#allTicketsTable thead").find(".remaining-hours-col").index();
                        var visibleLoggedHoursIndex =  jQuery("#allTicketsTable thead").find(".booked-hours-col").index();


                        var totalColumns = jQuery("#allTicketsTable thead th").length;
                        if(visiblePlannedHoursIndex > -1) {
                            totalColumns--;
                        }

                        if(visibleRemainingHoursIndex > -1) {
                            totalColumns--;
                        }

                        if(visibleLoggedHoursIndex > -1) {
                            totalColumns--;
                        }

                        var groupOutput = jQuery('<tr/>').append('<td colspan="'+totalColumns+'">' + group + ' ('+rows.count()+')</td>');

                        if(visiblePlannedHoursIndex > -1) {
                            groupOutput.append('<td>' + sumPlanned + '</td>');
                        }

                        if(visibleRemainingHoursIndex > -1) {
                            groupOutput.append('<td>' + sumRemaining + '</td>');
                        }

                        if(visibleLoggedHoursIndex > -1) {
                            groupOutput.append('<td>' + sumLogged + '</td>');
                        }

                        return groupOutput;


                    },
                    dataSrc: function (row) {
                        return row[columnIndex]["@data-order"];
                    }
                };

                orderFixedOption = {"pre":[[columnIndex, 'asc']]};
                defaultOrder = [[columnIndex, 'asc'], [dueDateCol, 'asc']];
            }

            var allTickets = jQuery("#allTicketsTable").DataTable({
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
                    "dom": '<"top">rt<"bottom"ilp><"clear">',
                    "searching": false,
                    "stateSave": true,
                    "displayLength":100,
                    "orderFixed": orderFixedOption,
                    "order": defaultOrder,
                    "rowGroup": rowGroupOption,

            });

            var buttons = new jQuery.fn.dataTable.Buttons(allTickets, {
                buttons: [
                    {
                        extend: 'csvHtml5',
                        title: leantime.i18n.__("label.filename_fileexport"),
                        charset: 'utf-8',
                        bom: true,
                        exportOptions: {
                            format: {
                                body: function ( data, row, column, node ) {
                                    if( typeof jQuery(node).data('order') !== 'undefined'){
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

            jQuery('#allTicketsTable').on( 'column-visibility.dt', function ( e, settings, column, state ) {
                allTickets.draw(false);
            } );

            jQuery('#allTicketsTable input').on( 'change', function ( e, settings, column, state ) {

                jQuery(this).parent().attr('data-order',jQuery(this).val());
                allTickets.draw();

            });

            var asc = true;
            if(groupBy != "") {
                jQuery("#allTicketsTable thead").find("." + groupBy + "-col").on('click', function (e, settings, column, state) {
                    asc = !asc;
                    var orderFixed= {"pre":[[columnIndex, asc === true? 'asc' : 'desc']]};
                    allTickets.order.fixed(orderFixed).draw();
                });
            }


        });
    };

    var initTagsInput = function( ) {
        jQuery("#tags").tagsInput();
    }

    // Make public what you want to have public, everything else is private
    return {
        toggleFilterBar: toggleFilterBar,
        triggerMilestoneModal: triggerMilestoneModal,
        initGanttChart:initGanttChart,
        updateRemainingHours:updateRemainingHours,
        updatePlannedHours:updatePlannedHours,
        initModals:initModals,
        openMilestoneModalManually:openMilestoneModalManually,
        openTicketModalManually: openTicketModalManually,
        initTimeSheetChart:initTimeSheetChart,
        initTicketTabs:initTicketTabs,
        initTicketSearchSubmit:initTicketSearchSubmit,
        initTicketKanban:initTicketKanban,
        initUserSelectBox:initUserSelectBox,
        initStatusSelectBox:initStatusSelectBox,
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
        initDueDateTimePickers:_initDueDateTimePickers,
        initDates:_initDates,
        setUpKanbanColumns:setUpKanbanColumns
    };
})();
