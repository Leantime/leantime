@props([
    'includeTitle' => true,
    'calendar' => [],
])

@dispatchEvent('beforeCalendar')


<div class="widget-slot-actions minCalendar tw:dropdown tw:dropdown-end">

    <div tabindex="0" role="button" class="btn btn-link btn-round-icon dropdown-toggle f-right" data-tippy-content="{{ __('text.calendar_view') }}"> <i class="fa-solid fa-calendar-week"></i></div>
    <ul tabindex="0" class="dropdown-menu tw:dropdown-content tw:menu tw:bg-base-100 tw:rounded-box tw:z-50 tw:min-w-52 tw:p-2 tw:shadow-sm tw:float-right">
        <li>
            <a class="fc-agendaDay-button fc-button fc-state-default fc-corner-right calendarViewSelect" href="javascript:void(0);"
               onclick="document.activeElement.blur();"
               data-value="multiMonthOneMonth"
               @if($tpl->getToggleState("dashboardCalendarView") == 'multiMonthOneMonth') selected='selected' @endif>Month</a>
        </li>
        <li>
            <a class="fc-timeGridWeek-button fc-button fc-state-default fc-corner-right calendarViewSelect" href="javascript:void(0);"
               onclick="document.activeElement.blur();"
               data-value="timeGridWeek" @if($tpl->getToggleState("dashboardCalendarView") == 'timeGridWeek') selected='selected' @endif>Week</a>
        </li>
        <li>
            <a class="fc-agendaWeek-button fc-button fc-state-default calendarViewSelect" href="javascript:void(0);"
               onclick="document.activeElement.blur();"
               data-value="timeGridDay" @if($tpl->getToggleState("dashboardCalendarView") == 'timeGridDay' || empty($tpl->getToggleState("dashboardCalendarView")) ) selected='selected' @endif>Day</a>
        </li>
        <li><a class="fc-agendaWeek-button fc-button fc-state-default calendarViewSelect" href="javascript:void(0);"
               onclick="document.activeElement.blur();"
               data-value="listWeek" @if($tpl->getToggleState("dashboardCalendarView") == 'listWeek') selected='selected' @endif>List</a></li>
    </ul>

</div>

<div class="tw:h-full tw:flex tw:flex-col minCalendar">
    <div class="day-selector">
        @php
            $today = dtHelper()->userNow();
            $startOfWeek = dtHelper()->userNow()->startOf("week");
            $startDate = $startOfWeek->modify("-14 days");
            $dates = [];
            for($i = 0; $i < 35; $i++) {
                $date = $startDate->modify("+$i days");
                $dates[] = $date;
            }
        @endphp
        @foreach($dates as $day)
            <button class="day-button {{ $day->format('Y-m-d') === $today->format('Y-m-d') ? 'today active' : '' }}" data-date="{{ $day->format('Y-m-d') }}">
                <span class="day-name">{{ $day->format('D') }}</span>
                <span class="day-num">{{ $day->format('d') }}</span>
            </button>
        @endforeach
    </div>
    <div class="minCalendarWrapper tw:flex-1 tw:min-h-0">
    </div>
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
                    url: '<?=BASE_URL ?>/calendar/externalCal/<?=$externalCalendar['id'] ?>',
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
