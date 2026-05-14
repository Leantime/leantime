@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-ban'">
    <h1>{{ __('weeklyplanning.headlines.blockers') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <a href="{{ BASE_URL }}/weekly-planning/showTeam" class="btn btn-default btn-sm tw-mb-m">
            <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
        </a>

        @if(count($blockedItems) === 0)
            <x-global::emptyState
                icon="fa-check-circle"
                headline="{{ __('weeklyplanning.text.no_blockers') }}"
                description="{{ __('weeklyplanning.text.no_blockers_hint') }}"
            />
        @else
            <p class="tw-text-sm tw-mb-m" style="color:var(--grey);">
                {{ count($blockedItems) }} {{ count($blockedItems) === 1 ? __('weeklyplanning.text.blocked_item') : __('weeklyplanning.text.blocked_items') }}
            </p>

            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>{{ __('weeklyplanning.labels.employee') }}</th>
                            <th>{{ __('weeklyplanning.labels.task') }}</th>
                            <th>{{ __('weeklyplanning.labels.status') }}</th>
                            <th>{{ __('weeklyplanning.labels.reason') }}</th>
                            <th>{{ __('weeklyplanning.labels.support_needed') }}</th>
                            <th>{{ __('weeklyplanning.labels.week') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($blockedItems as $item)
                            <tr>
                                <td>{{ $item['employeeFirstname'] }} {{ $item['employeeLastname'] }}</td>
                                <td>
                                    @if(!empty($item['ticketId']) && !empty($item['ticketHeadline']))
                                        <a href="{{ BASE_URL }}/tickets/showTicket/{{ $item['ticketId'] }}" preload="mouseover">
                                            {{ $item['ticketHeadline'] }}
                                        </a>
                                    @else
                                        {{ $item['expectedOutcome'] ?? '—' }}
                                    @endif
                                </td>
                                <td>
                                    <span class="label label-{{ $item['status'] === 'blocked' ? 'warning' : 'danger' }}">
                                        {{ __('weeklyplanning.status.'.$item['status']) }}
                                    </span>
                                </td>
                                <td class="tw-text-sm">{{ $item['completionReason'] ?? '—' }}</td>
                                <td class="tw-text-sm">{{ $item['supportNeeded'] ?? '—' }}</td>
                                <td class="tw-text-sm">{{ $item['weekLabel'] }} / {{ $item['month'] }}</td>
                                <td>
                                    <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $item['weeklyPlanId'] }}" class="btn btn-default btn-xs">
                                        {{ __('weeklyplanning.buttons.view_plan') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</div>

@endsection
