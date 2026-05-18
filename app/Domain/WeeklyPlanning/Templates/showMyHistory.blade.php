@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-history'">
    <h1>{{ __('weeklyplanning.headlines.my_weekly_history') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <a href="{{ BASE_URL }}/weekly-planning/showMy" class="btn btn-default btn-sm" style="margin-bottom:20px;">
            <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_my_plan') }}
        </a>

        @if($totalPlans === 0)
        <div style="text-align:center; padding:60px 20px; color:var(--grey);">
            <i class="fa fa-history" style="font-size:48px; opacity:.2; display:block; margin-bottom:12px;"></i>
            <p style="font-size:15px; font-weight:600; margin-bottom:6px;">{{ __('weeklyplanning.text.no_history_yet') }}</p>
            <p style="font-size:13px;">{{ __('weeklyplanning.text.no_history_hint') }}</p>
        </div>
        @else

        @foreach($plansByMonth as $month => $monthPlans)
        <div style="margin-bottom:28px;">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                <h4 style="margin:0; font-size:15px; font-weight:700;">
                    <i class="fa fa-calendar" style="color:var(--accent1); margin-right:6px; opacity:.7;"></i>
                    {{ $month }}
                </h4>
                <span style="font-size:12px; color:var(--grey);">
                    {{ count($monthPlans) }} {{ count($monthPlans) === 1 ? __('weeklyplanning.text.plan') : __('weeklyplanning.text.plans') }}
                </span>
            </div>

            <div class="row">
                @foreach($monthPlans as $plan)
                @php
                    $statusClass = match($plan['status'] ?? 'draft') {
                        'reviewed' => 'background:rgba(34,197,94,.15); color:#22c55e;',
                        'active'   => 'background:rgba(74,158,255,.15); color:#4a9eff;',
                        default    => 'background:rgba(150,150,150,.1); color:var(--grey);',
                    };
                @endphp
                <div class="col-md-4 col-sm-6" style="margin-bottom:16px;">
                    <div style="background:var(--secondary-background); border:1px solid var(--main-border-color);
                                border-radius:var(--box-radius); box-shadow:var(--min-shadow);
                                padding:16px 18px; display:flex; flex-direction:column; gap:8px;">
                        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:8px;">
                            <strong style="font-size:14px;">{{ $plan['weekLabel'] ?? '' }}</strong>
                            <span style="display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; flex-shrink:0; {{ $statusClass }}">
                                {{ __('weeklyplanning.plan_status.'.$plan['status']) }}
                            </span>
                        </div>
                        <div style="font-size:12px; color:var(--grey);">
                            <i class="fa fa-calendar-range" style="margin-right:4px; opacity:.5;"></i>
                            {{ \Carbon\Carbon::parse($plan['weekStart'])->format('d M') }}
                            – {{ \Carbon\Carbon::parse($plan['weekEnd'])->format('d M Y') }}
                        </div>
                        @if(!empty($plan['teamLeadFirstname']))
                        <div style="font-size:12px; color:var(--grey);">
                            <i class="fa fa-user-tie" style="margin-right:4px; opacity:.5;"></i>
                            {{ $plan['teamLeadFirstname'] }} {{ $plan['teamLeadLastname'] }}
                        </div>
                        @endif
                        <div style="margin-top:4px;">
                            <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $plan['id'] }}"
                                class="btn btn-default btn-xs" style="width:100%; text-align:center;" preload="mouseover">
                                <i class="fa fa-expand" style="margin-right:4px;"></i>{{ __('weeklyplanning.buttons.view_plan') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        @endif

    </div>
</div>

@endsection
