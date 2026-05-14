@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-users-gear'">
    <h1>{{ __('weeklyplanning.headlines.team_work') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Month selector --}}
        @if(count($months) > 0)
        <div class="tw-flex tw-items-center tw-gap-s tw-mb-l">
            <span class="tw-font-semibold tw-text-sm" style="color:var(--grey);">{{ __('weeklyplanning.labels.month') }}:</span>
            <div class="btn-group">
                @foreach($months as $month)
                <a href="{{ BASE_URL }}/weekly-planning/showTeam?month={{ urlencode($month) }}"
                    class="btn btn-sm {{ $selectedMonth === $month ? 'btn-primary' : 'btn-default' }}">
                    {{ $month }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Team Lead: create plan button --}}
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-m">
            <h4 class="widgettitle title-light tw-mb-0">{{ __('weeklyplanning.headlines.your_team') }}</h4>
            {{-- Top-level button is a dropdown so the TL picks which employee to plan for --}}
            <div class="btn-group">
                <button type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-plus"></i> {{ __('weeklyplanning.buttons.create_plan') }}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    @foreach($teamMembers as $member)
                    <li>
                        <a href="{{ BASE_URL }}/weekly-planning/newPlan?employeeId={{ $member['id'] }}">
                            {{ $member['firstname'] }} {{ $member['lastname'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if(count($teamMembers) === 0)
        <x-global::emptyState
            icon="fa-users"
            headline="{{ __('weeklyplanning.text.no_team_yet') }}"
            description="{{ __('weeklyplanning.text.no_team_hint') }}" />
        @else
        <div class="row">
            @foreach($teamMembers as $member)
            <div class="col-md-6 col-lg-4 tw-mb-m">
                <div class="tw-p-m tw-rounded tw-h-full"
                    style="background:var(--secondary-background); border:1px solid var(--main-border-color);">

                    {{-- Member header --}}
                    <div class="tw-flex tw-items-center tw-gap-s tw-mb-s">
                        <div class="tw-w-12 tw-h-12 tw-rounded-full tw-flex tw-items-center tw-justify-center tw-text-lg tw-font-bold tw-shrink-0"
                            style="background:var(--accent1); color:#fff;">
                            {{ strtoupper(substr($member['firstname'] ?? '', 0, 1).substr($member['lastname'] ?? '', 0, 1)) }}
                        </div>
                        <div class="tw-flex-1 tw-min-w-0">
                            <strong class="tw-block tw-truncate">{{ $member['firstname'] }} {{ $member['lastname'] }}</strong>
                            @if(!empty($member['jobTitle']))
                            <small style="color:var(--grey);">{{ $member['jobTitle'] }}</small>
                            @endif
                        </div>
                    </div>

                    {{-- Weekly plans for this member --}}
                    @if(count($member['plans']) === 0)
                    <p class="tw-text-sm tw-text-center tw-py-s" style="color:var(--grey);">
                        {{ __('weeklyplanning.text.no_plan_for_member') }}
                    </p>
                    @else
                    @foreach($member['plans'] as $plan)
                    @php
                    $items = $plan['_items'] ?? [];
                    $done = collect($items)->where('status', 'completed')->count();
                    $blocked = collect($items)->where('status', 'blocked')->count();
                    $missed = collect($items)->where('status', 'not_completed')->count();
                    $total = count($items);
                    @endphp
                    <div class="tw-mb-s tw-p-s tw-rounded"
                        style="background:var(--layered-background); border:1px solid var(--main-border-color);">
                        <div class="tw-flex tw-justify-between tw-items-start tw-mb-xs">
                            <div>
                                <strong class="tw-text-sm">{{ $plan['weekLabel'] }}</strong>
                                <small class="tw-block" style="color:var(--grey);">
                                    {{ \Carbon\Carbon::parse($plan['weekStart'])->format('d M') }}
                                    – {{ \Carbon\Carbon::parse($plan['weekEnd'])->format('d M') }}
                                </small>
                            </div>
                            <span class="label label-{{ $plan['status'] === 'reviewed' ? 'success' : ($plan['status'] === 'active' ? 'primary' : 'default') }}">
                                {{ __('weeklyplanning.plan_status.'.$plan['status']) }}
                            </span>
                        </div>

                        @if($total > 0)
                        <div class="tw-flex tw-gap-s tw-text-xs tw-mb-xs">
                            <span class="tw-text-green-600"><i class="fa fa-check"></i> {{ $done }}</span>
                            @if($blocked > 0)
                            <span class="tw-text-orange-500"><i class="fa fa-ban"></i> {{ $blocked }}</span>
                            @endif
                            @if($missed > 0)
                            <span class="tw-text-red-500"><i class="fa fa-times"></i> {{ $missed }}</span>
                            @endif
                            <span style="color:var(--grey);">/ {{ $total }} {{ __('weeklyplanning.labels.tasks') }}</span>
                        </div>
                        @endif

                        <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $plan['id'] }}"
                            class="btn btn-xs btn-default">
                            {{ __('weeklyplanning.buttons.view_plan') }}
                        </a>
                    </div>
                    @endforeach
                    @endif

                    {{-- Always allow creating a new plan for this member (e.g. next week) --}}
                    <a href="{{ BASE_URL }}/weekly-planning/newPlan?employeeId={{ $member['id'] }}"
                        class="btn btn-sm btn-default tw-w-full tw-text-center tw-mt-xs">
                        <i class="fa fa-plus"></i> {{ __('weeklyplanning.buttons.create_plan') }}
                    </a>

                </div>
            </div>
            @endforeach
        </div>
        @endif

    </div>
</div>

@endsection