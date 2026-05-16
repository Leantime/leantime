@extends($layout)

@section('content')


<div class="maincontent admin-dashboard" style="margin-top:0; padding-top:20px;">

    {!! $tpl->displayNotification() !!}

    {{-- ── KPI Strip ── --}}
    <div class="admin-kpi-strip" style="display:grid; grid-template-columns: repeat(4,1fr); gap:16px; margin-bottom:20px; padding: 0 15px;">

        <div class="admin-kpi-card" data-filter="all">
            <div class="kpi-icon"><i class="fa fa-fw fa-briefcase"></i></div>
            <div class="kpi-value">{{ $totalActiveProjects }}</div>
            <div class="kpi-label">Active Projects</div>
        </div>

        <div class="admin-kpi-card {{ $totalOverdue > 0 ? 'kpi-warn' : '' }}" data-filter="at_risk">
            <div class="kpi-icon"><i class="fa fa-fw fa-clock"></i></div>
            <div class="kpi-value">{{ $totalOverdue }}</div>
            <div class="kpi-label">Overdue Tasks</div>
        </div>

        <div class="admin-kpi-card {{ $totalBlocked > 0 ? 'kpi-danger' : '' }}" data-filter="at_risk">
            <div class="kpi-icon"><i class="fa fa-fw fa-ban"></i></div>
            <div class="kpi-value">{{ $totalBlocked }}</div>
            <div class="kpi-label">Blocked Tasks</div>
        </div>

        <div class="admin-kpi-card {{ $openClientRequests > 0 ? 'kpi-info' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-inbox"></i></div>
            <div class="kpi-value">{{ $openClientRequests }}</div>
            <div class="kpi-label">
                <a href="{{ BASE_URL }}/clientportal/adminRequests" style="color:inherit; text-decoration:none;">
                    Open Client Requests
                </a>
            </div>
        </div>

    </div>{{-- /kpi strip --}}

    <div class="maincontentinner" style="margin: 0 15px;">

    {{-- ── Toolbar: search + filter chips + new project ── --}}
    <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; margin-bottom:20px;">

        <input
            type="text"
            id="admin-project-search"
            placeholder="Search projects or clients…"
            class="formfield"
            style="max-width:280px; margin:0;"
            oninput="adminDashboard.filterCards()"
        />

        <div class="admin-filter-chips" style="display:flex; gap:8px; flex-wrap:wrap;">
            <button class="admin-chip active" data-health="all" onclick="adminDashboard.setFilter(this,'all')">All</button>
            <button class="admin-chip" data-health="at_risk" onclick="adminDashboard.setFilter(this,'at_risk')">At Risk</button>
            <button class="admin-chip" data-health="idle" onclick="adminDashboard.setFilter(this,'idle')">Idle</button>
            <button class="admin-chip" data-health="on_track" onclick="adminDashboard.setFilter(this,'on_track')">On Track</button>
        </div>

        <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary" style="margin-left:auto;">
            <i class="fa fa-plus"></i> New Project
        </a>

    </div>

    {{-- ── Project cards grid ── --}}
    <div id="admin-cards-grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(480px,1fr)); gap:20px;">

        @forelse($projectCards as $card)
            @php
                $project  = $card['project'];
                $progress = $card['progress'];
                $team     = $card['team'];
                $health   = $card['health'];
                $percent  = isset($progress['percent']) ? (int) round($progress['percent']) : 0;

                $healthLabel = match($health) {
                    'at_risk'  => '<span class="health-badge health-atrisk"><i class="fa fa-exclamation-triangle"></i> At Risk</span>',
                    'idle'     => '<span class="health-badge health-idle"><i class="fa fa-pause-circle"></i> Idle</span>',
                    default    => '<span class="health-badge health-ontrack"><i class="fa fa-check-circle"></i> On Track</span>',
                };

                // Find the project's team lead. Prefer per-project assignment, then fall back to system role.
                // role can be stored as string ('teamlead') or numeric key (25), so we check both forms.
                $isTeamLead = function ($u) {
                    $projectRole = $u['projectRole'] ?? '';
                    $sysRole     = $u['role'] ?? '';
                    return $projectRole === 'teamlead'
                        || (string) $sysRole === 'teamlead'
                        || (int) $sysRole === 25;
                };
                $teamLead = collect($team)->first($isTeamLead)
                         ?? collect($team)->first();
            @endphp

            <div
                class="admin-project-card"
                data-health="{{ $health }}"
                data-name="{{ strtolower($project['name']) }}"
                data-client="{{ strtolower($project['clientName'] ?? '') }}"
            >
                {{-- Card header --}}
                <div class="card-header">
                    <div style="flex:1; min-width:0;">
                        <div class="card-title">
                            <a href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}">
                                {{ $project['name'] }}
                            </a>
                        </div>
                        @if(!empty($project['clientName']))
                            <div class="card-client">
                                <i class="fa fa-fw fa-building" style="opacity:.6;"></i>
                                {{ $project['clientName'] }}
                            </div>
                        @endif
                    </div>
                    <div style="flex-shrink:0;">
                        {!! $healthLabel !!}
                    </div>
                </div>

                {{-- Progress bar --}}
                <div class="card-progress-wrap">
                    <div style="display:flex; justify-content:space-between; font-size:12px; opacity:.75; margin-bottom:4px;">
                        <span>Progress</span>
                        <span>{{ $percent }}%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill {{ $health === 'at_risk' ? 'fill-atrisk' : ($health === 'idle' ? 'fill-idle' : 'fill-ontrack') }}"
                             style="width:{{ $percent }}%;"></div>
                    </div>
                    @if(!empty($project['end']) && $project['end'] !== '0000-00-00')
                        <div style="font-size:11px; opacity:.6; margin-top:4px;">
                            Due: {{ \Carbon\Carbon::parse($project['end'])->format('M j, Y') }}
                        </div>
                    @endif
                </div>

                {{-- Stats row --}}
                @if($card['overdueCount'] > 0 || $card['blockedCount'] > 0)
                <div class="card-stats-row">
                    @if($card['overdueCount'] > 0)
                        <span class="stat-badge stat-overdue">
                            <i class="fa fa-clock"></i> {{ $card['overdueCount'] }} overdue
                        </span>
                    @endif
                    @if($card['blockedCount'] > 0)
                        <span class="stat-badge stat-blocked">
                            <i class="fa fa-ban"></i> {{ $card['blockedCount'] }} blocked
                        </span>
                    @endif
                </div>
                @endif

                {{-- Team summary — avatar stack + count only --}}
                @if(!empty($team))
                <div class="card-section card-team-summary">
                    <div class="team-avatar-stack">
                        @foreach(array_slice($team, 0, 4) as $member)
                            <div class="team-avatar-bubble"
                                 title="{{ ($member['firstname'] ?? '') }} {{ ($member['lastname'] ?? '') }} — {{ ucfirst($member['projectRole'] ?? $member['role'] ?? 'Member') }}">
                                {{ strtoupper(substr($member['firstname'] ?? '?', 0, 1)) }}{{ strtoupper(substr($member['lastname'] ?? '', 0, 1)) }}
                            </div>
                        @endforeach
                        @if(count($team) > 4)
                            <div class="team-avatar-bubble team-avatar-more">
                                +{{ count($team) - 4 }}
                            </div>
                        @endif
                    </div>
                    <span class="team-count-label">
                        <i class="fa fa-fw fa-users"></i> {{ count($team) }} {{ count($team) === 1 ? 'member' : 'members' }}
                    </span>
                </div>
                @endif

                {{-- Recent activity --}}
                @if(!empty($card['recentActivity']))
                <div class="card-section">
                    <div class="card-section-title">
                        <i class="fa fa-fw fa-bolt"></i> Recent Activity
                    </div>
                    <ul class="card-activity-list">
                        @foreach($card['recentActivity'] as $activity)
                            <li>
                                <span class="activity-who">{{ $activity['editorFirstname'] }} {{ $activity['editorLastname'] }}</span>
                                <span class="activity-what">{{ \Illuminate\Support\Str::limit($activity['headline'], 45) }}</span>
                                <span class="activity-when">{{ \Carbon\Carbon::parse($activity['modified'])->diffForHumans() }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Upcoming milestones --}}
                @if(!empty($card['upcomingMilestones']))
                <div class="card-section">
                    <div class="card-section-title">
                        <i class="fa fa-fw fa-flag"></i> Upcoming Milestones
                    </div>
                    <ul class="card-milestone-list">
                        @foreach($card['upcomingMilestones'] as $ms)
                            <li>
                                <span class="ms-name">{{ \Illuminate\Support\Str::limit($ms['headline'], 40) }}</span>
                                <span class="ms-date">{{ \Carbon\Carbon::parse($ms['date'])->format('M j') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                {{-- Card footer actions --}}
                <div class="card-footer-actions">
                    <a href="{{ BASE_URL }}/projects/showProject/{{ $project['id'] }}" class="btn btn-default btn-sm">
                        <i class="fa fa-folder-open"></i> View Project
                    </a>
                    <a href="{{ BASE_URL }}/tickets/roadmap?projectId={{ $project['id'] }}" class="btn btn-default btn-sm">
                        <i class="fa fa-chart-gantt"></i> Timeline
                    </a>
                    @if($teamLead)
                        <a href="{{ BASE_URL }}/oneonone/newSession?employeeId={{ $teamLead['id'] }}" class="btn btn-default btn-sm">
                            <i class="fa fa-handshake"></i> 1:1 with {{ $teamLead['firstname'] ?? 'Lead' }}
                        </a>
                    @endif
                </div>

            </div>
        @empty
            <div style="grid-column:1/-1; text-align:center; padding:60px 0; opacity:.5;">
                <i class="fa fa-briefcase fa-3x" style="margin-bottom:12px; display:block;"></i>
                No active projects found.
                <br>
                <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary" style="margin-top:16px;">
                    Create your first project
                </a>
            </div>
        @endforelse

    </div>{{-- /grid --}}

    </div>{{-- /maincontentinner --}}

</div>{{-- /maincontent --}}


<style>
/* ── Admin Dashboard ── */
.admin-kpi-strip {
    margin-bottom: 28px;
}

.admin-kpi-card {
    background: var(--glass-background);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    border: var(--glass-border);
    border-radius: var(--box-radius, 8px);
    box-shadow: var(--large-shadow);
    padding: 22px 26px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    cursor: pointer;
    transition: box-shadow .15s, transform .15s;
}
.admin-kpi-card:hover { box-shadow: var(--regular-shadow); transform: translateY(-2px); }
.admin-kpi-card .kpi-icon { font-size: 22px; opacity: .45; margin-bottom: 6px; }
.admin-kpi-card .kpi-value { font-size: 36px; font-weight: 700; line-height: 1; }
.admin-kpi-card .kpi-label { font-size: 13px; opacity: .65; margin-top: 4px; }
.admin-kpi-card.kpi-warn  { border-left: 4px solid #f0ad4e; }
.admin-kpi-card.kpi-danger{ border-left: 4px solid #d9534f; }
.admin-kpi-card.kpi-info  { border-left: 4px solid var(--accent1, #4a9eff); }

/* Filter chips */
.admin-chip {
    padding: 5px 14px;
    border-radius: 20px;
    border: 1px solid var(--accent1, #4a9eff);
    background: transparent;
    color: var(--primary-font-color);
    cursor: pointer;
    font-size: 13px;
    transition: background .15s, color .15s;
}
.admin-chip.active,
.admin-chip:hover {
    background: var(--accent1, #4a9eff);
    color: #fff;
}

/* Project card */
.admin-project-card {
    background: var(--secondary-background, #fff);
    border-radius: var(--box-radius, 8px);
    box-shadow: var(--min-shadow);
    display: flex;
    flex-direction: column;
    gap: 0;
    overflow: hidden;
    transition: box-shadow .15s;
}
.admin-project-card:hover { box-shadow: var(--regular-shadow); }
.admin-project-card.hidden { display: none !important; }

.card-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 18px 20px 12px;
    border-bottom: 1px solid rgba(0,0,0,.06);
}
.card-title {
    font-size: 16px;
    font-weight: 600;
    line-height: 1.3;
}
.card-title a { color: inherit; text-decoration: none; }
.card-title a:hover { color: var(--accent1, #4a9eff); }
.card-client { font-size: 12px; opacity: .6; margin-top: 3px; }

/* Health badge */
.health-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    white-space: nowrap;
}
.health-atrisk  { background: rgba(217,83,79,.12); color: #d9534f; }
.health-idle    { background: rgba(240,173,78,.12); color: #c8860a; }
.health-ontrack { background: rgba(92,184,92,.12);  color: #3d9140; }

/* Progress bar */
.card-progress-wrap { padding: 12px 20px; border-bottom: 1px solid rgba(0,0,0,.06); }
.progress-bar-track {
    height: 7px;
    border-radius: 4px;
    background: rgba(0,0,0,.08);
    overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    border-radius: 4px;
    transition: width .4s ease;
}
.fill-ontrack { background: #5cb85c; }
.fill-idle    { background: #f0ad4e; }
.fill-atrisk  { background: #d9534f; }

/* Stats badges */
.card-stats-row { display: flex; gap: 8px; padding: 8px 20px; flex-wrap: wrap; }
.stat-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
}
.stat-overdue { background: rgba(240,173,78,.15); color: #c8860a; }
.stat-blocked { background: rgba(217,83,79,.15);  color: #d9534f; }

/* Card sections */
.card-section {
    padding: 12px 20px;
    border-top: 1px solid rgba(0,0,0,.05);
}
.card-section-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    opacity: .5;
    margin-bottom: 8px;
}

/* Team summary */
.card-team-summary {
    display: flex;
    align-items: center;
    gap: 12px;
}
.team-avatar-stack {
    display: flex;
    flex-direction: row;
}
.team-avatar-bubble {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--accent1, #4a9eff);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 2px solid var(--secondary-background, #fff);
    margin-left: -8px;
    cursor: default;
    transition: transform .15s;
}
.team-avatar-bubble:first-child { margin-left: 0; }
.team-avatar-bubble:hover { transform: translateY(-3px); z-index: 2; }
.team-avatar-more {
    background: rgba(0,0,0,.18);
    font-size: 10px;
}
.team-count-label {
    font-size: 13px;
    opacity: .65;
}

/* Activity */
.card-activity-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.card-activity-list li {
    font-size: 12px;
    display: flex;
    gap: 6px;
    align-items: baseline;
    flex-wrap: wrap;
}
.activity-who   { font-weight: 600; opacity: .85; white-space: nowrap; }
.activity-what  { opacity: .75; flex: 1; }
.activity-when  { opacity: .45; white-space: nowrap; font-size: 11px; }

/* Milestones */
.card-milestone-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.card-milestone-list li {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    align-items: center;
}
.ms-name { opacity: .85; }
.ms-date { font-size: 11px; opacity: .55; white-space: nowrap; }

/* Footer actions */
.card-footer-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    padding: 12px 20px 16px;
    border-top: 1px solid rgba(0,0,0,.06);
    margin-top: auto;
}
.card-footer-actions .btn { font-size: 12px; }
</style>

<script>
var adminDashboard = (function () {
    var activeFilter = 'all';

    function setFilter(btn, filter) {
        activeFilter = filter;
        document.querySelectorAll('.admin-chip').forEach(function (c) {
            c.classList.toggle('active', c.dataset.health === filter);
        });
        applyFilters();
    }

    function filterCards() {
        applyFilters();
    }

    function applyFilters() {
        var search = (document.getElementById('admin-project-search').value || '').toLowerCase().trim();
        document.querySelectorAll('.admin-project-card').forEach(function (card) {
            var matchHealth = activeFilter === 'all' || card.dataset.health === activeFilter;
            var matchSearch = !search
                || (card.dataset.name || '').includes(search)
                || (card.dataset.client || '').includes(search);
            card.classList.toggle('hidden', !(matchHealth && matchSearch));
        });
    }

    return { setFilter: setFilter, filterCards: filterCards };
})();
</script>

@endsection
