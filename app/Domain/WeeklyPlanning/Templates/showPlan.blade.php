@extends($layout)

@section('content')

<style>
/* ── Plan Review Page ───────────────────────────────── */
.wp-plan-hero {
    background: var(--secondary-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
    padding: 20px 24px;
    margin-bottom: 24px;
    display: flex; flex-wrap: wrap; gap: 20px; align-items: center;
}
.wp-hero-meta { display: flex; flex-wrap: wrap; gap: 20px; flex: 1; }
.wp-meta-item { display: flex; flex-direction: column; gap: 2px; min-width: 120px; }
.wp-meta-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: var(--grey); }
.wp-meta-value { font-size: 14px; font-weight: 600; }
.wp-plan-status-badge {
    padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap;
}
.wp-badge-reviewed { background: rgba(34,197,94,.15); color: #22c55e; }
.wp-badge-active   { background: rgba(74,158,255,.15); color: #4a9eff; }
.wp-badge-draft    { background: rgba(150,150,150,.1); color: var(--grey); }

/* ── Card wrapper ── */
.wp-card {
    background: var(--secondary-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
    margin-bottom: 18px;
    overflow: hidden;
}
.wp-card-head {
    padding: 12px 18px;
    border-bottom: 1px solid var(--main-border-color);
    display: flex; align-items: center; justify-content: space-between; gap: 8px;
}
.wp-card-title {
    font-size: 13px; font-weight: 700;
    display: flex; align-items: center; gap: 8px;
    margin: 0;
}
.wp-card-title i { color: var(--accent1); width: 16px; text-align: center; }
.wp-card-body { padding: 14px 18px; }

/* ── Tasks table ── */
.wp-tasks-table { width: 100%; border-collapse: collapse; }
.wp-tasks-table th {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    color: var(--grey); padding: 0 8px 10px 0; border-bottom: 2px solid var(--main-border-color);
}
.wp-tasks-table td {
    padding: 10px 8px 10px 0; border-bottom: 1px solid var(--main-border-color);
    vertical-align: middle; font-size: 13px;
}
.wp-tasks-table tr:last-child td { border-bottom: none; }
.wp-task-link { color: var(--primary-font-color); text-decoration: none; }
.wp-task-link:hover { color: var(--accent1); }
.wp-reason-note {
    margin-top: 5px; padding: 5px 10px; border-radius: 4px; font-size: 11px;
    background: var(--layered-background); border-left: 3px solid var(--accent2); color: var(--grey);
}
.wp-status-chip {
    display: inline-block; padding: 3px 10px; border-radius: 20px;
    font-size: 11px; font-weight: 600; white-space: nowrap;
}
.wp-chip-completed     { background: rgba(34,197,94,.15); color: #22c55e; }
.wp-chip-in_progress   { background: rgba(74,158,255,.15); color: #4a9eff; }
.wp-chip-blocked       { background: rgba(249,115,22,.15); color: #f97316; }
.wp-chip-not_completed { background: rgba(239,68,68,.15); color: #ef4444; }
.wp-chip-not_started   { background: rgba(150,150,150,.1); color: var(--grey); }

/* ── Section text blocks ── */
.wp-section-text {
    font-size: 13px; line-height: 1.7; white-space: pre-wrap;
    color: var(--primary-font-color);
    padding: 4px 0;
}
.wp-section-empty { font-size: 13px; color: var(--grey); font-style: italic; }
.wp-edit-btn {
    background: none; border: none; cursor: pointer;
    color: var(--grey); font-size: 13px; padding: 2px 6px;
    border-radius: 4px; transition: color .15s, background .15s;
}
.wp-edit-btn:hover { color: var(--accent1); background: rgba(74,158,255,.08); }

/* ── Sidebar right ── */
.wp-sidebar-card {
    background: var(--secondary-background);
    border: 1px solid var(--main-border-color);
    border-radius: var(--box-radius);
    box-shadow: var(--min-shadow);
    margin-bottom: 18px; overflow: hidden;
}
.wp-sidebar-head {
    padding: 11px 16px;
    border-bottom: 1px solid var(--main-border-color);
    display: flex; align-items: center; justify-content: space-between;
}
.wp-sidebar-title { font-size: 13px; font-weight: 700; display: flex; align-items: center; gap: 7px; margin: 0; }
.wp-sidebar-title i { color: var(--accent1); }
.wp-sidebar-body { padding: 14px 16px; }

/* ── Feedback rows ── */
.wp-fb-row { margin-bottom: 14px; padding-bottom: 14px; border-bottom: 1px solid var(--main-border-color); }
.wp-fb-row:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
.wp-fb-label { font-size: 11px; font-weight: 700; color: var(--grey); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px; }
.wp-fb-text { font-size: 13px; line-height: 1.6; }
.wp-fb-empty { font-size: 12px; color: var(--grey); font-style: italic; }
.wp-fb-edit-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }

/* ── Commitment items ── */
.wp-commitment-row { display: flex; align-items: flex-start; gap: 10px; padding: 8px 0; border-bottom: 1px solid var(--main-border-color); }
.wp-commitment-row:last-child { border-bottom: none; padding-bottom: 0; }
.wp-commitment-check { width: 16px; height: 16px; border-radius: 50%; background: rgba(74,158,255,.15); color: #4a9eff; display: flex; align-items: center; justify-content: center; font-size: 10px; flex-shrink: 0; margin-top: 2px; }
.wp-commitment-text { font-size: 13px; flex: 1; }
.wp-commitment-deadline { font-size: 11px; color: var(--grey); margin-top: 2px; }
.wp-commit-done .wp-commitment-check { background: rgba(34,197,94,.15); color: #22c55e; }
.wp-commit-done .wp-commitment-text { text-decoration: line-through; color: var(--grey); }

/* ── Growth items ── */
.wp-growth-row { margin-bottom: 12px; }
.wp-growth-row:last-child { margin-bottom: 0; }
.wp-growth-label { font-size: 11px; font-weight: 700; color: var(--grey); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 3px; }
.wp-growth-val { font-size: 13px; }
.wp-growth-empty { font-size: 12px; color: var(--grey); font-style: italic; }

/* ── Add plan item inline ── */
.wp-add-item-bar {
    display: flex; gap: 8px; margin-top: 10px;
}
.wp-add-item-bar .btn { flex-shrink: 0; }
</style>

<x-global::pageheader :icon="'fa fa-calendar-check'">
    <h1>
        {{ $plan['employeeFirstname'] }} {{ $plan['employeeLastname'] }} —
        {{ $plan['weekLabel'] }}, {{ $plan['month'] }}
    </h1>
</x-global::pageheader>

@displayNotification()

<div class="maincontent">
    <div class="maincontentinner">

        @if($isTeamLead)
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:8px;">
            <a href="{{ BASE_URL }}/weekly-planning/showTeam" class="btn btn-default btn-sm">
                <i class="fa fa-arrow-left"></i> {{ __('weeklyplanning.buttons.back_to_team') }}
            </a>
            <a href="{{ BASE_URL }}/weekly-planning/deletePlan/{{ $plan['id'] }}"
                class="btn btn-sm"
                style="background:rgba(239,68,68,.1); color:#ef4444; border:1px solid rgba(239,68,68,.3);"
                onmouseover="this.style.background='rgba(239,68,68,.2)'"
                onmouseout="this.style.background='rgba(239,68,68,.1)'">
                <i class="fa fa-trash"></i> Delete Plan
            </a>
        </div>
        @endif

        {{-- Hero bar --}}
        <div class="wp-plan-hero">
            <div class="wp-hero-meta">
                <div class="wp-meta-item">
                    <span class="wp-meta-label"><i class="fa fa-user" style="margin-right:4px;"></i>{{ __('weeklyplanning.labels.employee') }}</span>
                    <span class="wp-meta-value">{{ $plan['employeeFirstname'] }} {{ $plan['employeeLastname'] }}</span>
                </div>
                <div class="wp-meta-item">
                    <span class="wp-meta-label"><i class="fa fa-user-tie" style="margin-right:4px;"></i>{{ __('weeklyplanning.labels.team_lead') }}</span>
                    <span class="wp-meta-value">{{ $plan['teamLeadFirstname'] }} {{ $plan['teamLeadLastname'] }}</span>
                </div>
                <div class="wp-meta-item">
                    <span class="wp-meta-label"><i class="fa fa-calendar" style="margin-right:4px;"></i>{{ __('weeklyplanning.labels.week') }}</span>
                    <span class="wp-meta-value">
                        {{ \Carbon\Carbon::parse($plan['weekStart'])->format('d M') }}
                        – {{ \Carbon\Carbon::parse($plan['weekEnd'])->format('d M Y') }}
                    </span>
                </div>
                @if(!empty($plan['dateOfOneOnOne']))
                <div class="wp-meta-item">
                    <span class="wp-meta-label"><i class="fa fa-handshake" style="margin-right:4px;"></i>{{ __('weeklyplanning.labels.one_on_one_date') }}</span>
                    <span class="wp-meta-value" style="display:flex; align-items:center; gap:8px;">
                        {{ \Carbon\Carbon::parse($plan['dateOfOneOnOne'])->format('d M Y') }}
                        @if($isTeamLead)
                        <a href="{{ BASE_URL }}/oneonone/newSession?employeeId={{ $plan['employeeId'] }}&meetingDate={{ $plan['dateOfOneOnOne'] }}T12:00"
                            style="font-size:11px; padding:2px 8px; border-radius:20px;
                                   background:rgba(74,158,255,.12); color:var(--accent1);
                                   text-decoration:none; font-weight:600; white-space:nowrap;"
                            title="Schedule 1:1 session for this date">
                            <i class="fa fa-plus"></i> Schedule 1:1
                        </a>
                        @endif
                    </span>
                </div>
                @endif
            </div>
            @php
                $heroStatusClass = match($plan['status']) {
                    'reviewed' => 'wp-badge-reviewed',
                    'active'   => 'wp-badge-active',
                    default    => 'wp-badge-draft',
                };
            @endphp
            <span class="wp-plan-status-badge {{ $heroStatusClass }}">
                {{ __('weeklyplanning.plan_status.'.$plan['status']) }}
            </span>
        </div>

        <div class="row">
            {{-- ─────── LEFT column ─────── --}}
            <div class="col-md-8">

                {{-- Assigned Tasks --}}
                <div class="wp-card">
                    <div class="wp-card-head">
                        <h4 class="wp-card-title">
                            <i class="fa fa-list-check"></i>
                            {{ __('weeklyplanning.sections.assigned_tasks') }}
                        </h4>
                        @if($isTeamLead)
                        <div style="display:flex; gap:6px;">
                            <a href="#/tickets/newTicketForPlan?planId={{ $plan['id'] }}&employeeId={{ $plan['employeeId'] }}"
                                class="btn btn-xs btn-primary">
                                <i class="fa fa-plus"></i> {{ __('weeklyplanning.buttons.add_task') }}
                            </a>
                            <button class="btn btn-xs btn-default"
                                hx-get="{{ BASE_URL }}/hx/weekly-planning/planItems/addForm?planId={{ $plan['id'] }}"
                                hx-target="#add-item-container"
                                hx-swap="innerHTML">
                                <i class="fa fa-pen-to-square"></i> Add Custom
                            </button>
                            <button class="btn btn-xs btn-default"
                                hx-post="{{ BASE_URL }}/hx/weekly-planning/planItems/carryOver?planId={{ $plan['id'] }}"
                                hx-target="#plan-items-list"
                                hx-swap="innerHTML"
                                hx-confirm="{{ __('weeklyplanning.buttons.carry_over') }}?">
                                <i class="fa fa-forward"></i> {{ __('weeklyplanning.buttons.carry_over') }}
                            </button>
                        </div>
                        @endif
                    </div>
                    <div class="wp-card-body">
                        <div id="add-item-container"></div>
                        <table class="wp-tasks-table">
                            <thead>
                                <tr>
                                    <th>{{ __('weeklyplanning.labels.task') }}</th>
                                    <th style="width:160px;">{{ __('weeklyplanning.labels.status') }}</th>
                                    @if($isTeamLead)
                                    <th style="width:36px;"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="plan-items-list">
                                @include('weeklyplanning::partials.planItemsList', [
                                    'items'      => $items,
                                    'planId'     => $plan['id'],
                                    'isTeamLead' => $isTeamLead,
                                ])
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Text sections --}}
                @php
                $sections = [
                    ['key' => 'topPriorities',         'icon' => 'fa-star',          'label' => 'weeklyplanning.sections.top_priorities',    'editable' => true],
                    ['key' => 'winsAndProgress',        'icon' => 'fa-trophy',        'label' => 'weeklyplanning.sections.wins_and_progress',  'editable' => true],
                    ['key' => 'challengesAndBlockers',  'icon' => 'fa-circle-xmark',  'label' => 'weeklyplanning.sections.challenges_blockers','editable' => true],
                    ['key' => 'managerSupportNeeded',   'icon' => 'fa-hands-helping', 'label' => 'weeklyplanning.sections.manager_support',    'editable' => true],
                    ['key' => 'ideasAndSuggestions',    'icon' => 'fa-lightbulb',     'label' => 'weeklyplanning.sections.ideas_suggestions',  'editable' => true],
                    ['key' => 'nextWeekPriorities',     'icon' => 'fa-forward',       'label' => 'weeklyplanning.sections.next_week_priorities','editable' => $isTeamLead],
                ];
                @endphp

                @foreach($sections as $section)
                <div class="wp-card" id="section-{{ $section['key'] }}">
                    <div class="wp-card-head">
                        <h4 class="wp-card-title">
                            <i class="fa {{ $section['icon'] }}"></i>
                            {{ __($section['label']) }}
                        </h4>
                        @if($section['editable'])
                        <button class="wp-edit-btn"
                            hx-get="{{ BASE_URL }}/hx/weekly-planning/planItems/editSection?planId={{ $plan['id'] }}&field={{ $section['key'] }}"
                            hx-target="#section-{{ $section['key'] }}"
                            hx-swap="outerHTML"
                            title="Edit">
                            <i class="fa fa-pencil"></i>
                        </button>
                        @endif
                    </div>
                    <div class="wp-card-body">
                        @if(!empty($plan[$section['key']]))
                        <div class="wp-section-text">{{ $plan[$section['key']] }}</div>
                        @else
                        <span class="wp-section-empty">Nothing added yet</span>
                        @endif
                    </div>
                </div>
                @endforeach

            </div>

            {{-- ─────── RIGHT sidebar ─────── --}}
            <div class="col-md-4">

                {{-- Feedback Exchange --}}
                <div class="wp-sidebar-card">
                    <div class="wp-sidebar-head">
                        <h4 class="wp-sidebar-title">
                            <i class="fa fa-comments"></i> {{ __('weeklyplanning.sections.feedback_exchange') }}
                        </h4>
                    </div>
                    <div class="wp-sidebar-body">
                        @php $feedbackByType = collect($feedback)->keyBy('type')->toArray(); @endphp
                        @foreach($feedbackTypes as $type => $label)
                        @php
                            $canEdit = ($isTeamLead && str_starts_with($type, 'manager_'))
                                    || (! $isTeamLead && str_starts_with($type, 'employee_'));
                            $msg = $feedbackByType[$type]['message'] ?? null;
                        @endphp
                        <div class="wp-fb-row" id="feedback-{{ $type }}">
                            <div class="wp-fb-edit-row">
                                <span class="wp-fb-label">{{ __($label) }}</span>
                                @if($canEdit)
                                <button class="wp-edit-btn"
                                    hx-get="{{ BASE_URL }}/hx/weekly-planning/feedback/editForm?planId={{ $plan['id'] }}&type={{ $type }}"
                                    hx-target="#feedback-{{ $type }}"
                                    hx-swap="outerHTML"
                                    title="Edit">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                @endif
                            </div>
                            @if($msg)
                            <div class="wp-fb-text">{{ $msg }}</div>
                            @else
                            <div class="wp-fb-empty">Not provided yet</div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Growth & Development --}}
                <div class="wp-sidebar-card">
                    <div class="wp-sidebar-head">
                        <h4 class="wp-sidebar-title">
                            <i class="fa fa-seedling"></i> {{ __('weeklyplanning.sections.growth_development') }}
                        </h4>
                    </div>
                    <div class="wp-sidebar-body">
                        <div class="wp-growth-row">
                            <div class="wp-growth-label">{{ __('weeklyplanning.labels.current_focus') }}</div>
                            @if(!empty($plan['growthCurrentFocus']))
                            <div class="wp-growth-val">{{ $plan['growthCurrentFocus'] }}</div>
                            @else
                            <div class="wp-growth-empty">Not set</div>
                            @endif
                        </div>
                        <div class="wp-growth-row">
                            <div class="wp-growth-label">{{ __('weeklyplanning.labels.support_needed') }}</div>
                            @if(!empty($plan['growthSupportNeeded']))
                            <div class="wp-growth-val">{{ $plan['growthSupportNeeded'] }}</div>
                            @else
                            <div class="wp-growth-empty">Not set</div>
                            @endif
                        </div>
                        <div class="wp-growth-row">
                            <div class="wp-growth-label">{{ __('weeklyplanning.labels.next_milestone') }}</div>
                            @if(!empty($plan['growthNextMilestone']))
                            <div class="wp-growth-val">{{ $plan['growthNextMilestone'] }}</div>
                            @else
                            <div class="wp-growth-empty">Not set</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Commitments & Follow-ups --}}
                <div class="wp-sidebar-card">
                    <div class="wp-sidebar-head">
                        <h4 class="wp-sidebar-title">
                            <i class="fa fa-handshake"></i> {{ __('weeklyplanning.sections.commitments') }}
                        </h4>
                        @if($isTeamLead)
                        <button class="btn btn-xs btn-default"
                            hx-get="{{ BASE_URL }}/hx/weekly-planning/planItems/commitmentForm?planId={{ $plan['id'] }}"
                            hx-target="#commitment-container"
                            hx-swap="innerHTML">
                            <i class="fa fa-plus"></i>
                        </button>
                        @endif
                    </div>
                    <div class="wp-sidebar-body">
                        <div id="commitment-container"></div>
                        <div id="commitments-list">
                            @if(count($commitments) === 0)
                            <div style="font-size:12px; color:var(--grey); font-style:italic;">
                                {{ __('weeklyplanning.text.no_commitments') }}
                            </div>
                            @else
                            @foreach($commitments as $c)
                            @include('weeklyplanning::partials.commitment', ['c' => $c, 'isTeamLead' => $isTeamLead])
                            @endforeach
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
