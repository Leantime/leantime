@php
    $calendarEvents = [];
    foreach ($calendar as $event) {
        $entry = [
            'title'           => $event['title'],
            'start'           => format($event['dateFrom'])->jsTimestamp(),
            'allDay'          => isset($event['allDay']) && $event['allDay'] === true,
            'enitityId'       => (int) $event['id'],
            'url'             => (isset($event['eventType']) && $event['eventType'] === 'calendar')
                                    ? '#/calendar/editEvent/' . (int) $event['id']
                                    : '#/tickets/showTicket/' . (int) $event['id'],
            'backgroundColor' => $event['backgroundColor'] ?? 'var(--accent2)',
            'borderColor'     => $event['borderColor'] ?? 'var(--accent2)',
        ];
        if (!empty($event['dateTo'])) {
            $entry['end'] = format($event['dateTo'])->jsTimestamp();
        }
        $calendarEvents[] = $entry;
    }

    $externalSources = [];
    foreach ($externalCalendars as $ext) {
        $externalSources[] = [
            'url'      => BASE_URL . '/calendar/externalCal/' . (int) $ext['id'],
            'format'   => 'ics',
            'color'    => $ext['colorClass'],
            'editable' => false,
        ];
    }

    $initialView = $tpl->getToggleState('dashboardCalendarView') ?: 'timeGridDay';
    $calendarPayload = json_encode([
        'events' => $calendarEvents,
        'external' => $externalSources,
        'view' => $initialView,
    ]);
@endphp

<div class="htmx-indicator full-width-loader">
    <div class="indeterminate"></div>
</div>

