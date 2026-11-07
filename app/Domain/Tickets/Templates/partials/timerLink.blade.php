@props([
    'parentTicketId' => false,
    'onTheClock' => false
 ])

{{-- The start/stop links swap the header stopwatch (#timerHeadMenu), not this container, so they
     carry data-lt-refresh-container: this container still refreshes on the timer event its own click caused. --}}
<li id="timerContainer-{{ $parentTicketId }}"
    hx-get="{{BASE_URL}}/tickets/timerButton/get-status/{{ $parentTicketId }}"
    hx-trigger="{{ \Leantime\Domain\Timesheets\Events\TimerStarted::CLIENT_EVENT }} from:body, {{ \Leantime\Domain\Timesheets\Events\TimerStopped::CLIENT_EVENT }} from:body, {{ \Leantime\Core\Events\Htmx\HtmxUiEvents::TimerRefresh->value }} from:body"
    hx-swap="outerHTML"
    class="timerContainer">

    @if ($onTheClock === false)
        <a href="javascript:void(0);" data-value="{{ $parentTicketId }}"
           hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/start-timer/"
           data-lt-refresh-container
           hx-target="#timerHeadMenu"
           hx-swap="outerHTML"
           hx-vals='{"ticketId": "{{ $parentTicketId }}", "action":"start"}'>
            <span class="fa-regular fa-clock"></span> {{ __("links.start_work") }}
        </a>
    @endif

    @if ($onTheClock !== false && $onTheClock["id"] == $parentTicketId)
    <a href="javascript:void(0);" data-value="{{ $parentTicketId }}"
       hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
       data-lt-refresh-container
       hx-target="#timerHeadMenu"
       hx-vals='{"ticketId": "{{ $parentTicketId }}", "action":"stop"}'
       hx-swap="outerHTML">
        <span class="fa fa-stop"></span>

        @if (is_array($onTheClock) == true)
            {!!  sprintf(__("links.stop_work_started_at"), dtHelper()->userNow()->setTimestamp((int) $onTheClock["since"])->format(__("language.timeformat"))) !!}
        @else
            {!! sprintf(__("links.stop_work_started_at"), dtHelper()->userNow()->format(__("language.timeformat"))) !!}
        @endif
    </a>
    @endif
    @if ($onTheClock !== false && $onTheClock["id"] != $parentTicketId)
        <span class='working'>
            {{ __("text.timer_set_other_todo") }}
        </span>
    @endif
</li>

