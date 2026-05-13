@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-calendar-plus'">
    <h1>{{ __('weeklyplanning.headlines.new_plan') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <a href="{{ BASE_URL }}/weeklyplanning/showTeam" class="btn btn-default btn-sm tw-mb-m">
            <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
        </a>

        <div class="row">
            <div class="col-md-6">
                <div class="tw-p-m tw-rounded" style="background:var(--secondary-background); border:1px solid var(--main-border-color);">

                    @if($employee)
                        <p class="tw-mb-m">
                            {{ __('weeklyplanning.text.creating_plan_for') }}
                            <strong>{{ $employee['firstname'] }} {{ $employee['lastname'] }}</strong>
                        </p>
                    @endif

                    <form method="post" action="{{ BASE_URL }}/weeklyplanning/newPlan">
                        <input type="hidden" name="employeeId" value="{{ $employeeId }}">

                        <div class="form-group tw-mb-m">
                            <label class="tw-font-semibold">{{ __('weeklyplanning.labels.week_start') }}</label>
                            <input type="date"
                                   name="weekStart"
                                   class="form-control"
                                   value="{{ $weekStart }}"
                                   required>
                        </div>

                        <div class="form-group tw-mb-m">
                            <label class="tw-font-semibold">{{ __('weeklyplanning.labels.week_end') }}</label>
                            <input type="date"
                                   name="weekEnd"
                                   class="form-control"
                                   value="{{ $weekEnd }}"
                                   required>
                        </div>

                        <div class="form-group tw-mb-m">
                            <label class="tw-font-semibold">
                                {{ __('weeklyplanning.labels.one_on_one_date') }}
                                <small style="color:var(--grey);">({{ __('weeklyplanning.labels.optional') }})</small>
                            </label>
                            <input type="date"
                                   name="dateOfOneOnOne"
                                   class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-check"></i> {{ __('weeklyplanning.buttons.create_plan') }}
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

@endsection
