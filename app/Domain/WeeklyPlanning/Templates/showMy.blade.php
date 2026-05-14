@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-calendar-week'">
    <h1>{{ __('weeklyplanning.headlines.my_weekly_plan') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Month navigation --}}
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-l">
            <a href="{{ BASE_URL }}/weekly-planning/showMy?year={{ $prevMonth->year }}&month={{ $prevMonth->month }}"
                class="btn btn-default btn-sm">
                <i class="fa fa-chevron-left"></i> {{ $prevMonth->format('M Y') }}
            </a>
            <h3 class="tw-m-0" style="color:var(--primary-font-color);">
                <i class="fa fa-calendar tw-mr-xs"></i> {{ $monthDate->format('F Y') }}
            </h3>
            <a href="{{ BASE_URL }}/weekly-planning/showMy?year={{ $nextMonth->year }}&month={{ $nextMonth->month }}"
                class="btn btn-default btn-sm">
                {{ $nextMonth->format('M Y') }} <i class="fa fa-chevron-right"></i>
            </a>
        </div>

        @if(empty($weekSlots))
        <x-global::emptyState
            icon="fa-calendar-week"
            headline="{{ __('weeklyplanning.text.no_plan_yet') }}"
            description="{{ __('weeklyplanning.text.no_plan_hint') }}" />
        @else

        @foreach($weekSlots as $index => $slot)
        @php
        $plan = $slot['plan'];
        $items = $slot['items'];
        $weekNum = $index + 1;
        $isCurrent = $slot['isCurrent'] ?? false;
        $start = \Carbon\Carbon::parse($slot['weekStart'])->format('d M');
        $end = \Carbon\Carbon::parse($slot['weekEnd'])->format('d M Y');
        @endphp

        <div class="tw-mb-l tw-rounded"
            style="border:2px solid {{ $isCurrent ? 'var(--accent1)' : 'var(--main-border-color)' }};
                            background:{{ $isCurrent ? 'var(--layered-background)' : 'var(--secondary-background)' }};">

            {{-- Week header --}}
            <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-p-m"
                style="border-bottom:1px solid var(--main-border-color);">
                <div class="tw-flex tw-items-center tw-gap-m">
                    <div>
                        <span class="label {{ $isCurrent ? 'label-primary' : 'label-default' }}">
                            {{ __('weeklyplanning.labels.week') }} {{ $weekNum }}
                        </span>
                        @if($isCurrent)
                        <span class="label label-success tw-ml-xs">
                            <i class="fa fa-circle-dot"></i> {{ __('weeklyplanning.labels.current_week') }}
                        </span>
                        @endif
                    </div>
                    <strong>{{ $start }} – {{ $end }}</strong>
                    @if($plan)
                    @if(!empty($plan['teamLeadFirstname']))
                    <span class="tw-text-sm" style="color:var(--grey);">
                        <i class="fa fa-user-tie"></i>
                        {{ $plan['teamLeadFirstname'] }} {{ $plan['teamLeadLastname'] }}
                    </span>
                    @endif
                    <span class="label label-{{ $plan['status'] === 'reviewed' ? 'success' : ($plan['status'] === 'active' ? 'primary' : 'default') }}">
                        {{ __('weeklyplanning.plan_status.'.($plan['status'] ?? 'draft')) }}
                    </span>
                    @else
                    <span class="label label-default" style="color:var(--grey);">
                        {{ __('weeklyplanning.text.no_plan_this_week') }}
                    </span>
                    @endif
                </div>
                @if($plan)
                <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $plan['id'] }}"
                    class="btn btn-default btn-xs" preload="mouseover">
                    <i class="fa fa-expand"></i> {{ __('weeklyplanning.buttons.view_full_plan') }}
                </a>
                @endif
            </div>

            {{-- Tasks --}}
            <div class="tw-p-m">
                @if(empty($items))
                <p class="tw-text-sm tw-m-0" style="color:var(--grey);">
                    {{ __('weeklyplanning.text.no_tasks_in_plan') }}
                </p>
                @else
                <table class="table table-condensed tw-mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('weeklyplanning.labels.task') }}</th>
                            <th style="width:150px;">{{ __('weeklyplanning.labels.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr id="plan-item-{{ $item['id'] }}">
                            <td>
                                @if(!empty($item['ticketId']))
                                <a href="#/tickets/showTicket/{{ $item['ticketId'] }}" preload="mouseover">
                                    {{ $item['ticketHeadline'] ?? __('weeklyplanning.text.task_deleted') }}
                                </a>
                                @else
                                <span>{{ $item['expectedOutcome'] ?? '—' }}</span>
                                @endif
                                @if(!empty($item['completionReason']))
                                <div class="tw-mt-xs tw-p-xs tw-rounded tw-text-xs"
                                    style="background:var(--primary-background); border-left:3px solid var(--accent2);">
                                    <strong>{{ __('weeklyplanning.labels.reason') }}:</strong>
                                    {{ $item['completionReason'] }}
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($isCurrent && $plan)
                                <div hx-get="{{ BASE_URL }}/hx/weekly-planning/statusUpdate/get?itemId={{ $item['id'] }}"
                                    hx-trigger="load"
                                    hx-swap="innerHTML">
                                    <span class="label label-{{ match($item['status'] ?? 'not_started') {
                                                            'completed'     => 'success',
                                                            'in_progress'   => 'primary',
                                                            'blocked'       => 'warning',
                                                            'not_completed' => 'danger',
                                                            default         => 'default'
                                                        } }}">
                                        {{ __('weeklyplanning.status.'.($item['status'] ?? 'not_started')) }}
                                    </span>
                                </div>
                                @else
                                <span class="label label-{{ match($item['status'] ?? 'not_started') {
                                                        'completed'     => 'success',
                                                        'in_progress'   => 'primary',
                                                        'blocked'       => 'warning',
                                                        'not_completed' => 'danger',
                                                        default         => 'default'
                                                    } }}">
                                    {{ __('weeklyplanning.status.'.($item['status'] ?? 'not_started')) }}
                                </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

        </div>
        @endforeach

        @endif

        {{-- Links --}}
        <div class="tw-text-center tw-mt-m">
            <a href="{{ BASE_URL }}/weekly-planning/showMyHistory" class="btn btn-default">
                <i class="fa fa-history"></i> {{ __('weeklyplanning.buttons.view_history') }}
            </a>
        </div>

    </div>
</div>

@endsection
