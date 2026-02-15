leantime.calendarController = (function () {

    var closeModal = false;

    //Functions
    var initCalendar = function (userEvents) {

        var date = new Date();
        var d = date.getDate();
        var m = date.getMonth();
        var y = date.getFullYear();

        var heightWindow = document.body.offsetHeight - 260;

        var calendarEl = document.querySelector('#calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: leantime.i18n.__("usersettings.timezone"),
            height: heightWindow,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listDay'
            },
            titleFormat: {
                year: 'numeric',
                month: 'long'
            },
            dayHeaderFormat: {
                weekday: 'short'
            },
            eventTimeFormat: leantime.dateHelper.getFormatFromSettings("timeformat", "luxon"),
            // locale
            direction: leantime.i18n.__("language.isRTL") == "false" ? 'ltr' : 'rtl',
            firstDay: parseInt(leantime.i18n.__("language.firstDayOfWeek"), 10),
            locale: leantime.i18n.__("language.code"),
            buttonText: {
                today: leantime.i18n.__("buttons.today"),
                month: leantime.i18n.__("buttons.month"),
                week: leantime.i18n.__("buttons.week"),
                day: leantime.i18n.__("buttons.day")
            },
            selectable: true,
            select: function (info) {
                var title = prompt(leantime.i18n.__("label.event_title"));
                if (title) {
                    calendar.addEvent({
                        title: title,
                        start: info.start,
                        end: info.end,
                        allDay: info.allDay
                    });
                }
                calendar.unselect();
            },
            events: userEvents,
            eventColor: '#0866c6'
        });
        calendar.render();
    };

    var initEventDatepickers = function () {

        document.addEventListener('DOMContentLoaded', function () {

            Date.prototype.addDays = function (days) {
                this.setDate(this.getDate() + days);
                return this;
            };

            var dateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "flatpickr");
            var fromEl = document.querySelector("#event_date_from");
            var toEl = document.querySelector("#event_date_to");

            var fromPicker = flatpickr(fromEl, {
                dateFormat: dateFormat,
                locale: {
                    firstDayOfWeek: parseInt(leantime.i18n.__("language.firstDayOfWeek"), 10)
                },
                allowInput: true,
                onOpen: function (selectedDates, dateStr, instance) {
                    if (instance.element.hasAttribute('readonly')) {
                        instance.close();
                        return false;
                    }
                },
                onChange: function (selectedDates, dateStr) {
                    if (selectedDates.length > 0) {
                        toPicker.set("minDate", selectedDates[0]);
                    }
                    if (toEl.value === '') {
                        toPicker.setDate(selectedDates[0], true);
                    }
                }
            });

            var toPicker = flatpickr(toEl, {
                dateFormat: dateFormat,
                locale: {
                    firstDayOfWeek: parseInt(leantime.i18n.__("language.firstDayOfWeek"), 10)
                },
                allowInput: true,
                onChange: function (selectedDates) {
                    if (selectedDates.length > 0) {
                        fromPicker.set("maxDate", selectedDates[0]);
                    }
                }
            });
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
                    // nyroModal is a jQuery plugin -- requires jQuery wrapper
                    jQuery(".formModal").nyroModal(exportModalConfig);
                },
                beforeClose: function () {
                    location.reload();
                }


            },
            titleFromIframe: true
        };
        // nyroModal is a jQuery plugin -- requires jQuery wrapper
        jQuery(".exportModal").nyroModal(exportModalConfig);

    }

    var initWidgetCalendar = function (element, initialView) {

        let calendarEl = document.querySelector(element);
        let userDateFormat = leantime.dateHelper.getFormatFromSettings("dateformat", "luxon");
        let userTimeFormat = leantime.dateHelper.getFormatFromSettings("timeformat", "luxon");


        const calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: leantime.i18n.__("usersettings.timezone"),
            height: '100%',
            stickyHeaderDates: true,
            initialView: initialView,
            eventStartEditable: true,
            dayHeaderFormat: userDateFormat,
            eventTimeFormat: userTimeFormat,
            slotLabelFormat: userTimeFormat,
            views: {
                multiMonthOneMonth: {
                    type: 'multiMonth',
                    duration: {months: 1},
                    multiMonthTitleFormat: {month: 'long', year: 'numeric'},
                    dayHeaderFormat: {weekday: 'short'},
                },
                timeGridDay: {
                    dayHeaders: false
                },
                listWeek: {
                    listDayFormat: {weekday: 'long'},
                    listDaySideFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "luxon"),
                }
            },
            droppable: true,
            eventSources: eventSources,

            editable: true,
            headerToolbar: false,
            nowIndicator: true,
            eventDrop: function (event) {
                if (event.event.extendedProps.enitityType == "ticket") {
                    fetch(leantime.appUrl + '/api/tickets', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: event.event.extendedProps.enitityId,
                            editFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                            timeFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                            editTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                            timeTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),
                        })
                    });
                } else if (event.event.extendedProps.enitityType == "event") {
                    fetch(leantime.appUrl + '/api/calendar', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: event.event.extendedProps.enitityId,
                            dateFrom: event.event.startStr,
                            dateTo: event.event.endStr
                        })
                    });
                }
            },
            eventResize: function (event) {
                if (event.event.extendedProps.enitityType == "ticket") {
                    fetch(leantime.appUrl + '/api/tickets', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: event.event.extendedProps.enitityId,
                            editFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                            timeFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                            editTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                            timeTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),
                        })
                    });
                } else if (event.event.extendedProps.enitityType == "event") {
                    fetch(leantime.appUrl + '/api/calendar', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            id: event.event.extendedProps.enitityId,
                            dateFrom: event.event.startStr,
                            dateTo: event.event.endStr
                        })
                    });
                }

            },
            eventReceive: function (event) {

                fetch(leantime.appUrl + '/api/tickets', {
                    method: 'PATCH',
                    credentials: 'include',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({
                        id: event.event.id,
                        editFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                        timeFrom: luxon.DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                        editTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                        timeTo: luxon.DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),
                    })
                });

            },
            eventDragStart: function (event) {

            },
            eventDidMount: function (info) {

                if (info.isDraggable === false) {
                    info.el.classList.add("locked");
                }

                if (info.event.extendedProps.location != null
                    && info.event.extendedProps.location != ""
                    && info.event.extendedProps.location.indexOf("http") == 0
                ) {
                    info.el.setAttribute("href", info.event.extendedProps.location);
                    info.el.setAttribute("target", "_blank");
                }
            }
        });

        // Initialize immediately — DOMContentLoaded has already fired
        // when this runs inside HTMX-loaded widget content.
        var todoContainer = document.querySelector("#yourToDoContainer");
        if (todoContainer) {
            initializeThirdPartyDraggable(todoContainer);
        }

        initButtons();

        calendar.scrollToTime(Date.now());

        // function setupDraggableTickets() {
        //     jQuery("#yourToDoContainer").find(".ticketBox").each(function () {
        //         setupTicketDraggable(jQuery(this));
        //     });
        // }
        //
        // function setupTicketDraggable(ticketElement) {
        //     var currentTicket = ticketElement;
        //     currentTicket.data('event', {
        //         id: currentTicket.attr("data-val"),
        //         title: currentTicket.find(".titleContainer strong").text(),
        //         color: 'var(--accent2)',
        //         enitityType: "ticket",
        //         url: '#/tickets/showTicket/' + currentTicket.attr("data-val"),
        //     });
        //
        //     currentTicket.draggable({
        //
        //         zIndex: 999999,
        //         revert: true,      // will cause the event to go back to its
        //         revertDuration: 0,  //  original position after the drag
        //         helper: "clone",
        //         appendTo: '.maincontent',
        //         cursor: "grab",
        //         cursorAt: {bottom: 5, right: 5},
        //         distance: 10,       // Minimum distance before drag starts
        //         delay: 150,         // Small delay to allow for sortable to initialize first
        //     });
        // }

        function initializeThirdPartyDraggable(element) {

            var tickets = element;
            if (tickets) {
                new FullCalendar.ThirdPartyDraggable(tickets, {
                    itemSelector: '.draggable-todo',
                    mirrorClass: 'dragging-mirror',
                    eventDragMinDistance: 10,
                    mirrorSelector: function (el) {
                        return el.closest('.ticketBox');
                    },
                    eventData: function (eventEl) {

                        let ticketEventData = JSON.parse(eventEl.dataset.event);

                        return {
                            id: ticketEventData.id,
                            title:  ticketEventData.title,
                            color: ticketEventData.color,
                            enitityType: "ticket",
                            duration: '01:00',
                            url: ticketEventData.url,
                        };

                    }
                });
            }

            calendar.scrollToTime(Date.now());
        };

        function initButtons() {

            calendar.setOption('locale', leantime.i18n.__("language.code"));
            calendar.render();

            calendar.scrollToTime(Date.now());

            var prevBtn = document.querySelector('.minCalendar .fc-prev-button');
            if (prevBtn) {
                prevBtn.addEventListener('click', function () {
                    calendar.prev();
                    calendar.getCurrentData();
                });
            }
            var nextBtn = document.querySelector('.minCalendar .fc-next-button');
            if (nextBtn) {
                nextBtn.addEventListener('click', function () {
                    calendar.next();
                });
            }
            var todayBtn = document.querySelector('.minCalendar .fc-today-button');
            if (todayBtn) {
                todayBtn.addEventListener('click', function () {
                    calendar.today();
                });
            }
            document.querySelectorAll(".minCalendar .calendarViewSelect").forEach(function (el) {
                el.addEventListener("click", function (e) {

                    console.log(this.dataset.value);
                    calendar.changeView(this.dataset.value);

                    fetch(leantime.appUrl + '/api/submenu', {
                        method: 'PATCH',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({
                            submenu: "dashboardCalendarView",
                            state: this.dataset.value
                        })
                    });

                });
            });

            // Initialize day selector buttons
            document.querySelectorAll('.day-button').forEach(function (el) {
                el.addEventListener('click', function () {
                    var date = this.dataset.date;
                    calendar.gotoDate(date);

                    // Update active state
                    document.querySelectorAll('.day-button').forEach(function (btn) {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                });
            });
        }

        htmx.onLoad(function (content) {



            // Find any todo containers that were loaded via HTMX
            if(content.id == "yourToDoContainer") {
                initializeThirdPartyDraggable(content);
            }

            return calendarEl;
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        initCalendar:initCalendar,
        initEventDatepickers:initEventDatepickers,
        initExportModal:initExportModal,
        initWidgetCalendar:initWidgetCalendar
    };
})();
