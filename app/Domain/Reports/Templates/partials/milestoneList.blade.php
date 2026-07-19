{{--
    Milestone list used by all report screens in three modes:
    - completed: completion date + outcome narrative (inline-capturable) + key-task drill-down
    - inflight:  progress bar + due date (overdue rows passed in first by the engine)
    - upcoming:  schedule only

    Expects:
    $milestones:       object[] - engine-enriched milestone rows
    $mode:             string - completed|inflight|upcoming
    $showProjects:     bool - show project names next to milestones (rollup screens)
    $showTasks:        bool - show the key-task drill-down on completed milestones (default true)
    $allowOutcomeEdit: bool - enable inline outcome capture (project-level screen)
    $effortByMilestone: array<int, float> - hours logged per milestone in the period
    $period:           \Leantime\Domain\Reports\Models\ReportPeriod
    $emptyText:        string - shown when the list is empty
--}}
@php
    $showProjects = $showProjects ?? false;
    $showTasks = $showTasks ?? true;
    $allowOutcomeEdit = $allowOutcomeEdit ?? false;
    $effortByMilestone = $effortByMilestone ?? [];
@endphp

@if (count($milestones) === 0)
    <div class="tw-opacity-60 tw-text-sm tw-py-2">{{ $emptyText }}</div>
@else
    <ul class="reportMilestoneList">
        @foreach ($milestones as $milestone)
            <li class="reportMilestone" style="border-left: 3px solid {{ $milestone->tags }};">

                <div class="milestoneTitleRow">
                    @if ($mode === 'completed')
                        <i class="fa fa-check-circle" style="color: var(--green);"></i>
                    @endif
                    <strong>
                        <a href="{{ BASE_URL }}/tickets/editMilestone/{{ $milestone->id }}" class="milestoneModal hideLinkOnPrint">{{ $tpl->escape($milestone->headline) }}</a>
                    </strong>
                    @if ($showProjects)
                        <span class="milestoneProject">{{ $tpl->escape($milestone->projectName) }}</span>
                    @endif

                    <span class="milestoneMeta">
                        @if ($mode === 'completed')
                            {{ __('label.completed_on') }} {{ $milestone->completedOn?->formatDateForUser() ?? '—' }}
                        @elseif ($mode === 'upcoming')
                            {{ $milestone->startDate?->formatDateForUser() }} – {{ $milestone->dueDate?->formatDateForUser() ?? '—' }}
                        @else
                            @php $isOverdue = $milestone->dueDate !== null && $milestone->dueDate->isPast(); @endphp
                            <span @if ($isOverdue) style="color: var(--red); font-weight: 600;" @endif>
                                {{ __('label.due') }} {{ $milestone->dueDate?->formatDateForUser() ?? __('text.no_date_defined') }}
                            </span>
                        @endif
                        @if (!empty($effortByMilestone[$milestone->id]))
                            · {{ \Illuminate\Support\Number::format($effortByMilestone[$milestone->id], maxPrecision: 1) }} {{ __('label.hours_short') }}
                        @endif
                    </span>
                </div>

                @if ($mode === 'completed')
                    @include('reports::partials.outcome', ['milestone' => $milestone, 'canEdit' => $allowOutcomeEdit])

                    @if ($showTasks && !empty($milestone->keyTasks))
                        <details class="reportKeyTasks">
                            <summary>
                                {{ sprintf(__('text.tasks_done_of_total'), $milestone->taskStats['done'], $milestone->taskStats['total']) }}
                            </summary>
                            <ul class="tw-list-none tw-pl-5 tw-pt-1 tw-m-0 tw-text-sm tw-opacity-80">
                                @foreach ($milestone->keyTasks as $task)
                                    <li>
                                        <i class="fa fa-fw {{ $task->isDone ? 'fa-check tw-opacity-60' : 'fa-circle-o' }}"></i>
                                        {{ $tpl->escape($task->headline) }}
                                    </li>
                                @endforeach
                                @if ($milestone->taskStats['total'] > count($milestone->keyTasks))
                                    <li class="tw-opacity-60">{{ sprintf(__('text.and_n_more'), $milestone->taskStats['total'] - count($milestone->keyTasks)) }}</li>
                                @endif
                            </ul>
                        </details>
                    @endif
                @endif

                @if ($mode === 'inflight')
                    <div class="tw-flex tw-items-center tw-gap-3 tw-mt-2">
                        <div class="progress">
                            <div class="progress-bar progress-bar-success" role="progressbar"
                                 aria-valuenow="{{ round($milestone->percentDone) }}" aria-valuemin="0" aria-valuemax="100"
                                 style="width: {{ round($milestone->percentDone) }}%">
                            </div>
                        </div>
                        <span class="tw-text-sm tw-opacity-70 tw-whitespace-nowrap" style="font-variant-numeric: tabular-nums;">{{ round($milestone->percentDone) }}%
                            @if ($milestone->taskStats['total'] > 0)
                                · {{ sprintf(__('text.tasks_done_of_total'), $milestone->taskStats['done'], $milestone->taskStats['total']) }}
                            @endif
                        </span>
                    </div>
                @endif

            </li>
        @endforeach
    </ul>
@endif
