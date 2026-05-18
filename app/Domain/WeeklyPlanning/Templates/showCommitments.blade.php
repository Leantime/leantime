@extends($layout)

@section('content')

<style>
.wp-commit-card {
    background: var(--secondary-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
    overflow: hidden;
}
.wp-commit-table { width: 100%; border-collapse: collapse; }
.wp-commit-table th {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
    color: var(--grey); padding: 12px 14px;
    border-bottom: 2px solid var(--main-border-color);
    background: var(--layered-background);
    white-space: nowrap;
}
.wp-commit-table td {
    padding: 12px 14px; border-bottom: 1px solid var(--main-border-color);
    vertical-align: middle; font-size: 13px;
}
.wp-commit-table tr:last-child td { border-bottom: none; }
.wp-commit-table tr:hover td { background: var(--layered-background); }
.wp-commit-task { font-weight: 500; }
.wp-commit-done-row .wp-commit-task { text-decoration: line-through; color: var(--grey); }
.wp-commit-chip {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600;
}
.wp-chip-done    { background: rgba(34,197,94,.15); color: #22c55e; }
.wp-chip-pending { background: rgba(150,150,150,.1); color: var(--grey); }
.wp-filter-tabs { display: flex; gap: 6px; margin-bottom: 20px; }
.wp-filter-tab {
    padding: 5px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;
    border: 1px solid var(--main-border-color);
    text-decoration: none; color: var(--primary-font-color);
    transition: all .15s;
}
.wp-filter-tab.active { background: var(--accent1); color: #fff; border-color: var(--accent1); }
.wp-filter-tab:hover:not(.active) { border-color: var(--accent1); color: var(--accent1); }
.wp-employee-cell { display: flex; align-items: center; gap: 8px; }
.wp-mini-avatar {
    width: 28px; height: 28px; border-radius: 50%;
    background: var(--accent1); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; flex-shrink: 0;
}
</style>

<x-global::pageheader :icon="'fa fa-handshake'">
    <h1>{{ __('weeklyplanning.headlines.commitments') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
            <a href="{{ BASE_URL }}/weekly-planning/showTeam" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
            </a>
            <div class="wp-filter-tabs">
                <a href="{{ BASE_URL }}/weekly-planning/showCommitments"
                    class="wp-filter-tab {{ $openOnly ? 'active' : '' }}">
                    <i class="fa fa-clock" style="margin-right:4px;"></i>{{ __('weeklyplanning.buttons.open_only') }}
                </a>
                <a href="{{ BASE_URL }}/weekly-planning/showCommitments?showAll=1"
                    class="wp-filter-tab {{ ! $openOnly ? 'active' : '' }}">
                    <i class="fa fa-list" style="margin-right:4px;"></i>{{ __('weeklyplanning.buttons.show_all') }}
                </a>
            </div>
        </div>

        @if(count($commitments) === 0)
        <div style="text-align:center; padding:60px 20px; color:var(--grey);">
            <i class="fa fa-handshake" style="font-size:52px; opacity:.2; display:block; margin-bottom:14px;"></i>
            <p style="font-size:16px; font-weight:600; margin-bottom:6px;">{{ __('weeklyplanning.text.no_commitments_yet') }}</p>
            <p style="font-size:13px;">{{ __('weeklyplanning.text.no_commitments_hint') }}</p>
        </div>
        @else
        <div class="wp-commit-card">
            <table class="wp-commit-table">
                <thead>
                    <tr>
                        <th>{{ __('weeklyplanning.labels.task') }}</th>
                        <th>{{ __('weeklyplanning.labels.employee') }}</th>
                        <th>{{ __('weeklyplanning.labels.owner') }}</th>
                        <th>{{ __('weeklyplanning.labels.deadline') }}</th>
                        <th>{{ __('weeklyplanning.labels.status') }}</th>
                        <th>{{ __('weeklyplanning.labels.week') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commitments as $c)
                    @php $isDone = ($c['status'] ?? '') === 'done'; @endphp
                    <tr class="{{ $isDone ? 'wp-commit-done-row' : '' }}">
                        <td>
                            <div class="wp-commit-task">{{ $c['task'] }}</div>
                        </td>
                        <td>
                            <div class="wp-employee-cell">
                                <div class="wp-mini-avatar">
                                    {{ strtoupper(substr($c['employeeFirstname'] ?? '', 0, 1).substr($c['employeeLastname'] ?? '', 0, 1)) }}
                                </div>
                                {{ $c['employeeFirstname'] }} {{ $c['employeeLastname'] }}
                            </div>
                        </td>
                        <td style="font-size:12px;">
                            {{ trim(($c['ownerFirstname'] ?? '') . ' ' . ($c['ownerLastname'] ?? '')) ?: '—' }}
                        </td>
                        <td style="font-size:12px; white-space:nowrap;">
                            @if(!empty($c['deadline']))
                            <i class="fa fa-clock" style="opacity:.4; margin-right:3px;"></i>
                            {{ \Carbon\Carbon::parse($c['deadline'])->format('d M Y') }}
                            @else
                            —
                            @endif
                        </td>
                        <td>
                            <span class="wp-commit-chip {{ $isDone ? 'wp-chip-done' : 'wp-chip-pending' }}">
                                {{ __('weeklyplanning.commitment_status.'.$c['status']) }}
                            </span>
                        </td>
                        <td style="font-size:12px; color:var(--grey); white-space:nowrap;">
                            {{ $c['weekLabel'] }}
                            <span style="opacity:.5;"> / {{ $c['month'] }}</span>
                        </td>
                        <td>
                            <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $c['weeklyPlanId'] }}"
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
