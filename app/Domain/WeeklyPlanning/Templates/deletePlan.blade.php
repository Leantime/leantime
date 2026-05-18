@extends($layout)

@section('content')

<x-global::pageheader :icon="'fa fa-trash'">
    <h1>Delete Weekly Plan</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-6 col-md-offset-3 col-sm-12">

                <div style="background:var(--secondary-background); border:1px solid var(--main-border-color);
                            border-radius:var(--box-radius); box-shadow:var(--min-shadow); padding:28px 32px;">

                    {{-- Warning icon --}}
                    <div style="text-align:center; margin-bottom:20px;">
                        <div style="width:56px; height:56px; border-radius:50%;
                                    background:rgba(239,68,68,.1); color:#ef4444;
                                    display:inline-flex; align-items:center; justify-content:center;
                                    font-size:22px; margin-bottom:12px;">
                            <i class="fa fa-triangle-exclamation"></i>
                        </div>
                        <h3 style="margin:0; font-size:17px; font-weight:700;">Are you sure?</h3>
                        <p style="margin:8px 0 0; font-size:13px; color:var(--grey);">
                            This will permanently delete the weekly plan and all its tasks, feedback, and commitments.
                        </p>
                    </div>

                    {{-- Plan info --}}
                    <div style="background:var(--layered-background); border:1px solid var(--main-border-color);
                                border-radius:var(--box-radius-small); padding:14px 16px; margin-bottom:24px;">
                        <div style="display:flex; flex-direction:column; gap:6px;">
                            <div style="display:flex; gap:8px; font-size:13px;">
                                <span style="color:var(--grey); width:90px; flex-shrink:0;">Employee</span>
                                <strong>{{ $plan['employeeFirstname'] }} {{ $plan['employeeLastname'] }}</strong>
                            </div>
                            <div style="display:flex; gap:8px; font-size:13px;">
                                <span style="color:var(--grey); width:90px; flex-shrink:0;">Week</span>
                                <strong>{{ $plan['weekLabel'] }}, {{ $plan['month'] }}</strong>
                            </div>
                            <div style="display:flex; gap:8px; font-size:13px;">
                                <span style="color:var(--grey); width:90px; flex-shrink:0;">Dates</span>
                                <strong>
                                    {{ \Carbon\Carbon::parse($plan['weekStart'])->format('d M') }}
                                    – {{ \Carbon\Carbon::parse($plan['weekEnd'])->format('d M Y') }}
                                </strong>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <form method="post" action="{{ BASE_URL }}/weekly-planning/deletePlan">
                        <input type="hidden" name="id" value="{{ $plan['id'] }}">
                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="btn btn-danger" style="flex:1;">
                                <i class="fa fa-trash"></i> Yes, Delete Plan
                            </button>
                            <a href="{{ BASE_URL }}/weekly-planning/showTeam"
                                class="btn btn-default" style="flex:1; text-align:center;">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection
