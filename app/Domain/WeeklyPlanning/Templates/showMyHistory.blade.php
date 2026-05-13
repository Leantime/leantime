@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-history'">
    <h1>{{ __('weeklyplanning.headlines.my_weekly_history') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <a href="{{ BASE_URL }}/weeklyplanning/showMy" class="btn btn-default btn-sm tw-mb-m">
            <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_my_plan') }}
        </a>

        @if($totalPlans === 0)
            <x-global::emptyState
                icon="fa-history"
                headline="{{ __('weeklyplanning.text.no_history_yet') }}"
                description="{{ __('weeklyplanning.text.no_history_hint') }}"
            />
        @else
            @foreach($plansByMonth as $month => $monthPlans)
                <h4 class="widgettitle title-light tw-mt-l">
                    <i class="fa fa-calendar"></i> {{ $month }}
                    <small style="color:var(--grey);">({{ count($monthPlans) }} {{ count($monthPlans) === 1 ? __('weeklyplanning.text.plan') : __('weeklyplanning.text.plans') }})</small>
                </h4>

                <div class="row">
                    @foreach($monthPlans as $plan)
                        <div class="col-md-4 tw-mb-m">
                            <div class="tw-p-m tw-rounded" style="background:var(--secondary-background); border:1px solid var(--main-border-color);">
                                <h5 class="tw-mb-xs">
                                    {{ $plan['weekLabel'] ?? '' }}
                                </h5>
                                <p class="tw-text-sm tw-mb-xs" style="color:var(--grey);">
                                    {{ \Carbon\Carbon::parse($plan['weekStart'])->format('d M') }}
                                    – {{ \Carbon\Carbon::parse($plan['weekEnd'])->format('d M Y') }}
                                </p>
                                @if(!empty($plan['teamLeadFirstname']))
                                    <p class="tw-text-xs tw-mb-xs" style="color:var(--grey);">
                                        <i class="fa fa-user"></i> {{ $plan['teamLeadFirstname'] }} {{ $plan['teamLeadLastname'] }}
                                    </p>
                                @endif
                                <p class="tw-text-xs tw-mb-s">
                                    <span class="label label-{{ $plan['status'] === 'reviewed' ? 'success' : ($plan['status'] === 'active' ? 'primary' : 'default') }}">
                                        {{ __('weeklyplanning.plan_status.'.$plan['status']) }}
                                    </span>
                                </p>
                                <a href="{{ BASE_URL }}/weeklyplanning/showPlan/{{ $plan['id'] }}" class="btn btn-default btn-xs">
                                    {{ __('weeklyplanning.buttons.view_plan') }}
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif

    </div>
</div>

@endsection
