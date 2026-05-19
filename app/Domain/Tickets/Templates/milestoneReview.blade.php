@extends($layout)

@section('content')
@php
$milestone = $tpl->get('milestone');
$milestoneTasks = $tpl->get('milestoneTasks') ?? [];
$statusLabels = $tpl->get('statusLabels') ?? [];
$milestoneProgress = (float) ($tpl->get('milestoneProgress') ?? 0);
$doneStatusId = $tpl->get('doneStatusId');
$canReview = $tpl->get('canCompleteMilestone') ?? false;

$milestoneStatus = (int) ($milestone->status ?? 3);
$isReadyForReview = $milestoneStatus === 5;
$isCompleted = $doneStatusId !== null && (string) $milestoneStatus === (string) $doneStatusId;
$isInProgress = !$isReadyForReview && !$isCompleted;
$allTasksDone = $milestoneProgress >= 100;

$totalTasks = count($milestoneTasks);
$doneTasks = 0;
foreach ($milestoneTasks as $t) {
$s = (int)(is_object($t) ? ($t->status ?? 0) : ($t['status'] ?? 0));
$label = $statusLabels[$s] ?? [];
if (($label['statusType'] ?? '') === 'DONE') { $doneTasks++; }
}

$dueDateStr = '';
$rawDue = is_object($milestone) ? ($milestone->editTo ?? '') : '';
if (!empty($rawDue) && $rawDue !== '0000-00-00 00:00:00') {
try { $dueDateStr = \Carbon\Carbon::parse($rawDue)->format('M j, Y'); } catch (\Exception $e) {}
}
@endphp

