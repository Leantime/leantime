@props([
    'parentTicketId' => false,
    'onTheClock' => false
 ])

<li id="timerContainer-{{ $parentTicketId }}"
    hx-get="{{BASE_URL}}/tickets/timerButton/get-status/{{ $parentTicketId }}"
    hx-trigger="timerUpdate from:body"
    hx-swap="outerHTML"
    aria-live="assertive"
    class="timerContainer">

    @if ($onTheClock === false)
        <a href="javascript:void(0);" data-value="{{ $parentTicketId }}"
           hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/start-timer/"
           hx-target="#timerHeadMenu"
           hx-swap="outerHTML"
           hx-vals='{"ticketId": "{{ $parentTicketId }}", "action":"start"}'>
            <x-global::elements.icon name="schedule" /> {{ __("links.start_work") }}
        </a>
    @endif

    @if ($onTheClock !== false && $onTheClock["id"] == $parentTicketId)
    <a href="javascript:void(0);" data-value="{{ $parentTicketId }}"
       hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
       hx-target="#timerHeadMenu"
       hx-vals='{"ticketId": "{{ $parentTicketId }}", "action":"stop"}'
       hx-swap="outerHTML">
        <x-global::elements.icon name="stop" />

        @if (is_array($onTheClock) == true)
            {!!  sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), $onTheClock["since"])) !!}
        @else
            {!! sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), time())) !!}
        @endif
    </a>
    @endif
    @if ($onTheClock !== false && $onTheClock["id"] != $parentTicketId)
        <span class='working'>
            {{ __("text.timer_set_other_todo") }}
        </span>
    @endif
</li>

