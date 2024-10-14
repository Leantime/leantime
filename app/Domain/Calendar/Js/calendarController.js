leantime.calendarController = (function () {

    var closeModal = false;

    //Functions
    var initCalendar = function (userEvents) {

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var heightWindow = jQuery("body").height() - 260;

        var calendar = jQuery('#calendar').fullCalendar({
            timeZone: leantime.i18n.__("usersettings.timezone"),
            height: heightWindow,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,agendaWeek,agendaDay,listDay'
            },
            titleFormat: {
                month: 'MMMM yyyy',
                week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
                day: 'dddd, MMM d, yyyy'
            },
            columnFormat: {
                month: leantime.i18n.__("language.columnFormatMonth"),
                week: leantime.i18n.__("language.columnFormatWeek"),
                day: leantime.i18n.__("language.columnFormatday")
            },
            timeFormat: { // for event elements
                '': leantime.dateHelper.getFormatFromSettings("timeformat", "luxon")
            },
            // locale
            isRTL: leantime.i18n.__("language.isRTL") == "false" ? 0 : 1,
            firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            monthNamesShort: leantime.i18n.__("language.monthNamesShort").split(","),
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            buttonText: {
                prev: '&laquo;',
                next: '&raquo;',
                prevYear: '&nbsp;&lt;&lt;&nbsp;',
                nextYear: '&nbsp;&gt;&gt;&nbsp;',
                today: leantime.i18n.__("buttons.today"),
                month: leantime.i18n.__("buttons.month"),
                week: leantime.i18n.__("buttons.week"),
                day: leantime.i18n.__("buttons.day")
            },
            select: function (start, end, allDay) {
                var title = prompt(leantime.i18n.__("label.event_title"));
                if (title) {
                    calendar.fullCalendar(
                        'renderEvent',
                        {
                            title: title,
                            start: start,
                            end: end,
                            allDay: allDay
                        },
                        true // make the event "stick"
                    );
                }
                calendar.fullCalendar('unselect');
            },
            events: userEvents,
            eventColor: '#0866c6'
        });
    };

    var initEventDatepickers = function () {

        jQuery(document).ready(function () {

            Date.prototype.addDays = function (days) {
                this.setDate(this.getDate() + days);
                return this;
            };
            jQuery.datepicker.setDefaults(
                { beforeShow: function (i) {
                    if (jQuery(i).attr('readonly')) {
                        return false; } } }
            );

            var dateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "jquery");

            from = jQuery("#event_date_from")
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
                    function (date) {
                        to.datepicker("option", "minDate", getDate(this));

                        if (jQuery("#event_date_to").val() == '') {
                            jQuery("#event_date_to").val(jQuery("#event_date_from").val());
                        }
                    }
                ),

            to = jQuery("#event_date_to").datepicker(
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
        });


    };

    var initExportModal = function () {

        var exportModalConfig = {
            sizes: {
                minW: 400,
                minH: 350
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                afterShowCont: function () {

                    jQuery(".formModal").nyroModal(exportModalConfig);
                },
                beforeClose: function () {
                    location.reload();
                }


            },
            titleFromIframe: true
        };
        jQuery(".exportModal").nyroModal(exportModalConfig);

    }

    var initWidgetCalendar = function (element, initialView) {

        let calendarEl = document.querySelector(element);
        let userDateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "luxon");
        let userTimeFormat = leantime.dateHelper.getFormatFromSettings("timeformat", "luxon");


        const calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: leantime.i18n.__("usersettings.timezone"),
            height:'auto',
            initialView: initialView,
            dayHeaderFormat: userDateFormat,
            eventTimeFormat: userTimeFormat,
            slotLabelFormat: userTimeFormat,
            views: {
                multiMonthOneMonth: {
                    type: 'multiMonth',
                    duration: { months: 1 },
                    multiMonthTitleFormat: { month: 'long', year: 'numeric' },
                    dayHeaderFormat: { weekday: 'short' },
                },
                timeGridWeek: {

                },
                listWeek: {
                    listDayFormat: { weekday: 'long' },
                    listDaySideFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "luxon"),
                }
            },
            droppable: true,
            eventSources: eventSources,

            editable: true,
            headerToolbar: false,
            nowIndicator: true,
            bootstrapFontAwesome: {
                close: 'fa-times',
                prev: 'fa-chevron-left',
                next: 'fa-chevron-right',
                prevYear: 'fa-angle-double-left',
                nextYear: 'fa-angle-double-right'
            },
            eventDrop: function (event) {

                if (event.event.extendedProps.enitityType == "ticket") {
                    jQuery.ajax({
                        type : 'PATCH',
                        url  : leantime.appUrl + '/api/tickets',
                        data : {
                            id: event.event.extendedProps.enitityId,
                            editFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                            timeFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                            editTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                            timeTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),
                        }
                    });
                } else if (event.event.extendedProps.enitityType == "event") {
                    jQuery.ajax({
                        type : 'PATCH',
                        url  : leantime.appUrl + '/api/calendar',
                        data : {
                            id: event.event.extendedProps.enitityId,
                            dateFrom: event.event.startStr,
                            dateTo: event.event.endStr
                        }
                    })
                }
            },
            eventResize: function (event) {

                if (event.event.extendedProps.enitityType == "ticket") {
                    jQuery.ajax({
                        type : 'PATCH',
                        url  : leantime.appUrl + '/api/tickets',
                        data : {
                            id: event.event.extendedProps.enitityId,
                            editFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                            timeFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                            editTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                            timeTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),
                        }
                    })
                } else if (event.event.extendedProps.enitityType == "event") {
                    jQuery.ajax({
                        type : 'PATCH',
                        url  : leantime.appUrl + '/api/calendar',
                        data : {
                            id: event.event.extendedProps.enitityId,
                            dateFrom: event.event.startStr,
                            dateTo: event.event.endStr
                        }
                    })
                }

            },
            eventMouseEnter: function () {
            },
            dateClick: function (info) {
                if (info.view.type == "timeGridDay") {
                }
            },
            eventReceive: function (event) {

                jQuery.ajax({
                    type : 'PATCH',
                    url  : leantime.appUrl + '/api/tickets',
                    data : {
                        id: event.event.id,
                        editFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                        timeFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                        editTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                        timeTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),

                    }
                })

            },
            eventDragStart: function (event) {

            },
            eventDidMount: function (info) {

                if (info.isDraggable === false) {
                    jQuery(info.el).addClass("locked");
                }

                if (info.event.extendedProps.location != null
                    && info.event.extendedProps.location != ""
                    && info.event.extendedProps.location.indexOf("http") == 0
                ) {
                    //jQuery(info.el).prepend("<div class='pull-right'><a href='"+info.event.extendedProps.location+"'>Join Call</a></div>")
                    jQuery(info.el).attr("href", info.event.extendedProps.location);
                    jQuery(info.el).attr("target", "_blank");
                }
            }
        });

        jQuery(document).ready(function () {
            //let tickets = jQuery("#yourToDoContainer")[0];

            jQuery("#yourToDoContainer").find(".ticketBox").each(function () {

                var currentTicket = jQuery(this);
                jQuery(this).data('event', {
                    id: currentTicket.attr("data-val"),
                    title: currentTicket.find(".titleContainer strong").text(),
                    color: 'var(--accent2)',
                    enitityType: "ticket",
                    url: '#/tickets/showTicket/' + currentTicket.attr("data-val"),
                });

                jQuery(this).draggable({
                    zIndex: 999999,
                    revert: true,      // will cause the event to go back to its
                    revertDuration: 0,  //  original position after the drag
                    helper: "clone",
                    appendTo: '.maincontent',
                    cursor: "grab",
                    cursorAt: { bottom: 5, right: 5},
                });


            });

            var tickets =  jQuery("#yourToDoContainer")[0];
            if (tickets) {
                new FullCalendar.ThirdPartyDraggable(tickets, {
                    itemSelector: '.ticketBox',
                    eventDragMinDistance: 10,
                    eventData: function (eventEl) {
                        return {
                            id: jQuery(eventEl).attr("data-val"),
                            title: jQuery(eventEl).find(".titleContainer strong").text(),
                            borderColor: 'var(--accent2)',
                            enitityType: "ticket",
                            dateContext: "edit",
                            duration: '01:00',
                            url: '#/tickets/showTicket/' + jQuery(eventEl).attr("data-val"),
                        };
                    }
                });
            }

            calendar.scrollToTime(Date.now());
        });


        htmx.onLoad(function (content) {

            // look up all elements with the tomselect class on it within the element
            var allSelects = htmx.findAll(content, "#yourToDoContainer")
            let select;
            for (var i = 0; i < allSelects.length; i++) {
                const tickets = allSelects[i];

                /* store data so the calendar knows to render an event upon drop
                jQuery(this).data('event', {
                    title: $.trim($(this).text()), // use the element's text as the event title
                    stick: true // maintain when user navigates (see docs on the renderEvent method)
                });*/

                // make the event draggable using jQuery UI
                jQuery(tickets).find(".ticketBox").each(function () {

                    var currentTicket = jQuery(this);
                    jQuery(this).data('event', {
                        id: currentTicket.attr("data-val"),
                        title: currentTicket.find(".titleContainer strong").text(),
                        borderColor: 'var(--accent2)',
                        enitityType: "ticket",
                        url: '#/tickets/showTicket/' + currentTicket.attr("data-val"),
                    });

                    jQuery(this).draggable({
                        zIndex: 999999,
                        revert: true,      // will cause the event to go back to its
                        revertDuration: 0,  //  original position after the drag
                        helper: "clone",
                        appendTo: '.maincontent',
                        cursor: "grab",
                        cursorAt: { bottom: 5, right: 5},
                    });
                });


                new FullCalendar.ThirdPartyDraggable(tickets, {
                    eventDragMinDistance: 10,
                    itemSelector: '.ticketBox',
                    eventData: function (eventEl) {
                        return {
                            id: jQuery(eventEl).attr("data-val"),
                            title: jQuery(eventEl).find(".titleContainer strong").text(),
                            color: 'var(--accent2)',
                            enitityType: "ticket",
                            duration: '01:00',
                            url: '#/tickets/showTicket/' + jQuery(eventEl).attr("data-val"),
                        };
                    }
                });

                calendar.scrollToTime(Date.now());
            }

        });

        calendar.setOption('locale', leantime.i18n.__("language.code"));
        calendar.render();

        jQuery(".minCalendar .calendarTitle h2").text(calendar.getCurrentData().viewTitle);

        jQuery('.minCalendar .fc-prev-button').click(function () {
            calendar.prev();
            calendar.getCurrentData()
            jQuery(".minCalendar .calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery('.minCalendar .fc-next-button').click(function () {
            calendar.next();
            jQuery(".minCalendar .calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery('.minCalendar .fc-today-button').click(function () {
            calendar.today();
            jQuery(".minCalendar .calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery(".minCalendar .calendarViewSelect").on("change", function (e) {

            calendar.changeView(jQuery(".minCalendar .calendarViewSelect option:selected").val());

            jQuery(".minCalendar .calendarTitle h2").text(calendar.getCurrentData().viewTitle);

            jQuery.ajax({
                type : 'PATCH',
                url  : leantime.appUrl + '/api/submenu',
                data : {
                    submenu : "dashboardCalendarView",
                    state   : jQuery(".minCalendar .calendarViewSelect option:selected").val()
                }
            });

        });

        return calendarEl;
    }


    // Make public what you want to have public, everything else is private
    return {
        initCalendar:initCalendar,
        initEventDatepickers:initEventDatepickers,
        initExportModal:initExportModal,
        initWidgetCalendar:initWidgetCalendar
    };
})();