<div class="maincontent milestone-review-page" style="margin-top: 0; padding: 24px 30px;">

    {!! $tpl->displayNotification() !!}

    {{-- ── Breadcrumb ── --}}
    <div class="milestone-review-breadcrumb">
        <a href="{{ BASE_URL }}/dashboard/home"><i class="fa fa-home"></i></a>
        <span class="bc-sep">/</span>
        <a href="{{ BASE_URL }}/tickets/roadmap">Milestones</a>
        <span class="bc-sep">/</span>
        <span>{{ $milestone->headline ?? 'Milestone' }}</span>
    </div>

    {{-- ── Page header ── --}}
    <div class="milestone-review-header">
        <div class="mrh-left">
            <h2 class="mrh-title">
                @if(!empty($milestone->tags))
                <span class="mrh-color-dot" style="background:{{ $milestone->tags }};"></span>
                @endif
                {{ $milestone->headline ?? 'Untitled Milestone' }}
            </h2>
            <div class="mrh-meta">
                @if($dueDateStr)
                <span><i class="fa fa-calendar-alt"></i> Due {{ $dueDateStr }}</span>
                @endif
                @if(!empty($milestone->editorFirstname))
                <span><i class="fa fa-user"></i> {{ $milestone->editorFirstname }} {{ $milestone->editorLastname ?? '' }}</span>
                @endif
                <span><i class="fa fa-layer-group"></i>
                    @if(isset($statusLabels[$milestoneStatus]))
                    {{ $statusLabels[$milestoneStatus]['name'] }}
                    @else
                    Unknown
                    @endif
                </span>
            </div>
        </div>
        <div class="mrh-right" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            {{-- Status badge --}}
            @if($isCompleted)
            <span class="ms-badge ms-badge-done"><i class="fa fa-check-circle"></i> Completed</span>
            @elseif($isReadyForReview)
            <span class="ms-badge ms-badge-review"><i class="fa fa-hourglass-half"></i> Ready for Review</span>
            @elseif($allTasksDone)
            <span class="ms-badge ms-badge-allDone"><i class="fa fa-tasks"></i> All Tasks Done</span>
            @else
            <span class="ms-badge ms-badge-inprogress"><i class="fa fa-spinner"></i> In Progress</span>
            @endif
            {{-- Delete button (editor+ only) --}}
            @if(\Leantime\Domain\Auth\Services\Auth::userIsAtLeast(\Leantime\Domain\Auth\Models\Roles::$editor))
            <a href="#/tickets/delMilestone/{{ $milestone->id }}"
                class="btn btn-danger btn-xs ms-delete-btn"
                title="Delete this milestone">
                <i class="fa fa-trash-can"></i> Delete
            </a>
            @endif
        </div>
    </div>

    {{-- ── Main grid ── --}}
    <div class="milestone-review-grid">

        {{-- ══ Left: Task list ══ --}}
        <div class="mr-main">

            {{-- Progress bar --}}
            <div class="mr-section">
                <div class="mr-section-title">
                    <i class="fa fa-chart-bar"></i> Progress
                    <span class="mr-section-sub">{{ $doneTasks }} of {{ $totalTasks }} tasks done</span>
                </div>
                <div class="mr-progress-track">
                    <div class="mr-progress-fill {{ $isCompleted ? 'fill-done' : ($allTasksDone ? 'fill-review' : 'fill-active') }}"
                        style="width: {{ min(100, (int) round($milestoneProgress)) }}%;"></div>
                </div>
                <div class="mr-progress-label">{{ (int) round($milestoneProgress) }}% complete</div>
            </div>

            {{-- Task table --}}
            <div class="mr-section">
                <div class="mr-section-title">
                    <i class="fa fa-list-check"></i> Tasks
                    <a href="#/tickets/newTicketForMilestone?milestoneId={{ $milestone->id }}&projectId={{ $milestone->projectId }}"
                        class="btn btn-default btn-xs" style="margin-left:auto; font-size:11px;">
                        <i class="fa fa-plus"></i> Add Task
                    </a>
                </div>

                @if(empty($milestoneTasks))
                <div class="mr-empty">
                    <i class="fa fa-inbox fa-2x" style="opacity:.3; margin-bottom:8px;"></i>
                    <div>No tasks assigned to this milestone yet.</div>
                </div>
                @else
                <table class="mr-task-table">
                    <thead>
                        <tr>
                            <th style="width:36px;"></th>
                            <th>Task</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($milestoneTasks as $task)
                        @php
                        $isObj = is_object($task);
                        $tid = (int)($isObj ? ($task->id ?? 0) : ($task['id'] ?? 0));
                        $headline = $isObj ? ($task->headline ?? '') : ($task['headline'] ?? '');
                        $taskStatus = (int)($isObj ? ($task->status ?? 0) : ($task['status'] ?? 0));
                        $editorFirst = $isObj ? ($task->editorFirstname ?? '') : ($task['editorFirstname'] ?? '');
                        $editorLast = $isObj ? ($task->editorLastname ?? '') : ($task['editorLastname'] ?? '');
                        $taskDue = $isObj ? ($task->dateToFinish ?? '') : ($task['dateToFinish'] ?? '');
                        $taskLabel = $statusLabels[$taskStatus] ?? ['name' => 'Unknown', 'statusType' => ''];
                        $isDone = ($taskLabel['statusType'] ?? '') === 'DONE';
                        $isBlocked = $taskStatus === 1;
                        $taskDueStr = '';
                        if (!empty($taskDue) && $taskDue !== '0000-00-00 00:00:00') {
                        try { $taskDueStr = \Carbon\Carbon::parse($taskDue)->format('M j'); } catch (\Exception $e) {}
                        }
                        $isOverdue = !$isDone && !empty($taskDueStr) && \Carbon\Carbon::parse($taskDue)->isPast();
                        @endphp
                        <tr class="mr-task-row {{ $isDone ? 'task-done' : '' }} {{ $isBlocked ? 'task-blocked' : '' }}">
                            <td class="task-check">
                                @if($isDone)
                                <span class="task-tick"><i class="fa fa-check-circle"></i></span>
                                @elseif($isBlocked)
                                <span class="task-blocked-icon"><i class="fa fa-ban"></i></span>
                                @else
                                <span class="task-open-icon"><i class="fa fa-circle-notch"></i></span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ BASE_URL }}/tickets/showTicket/{{ $tid }}" class="task-link {{ $isDone ? 'task-link-done' : '' }}">
                                    {{ $headline }}
                                </a>
                            </td>
                            <td class="task-assignee">
                                @if($editorFirst || $editorLast)
                                <span class="assignee-avatar">{{ strtoupper(substr($editorFirst, 0, 1)) }}{{ strtoupper(substr($editorLast, 0, 1)) }}</span>
                                {{ trim($editorFirst . ' ' . $editorLast) }}
                                @else
                                <span style="opacity:.4;">—</span>
                                @endif
                            </td>
                            <td>
                                @php
                                $badgeClass = match(true) {
                                $isDone => 'task-status-done',
                                $isBlocked => 'task-status-blocked',
                                default => 'task-status-open',
                                };
                                @endphp
                                <span class="task-status-pill {{ $badgeClass }}">{{ $taskLabel['name'] }}</span>
                            </td>
                            <td class="task-due {{ $isOverdue ? 'task-overdue' : '' }}">
                                {{ $taskDueStr ?: '—' }}
                                @if($isOverdue) <i class="fa fa-exclamation-triangle" title="Overdue"></i> @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            {{-- Description --}}
            @if(!empty($milestone->description))
            <div class="mr-section">
                <div class="mr-section-title"><i class="fa fa-align-left"></i> Notes</div>
                <div class="mr-description">{!! nl2br(e($milestone->description)) !!}</div>
            </div>
            @endif

            {{-- Comments --}}
            <div class="mr-section">
                <div class="mr-section-title"><i class="fa fa-comments"></i> Comments</div>
                @php $tpl->assign('formUrl', '/tickets/editMilestone/' . $milestone->id); @endphp
                {!! $tpl->displaySubmodule('comments-generalComment') !!}
            </div>

        </div>

        {{-- ══ Right: Actions sidebar ══ --}}
        <div class="mr-sidebar">

            {{-- Action card --}}
            <div class="mr-action-card">
                <div class="mr-action-title"><i class="fa fa-bolt"></i> Actions</div>

                <form method="post" action="{{ BASE_URL }}/tickets/editMilestone/{{ $milestone->id }}?id={{ $milestone->id }}">

                    @if(!$isCompleted)

                    @if($isReadyForReview && $canReview)
                    {{-- Senior: Approve or Reject --}}
                    <div class="mr-action-group">
                        <p class="mr-action-hint">This milestone is awaiting your approval.</p>
                        <button type="submit" name="markComplete" value="1" class="btn btn-success btn-block mr-action-btn">
                            <i class="fa fa-check"></i> Approve &amp; Complete
                        </button>
                    </div>

                    @elseif($isInProgress)
                    @if($canReview)
                    {{-- Senior: mark complete directly --}}
                    <div class="mr-action-group">
                        <button type="submit" name="markComplete" value="1" class="btn btn-success btn-block mr-action-btn">
                            <i class="fa fa-check"></i> Mark Complete
                        </button>
                    </div>
                    @elseif($allTasksDone)
                    {{-- Junior: all tasks done, can submit --}}
                    <div class="mr-action-group">
                        <p class="mr-action-hint">All tasks are done. Submit this milestone for review.</p>
                        <button type="submit" name="sendForReview" value="1" class="btn btn-primary btn-block mr-action-btn">
                            <i class="fa fa-paper-plane"></i> Send for Review
                        </button>
                    </div>
                    @else
                    <div class="mr-action-group">
                        <p class="mr-action-hint" style="opacity:.6;">Complete all tasks before sending for review.</p>
                        <button type="button" class="btn btn-default btn-block mr-action-btn" disabled>
                            <i class="fa fa-lock"></i> Send for Review
                        </button>
                    </div>
                    @endif
                    @endif

                    @else
                    <p class="mr-action-hint" style="color:#5cb85c; font-weight:600;">
                        <i class="fa fa-check-circle"></i> This milestone is complete.
                    </p>
                    @endif

                </form>
            </div>

            {{-- Inline edit card --}}
            <div class="mr-info-card">
                <div class="mr-action-title">
                    <i class="fa fa-pencil"></i> Edit Details
                </div>
                <form method="post" action="{{ BASE_URL }}/tickets/editMilestone/{{ $milestone->id }}?id={{ $milestone->id }}">
                    <div class="mr-field">
                        <label class="mr-label">Title</label>
                        <input type="text" name="headline" value="{{ $milestone->headline ?? '' }}"
                            class="mr-input" placeholder="Milestone title" />
                    </div>
                    <div class="mr-field">
                        <label class="mr-label">Owner</label>
                        <select name="editorId" class="mr-input">
                            <option value="">— Not assigned —</option>
                            @foreach($tpl->get('users') ?? [] as $u)
                            <option value="{{ $u['id'] }}"
                                {{ (isset($milestone->editorId) && $milestone->editorId == $u['id']) ? 'selected' : '' }}>
                                {{ $u['firstname'] }} {{ $u['lastname'] }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mr-field">
                        <label class="mr-label">Start Date</label>
                        <input type="text" name="editFrom" id="milestoneEditFrom"
                            value="{{ format($milestone->editFrom ?? '')->date() }}"
                            class="mr-input" placeholder="{{ __('language.dateformat') }}" autocomplete="off" />
                    </div>
                    <div class="mr-field">
                        <label class="mr-label">Due Date</label>
                        <input type="text" name="editTo" id="milestoneEditTo"
                            value="{{ format($milestone->editTo ?? '')->date() }}"
                            class="mr-input" placeholder="{{ __('language.dateformat') }}" autocomplete="off" />
                    </div>
                    <div class="mr-field">
                        <label class="mr-label">Weight (pts)</label>
                        <input type="number" name="storypoints" min="0" max="100"
                            value="{{ $milestone->storypoints ?? '' }}"
                            class="mr-input" placeholder="0" />
                        <p class="mr-field-hint">Higher weight = more impact on overall project progress when this milestone is completed.</p>
                    </div>
                    <div class="mr-field">
                        <label class="mr-label">Tasks</label>
                        <div class="mr-label" style="font-weight:400; opacity:.7;">{{ $doneTasks }} / {{ $totalTasks }} done</div>
                    </div>
                    <div style="margin-top:12px;">
                        <button type="submit" name="headline" class="btn btn-primary btn-block" style="font-size:12px;">
                            <i class="fa fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<style>
    /* ── Milestone Review Page ── */
    .milestone-review-page {
        max-width: 1200px;
    }

    .milestone-review-breadcrumb {
        font-size: 12px;
        opacity: .6;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .milestone-review-breadcrumb a {
        color: inherit;
        text-decoration: none;
    }

    .milestone-review-breadcrumb a:hover {
        opacity: 1;
        text-decoration: underline;
    }

    .bc-sep {
        opacity: .4;
    }

    .milestone-review-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 28px;
        flex-wrap: wrap;
    }

    .mrh-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0 0 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .mrh-color-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        flex-shrink: 0;
        display: inline-block;
    }

    .mrh-meta {
        display: flex;
        gap: 18px;
        flex-wrap: wrap;
        font-size: 13px;
        opacity: .65;
    }

    .mrh-meta span {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    /* Status badges */
    .ms-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
    }

    .ms-badge-done {
        background: rgba(92, 184, 92, .15);
        color: #3d9140;
        border: 1px solid rgba(92, 184, 92, .3);
    }

    .ms-badge-review {
        background: rgba(230, 126, 34, .15);
        color: #e67e22;
        border: 1px solid rgba(230, 126, 34, .3);
    }

    .ms-badge-allDone {
        background: rgba(240, 173, 78, .15);
        color: #c8860a;
        border: 1px solid rgba(240, 173, 78, .3);
    }

    .ms-badge-inprogress {
        background: rgba(74, 158, 255, .12);
        color: var(--accent1, #4a9eff);
        border: 1px solid rgba(74, 158, 255, .25);
    }

    /* Two-column grid */
    .milestone-review-grid {
        display: grid;
        grid-template-columns: 1fr 280px;
        gap: 24px;
        align-items: start;
    }

    /* Sections */
    .mr-section {
        background: var(--secondary-background, #fff);
        border-radius: var(--box-radius, 8px);
        box-shadow: var(--min-shadow);
        padding: 20px 22px;
        margin-bottom: 16px;
    }

    .mr-section-title {
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        opacity: .6;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .mr-section-sub {
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        opacity: .7;
        font-size: 12px;
    }

    /* Progress bar */
    .mr-progress-track {
        height: 10px;
        background: rgba(0, 0, 0, .07);
        border-radius: 5px;
        overflow: hidden;
        margin-bottom: 6px;
    }

    .mr-progress-fill {
        height: 100%;
        border-radius: 5px;
        transition: width .4s ease;
    }

    .fill-done {
        background: #5cb85c;
    }

    .fill-review {
        background: #e67e22;
    }

    .fill-active {
        background: var(--accent1, #4a9eff);
    }

    .mr-progress-label {
        font-size: 12px;
        opacity: .6;
    }

    /* Task table */
    .mr-task-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .mr-task-table th {
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        opacity: .5;
        padding: 0 10px 8px;
        border-bottom: 1px solid rgba(0, 0, 0, .06);
    }

    .mr-task-row td {
        padding: 9px 10px;
        border-bottom: 1px solid rgba(0, 0, 0, .04);
        vertical-align: middle;
    }

    .mr-task-row:last-child td {
        border-bottom: none;
    }

    .mr-task-row:hover td {
        background: rgba(0, 0, 0, .02);
    }

    .mr-task-row.task-done {
        opacity: .55;
    }

    .mr-task-row.task-blocked td {
        background: rgba(217, 83, 79, .04);
    }

    .task-check {
        text-align: center;
        width: 36px;
    }

    .task-tick {
        color: #5cb85c;
        font-size: 16px;
    }

    .task-blocked-icon {
        color: #d9534f;
        font-size: 15px;
    }

    .task-open-icon {
        color: rgba(0, 0, 0, .25);
        font-size: 15px;
    }

    .task-link {
        color: inherit;
        text-decoration: none;
    }

    .task-link:hover {
        color: var(--accent1, #4a9eff);
        text-decoration: underline;
    }

    .task-link-done {
        text-decoration: line-through;
    }

    .task-assignee {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
    }

    .assignee-avatar {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: var(--accent1, #4a9eff);
        color: #fff;
        font-size: 9px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .task-status-pill {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
        white-space: nowrap;
    }

    .task-status-done {
        background: rgba(92, 184, 92, .15);
        color: #3d9140;
    }

    .task-status-blocked {
        background: rgba(217, 83, 79, .15);
        color: #d9534f;
    }

    .task-status-open {
        background: rgba(0, 0, 0, .07);
        color: inherit;
    }

    .task-due {
        font-size: 12px;
        opacity: .6;
        white-space: nowrap;
    }

    .task-overdue {
        color: #d9534f !important;
        opacity: 1 !important;
        font-weight: 600;
    }

    .mr-empty {
        text-align: center;
        padding: 30px;
        opacity: .5;
        font-size: 13px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }

    .mr-description {
        font-size: 13px;
        line-height: 1.65;
        opacity: .85;
    }

    /* Sidebar */
    .mr-action-card,
    .mr-info-card {
        background: var(--secondary-background, #fff);
        border-radius: var(--box-radius, 8px);
        box-shadow: var(--min-shadow);
        padding: 18px 20px;
        margin-bottom: 14px;
    }

    .mr-action-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        opacity: .5;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .mr-action-group {}

    .mr-action-hint {
        font-size: 12px;
        opacity: .7;
        margin-bottom: 8px;
        line-height: 1.5;
    }

    .mr-action-btn {
        font-size: 13px;
    }

    .mr-textarea {
        width: 100%;
        border-radius: var(--input-radius, 4px);
        border: 1px solid rgba(0, 0, 0, .15);
        padding: 8px;
        font-size: 12px;
        resize: vertical;
        background: var(--primary-background);
        color: var(--primary-font-color);
    }

    .mr-info-list {
        margin: 0;
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 6px 12px;
        font-size: 13px;
    }

    .mr-info-list dt {
        font-weight: 600;
        opacity: .55;
        white-space: nowrap;
    }

    .mr-info-list dd {
        margin: 0;
    }

    /* Inline edit fields */
    .mr-field {
        margin-bottom: 10px;
    }

    .mr-label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
        opacity: .55;
        margin-bottom: 4px;
    }

    .mr-input {
        width: 100%;
        padding: 6px 9px;
        font-size: 12px;
        border: 1px solid rgba(0, 0, 0, .15);
        border-radius: var(--input-radius, 4px);
        background: var(--primary-background);
        color: var(--primary-font-color);
        box-sizing: border-box;
    }

    .mr-input:focus {
        outline: none;
        border-color: var(--accent1, #4a9eff);
        box-shadow: 0 0 0 2px rgba(74, 158, 255, .15);
    }

    .mr-field-hint {
        font-size: 11px;
        opacity: .55;
        margin: 3px 0 0;
        line-height: 1.4;
    }

    .ms-delete-btn {
        font-size: 12px;
        padding: 4px 10px;
        white-space: nowrap;
    }

    /* Mobile */
    @media (max-width: 860px) {
        .milestone-review-grid {
            grid-template-columns: 1fr;
        }

        .mr-sidebar {
            order: -1;
        }
    }
</style>

<script>
    jQuery(document).ready(function() {
        leantime.ticketsController.initMilestoneDates();
    });
</script>

@endsection