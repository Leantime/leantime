@extends($layout)

@section('content')

<style>
.wp-blocker-card {
    background: var(--secondary-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
    overflow: hidden;
}
.wp-blocker-table { width: 100%; border-collapse: collapse; }
.wp-blocker-table th {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    color: var(--grey); padding: 12px 14px;
    border-bottom: 2px solid var(--main-border-color);
    background: var(--layered-background);
    white-space: nowrap;
}
.wp-blocker-table td { padding: 12px 14px; border-bottom: 1px solid var(--main-border-color); vertical-align: middle; font-size: 13px; }
.wp-blocker-table tr:last-child td { border-bottom: none; }
.wp-blocker-table tr:hover td { background: var(--layered-background); }
.wp-blocker-chip {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600; white-space: nowrap;
}
.wp-chip-blocked   { background: rgba(249,115,22,.15); color: #f97316; }
.wp-chip-not_done  { background: rgba(239,68,68,.15); color: #ef4444; }
.wp-employee-cell { display: flex; align-items: center; gap: 8px; }
.wp-mini-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--accent1); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
.wp-task-link { color: var(--primary-font-color); text-decoration: none; }
.wp-task-link:hover { color: var(--accent1); }
.wp-reason-cell { font-size: 12px; color: var(--grey); max-width: 200px; }
.wp-stats-strip {
    display: flex; gap: 20px; flex-wrap: wrap;
    margin-bottom: 20px; padding: 14px 18px;
    background: var(--secondary-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
}
.wp-stat-box { text-align: center; }
.wp-stat-num { font-size: 24px; font-weight: 700; line-height: 1; }
.wp-stat-lbl { font-size: 11px; color: var(--grey); margin-top: 3px; text-transform: uppercase; letter-spacing: .04em; }
.wp-stat-orange { color: #f97316; }
.wp-stat-red    { color: #ef4444; }
</style>

<x-global::pageheader :icon="'fa fa-triangle-exclamation'">
    <h1>{{ __('weeklyplanning.headlines.blockers') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <a href="{{ BASE_URL }}/weekly-planning/showTeam" class="btn btn-default btn-sm" style="margin-bottom:20px;">
            <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
        </a>

        @if(count($blockedItems) === 0)
        <div style="text-align:center; padding:60px 20px; color:var(--grey);">
            <i class="fa fa-check-circle" style="font-size:52px; opacity:.2; display:block; margin-bottom:14px; color:#22c55e;"></i>
            <p style="font-size:16px; font-weight:600; margin-bottom:6px;">{{ __('weeklyplanning.text.no_blockers') }}</p>
            <p style="font-size:13px;">{{ __('weeklyplanning.text.no_blockers_hint') }}</p>
        </div>
        @else

        @php
            $countBlocked  = collect($blockedItems)->where('status', 'blocked')->count();
            $countNotDone  = collect($blockedItems)->where('status', 'not_completed')->count();
        @endphp

        {{-- Stats strip --}}
        <div class="wp-stats-strip">
            <div class="wp-stat-box">
                <div class="wp-stat-num" style="color:var(--primary-font-color);">{{ count($blockedItems) }}</div>
                <div class="wp-stat-lbl">Total Issues</div>
            </div>
            @if($countBlocked)
            <div class="wp-stat-box">
                <div class="wp-stat-num wp-stat-orange">{{ $countBlocked }}</div>
                <div class="wp-stat-lbl">Blocked</div>
            </div>
            @endif
            @if($countNotDone)
            <div class="wp-stat-box">
                <div class="wp-stat-num wp-stat-red">{{ $countNotDone }}</div>
                <div class="wp-stat-lbl">Not Completed</div>
            </div>
            @endif
        </div>

        <div class="wp-blocker-card">
            <table class="wp-blocker-table">
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
                        <td>
                            <div class="wp-employee-cell">
                                <div class="wp-mini-avatar">
                                    {{ strtoupper(substr($item['employeeFirstname'] ?? '', 0, 1).substr($item['employeeLastname'] ?? '', 0, 1)) }}
                                </div>
                                <span>{{ $item['employeeFirstname'] }} {{ $item['employeeLastname'] }}</span>
                            </div>
                        </td>
                        <td>
                            @if(!empty($item['ticketId']) && !empty($item['ticketHeadline']))
                            <a href="{{ BASE_URL }}/tickets/showTicket/{{ $item['ticketId'] }}" class="wp-task-link" preload="mouseover">
                                <i class="fa fa-ticket" style="opacity:.4; margin-right:4px;"></i>
                                {{ $item['ticketHeadline'] }}
                            </a>
                            @else
                            {{ $item['expectedOutcome'] ?? '—' }}
                            @endif
                        </td>
                        <td>
                            @php $chipClass = $item['status'] === 'blocked' ? 'wp-chip-blocked' : 'wp-chip-not_done'; @endphp
                            <span class="wp-blocker-chip {{ $chipClass }}">
                                {{ __('weeklyplanning.status.'.$item['status']) }}
                            </span>
                        </td>
                        <td class="wp-reason-cell">{{ $item['completionReason'] ?? '—' }}</td>
                        <td class="wp-reason-cell">{{ $item['supportNeeded'] ?? '—' }}</td>
                        <td style="font-size:12px; color:var(--grey); white-space:nowrap;">
                            {{ $item['weekLabel'] }}
                            <span style="opacity:.5;"> / {{ $item['month'] }}</span>
                        </td>
                        <td>
                            <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $item['weeklyPlanId'] }}"
                                class="btn btn-xs btn-default" preload="mouseover">
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
