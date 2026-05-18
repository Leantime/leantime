@extends($layout)

@section('content')

<style>
.wp-week-card {
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
    overflow: hidden;
    margin-bottom: 20px;
    border: 2px solid var(--main-border-color);
    background: var(--secondary-background);
    transition: border-color .2s;
}
.wp-week-card.wp-current-week { border-color: var(--accent1); }
.wp-week-header {
    display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid var(--main-border-color);
}
.wp-week-header.wp-current-week-header {
    background: linear-gradient(90deg, rgba(74,158,255,.08) 0%, transparent 100%);
}
.wp-week-left { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.wp-week-num {
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    border-radius: 20px; background: var(--accent1); color: #fff; white-space: nowrap;
}
.wp-week-dates { font-weight: 600; font-size: 14px; }
.wp-week-tl { font-size: 12px; color: var(--grey); }
.wp-status-pill {
    font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; white-space: nowrap;
}
.wp-status-active   { background: rgba(74,158,255,.15); color: #4a9eff; }
.wp-status-reviewed { background: rgba(34,197,94,.15); color: #22c55e; }
.wp-status-draft    { background: rgba(150,150,150,.12); color: var(--grey); }
.wp-status-none     { background: rgba(150,150,150,.08); color: var(--grey); }
.wp-week-body { padding: 16px 20px; }
.wp-task-table { width: 100%; border-collapse: collapse; }
.wp-task-table th {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    color: var(--grey); padding: 0 0 8px; border-bottom: 1px solid var(--main-border-color);
}
.wp-task-table th:last-child { width: 140px; text-align: right; }
.wp-task-table td { padding: 9px 0; border-bottom: 1px solid var(--main-border-color); vertical-align: middle; }
.wp-task-table tr:last-child td { border-bottom: none; }
.wp-task-name { font-size: 13px; }
.wp-task-name a { color: var(--primary-font-color); text-decoration: none; }
.wp-task-name a:hover { color: var(--accent1); }
.wp-task-reason {
    margin-top: 5px; padding: 6px 10px; border-radius: 4px; font-size: 11px;
    background: var(--layered-background); border-left: 3px solid var(--accent2);
}
.wp-status-badge-table { font-size: 11px; padding: 3px 10px; border-radius: 20px; font-weight: 600; float: right; }
.wp-badge-completed   { background: rgba(34,197,94,.15); color: #22c55e; }
.wp-badge-in_progress { background: rgba(74,158,255,.15); color: #4a9eff; }
.wp-badge-blocked     { background: rgba(249,115,22,.15); color: #f97316; }
.wp-badge-not_completed { background: rgba(239,68,68,.15); color: #ef4444; }
.wp-badge-not_started { background: rgba(150,150,150,.1); color: var(--grey); }
.wp-no-tasks { color: var(--grey); font-size: 13px; font-style: italic; padding: 8px 0; }
.wp-month-nav {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px; padding: 0;
}
.wp-month-nav-center { font-size: 18px; font-weight: 700; }
.wp-current-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; padding: 2px 8px; border-radius: 20px;
    background: rgba(34,197,94,.15); color: #22c55e; font-weight: 600;
}
</style>

<x-global::pageheader :icon="'fa fa-calendar-week'">
    <h1>{{ __('weeklyplanning.headlines.my_weekly_plan') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Month navigation --}}
        <div class="wp-month-nav">
            <a href="{{ BASE_URL }}/weekly-planning/showMy?year={{ $prevMonth->year }}&month={{ $prevMonth->month }}"
                class="btn btn-default btn-sm">
                <i class="fa fa-chevron-left"></i> {{ $prevMonth->format('M Y') }}
            </a>
            <div class="wp-month-nav-center">
                <i class="fa fa-calendar" style="opacity:.4; margin-right:8px;"></i>{{ $monthDate->format('F Y') }}
            </div>
            <a href="{{ BASE_URL }}/weekly-planning/showMy?year={{ $nextMonth->year }}&month={{ $nextMonth->month }}"
                class="btn btn-default btn-sm">
                {{ $nextMonth->format('M Y') }} <i class="fa fa-chevron-right"></i>
            </a>
        </div>

        @if(empty($weekSlots))
        <div style="text-align:center; padding:60px 20px; color:var(--grey);">
            <i class="fa fa-calendar-week" style="font-size:48px; opacity:.2; display:block; margin-bottom:12px;"></i>
            <p style="font-size:15px; font-weight:600; margin-bottom:6px;">{{ __('weeklyplanning.text.no_plan_yet') }}</p>
            <p style="font-size:13px;">{{ __('weeklyplanning.text.no_plan_hint') }}</p>
        </div>
        @else

        @foreach($weekSlots as $index => $slot)
        @php
            $plan      = $slot['plan'];
            $items     = $slot['items'];
            $weekNum   = $index + 1;
            $isCurrent = $slot['isCurrent'] ?? false;
            $start     = \Carbon\Carbon::parse($slot['weekStart'])->format('d M');
            $end       = \Carbon\Carbon::parse($slot['weekEnd'])->format('d M Y');
            $statusClass = $plan ? match($plan['status'] ?? 'draft') {
                'reviewed' => 'wp-status-reviewed',
                'active'   => 'wp-status-active',
                default    => 'wp-status-draft',
            } : 'wp-status-none';
        @endphp

        <div class="wp-week-card {{ $isCurrent ? 'wp-current-week' : '' }}">

            <div class="wp-week-header {{ $isCurrent ? 'wp-current-week-header' : '' }}">
                <div class="wp-week-left">
                    <span class="wp-week-num" style="{{ $isCurrent ? '' : 'background:var(--secondary-background); color:var(--grey); border:1px solid var(--main-border-color);' }}">
                        {{ __('weeklyplanning.labels.week') }} {{ $weekNum }}
                    </span>
                    <span class="wp-week-dates">{{ $start }} – {{ $end }}</span>
                    @if($isCurrent)
                    <span class="wp-current-badge"><i class="fa fa-circle" style="font-size:7px;"></i> Current Week</span>
                    @endif
                    @if($plan && !empty($plan['teamLeadFirstname']))
                    <span class="wp-week-tl"><i class="fa fa-user-tie"></i> {{ $plan['teamLeadFirstname'] }} {{ $plan['teamLeadLastname'] }}</span>
                    @endif
                    @if($plan)
                    <span class="wp-status-pill {{ $statusClass }}">
                        {{ __('weeklyplanning.plan_status.'.($plan['status'] ?? 'draft')) }}
                    </span>
                    @else
                    <span class="wp-status-pill wp-status-none">No plan</span>
                    @endif
                </div>
                @if($plan)
                <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $plan['id'] }}"
                    class="btn btn-default btn-xs" preload="mouseover">
                    <i class="fa fa-expand"></i> {{ __('weeklyplanning.buttons.view_full_plan') }}
                </a>
                @endif
            </div>

            <div class="wp-week-body">
                @if(empty($items))
                <p class="wp-no-tasks">{{ __('weeklyplanning.text.no_tasks_in_plan') }}</p>
                @else
                <table class="wp-task-table">
                    <thead>
                        <tr>
                            <th>{{ __('weeklyplanning.labels.task') }}</th>
                            <th>{{ __('weeklyplanning.labels.status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        @php
                            $statusKey = $item['status'] ?? 'not_started';
                            $badgeClass = match($statusKey) {
                                'completed'     => 'wp-badge-completed',
                                'in_progress'   => 'wp-badge-in_progress',
                                'blocked'       => 'wp-badge-blocked',
                                'not_completed' => 'wp-badge-not_completed',
                                default         => 'wp-badge-not_started',
                            };
                        @endphp
                        <tr id="plan-item-{{ $item['id'] }}">
                            <td>
                                <div class="wp-task-name">
                                    @if(!empty($item['ticketId']))
                                    <a href="#/tickets/showTicket/{{ $item['ticketId'] }}" preload="mouseover">
                                        <i class="fa fa-ticket" style="opacity:.4; margin-right:4px;"></i>
                                        {{ $item['ticketHeadline'] ?? __('weeklyplanning.text.task_deleted') }}
                                    </a>
                                    @else
                                    <span>{{ $item['expectedOutcome'] ?? '—' }}</span>
                                    @endif
                                </div>
                                @if(!empty($item['completionReason']))
                                <div class="wp-task-reason">
                                    <strong>{{ __('weeklyplanning.labels.reason') }}:</strong> {{ $item['completionReason'] }}
                                </div>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                @if($isCurrent && $plan)
                                <div hx-get="{{ BASE_URL }}/hx/weekly-planning/statusUpdate/get?itemId={{ $item['id'] }}"
                                    hx-trigger="load" hx-swap="innerHTML">
                                    <span class="wp-status-badge-table {{ $badgeClass }}">
                                        {{ __('weeklyplanning.status.'.$statusKey) }}
                                    </span>
                                </div>
                                @else
                                <span class="wp-status-badge-table {{ $badgeClass }}">
                                    {{ __('weeklyplanning.status.'.$statusKey) }}
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

        <div style="text-align:center; margin-top:8px;">
            <a href="{{ BASE_URL }}/weekly-planning/showMyHistory" class="btn btn-default">
                <i class="fa fa-history"></i> {{ __('weeklyplanning.buttons.view_history') }}
            </a>
        </div>

    </div>
</div>

@endsection
