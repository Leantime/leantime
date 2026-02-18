@if ($login::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor, true))

    <li class='tw:dropdown tw:dropdown-end timerHeadMenu' id='timerHeadMenu' hx-get="{{BASE_URL}}/timesheets/stopwatch/get-status" hx-trigger="timerUpdate from:body">

    @if ($onTheClock !== false)
            <div
                tabindex="0"
                role="button"
                class='dropdown-toggle'
            >{!! sprintf(
                    __('text.timer_on_todo'),
                    $onTheClock['totalTime'],
                    substr($onTheClock['headline'], 0, 10)
                ) !!}</div>

            <ul tabindex="0" class="dropdown-menu tw:dropdown-content tw:menu tw:bg-base-100 tw:rounded-box tw:z-50 tw:min-w-52 tw:p-2 tw:shadow-sm">
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
            </ul>
    @endif
    </li>
@endif
