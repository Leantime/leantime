@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-calendar-plus'">
    <h1>{{ __('weeklyplanning.headlines.new_plan') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <a href="{{ BASE_URL }}/weekly-planning/showTeam" class="btn btn-default btn-sm" style="margin-bottom:20px;">
            <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
        </a>

        <div class="row">
            <div class="col-md-6 col-lg-5">

                @if($employee)
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;
                            padding:14px 18px; border-radius:var(--box-radius);
                            background:rgba(74,158,255,.07); border:1px solid rgba(74,158,255,.2);">
                    <div style="width:40px; height:40px; border-radius:50%; background:var(--accent1); color:#fff;
                                display:flex; align-items:center; justify-content:center; font-weight:700; font-size:15px; flex-shrink:0;">
                        {{ strtoupper(substr($employee['firstname'] ?? '', 0, 1).substr($employee['lastname'] ?? '', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-size:11px; color:var(--grey); text-transform:uppercase; letter-spacing:.05em;">Creating plan for</div>
                        <div style="font-weight:600; font-size:15px;">{{ $employee['firstname'] }} {{ $employee['lastname'] }}</div>
                    </div>
                </div>
                @endif

                <div style="background:var(--secondary-background); border:1px solid var(--main-border-color);
                            border-radius:var(--box-radius); box-shadow:var(--min-shadow); padding:24px;">

                    <h4 style="margin:0 0 20px; font-size:15px; font-weight:600; display:flex; align-items:center; gap:8px;">
                        <i class="fa fa-calendar-week" style="color:var(--accent1);"></i>
                        Plan Details
                    </h4>

                    <form method="post" action="{{ BASE_URL }}/weekly-planning/newPlan">
                        <input type="hidden" name="employeeId" value="{{ $employeeId }}">

                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-size:12px; font-weight:700; color:var(--grey); text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:6px;">
                                {{ __('weeklyplanning.labels.week_start') }}
                            </label>
                            <input type="date" name="weekStart" class="form-control" value="{{ $weekStart }}" required>
                        </div>

                        <div class="form-group" style="margin-bottom:16px;">
                            <label style="font-size:12px; font-weight:700; color:var(--grey); text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:6px;">
                                {{ __('weeklyplanning.labels.week_end') }}
                            </label>
                            <input type="date" name="weekEnd" class="form-control" value="{{ $weekEnd }}" required>
                        </div>

                        <div class="form-group" style="margin-bottom:24px;">
                            <label style="font-size:12px; font-weight:700; color:var(--grey); text-transform:uppercase; letter-spacing:.05em; display:block; margin-bottom:6px;">
                                {{ __('weeklyplanning.labels.one_on_one_date') }}
                                <span style="font-weight:400; text-transform:none; font-size:11px;">({{ __('weeklyplanning.labels.optional') }})</span>
                            </label>
                            <input type="date" name="dateOfOneOnOne" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            <i class="fa fa-check"></i> {{ __('weeklyplanning.buttons.create_plan') }}
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection
