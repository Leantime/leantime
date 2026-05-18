@extends($layout)

@section('content')

<div class="maincontent tlcm-dashboard" style="margin-top:0; padding-top:20px;">

    {!! $tpl->displayNotification() !!}

    {{-- ── Aggregated KPI Strip ── --}}
    <div class="tlcm-kpi-strip" style="display:grid; grid-template-columns: repeat(6,1fr); gap:14px; margin-bottom:20px; padding: 0 15px;">

        <div class="tlcm-kpi-card">
            <div class="kpi-icon"><i class="fa fa-fw fa-briefcase"></i></div>
            <div class="kpi-value">{{ $totalActiveProjects }}</div>
            <div class="kpi-label">My Projects</div>
        </div>

        <div class="tlcm-kpi-card">
            <div class="kpi-icon"><i class="fa fa-fw fa-list-check"></i></div>
            <div class="kpi-value">{{ $totalOpenTasks }}</div>
            <div class="kpi-label">Open Tasks</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalOverdue > 0 ? 'kpi-warn' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-clock"></i></div>
            <div class="kpi-value">{{ $totalOverdue }}</div>
            <div class="kpi-label">Overdue</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalBlocked > 0 ? 'kpi-danger' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-ban"></i></div>
            <div class="kpi-value">{{ $totalBlocked }}</div>
            <div class="kpi-label">Blocked</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalOpenReqs > 0 ? 'kpi-info' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-inbox"></i></div>
            <div class="kpi-value">{{ $totalOpenReqs }}</div>
            <div class="kpi-label">Open Client Requests</div>
        </div>

        <div class="tlcm-kpi-card {{ $totalPendingReviews > 0 ? 'kpi-review' : '' }}">
            <div class="kpi-icon"><i class="fa fa-fw fa-hourglass-half"></i></div>
            <div class="kpi-value">{{ $totalPendingReviews }}</div>
            <div class="kpi-label">Milestones to Review</div>
        </div>

    </div>

    <div class="maincontentinner" style="margin: 0 15px;">

        {{-- ── Top action bar ── --}}
        <div class="tlcm-actionbar">
            <div class="tlcm-actionbar-title">
                <i class="fa fa-fw fa-folder-open" style="opacity:.55;"></i>
                My Projects ({{ $totalActiveProjects }})
            </div>
            <div class="tlcm-actionbar-buttons">
                <a href="{{ BASE_URL }}/weekly-planning/showTeam" class="btn btn-default btn-sm">
                    <i class="fa fa-calendar-week"></i> Weekly Planning
                </a>
                @if($isCM)
                    <a href="{{ BASE_URL }}/projects/showAll" class="btn btn-default btn-sm">
                        <i class="fa fa-briefcase"></i> All Projects
                    </a>
                    @if($isAdmin)
                    <a href="{{ BASE_URL }}/clients/showAll" class="btn btn-default btn-sm">
                        <i class="fa fa-building"></i> Manage Clients
                    </a>
                    @endif
                    <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> New Project
                    </a>
                @endif
            </div>
        </div>

        {{-- ── Project rows ── --}}
        @if(empty($cards))
            <div class="tlcm-empty">
                <i class="fa fa-folder-open fa-3x" style="opacity:.35; margin-bottom:12px;"></i>
                <div style="font-size:16px; font-weight:600; margin-bottom:6px;">No projects assigned yet</div>
                <div style="opacity:.6; max-width:480px; margin:0 auto;">
                    @if($isCM)
                        Create your first project or ask an administrator to assign you to an existing one.
                        <div style="margin-top:16px;">
                            <a href="{{ BASE_URL }}/projects/newProject" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Create Project
                            </a>
                        </div>
                    @else
                        Ask an administrator or a manager to add you to a project.
                    @endif
                </div>
            </div>
        @else
            <div class="tlcm-project-list">
                @foreach($cards as $idx => $card)
                    @php
                        $p        = $card['project'];
                        $pid      = (int) $p['id'];
                        $percent  = isset($card['progress']['percent']) ? (int) round($card['progress']['percent']) : 0;
                        $health   = $card['health'];
                        $rowId    = 'tlcm-row-'.$pid;
                    @endphp

                    <div class="tlcm-row {{ $health === 'at_risk' ? 'is-atrisk' : '' }}" id="{{ $rowId }}">

                        {{-- ── Summary line (one row) ── --}}
                        <div class="tlcm-row-summary">

                            {{-- Toggle chevron --}}
                            <button type="button"
                                    class="tlcm-toggle"
                                    aria-expanded="false"
                                    aria-controls="{{ $rowId }}-detail"
                                    onclick="tlcmDashboard.toggle('{{ $rowId }}')"
                                    title="Show more details">
                                <i class="fa fa-chevron-right"></i>
                            </button>

                            {{-- Project name + client (clickable -> enter project) --}}
                            <a class="tlcm-row-name" href="{{ BASE_URL }}/projects/changeCurrentProject/{{ $pid }}">
                                <span class="tlcm-name-text">{{ $p['name'] ?? 'Untitled' }}</span>
                                @if(!empty($p['clientName']))
                                    <span class="tlcm-name-client">
                                        <i class="fa fa-fw fa-building" style="opacity:.55;"></i> {{ $p['clientName'] }}
                                    </span>
                                @endif
                            </a>

                            {{-- Progress inline --}}
                            <div class="tlcm-row-progress">
                                <div class="tlcm-progress-track">
                                    <div class="tlcm-progress-fill {{ $health === 'at_risk' ? 'fill-atrisk' : 'fill-ontrack' }}"
                                         style="width:{{ $percent }}%;"></div>
                                </div>
                                <span class="tlcm-progress-text">{{ $percent }}%</span>
                            </div>

                            {{-- Stats chips --}}
                            <div class="tlcm-row-stats">
                                <span class="tlcm-chip" title="Open tasks">
                                    <i class="fa fa-list-check"></i> {{ $card['openCount'] }}
                                </span>
                                @if($card['overdueCount'] > 0)
                                    <span class="tlcm-chip chip-warn" title="Overdue tasks">
                                        <i class="fa fa-clock"></i> {{ $card['overdueCount'] }}
                                    </span>
                                @endif
                                @if($card['blockedCount'] > 0)
                                    <span class="tlcm-chip chip-danger" title="Blocked tasks">
                                        <i class="fa fa-ban"></i> {{ $card['blockedCount'] }}
                                    </span>
                                @endif
                                @if(count($card['openRequests']) > 0)
                                    <span class="tlcm-chip chip-info" title="Open client requests">
                                        <i class="fa fa-inbox"></i> {{ count($card['openRequests']) }}
                                    </span>
                                @endif
                                @if(count($card['pendingMilestones']) > 0)
                                    <span class="tlcm-chip chip-review" title="Milestones awaiting your review">
                                        <i class="fa fa-hourglass-half"></i> {{ count($card['pendingMilestones']) }}
                                    </span>
                                @endif
                                <span class="tlcm-chip chip-soft" title="Team size">
                                    <i class="fa fa-users"></i> {{ count($card['team']) }}
                                </span>
                            </div>

                            {{-- Enter project --}}
                            <a class="tlcm-enter" href="{{ BASE_URL }}/projects/changeCurrentProject/{{ $pid }}" title="Enter project">
                                <i class="fa fa-arrow-right"></i>
                            </a>
                        </div>

                        {{-- ── Expandable detail panel ── --}}
                        <div class="tlcm-row-detail" id="{{ $rowId }}-detail" hidden>
                            <div class="tlcm-detail-grid">

                                {{-- Team --}}
                                <div class="tlcm-detail-block">
                                    <div class="tlcm-detail-title"><i class="fa fa-fw fa-users"></i> Team ({{ count($card['team']) }})</div>
                                    @if(empty($card['team']))
                                        <div class="tlcm-detail-empty">No members assigned.</div>
                                    @else
                                        <div class="tlcm-team-grid">
                                            @foreach($card['team'] as $member)
                                                <div class="tlcm-team-card">
                                                    <div class="tlcm-team-avatar">
                                                        {{ strtoupper(substr($member['firstname'] ?? '?', 0, 1)) }}{{ strtoupper(substr($member['lastname'] ?? '', 0, 1)) }}
                                                    </div>
                                                    <div class="tlcm-team-meta">
                                                        <div class="tlcm-team-name">{{ ($member['firstname'] ?? '') }} {{ ($member['lastname'] ?? '') }}</div>
                                                        <div class="tlcm-team-role">{{ ucfirst($member['projectRole'] ?? $member['role'] ?? 'Member') }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                {{-- Recent activity --}}
                                <div class="tlcm-detail-block">
                                    <div class="tlcm-detail-title"><i class="fa fa-fw fa-bolt"></i> Recent Activity</div>
                                    @if(empty($card['recentActivity']))
                                        <div class="tlcm-detail-empty">No recent updates.</div>
                                    @else
                                        <ul class="tlcm-detail-list">
                                            @foreach($card['recentActivity'] as $a)
                                                <li>
                                                    <span class="activity-who">{{ trim(($a['editorFirstname'] ?? '').' '.($a['editorLastname'] ?? '')) ?: 'Someone' }}</span>
                                                    <span class="activity-sep">·</span>
                                                    <a href="{{ BASE_URL }}/dashboard/home#/tickets/showTicket/{{ $a['id'] }}" class="activity-what">
                                                        {{ \Illuminate\Support\Str::limit($a['headline'], 50) }}
                                                    </a>
                                                    <span class="activity-when">{{ \Carbon\Carbon::parse($a['modified'])->diffForHumans() }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                {{-- Milestones pending review --}}
                                <div class="tlcm-detail-block">
                                    <div class="tlcm-detail-title">
                                        <i class="fa fa-fw fa-hourglass-half"></i> Milestones to Review ({{ count($card['pendingMilestones']) }})
                                    </div>
                                    @if(empty($card['pendingMilestones']))
                                        <div class="tlcm-detail-empty">No milestones awaiting review.</div>
                                    @else
                                        <ul class="tlcm-detail-list">
                                            @foreach($card['pendingMilestones'] as $ms)
                                                <li>
                                                    <div class="tlcm-milestone-review-row">
                                                        <a href="{{ BASE_URL }}/tickets/editMilestone/{{ $ms['id'] }}" class="tlcm-milestone-link">
                                                            <i class="fa fa-flag" style="opacity:.5;"></i>
                                                            {{ \Illuminate\Support\Str::limit($ms['headline'], 40) }}
                                                            @if(!empty($ms['editTo']) && $ms['editTo'] !== '0000-00-00 00:00:00')
                                                                <span class="req-when">Due {{ \Carbon\Carbon::parse($ms['editTo'])->format('M j') }}</span>
                                                            @endif
                                                        </a>
                                                        <div class="tlcm-milestone-actions">
                                                            <form method="post" action="{{ BASE_URL }}/tickets/editMilestone/{{ $ms['id'] }}?id={{ $ms['id'] }}" style="display:inline;">
                                                                <button type="submit" name="markComplete" value="1"
                                                                        class="btn-inline btn-inline-approve"
                                                                        title="Approve & Complete">
                                                                    <i class="fa fa-check"></i>
                                                                </button>
                                                            </form>
                                                            <a href="{{ BASE_URL }}/tickets/editMilestone/{{ $ms['id'] }}"
                                                               class="btn-inline btn-inline-view" title="Open to review / reject">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                {{-- Awaiting response --}}
                                <div class="tlcm-detail-block">
                                    <div class="tlcm-detail-title">
                                        <i class="fa fa-fw fa-inbox"></i> Awaiting My Response ({{ count($card['openRequests']) }})
                                    </div>
                                    @if(empty($card['openRequests']))
                                        <div class="tlcm-detail-empty">All client requests are handled.</div>
                                    @else
                                        <ul class="tlcm-detail-list">
                                            @foreach(array_slice($card['openRequests'], 0, 5) as $req)
                                                <li>
                                                    <a href="{{ BASE_URL }}/clientportal/adminRequests?projectId={{ $pid }}#request-{{ $req['id'] }}">
                                                        <span class="req-title">{{ \Illuminate\Support\Str::limit($req['title'] ?? 'Untitled', 55) }}</span>
                                                        <span class="req-when">
                                                            @if(!empty($req['createdAt']))
                                                                {{ \Carbon\Carbon::parse($req['createdAt'])->diffForHumans() }}
                                                            @endif
                                                        </span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                            </div>

                            {{-- Quick links --}}
                            <div class="tlcm-detail-actions">
                                <a href="{{ BASE_URL }}/projects/changeCurrentProject/{{ $pid }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-folder-open"></i> Enter Project
                                </a>
                                <a href="{{ BASE_URL }}/clientportal/adminRequests?projectId={{ $pid }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-inbox"></i> Client Requests
                                </a>
                                <a href="{{ BASE_URL }}/tickets/roadmap?projectId={{ $pid }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-chart-gantt"></i> Timeline
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>

<style>
/* ── TL / CM Dashboard ── */
.tlcm-kpi-card {
    background: var(--glass-background);
    backdrop-filter: var(--glass-blur);
    -webkit-backdrop-filter: var(--glass-blur);
    border: var(--glass-border);
    border-radius: var(--box-radius, 8px);
    box-shadow: var(--large-shadow);
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 2px;
    transition: box-shadow .15s, transform .15s;
}
.tlcm-kpi-card:hover  { box-shadow: var(--regular-shadow); transform: translateY(-2px); }
.tlcm-kpi-card .kpi-icon  { font-size: 18px; opacity: .45; margin-bottom: 4px; }
.tlcm-kpi-card .kpi-value { font-size: 28px; font-weight: 700; line-height: 1; }
.tlcm-kpi-card .kpi-label { font-size: 12px; opacity: .65; margin-top: 4px; }
.tlcm-kpi-card.kpi-warn   { border-left: 4px solid #f0ad4e; }
.tlcm-kpi-card.kpi-danger { border-left: 4px solid #d9534f; }
.tlcm-kpi-card.kpi-info   { border-left: 4px solid var(--accent1, #4a9eff); }
.tlcm-kpi-card.kpi-review { border-left: 4px solid #e67e22; }

/* Action bar */
.tlcm-actionbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.tlcm-actionbar-title  { font-size: 15px; font-weight: 600; opacity: .85; }
.tlcm-actionbar-buttons { display: flex; gap: 8px; flex-wrap: wrap; }
.tlcm-actionbar-buttons .btn { font-size: 12px; }

/* Project list */
.tlcm-project-list { display: flex; flex-direction: column; gap: 10px; }

.tlcm-row {
    background: var(--secondary-background, #fff);
    border-radius: var(--box-radius, 8px);
    box-shadow: var(--min-shadow);
    overflow: hidden;
    transition: box-shadow .15s;
}
.tlcm-row:hover { box-shadow: var(--regular-shadow); }
.tlcm-row.is-atrisk { border-left: 3px solid #d9534f; }

.tlcm-row-summary {
    display: grid;
    grid-template-columns: 36px minmax(220px, 1.5fr) minmax(140px, 0.8fr) auto 36px;
    align-items: center;
    gap: 14px;
    padding: 12px 16px;
}

.tlcm-toggle {
    background: transparent;
    border: 1px solid rgba(0,0,0,.1);
    color: inherit;
    width: 28px; height: 28px;
    border-radius: 50%;
    cursor: pointer;
    transition: background .15s, transform .15s;
    font-size: 11px;
    display: flex; align-items: center; justify-content: center;
}
.tlcm-toggle:hover { background: rgba(0,0,0,.05); }
.tlcm-toggle[aria-expanded="true"] { transform: rotate(90deg); background: rgba(74,158,255,.12); border-color: var(--accent1,#4a9eff); color: var(--accent1,#4a9eff); }

.tlcm-row-name {
    display: flex;
    flex-direction: column;
    color: inherit;
    text-decoration: none;
    min-width: 0;
}
.tlcm-row-name:hover .tlcm-name-text { color: var(--accent1, #4a9eff); }
.tlcm-name-text   { font-size: 15px; font-weight: 600; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.tlcm-name-client { font-size: 11px; opacity: .6; margin-top: 3px; }

.tlcm-row-progress {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
}
.tlcm-progress-track {
    flex: 1;
    height: 6px;
    background: rgba(0,0,0,.08);
    border-radius: 3px;
    overflow: hidden;
}
.tlcm-progress-fill { height: 100%; border-radius: 3px; transition: width .4s ease; }
.fill-ontrack { background: #5cb85c; }
.fill-atrisk  { background: #d9534f; }
.tlcm-progress-text { font-size: 11px; opacity: .6; min-width: 32px; text-align: right; }

.tlcm-row-stats { display: flex; gap: 6px; flex-wrap: wrap; justify-content: flex-end; }
.tlcm-chip {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 20px;
    background: rgba(0,0,0,.06);
    color: var(--primary-font-color);
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.tlcm-chip.chip-soft   { opacity: .65; }
.tlcm-chip.chip-warn   { background: rgba(240,173,78,.18); color: #c8860a; }
.tlcm-chip.chip-danger { background: rgba(217,83,79,.18); color: #d9534f; }
.tlcm-chip.chip-info   { background: rgba(74,158,255,.18); color: var(--accent1,#4a9eff); }
.tlcm-chip.chip-review { background: rgba(230,126,34,.18); color: #e67e22; }

.tlcm-enter {
    width: 32px; height: 32px;
    border-radius: 50%;
    background: rgba(74,158,255,.12);
    color: var(--accent1, #4a9eff);
    display: flex; align-items: center; justify-content: center;
    text-decoration: none;
    font-size: 13px;
    transition: background .15s, transform .15s;
}
.tlcm-enter:hover { background: var(--accent1, #4a9eff); color: #fff; transform: translateX(2px); }

/* Detail panel */
.tlcm-row-detail {
    border-top: 1px solid rgba(0,0,0,.06);
    padding: 16px 18px;
    background: rgba(0,0,0,.015);
}
.tlcm-detail-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
}
.tlcm-detail-block { min-width: 0; }
.tlcm-detail-title {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .5px;
    opacity: .55;
    margin-bottom: 8px;
}
.tlcm-detail-empty { font-size: 12px; opacity: .55; padding: 4px 0; }

.tlcm-detail-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
.tlcm-detail-list li { font-size: 12px; }
.tlcm-detail-list li a {
    color: inherit;
    text-decoration: none;
    display: flex;
    justify-content: space-between;
    gap: 8px;
    padding: 5px 6px;
    border-radius: 4px;
    transition: background .12s;
}
.tlcm-detail-list li a:hover { background: rgba(0,0,0,.04); }

.activity-who   { font-weight: 600; opacity: .8; }
.activity-sep   { opacity: .35; margin: 0 4px; }
.activity-what  { opacity: .75; }
.activity-when  { opacity: .45; font-size: 11px; white-space: nowrap; }

.req-title { flex: 1; }
.req-when  { opacity: .5; font-size: 11px; white-space: nowrap; }

/* Team mini grid in detail panel */
.tlcm-team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 6px;
}
.tlcm-team-card {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 8px;
    border-radius: 4px;
    background: rgba(0,0,0,.04);
}
.tlcm-team-avatar {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--accent1, #4a9eff);
    color: #fff;
    font-size: 10px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.tlcm-team-name { font-size: 12px; font-weight: 600; line-height: 1.15; }
.tlcm-team-role { font-size: 10px; opacity: .55; }

.tlcm-detail-actions {
    display: flex; gap: 8px; flex-wrap: wrap;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 1px dashed rgba(0,0,0,.08);
}
.tlcm-detail-actions .btn { font-size: 12px; }

/* Empty state */
.tlcm-empty {
    background: var(--secondary-background, #fff);
    border-radius: var(--box-radius, 8px);
    box-shadow: var(--regular-shadow);
    padding: 50px 30px;
    text-align: center;
}

/* Milestone review rows */
.tlcm-milestone-review-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 5px 6px;
    border-radius: 4px;
    transition: background .12s;
}
.tlcm-milestone-review-row:hover { background: rgba(0,0,0,.04); }
.tlcm-milestone-link {
    display: flex;
    align-items: center;
    gap: 6px;
    color: inherit;
    text-decoration: none;
    font-size: 12px;
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.tlcm-milestone-link:hover { color: var(--accent1, #4a9eff); }
.tlcm-milestone-actions { display: flex; gap: 4px; flex-shrink: 0; }

.btn-inline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px; height: 26px;
    border-radius: 50%;
    border: 1px solid transparent;
    font-size: 11px;
    cursor: pointer;
    text-decoration: none;
    transition: background .12s, color .12s, border-color .12s;
    background: rgba(0,0,0,.05);
    color: inherit;
    padding: 0;
}
.btn-inline:hover { text-decoration: none; }
.btn-inline-approve {
    background: rgba(92,184,92,.15);
    color: #3d9140;
    border-color: rgba(92,184,92,.3);
}
.btn-inline-approve:hover {
    background: #5cb85c;
    color: #fff;
    border-color: #5cb85c;
}
.btn-inline-view {
    background: rgba(230,126,34,.12);
    color: #e67e22;
    border-color: rgba(230,126,34,.3);
}
.btn-inline-view:hover {
    background: #e67e22;
    color: #fff;
    border-color: #e67e22;
}

/* Mobile */
@media (max-width: 900px) {
    .tlcm-kpi-strip   { grid-template-columns: repeat(2,1fr) !important; }
    .tlcm-row-summary { grid-template-columns: 32px 1fr auto; row-gap: 8px; }
    .tlcm-row-progress, .tlcm-row-stats { grid-column: 1 / -1; }
    .tlcm-detail-grid { grid-template-columns: 1fr; }
}
</style>

<script>
var tlcmDashboard = (function () {
    function toggle(rowId) {
        var row    = document.getElementById(rowId);
        if (!row) return;
        var btn    = row.querySelector('.tlcm-toggle');
        var detail = row.querySelector('.tlcm-row-detail');
        if (!btn || !detail) return;
        var open = btn.getAttribute('aria-expanded') === 'true';
        btn.setAttribute('aria-expanded', open ? 'false' : 'true');
        if (open) {
            detail.setAttribute('hidden', '');
        } else {
            detail.removeAttribute('hidden');
        }
    }
    return { toggle: toggle };
})();
</script>

@endsection
