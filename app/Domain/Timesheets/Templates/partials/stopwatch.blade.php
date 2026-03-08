@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor, true))

    <li class='timerHeadMenu' id='timerHeadMenu' hx-get="{{BASE_URL}}/timesheets/stopwatch/get-status" hx-trigger="timerUpdate from:body{{ $onTheClock !== false ? ', every 60s' : '' }}" hx-swap="outerHTML" aria-live="assertive">

    @if ($onTheClock !== false)
        <x-globals::actions.dropdown-menu
            variant="link"
            :label="sprintf(__('text.timer_on_todo'), $onTheClock['totalTime'], substr($onTheClock['headline'], 0, 10))"
        >
            <x-globals::actions.dropdown-item href="#/tickets/showTicket/{{ $onTheClock['id'] }}">
                {!! __('links.view_todo') !!}
            </x-globals::actions.dropdown-item>
            <x-globals::actions.dropdown-item
                href="javascript:void(0);"
                class="punchOut"
                hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
                hx-target="#timerHeadMenu"
                hx-vals='{"ticketId": "{{ $onTheClock["id"] }}", "action":"stop"}'
                hx-swap="outerHTML"
            >
                {!! __('links.stop_timer') !!}
            </x-globals::actions.dropdown-item>
        </x-globals::actions.dropdown-menu>
    @endif
    </li>
@endif
