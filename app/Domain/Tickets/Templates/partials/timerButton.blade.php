@props([
    'parentTicketId' => false,
    'onTheClock' => false,
    'style' => 'simple' //simple just the button, full witrh button text
 ])

<div id="timer-button-container-{{ $parentTicketId }}"
    hx-get="{{BASE_URL}}/tickets/timerButton/get-status-button/{{ $parentTicketId }}"
    hx-trigger="timerUpdate from:body"
    hx-swap="outerHTML"

    class="tw:relative timerContainer">

    @if ($onTheClock === false)
        <a href="javascript:void(0);" data-value="{{ $parentTicketId }}"
           hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/start-timer/"
           hx-target="#timerHeadMenu"
           hx-swap="outerHTML"
           onclick="this.classList.add('starting');"
           hx-vals='{"ticketId": "{{ $parentTicketId }}", "action":"start"}'
            data-tippy-content="{{ __("links.start_work") }}">

                    <span class="fa-regular fa-circle-play" style="font-size:18px; padding-top:3px;"></span>

            @if($style=="full")
                {{ __("links.start_work") }}
            @endif

        </a>
    @endif

    @if ($onTheClock !== false && $onTheClock["id"] == $parentTicketId)
        <a href="javascript:void(0);" data-value="{{ $parentTicketId }}"
            hx-trigger="click delay:500ms"
           hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/stop-timer/"
           hx-target="#timerHeadMenu"
           hx-vals='{"ticketId": "{{ $parentTicketId }}", "action":"stop"}'
           hx-swap="outerHTML"
           onclick="this.classList.add('stopped');"
           data-tippy-content="@if (is_array($onTheClock) == true) {!! strip_tags(sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), $onTheClock["since"]))) !!} @else {!! strip_tags(sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), time()))) !!} @endif"
        >


                <span class="fa-regular fa-circle-stop" style="font-size:18px; padding-top:3px;"></span>
                @if($style=="full")
                    @if (is_array($onTheClock) == true)
                        {!!  sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), $onTheClock["since"])) !!}
                    @else
                        {!! sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), time())) !!}
                    @endif
                @endif

            <!-- These elements will be added dynamically when the timer is stopped -->
            <div class="success-circle"></div>
            <div class="particles-container">
                <div class="particle particle-1"></div>
                <div class="particle particle-2"></div>
                <div class="particle particle-3"></div>
                <div class="particle particle-4"></div>
                <div class="particle particle-5"></div>
                <div class="particle particle-6"></div>
                <div class="particle particle-7"></div>
                <div class="particle particle-8"></div>
            </div>

        </a>
    @endif
    @if ($onTheClock !== false && $onTheClock["id"] != $parentTicketId)
        <span class='working'>
             @if($style=="full")
                {{ __("text.timer_set_other_todo") }}
            @else
                <span class="fa-solid fa-user-clock" style="font-size:16px; padding-top:3px; color:var(--grey);" data-tippy-content="{{ __("text.timer_set_other_todo") }}"></span>
            @endif
        </span>
    @endif
</div>

