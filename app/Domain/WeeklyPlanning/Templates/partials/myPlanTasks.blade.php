{{-- This Week's Plan section — embedded in MyToDos widget --}}
@if(!empty($items))
<div id="my-plan-tasks-section" class="tw-mb-m">
    <div class="tw-flex tw-items-center tw-gap-xs tw-mb-xs"
         style="border-bottom:1px solid var(--main-border-color); padding-bottom:6px;">
        <i class="fa fa-calendar-week" style="color:var(--accent1);"></i>
        <strong class="tw-text-sm">{{ __('weeklyplanning.sections.this_weeks_plan') }}</strong>
        @if($plan)
            <span class="tw-text-xs" style="color:var(--grey);">
                {{ $plan['weekLabel'] }}, {{ $plan['month'] }}
            </span>
        @endif
    </div>

    <ul class="tw-list-none tw-p-0 tw-m-0 tw-flex tw-flex-col tw-gap-xs">
        @foreach($items as $item)
            <li class="tw-flex tw-items-center tw-gap-xs tw-py-xs"
                style="border-bottom:1px solid var(--secondary-background);">

                <span class="label label-{{ match($item['status']) {
                    'completed'     => 'success',
                    'in_progress'   => 'primary',
                    'blocked'       => 'warning',
                    'not_completed' => 'danger',
                    default         => 'default'
                } }} tw-text-xs" style="white-space:nowrap;">
                    {{ __('weeklyplanning.status.'.$item['status']) }}
                </span>

                <span class="tw-flex-1 tw-text-sm {{ $item['status'] === 'completed' ? 'tw-line-through' : '' }}"
                      style="{{ $item['status'] === 'completed' ? 'color:var(--grey);' : '' }}">
                    @if(!empty($item['ticketId']))
                        <a href="{{ BASE_URL }}/tickets/showTicket/{{ $item['ticketId'] }}" preload="mouseover">
                            {{ $item['ticketHeadline'] ?? $item['expectedOutcome'] ?? '—' }}
                        </a>
                    @else
                        {{ $item['expectedOutcome'] ?? '—' }}
                    @endif
                </span>

            </li>
        @endforeach
    </ul>
</div>
@endif
