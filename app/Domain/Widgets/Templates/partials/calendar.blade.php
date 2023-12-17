@props([
    'includeTitle' => true,
    'calendar' => [],
])

@dispatchEvent('beforeCalendar')

<div class="tw-h-full minCalendar">

    <h5 class="subtitle tw-pb-m">🗓️ {{ __('headlines.calendar') }}</h5>

    <div class="clear"></div>
    <div class="fc-toolbar">
        <div class="fc-left pull-left">
            <div class="fc-button-group pull-left">
                <button class="btn btn-default fc-today-button fc-button fc-state-default fc-corner-left pull-left
       fc-corner-right fc-state-disabled">Today</button>

                <button class="btn btn-link fc-prev-button fc-button fc-state-default fc-corner-left pull-left"
                        type="button">
                    <i class="fa fa-chevron-left"></i>
                </button>
                <button class="btn btn-link fc-next-button fc-button fc-state-default fc-corner-
      right pull-left" type="button">
                    <i class="fa fa-chevron-right"></i>
                </button>
            </div>

        </div>
        <div class="fc-right pull-right">
            <div class="fc-button-group">
                <select id="calendarViewSelect">
                    <option class="fc-agendaDay-button fc-button fc-state-default fc-
           corner-right" value="multiMonthOneMonth" @if($tpl->getToggleState("dashboardCalendarView") == 'multiMonthOneMonth') selected='selected' @endif>Month</option>
                    <option class="fc-agendaWeek-button fc-button fc-state-
          default" value="timeGridDay" @if($tpl->getToggleState("dashboardCalendarView") == 'timeGridDay') selected='selected' @endif>Day</option>
                    <option class="fc-agendaWeek-button fc-button fc-state-
          default" value="listWeek" @if($tpl->getToggleState("dashboardCalendarView") == 'listWeek') selected='selected' @endif>List</option>
                </select>
            </div>
        </div>
        <div class="fc-center center tw-pt-[7px]" id="calendarTitle">
            <h2></h2>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div class="minCalendarWrapper minCalendar tw-h-full" style="height:calc(100% - 55px)"></div>
</div>

