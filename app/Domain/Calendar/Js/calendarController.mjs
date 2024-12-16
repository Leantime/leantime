/** @format */
import jQuery from 'jquery';
import htmx from 'htmx.org';
import {Calendar, ThirdPartyDraggable} from 'fullcalendar';
import { appUrl } from 'js/app/core/instance-info.module';
import { getFormatFromSettings } from 'js/app/components/dates/dateHelper.module';
import { DateTime } from 'luxon';
import iCalendarPlugin from '@fullcalendar/icalendar';
import luxon3Plugin from '@fullcalendar/luxon3'

export const initShowMyCalendar = function (
    element,
    eventSources,
    initialView,
) {
    const heightWindow = jQuery("body").height() - 210;

    const calendar = new Calendar(element, {
        plugins: [iCalendarPlugin, luxon3Plugin],
        timeZone: leantime.i18n.__("usersettings.timezone"),
        height: heightWindow,
        initialView: initialView,
        eventSources: eventSources,
        editable: true,
        headerToolbar: false,
        dayHeaderFormat: getFormatFromSettings("dateformat", "luxon"),
        eventTimeFormat: getFormatFromSettings("timeformat", "luxon"),
        slotLabelFormat: getFormatFromSettings("timeformat", "luxon"),
        views: {
            timeGridDay: {},
            timeGridWeek: {},
            dayGridMonth: {
                dayHeaderFormat: { weekday: 'short' },
            },
            multiMonthYear: {
                showNonCurrentDates: true,
                multiMonthTitleFormat: { month: 'long', year: 'numeric' },
                dayHeaderFormat: { weekday: 'short' },
            }
        },
        nowIndicator: true,
        bootstrapFontAwesome: {
            close: 'fa-times',
            prev: 'fa-chevron-left',
            next: 'fa-chevron-right',
            prevYear: 'fa-angle-double-left',
            nextYear: 'fa-angle-double-right'
        },
        eventDrop: function (event) {
            if(event.event.extendedProps.entityType == "ticket") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : leantime.appUrl + '/api/tickets',
                    data : {
                        id: event.event.extendedProps.entityId,
                        editFrom: event.event.startStr,
                        editTo: event.event.endStr
                    }
                });
            }else if(event.event.extendedProps.entityType == "event") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : leantime.appUrl + '/api/calendar',
                    data : {
                        id: event.event.extendedProps.entityId,
                        dateFrom: event.event.startStr,
                        dateTo: event.event.endStr
                    }
                })
            }
        },
        eventResize: function (event) {
            if(event.event.extendedProps.entityType == "ticket") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : leantime.appUrl + '/api/tickets',
                    data : {
                        id: event.event.extendedProps.entityId,
                        editFrom: event.event.startStr,
                        editTo: event.event.endStr
                    }
                })
            }else if(event.event.extendedProps.entityType == "event") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : leantime.appUrl + '/api/calendar',
                    data : {
                        id: event.event.extendedProps.entityId,
                        dateFrom: event.event.startStr,
                        dateTo: event.event.endStr
                    }
                })
            }
        },
        eventMouseEnter: function() {},
    });

    calendar.setOption('locale', leantime.i18n.__("language.code"));
    calendar.render();
    calendar.scrollToTime( Date.now() );
    jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);

    jQuery('.fc-prev-button').click(function() {
        calendar.prev();
        calendar.getCurrentData()
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
    });
    jQuery('.fc-next-button').click(function() {
        calendar.next();
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
    });
    jQuery('.fc-today-button').click(function() {
        calendar.today();
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
    });
    jQuery("#my-select").on("change", function(e){
        calendar.changeView(jQuery("#my-select option:selected").val());

        jQuery.ajax({
            type : 'PATCH',
            url  : appUrl + '/api/submenu',
            data : {
                submenu : "myCalendarView",
                state   : jQuery("#my-select option:selected").val()
            }
        });
    });
};

export const initTicketsCalendar = function (
    element,
    initialView,
    events,
) {
    const heightWindow = jQuery("body").height() - 190;
    const calendar = new Calendar(element, {
        plugins: [luxon3Plugin],
        timeZone: leantime.i18n.__("usersettings.timezone"),
        height: heightWindow,
        initialView: initialView,
        events: events,
        editable: true,
        headerToolbar: false,
        dayHeaderFormat: getFormatFromSettings("dateformat", "luxon"),
        eventTimeFormat: getFormatFromSettings("timeformat", "luxon"),
        slotLabelFormat: getFormatFromSettings("timeformat", "luxon"),
        nowIndicator: true,
        bootstrapFontAwesome: {
            close: 'fa-times',
            prev: 'fa-chevron-left',
            next: 'fa-chevron-right',
            prevYear: 'fa-angle-double-left',
            nextYear: 'fa-angle-double-right'
        },
        eventDrop: function (event) {
            jQuery.ajax({
                type : 'PATCH',
                url  : appUrl + '/api/tickets',
                data : {
                    id: event.event.extendedProps.entityId,
                    editFrom: event.event.startStr,
                    editTo: event.event.endStr
                }
            });
        },
        eventResize: function (event) {
            jQuery.ajax({
                type : 'PATCH',
                url  : appUrl + '/api/tickets',
                data : {
                    id: event.event.extendedProps.entityId,
                    editFrom: event.event.startStr,
                    editTo: event.event.endStr
                }
            })
        },
        eventMouseEnter: function() {},
    });
    calendar.setOption('locale', leantime.i18n.__("language.code"));
    calendar.render();
    calendar.scrollToTime( 100 );
    jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
    jQuery('.fc-prev-button').click(function() {
        calendar.prev();
        calendar.getCurrentData()
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
    });
    jQuery('.fc-next-button').click(function() {
        calendar.next();
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
    });
    jQuery('.fc-today-button').click(function() {
        calendar.today();
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
    });
    jQuery("#my-select").on("change", function(e){
        calendar.changeView(jQuery("#my-select option:selected").val());
        jQuery.ajax({
            type : 'PATCH',
            url  : appUrl + '/api/submenu',
            data : {
                submenu : "myProjectCalendarView",
                state   : jQuery("#my-select option:selected").val()
            }
        });
    });
};

