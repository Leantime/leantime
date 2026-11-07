{{-- Starting a timer needs timesheets.create; its status refresh (stopwatch/get-status) needs timesheets.view. --}}
@can('timesheets.create')
@can('timesheets.view')
    @php
        // Re-render on every timer change; while a timer runs, also refresh the running time once a minute.
        $timerEvents = \Leantime\Domain\Timesheets\Events\TimerStarted::CLIENT_EVENT.' from:body, '.\Leantime\Domain\Timesheets\Events\TimerStopped::CLIENT_EVENT.' from:body, '.\Leantime\Core\Events\Htmx\HtmxUiEvents::TimerRefresh->value.' from:body';
        $timerRefreshTrigger = $onTheClock !== false ? $timerEvents.', every 60s' : $timerEvents;
    @endphp

    @if ($onTheClock !== false)
        <x-global::actions.dropdown variant="panel" as="li" menu-as="ul" class="timerHeadMenu" id="timerHeadMenu" hx-get="{{ BASE_URL }}/timesheets/stopwatch/get-status" hx-trigger="{{ $timerRefreshTrigger }}" hx-swap="outerHTML">
            <x-slot:trigger>{!! sprintf(
                __('text.timer_on_todo'),
                $onTheClock['totalTime'],
                e(mb_substr((string) $onTheClock['headline'], 0, 10))
            ) !!}</x-slot:trigger>
            <li>
                <a href="#/tickets/showTicket/{{ $onTheClock['id'] }}">
                    {!! __('links.view_todo') !!}
                </a>
            </li>
            <li>
                <a
                    href="javascript:void(0);"
                    class="punchOut"
                    hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
                    hx-target="#timerHeadMenu"
                    hx-vals='{"ticketId": "{{ $onTheClock['id']  }}", "action":"stop"}'
                    hx-swap="outerHTML"
                >{!! __('links.stop_timer') !!}</a>
            </li>
        </x-global::actions.dropdown>
    @else
        {{-- No timer running: an empty swap target that re-renders when a timer starts. --}}
        <li class="timerHeadMenu" id="timerHeadMenu" hx-get="{{ BASE_URL }}/timesheets/stopwatch/get-status" hx-trigger="{{ $timerRefreshTrigger }}" hx-swap="outerHTML"></li>
    @endif
@endcan
@endcan