<div id="myTodosCalendarContainer" class="tw-h-full" style="min-height:420px;">

    {{-- Toolbar: view toggle --}}
    <div class="tw-flex tw-items-center tw-gap-1" style="position:absolute; top:10px; right:35px;">
        <div class="btn-group left" style="margin-right:4px;">
            <button class="btn btn-link btn-round-icon"
                title="{{ __('buttons.list_view') }}" aria-label="{{ __('buttons.list_view') }}"
                hx-get="{{ BASE_URL }}/widgets/myToDos/get"
                hx-target="#myTodosCalendarContainer" hx-swap="outerHTML" hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-list"></span>
            </button>
            <button class="btn btn-link btn-round-icon"
                title="Kanban" aria-label="Kanban"
                hx-get="{{ BASE_URL }}/hx/widgets/myToDosKanban/get"
                hx-target="#myTodosCalendarContainer" hx-swap="outerHTML" hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-table-columns"></span>
            </button>
            <button class="btn btn-link btn-round-icon active"
                title="Calendar" aria-label="Calendar" style="color:var(--accent1);">
                <span class="fa-solid fa-calendar-days"></span>
            </button>
            <button class="btn btn-link btn-round-icon"
                title="Recently Updated" aria-label="Recently Updated"
                hx-get="{{ BASE_URL }}/hx/widgets/myToDosRecentlyUpdated/get"
                hx-target="#myTodosCalendarContainer" hx-swap="outerHTML" hx-indicator=".htmx-indicator">
                <span class="fa-solid fa-clock-rotate-left"></span>
            </button>
        </div>
    </div>

    {{-- Calendar view selector --}}
    <div class="tw-flex tw-items-center tw-justify-end tw-gap-1 tw-mb-[8px]"
        style="border-bottom:1px solid var(--main-border-color); padding-bottom:6px; margin-top:44px;">
        <div class="btn-group">
            <button class="btn btn-link btn-round-icon dropdown-toggle" type="button"
                data-tippy-content="{{ __('text.calendar_view') }}" data-toggle="dropdown">
                <i class="fa-solid fa-calendar-week" style="font-size:13px;"></i>
            </button>
            <ul class="dropdown-menu pull-right">
                <li><a class="calendarViewSelect" href="javascript:void(0);" data-value="multiMonthOneMonth">Month</a></li>
                <li><a class="calendarViewSelect" href="javascript:void(0);" data-value="timeGridWeek">Week</a></li>
                <li><a class="calendarViewSelect" href="javascript:void(0);" data-value="timeGridDay">Day</a></li>
                <li><a class="calendarViewSelect" href="javascript:void(0);" data-value="listWeek">List</a></li>
            </ul>
        </div>
    </div>

    {{-- Calendar mount point with JSON payload in data attribute --}}
    <div class="tw-h-full tw-w-full minCalendar" style="height:calc(100% - 100px); min-height:380px; width:100%;">
        <div id="myTodosCalendarMount" class="minCalendarWrapper" style="height:100%; width:100%;" data-payload="{{ $calendarPayload }}"></div>
    </div>

    <style>
        #myTodosCalendarMount { width:100% !important; }
        #myTodosCalendarMount .fc { width:100% !important; height:100% !important; }
        #myTodosCalendarMount .fc-multimonth { width:100% !important; height:100% !important; border:none !important; }
        #myTodosCalendarMount .fc-multimonth-multicol .fc-multimonth-month,
        #myTodosCalendarMount .fc-multimonth-singlecol .fc-multimonth-month { width:100% !important; padding:0 !important; }
        #myTodosCalendarMount .fc-multimonth-daygrid-table { width:100% !important; }
        #myTodosCalendarMount .fc-scrollgrid { width:100% !important; }
        /* Compact rows + readable events */
        #myTodosCalendarMount .fc-multimonth-daygrid-table tr { height:auto !important; }
        #myTodosCalendarMount .fc-daygrid-day-frame { min-height:60px !important; padding:2px 4px !important; }
        #myTodosCalendarMount .fc-daygrid-day-top { justify-content:flex-end; }
        #myTodosCalendarMount .fc-daygrid-day-number { font-size:var(--font-size-xs); padding:2px 4px !important; }
        #myTodosCalendarMount .fc-multimonth-title { font-size:var(--font-size-l); font-weight:600; padding:8px 0 12px !important; }
        #myTodosCalendarMount .fc-col-header-cell { padding:6px 0 !important; }
        #myTodosCalendarMount .fc-col-header-cell-cushion { font-size:var(--font-size-xs); font-weight:500; opacity:.7; }
        #myTodosCalendarMount .fc-daygrid-event { font-size:var(--font-size-xs) !important; padding:1px 4px !important; border-radius:3px !important; margin:1px 2px !important; }
        #myTodosCalendarMount .fc-daygrid-more-link { font-size:var(--font-size-xs) !important; padding:1px 4px !important; }
        /* Responsive shrink */
        @media (max-width: 900px) {
            #myTodosCalendarMount .fc-daygrid-day-frame { min-height:45px !important; }
            #myTodosCalendarMount .fc-col-header-cell-cushion { font-size:10px; }
        }
    </style>

</div>{{-- /#myTodosCalendarContainer --}}

<script type="text/javascript">
window.leantime = window.leantime || {};
leantime.myTodosCalendarInit = function () {
    var mount = document.getElementById('myTodosCalendarMount');
    if (!mount) return;
    var payload;
    try { payload = JSON.parse(mount.getAttribute('data-payload')); } catch (err) { return; }
    var events = payload.events || [];
    for (var i = 0; i < events.length; i++) {
        events[i].start = new Date(events[i].start);
        if (events[i].end) events[i].end = new Date(events[i].end);
    }
    window.eventSources = [];
    var src = new Object();
    src.events = events;
    window.eventSources.push(src);
    var ext = payload.external || [];
    for (var j = 0; j < ext.length; j++) window.eventSources.push(ext[j]);
    leantime.calendarController.initWidgetCalendar('#myTodosCalendarMount', payload.view || 'timeGridDay');
    setTimeout(function () {
        if (window.dispatchEvent) window.dispatchEvent(new Event('resize'));
    }, 50);
};
leantime.myTodosCalendarInit();
</script>