export const initEventDatepickers = function () {
    jQuery(document).ready(function () {

        Date.prototype.addDays = function (days) {
            this.setDate(this.getDate() + days);
            return this;
        };
        jQuery.datepicker.setDefaults(
            { beforeShow: function (inputElement) {
                if (jQuery(inputElement).attr('readonly')) {
                    return false; } } }
        );

        var dateFormat = getFormatFromSettings("dateformat", "jquery");

        from = jQuery("#event_date_from")
            .datepicker(
                {
                    numberOfMonths: 1,
                    dateFormat: getFormatFromSettings("dateformat", "jquery"),
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
                dateFormat: getFormatFromSettings("dateformat", "jquery"),
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


var initWidgetCalendar = function (element, initialView, eventSources) {
    let calendarElement = document.querySelector(element);
    let userDateFormat = getFormatFromSettings("dateformat", "luxon");
    let userTimeFormat = getFormatFromSettings("timeformat", "luxon");

    const calendar = new Calendar(calendarElement, {
        plugins: [iCalendarPlugin, luxon3Plugin],
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
            timeGridWeek: {},
            listWeek: {
                listDayFormat: { weekday: 'long' },
                listDaySideFormat: getFormatFromSettings("dateformat", "luxon"),
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
            if (event.event.extendedProps.entityType == "ticket") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : appUrl + '/api/tickets',
                    data : {
                        id: event.event.extendedProps.entityId,
                        editFrom: DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                        timeFrom: DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                        editTo: DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                        timeTo: DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),
                    }
                });
            } else if (event.event.extendedProps.entityType == "event") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : appUrl + '/api/calendar',
                    data : {
                        id: event.event.extendedProps.entityId,
                        dateFrom: event.event.startStr,
                        dateTo: event.event.endStr
                    }
                })
            }
        },
        eventResize: function (event) {
            if (event.event.extendedProps.entityType == "ticket") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : appUrl + '/api/tickets',
                    data : {
                        id: event.event.extendedProps.entityId,
                        editFrom: DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                        timeFrom: DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                        editTo: DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                        timeTo: DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),
                    }
                })
            } else if (event.event.extendedProps.entityType == "event") {
                jQuery.ajax({
                    type : 'PATCH',
                    url  : appUrl + '/api/calendar',
                    data : {
                        id: event.event.extendedProps.entityId,
                        dateFrom: event.event.startStr,
                        dateTo: event.event.endStr
                    }
                })
            }
        },
        eventMouseEnter: function () {},
        dateClick: function (info) {
            if (info.view.type == "timeGridDay") {
            }
        },
        eventReceive: function (event) {

            jQuery.ajax({
                type : 'PATCH',
                url  : appUrl + '/api/tickets',
                data : {
                    id: event.event.id,
                    editFrom: DateTime.fromJSDate(event.event.start).toFormat(userDateFormat),
                    timeFrom: DateTime.fromJSDate(event.event.start).toFormat(userTimeFormat),
                    editTo: DateTime.fromJSDate(event.event.end).toFormat(userDateFormat),
                    timeTo: DateTime.fromJSDate(event.event.end).toFormat(userTimeFormat),

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
                entityType: "ticket",
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
                new ThirdPartyDraggable(tickets, {
                    itemSelector: '.ticketBox',
                    eventDragMinDistance: 10,
                    eventData: function (eventElement) {
                        return {
                            id: jQuery(eventElement).attr("data-val"),
                            title: jQuery(eventElement).find(".titleContainer strong").text(),
                            borderColor: 'var(--accent2)',
                            entityType: "ticket",
                            dateContext: "edit",
                            duration: '01:00',
                            url: '#/tickets/showTicket/' + jQuery(eventElement).attr("data-val"),
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
                    entityType: "ticket",
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


            new ThirdPartyDraggable(tickets, {
                eventDragMinDistance: 10,
                itemSelector: '.ticketBox',
                eventData: function (eventElement) {
                    return {
                        id: jQuery(eventElement).attr("data-val"),
                        title: jQuery(eventElement).find(".titleContainer strong").text(),
                        color: 'var(--accent2)',
                        entityType: "ticket",
                        duration: '01:00',
                        url: '#/tickets/showTicket/' + jQuery(eventElement).attr("data-val"),
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
            url  : appUrl + '/api/submenu',
            data : {
                submenu : "dashboardCalendarView",
                state   : jQuery(".minCalendar .calendarViewSelect option:selected").val()
            }
        });
    });

    return calendarElement;
}

export const calendarController = {
    initShowMyCalendar: initShowMyCalendar,
    initTicketsCalendar: initTicketsCalendar,
    initEventDatepickers: initEventDatepickers,
    initWidgetCalendar: initWidgetCalendar
};

export default calendarController;
