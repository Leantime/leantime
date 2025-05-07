@props([
    'includeTitle' => true,
    'calendar' => [],
])

@dispatchEvent('beforeCalendar')


<div class="clear minCalendar" style="position:absolute; top:10px; right:35px;">

    <button class="btn btn-link btn-round-icon dropdown-toggle f-right" type="button" data-tippy-content="{{ __('text.calendar_view') }}"
            data-toggle="dropdown"> <i class="fa-solid fa-calendar-week"></i></button>
    <ul class="dropdown-menu pull-right">
        <li>
            <a class="fc-agendaDay-button fc-button fc-state-default fc-corner-right calendarViewSelect" href="javascript:void(0);"
               data-value="multiMonthOneMonth"
               @if($tpl->getToggleState("dashboardCalendarView") == 'multiMonthOneMonth') selected='selected' @endif>Month</a>
        </li>
        <li>
            <a class="fc-timeGridWeek-button fc-button fc-state-default fc-corner-right calendarViewSelect" href="javascript:void(0);"
               data-value="timeGridWeek" @if($tpl->getToggleState("dashboardCalendarView") == 'timeGridWeek') selected='selected' @endif>Week</a>
        </li>
        <li>
            <a class="fc-agendaWeek-button fc-button fc-state-default calendarViewSelect" href="javascript:void(0);"
               data-value="timeGridDay" @if($tpl->getToggleState("dashboardCalendarView") == 'timeGridDay' || empty($tpl->getToggleState("dashboardCalendarView")) ) selected='selected' @endif>Day</a>
        </li>
        <li><a class="fc-agendaWeek-button fc-button fc-state-default calendarViewSelect" href="javascript:void(0);"
               data-value="listWeek" @if($tpl->getToggleState("dashboardCalendarView") == 'listWeek') selected='selected' @endif>List</a></li>
    </ul>

</div>

<div class="tw-h-full minCalendar">
    <div class="clear"></div>
    <div class="fc-toolbar tw-z-10">
        <div class="fc-left tw-flex">
            <div class="day-selector tw-w-full tw-flex tw-gap-2 tw-mb-4 tw-justify-between">
                @php
                    $today = dtHelper()->userNow();
                    $startOfWeek = dtHelper()->userNow()->startOf("week");
                    $week = [];
                    for($i = 0; $i < 7; $i++) {
                        $date = $startOfWeek->modify("+$i days");
                        $week[] = $date;
                    }
                @endphp
                @foreach($week as $day)
                    <button class="day-button tw-rounded-md tw-w-12 tw-h-12 tw-flex tw-flex-col tw-items-center tw-justify-center tw-text-sm {{ $day->format('Y-m-d') === $today->format('Y-m-d') ? 'today active' : '' }}" data-date="{{ $day->format('Y-m-d') }}">
                        <span class="tw-text-xs">{{ $day->format('D') }}</span>
                        <span class="tw-font-medium">{{ $day->format('d') }}</span>
                    </button>
                @endforeach
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
    <div class="minCalendarWrapper">
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
