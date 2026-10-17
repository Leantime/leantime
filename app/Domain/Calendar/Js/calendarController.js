leantime.calendarController = (function () {

    /**
     * Ticket dates (in the user's date/time formats) for a moved, resized or received calendar event.
     *
     * Read from FullCalendar's startStr/endStr, which carry the calendar's own timezone offset, so the
     * values are the wall-clock time the user dropped the event on. Reading the JS Date with
     * luxon.DateTime.fromJSDate() used the browser's timezone instead and shifted every time by the
     * difference whenever the calendar's zone (the user's setting) and the browser's differ.
     *
     * FullCalendar leaves `end` null for single-point events (a task with only a due date, or a to-do
     * dragged in); fall back to the start: same day for all-day events, one hour later for timed ones
     * (#3139, #3733).
     *
     * @param {Object} fcEvent    FullCalendar EventApi
     * @param {string} dateFormat Luxon date format
     * @param {string} timeFormat Luxon time format
     * @returns {{editFrom: string, timeFrom: string, editTo: string, timeTo: string}}
     */
    /**
     * Removes every other calendar event of the same ticket, keeping keptEvent.
     *
     * FullCalendar's getEvents() returns new EventApi wrappers on each call, so comparing them with
     * keptEvent by identity never matched and the kept event removed itself too: a to-do dropped on
     * the calendar was saved but vanished until the page was reloaded. The kept event is tagged with
     * a unique marker instead and recognised by that.
     *
     * @param {Object} calendar  FullCalendar Calendar
     * @param {Object} keptEvent FullCalendar EventApi to keep (enitityId = ticket id)
     */
    var removeStaleTicketCopies = function (calendar, keptEvent) {
        var ticketId = String(keptEvent.extendedProps.enitityId);
        var keepMarker = 'keep-' + Date.now() + '-' + Math.random().toString(36).slice(2);

        keptEvent.setExtendedProp('keepMarker', keepMarker);

        calendar.getEvents().forEach(function (otherEvent) {
            if (otherEvent.extendedProps.keepMarker !== keepMarker
                && otherEvent.extendedProps.enitityType == 'ticket'
                && String(otherEvent.extendedProps.enitityId) === ticketId) {
                otherEvent.remove();
            }
        });
    };

    var buildTicketDateValues = function (fcEvent, dateFormat, timeFormat) {
        var start = luxon.DateTime.fromISO(fcEvent.startStr, { setZone: true });
        var end = fcEvent.endStr
            ? luxon.DateTime.fromISO(fcEvent.endStr, { setZone: true })
            : (fcEvent.allDay ? start : start.plus({ hours: 1 }));

        return {
            editFrom: start.toFormat(dateFormat),
            timeFrom: start.toFormat(timeFormat),
            editTo: end.toFormat(dateFormat),
            timeTo: end.toFormat(timeFormat),
        };
    };

    // Stacks the title under the time instead of FullCalendar's default single-line dot+time+title,
    // which left ~0px for the title in this widget's narrow columns. Builds DOM nodes with textContent
    // only, never HTML, so a title can't be parsed as markup. Reuses FullCalendar's own
    // fc-daygrid-event-dot/fc-event-time/fc-event-title classes for the existing color/theme CSS.
    var monthEventContent = function (info) {
        // info.event can be missing while an external item is being dragged over the calendar.
        if (!info || !info.event) {
            return {domNodes: []};
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'dashboard-month-event';

        var dot = document.createElement('span');
        dot.className = 'fc-daygrid-event-dot';
        wrapper.appendChild(dot);

        if (info.timeText) {
            var time = document.createElement('span');
            time.className = 'fc-event-time';
            time.textContent = info.timeText;
            wrapper.appendChild(time);
        }

        var title = document.createElement('span');
        title.className = 'fc-event-title';
        title.textContent = info.event.title || '';
        wrapper.appendChild(title);

        return {domNodes: [wrapper]};
    };

    var closeModal = false;

    // Latest todo-draggable initializer, refreshed on each initWidgetCalendar() call. The single
    // global htmx.onLoad handler (registered once) calls THIS, so reloading the calendar widget
    // rewires drag/drop to the current calendar instance instead of a stale closure.
    var latestTodoDraggableInit = null;

    // Same reload problem as latestTodoDraggableInit above: disconnected and replaced on each
    // init so a widget reload doesn't stack a second observer reacting to the same popovers.
    var activePopoverObserver = null;

    // Lets a poll loop started by a previous init recognize it's stale and stop, since
    // disconnecting the observer above doesn't cancel an already-scheduled setTimeout.
    var activePopoverGeneration = null;

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

        let ticketDateValues = function (fcEvent) {
            return buildTicketDateValues(fcEvent, userDateFormat, userTimeFormat);
        };


        const calendar = new FullCalendar.Calendar(calendarEl, {
            timeZone: leantime.i18n.__("usersettings.timezone"),
            height: 'calc(100% - 65px)',
            stickyHeaderDates: true,
            initialView: initialView,
            eventStartEditable: true,
            dayHeaderFormat: userDateFormat,
            eventTimeFormat: userTimeFormat,
            slotLabelFormat: userTimeFormat,
            firstDay: leantime.i18n.__("language.firstDayOfWeek"),
            // closeHint has no entry in FullCalendar's fr locale pack (and several others),
            // so it silently falls back to English; Leantime already has a string for this.
            closeHint: leantime.i18n.__("buttons.close"),
            // fr locale pack's "+N en plus" reads as an awkward double "plus"; French only,
            // every other locale keeps its own pack's moreLinkText untouched. Spread-in (not
            // `moreLinkText: ... : undefined`) so other languages' own default isn't overwritten.
            // Function form (not a plain string) is needed to match singular/plural "autre(s)".
            ...(leantime.i18n.__("language.code") === 'fr' ? {
                moreLinkText: function (num) {
                    return '+' + num + ' ' + (num === 1 ? 'autre' : 'autres');
                },
            } : {}),
            views: {
                // Kept the view id 'multiMonthOneMonth' (referenced by calendar.blade.php's Month
                // toggle) though the type below is now dayGridMonth, not multiMonth: that component
                // hardcodes dayMaxEvents/dayMaxEventRows to true internally, ignoring the options
                // below, which is why #3863's dayMaxEvents: false never fully worked here.
                multiMonthOneMonth: {
                    type: 'dayGridMonth',
                    titleFormat: {month: 'long', year: 'numeric'},
                    dayHeaderFormat: {weekday: 'short'},
                    fixedWeekCount: false,
                    dayMaxEvents: 2,
                    moreLinkClick: 'popover',
                    eventContent: monthEventContent,
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
            bootstrapFontAwesome: {
                close: 'fa-times',
                prev: 'fa-chevron-left',
                next: 'fa-chevron-right',
                prevYear: 'fa-angle-double-left',
                nextYear: 'fa-angle-double-right'
            },
            eventDrop: function (event) {
                if (event.event.extendedProps.enitityType == "ticket") {
                    leantime.rpc('Tickets.Tickets.patchTicket', {
                        id: event.event.extendedProps.enitityId,
                        values: ticketDateValues(event.event)
                    }).then(function (success) {
                        // patchTicket resolves false when the write fails; undo the visual move.
                        if (! success) {
                            jQuery.growl({ message: leantime.i18n.__("short_notifications.not_saved"), style: "error" });
                            event.revert();
                        }
                    }).catch(function (error) {
                        jQuery.growl({ message: (error && error.message) ? error.message : leantime.i18n.__("short_notifications.not_saved"), style: "error" });
                        event.revert();
                        console.error('Could not update ticket dates', error);
                    });
                } else if (event.event.extendedProps.enitityType == "event") {
                    leantime.rpc('Calendar.Calendar.patch', {
                        id: event.event.extendedProps.enitityId,
                        params: {
                            dateFrom: event.event.startStr,
                            dateTo: event.event.endStr
                        }
                    }).then(function (success) {
                        // Denied/failed update resolves to false — undo the visual move.
                        if (! success) { event.revert(); }
                    }).catch(function (error) {
                        console.error('Could not update event dates', error);
                        event.revert();
                    })
                }
            },
            eventResize: function (event) {
                if (event.event.extendedProps.enitityType == "ticket") {
                    leantime.rpc('Tickets.Tickets.patchTicket', {
                        id: event.event.extendedProps.enitityId,
                        values: ticketDateValues(event.event)
                    }).then(function (success) {
                        // patchTicket resolves false when the write fails; undo the visual move.
                        if (! success) {
                            jQuery.growl({ message: leantime.i18n.__("short_notifications.not_saved"), style: "error" });
                            event.revert();
                        }
                    }).catch(function (error) {
                        jQuery.growl({ message: (error && error.message) ? error.message : leantime.i18n.__("short_notifications.not_saved"), style: "error" });
                        event.revert();
                        console.error('Could not update ticket dates', error);
                    });
                } else if (event.event.extendedProps.enitityType == "event") {
                    leantime.rpc('Calendar.Calendar.patch', {
                        id: event.event.extendedProps.enitityId,
                        params: {
                            dateFrom: event.event.startStr,
                            dateTo: event.event.endStr
                        }
                    }).then(function (success) {
                        // Denied/failed update resolves to false — undo the visual move.
                        if (! success) { event.revert(); }
                    }).catch(function (error) {
                        console.error('Could not update event dates', error);
                        event.revert();
                    })
                }

            },
            eventReceive: function (event) {

                leantime.rpc('Tickets.Tickets.patchTicket', {
                    id: event.event.id,
                    values: ticketDateValues(event.event)
                }).then(function (success) {
                    if (! success) {
                        jQuery.growl({ message: leantime.i18n.__("short_notifications.not_saved"), style: "error" });
                        event.revert();
                        return;
                    }

                    // Keep the dropped event: it is the only one with the new dates (the event source
                    // is a server-rendered snapshot). Make it behave like a scheduled ticket, and drop
                    // any stale copy of the same ticket still shown at its old slot, so it appears
                    // once (#3733).
                    event.event.setExtendedProp('enitityType', 'ticket');
                    event.event.setExtendedProp('enitityId', event.event.id);
                    removeStaleTicketCopies(calendar, event.event);
                }).catch(function (error) {
                        jQuery.growl({ message: (error && error.message) ? error.message : leantime.i18n.__("short_notifications.not_saved"), style: "error" });
                        event.revert();
                        console.error('Could not update ticket dates', error);
                    });

            },
            eventDragStart: function (event) {

            },
            eventDidMount: function (info) {

                if (info.isDraggable === false) {
                    jQuery(info.el).addClass("locked");
                }

                // Month view line-clamps long titles visually; title attribute lets sighted
                // mouse users read the full text on hover (harmless in other views too).
                if (info.event.title) {
                    jQuery(info.el).attr('title', info.event.title);
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

            // Set up draggable for each ticket box
            // setupDraggableTickets();
            //
            // Initialize the ThirdPartyDraggable for the todo container

            if(jQuery("#yourToDoContainer").length > 0) {
                initializeThirdPartyDraggable(jQuery("#yourToDoContainer")[0]);
            }

            initButtons();

            calendar.scrollToTime(Date.now());


        });

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
                    // Must be a CSS selector string, not a function: FullCalendar calls
                    // document.querySelector(this.mirrorSelector) on it directly. Matches
                    // mirrorClass above (the class FullCalendar itself adds to the mirror).
                    mirrorSelector: '.dragging-mirror',
                    eventData: function (eventEl) {

                        let ticketEventData = jQuery(eventEl).data("event");

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

        // Point the shared reference at THIS init's closure (bound to the current calendar instance).
        latestTodoDraggableInit = initializeThirdPartyDraggable;

        function initButtons() {

            calendar.setOption('locale', leantime.i18n.__("language.code"));
            calendar.render();

            calendar.scrollToTime(Date.now());

            // FullCalendar positions the "+N more" popover with no regard for whether it fits
            // the window or a clipping ancestor, and its CSS max-height is a fixed cap even when
            // less space is actually available. getVisibleLimits finds the tightest top/bottom
            // bound in viewport coordinates (window + every non-"visible overflow" ancestor);
            // shrinkPopoverBodyToFit and clampPopoverToVisibleArea below use it to fix both.
            var getVisibleLimits = function (el, margin) {
                var top = margin;
                var bottom = window.innerHeight - margin;
                var ancestor = el.parentElement;
                while (ancestor && ancestor !== document.body) {
                    var ancestorStyle = getComputedStyle(ancestor);
                    if (ancestorStyle.overflowY !== 'visible' || ancestorStyle.overflowX !== 'visible') {
                        var ancestorRect = ancestor.getBoundingClientRect();
                        top = Math.max(top, ancestorRect.top + margin);
                        bottom = Math.min(bottom, ancestorRect.bottom - margin);
                    }
                    ancestor = ancestor.parentElement;
                }

                return {top: top, bottom: bottom};
            };

            // Caps the popover body's height to the space actually available (limits minus
            // header and .fc-popover's own border), never more than calendar.css's min(45vh,
            // 320px). No minimum floor: a floor could itself push the popover past a clipping
            // ancestor's edge in the exact cramped case this exists to handle.
            var shrinkPopoverBodyToFit = function (node, limits) {
                var body = node.querySelector('.fc-popover-body');
                if (!body) {
                    return;
                }
                var header = node.querySelector('.fc-popover-header');
                var headerHeight = header ? header.getBoundingClientRect().height : 0;
                var popoverStyle = getComputedStyle(node);
                var borderHeight = parseFloat(popoverStyle.borderTopWidth || 0)
                    + parseFloat(popoverStyle.borderBottomWidth || 0);
                var maxAllowed = Math.min(320, window.innerHeight * 0.45);
                var available = (limits.bottom - limits.top) - headerHeight - borderHeight;
                body.style.maxHeight = Math.max(0, Math.min(available, maxAllowed)) + 'px';
            };

            // Translates the popover's CSS `top` by `delta`, a pure viewport-space difference
            // (rect/limits are re-measured from scratch each call, so delta never accumulates
            // across repeated calls) -- valid regardless of which coordinate system `top` is
            // itself expressed in. Checks both edges, not just the bottom.
            var clampPopoverToVisibleArea = function (node, margin) {
                var limits = getVisibleLimits(node, margin);
                shrinkPopoverBodyToFit(node, limits);
                var rect = node.getBoundingClientRect();
                var currentTop = parseFloat(node.style.top) || 0;
                var delta = 0;
                if (rect.bottom > limits.bottom) {
                    delta = limits.bottom - rect.bottom;
                } else if (rect.top < limits.top) {
                    delta = limits.top - rect.top;
                }
                if (delta !== 0) {
                    node.style.top = (currentTop + delta) + 'px';
                }
            };

            // FullCalendar can still be populating the popover after it's inserted, so a single
            // check right after insertion undershoots. requestAnimationFrame and a ResizeObserver
            // on the popover both proved unreliable here (confirmed via logging in the deployed
            // bundle); setTimeout is the one that reliably keeps firing, hence this short,
            // bounded poll loop (15 checks, 50ms apart). thisGeneration lets a poll loop from a
            // previous init recognize it's superseded, the same way latestTodoDraggableInit does.
            if (activePopoverObserver) {
                activePopoverObserver.disconnect();
            }
            var thisGeneration = {};
            activePopoverGeneration = thisGeneration;
            activePopoverObserver = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1 || !node.classList || !node.classList.contains('fc-popover')) {
                            return;
                        }
                        var margin = 8;
                        var checksLeft = 15;
                        var pollOnce = function () {
                            if (activePopoverGeneration !== thisGeneration || !document.body.contains(node) || checksLeft <= 0) {
                                return;
                            }
                            checksLeft -= 1;
                            clampPopoverToVisibleArea(node, margin);
                            setTimeout(pollOnce, 50);
                        };
                        pollOnce();
                    });
                });
            });
            activePopoverObserver.observe(calendarEl, {childList: true, subtree: true});

            jQuery('.minCalendar .fc-prev-button').click(function () {
                calendar.prev();
                calendar.getCurrentData()
            });
            jQuery('.minCalendar .fc-next-button').click(function () {
                calendar.next();
            });
            jQuery('.minCalendar .fc-today-button').click(function () {
                calendar.today();
            });
            jQuery(".minCalendar .calendarViewSelect").on("click", function (e) {

                var newView = jQuery(this).data("value");
                calendar.changeView(newView);

                // Show the day selector only in day view
                if (newView === 'timeGridDay') {
                    jQuery('.day-selector').show();
                } else {
                    jQuery('.day-selector').hide();
                }

                leantime.rpc('Api.Api.setSubmenuState', {
                    submenu: "dashboardCalendarView",
                    state: newView
                }).catch(function (e) { console.error('Could not update submenu state', e); });

            });

            // Initialize day selector buttons (only active in day view)
            jQuery('.day-button').on('click', function() {
                var date = jQuery(this).data('date');
                calendar.gotoDate(date);

                // Update active state
                jQuery('.day-button').removeClass('active');
                jQuery(this).addClass('active');
            });
        }

        // Register once. This runs inside an init function that HTMX re-invokes on every
        // swap, so without the guard each load stacked another global onLoad handler
        // (handler leak → compounding churn on the dashboard).
        if (!window.leantime._calendarTodoOnLoadRegistered) {
            window.leantime._calendarTodoOnLoadRegistered = true;
            htmx.onLoad(function (content) {
                // Find any todo containers that were loaded via HTMX. Call the LATEST initializer so
                // drag/drop binds to the current calendar instance, not the one from first init.
                if (content.id == "yourToDoContainer" && latestTodoDraggableInit) {
                    latestTodoDraggableInit(content);
                }
            });
        }
    };

    // Make public what you want to have public, everything else is private
    return {
        buildTicketDateValues: buildTicketDateValues,
        removeStaleTicketCopies: removeStaleTicketCopies,
        initCalendar:initCalendar,
        initEventDatepickers:initEventDatepickers,
        initExportModal:initExportModal,
        initWidgetCalendar:initWidgetCalendar
    };
})();
