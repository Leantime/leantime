{{--
    "Plan" items for the to-do ⋮ menu: the day you chose to work on it (a date-only Work Start).
    Planning never changes the due date, and a to-do with a set time (a time box) is left alone.
--}}
@php
    $planTicketId = (int) ($ticket['id'] ?? 0);
    $planTimezone = dtHelper()->userNow()->getTimezone()->getName();
    $planEditFrom = is_string($ticket['editFrom'] ?? null) ? $ticket['editFrom'] : null;
    $planEditTo = is_string($ticket['editTo'] ?? null) ? $ticket['editTo'] : null;
    $planTimeBoxed = \Leantime\Domain\Tickets\Support\WorkStart::isTimeBoxed($planEditFrom, $planEditTo, $planTimezone);
    $planDays = \Leantime\Domain\Tickets\Support\WorkStart::plannedDays($planEditFrom, $planEditTo, $planTimezone);
    $planToday = dtHelper()->userNow()->format('Y-m-d');
    $planTomorrow = dtHelper()->userNow()->addDay()->format('Y-m-d');
@endphp

@if($planTicketId > 0)
    <li class="nav-header border">{{ __('subtitles.plan') }}</li>

    @if($planTimeBoxed)
        <li class="tw-px-[10px] tw-py-[4px]"><small class="text-muted">{{ __('label.plan_has_set_time') }}</small></li>
    @else
        <li>
            <a href="javascript:void(0);"
               hx-post="{{ BASE_URL }}/hx/tickets/plan/set"
               hx-vals="{{ json_encode(['id' => $planTicketId, 'day' => 'today']) }}"
               hx-swap="none">
                <i class="fa-regular fa-sun"></i> {{ __('links.plan_today') }}
                @if(in_array($planToday, $planDays, true))
                    <i class="fa fa-check tw-ml-[4px]" aria-label="{{ __('label.planned_today') }}"></i>
                @endif
            </a>
        </li>
        <li>
            <a href="javascript:void(0);"
               hx-post="{{ BASE_URL }}/hx/tickets/plan/set"
               hx-vals="{{ json_encode(['id' => $planTicketId, 'day' => 'tomorrow']) }}"
               hx-swap="none">
                <i class="fa-regular fa-moon"></i> {{ __('links.plan_tomorrow') }}
                @if(in_array($planTomorrow, $planDays, true))
                    <i class="fa fa-check tw-ml-[4px]" aria-label="{{ __('label.planned_tomorrow') }}"></i>
                @endif
            </a>
        </li>
        <li class="tw-px-[10px] tw-py-[4px]" onclick="event.stopPropagation();">
            <label for="planDay-{{ $planTicketId }}" class="tw-flex tw-items-center tw-gap-[6px] tw-m-0">
                <i class="fa-regular fa-calendar"></i>
                <span>{{ __('label.plan_pick_day') }}</span>
            </label>
            <input type="date"
                   id="planDay-{{ $planTicketId }}"
                   name="day"
                   min="{{ $planToday }}"
                   value="{{ $planDays[0] ?? '' }}"
                   hx-post="{{ BASE_URL }}/hx/tickets/plan/set"
                   hx-trigger="change"
                   hx-vals="{{ json_encode(['id' => $planTicketId]) }}"
                   hx-swap="none"
                   style="width:100%;" />
        </li>
        @if($planDays !== [])
            <li>
                <a href="javascript:void(0);"
                   hx-post="{{ BASE_URL }}/hx/tickets/plan/clear"
                   hx-vals="{{ json_encode(['id' => $planTicketId]) }}"
                   hx-swap="none">
                    <i class="fa fa-xmark"></i> {{ __('links.clear_plan') }}
                </a>
            </li>
        @endif
    @endif
@endif
