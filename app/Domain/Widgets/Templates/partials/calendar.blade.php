@props([
    'includeTitle' => true,
    'calendar' => [],
])

@dispatchEvent('beforeCalendar')

<div class="minCalendar tw-h-full"
     hx-get="{{BASE_URL}}/widgets/calendar/get"
     hx-trigger="ticketUpdate from:body"
     hx-swap="outerHTML"
    >

    <h5 class="subtitle">{{ __('headlines.calendar') }}</h5>

    <button class="fc-next-button btn btn-default right" type="button" style="position:relative; z-index:9;">
        <span class="fc-icon fc-icon-chevron-right"></span>
    </button>
    <button class="fc-prev-button btn btn-default right" type="button" style="margin-right:5px; position:relative; z-index:9;">
        <span class="fc-icon fc-icon-chevron-left"></span>
    </button>

    <button class="fc-today-button btn btn-default right" style="margin-right:5px; position:relative; z-index:9;">today</button>

    <div class="clear"></div>

    <div id="calendar" class="tw-h-full" style="height:calc(100% - 55px)"></div>
</div>

<script>

    var events = [
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
                    url: '{{ CURRENT_URL }}#/calendar/editEvent/{{ $event['id'] }}',
                    color: 'var(--accent2)',
                    enitityType: "event",
                @else
                    url: '{{ CURRENT_URL }}#/tickets/showTicket/{{ $event['id'] }}?projectId={{ $event['projectId'] }}',
                    color: 'var(--accent1)',
                    enitityType: "ticket",
                @endif
            },
       @endforeach
    ];






        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
                height:'auto',
                initialView: 'multiMonthOneMonth',
                views: {
                    multiMonthOneMonth: {
                        type: 'multiMonth',
                        duration: { months: 1 },
                        multiMonthTitleFormat: { month: 'long', year: 'numeric' },
                    }
                },
                events: events,
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
                }
            }
        );
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
                url  : leantime.appUrl + '/api/submenu',
                data : {
                    submenu : "myCalendarView",
                    state   : jQuery("#my-select option:selected").val()
                }
            });

        });


    @dispatchEvent('scripts.beforeClose')

</script>
