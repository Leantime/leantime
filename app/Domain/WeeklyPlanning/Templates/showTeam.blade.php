@extends($layout)

@section('content')

<style>
.wp-team-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px; }
.wp-member-card {
    background: var(--secondary-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
    display: flex; flex-direction: column;
    overflow: hidden;
}
.wp-member-header {
    padding: 16px 20px 12px;
    border-bottom: 1px solid var(--main-border-color);
    display: flex; align-items: center; gap: 12px;
}
.wp-avatar {
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--accent1); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px; font-weight: 700; flex-shrink: 0;
}
.wp-member-name { font-weight: 600; font-size: 15px; line-height: 1.3; }
.wp-member-role { font-size: 12px; color: var(--grey); margin-top: 2px; }
.wp-plans-body { padding: 12px 16px; flex: 1; display: flex; flex-direction: column; gap: 8px; }
.wp-plan-row {
    background: var(--layered-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius-small);
    padding: 10px 14px;
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
}
.wp-plan-meta { flex: 1; min-width: 0; }
.wp-plan-week { font-weight: 600; font-size: 13px; }
.wp-plan-dates { font-size: 11px; color: var(--grey); margin-top: 2px; }
.wp-plan-stats { display: flex; gap: 8px; margin-top: 5px; flex-wrap: wrap; }
.wp-stat { font-size: 11px; display: flex; align-items: center; gap: 3px; }
.wp-stat-done { color: #22c55e; }
.wp-stat-blocked { color: #f97316; }
.wp-stat-missed { color: #ef4444; }
.wp-stat-total { color: var(--grey); }
.wp-status-badge {
    font-size: 11px; padding: 2px 8px; border-radius: 20px; white-space: nowrap;
    font-weight: 600; flex-shrink: 0;
}
.wp-status-active { background: rgba(74,158,255,.15); color: #4a9eff; }
.wp-status-reviewed { background: rgba(34,197,94,.15); color: #22c55e; }
.wp-status-draft { background: rgba(150,150,150,.12); color: var(--grey); }
.wp-view-btn {
    font-size: 11px; padding: 3px 10px;
    border-radius: var(--element-radius);
    border: 1px solid var(--main-border-color);
    background: var(--secondary-background);
    color: var(--primary-font-color);
    text-decoration: none; white-space: nowrap; flex-shrink: 0;
    transition: background .15s;
}
.wp-view-btn:hover { background: var(--accent1); color: #fff; border-color: var(--accent1); }
.wp-card-footer {
    padding: 10px 16px;
    border-top: 1px solid var(--main-border-color);
}
.wp-add-plan-btn {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 7px;
    border-radius: var(--element-radius);
    border: 1px dashed var(--main-border-color);
    background: transparent; color: var(--grey);
    font-size: 13px; text-decoration: none;
    transition: all .15s;
}
.wp-add-plan-btn:hover { border-color: var(--accent1); color: var(--accent1); background: rgba(74,158,255,.05); }
.wp-empty-plans { padding: 16px; text-align: center; color: var(--grey); font-size: 13px; }
.wp-month-pills { display: flex; gap: 6px; flex-wrap: wrap; }
.wp-month-pill {
    padding: 4px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
    border: 1px solid var(--main-border-color);
    background: var(--secondary-background); color: var(--primary-font-color);
    text-decoration: none; transition: all .15s;
}
.wp-month-pill.active { background: var(--accent1); color: #fff; border-color: var(--accent1); }
.wp-month-pill:hover:not(.active) { border-color: var(--accent1); color: var(--accent1); }
</style>

<x-global::pageheader :icon="'fa fa-users-gear'">
    <h1>{{ __('weeklyplanning.headlines.team_work') }}</h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        {{-- Month pills --}}
        @if(count($months) > 0)
        <div class="tw-flex tw-items-center tw-gap-m tw-mb-l" style="flex-wrap:wrap;">
            <span style="font-size:12px; font-weight:600; color:var(--grey); text-transform:uppercase; letter-spacing:.05em;">
                <i class="fa fa-calendar-alt" style="margin-right:4px;"></i>{{ __('weeklyplanning.labels.month') }}
            </span>
            <div class="wp-month-pills">
                @foreach($months as $month)
                <a href="{{ BASE_URL }}/weekly-planning/showTeam?month={{ urlencode($month) }}"
                    class="wp-month-pill {{ $selectedMonth === $month ? 'active' : '' }}">
                    {{ $month }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Section header --}}
        <div class="tw-flex tw-items-center tw-justify-between tw-mb-l">
            <div>
                <h3 style="margin:0; font-size:18px;">{{ __('weeklyplanning.headlines.your_team') }}</h3>
                @if(count($teamMembers) > 0)
                <small style="color:var(--grey);">{{ count($teamMembers) }} {{ count($teamMembers) === 1 ? 'member' : 'members' }}</small>
                @endif
            </div>
            <div class="dropdown">
                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                    <i class="fa fa-plus"></i> {{ __('weeklyplanning.buttons.create_plan') }}
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-right">
                    @foreach($teamMembers as $member)
                    <li>
                        <a href="{{ BASE_URL }}/weekly-planning/newPlan?employeeId={{ $member['id'] }}">
                            <i class="fa fa-user" style="width:16px; opacity:.5;"></i>
                            {{ $member['firstname'] }} {{ $member['lastname'] }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        @if(count($teamMembers) === 0)
        <div style="text-align:center; padding:60px 20px; color:var(--grey);">
            <i class="fa fa-users" style="font-size:48px; opacity:.2; display:block; margin-bottom:12px;"></i>
            <p style="font-size:15px; font-weight:600; margin-bottom:6px;">{{ __('weeklyplanning.text.no_team_yet') }}</p>
            <p style="font-size:13px;">{{ __('weeklyplanning.text.no_team_hint') }}</p>
        </div>
        @else
        <div class="wp-team-grid">
            @foreach($teamMembers as $member)
            <div class="wp-member-card">

                {{-- Member header --}}
                <div class="wp-member-header">
                    <div class="wp-avatar">
                        {{ strtoupper(substr($member['firstname'] ?? '', 0, 1).substr($member['lastname'] ?? '', 0, 1)) }}
                    </div>
                    <div class="tw-flex-1 tw-min-w-0">
                        <div class="wp-member-name tw-truncate">{{ $member['firstname'] }} {{ $member['lastname'] }}</div>
                        @if(!empty($member['jobTitle']))
                        <div class="wp-member-role">{{ $member['jobTitle'] }}</div>
                        @else
                        <div class="wp-member-role">Team Member</div>
                        @endif
                    </div>
                    @if(count($member['plans']) > 0)
                    <span style="font-size:11px; color:var(--grey);">{{ count($member['plans']) }} plan{{ count($member['plans']) !== 1 ? 's' : '' }}</span>
                    @endif
                </div>

                {{-- Plans list --}}
                <div class="wp-plans-body">
                    @if(count($member['plans']) === 0)
                    <div class="wp-empty-plans">
                        <i class="fa fa-calendar-xmark" style="opacity:.3; font-size:20px; display:block; margin-bottom:6px;"></i>
                        {{ __('weeklyplanning.text.no_plan_for_member') }}
                    </div>
                    @else
                    @foreach($member['plans'] as $plan)
                    @php
                        $items   = $plan['_items'] ?? [];
                        $done    = collect($items)->where('status', 'completed')->count();
                        $blocked = collect($items)->where('status', 'blocked')->count();
                        $missed  = collect($items)->where('status', 'not_completed')->count();
                        $total   = count($items);
                        $statusClass = match($plan['status']) {
                            'reviewed' => 'wp-status-reviewed',
                            'active'   => 'wp-status-active',
                            default    => 'wp-status-draft',
                        };
                    @endphp
                    <div class="wp-plan-row">
                        <div class="wp-plan-meta">
                            <div class="wp-plan-week">{{ $plan['weekLabel'] }}</div>
                            <div class="wp-plan-dates">
                                {{ \Carbon\Carbon::parse($plan['weekStart'])->format('d M') }} –
                                {{ \Carbon\Carbon::parse($plan['weekEnd'])->format('d M') }}
                            </div>
                            @if($total > 0)
                            <div class="wp-plan-stats">
                                <span class="wp-stat wp-stat-done"><i class="fa fa-check-circle"></i> {{ $done }}</span>
                                @if($blocked > 0)
                                <span class="wp-stat wp-stat-blocked"><i class="fa fa-ban"></i> {{ $blocked }}</span>
                                @endif
                                @if($missed > 0)
                                <span class="wp-stat wp-stat-missed"><i class="fa fa-circle-xmark"></i> {{ $missed }}</span>
                                @endif
                                <span class="wp-stat wp-stat-total">/ {{ $total }} tasks</span>
                            </div>
                            @endif
                        </div>
                        <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
                            <span class="wp-status-badge {{ $statusClass }}">
                                {{ __('weeklyplanning.plan_status.'.$plan['status']) }}
                            </span>
                            <div style="display:flex; gap:5px; align-items:center;">
                                <a href="{{ BASE_URL }}/weekly-planning/showPlan/{{ $plan['id'] }}"
                                    class="wp-view-btn" preload="mouseover">
                                    {{ __('weeklyplanning.buttons.view_plan') }}
                                </a>
                                <a href="{{ BASE_URL }}/weekly-planning/deletePlan/{{ $plan['id'] }}"
                                    title="Delete plan"
                                    style="display:inline-flex; align-items:center; justify-content:center;
                                           width:26px; height:26px; border-radius:var(--element-radius);
                                           border:1px solid var(--main-border-color);
                                           background:var(--secondary-background); color:var(--grey);
                                           text-decoration:none; font-size:11px; transition:all .15s;"
                                    onmouseover="this.style.background='rgba(239,68,68,.1)'; this.style.color='#ef4444'; this.style.borderColor='rgba(239,68,68,.3)'"
                                    onmouseout="this.style.background='var(--secondary-background)'; this.style.color='var(--grey)'; this.style.borderColor='var(--main-border-color)'">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

                {{-- Card footer: create plan --}}
                <div class="wp-card-footer">
                    <a href="{{ BASE_URL }}/weekly-planning/newPlan?employeeId={{ $member['id'] }}"
                        class="wp-add-plan-btn">
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