<script>

        var eventSources = [];

        var events = {events: [
            @foreach ($calendar as $event)
            {

                title: {!! json_encode($event['title']) !!},

                start: new Date({{
                    $event['dateFrom']['y'] . ',' .
                    ($event['dateFrom']['m'] - 1) . ',' .
                    $event['dateFrom']['d'] . ',' .
                    $event['dateFrom']['h'] . ',' .
                    $event['dateFrom']['i'] }}),
                @if (isset($event['dateTo']))
                    end: new Date({{
                        $event['dateTo']['y'] . ',' .
                        ($event['dateTo']['m'] - 1) . ',' .
                        $event['dateTo']['d'] . ',' .
                        $event['dateTo']['h'] . ',' .
                        $event['dateTo']['i'] }}),
                @endif
                @if ((isset($event['allDay']) && $event['allDay'] === true))
                    allDay: true,
                @else
                    allDay: false,
                @endif
                enitityId: {{ $event['id'] }},
                @if (isset($event['eventType']) && $event['eventType'] == 'calendar')
                    url: '#/calendar/editEvent/{{ $event['id'] }}',
                    backgroundColor: '{{ $event['backgroundColor'] ?? "var(--accent2)" }}',
                    borderColor: '{{ $event['borderColor'] ?? "var(--accent2)" }}',
                    enitityType: "event",
                @else
                    url: '#/tickets/showTicket/{{ $event['id'] }}?projectId={{ $event['projectId'] }}',
                    backgroundColor: '{{ $event['backgroundColor'] ?? "var(--accent2)" }}',
                    borderColor: '{{ $event['borderColor'] ?? "var(--accent2)" }}',
                    enitityType: "ticket",
                @endif
            },
         @endforeach
        ]};

        eventSources.push(events);

        <?php
        $externalCalendars = $tpl->get("externalCalendars");

        foreach($externalCalendars as $externalCalendar) { ?>
        eventSources.push(
            {
                url: '<?=BASE_URL ?>/calendar/externalCal/<?=$externalCalendar['id'] ?>',
                format: 'ics',
                color: '<?=$externalCalendar['colorClass'] ?>',
                editable: false,
            }
        );

        <?php } ?>

        let calendarEl = document.querySelector(".minCalendarWrapper")

        const calendar = new FullCalendar.Calendar(calendarEl, {
                height:'auto',
                initialView: '{{ $tpl->getToggleState("dashboardCalendarView") ? $tpl->getToggleState("dashboardCalendarView") : "timeGridDay" }}',
                views: {
                    multiMonthOneMonth: {
                        type: 'multiMonth',
                        duration: { months: 1 },
                        multiMonthTitleFormat: { month: 'long', year: 'numeric' },
                    }
                },
                dayHeaderFormat: {
                    weekday: 'long',
                    month: 'numeric',
                    day: 'numeric',
                    omitCommas: true
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


                    if(event.event.extendedProps.enitityType == "ticket") {
                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/tickets',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                editFrom: event.event.startStr,
                                editTo: event.event.endStr
                            }
                        });

                    }else if(event.event.extendedProps.enitityType == "event") {

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

                    if(event.event.extendedProps.enitityType == "ticket") {
                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/tickets',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                editFrom: event.event.startStr,
                                editTo: event.event.endStr
                            }
                        })
                    }else if(event.event.extendedProps.enitityType == "event") {

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
                eventMouseEnter: function() {
                },
                dateClick: function(info) {
                    if(info.view.type == "timeGridDay") {

                    }
                },
                eventReceive: function(event) {

                    console.log(event.event);

                    jQuery.ajax({
                        type : 'PATCH',
                        url  : leantime.appUrl + '/api/tickets',
                        data : {
                            id: event.event.id,
                            editFrom: event.event.startStr,
                            editTo: event.event.endStr
                        }
                    })

                },
                eventDragStart: function(event) {
                    console.log(event);
                },
                eventDidMount: function (info) {
                    console.log(info);
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

        jQuery(document).ready(function() {
            //let tickets = jQuery("#yourToDoContainer")[0];

            jQuery("#yourToDoContainer").find(".ticketBox").each(function(){

                var currentTicket = jQuery(this);
                jQuery(this).data('event', {
                    id: currentTicket.attr("data-val"),
                    title: currentTicket.find(".timerContainer strong").text(),
                    color: 'var(--accent1)',
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
            if(tickets) {
                new FullCalendar.ThirdPartyDraggable(tickets, {
                    itemSelector: '.ticketBox',
                    eventDragMinDistance: 10,
                    eventData: function (eventEl) {
                        return {
                            id: jQuery(eventEl).attr("data-val"),
                            title: jQuery(eventEl).find(".timerContainer strong").text(),
                            borderColor: 'var(--accent1)',
                            enitityType: "ticket",
                            duration: '01:00',
                            url: '#/tickets/showTicket/' + jQuery(eventEl).attr("data-val"),
                        };
                    }
                });
            }

            calendar.scrollToTime( Date.now() );
        });


        htmx.onLoad(function(content) {

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
                jQuery(tickets).find(".ticketBox").each(function() {

                    var currentTicket = jQuery(this);
                    jQuery(this).data('event', {
                        id: currentTicket.attr("data-val"),
                        title: currentTicket.find(".timerContainer strong").text(),
                        borderColor: 'var(--accent1)',
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
                    eventData: function(eventEl) {
                        return {
                            id: jQuery(eventEl).attr("data-val"),
                            title: jQuery(eventEl).find(".timerContainer strong").text(),
                            color: 'var(--accent1)',
                            enitityType: "ticket",
                            duration: '01:00',
                            url: '#/tickets/showTicket/' + jQuery(eventEl).attr("data-val"),
                        };
                    }
                });

                calendar.scrollToTime( Date.now() );
            }

        });

        calendar.setOption('locale', leantime.i18n.__("language.code"));
        calendar.render();

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
        jQuery("#calendarViewSelect").on("change", function(e){

            calendar.changeView(jQuery("#calendarViewSelect option:selected").val());

            jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);

            jQuery.ajax({
                type : 'PATCH',
                url  : leantime.appUrl + '/api/submenu',
                data : {
                    submenu : "dashboardCalendarView",
                    state   : jQuery("#calendarViewSelect option:selected").val()
                }
            });

        });





    @dispatchEvent('scripts.beforeClose')

</script>
