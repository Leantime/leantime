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
                <x-global::forms.button type="button" content-role="ghost">
                    Today
                </x-global::forms.button>
                
                <x-global::forms.button type="button" content-role="link">
                    <i class="fa fa-chevron-left"></i>
                </x-global::forms.button>
                
                <x-global::forms.button type="button" content-role="link">
                    <i class="fa fa-chevron-right"></i>
                </x-global::forms.button>
            </div>

        </div>
        <div class="fc-right pull-right">
            <div class="fc-button-group">
                <x-global::forms.select class="calendarViewSelect">
                    <x-global::forms.select.select-option 
                        class="fc-agendaDay-button fc-button fc-state-default fc-corner-right" 
                        value="multiMonthOneMonth" 
                        :selected="$tpl->getToggleState('dashboardCalendarView') == 'multiMonthOneMonth'"
                    >
                        Month
                    </x-global::forms.select.select-option>
                
                    <x-global::forms.select.select-option 
                        class="fc-timeGridWeek-button fc-button fc-state-default fc-corner-right" 
                        value="timeGridWeek" 
                        :selected="$tpl->getToggleState('dashboardCalendarView') == 'timeGridWeek'"
                    >
                        Week
                    </x-global::forms.select.select-option>
                
                    <x-global::forms.select.select-option 
                        class="fc-agendaWeek-button fc-button fc-state-default" 
                        value="timeGridDay" 
                        :selected="$tpl->getToggleState('dashboardCalendarView') == 'timeGridDay' || empty($tpl->getToggleState('dashboardCalendarView'))"
                    >
                        Day
                    </x-global::forms.select.select-option>
                
                    <x-global::forms.select.select-option 
                        class="fc-agendaWeek-button fc-button fc-state-default" 
                        value="listWeek" 
                        :selected="$tpl->getToggleState('dashboardCalendarView') == 'listWeek'"
                    >
                        List
                    </x-global::forms.select.select-option>
                </x-global::forms.select>
                
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
