@props([
    'ticket',
    'clockedIn',
])

<a href="javascript:void(0);" data-value="{{ $ticket["id"] }}"
   hx-patch="{{ BASE_URL }}/hx/timesheets/stopwatch/start-timer/"
   hx-target="#timerHeadMenu"
   hx-swap="outerHTML"
   hx-vals='{"ticketId": "{{ $ticket["id"] }}", "action":"start"}'

   @if ($clockedIn !== false)
     style='display:none;'
   @endif
>
   <span class="fa-regular fa-clock"></span> {{ __("links.start_work") }}
</a>

<a href="javascript:void(0);" data-value="{{ $ticket["id"] }}"
   hx-patch="{{ BASE_URL }}/hx/timesheets/Stopwatch/stop-timer/"
   hx-target="#timerHeadMenu"
   hx-vals='{"ticketId": "{{ $ticket["id"] }}", "action":"stop"}'
   hx-swap="outerHTML"

    @if ($clockedIn === false || $clockedIn["id"] != $ticket["id"])
     style='display:none;'
    @endif
>
    <span class="fa fa-stop"></span>

    @if (is_array($clockedIn) == true)
    {{ sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), $clockedIn["since"])) }}
    @else
        {{ sprintf(__("links.stop_work_started_at"), date(__("language.timeformat"), time())) }}
   @endif
</a>
<span class='working'
      @if ($clockedIn === false || $clockedIn["id"] === $ticket["id"])
        style='display:none;'
    @endif>>

        {{ __("text.timer_set_other_todo") }}
</span>






