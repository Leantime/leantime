@props([
    'includeTitle' => true,
    'calendar' => [],
])

@dispatchEvent('beforeCalendar')

<div class="h-full minCalendar">
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
                <select class="calendarViewSelect">
                    <option class="fc-agendaDay-button fc-button fc-state-default fc-
           corner-right" value="multiMonthOneMonth" @if($tpl->getToggleState("dashboardCalendarView") == 'multiMonthOneMonth') selected='selected' @endif>Month</option>
                    <option class="fc-timeGridWeek-button fc-button fc-state-default fc-
           corner-right" value="timeGridWeek" @if($tpl->getToggleState("dashboardCalendarView") == 'timeGridWeek') selected='selected' @endif>Week</option>
                    <option class="fc-agendaWeek-button fc-button fc-state-
          default" value="timeGridDay" @if($tpl->getToggleState("dashboardCalendarView") == 'timeGridDay' || empty($tpl->getToggleState("dashboardCalendarView")) ) selected='selected' @endif>Day</option>
                    <option class="fc-agendaWeek-button fc-button fc-state-
          default" value="listWeek" @if($tpl->getToggleState("dashboardCalendarView") == 'listWeek') selected='selected' @endif>List</option>
                </select>
            </div>
        </div>
        <div class="fc-center center pt-[7px] calendarTitle">
            <h2></h2>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div class="minCalendarWrapper minCalendar h-full" style="height:calc(100% - 55px)"></div>
</div>

<script>

        var eventSources = [];

        var events = {events: [
            @foreach ($calendar as $event)
            {

                title: {!! json_encode($event['title']) !!},

                start: new Date({{ format($event['dateFrom'])->jsTimestamp() }}),
                @if (isset($event['dateTo']))
                    end: new Date({{ format($event['dateTo'])->jsTimestamp() }}),
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
                    url: '{{ BASE_URL }}/calendar/externalCal/<?=$externalCalendar['id'] ?>',
                    format: 'ics',
                    color: '<?=$externalCalendar['colorClass'] ?>',
                    editable: false,
                }
            );
        <?php } ?>

        var initialView =   '{{ $tpl->getToggleState("dashboardCalendarView") ? $tpl->getToggleState("dashboardCalendarView") : "timeGridDay" }}';
        leantime.calendarController.initWidgetCalendar(".minCalendarWrapper", initialView)



    @dispatchEvent('scripts.beforeClose')

</script>
